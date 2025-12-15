<?php
use Bitrix\Main\Loader;
require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
IncludeModuleLangFile(__FILE__);

class CKDAExportExcel {
	protected static $moduleId = 'esol.importexportexcel';
	protected static $moduleSubDir = 'export/';
	protected static $instance = array();
	private $pid = false;
	private $filesForMove = array();
	private $sectionPaths = array();
	private $sectionCache = array();
	private $sectionCacheSize = 0;
	private $imagedir = '';
	private $hlbl = array();
	private $hlblFields = array();
	private $arConvFields = array();
	private $colListUpdated = false;
	var $errors = array();

	function __construct($params=array(), $fparams=array(), $stepparams=false, $pid = false)
	{
		$this->params = $params;
		$this->fparams = $fparams;
		$this->memoryLimit = max(128*1024*1024, (int)CKDAExportUtils::GetIniAbsVal('memory_limit'));
		$this->maxReadRows = 100;
		$this->maxReadRowsWOffers = 20;
		if((int)$this->params['MAX_READ_ROWS'] > 0)
		{
			$this->maxReadRows = (int)$this->params['MAX_READ_ROWS'];
			$this->maxReadRowsWOffers = (int)$this->params['MAX_READ_ROWS'];
		}
		$this->stepparams = array();
		$this->stepparams['parentSections'] = array();
		$this->docRoot = rtrim($_SERVER["DOCUMENT_ROOT"], '/');
		$this->bCurrency = \Bitrix\Main\Loader::includeModule("currency");
		$this->bCatalog = \Bitrix\Main\Loader::includeModule("catalog");
		$this->bSale = \Bitrix\Main\Loader::includeModule('sale');
		$this->discountFromBasket = (bool)($this->bSale && (string)\Bitrix\Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y' && \Bitrix\KdaImportexcel\ClassManager::VersionGeqThen('catalog', '21.0.0'));
		$this->pid = $pid;
		$this->fparamsByName = array();
		if(is_array($this->params['FIELDS_LIST']))
		{
			foreach($this->params['FIELDS_LIST'] as $listIndex=>$arFields)
			{
				foreach($arFields as $key=>$field)
				{
					if(preg_match('/^(OFFER_)?IE_QR_CODE_IMAGE$/', $field))
					{
						if(!is_array($this->fparams[$listIndex][$key])) $this->fparams[$listIndex][$key] = array();
						$this->fparams[$listIndex][$key]['INSERT_PICTURE'] = 'Y';
						$this->fparams[$listIndex][$key]['QRCODE_SIZE'] = (isset($this->fparams[$listIndex][$key]['QRCODE_SIZE']) && (int)$this->fparams[$listIndex][$key]['QRCODE_SIZE'] > 0 ? (int)$this->fparams[$listIndex][$key]['QRCODE_SIZE'] : 3);
						$this->fparams[$listIndex][$key]['PICTURE_WIDTH'] = $this->fparams[$listIndex][$key]['PICTURE_HEIGHT'] = $this->fparams[$listIndex][$key]['QRCODE_SIZE']*41;
					}
					elseif(preg_match('/^(OFFER_)?ICAT_BARCODE_IMAGE$/', $field))
					{
						if(!is_array($this->fparams[$listIndex][$key])) $this->fparams[$listIndex][$key] = array();
						$this->fparams[$listIndex][$key]['INSERT_PICTURE'] = 'Y';
						$this->fparams[$listIndex][$key]['BARCODE_HEIGHT'] = (isset($this->fparams[$listIndex][$key]['BARCODE_HEIGHT']) && (int)$this->fparams[$listIndex][$key]['BARCODE_HEIGHT'] > 0 ? (int)$this->fparams[$listIndex][$key]['BARCODE_HEIGHT'] : 80);
						$this->fparams[$listIndex][$key]['PICTURE_HEIGHT'] = $this->fparams[$listIndex][$key]['BARCODE_HEIGHT'];
						$this->fparams[$listIndex][$key]['PICTURE_WIDTH'] = $this->fparams[$listIndex][$key]['PICTURE_HEIGHT']*2;
					}
					$this->fparamsByName[$listIndex][$field] = $this->fparams[$listIndex][$key];
				}
			}
		}
		if($this->params['EXPORT_SEP_SECTIONS']!='Y')
		{
			$this->params['EXPORT_GROUP_SUBSECTIONS'] = 'N';
			$this->params['EXPORT_GROUP_PRODUCTS'] = 'N';
			$this->params['EXPORT_GROUP_INDENT'] = 'N';
			$this->params['EXPORT_SECTION_PATH'] = 'N';
		}
		if(strlen($this->params['ELEMENT_MULTIPLE_SEPARATOR']))
		{
			$this->params['ELEMENT_MULTIPLE_SEPARATOR'] = $this->GetSeparator($this->params['ELEMENT_MULTIPLE_SEPARATOR']);
		}
		
		foreach($this->params['LIST_NAME'] as $listIndex=>$listName)
		{
			$this->params['LIST_NAME'][$listIndex] = preg_replace_callback('/\{DATE_(\S*)\}/', array('CKDAExportUtils', 'GetDateFormat'), $listName);
		}

		if(is_array($stepparams))
		{
			$this->stepparams = $stepparams;
			$this->stepparams['list_number'] = (strlen($this->stepparams['list_number']) > 0 ? intval($this->stepparams['list_number']) : '');
			$this->stepparams['list_current_page'] = intval($this->stepparams['list_current_page']);
			$this->stepparams['list_last_page'] = intval($this->stepparams['list_last_page']);
			$this->stepparams['total_read_line'] = intval($this->stepparams['total_read_line']);
			$this->stepparams['total_file_line'] = intval($this->stepparams['total_file_line']);
			$this->stepparams['image_cnt'] = intval($this->stepparams['image_cnt']);
			if(!isset($this->stepparams['string_lengths']) && ($this->params['FILE_EXTENSION']=='dbf' || $this->params['REMOVE_EMPTY_COLUMNS']=='Y')) $this->stepparams['string_lengths'] = array();
			$this->stepparams['currentPageCnt'] = intval($this->stepparams['currentPageCnt']);
			
			if(!isset($this->params['MAX_EXECUTION_TIME']) || $this->params['MAX_EXECUTION_TIME']!==0)
			{
				if(COption::GetOptionString(static::$moduleId, 'SET_MAX_EXECUTION_TIME')=='Y' && is_numeric(COption::GetOptionString(static::$moduleId, 'MAX_EXECUTION_TIME')))
				{
					$this->params['MAX_EXECUTION_TIME'] = intval(COption::GetOptionString(static::$moduleId, 'MAX_EXECUTION_TIME'));
					if(ini_get('max_execution_time') && $this->params['MAX_EXECUTION_TIME'] > ini_get('max_execution_time') - 5) $this->params['MAX_EXECUTION_TIME'] = ini_get('max_execution_time') - 5;
					if($this->params['MAX_EXECUTION_TIME'] < 5) $this->params['MAX_EXECUTION_TIME'] = 5;
					if($this->params['MAX_EXECUTION_TIME'] > 300) $this->params['MAX_EXECUTION_TIME'] = 300;
				}
				else
				{
					/*$this->params['MAX_EXECUTION_TIME'] = intval(ini_get('max_execution_time')) - 10;
					if($this->params['MAX_EXECUTION_TIME'] < 10) $this->params['MAX_EXECUTION_TIME'] = 10;
					if($this->params['MAX_EXECUTION_TIME'] > 50) $this->params['MAX_EXECUTION_TIME'] = 30;*/
					$this->params['MAX_EXECUTION_TIME'] = 10;
				}
			}
			
			/*Temp folders*/
			$dir = $this->SetTmpFolders($pid);
			
			$this->tmpfile = $this->tmpdir.'params.txt';
			$oProfile = CKDAExportProfile::getInstance();
			$oProfile->SetExportParams($pid, $stepparams);
			/*/Temp folders*/
			
			if(file_exists($this->tmpfile))
			{
				$this->stepparams = array_merge($this->stepparams, \KdaIE\Utils::Unserialize(file_get_contents($this->tmpfile)));
			}
			
			if(!isset($this->stepparams['curstep'])) $this->stepparams['curstep'] = 'export';
			
			$this->sftp = new \Bitrix\KdaImportexcel\Sftp();
		
			if($pid!==false)
			{
				$this->procfile = $dir.$pid.'.txt';
				if((int)$this->stepparams['export_started'] < 1)
				{
					$oProfile = CKDAExportProfile::getInstance();
					$oProfile->OnStartExport();
				
					$this->SaveStatusImport();
					if($this->params['EXPORT_FILES_IN_ARCHIVE']=='Y' && strlen($this->params['FILES_ARCHIVE_PATH']) > 0)
					{
						if(preg_match('#\s*ftps?://#is', $this->params['FILES_ARCHIVE_PATH'], $m))
						{
							$ftpFolder = preg_replace('#/[^/]*$#', '/', $this->params['FILES_ARCHIVE_PATH']);
							$ftpFile = mb_substr($this->params['FILES_ARCHIVE_PATH'], mb_strlen($ftpFolder));
							$arFtpFiles = $this->sftp->GetListFiles($ftpFolder);
							$arFtpFiles = preg_grep('#/'.preg_replace('/\\\./', '(_\d+)?\.', preg_quote($ftpFile, '#')).'$#', $arFtpFiles);
							foreach($arFtpFiles as $ftpFileItem)
							{
								$this->sftp->Delete($ftpFolder.'/'.end(explode('/', $ftpFileItem)));
							}
						}
						else
						{
							$archivePath = $this->docRoot. preg_replace('/\.zip\s*$/U', '', '/'.ltrim($this->params['FILES_ARCHIVE_PATH'], '/'));
							for($suffix=0; $suffix<501; $suffix++)
							{
								$zipFile = $archivePath.($suffix > 0 ? '_'.$suffix : '').'.zip';
								if(file_exists($zipFile)) unlink($zipFile);
							}
						}
					}
				}
			}
		}
		elseif($pid!==false)
		{
			$this->SetTmpFolders($pid, '_preview');
		}
	}
	
	public static function getInstance($params=array(), $fparams=array(), $stepparams=false, $pid = false)
	{
		$hash = md5(serialize(array($params, $fparams, $stepparams, $pid)));
		if (!isset(static::$instance[$hash]))
			static::$instance[$hash] = new static($params, $fparams, $stepparams, $pid);

		return static::$instance[$hash];
	}
	
	public function SetTmpFolders($pid, $suffix='')
	{		
		$dir = $this->docRoot.'/upload/tmp/'.static::$moduleId.'/'.static::$moduleSubDir;
		CheckDirPath($dir);
		if(!isset($this->stepparams) || !is_array($this->stepparams)) $this->stepparams = array();
		if(!$this->stepparams['tmpdir'])
		{
			if($this->stepparams['EXPORT_MODE']=='COMPONENT' && $pid!==false)
			{
				$i = 0;
				while(($tmpdir = $dir.'p'.$pid.$suffix.'_'.$i.'/') && file_exists($tmpdir)){$i++;}
			}
			elseif($pid!==false)
			{
				$tmpdir = $dir.'p'.$pid.$suffix.'/';
				if(file_exists($tmpdir))
				{
					DeleteDirFilesEx(substr($tmpdir, strlen($this->docRoot)));
				}
			}
			else
			{
				$i = 0;
				while(($tmpdir = $dir.$i.'/') && file_exists($tmpdir)){$i++;}
			}
			$this->stepparams['tmpdir'] = $tmpdir;
			CheckDirPath($tmpdir);
		}
		$this->tmpdir = $this->stepparams['tmpdir'];
		$this->imagedir = $this->stepparams['tmpdir'].'images/';
		CheckDirPath($this->imagedir);
		return $dir;
	}
	
	public function GetPublicImagePath()
	{
		return substr($this->imagedir, strlen($this->docRoot));
	}
	
	public function GetProfileId()
	{
		return $this->pid;
	}
	
	public function CheckTimeEnding()
	{
		//return ($this->params['MAX_EXECUTION_TIME'] && (time()-$this->timeBegin >= $this->params['MAX_EXECUTION_TIME']));
		return ($this->params['MAX_EXECUTION_TIME'] && (time()-$this->timeBegin >= $this->params['MAX_EXECUTION_TIME'] || $this->memoryLimit - memory_get_peak_usage() < 2097152));
	}
	
	public function OpenTmpdataHandler($listIndex, $mode = 'a')
	{
		$this->CloseTmpdataHandler();
		$this->tmpdatafile = $this->tmpdir.'data_'.$listIndex.'.txt';
		$this->tmpdatafilehandler = fopen($this->tmpdatafile, $mode);
	}
	
	public function CloseTmpdataHandler()
	{
		if($this->tmpdatafilehandler)
		{
			fclose($this->tmpdatafilehandler);
		}
		$this->tmpdatafilehandler = false;
	}
	
	public function WriteTmpdata($arElement)
	{
		fwrite($this->tmpdatafilehandler, base64_encode(serialize($arElement))."\r\n");
	}
	
	public function Export()
	{
		$this->stepparams['export_started'] = 1;
		$this->SaveStatusImport();
		$this->timeBegin = time();
		
		$arListIndexes = array(0);
		if(is_array($this->params['LIST_NAME']) && count($this->params['LIST_NAME']) > 0)
		{
			$arListIndexes = array_keys($this->params['LIST_NAME']);
		}
		
		/*Search CT list*/
		$arListIndexesOrig = $arListIndexes;
		$ctListIndex = false;
		$ctKeys = array_keys(\CKDAEEFieldList::GetContentTableFields());
		foreach($arListIndexes as $k=>$v)
		{
			$arListFields = $this->GetFieldList($v);
			if(!empty($arListFields) && count(array_diff($arListFields, $ctKeys))==0)
			{
				$ctListIndex = $v;
				break;
			}
		}
		if($ctListIndex!==false)
		{
			$arListIndexes = array_diff($arListIndexes, array($ctListIndex));
			$arListIndexes[] = $ctListIndex;
		}
		/*/Search CT list*/
		
		//$listIndex = 0;
		$listIndex = $this->stepparams['list_number'];
		if(!in_array($listIndex, $arListIndexes, true)) $listIndex = (int)current($arListIndexes);
		//$maxListIndex = max($arListIndexes);
		$lastListIndex = end($arListIndexes);
		
		$page = max(1, $this->stepparams['list_current_page']);
		$lastPage = $this->stepparams['list_last_page'];
		$sectionKey = max(1, $this->stepparams['list_current_section']);
		$lastSectionKey = $this->stepparams['list_last_section'];
		$arFields = $this->GetFieldList($listIndex);
		
		//($lastPage > 0 && $page > $lastPage) - deleted. When $sectionKey inc, $page=1
		$break = (($lastSectionKey > 0 && $sectionKey > $lastSectionKey) 
			/*&& ($lastPage > 0 && $page > $lastPage)*/
			/*&& ($listIndex >= $maxListIndex)*/ && ($listIndex == $lastListIndex));
		if(!$break) $this->OpenTmpdataHandler($listIndex);
		while(!$break)
		{
			$this->currentPageCnt = $this->stepparams['currentPageCnt'];
			$arRes = $this->GetExportData($listIndex, $this->maxReadRows, $page, $sectionKey);
			$arData = $arRes['DATA'];
			$lastPage = $arRes['PAGE_COUNT'];
			$recordCount = $arRes['RECORD_COUNT'];
			$sectionKey = $arRes['SECTION_KEY'];
			$lastSectionKey = $arRes['SECTION_COUNT'];
			
			if(!empty($arData))
			{
				foreach($arData as $arElement)
				{
					$this->WriteTmpdata($arElement);
					$this->stepparams['total_read_line']++;
					if(!isset($this->stepparams['rows'][$listIndex])) $this->stepparams['rows'][$listIndex] = 0;
					$this->stepparams['rows'][$listIndex]++;
					if(!isset($this->stepparams['rows2'][$listIndex])) $this->stepparams['rows2'][$listIndex] = 0;
					$this->stepparams['rows2'][$listIndex] += ((isset($arElement['ROWS_COUNT']) && (int)$arElement['ROWS_COUNT'] > 0) ? (int)$arElement['ROWS_COUNT'] : 1);
				}
			}
			
			if(!$this->stepparams['currentPageCnt']) $page++;
			$break = (($lastSectionKey > 0 && $sectionKey > $lastSectionKey) && ($page > $lastPage));
			if($break)
			{
				$break = ($break /*&& ($listIndex >= $maxListIndex)*/ && ($listIndex == $lastListIndex));
				if(!$break)
				{
					$lastSectionKey = $sectionKey = $lastPage = $page = 1;
					reset($arListIndexes);
					$next = current($arListIndexes);
					while($next!=$listIndex && (($next = next($arListIndexes)) || $next!==false)){}
					$next = next($arListIndexes);
					$listIndex = $next;
					unset($this->sepSectionIds);
					$this->stepparams['parentSections'] = array();
					$this->OpenTmpdataHandler($listIndex);
				}
			}
			
			if($page > $lastPage)
			{
				$page = 1;
			}
			
			$this->stepparams['list_number'] = $listIndex;
			$this->stepparams['list_current_page'] = $page;
			$this->stepparams['list_last_page'] = $lastPage;
			$this->stepparams['list_current_section'] = $sectionKey;
			$this->stepparams['list_last_section'] = $lastSectionKey;
			$this->stepparams['total_file_line'] = $recordCount;
			$this->SaveStatusImport();
			if($this->CheckTimeEnding())
			{
				return $this->GetBreakParams();
			}
		}
		
		$this->CloseTmpdataHandler();
		$this->PutFileToArchive();
		if($this->CheckTimeEnding())
		{
			return $this->GetBreakParams();
		}
		
		$arListIndexes = $arListIndexesOrig;
		CKDAExportUtils::PrepareTextRows($this->params['TEXT_ROWS_TOP'], $this->params, $this->stepparams);
		CKDAExportUtils::PrepareTextRows($this->params['TEXT_ROWS_TOP2'], $this->params, $this->stepparams);
		CKDAExportUtils::PrepareTextRows($this->params['TEXT_ROWS_TOP3'], $this->params, $this->stepparams);

		if(isset($this->stepparams['OUTPUTFILE']) && $this->stepparams['OUTPUTFILE']) $filePath = $this->stepparams['OUTPUTFILE'];
		else $filePath = CKDAExportUtils::PrepareExportFileName($this->params['FILE_PATH']);
		$outputFile = $this->docRoot.$filePath;
		$dir = dirname($filePath);
		if(strlen($dir) > 1 && $dir!='/upload' && file_exists($dir) && is_writable($dir))
		{
			$outputFile = $filePath;
		}
		else
		{
			CheckDirPath(dirname($outputFile).'/');
		}
		
		//update column list
		foreach($arListIndexes as $listIndex)
		{
			$arFields = $this->GetFieldList($listIndex, true);
		}
		
		$arWriterParams = array(
			'DOCROOT' => $this->docRoot,
			'OUTPUTFILE' => $outputFile,
			'TMPDIR' => $this->tmpdir,
			'IMAGEDIR' => $this->imagedir,
			'LIST_INDEXES' => $arListIndexes,
			'ROWS' => $this->stepparams['rows'],
			'STRING_LENGTHS' => $this->stepparams['string_lengths'],
			'EXTRAPARAMS' => $this->fparams,
			'PARAMS' => $this->params,
			'LISTINDEX' => $listIndex
		);
		if($this->params['FILE_EXTENSION']=='xlsx' || $this->params['FILE_EXTENSION']=='xlsm')
		{
			$objWriter = false;
			if(isset($this->stepparams['WRITER_FILE_PARAMS']) && file_exists($this->stepparams['WRITER_FILE_PARAMS']))
			{
				$objWriter = \KdaIE\Utils::Unserialize(file_get_contents($this->stepparams['WRITER_FILE_PARAMS']), 'CKDAExportExcelWriterXlsx');
				if(is_callable(array($objWriter, 'SetEObject')))
				{
					$objWriter->SetEObject($this);
				}
			}
			if(!is_object($objWriter))
			{
				$objWriter = new CKDAExportExcelWriterXlsx($arWriterParams, $this);
			}
			if(false===$objWriter->Save()/* && $this->CheckTimeEnding()*/)
			{
				$writerFileParams = $this->tmpdir.'writer_params.txt';
				file_put_contents($writerFileParams, serialize($objWriter));
				$this->stepparams['WRITER_FILE_PARAMS'] = $writerFileParams;
				return $this->GetBreakParams();
			}
		}
		elseif($this->params['FILE_EXTENSION']=='csv')
		{
			$objWriter = false;
			if(isset($this->stepparams['WRITER_FILE_PARAMS']) && file_exists($this->stepparams['WRITER_FILE_PARAMS']))
			{
				$objWriter = \KdaIE\Utils::Unserialize(file_get_contents($this->stepparams['WRITER_FILE_PARAMS']), 'CKDAExportExcelWriterCsv');
				if(is_callable(array($objWriter, 'SetEObject')))
				{
					$objWriter->SetEObject($this);
				}
			}
			if(!is_object($objWriter))
			{
				$objWriter = new CKDAExportExcelWriterCsv($arWriterParams, $this);
			}
			if(false===$objWriter->Save()/* && $this->CheckTimeEnding()*/)
			{
				$writerFileParams = $this->tmpdir.'writer_params.txt';
				file_put_contents($writerFileParams, serialize($objWriter));
				$this->stepparams['WRITER_FILE_PARAMS'] = $writerFileParams;
				return $this->GetBreakParams();
			}
		}
		elseif($this->params['FILE_EXTENSION']=='dbf')
		{
			$dir = dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel/Reader/XBase/';
			require_once($dir.'Table.php');
			require_once($dir.'WritableTable.php');
			require_once($dir.'Column.php');
			require_once($dir.'Record.php');
			require_once($dir.'Memo.php');
		
			$objWriter = false;
			if(isset($this->stepparams['WRITER_FILE_PARAMS']) && file_exists($this->stepparams['WRITER_FILE_PARAMS']))
			{
				$objWriter = \KdaIE\Utils::Unserialize(file_get_contents($this->stepparams['WRITER_FILE_PARAMS']), array('CKDAExportExcelWriterDbf', 'Xbase\WritableTable', 'XBase\Column', 'XBase\Record'));
				if(is_callable(array($objWriter, 'SetEObject')))
				{
					$objWriter->SetEObject($this);
				}
			}
			if(!is_object($objWriter))
			{
				$objWriter = new CKDAExportExcelWriterDbf($arWriterParams, $this);
			}
			if(false===$objWriter->Save()/* && $this->CheckTimeEnding()*/)
			{
				$writerFileParams = $this->tmpdir.'writer_params.txt';
				file_put_contents($writerFileParams, serialize($objWriter));
				$this->stepparams['WRITER_FILE_PARAMS'] = $writerFileParams;
				return $this->GetBreakParams();
			}
		}
		else
		{
			$writerType = 'CSV';
			if($this->params['FILE_EXTENSION']=='xlsx') $writerType = 'Excel2007';
			elseif($this->params['FILE_EXTENSION']=='xls') $writerType = 'Excel5';
			elseif($this->params['FILE_EXTENSION']=='pdf') $writerType = 'PDF';
			elseif($this->params['FILE_EXTENSION']=='html') $writerType = 'HTML';
			
			$objPHPExcel = new KDAPHPExcel();
			$arCols = range('A', 'Z');
			foreach(range('A', 'Z') as $v1)
			{
				foreach(range('A', 'Z') as $v2)
				{
					$arCols[] = $v1.$v2;
				}
			}
			
			$row = 1;
			foreach($arListIndexes as $listIndex)
			{
				$arFields = $this->GetFieldList($listIndex);
				if($listIndex == 0) $worksheet = $objPHPExcel->getActiveSheet();
				else
				{
					if($writerType != 'CSV')
					{
						$worksheet = $objPHPExcel->createSheet();
						$row = 1;
					}
				}

				if($this->params['GRIDLINES']=='HIDE')
				{
					$worksheet->setShowGridlines(false);
				}
				if($this->params['MARGINS']=='NONE')
				{
					$obMargins = new KDAPHPExcel_Worksheet_PageMargins();
					$obMargins->setLeft(0);
					$obMargins->setRight(0);
					$obMargins->setTop(0);
					$obMargins->setBottom(0);
					$worksheet->setPageMargins($obMargins);
				}
				if($this->params['LIST_NAME'][$listIndex])
				{
					$worksheet->setTitle($this->GetCellValue($this->params['LIST_NAME'][$listIndex]));
				}
				$this->SetExcelStyleForObj($worksheet->getDefaultStyle(), $this->params);
				$lastCol = count($arFields) - 1;
				$arMergeCells = array();
				
				if(count($arCols) < $lastCol + 1)
				{
					$arLetters = range('A', 'Z');
					$letter = current($arLetters);
					while(count($arCols) < $lastCol + 1)
					{
						foreach(range('A', 'Z') as $v1)
						{
							foreach(range('A', 'Z') as $v2)
							{
								$arCols[] = $letter.$v1.$v2;
							}
						}
						$letter = next($arLetters);
					}
				}
				
				$this->AddTextRows($row, $arMergeCells, $listIndex, 'TEXT_ROWS_TOP', $worksheet, $arCols, $lastCol);

				if($this->params['HIDE_COLUMN_TITLES'][$listIndex]!='Y')
				{
					$col = 0;
					$fNames = array();
					if(isset($this->params['FIELDS_LIST_NAMES'][$listIndex]))
					{
						$fNames = $this->params['FIELDS_LIST_NAMES'][$listIndex];
					}
					foreach($arFields as $k=>$field)
					{
						$width = 200;
						if(isset($this->fparams[$listIndex][$col]['DISPLAY_WIDTH']) && (int)$this->fparams[$listIndex][$col]['DISPLAY_WIDTH'] > 0) $width = (int)$this->fparams[$listIndex][$col]['DISPLAY_WIDTH'];
						$worksheet->getColumnDimension($arCols[$col])->setWidth($width / 9.7);
						$worksheet->setCellValueExplicit($arCols[$col].$row, $this->GetCellValue($fNames[$k]));
						$col++;
					}
					$this->SetExcelStyle($worksheet, (array)$this->params['DISPLAY_PARAMS'][$listIndex]['COLUMN_TITLES'], $arCols[0].$row.':'.$arCols[$lastCol].$row);
					$row++;
				}
				
				$this->AddTextRows($row, $arMergeCells, $listIndex, 'TEXT_ROWS_TOP2', $worksheet, $arCols, $lastCol);

				$this->OpenTmpdataHandler($listIndex, 'r');
				while(!feof($this->tmpdatafilehandler)) 
				{
					$buffer = trim(fgets($this->tmpdatafilehandler));
					if(strlen($buffer) < 1) continue;
					$arElement = \KdaIE\Utils::Unserialize(base64_decode($buffer));
					if(empty($arElement)) continue;
					
					if(isset($arElement['RTYPE']) && ($arElement['RTYPE']=='SECTION_PATH' || preg_match('/^SECTION_\d+$/', $arElement['RTYPE'])))
					{
						$worksheet->setCellValueExplicit($arCols[0].$row, $this->GetCellValue($arElement['NAME']));
						if($lastCol > 0) $arMergeCells[] = $arCols[0].$row.':'.$arCols[$lastCol].$row;
						$this->SetExcelStyle($worksheet, (array)$this->params['DISPLAY_PARAMS'][$listIndex][$arElement['RTYPE']], $arCols[0].$row.':'.$arCols[$lastCol].$row);
					}
					else
					{
						$col = 0;
						$arSettings = (array)$this->fparams[$listIndex];
						foreach($arFields as $k=>$field)
						{
							$cell = $arCols[$col++].$row;
							$val = (isset($arElement[$field.'_'.$k]) ? $arElement[$field.'_'.$k] : $arElement[$field]);
							if(preg_match('/<a[^>]+class="kda\-ee\-conversion\-link"[^>]+href="([^"]*)"[^>]*>(.*)<\/a>/Uis', $val, $m))
							{
								$worksheet->getCell($cell)->setHyperlink(new \KDAPHPExcel_Cell_Hyperlink($m[1]));
								$val = $m[2];
							}
							if(isset($arSettings[$k]['INSERT_PICTURE']) && $arSettings[$k]['INSERT_PICTURE']=='Y')
							{
								if(strlen($val) > 0 && file_exists($this->imagedir.$val))
								{
									$objDrawing = new \KDAPHPExcel_Worksheet_Drawing();
									$objDrawing->setPath($this->imagedir.$val);
									$objDrawing->setCoordinates($cell);
									$objDrawing->setWorksheet($worksheet);
								}
								continue;
							}
							$worksheet->setCellValueExplicit($cell, $this->GetCellValue($val));
							
							$arStyles = array();
							if(isset($arElement['CELLSTYLE_ROW']) && is_array($arElement['CELLSTYLE_ROW'])) $arStyles = $arElement['CELLSTYLE_ROW'];
							if(isset($arElement['CELLSTYLE_'.$k]) && is_array($arElement['CELLSTYLE_'.$k])) $arStyles = array_merge($arStyles, $arElement['CELLSTYLE_'.$k]);
							if(!empty($arStyles)) $this->SetExcelStyle($worksheet, $arStyles, $cell);
						}
					}
					$row++;
				}
				$this->CloseTmpdataHandler();
				
				$this->AddTextRows($row, $arMergeCells, $listIndex, 'TEXT_ROWS_TOP3', $worksheet, $arCols, $lastCol);
				$worksheet->setMergeCells($arMergeCells);
			}
			
			if($writerType == 'PDF')
			{
				\KDAPHPExcel_Settings::setPdfRenderer('tcPDF', dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel/Writer/PDF/TCPDF/');
			}
			$objWriter = KDAPHPExcel_IOFactory::createWriter($objPHPExcel, $writerType);
			if($writerType == 'CSV')
			{
				//$objWriter->setExcelCompatibility(true);
				$delimiter = ($this->params['CSV_SEPARATOR'] ? $this->params['CSV_SEPARATOR'] : ';');
				$objWriter->setDelimiter($delimiter);
				$enclosure = ($this->params['CSV_ENCLOSURE'] ? $this->params['CSV_ENCLOSURE'] : '"');
				$objWriter->setEnclosure($enclosure);
				if($this->params['CSV_ENCODING']=='UTF-8')
				{
					$objWriter->setUseBOM(true);
				}
			}
			$objWriter->save($outputFile);
		}
		$this->SaveStatusImport(true);
		
		if($this->params['EXPORT_ARCHIVE']=='Y')
		{
			$zipFile = preg_replace('/\.[^\.]*$/', '', $outputFile).'.zip';
			if(file_exists($zipFile)) unlink($zipFile);
			$this->CreateZipArchive($zipFile, $outputFile);
		}
		
		$this->CheckExtServices($outputFile);
		
		$oProfile = CKDAExportProfile::getInstance();
		$arEventData = $oProfile->OnEndExport($outputFile, $this->stepparams);
		
		foreach(GetModuleEvents(static::$moduleId, "OnEndExport", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array($this->pid, $arEventData));
		}
		
		return $this->GetBreakParams('finish');
	}
	
	public function AddTextRows(&$row, &$arMergeCells, $listIndex, $code, $worksheet, $arCols, $lastCol)
	{
		if(isset($this->params[$code][$listIndex]))
		{
			foreach($this->params[$code][$listIndex] as $k=>$v)
			{
				$displayParams = (array)$this->params['DISPLAY_PARAMS'][$listIndex][$code.'_'.$k];
				if(preg_match('/^\[\[(\d+)\]\]$/', $v, $m))
				{
					$fileId = $m[1];
					$maxWidth = ($displayParams['PICTURE_WIDTH'] ? $displayParams['PICTURE_WIDTH'] : 3000);
					$maxHeight = ($displayParams['PICTURE_HEIGHT'] ? $displayParams['PICTURE_HEIGHT'] : 3000);
					$arFileOrig = \CKDAExportUtils::GetFileArray($fileId);
					$arFile = \CFile::MakeFileArray($fileId);
					if($arFile)
					{
						\CFile::ResizeImage($arFile, array("width" => $maxWidth, "height" => $maxHeight));
						$objDrawing = new \KDAPHPExcel_Worksheet_Drawing();
						$objDrawing->setPath($arFile['tmp_name']);
						$objDrawing->setCoordinates($arCols[0].$row);
						$objDrawing->setWorksheet($worksheet);
					}
				}
				else
				{
					$worksheet->setCellValueExplicit($arCols[0].$row, $this->GetCellValue($v));
				}
				$this->SetExcelStyle($worksheet, $displayParams, $arCols[0].$row.':'.$arCols[$lastCol].$row);
				if($lastCol > 0) $arMergeCells[] = $arCols[0].$row.':'.$arCols[$lastCol].$row;
				$row++;
			}
		}
	}
	
	public function SetExcelStyle($worksheet, $arStyle, $pRange)
	{
		if(!empty($arStyle))
		{
			if(!isset($this->excelStyles)) $this->excelStyles = array();
			ksort($arStyle);
			$key = md5(serialize($arStyle));
			if(!isset($this->excelStyles[$key]))
			{
				$pStyle = new \KDAPHPExcel_Style();
				$this->SetExcelStyleForObj($pStyle, $arStyle);				
				$this->excelStyles[$key] = $pStyle;
			}
			else
			{
				$pStyle = $this->excelStyles[$key];
			}
			$worksheet->setSharedStyle($pStyle, $pRange);
		}
	}
	
	public function SetExcelStyleForObj(&$pStyle, $arStyle)
	{
		if(isset($arStyle['STYLE_BOLD']) && $arStyle['STYLE_BOLD']=='Y') $pStyle->getFont()->setBold(true);
		if(isset($arStyle['STYLE_ITALIC']) && $arStyle['STYLE_ITALIC']=='Y') $pStyle->getFont()->setItalic(true);
		if(isset($arStyle['FONT_SIZE']) && (int)$arStyle['FONT_SIZE'] > 0) $pStyle->getFont()->setSize((int)$arStyle['FONT_SIZE']);
		if(isset($arStyle['FONT_COLOR']) && preg_match('/^#[0-9A-F]{6}$/i', $arStyle['FONT_COLOR']))
		{
			$pStyle->getFont()->setColor(new KDAPHPExcel_Style_Color('FF'.ToUpper(mb_substr($arStyle['FONT_COLOR'], 1))));
		}
		if(isset($arStyle['BACKGROUND_COLOR']) && preg_match('/^#[0-9A-F]{6}$/i', $arStyle['BACKGROUND_COLOR']))
		{
			$pStyle->getFill()->setFillType('solid');
			$pStyle->getFill()->setStartColor(new KDAPHPExcel_Style_Color('FF'.ToUpper(mb_substr($arStyle['BACKGROUND_COLOR'], 1))));
		}
		if(isset($arStyle['DISPLAY_TEXT_ALIGN']) && !isset($arStyle['TEXT_ALIGN'])) $arStyle['TEXT_ALIGN'] = $arStyle['DISPLAY_TEXT_ALIGN'];
		if(isset($arStyle['TEXT_ALIGN']) && in_array($arStyle['TEXT_ALIGN'], array('LEFT', 'RIGHT', 'CENTER')))
		{
			$pStyle->getAlignment()->setHorizontal(ToLower($arStyle['TEXT_ALIGN']));
		}
		if(isset($arStyle['DISPLAY_VERTICAL_ALIGN']) && !isset($arStyle['VERTICAL_ALIGN'])) $arStyle['VERTICAL_ALIGN'] = $arStyle['DISPLAY_VERTICAL_ALIGN'];
		if(isset($arStyle['VERTICAL_ALIGN']) && in_array($arStyle['VERTICAL_ALIGN'], array('TOP', 'BOTTOM', 'CENTER')))
		{
			$pStyle->getAlignment()->setVertical(ToLower($arStyle['VERTICAL_ALIGN']));
		}
		if(isset($arStyle['BORDER_STYLE']) && in_array($arStyle['BORDER_STYLE'], array('NONE', 'THIN', 'MEDIUM', 'THICK')))
		{
			$pStyle->getBorders()->applyFromArray(array(
				'top' => array('style'=>ToLower($arStyle['BORDER_STYLE'])),
				'right' => array('style'=>ToLower($arStyle['BORDER_STYLE'])),
				'bottom' => array('style'=>ToLower($arStyle['BORDER_STYLE'])),
				'left' => array('style'=>ToLower($arStyle['BORDER_STYLE']))
			));
		}
	}
	
	public function CheckExtServices($outputFile)
	{
		if($this->params['EXPORT_TO_BX24']=="Y" && $this->params['BX24_REST_URL'] && $this->params['BX24_FOLDER_ID'])
		{
			$url = trim($this->params['BX24_REST_URL']);
			if(substr($url, -1)!='/') $url .= '/';
			$folderType = 'storage';
			$folderId = trim($this->params['BX24_FOLDER_ID']);
			if(preg_match('/_\d+$/', $folderId, $m))
			{
				$folderType = ToLower(mb_substr($folderId, 0, -mb_strlen($m[0])));
				$folderId = mb_substr($m[0], 1);
			}
			$fileName = bx_basename($outputFile);
			$fileContent = base64_encode(file_get_contents($outputFile));
			if(in_array($folderType, array('folder', 'storage')))
			{
				$client = new \Bitrix\Main\Web\HttpClient();
				$res = $client->post($url.'disk.'.$folderType.'.getchildren', array('id' => $folderId, 'filter' => array('TYPE' => 'file', 'NAME'=>$fileName)));
				$arResult = \KdaIE\Utils::JsObjectToPhp($res);
				if($arResult['total'] > 0 && $arResult['result'][0]['ID'])
				{
					$fileId = $arResult['result'][0]['ID'];
					if($this->params['BX24_MODE']=='REPLACE')
					{
						$client = new \Bitrix\Main\Web\HttpClient();
						$res = $client->post($url.'disk.file.delete', array('id' => $fileId));
						$client = new \Bitrix\Main\Web\HttpClient();
						$res = $client->post($url.'disk.'.$folderType.'.uploadfile', array('id' => $folderId, 'data' => array('NAME' => $fileName), 'fileContent'=>$fileContent));
					}
					else
					{
						$client = new \Bitrix\Main\Web\HttpClient();
						$res = $client->post($url.'disk.file.uploadversion', array('id' => $fileId, 'fileContent'=>$fileContent));
					}
				}
				else
				{
					$client = new \Bitrix\Main\Web\HttpClient();
					$res = $client->post($url.'disk.'.$folderType.'.uploadfile', array('id' => $folderId, 'data' => array('NAME' => $fileName), 'fileContent'=>$fileContent));
				}
			}
		}
		
		if($this->params['EXPORT_TO_YADISK']=="Y" && $this->params['YADISK_TOKEN'] && $this->params['YADISK_PATH'])
		{
			$token = $this->params['YADISK_TOKEN'];
			$path = CKDAExportUtils::PrepareExportFileName($this->params['YADISK_PATH']);
			if(!defined('BX_UTF') || !BX_UTF) $path = \Bitrix\Main\Text\Encoding::convertEncoding($path, 'CP1251', 'UTF-8');
			$client = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>15, 'disableSslVerification'=>true));
			$client->setHeader('Authorization', "OAuth ".$token);
			$res = $client->get('https://cloud-api.yandex.net/v1/disk/resources/upload?path='.urlencode($path).'&overwrite=true');
			$arRes = \KdaIE\Utils::JsObjectToPhp($res);
			if(is_array($arRes) && $arRes['error'] && $arRes['message'])
			{
				$this->errors[] = sprintf(GetMessage("KDA_EE_YADISK_ERROR"), $arRes['message']);
			}
			if(is_array($arRes) && $arRes['href'])
			{
				$client = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>60, 'disableSslVerification'=>true));
				$client->setHeader('Authorization', "OAuth ".$token);
				if(class_exists('\Bitrix\Main\Web\Http\Stream'))
				{
					$handle = fopen($outputFile, 'r');
					$res = $client->query('PUT', $arRes['href'], $handle);
					fclose($handle);
				}
				else
				{
					$res = $client->query('PUT', $arRes['href'], file_get_contents($outputFile));
				}
			}
			
			if($this->params['EXPORT_FILES_IN_ARCHIVE']=='Y' && strlen($this->params['FILES_ARCHIVE_PATH']) > 0)
			{
				if(!preg_match('#\s*ftps?://#is', $this->params['FILES_ARCHIVE_PATH'], $m))
				{
					$yaDirPath = preg_replace('#/[^/]*$#', '/', $path);
					$archivePath = $this->docRoot. preg_replace('/\.zip\s*$/U', '', '/'.ltrim($this->params['FILES_ARCHIVE_PATH'], '/'));
					for($suffix=0; $suffix<501; $suffix++)
					{
						$zipFile = $archivePath.($suffix > 0 ? '_'.$suffix : '').'.zip';
						if(file_exists($zipFile))
						{
							$yaFilePath = $yaDirPath.end(explode('/', $zipFile));
							$client = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>15, 'disableSslVerification'=>true));
							$client->setHeader('Authorization', "OAuth ".$token);
							$res = $client->get('https://cloud-api.yandex.net/v1/disk/resources/upload?path='.urlencode($yaFilePath).'&overwrite=true');
							$arRes = \KdaIE\Utils::JsObjectToPhp($res);
							if(is_array($arRes) && $arRes['error'] && $arRes['message'])
							{
								$this->errors[] = sprintf(GetMessage("KDA_EE_YADISK_ERROR"), $arRes['message']);
							}
							if(is_array($arRes) && $arRes['href'])
							{
								$client = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>60, 'disableSslVerification'=>true));
								$client->setHeader('Authorization', "OAuth ".$token);
								if(class_exists('\Bitrix\Main\Web\Http\Stream'))
								{
									$handle = fopen($zipFile, 'r');
									$res = $client->query('PUT', $arRes['href'], $handle);
									fclose($handle);
								}
								else
								{
									$res = $client->query('PUT', $arRes['href'], file_get_contents($zipFile));
								}
							}
						}
					}
				}
			}
		}
		
		if($this->params['EXPORT_TO_GOOGLE_SPREADSHEETS']=="Y" && $this->params['GOOGLE_TOKEN'] && $this->params['GOOGLE_SID'] && function_exists('json_encode'))
		{
			$tblSheetId = false;
			$tblId = $this->params['GOOGLE_SID'];
			if(preg_match('/\:(\d+)$/', $tblId, $m))
			{
				$tblId = mb_substr($tblId, 0, -mb_strlen($m[1]) - 1);
				$tblSheetId = $m[1];
			}
			$refreshToken = $this->params['GOOGLE_TOKEN'];
			$accessToken = '';
			$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
			$res = $ob->post('https://esolutions.su/marketplace/oauth.php', array('refresh_token'=> $refreshToken));
			$arRes = \KdaIE\Utils::JsObjectToPhp($res);
			if($arRes['access_token'])
			{
				$accessToken = $arRes['access_token'];
			}

			if($accessToken)
			{
				$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
				$ob->setHeader('Authorization', "Bearer ".$accessToken);
				$res = $ob->get('https://sheets.googleapis.com/v4/spreadsheets/'.$tblId);
				$arRes = \KdaIE\Utils::JsObjectToPhp($res);
				$arSheets = $arRes['sheets'];
				$tblSheet = 0;
				if($tblSheetId!==false)
				{
					$tblSheet = false;
					foreach($arSheets as $k=>$v)
					{
						if($v['properties']['sheetId']==$tblSheetId) $tblSheet = $k;
					}
				}
				if($tblSheet!==false)
				{
					foreach($this->params['LIST_NAME'] as $listIndex=>$listName)
					{
						$iblockId = $this->GetIblockIdByListIndex($listIndex);
						$domain = '';
						if($host = $this->GetIblockDomain($iblockId)) $domain = '//'.$host;
						$dataFile = $this->tmpdir.'data_'.$listIndex.'.txt';
						if(file_exists($dataFile) && isset($arSheets[$listIndex+$tblSheet]))
						{
							$arSheet = $arSheets[$listIndex+$tblSheet];
							$listTitle = $arSheet['properties']['title'];
							$listID = $arSheet['properties']['sheetId'];
							
							/*Clear old data*/
							$maxRow = max(1, $arSheet['properties']['gridProperties']['rowCount']);
							$maxCol = max(1, $arSheet['properties']['gridProperties']['columnCount']);
							$maxColLetter = $maxCol;
							$arLNumbers = array();
							while($maxCol > 26)
							{
								$arLNumbers[] = ($maxCol-1)%26;
								$maxCol = ($maxCol-1)/26;
							}
							$arLNumbers[] = ($maxCol-1)%26;
							$arLNumbers = array_reverse($arLNumbers);
							$maxCol = '';
							foreach($arLNumbers as $n)
							{
								$maxCol .= range('A', 'Z')[$n];
							}
							$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
							$ob->setHeader('Authorization', "Bearer ".$accessToken);
							$res = $ob->post('https://sheets.googleapis.com/v4/spreadsheets/'.$tblId.'/values/'.(strlen($listTitle) > 0 ? "'".urlencode($listTitle)."'!" : '').'A1:'.$maxCol.$maxRow.':clear');
							/*/Clear old data*/
							
							/*Write new data*/
							$bFormula = false;
							$arData = $arDataFormula = $arImgHeights = $arStyleRequests = array();
							$arFields = $this->GetFieldList($listIndex);		
							//$this->AddGoogleTextRows($arData, $arStyleRequests, $arMergeCells, $listID, $listIndex, 'TEXT_ROWS_TOP');
							if($this->params['HIDE_COLUMN_TITLES'][$listIndex]!='Y')
							{
								$arDataRow = array();
								foreach($this->params['FIELDS_LIST_NAMES'][$listIndex] as $k=>$field)
								{
									$arDataRow[$k] = $this->GetGoogleCellValue($field);
								}
								$this->SetGoogleSheetsStyle($arStyleRequests, (array)$this->params['DISPLAY_PARAMS'][$listIndex]['COLUMN_TITLES'], $listID, count($arData), count($arData) + 1, 0, count($arFields) + 1);
								$arData[] = $arDataRow;
								$arDataFormula[] = array();
							}
							//$this->AddGoogleTextRows($arData, $arStyleRequests, $arMergeCells, $listID, $listIndex, 'TEXT_ROWS_TOP2');
							
							$frozenLines = 0;
							if($this->params['DISPLAY_LOCK_HEADERS']=='Y')
							{
								$frozenLines = count($arData);
								if(strlen($this->params['DISPLAY_LOCK_HEADERS_CNT']) > 0 && (int)$this->params['DISPLAY_LOCK_HEADERS_CNT'] > 0) $frozenLines = (int)$this->params['DISPLAY_LOCK_HEADERS_CNT'];
							}
							$arStyleRequests[] = array(
								'updateSheetProperties' => array(
									'properties' => array(
										'sheetId' => $listID,
										'gridProperties' => array('frozenRowCount' => $frozenLines)
									),
									'fields' => 'gridProperties.frozenRowCount'
								)
							);
							
							$firstRow = 1;
							$handle = fopen($dataFile, 'r');
							while(!feof($handle)) 
							{
								$buffer = trim(fgets($handle));
								if(strlen($buffer) < 1) continue;
								$arElement = \KdaIE\Utils::Unserialize(base64_decode($buffer));
								if(empty($arElement)) continue;

								if(isset($arElement['RTYPE']) && ($arElement['RTYPE']=='SECTION_PATH' || preg_match('/^SECTION_(\d+)$/', $arElement['RTYPE'], $m)))
								{
									$this->SetGoogleSheetsStyle($arStyleRequests, (array)$this->params['DISPLAY_PARAMS'][$listIndex][$arElement['RTYPE']], $listID, count($arData), count($arData) + 1, 0, count($arFields) + 1);
									$arData[] = array($this->GetGoogleCellValue($arElement['NAME']));
									$arDataFormula[] = array();
								}
								else
								{
									$level = 1;
									if($this->currentSectionLevel > 0) $level = $this->currentSectionLevel + 1;
									$rowParams = array();
									if($this->params['EXPORT_GROUP_OPEN']!='Y') $rowParams['hidden'] = 1;
									if($this->params['EXPORT_GROUP_SUBSECTIONS']=='Y') $rowParams['outlineLevel'] = $level;
									else $rowParams['outlineLevel'] = 1;
									
									/*Multicell*/
									$arVals = $fullCells = array();
									$cellKey = 0;
									foreach($arFields as $k=>$field)
									{
										$arSettings = (isset($this->fparams[$listIndex][$k]) ? $this->fparams[$listIndex][$k] : array());
										if(!is_array($arSettings)) $arSettings = array();
										$valIndex = 0;
										$val = (isset($arElement[$field.'_'.$k]) ? $arElement[$field.'_'.$k] : $arElement[$field]);
										if(is_array($val) && isset($val['TYPE']) && $val['TYPE']=='MULTICELL')
										{
											foreach($val as $kVal=>$vVal)
											{
												if(!is_numeric($kVal) && $kVal=='TYPE') continue;
												if(is_array($vVal) && isset($vVal['VALUE'])) $arVals[$valIndex][$k] = (string)$vVal['VALUE'];
												elseif(!is_array($vVal)) $arVals[$valIndex][$k] = (string)$vVal;
												else $arVals[$valIndex][$k] = '';
												foreach($arFields as $k2=>$field2)
												{
													if(!isset($arVals[$valIndex][$k2])) $arVals[$valIndex][$k2] = '';
												}
												$fullCells[$cellKey] = $valIndex;
												$valIndex++;
											}								
										}
										if($valIndex==0)
										{
											if($arSettings['INSERT_PICTURE']=='Y' && isset($arElement[$field]))
											{
												$val = $arElement[$field];
												if(isset($arElement[$field.'_'.$k.'_ORIG'])) $val = $arElement[$field.'_'.$k.'_ORIG'];
												if(preg_match('#^(.*/)([^/]+)$#', rawurldecode($val), $m)) $val = $m[1].rawurlencode($m[2]);
												if(strpos($val, '/')===0 && strpos($val, '//')!==0)
												{
													if($domain) $val = $domain.$val;
												}
												if(strlen($val) > 0) $val = '=IMAGE("'.$val.'";1)';
												if(isset($arElement[$field.'_'.$k.'_MAXHEIGHT']) && $arElement[$field.'_'.$k.'_MAXHEIGHT'] > 20)
												{
													$rowIndex = count($arData);
													$arImgHeights[$rowIndex] = max((int)$arImgHeights[$rowIndex], (int)$arElement[$field.'_'.$k.'_MAXHEIGHT']);
												}
											}
											$arVals[$valIndex][$k] = $val;
											$fullCells[$cellKey] = $valIndex;
										}
										
										if(isset($arElement['CELLSTYLE_ROW']) || isset($arElement['CELLSTYLE_'.$k]))
										{
											$arStyle = array();
											if(isset($arElement['CELLSTYLE_ROW']) && is_array($arElement['CELLSTYLE_ROW'])) $arStyle = array_merge($arStyle, $arElement['CELLSTYLE_ROW']);
											if(isset($arElement['CELLSTYLE_'.$k]) && is_array($arElement['CELLSTYLE_'.$k])) $arStyle = array_merge($arStyle, $arElement['CELLSTYLE_'.$k]);
											$this->SetGoogleSheetsStyle($arStyleRequests, $arStyle, $listID, count($arData), count($arData) + 1, $k, $k + 1);
										}
										
										$cellKey++;
									}
									/*/Multicell*/
									
									
									foreach($arVals as $valIndex=>$arValue)
									{
										$arDataRow = $arDataFormulaRow = array();
										foreach($arFields as $k=>$field)
										{
											$arDataRow[$k] = $this->GetGoogleCellValue($arValue[$k]);
											$arDataFormulaRow[$k] = null;
											if(preg_match('/^=[A-Z][A-Z0-9\.]+\(/', $arDataRow[$k]))
											{
												$arDataFormulaRow[$k] = preg_replace('/([A-Z])0/', '${1}'.(count($arDataFormula)+1), $arDataRow[$k]);
												$bFormula = true;
											}
										}
										$arData[] = $arDataRow;
										$arDataFormula[] = $arDataFormulaRow;
									}
								}
								
								/*if(count($arData) >= 1000)
								{
									$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
									$ob->setHeader('Authorization', "Bearer ".$accessToken);
									$ob->query('PUT', 'https://sheets.googleapis.com/v4/spreadsheets/'.$tblId.'/values/'.(strlen($listTitle) > 0 ? "'".urlencode($listTitle)."'!" : '').'A'.$firstRow.'?valueInputOption=RAW', json_encode(array('values'=>$arData)));
									$res = $ob->getResult();
									$firstRow += count($arData) - 1;
									$arData = array_slice($arData, -1);
								}*/
							}
							fclose($handle);
							
							//$this->AddGoogleTextRows($arData, $arStyleRequests, $arMergeCells, $listID, $listIndex, 'TEXT_ROWS_TOP3');
							
							if(count($arData) > 0)
							{
								$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
								$ob->setHeader('Authorization', "Bearer ".$accessToken);
								$ob->query('PUT', 'https://sheets.googleapis.com/v4/spreadsheets/'.$tblId.'/values/'.(strlen($listTitle) > 0 ? "'".urlencode($listTitle)."'!" : '').'A'.$firstRow.'?valueInputOption=RAW', json_encode(array('values'=>$arData)/*, JSON_UNESCAPED_UNICODE*/));
								$res = $ob->getResult();
								
								if($bFormula)
								{
									$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
									$ob->setHeader('Authorization', "Bearer ".$accessToken);
									$ob->query('PUT', 'https://sheets.googleapis.com/v4/spreadsheets/'.$tblId.'/values/'.(strlen($listTitle) > 0 ? "'".urlencode($listTitle)."'!" : '').'A'.$firstRow.'?valueInputOption=USER_ENTERED', json_encode(array('values'=>$arDataFormula)/*, JSON_UNESCAPED_UNICODE*/));
									$res = $ob->getResult();
								}
							}
							/*/Write new data*/
							
							if(count($arImgHeights) > 0)
							{
								$arRequests = array();
								foreach($arImgHeights as $rowIndex=>$rowHeight)
								{
									$arRequests[] = array(
										'updateDimensionProperties' => array(
											'properties' => array('pixelSize'=>$rowHeight), 
											'fields' => 'pixelSize',
											'range'=>array('sheetId'=>$listID, 'dimension'=>'ROWS', 'startIndex'=>$rowIndex, 'endIndex'=>$rowIndex + 1)
										)
									);
								}
								$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
								$ob->setHeader('Authorization', "Bearer ".$accessToken);
								$ob->setHeader('Content-type', 'application/json');
								$ob->query('POST', 'https://sheets.googleapis.com/v4/spreadsheets/'.$tblId.':batchUpdate', json_encode(
									array('requests' => $arRequests)
								));
								$res = $ob->getResult();
							}
							
							$i = 0;
							foreach($arFields as $k=>$field)
							{
								$this->SetGoogleSheetsStyle($arStyleRequests, (array)$this->fparams[$listIndex][$k], $listID, 0, count($arData), $i, $i + 1, true);
								$i++;
							}
							
							if(count($arStyleRequests) > 0)
							{							
								$this->SetGoogleSheetsStyle($arStyleRequests, true, $listID, 0, max(10000, count($arData) + 1), 0, count($arFields) + 1, true);
								$ob = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20, 'disableSslVerification'=>true));
								$ob->setHeader('Authorization', "Bearer ".$accessToken);
								$ob->setHeader('Content-type', 'application/json');
								$ob->query('POST', 'https://sheets.googleapis.com/v4/spreadsheets/'.$tblId.':batchUpdate', json_encode(
									array('requests' => $arStyleRequests)
								));
								$res = $ob->getResult();
							}
						}
					}
				}
			}
		}
		
		if($this->params['EXPORT_TO_EMAIL']=="Y" && (int)$this->params['MAIL_TEMPLATE_ID'] > 0 && $this->stepparams['total_read_line'] > 0)
		{
			$oProfile = CKDAExportProfile::getInstance();
			$arProfile = $oProfile->GetFieldsByID($this->pid);
			$arEmails = array_diff(array_map('trim', explode(';', $this->params['MAIL_TEMPLATE_EMAIL'])), array(''));
			if(count($arEmails)==0) $arEmails = array('');
			foreach($arEmails as $email)
			{
				$arMailFields = array(
					'DATE' => date('d.m.Y'),
					'DATETIME' => date('d.m.Y H:i:s'),
					'EMAIL_TO' => $email,
					'PROFILE_NAME' => $arProfile['NAME'],
					'EXPORT_START_DATETIME' => (is_callable(array($arProfile['DATE_START'], 'toString')) ? $arProfile['DATE_START']->toString() : ''),
					'EXPORT_FINISH_DATETIME' => ConvertTimeStamp(false, 'FULL')
				);
				\CEvent::Send('KDA_EXPORT_SEND_FILE', $this->GetDefaultSiteId(), $arMailFields, 'Y', $this->params['MAIL_TEMPLATE_ID'], array($outputFile));
			}
		}
		
		if(strlen(trim($this->params['EXPORT_TO_FTP'])) > 0)
		{
			$ftpPath = preg_replace_callback('/\{DATE_(\S*)\}/', array(__CLASS__, 'GetDateForPath'), $this->params['EXPORT_TO_FTP']);
			$this->sftp->Upload($ftpPath, $outputFile);
		}
		
		if($this->params['DELETE_LOCAL_FILE']=='Y')
		{
			unlink($outputFile);
			if($this->params['EXPORT_FILES_IN_ARCHIVE']=='Y' && strlen($this->params['FILES_ARCHIVE_PATH']) > 0)
			{
				if(!preg_match('#\s*ftps?://#is', $this->params['FILES_ARCHIVE_PATH'], $m))
				{
					$archivePath = $this->docRoot. preg_replace('/\.zip\s*$/U', '', '/'.ltrim($this->params['FILES_ARCHIVE_PATH'], '/'));
					for($suffix=0; $suffix<501; $suffix++)
					{
						$zipFile = $archivePath.($suffix > 0 ? '_'.$suffix : '').'.zip';
						if(file_exists($zipFile)) unlink($zipFile);
					}
				}
			}
		}
	}
	
	public function GetGoogleCellValue($val)
	{
		$val = mb_substr($val, 0, 32767);
		if(is_numeric($val) && !preg_match('/[A-Za-z]/', $val) && strlen($val)<ini_get('precision')) return (float)$val;
		return (string)$val;
	}
	
	public function AddGoogleTextRows(&$arData, &$arStyleRequests, &$arMergeCells, $listID, $listIndex, $code)
	{
		if(isset($this->params[$code][$listIndex]))
		{
			foreach($this->params[$code][$listIndex] as $k=>$v)
			{
				$displayParams = (array)$this->params['DISPLAY_PARAMS'][$listIndex][$code.'_'.$k];
				$arDataRow = array($v);
				foreach($this->params['FIELDS_LIST_NAMES'][$listIndex] as $k=>$field)
				{
					$arDataRow[$k] = $this->GetGoogleCellValue($field);
				}
				$this->SetGoogleSheetsStyle($arStyleRequests, (array)$displayParams, $listID, count($arData), count($arData) + 1, 0, count($arFields) + 1);
				$arData[] = $arDataRow;
				
				
				/*if(preg_match('/^\[\[(\d+)\]\]$/', $v, $m))
				{
					$fileId = $m[1];
					$maxWidth = ($displayParams['PICTURE_WIDTH'] ? $displayParams['PICTURE_WIDTH'] : 3000);
					$maxHeight = ($displayParams['PICTURE_HEIGHT'] ? $displayParams['PICTURE_HEIGHT'] : 3000);
					$arFileOrig = \CKDAExportUtils::GetFileArray($fileId);
					$arFile = \CFile::MakeFileArray($fileId);
					if($arFile)
					{
						\CFile::ResizeImage($arFile, array("width" => $maxWidth, "height" => $maxHeight));
						$objDrawing = new \KDAPHPExcel_Worksheet_Drawing();
						$objDrawing->setPath($arFile['tmp_name']);
						$objDrawing->setCoordinates($arCols[0].$row);
						$objDrawing->setWorksheet($worksheet);
					}
				}
				else
				{
					$worksheet->setCellValueExplicit($arCols[0].$row, $this->GetCellValue($v));
				}*/
			}
		}
	}
	
	public function SetGoogleSheetsStyle(&$arStyleRequests, $arStyle, $sheetId, $startRowIndex, $endRowIndex, $startColumnIndex, $endColumnIndex, $toBegining=false)
	{
		$resetStyle = (bool)($arStyle===true);
		$arColor = false;
		if($resetStyle)
		{
			$arColor = array(
				"red" => 1,
				"green" => 1,
				"blue" => 1,
				"alpha" => 0
			);
		}
		elseif(is_array($arStyle) && isset($arStyle['BACKGROUND_COLOR']) && preg_match('/^\s*#([0-9A-F]{6})\s*$/i', $arStyle['BACKGROUND_COLOR'], $m))
		{
			$arColor = array(
				"red" => hexdec(substr($m[1], 0, 2))/255,
				"green" => hexdec(substr($m[1], 2, 2))/255,
				"blue" => hexdec(substr($m[1], 4, 2))/255
			);
		}
		if(is_array($arColor))
		{
			$arRequest = array(
				'repeatCell' => array(
					'range' => array(
						'sheetId' => $sheetId,
						'startRowIndex' => $startRowIndex,
						'endRowIndex' => $endRowIndex,
						'startColumnIndex' => $startColumnIndex,
						'endColumnIndex' => $endColumnIndex
					),
					'cell' => array(
						'userEnteredFormat' => array(
							'backgroundColor' => $arColor
						)
					),
					'fields' => 'userEnteredFormat(backgroundColor)'
				)
			);
			if($toBegining) array_unshift($arStyleRequests, $arRequest);
			else array_push($arStyleRequests, $arRequest);
		}
	}
	
	public function GetIblockIdByListIndex($listIndex)
	{
		$iblockId = $this->params['IBLOCK_ID'];
		$changeIblockId = (bool)($this->params['CHANGE_IBLOCK_ID'][$listIndex]=='Y');
		if($changeIblockId && $this->params['LIST_IBLOCK_ID'][$listIndex])
		{
			$iblockId = $this->params['LIST_IBLOCK_ID'][$listIndex];
		}
		return $iblockId;
	}
	
	public function GetCellValue($val)
	{
		if($this->params['FILE_EXTENSION']=='csv' && $this->params['CSV_ENCODING']=='CP1251')
		{
			if(defined('BX_UTF') && BX_UTF)
			{
				$val = $GLOBALS['APPLICATION']->ConvertCharset($val, 'UTF-8', 'CP1251');
			}
		}
		elseif(!defined('BX_UTF') || !BX_UTF)
		{
			$val = $GLOBALS['APPLICATION']->ConvertCharset($val, 'CP1251', 'UTF-8');
		}
		return $val;
	}
	
	public function GetBreakParams($action = 'continue')
	{
		$arStepParams = array(
			'params'=> $this->stepparams,
			'action' => $action,
			'errors' => $this->errors,
			'sessid' => bitrix_sessid()
		);
		
		if($action == 'continue')
		{
			if(isset($this->tmpdatafilehandler) && $this->tmpdatafilehandler!==false) fclose($this->tmpdatafilehandler);
			file_put_contents($this->tmpfile, serialize($arStepParams['params']));
			/*if(file_exists($this->imagedir))
			{
				DeleteDirFilesEx(substr($this->imagedir, strlen($this->docRoot)));
			}*/
		}
		elseif(file_exists($this->tmpdir))
		{
			DeleteDirFilesEx(substr($this->tmpdir, strlen($this->docRoot)));
			unlink($this->procfile);
		}
		
		return $arStepParams;
	}
	
	public function ExecuteFilterExpression($val, $expression, $altReturn = true)
	{
		$expression = trim($expression);
		try{				
			if(stripos($expression, 'return')===0)
			{
				$command = $expression.';';
				$val = eval($command);
			}
			elseif(preg_match('/\$val\s*=/', $expression))
			{
				$command = $expression.';';
				eval($command);
				return $val;
			}
			else
			{
				$command = 'return '.$expression.';';
				$val = eval($command);
			}
			if(!isset($val)) $val = '';
			return $val;
		}catch(Exception | Error $ex){
			//return $altReturn;
			return $ex->getMessage();
		}
	}
	
	public function ExecuteOnAfterSaveHandler($handler, $ID)
	{
		try{				
			$command = $handler.';';
			eval($command);
		}catch(Exception $ex){}
	}
	
	public function SaveStatusImport($end = false)
	{
		if($this->procfile)
		{
			$writeParams = $this->stepparams;
			$writeParams['action'] = ($end ? 'finish' : 'continue');
			file_put_contents($this->procfile, \KdaIE\Utils::PhpToJSObject($writeParams));
		}
	}
	
	public function GetFileArray($file, $arDef=array())
	{
		$file = \Bitrix\Main\IO\Path::convertLogicalToPhysical(trim($file));
		if(strpos($file, '/')===0)
		{
			if(file_exists($this->docRoot.$file) || (preg_match('/\.[\w]{2,5}$/', $file) && file_exists($file)))
			{
				$arFile = CFile::MakeFileArray($file);
			}
			else
			{
				$arFile = array();
			}
		}
		elseif(preg_match('/http(s)?:\/\//', $file))
		{
			$arUrl = parse_url($file);
			//Cyrillic domain
			if(preg_match('/[^A-Za-z0-9\-\.]/', $arUrl['host']))
			{
				if(!class_exists('idna_convert')) require_once(dirname(__FILE__).'/../../lib/idna_convert.class.php');
				if(class_exists('idna_convert'))
				{
					$idn = new idna_convert();
					$oldHost = $arUrl['host'];
					if(!CUtil::DetectUTF8($oldHost)) $oldHost = CKDAExportUtils::Win1251Utf8($oldHost);
					$file = str_replace($arUrl['host'], $idn->encode($oldHost), $file);
				}
			}
			$arFile = CFile::MakeFileArray($file);
		}
		
		if(is_array($arFile) && !empty($arFile) && isset($arFile['tmp_name']))
		{
			$ext = '.jpg';
			if(preg_match('/\.[^\.]{2,5}$/', $arFile['name'], $m))
			{
				$ext = ToLower($m[0]);
			}
			if($ext=='.webp')
			{
				if(stripos($arFile['type'], 'webp')!==false)
				{
					$file = $this->GetNewImagePath('png');
					if(function_exists('imagecreatefromwebp') && function_exists('imagepng'))
					{
						$img = imagecreatefromwebp($arFile['tmp_name']);
						imageinterlace($img, false);
						imagepng($img, $file, 9);
						imagedestroy($img);
					}
				}
				elseif(preg_match('/^image\//i', $arFile['type'], $m))
				{
					$file = $this->GetNewImagePath(substr($arFile['type'], 6));
					copy($arFile['tmp_name'], $file);
				}
			}
			else
			{
				$file = $this->GetNewImagePath($ext);
				copy($arFile['tmp_name'], $file);
			}
		}
		else return $arFile;
		
		$arFile = CFile::MakeFileArray($file);
		if(!$arFile['name'] && !CUtil::DetectUTF8($file))
		{
			$file = CKDAExportUtils::Win1251Utf8($file);
			$arFile = CFile::MakeFileArray($file);
		}
		if(strpos($arFile['type'], 'image/')===0)
		{
			$ext = ToLower(str_replace('image/', '', $arFile['type']));
			if(mb_substr($arFile['name'], -(mb_strlen($ext) + 1))!='.'.$ext)
			{
				if($ext!='jpeg' || (($ext='jpg') && mb_substr($arFile['name'], -(mb_strlen($ext) + 1))!='.'.$ext))
				{
					$arFile['name'] = $arFile['name'].'.'.$ext;
				}
			}
		}
		if(!empty($arDef))
		{
			$arFile = $this->PictureProcessing($arFile, $arDef);
		}
		return $arFile;
	}
	
	public function GetNewImagePath($ext)
	{
		return $this->imagedir.'image'.'_'.$this->listIndex.'_'.(++$this->stepparams['image_cnt']).'.'.ltrim($ext, '.');
	}
	
	public function GetBoolValue($val)
	{
		$trueVals = array_map('trim', explode(',', GetMessage("KDA_EE_FIELD_VAL_Y")));
		$falseVals = array_map('trim', explode(',', GetMessage("KDA_EE_FIELD_VAL_N")));
		if(in_array(ToLower($val), $trueVals))
		{
			return 'Y';
		}
		elseif(in_array(ToLower($val), $falseVals))
		{
			return 'N';
		}
		else
		{
			return false;
		}
	}
	
	public function GetIblockProperties($IBLOCK_ID)
	{
		if(!$this->props[$IBLOCK_ID])
		{
			$this->props[$IBLOCK_ID] = array();
			$dbRes = CIBlockProperty::GetList(array(), array('IBLOCK_ID'=>$IBLOCK_ID));
			while($arProp = $dbRes->Fetch())
			{
				$this->props[$IBLOCK_ID][$arProp['ID']] = $arProp;
			}
		}
		return $this->props[$IBLOCK_ID];
	}
	
	public function GetProductFieldValue($val, $relField)
	{
		if(preg_match('/^ICAT_PRICE(\d+)_(PRICE|CURRENCY)$/', $relField, $m))
		{
			if($arr = \Bitrix\Catalog\PriceTable::getList(array('filter'=>array('PRODUCT_ID'=>$val, 'CATALOG_GROUP_ID'=>$m[1])))->fetch())
			{
				return $arr[$m[2]];
			}
		}
		elseif($relField=='ICAT_MEASURE_RATIO')
		{
			$dbRes = CCatalogMeasureRatio::getList(array(), array('PRODUCT_ID' => $val), false, false, array('RATIO'));
			if($arRatio = $dbRes->Fetch())
			{
				return $arRatio['RATIO'];
			}
		}
		else
		{
			$fieldName = mb_substr($relField, 5);
			if(($arr = \Bitrix\Catalog\ProductTable::getList(array('filter'=>array('ID'=>$val)))->fetch()) && array_key_exists($fieldName, $arr))
			{
				return $arr[$fieldName];
			}
		}
		return '';
	}
	
	public function GetPropertyListValue($arProp, $val, $relField='')
	{
		if($val)
		{
			if(strlen($relField)==0 || !in_array($relField, array('VALUE', 'XML_ID', 'SORT'))) $relField = 'VALUE';
			$key = $val.'_'.$relField;
			if(!isset($this->propVals[$arProp['ID']][$key]))
			{
				$dbRes = CIBlockPropertyEnum::GetList(array(), array("PROPERTY_ID"=>$arProp['ID'], "ID"=>$val));
				if($arPropEnum = $dbRes->Fetch())
				{
					$this->propVals[$arProp['ID']][$key] = $arPropEnum[$relField];
				}
				else
				{
					$this->propVals[$arProp['ID']][$key] = '';
				}
			}
			$val = $this->propVals[$arProp['ID']][$key];
		}
		return $val;
	}
	
	public function GetPropertyElementValue($arProp, $val, $relField)
	{
		if($val)
		{
			$selectField = 'NAME';
			if($relField)
			{
				if(strpos($relField, 'IE_')===0)
				{
					$selectField = substr($relField, 3);
				}
				elseif(strpos($relField, 'IP_PROP')===0)
				{
					$selectField = 'PROPERTY_'.substr($relField, 7);
				}
				elseif(strpos($relField, 'ICAT_')===0)
				{
					$selectField = $relField;
				}
			}
			
			if(!isset($this->propVals[$arProp['ID']][$selectField][$val]))
			{
				$this->propVals[$arProp['ID']][$selectField][$val] = '';
				if(strpos($selectField, 'PROPERTY_')===0 && $arProp['LINK_IBLOCK_ID'])
				{
					$dbRes = \CIBlockElement::GetProperty($arProp['LINK_IBLOCK_ID'], $val, array(), array('ID'=>substr($selectField, 9)));
					$arVals = array();
					while($arElem = $dbRes->Fetch())
					{
						$pval = $arElem['VALUE'];
						if($arElem['PROPERTY_TYPE']=='L')
						{
							$pval = $this->GetPropertyListValue($arElem, $pval);
						}
						elseif($arElem['PROPERTY_TYPE']=='S' && $arElem['USER_TYPE']=='directory')
						{
							$pval = $this->GetHighloadBlockValue($arElem, $pval);
						}
						elseif($arElem['PROPERTY_TYPE']=='S' && $arElem['USER_TYPE']=='HTML')
						{
							$pval = $this->GetHTMLValue($arElem, $pval);
						}
						$arVals[] = $pval;
					}
					$this->propVals[$arProp['ID']][$selectField][$val] = implode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $arVals);
				}
				elseif(strpos($selectField, 'ICAT_')===0)
				{
					$this->propVals[$arProp['ID']][$selectField][$val] = $this->GetProductFieldValue($val, $selectField);
				}
				else
				{
					$dbRes = CIBlockElement::GetList(array(), array("ID"=>$val), false, false, array($selectField));
					if($arElem = $dbRes->GetNext())
					{
						$selectedField = $selectField;
						if(strpos($selectedField, 'PROPERTY_')===0) $selectedField .= '_VALUE';
						if(array_key_exists('~'.$selectedField, $arElem)) $selectedField = '~'.$selectedField;
						$this->propVals[$arProp['ID']][$selectField][$val] = $arElem[$selectedField];
					}
				}
			}
			$val = $this->propVals[$arProp['ID']][$selectField][$val];
		}
		return $val;
	}
	
	public function GetPropertySectionValue($arProp, $val, $relField)
	{
		if($val)
		{
			$selectField = 'NAME';
			if($relField)
			{
				$selectField = $relField;
			}
			if(!isset($this->propVals[$arProp['ID']][$selectField][$val]))
			{
				$arFilter = array("ID"=>$val);
				if($arProp['LINK_IBLOCK_ID']) $arFilter['IBLOCK_ID'] = $arProp['LINK_IBLOCK_ID'];
				$dbRes = CIBlockSection::GetList(array(), $arFilter, false, array($selectField));
				if($arSect = $dbRes->GetNext())
				{
					$this->propVals[$arProp['ID']][$selectField][$val] = $arSect[$selectField];
				}
				else
				{
					$this->propVals[$arProp['ID']][$selectField][$val] = '';
				}
			}
			$val = $this->propVals[$arProp['ID']][$selectField][$val];
		}
		return $val;
	}
	
	public function GetFileValue($val, $key=false, $relField='', $index='')
	{
		if($val)
		{
			if(is_numeric($val))
			{
				$arFile = CKDAExportUtils::GetFileArray($val);
				if($arFile)
				{
					if($relField) return $arFile[$relField];
					$val = $arFile['SRC'];
				}
				else
				{
					$val = '';
				}
			}
			
			if($this->params['EXPORT_FILES_IN_ARCHIVE']=='Y' && strlen($this->params['FILES_ARCHIVE_PATH']) > 0 && strlen($val) > 0 && file_exists($this->docRoot.$val))
			{
				if(is_array($key))
				{
					$key2 = false;
					foreach($key as $k)
					{
						if(!empty($this->fparamsByName[$this->listIndex][$k]['CONVERSION'])) $key2 = $k;
					}
					$key = $key2;
				}
				if($key!==false && !empty($this->fparamsByName[$this->listIndex][$key]['CONVERSION']))
				{
					$this->filesForMove[] = array('path'=>$val, 'conv'=>$this->fparamsByName[$this->listIndex][$key]['CONVERSION'], 'index'=>$index);
				}
				else
				{
					$this->PutFileToArchive($val);
				}
			}
		}
		if(strlen($val) > 0 && strpos($val, $this->docRoot)!==0)
		{
			$val = $this->AddUrlDomain($val);
		}
		return $val;
	}
	
	public function ProcessMoveFiles($arElementData)
	{
		if(empty($this->filesForMove)) return;
		$parentDir = $this->tmpdir.'tmpimages/';
		foreach($this->filesForMove as $arFile)
		{
			$newPath = $this->ApplyConversions($arFile['path'], $arFile['conv'], $arElementData, false, $arFile['index']);
			$newPath = trim(trim(preg_replace('/[\x01-\x1F'.preg_quote("\\:*?\"'<>|~#&;", "/").']+/', '', $newPath)), '/');
			if(preg_match('/^\s*https?:\/\//i', $newPath)) continue;
			$newPath = $parentDir.$newPath;
			$io = CBXVirtualIo::GetInstance();
			$io->copy($this->docRoot.$arFile['path'], $newPath);
			$this->PutFileToArchive(substr($newPath, strlen($this->docRoot)), $parentDir);
		}
		DeleteDirFilesEx(substr($parentDir, strlen($this->docRoot)));
		$this->filesForMove = array();
	}
	
	public function PutFileToArchive($val='', $removePath='')
	{
		if($this->stepparams['curstep'] != 'export') return;
		$maxSize = 1024*1024*150;
		$parentDir = $this->tmpdir.'archiveimages/';
		$this->stepparams['imgarchivesize'] = (int)$this->stepparams['imgarchivesize'];
		if(strlen($val) > 0)
		{
			$io = CBXVirtualIo::GetInstance();
			$newVal = $val;
			if(strlen($removePath) > 0) $newVal = substr($val, strlen(substr($removePath, strlen($this->docRoot))));
			$newVal = ltrim($newVal, '/');
			$io->copy($this->docRoot.$val, $parentDir.$newVal);
			$this->stepparams['imgarchivesize'] += filesize($this->docRoot.$val);
		}
		if((($this->stepparams['imgarchivesize'] > $maxSize && $this->params['EXPORT_FILES_ARCHIVE_SINGLE']!='Y') || strlen($val)==0) && file_exists($parentDir) && count(array_diff(scandir($parentDir), array('.', '..'))) > 0)
		{
			if(!isset($this->stepparams['imgarchivenumber'])) $this->stepparams['imgarchivenumber'] = 0;
			$this->stepparams['imgarchivenumber']++;
			//if(preg_match('#\s*(ftps?://)([^:]*):(.*)@(.*/.*)$#is', $this->params['FILES_ARCHIVE_PATH'], $m))
			if(preg_match('#\s*ftps?://#is', $this->params['FILES_ARCHIVE_PATH'], $m))
			{
				$fn = end(explode('/', $this->params['FILES_ARCHIVE_PATH']));
				$tempPath = \CFile::GetTempName('', $fn);
				CheckDirPath($tempPath);
				$this->CreateZipArchive($tempPath, $parentDir);
				$archivePath = $this->params['FILES_ARCHIVE_PATH'];
				if($this->stepparams['imgarchivenumber'] > 1)
				{
					$archivePath = preg_replace('/(\.[^\.]*)$/', '_'.($this->stepparams['imgarchivenumber'] - 1).'$1', $archivePath);
				}
				$this->sftp->Upload($archivePath, $tempPath);
			}
			else
			{
				$zipFile = '';
				$suffix = 0;
				$archivePath = $this->docRoot. preg_replace('/\.zip\s*$/U', '', '/'.ltrim($this->params['FILES_ARCHIVE_PATH'], '/'));
				while(strlen($zipFile)==0 || file_exists($zipFile))
				{
					$zipFile = $archivePath.($suffix > 0 ? '_'.$suffix : '').'.zip';
					$suffix++;
				}
				$this->CreateZipArchive($zipFile, $parentDir);
			}
			
			$this->stepparams['imgarchivesize'] = 0;
			DeleteDirFilesEx(substr($parentDir, strlen($this->docRoot)));
		}
	}
	
	public function CreateZipArchive($zipFile, $parentDir)
	{
		if(class_exists('RecursiveIteratorIterator') && class_exists('ZipArchive') && ($zipObj = new ZipArchive()) && $zipObj->open($zipFile, ZipArchive::CREATE)===true)
		{
			if(is_dir($parentDir))
			{
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($parentDir), RecursiveIteratorIterator::SELF_FIRST);
				foreach($files as $file){
					if(in_array(substr($file, strrpos($file, '/')+1), array('.', '..')))continue;
					if (is_dir($file) === true){
						$zipObj->addEmptyDir(str_replace($parentDir, '', $file.'/'));
					}else if (is_file($file) === true){
						//$zipObj->addFromString(str_replace($parentDir, '', $file), file_get_contents($file));
						$zipObj->addFile($file, str_replace($parentDir, '', $file));
					}
				}
			}
			else
			{
				//$zipObj->addFromString(bx_basename($parentDir), file_get_contents($parentDir));
				$zipObj->addFile($parentDir, bx_basename($parentDir));
			}
			$zipObj->close();
		}
		else
		{
			$zipObj = \CBXArchive::GetArchive($zipFile, 'ZIP');
			if(is_dir($parentDir))
			{
				$zipObj->Add($parentDir, array("add_path" => false, "remove_path" => $parentDir));
			}
			else
			{
				$zipObj->Add($parentDir, array("add_path" => false, "remove_path" => dirname($parentDir)));
			}
		}
	}
	
	public function GetFileDescription($val)
	{
		if($val)
		{
			$arFile = CKDAExportUtils::GetFileArray($val);
			if($arFile)
			{
				$val = $arFile['DESCRIPTION'];
			}
			else
			{
				$val = '';
			}
		}
		return $val;
	}
	
	public function GetHighloadBlockValue($arProp, $val, $relField = '')
	{
		if($val && CModule::IncludeModule('highloadblock') && $arProp['USER_TYPE_SETTINGS']['TABLE_NAME'])
		{
			if(strlen($relField)==0) $relField = 'UF_NAME';
			if(!isset($this->propVals[$arProp['ID']][$relField][$val]))
			{
				if(!$this->hlbl[$arProp['ID']] && ($hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('TABLE_NAME'=>$arProp['USER_TYPE_SETTINGS']['TABLE_NAME'])))->fetch()))
				{
					$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
					$this->hlbl[$arProp['ID']] = $entity->getDataClass();
					
					$dbRes = \CUserTypeEntity::GetList(array(), array('ENTITY_ID'=>'HLBLOCK_'.$hlblock['ID'], 'LANG'=>LANGUAGE_ID));
					$this->hlblFields[$arProp['ID']] = array();
					while($arHLField = $dbRes->Fetch())
					{
						$this->hlblFields[$arProp['ID']][] = $arHLField['FIELD_NAME'];
					}
				}
				
				$this->propVals[$arProp['ID']][$relField][$val] = '';
				if(isset($this->hlblFields[$arProp['ID']]) && in_array($relField, $this->hlblFields[$arProp['ID']]) && in_array("UF_XML_ID", $this->hlblFields[$arProp['ID']]))
				{
					$entityDataClass = $this->hlbl[$arProp['ID']];
					$dbRes2 = $entityDataClass::GetList(array('filter'=>array("UF_XML_ID"=>$val), 'select'=>array('ID', $relField), 'limit'=>1));
					if($arr2 = $dbRes2->Fetch())
					{
						$this->propVals[$arProp['ID']][$relField][$val] = $arr2[$relField];
					}
				}
			}
			return $this->propVals[$arProp['ID']][$relField][$val];
		}
		return $val;
	}
	
	public function GetHTMLValue($arProp, $val)
	{
		if(isset($val['TEXT'])) return $val['TEXT'];
		else return $val;
	}
	
	public function PictureProcessing($arFile, $arDef)
	{
		if($arDef["SCALE"] === "Y")
		{
			$arNewPicture = CIBlock::ResizePicture($arFile, $arDef);
			if(is_array($arNewPicture))
			{
				$arFile = $arNewPicture;
			}
			/*elseif($arDef["IGNORE_ERRORS"] !== "Y")
			{
				unset($arFile);
				$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
			}*/
		}

		if($arDef["USE_WATERMARK_FILE"] === "Y")
		{
			CIBLock::FilterPicture($arFile["tmp_name"], array(
				"name" => "watermark",
				"position" => $arDef["WATERMARK_FILE_POSITION"],
				"type" => "file",
				"size" => "real",
				"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
				"file" => $this->docRoot.Rel2Abs("/", $arDef["WATERMARK_FILE"]),
			));
		}

		if($arDef["USE_WATERMARK_TEXT"] === "Y")
		{
			CIBLock::FilterPicture($arFile["tmp_name"], array(
				"name" => "watermark",
				"position" => $arDef["WATERMARK_TEXT_POSITION"],
				"type" => "text",
				"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
				"text" => $arDef["WATERMARK_TEXT"],
				"font" => $this->docRoot.Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
				"color" => $arDef["WATERMARK_TEXT_COLOR"],
			));
		}
		return $arFile;
	}
	
	public function GetIblockSite($IBLOCK_ID, $one=false)
	{
		if(!isset($this->arIblockSites)) $this->arIblockSites = array();
		if(!$this->arIblockSites[$IBLOCK_ID])
		{
			/*$dbRes = CIBlock::GetList(array(), array('ID'=>$IBLOCK_ID));
			$arIblock = $dbRes->Fetch();
			$this->arIblockSites[$IBLOCK_ID] = $arIblock['LID'];*/
			$arSiteList = array();
			$rsIBlockSites = CIBlock::GetSite($IBLOCK_ID);
			while ($arIBlockSite = $rsIBlockSites->Fetch())
			{
				$arSiteList[] = $arIBlockSite['SITE_ID'];
			}
			if(count($arSiteList)==0) $arSiteList[] = '';
			$this->arIblockSites[$IBLOCK_ID] = $arSiteList;
		}
		if($one) return $this->arIblockSites[$IBLOCK_ID][0];
		else return $this->arIblockSites[$IBLOCK_ID];
	}
	
	public function AddUrlDomain($url, $IBLOCK_ID=0, $forceAdd=false)
	{
		if($this->params['EXPORT_ADD_DOMAIN']!='Y' && !$forceAdd) return $url;
		
		$domain = trim(rtrim($this->params['EXPORT_ADD_DOMAIN_VALUE'], '/'));
		if(strlen($domain)==0)
		{
			if($IBLOCK_ID===0 && isset($this->iblockId) && $this->iblockId > 0) $IBLOCK_ID = $this->iblockId;
			if($IBLOCK_ID > 0 && ($host = $this->GetIblockDomain($IBLOCK_ID)))
			{
				if(!isset($this->domainProtocol))
				{
					if(defined("BX_CRONTAB") && BX_CRONTAB===true) $this->domainProtocol = 'https';
					else
					{
						$obRequest = \Bitrix\Main\Context::getCurrent()->getRequest();
						$requestUri = trim($obRequest->getRequestUri());
						$this->domainProtocol = ($obRequest->isHttps() ? 'https' : 'http');
					}
				}
				$domain = $this->domainProtocol.'://'.$host;
			}
		}
		
		$url = trim($url);
		if(strlen($url) > 0 && strlen($domain) > 0 && strpos($url, $domain)!==0) $url = $domain.'/'.ltrim($url, '/');
		return $url;
	}
	
	public function RemoveExtraDomain($val)
	{
		if($val===false) return $val;
		if($this->params['EXPORT_ADD_DOMAIN']=='Y')
		{
			if(($host = trim(preg_replace('#(https?://#', '', rtrim($this->params['EXPORT_ADD_DOMAIN_VALUE'], '/')))) || (isset($this->iblockId) && $this->iblockId > 0 && ($host = $this->GetIblockDomain($IBLOCK_ID))))
			{
				$val = preg_replace('#(https?://'.preg_quote($host, '#').'/?)https?://'.preg_quote($host, '#').'#i', '$1', $val);
			}
		}
		return $val;
		
	}
	
	public function GetIblockDomain($IBLOCK_ID)
	{
		if(!isset($this->arIblockDomains)) $this->arIblockDomains = array();
		if(!$this->arIblockDomains[$IBLOCK_ID])
		{
			$host = '';
			$find = false;
			$rsIBlockSites = CIBlock::GetSite($IBLOCK_ID);
			while($arIBlockSite = $rsIBlockSites->Fetch())
			{
				if($arIBlockSite['SERVER_NAME'] && (!$host || ($arIBlockSite['SERVER_NAME']==$_SERVER['HTTP_HOST'] && ($find = true)) || (!$find && $arIBlockSite['DEF']=='Y')))
				{
					$host = $arIBlockSite['SERVER_NAME'];
				}
			}
			//if(!$host && $_SERVER['HTTP_HOST']) $host = $_SERVER['HTTP_HOST'];
			if(!$host)
			{
				if($newHost = $this->params['EXPORT_ADD_DOMAIN_VALUE']) $host = $newHost;
				elseif($newHost = COption::GetOptionString('main', 'server_name')) $host = $newHost;
			}
			$this->arIblockDomains[$IBLOCK_ID] = $host;
		}
		return $this->arIblockDomains[$IBLOCK_ID];
	}
	
	public function ConversionReplaceValues($m)
	{
		$value = '';
		$paramName = $m[0];
		$quot = "'";
		$isVar = false;
		if(preg_match('/^\$\{([\'"])(.*)[\'"]\}?$/', $paramName, $m2))
		{
			$quot = $m2[1];
			$paramName = $m2[2];
			$isVar = true;
		}
		
		$k = substr($paramName, 1, -1);		
		if(1 || isset($this->currentItemValues[$k]))
		{
			$value = $this->GetValueForConversion($this->currentItemValues[$k], $k);
			if(is_array($value)) $value = implode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $value);
			if(preg_match('/^(OFFER_)?(PURCHASING_PRICE|ICAT_PRICE\d+_PRICE(_DISCOUNT)?)$/', $this->currentFieldName)
				&& preg_match('/^(OFFER_)?(PURCHASING_PRICE|ICAT_PRICE\d+_PRICE(_DISCOUNT)?)$/', $k))
			{
				$currKey = preg_replace('/_PRICE(_DISCOUNT)?$/', '_CURRENCY', $k);
				$pkey = 0;
				if(preg_match('/ICAT_PRICE(\d+)_/', $k, $m2)) $pkey = $m2[1];
				$value = $this->GetConvertedPrice($value, $this->currentItemValues[$currKey], $this->currentFieldName, array(), $pkey);
			}
			if(preg_match('/^(OFFER_)?ICAT_PRICE(\d+)_PRICE_DISCOUNT$/', $this->currentFieldName)
				&& preg_match('/^(OFFER_)?ICAT_PRICE(\d+)_PRICE_DISCOUNT$/', $k))
			{
				$arSettings = $this->currentFieldSettings;
				if(is_array($arSettings) && isset($arSettings['USER_GROUP']) && is_array($arSettings['USER_GROUP']) && !empty($arSettings['USER_GROUP']))
				{
					list($ugKey, $userGroup) = $this->GetUserGroupData($arSettings['USER_GROUP']);
					if(isset($this->currentItemValues[$k.'__'.$ugKey]))
					{
						$value = $this->currentItemValues[$k.'__'.$ugKey];
					}
				}
			}
		}

		if($isVar)
		{
			$this->extraConvParams[$paramName] = $value;
			return '$this->extraConvParams['.$quot.$paramName.$quot.']';
		}
		else return $value;
	}
	
	public function ApplyConversions($val, $arConv, $arItem, $field=false, $valueIndex='', $iblockFields=array())
	{
		if($val===false) $val = '';
		$this->curCellStyle = array();
		$this->curRowStyle = array();
		$fieldName = $fieldKey = false;
		if(!is_array($field))
		{
			$fieldName = $field;
		}
		else
		{
			if($field['NAME']) $fieldName = $field['NAME'];
			if($field['KEY']) $fieldKey = $field['KEY'];
		}
		
		if(is_array($arConv))
		{
			$execConv = false;
			$this->currentItemValues = $arItem;
			if(strlen($valueIndex) > 0) $this->currentItemValues['VALUE_INDEX'] = (int)$valueIndex + 1;
			$prefixPattern = '/(\$\{[\'"])?(#[A-Za-z0-9\_|=]+#)([\'"]\})?/';
			foreach($arConv as $k=>$v)
			{
				$condVal = $val;
				if(strlen($v['CELL']) > 0 && !in_array($v['CELL'], array('ELSE')))
				{
					$condVal = $this->GetValueForConversion((array_key_exists($v['CELL'], $arItem) ? $arItem[$v['CELL']] : ''), $v['CELL']);
				}
				if(is_array($condVal)) $condVal = implode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $condVal);
				if(strlen($v['FROM']) > 0) $v['FROM'] = preg_replace_callback($prefixPattern, array($this, 'ConversionReplaceValues'), $v['FROM']);
				if($v['CELL']=='ELSE') $v['WHEN'] = '';
				$condValNum = $this->GetFloatVal($condVal);
				$fromNum = $this->GetFloatVal($v['FROM']);
				if(($v['CELL']=='ELSE' && !$execConv)
					|| ($v['WHEN']=='EQ' && ($condVal==$v['FROM'] && strlen($condVal)==strlen($v['FROM'])))
					|| ($v['WHEN']=='NEQ' && ($condVal!=$v['FROM'] || strlen($condVal)!=strlen($v['FROM'])))
					|| ($v['WHEN']=='GT' && $condValNum > $fromNum)
					|| ($v['WHEN']=='LT' && $condValNum < $fromNum)
					|| ($v['WHEN']=='GEQ' && $condValNum >= $fromNum)
					|| ($v['WHEN']=='LEQ' && $condValNum <= $fromNum)
					|| ($v['WHEN']=='BETWEEN' && $condValNum >= $this->GetFloatVal(explode('-', $v['FROM'])[0]) && $condValNum <= $this->GetFloatVal(explode('-', $v['FROM'])[1]))
					|| ($v['WHEN']=='CONTAIN' && strpos($condVal, $v['FROM'])!==false)
					|| ($v['WHEN']=='NOT_CONTAIN' && strpos($condVal, $v['FROM'])===false)
					|| ($v['WHEN']=='REGEXP' && preg_match('/'.ToLower($v['FROM']).'/is'.CKDAExportUtils::getUtfModifier(), ToLower($condVal)))
					|| ($v['WHEN']=='NOT_REGEXP' && !preg_match('/'.ToLower($v['FROM']).'/is'.CKDAExportUtils::getUtfModifier(), ToLower($condVal)))
					|| ($v['WHEN']=='EMPTY' && strlen($condVal)==0)
					|| ($v['WHEN']=='NOT_EMPTY' && strlen($condVal) > 0)
					|| ($v['WHEN']=='ANY'))
				{
					$this->currentFieldKey = $fieldKey;
					$this->currentFieldName = $fieldName;
					if(strlen($v['TO']) > 0) $v['TO'] = preg_replace_callback($prefixPattern, array($this, 'ConversionReplaceValues'), $v['TO']);
					if($v['THEN']=='REPLACE_TO')
					{
						if($v['WHEN']=='REGEXP') $val = $this->GetConvReplaceRegexp($val, $v['FROM'], $v['TO'], false);
						else $val = $v['TO'];
					}
					elseif($v['THEN']=='REMOVE_SUBSTRING' && strlen($v['TO']) > 0) $val = str_replace($v['TO'], '', $val);
					elseif($v['THEN']=='REPLACE_SUBSTRING_TO' && strlen($v['FROM']) > 0)
					{
						if($v['WHEN']=='REGEXP') $val = $this->GetConvReplaceRegexp($val, $v['FROM'], $v['TO']);
						else $val = str_replace($v['FROM'], $v['TO'], $val);
					}
					elseif($v['THEN']=='ADD_TO_BEGIN') $val = $v['TO'].$val;
					elseif($v['THEN']=='ADD_TO_END') $val = $val.$v['TO'];
					elseif($v['THEN']=='MATH_ROUND') $val = round($this->GetFloatVal($val), $this->GetFloatVal($v['TO']));
					elseif($v['THEN']=='MATH_MULTIPLY') $val = $this->GetFloatRoundVal($this->GetFloatVal($val) * $this->GetFloatVal($v['TO']));
					elseif($v['THEN']=='MATH_DIVIDE') $val = ($this->GetFloatVal($v['TO'])==0 ? 0 : $this->GetFloatRoundVal($this->GetFloatVal($val) / $this->GetFloatVal($v['TO'])));
					elseif($v['THEN']=='MATH_ADD') $val = $this->GetFloatRoundVal($this->GetFloatVal($val) + $this->GetFloatVal($v['TO']));
					elseif($v['THEN']=='MATH_SUBTRACT') $val = $this->GetFloatRoundVal($this->GetFloatVal($val) - $this->GetFloatVal($v['TO']));
					elseif($v['THEN']=='SKIP_LINE') $val = false;
					elseif($v['THEN']=='EXPRESSION') $val = $this->ExecuteFilterExpression($val, $v['TO'], '');
					elseif($v['THEN']=='STRIP_TAGS') $val = strip_tags($val);
					elseif($v['THEN']=='CLEAR_TAGS') $val = preg_replace('/<([a-z][a-z0-9:]*)[^>]*(\/?)>/i','<$1$2>', $val);
					elseif($v['THEN']=='ADD_LINK') $val = '<a class="kda-ee-conversion-link" href="'.$v['TO'].'">'.$val.'</a>';
					elseif($v['THEN']=='TRANSLIT')
					{
						$arParams = array();
						if($fieldName && !empty($iblockFields))
						{
							$paramName = '';
							if($fieldName=='IE_CODE') $paramName = 'CODE';
							if(preg_match('/^ISECT\d+_CODE$/', $fieldName)) $paramName = 'SECTION_CODE';
							if($paramName && $iblockFields[$paramName]['DEFAULT_VALUE']['TRANSLITERATION']=='Y')
							{
								$arParams = $iblockFields[$paramName]['DEFAULT_VALUE'];
							}
						}
						$val = $this->Str2Url($val, $arParams);
					}
					elseif($v['THEN']=='SET_BG_COLOR')
					{
						$this->curCellStyle['BACKGROUND_COLOR'] = $v['TO'];
					}
					elseif($v['THEN']=='SET_TEXT_COLOR')
					{
						$this->curCellStyle['FONT_COLOR'] = $v['TO'];
					}
					elseif($v['THEN']=='SET_BG_COLOR_STR')
					{
						$this->curRowStyle['BACKGROUND_COLOR'] = $v['TO'];
					}
					$execConv = true;
				}
			}
		}
		$val = $this->RemoveExtraDomain($val);
		return $val;
	}
	
	public function GetConvReplaceRegexp($val, $from, $to, $substr = true)
	{
		$mod = \CKDAExportUtils::getUtfModifier();
		if($substr===false)
		{
			if(!preg_match('/[\\\$]\d/', $to)) return $to;
			if(preg_match('/'.$from.'/i'.$mod, $val, $m)) $val = $m[0];
			elseif(preg_match('/'.ToLower($from).'/i'.$mod, $val, $m)) $val = $m[0];
			else return $to;
		}
		if(preg_match('/'.$from.'/i'.$mod, $val)) $val = preg_replace('/'.$from.'/i'.$mod, $to, $val);
		else $val = preg_replace('/'.ToLower($from).'/i'.$mod, $to, $val);
		return $val;
	}
	
	public function GetValueForConversion($val, $key='')
	{
		if(strlen($key) > 0 && isset($this->currentItemValues['CONV|'.$key]))
		{
			$key = 'CONV|'.$key;
			$val = $this->currentItemValues[$key];
		}
		if(is_array($val) && array_key_exists('TYPE', $val))
		{
			unset($val['TYPE']);
			if(count($val) > 0)
			{
				reset($val);
				return current($val);
			}
			else return '';
		}
		elseif(strpos($key, 'PRICE')!==false && isset($this->currentItemValues[$key.'_ORIG']))
		{
			$val = $this->currentItemValues[$key.'_ORIG'];
		}
		return $val;
	}
	
	public function GetExportData($listIndex, $limit=10, $page=1, $sectionKey=1)
	{
		$this->listIndex = $listIndex;
		$this->sectionKey = $sectionKey;
		if(isset($this->stepparams['string_lengths']) && !isset($this->stepparams['string_lengths'][$listIndex]))
		{
			$this->stepparams['string_lengths'][$listIndex] = array();
		}

		$iblockId = $this->iblockId = $this->GetIblockIdByListIndex($listIndex);
		$boolSKU = false;
		if($arCatalog = CKDAExportUtils::GetOfferIblock($iblockId, true))
		{
			$offersIblockId = $arCatalog['OFFERS_IBLOCK_ID'];
			$offersPropId = $arCatalog['OFFERS_PROPERTY_ID'];
			$boolSKU = true;
		}
		
		if(!isset($this->filters)) $this->filters = array();
		if(!isset($this->skuFilters)) $this->skuFilters = array();
		if(!isset($this->sectionFilters)) $this->sectionFilters = array();
		if(!isset($this->filters[$listIndex]))
		{
			$arFilter = array(
				'IBLOCK_ID' => $iblockId
			);
			if($this->params['EXPORT_SEP_SECTIONS']=='Y' && $this->params['USE_NEW_FILTER']!='Y')
			{
				$arFilter['!SECTION_ID'] = false;
				$arFilter['INCLUDE_SUBSECTIONS'] = 'N';
			}
			
			$arSkuFilter = array();
			if($boolSKU) $arSkuFilter = array("IBLOCK_ID" => $offersIblockId);
			
			$arSectionFilter = null;
			
			if($this->params['USE_NEW_FILTER']=='Y')
			{
				/*new filter*/
				$arSectionFilter = array(
					'IBLOCK_ID' => $iblockId
				);
				if($this->params['SFILTER'][$listIndex])
				{
					$eFilter = new CKDAEEFilter($iblockId);
					$eFilter->SetSectionFilter($arSectionFilter, $this->params['SFILTER'][$listIndex]);
					if(count($arSectionFilter) > 1)
					{
						if(!isset($arFilter['SECTION_ID'])) $arFilter['SECTION_ID'] = array();
						elseif(!is_array($arFilter['SECTION_ID'])) $arFilter['SECTION_ID'] = array($arFilter['SECTION_ID']);
						$dbResSections = \CIblockSection::GetList(array(), $arSectionFilter, false, array('ID'));
						while($arr = $dbResSections->Fetch())
						{
							if(!in_array($arr['ID'], $arFilter['SECTION_ID'])) $arFilter['SECTION_ID'][] = $arr['ID'];
						}
						if(count($arFilter['SECTION_ID'])==0) $arFilter['SECTION_ID'] = -1;
					}
				}
					
				if($this->params['EFILTER'][$listIndex])
				{
					$eFilter = new CKDAEEFilter($iblockId);
					$eFilter->SetFilter($arFilter, $this->params['EFILTER'][$listIndex]);
				}
				if($boolSKU && $this->params['OFILTER'][$listIndex])
				{
					$oFilter = new CKDAEEFilter($offersIblockId);
					$oFilter->SetFilter($arSkuFilter, $this->params['OFILTER'][$listIndex], true);
					if(count($arSkuFilter) > 1 && !preg_grep('/^=?CATALOG_TYPE/', array_keys($arFilter)))
					{
						if(is_callable(array('\CCatalogAdminTools', 'getIblockProductTypeList')))
						{
							$arTypes = \CCatalogAdminTools::getIblockProductTypeList($iblockId, true);
							$arFilter['=CATALOG_TYPE'] = array_keys($arTypes);
						}
						else
						{
							$arFilter['=CATALOG_TYPE'] = array(1,2,3,4,5,6,7);
						}
					}
				}
				/*/new filter*/
			}
			elseif($this->params['FILTER'][$listIndex])
			{
				if(!isset($this->filterProps)) $this->filterProps = array();
				if(!isset($this->filterProps[$iblockId]))
				{
					$dbrFProps = CIBlockProperty::GetList(
						array(
							"SORT"=>"ASC",
							"NAME"=>"ASC"
						),
						array(
							"IBLOCK_ID"=>$iblockId,
							"CHECK_PERMISSIONS"=>"N",
						)
					);
					
					$arProps = array();
					while ($arProp = $dbrFProps->GetNext())
					{
						if ($arProp["ACTIVE"] == "Y")
						{
							$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
							$arProps[] = $arProp;
						}
					}
					$this->filterProps[$iblockId] = $arProps;
				}
				else
				{
					$arProps = $this->filterProps[$iblockId];
				}
				
				if($boolSKU)
				{
					if(!isset($this->filterProps[$offersIblockId]))
					{
						$dbrFProps = CIBlockProperty::GetList(
							array(
								"SORT"=>"ASC",
								"NAME"=>"ASC"
							),
							array(
								"IBLOCK_ID"=>$offersIblockId,
								"CHECK_PERMISSIONS"=>"N",
							)
						);
						
						$arSKUProps = array();
						while ($arProp = $dbrFProps->GetNext())
						{
							if ($arProp["ACTIVE"] == "Y")
							{
								$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
								$arSKUProps[] = $arProp;
							}
						}
						$this->filterProps[$offersIblockId] = $arSKUProps;
					}
					else
					{
						$arSKUProps = $this->filterProps[$offersIblockId];
					}
				}
				
				$arAddFilter = $this->params['FILTER'][$listIndex];
				
				if ($boolSKU)
				{
					if(is_array($arProductIds)) $arSkuFilter['ID'] = $arProductIds;
					if(!empty($arAddFilter['find_sub_el_id_start'])) $arSkuFilter[">=ID"] = $arAddFilter['find_sub_el_id_start'];
					if(!empty($arAddFilter['find_sub_el_id_end'])) $arSkuFilter["<=ID"] = $arAddFilter['find_sub_el_id_end'];
					if(strlen($arAddFilter['find_sub_el_active']) > 0) $arSkuFilter['ACTIVE'] = $arAddFilter['find_sub_el_active'];
					if(strlen(trim($arAddFilter['find_sub_el_sort'])) > 0)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_sort'], $arAddFilter['find_sub_el_sort_comp']);
						$arSkuFilter[$op.'SORT'] = $arAddFilter['find_sub_el_sort'];
					}
					$this->AddDateFilter($arSkuFilter, $arAddFilter, 'DATE_MODIFY_FROM', 'DATE_MODIFY_TO', 'find_sub_el_timestamp');
					
					if(strlen($arAddFilter['find_sub_el_catalog_quantity']) > 0)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_quantity'], $arAddFilter['find_sub_el_catalog_quantity_comp']);
						$arSkuFilter[$op.'CATALOG_QUANTITY'] = $arAddFilter['find_sub_el_catalog_quantity'];
					}
					if(strlen($arAddFilter['find_sub_el_catalog_purchasing_price']) > 0)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_purchasing_price'], $arAddFilter['find_sub_el_catalog_purchasing_price_comp']);
						$arSkuFilter[$op.'CATALOG_PURCHASING_PRICE'] = $arAddFilter['find_sub_el_catalog_purchasing_price'];
					}
					if(strlen($arAddFilter['find_sub_el_catalog_weight']) > 0)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_weight'], $arAddFilter['find_sub_el_catalog_weight_comp']);
						$arSkuFilter[$op.'WEIGHT'] = $arAddFilter['find_sub_el_catalog_weight'];
					}
					if(strlen($arAddFilter['find_sub_el_catalog_length']) > 0 || strpos($arAddFilter['find_sub_el_catalog_length_comp'], 'empty')!==false)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_length'], $arAddFilter['find_sub_el_catalog_length_comp']);
						$arSkuFilter[$op.'LENGTH'] = $arAddFilter['find_sub_el_catalog_length'];
					}
					if(strlen($arAddFilter['find_sub_el_catalog_width']) > 0 || strpos($arAddFilter['find_sub_el_catalog_width_comp'], 'empty')!==false)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_width'], $arAddFilter['find_sub_el_catalog_width_comp']);
						$arSkuFilter[$op.'WIDTH'] = $arAddFilter['find_sub_el_catalog_width'];
					}
					if(strlen($arAddFilter['find_sub_el_catalog_height']) > 0 || strpos($arAddFilter['find_sub_el_catalog_height_comp'], 'empty')!==false)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_height'], $arAddFilter['find_sub_el_catalog_height_comp']);
						$arSkuFilter[$op.'HEIGHT'] = $arAddFilter['find_sub_el_catalog_height'];
					}
					if (strlen($arAddFilter['find_sub_el_catalog_vat_included']) > 0)
					{
						$arSkuFilter['VAT_INCLUDED'] = $arAddFilter['find_sub_el_catalog_vat_included'];
					}
					
					$arStoreKeys = preg_grep('/^find_sub_el_catalog_store\d+_/', array_keys($arAddFilter));
					$arStoreKeys = array_unique(array_map(array(__CLASS__, 'ReplaceSubCatalogStore'), $arStoreKeys));
					if(!empty($arStoreKeys))
					{
						foreach($arStoreKeys as $storeKey)
						{
							if(strlen($arAddFilter['find_sub_el_catalog_store'.$storeKey.'_quantity']) > 0)
							{
								$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_store'.$storeKey.'_quantity'], $arAddFilter['find_sub_el_catalog_store'.$storeKey.'_quantity_comp']);
								$arSkuFilter[$op.'CATALOG_STORE_AMOUNT_'.$storeKey] = $arAddFilter['find_sub_el_catalog_store'.$storeKey.'_quantity'];
							}
						}
					}
					
					if(strlen($arAddFilter['find_sub_el_catalog_store_any_quantity']) > 0 && is_array($arAddFilter['find_sub_el_catalog_store_any_quantity_stores']) && count($arAddFilter['find_sub_el_catalog_store_any_quantity_stores']) > 0)
					{
						$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_store_any_quantity'], $arAddFilter['find_sub_el_catalog_store_any_quantity_comp']);
						$arFilterItem = array('LOGIC'=>'OR');
						foreach($arAddFilter['find_sub_el_catalog_store_any_quantity_stores'] as $storeKey)
						{
							$arFilterItem[] = array($op.'CATALOG_STORE_AMOUNT_'.$storeKey=>$arAddFilter['find_sub_el_catalog_store_any_quantity']);
						}
						$arSkuFilter[] = $arFilterItem;
					}
					
					$arPriceKeys = preg_grep('/^find_sub_el_catalog_price_\d+$/', array_keys($arAddFilter));
					$arPriceKeys = array_unique(array_map(array(__CLASS__, 'ReplaceSubCatalogPrice'), $arPriceKeys));
					if(!empty($arPriceKeys))
					{
						foreach($arPriceKeys as $priceKey)
						{
							if(strlen($arAddFilter['find_sub_el_catalog_price_'.$priceKey]) > 0
								|| $arAddFilter['find_sub_el_catalog_price_'.$priceKey.'_comp']=='empty')
							{
								$op = $this->GetNumberOperation($arAddFilter['find_sub_el_catalog_price_'.$priceKey], $arAddFilter['find_sub_el_catalog_price_'.$priceKey.'_comp']);
								$arSkuFilter[$op.'CATALOG_PRICE_'.$priceKey] = $arAddFilter['find_sub_el_catalog_price_'.$priceKey];
							}
						}
					}
					
					if(isset($arSKUProps) && is_array($arSKUProps))
					{
						foreach ($arSKUProps as $arProp)
						{
							if ('Y' == $arProp["FILTRABLE"] && 'F' != $arProp["PROPERTY_TYPE"])
							{
								if(is_array($arAddFilter["find_sub_el_property_".$arProp["ID"]]) && isset($arAddFilter["find_sub_el_property_".$arProp["ID"]]['TYPE'])) $arAddFilter["find_sub_el_property_".$arProp["ID"]] = '';
								if($arProp["PROPERTY_TYPE"]=='S' && in_array($arProp['USER_TYPE'], array('Date', 'DateTime')))
								{
									$this->AddDateFilter($arSkuFilter, $arAddFilter, '>=PROPERTY_'.$arProp["ID"], '<=PROPERTY_'.$arProp["ID"], "find_sub_el_property_".$arProp["ID"], true);
								}
								elseif (!empty($arProp['PROPERTY_USER_TYPE']) && isset($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"]))
								{
									$fieldName = "filter_".$listIndex."_find_sub_el_property_".$arProp["ID"];
									if($arProp["USER_TYPE"]=='DateTime' && $_REQUEST[$fieldName.'_to'] && \CIBlock::isShortDate($_REQUEST[$fieldName.'_to']))
									{
										$timeFormat = \Bitrix\Main\Type\Date::convertFormatToPhp(\CSite::getTimeFormat());
										$_REQUEST[$fieldName.'_to'] .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
									}
									$GLOBALS[$fieldName] = $arAddFilter["find_sub_el_property_".$arProp["ID"]];
									$GLOBALS['set_filter'] = 'Y';
									call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
										$arProp,
										array("VALUE" => $fieldName),
										&$arSkuFilter,
										&$filtered,
									));
								}
								else
								{
									$value = $arAddFilter["find_sub_el_property_".$arProp["ID"]];
									$valueComp = $arAddFilter["find_sub_el_property_".$arProp["ID"]."_comp"];
									if(is_array($value)) $value = array_diff(array_map('trim', $value), array(''));
									if((is_array($value) && count($value)>0) || (!is_array($value) && strlen($value))|| strpos($valueComp, 'empty')!==false)
									{
										if(is_array($value))
										{
											foreach($value as $k=>$v)
											{
												if($v === "NOT_REF") $value[$k] = false;
											}
										}
										elseif($value === "NOT_REF") $value = false;
										if($arProp["PROPERTY_TYPE"]=='E' && $arProp["USER_TYPE"]=='')
										{
											$value = trim($value);
											if(preg_match('/[,;\s\|]/', $value))
											{
												$arSkuFilter[] = array(
													'LOGIC'=>'OR', 
													array("PROPERTY_".$arProp["ID"] => array_diff(array_map('trim', preg_split('/[,;\s\|]/', $value)), array(''))), 
													array("PROPERTY_".$arProp["ID"].".NAME" => array_diff(array_map('trim', preg_split('/[,;\|]/', $value)), array('')))
												);
											}
											else 
											{
												$arSkuFilter[] = array(
													'LOGIC'=>'OR', 
													array("PROPERTY_".$arProp["ID"] => $value), 
													array("PROPERTY_".$arProp["ID"].".NAME" => $value)
												);
											}
										}
										elseif($arProp["PROPERTY_TYPE"]=='N' && $arProp["USER_TYPE"]=='')
										{
											$value = trim($value);
											$op = $this->GetNumberOperation($value, $arAddFilter["find_sub_el_property_".$arProp["ID"]."_comp"]);
											$arSkuFilter[$op.'PROPERTY_'.$arProp["ID"]] = $value;
										}
										else
										{
											$op = $this->GetStringOperation($value, $arAddFilter["find_sub_el_property_".$arProp["ID"]."_comp"]);
											$arSkuFilter[$op."PROPERTY_".$arProp["ID"]] = $value;
										}
									}
								}
							}
						}
					}
				}
				
				$arProductIds = false;
				if($this->bSale && class_exists('\Bitrix\Sale\Internals\BasketTable'))
				{
					$arOrderFilter = array();
					$this->AddDateFilter($arOrderFilter, $arAddFilter, '>=ORDER.DATE_INSERT', '<=ORDER.DATE_INSERT', 'find_el_sale_order_date_insert');
					if(strlen($arAddFilter['find_el_sale_order']) > 0)
					{
						$arOrderIds = array_diff(preg_split('/\D+/', $arAddFilter['find_el_sale_order']), array(''));
						if(!empty($arOrderIds)) $arOrderFilter['ORDER_ID'] = $arOrderIds;
					}
					if(!empty($arOrderFilter))
					{
						$arProductIds = array(0);
						$this->arFilterOrders = $arOrderFilter;
						$dbRes = \Bitrix\Sale\Internals\BasketTable::GetList(array('filter'=>$arOrderFilter, 'group'=>array('PRODUCT_ID'), 'select'=>array('PRODUCT_ID')));
						while($arr = $dbRes->Fetch())
						{
							$arProductIds[] = $arr['PRODUCT_ID'];
						}
						if(!empty($arProductIds))
						{
							if(isset($offersIblockId) && $offersIblockId > 0)
							{
								$arSkuFilter['ID'] = $arProductIds;
								$dbRes = \CiblockElement::GetList(array(), array('IBLOCK_ID'=>$offersIblockId, 'ID'=>$arProductIds), false, false, array('PROPERTY_'.$offersPropId));
								while($arr = $dbRes->Fetch())
								{
									$propKey = 'PROPERTY_'.$offersPropId.'_VALUE';
									if(isset($arr[$propKey]) && $arr[$propKey] > 0 && !in_array($arr[$propKey], $arProductIds)) $arProductIds[] = $arr[$propKey];
								}
							}
							$arFilter['ID'] = $arProductIds;
						}
					}
				}
				
				if(is_array($arAddFilter['find_section_section']) && count(array_diff($arAddFilter['find_section_section'], array('','-1'))) > 0) 
					$arFilter['SECTION_ID'] = array_diff($arAddFilter['find_section_section'], array('', '-1'));
				elseif(!is_array($arAddFilter['find_section_section']) && strlen($arAddFilter['find_section_section']) > 0 && (int)$arAddFilter['find_section_section'] >= 0) 
					$arFilter['SECTION_ID'] = $arAddFilter['find_section_section'];
				if($arAddFilter['find_el_subsections']=='Y')
				{
					if($arFilter['SECTION_ID']==0) unset($arFilter["SECTION_ID"]);
					else $arFilter["INCLUDE_SUBSECTIONS"] = "Y";
				}
				if(strlen($arAddFilter['find_el_modified_user_id']) > 0) $arFilter['MODIFIED_USER_ID'] = $arAddFilter['find_el_modified_user_id'];
				if(strlen($arAddFilter['find_el_modified_by']) > 0) $arFilter['MODIFIED_BY'] = $arAddFilter['find_el_modified_by'];
				if(strlen($arAddFilter['find_el_created_user_id']) > 0) $arFilter['CREATED_USER_ID'] = $arAddFilter['find_el_created_user_id'];
				if(strlen($arAddFilter['find_el_active']) > 0) $arFilter['ACTIVE'] = $arAddFilter['find_el_active'];
				if(strlen(trim($arAddFilter['find_el_sort'])) > 0)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_sort'], $arAddFilter['find_el_sort_comp']);
					$arFilter[$op.'SORT'] = $arAddFilter['find_el_sort'];
				}
				if(strlen($arAddFilter['find_el_code']) > 0) $arFilter['?CODE'] = $arAddFilter['find_el_code'];
				if(strlen($arAddFilter['find_el_external_id']) > 0) $arFilter['EXTERNAL_ID'] = $arAddFilter['find_el_external_id'];
				if(strlen($arAddFilter['find_el_tags']) > 0) $arFilter['?TAGS'] = $arAddFilter['find_el_tags'];
				if(strlen($arAddFilter['find_el_name']) > 0) $arFilter['?NAME'] = $arAddFilter['find_el_name'];
				if(strlen($arAddFilter['find_el_vtype_pretext']) > 0)
				{
					if($arAddFilter['find_el_vtype_pretext']=='empty') $arFilter['PREVIEW_TEXT'] = false;
					elseif($arAddFilter['find_el_vtype_pretext']=='not_empty') $arFilter['!PREVIEW_TEXT'] = false;
				}
				elseif(strlen($arAddFilter['find_el_pretext']) > 0) $arFilter['?PREVIEW_TEXT'] = $arAddFilter['find_el_pretext'];
				if(strlen($arAddFilter['find_el_vtype_intext']) > 0)
				{
					if($arAddFilter['find_el_vtype_intext']=='empty') $arFilter['DETAIL_TEXT'] = false;
					elseif($arAddFilter['find_el_vtype_intext']=='not_empty') $arFilter['!DETAIL_TEXT'] = false;
				}
				elseif(strlen($arAddFilter['find_el_intext']) > 0) $arFilter['?DETAIL_TEXT'] = $arAddFilter['find_el_intext'];
				if($arAddFilter['find_el_preview_picture']=='Y') $arFilter['!PREVIEW_PICTURE'] =  false;
				elseif($arAddFilter['find_el_preview_picture']=='N') $arFilter['PREVIEW_PICTURE'] =  false;
				if($arAddFilter['find_el_detail_picture']=='Y') $arFilter['!DETAIL_PICTURE'] =  false;
				elseif($arAddFilter['find_el_detail_picture']=='N') $arFilter['DETAIL_PICTURE'] =  false;
				
				if(!empty($arAddFilter['find_el_id_start'])) $arFilter[">=ID"] = $arAddFilter['find_el_id_start'];
				if(!empty($arAddFilter['find_el_id_end'])) $arFilter["<=ID"] = $arAddFilter['find_el_id_end'];
				$this->AddDateFilter($arFilter, $arAddFilter, 'DATE_MODIFY_FROM', 'DATE_MODIFY_TO', 'find_el_timestamp');
				$this->AddDateFilter($arFilter, $arAddFilter, '>=DATE_CREATE', '<=DATE_CREATE', 'find_el_created');
				if(!empty($arAddFilter['find_el_created_by']) && strlen($arAddFilter['find_el_created_by'])>0) $arFilter["CREATED_BY"] = $arAddFilter['find_el_created_by'];
				if($arAddFilter['find_el_vtype_active_from']=='empty') $arFilter["DATE_ACTIVE_FROM"] = false;
				elseif($arAddFilter['find_el_vtype_active_from']=='not_empty') $arFilter["!DATE_ACTIVE_FROM"] = false;
				else
				{
					if(!empty($arAddFilter['find_el_date_active_from_from'])) $arFilter[">=DATE_ACTIVE_FROM"] = $arAddFilter['find_el_date_active_from_from'];
					if(!empty($arAddFilter['find_el_date_active_from_to'])) $arFilter["<=DATE_ACTIVE_FROM"] = $arAddFilter['find_el_date_active_from_to'];
				}
				if($arAddFilter['find_el_vtype_date_active_to']=='empty') $arFilter["DATE_ACTIVE_TO"] = false;
				elseif($arAddFilter['find_el_vtype_date_active_to']=='not_empty') $arFilter["!DATE_ACTIVE_TO"] = false;
				else
				{
					if(!empty($arAddFilter['find_el_date_active_to_from'])) $arFilter[">=DATE_ACTIVE_TO"] = $arAddFilter['find_el_date_active_to_from'];
					if(!empty($arAddFilter['find_el_date_active_to_to'])) $arFilter["<=DATE_ACTIVE_TO"] = $arAddFilter['find_el_date_active_to_to'];
				}
				if(!empty($arAddFilter['find_el_catalog_type']))
				{
					$cTypes = $arAddFilter['find_el_catalog_type'];
					if(is_array($cTypes)) $cTypes = array_diff($cTypes, array(''));
					if(!empty($cTypes)) $arFilter['CATALOG_TYPE'] = $cTypes;
				}
				if (!empty($arAddFilter['find_el_catalog_available'])) $arFilter['CATALOG_AVAILABLE'] = $arAddFilter['find_el_catalog_available'];
				if (!empty($arAddFilter['find_el_catalog_bundle'])) $arFilter['CATALOG_BUNDLE'] = $arAddFilter['find_el_catalog_bundle'];
				if (strlen($arAddFilter['find_el_catalog_quantity']) > 0)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_quantity'], $arAddFilter['find_el_catalog_quantity_comp']);
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array($op.'CATALOG_QUANTITY'=>$arAddFilter['find_el_catalog_quantity'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter[$op.'CATALOG_QUANTITY'] = $arAddFilter['find_el_catalog_quantity'];
					}
				}
				if (strlen($arAddFilter['find_el_catalog_purchasing_price']) > 0)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_purchasing_price'], $arAddFilter['find_el_catalog_purchasing_price_comp']);
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array($op.'CATALOG_PURCHASING_PRICE'=>$arAddFilter['find_el_catalog_purchasing_price'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter[$op.'CATALOG_PURCHASING_PRICE'] = $arAddFilter['find_el_catalog_purchasing_price'];
					}
				}
				if (strlen($arAddFilter['find_el_catalog_weight']) > 0)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_weight'], $arAddFilter['find_el_catalog_weight_comp']);
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array($op.'WEIGHT'=>$arAddFilter['find_el_catalog_weight'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter[$op.'WEIGHT'] = $arAddFilter['find_el_catalog_weight'];
					}
				}
				if (strlen($arAddFilter['find_el_catalog_length']) > 0 || strpos($arAddFilter['find_el_catalog_length_comp'], 'empty')!==false)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_length'], $arAddFilter['find_el_catalog_length_comp']);
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array($op.'LENGTH'=>$arAddFilter['find_el_catalog_length'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter[$op.'LENGTH'] = $arAddFilter['find_el_catalog_length'];
					}
				}
				if (strlen($arAddFilter['find_el_catalog_width']) > 0 || strpos($arAddFilter['find_el_catalog_width_comp'], 'empty')!==false)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_width'], $arAddFilter['find_el_catalog_width_comp']);
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array($op.'WIDTH'=>$arAddFilter['find_el_catalog_width'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter[$op.'WIDTH'] = $arAddFilter['find_el_catalog_width'];
					}
				}
				if (strlen($arAddFilter['find_el_catalog_height']) > 0 || strpos($arAddFilter['find_el_catalog_height_comp'], 'empty')!==false)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_height'], $arAddFilter['find_el_catalog_height_comp']);
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array($op.'HEIGHT'=>$arAddFilter['find_el_catalog_height'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter[$op.'HEIGHT'] = $arAddFilter['find_el_catalog_height'];
					}
				}
				if (strlen($arAddFilter['find_el_catalog_vat_included']) > 0)
				{
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilter[] = array('LOGIC'=>'OR',
							array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
							array('VAT_INCLUDED'=>$arAddFilter['find_el_catalog_vat_included'])
						);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					else
					{
						$arFilter['VAT_INCLUDED'] = $arAddFilter['find_el_catalog_vat_included'];
					}
				}
				
				$arStoreKeys = preg_grep('/^find_el_catalog_store\d+_/', array_keys($arAddFilter));
				$arStoreKeys = array_unique(array_map(array(__CLASS__, 'ReplaceCatalogStore'), $arStoreKeys));
				if(!empty($arStoreKeys))
				{
					foreach($arStoreKeys as $storeKey)
					{
						if(strlen($arAddFilter['find_el_catalog_store'.$storeKey.'_quantity']) > 0)
						{
							$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_store'.$storeKey.'_quantity'], $arAddFilter['find_el_catalog_store'.$storeKey.'_quantity_comp']);
							if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
							{
								$arFilter[] = array('LOGIC'=>'OR',
									array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
									array($op.'CATALOG_STORE_AMOUNT_'.$storeKey=>$arAddFilter['find_el_catalog_store'.$storeKey.'_quantity'])
								);
								if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
							}
							else
							{
								$arFilter[$op.'CATALOG_STORE_AMOUNT_'.$storeKey] = $arAddFilter['find_el_catalog_store'.$storeKey.'_quantity'];
							}
						}
					}
				}
				
				if(strlen($arAddFilter['find_el_catalog_store_any_quantity']) > 0 && is_array($arAddFilter['find_el_catalog_store_any_quantity_stores']) && count($arAddFilter['find_el_catalog_store_any_quantity_stores']) > 0)
				{
					$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_store_any_quantity'], $arAddFilter['find_el_catalog_store_any_quantity_comp']);
					$arFilterItem = array('LOGIC'=>'OR');
					if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
					{
						$arFilterItem[] = array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU);
						if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
					}
					foreach($arAddFilter['find_el_catalog_store_any_quantity_stores'] as $storeKey)
					{
						$arFilterItem[] = array($op.'CATALOG_STORE_AMOUNT_'.$storeKey=>$arAddFilter['find_el_catalog_store_any_quantity']);
					}
					$arFilter[] = $arFilterItem;
				}
				
				$arPriceKeys = preg_grep('/^find_el_catalog_price_\d+$/', array_keys($arAddFilter));
				$arPriceKeys = array_unique(array_map(array(__CLASS__, 'ReplaceCatalogPrice'), $arPriceKeys));
				if(!empty($arPriceKeys))
				{
					foreach($arPriceKeys as $priceKey)
					{
						if(strlen($arAddFilter['find_el_catalog_price_'.$priceKey]) > 0
							|| $arAddFilter['find_el_catalog_price_'.$priceKey.'_comp']=='empty')
						{
							$op = $this->GetNumberOperation($arAddFilter['find_el_catalog_price_'.$priceKey], $arAddFilter['find_el_catalog_price_'.$priceKey.'_comp']);
							if(count($arSkuFilter) > 1 && defined('\Bitrix\Catalog\ProductTable::TYPE_SKU'))
							{
								$arFilter[] = array('LOGIC'=>'OR',
									array('CATALOG_TYPE'=>\Bitrix\Catalog\ProductTable::TYPE_SKU),
									array($op.'CATALOG_PRICE_'.$priceKey=>$arAddFilter['find_el_catalog_price_'.$priceKey])
								);
								if(!isset($arFilter['CATALOG_TYPE'])) $arFilter['CATALOG_TYPE'] = array(1,2,3);
							}
							else
							{
								$arFilter[$op.'CATALOG_PRICE_'.$priceKey] = $arAddFilter['find_el_catalog_price_'.$priceKey];
							}
						}
					}
				}
				
				foreach ($arProps as $arProp)
				{
					if ($arProp["FILTRABLE"]=='Y' || $arProp["PROPERTY_TYPE"]=='F')
					{
						if(is_array($arAddFilter["find_el_property_".$arProp["ID"]]) && isset($arAddFilter["find_el_property_".$arProp["ID"]]['TYPE'])) $arAddFilter["find_el_property_".$arProp["ID"]] = '';
						if($arProp["PROPERTY_TYPE"]=='S' && in_array($arProp['USER_TYPE'], array('Date', 'DateTime')))
						{
							$this->AddDateFilter($arFilter, $arAddFilter, '>=PROPERTY_'.$arProp["ID"], '<=PROPERTY_'.$arProp["ID"], "find_el_property_".$arProp["ID"], true);
						}
						elseif(!empty($arProp['PROPERTY_USER_TYPE']) && isset($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"]))
						{
							$fieldName = "filter_".$listIndex."_find_el_property_".$arProp["ID"];
							if($arProp["USER_TYPE"]=='DateTime' && $_REQUEST[$fieldName.'_to'] && \CIBlock::isShortDate($_REQUEST[$fieldName.'_to']))
							{
								$timeFormat = \Bitrix\Main\Type\Date::convertFormatToPhp(\CSite::getTimeFormat());
								$_REQUEST[$fieldName.'_to'] .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
							}
							$GLOBALS[$fieldName] = $arAddFilter["find_el_property_".$arProp["ID"]];
							$GLOBALS['set_filter'] = 'Y';
							call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
								$arProp,
								array("VALUE" => $fieldName),
								&$arFilter,
								&$filtered,
							));
						}
						else
						{							
							$value = $arAddFilter["find_el_property_".$arProp["ID"]];
							$valueComp = $arAddFilter["find_el_property_".$arProp["ID"]."_comp"];
							if(is_array($value)) $value = array_diff(array_map('trim', $value), array(''));
							if((is_array($value) && count($value)>0) || (!is_array($value) && strlen($value)) || strpos($valueComp, 'empty')!==false)
							{
								if(is_array($value))
								{
									foreach($value as $k=>$v)
									{
										if($v === "NOT_REF") $value[$k] = false;
									}
								}
								elseif($value === "NOT_REF") $value = false;
								if($arProp["PROPERTY_TYPE"]=='E' && $arProp["USER_TYPE"]=='')
								{
									$value = trim($value);
									if(preg_match('/[,;\s\|]/', $value))
									{
										$arFilter[] = array(
											'LOGIC'=>'OR', 
											array("PROPERTY_".$arProp["ID"] => array_diff(array_map('trim', preg_split('/[,;\s\|]/', $value)), array(''))), 
											array("PROPERTY_".$arProp["ID"].".NAME" => array_diff(array_map('trim', preg_split('/[,;\|]/', $value)), array('')))
										);
									}
									else 
									{
										$arFilter[] = array(
											'LOGIC'=>'OR', 
											array("PROPERTY_".$arProp["ID"] => $value), 
											array("PROPERTY_".$arProp["ID"].".NAME" => $value)
										);
									}
								}
								elseif($arProp["PROPERTY_TYPE"]=='N' && $arProp["USER_TYPE"]=='')
								{
									$value = trim($value);
									$op = $this->GetNumberOperation($value, $arAddFilter["find_el_property_".$arProp["ID"]."_comp"]);
									$arFilter[$op.'PROPERTY_'.$arProp["ID"]] = $value;
								}
								elseif($arProp["PROPERTY_TYPE"]=='F')
								{
									if($arAddFilter['find_el_property_'.$arProp["ID"]]=='Y') $arFilter['!PROPERTY_'.$arProp["ID"]] =  false;
									elseif($arAddFilter['find_el_property_'.$arProp["ID"]]=='N') $arFilter['PROPERTY_'.$arProp["ID"]] =  false;
								}
								else
								{
									$op = $this->GetStringOperation($value, $arAddFilter["find_el_property_".$arProp["ID"]."_comp"]);
									$arFilter[$op."PROPERTY_".$arProp["ID"]] = $value;
								}
							}
						}
					}
				}
			}

			if(isset($this->stepparams['ADDFILTER']['SECTION_ID']) && strlen($this->stepparams['ADDFILTER']['SECTION_ID']) > 0)
			{
				$arFilter['SECTION_ID'] = $this->stepparams['ADDFILTER']['SECTION_ID'];
				$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
			}
			elseif(isset($this->stepparams['ADDFILTER']['SECTION_CODE']) && strlen($this->stepparams['ADDFILTER']['SECTION_CODE']) > 0)
			{
				$arFilter['SECTION_CODE'] = $this->stepparams['ADDFILTER']['SECTION_CODE'];
				$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
				if(array_key_exists('SECTION_ID', $arFilter)) unset($arFilter['SECTION_ID']);
			}
			$arStepAddFilter = $this->stepparams['ADDFILTER'];
			if(is_array($arStepAddFilter))
			{
				unset($arStepAddFilter['SECTION_ID'], $arStepAddFilter['SECTION_CODE']);
				if(count($arStepAddFilter) > 0) $arFilter = array_merge($arFilter, $arStepAddFilter);
			}
			
			foreach(GetModuleEvents(static::$moduleId, "OnBeforeSaveFilter", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array(&$arFilter, &$arSkuFilter, $this->pid, $listIndex));
			}
			$this->filters[$listIndex] = $arFilter;
			$this->skuFilters[$listIndex] = $arSkuFilter;
			$this->sectionFilters[$listIndex] = $arSectionFilter;
		}
		else
		{
			$arFilter = $this->filters[$listIndex];
			$arSkuFilter = $this->skuFilters[$listIndex];
			$arSectionFilter = $this->sectionFilters[$listIndex];
		}

		$arFields = $this->GetFieldList($listIndex);		
	
		$this->customFieldSettings = array();
		$this->arPricesGroup = array();
		$this->arPropListProps = array();
		$this->useExtPrice = false;
		$this->arConvFields = array();
		$arFieldsAdded = array();
		if(is_array($this->fparams[$listIndex]))
		{
			foreach($this->fparams[$listIndex] as $fieldIndex=>$arSettings)
			{
				if(!is_array($arSettings)) $arSettings = array();
				$field = $arFields[$fieldIndex];
				$this->customFieldSettings[$field] = $arSettings;
				if($field=='IP_LIST_PROPS' || $field=='OFFER_IP_LIST_PROPS')
				{
					if(is_array($arSettings['PROPLIST_PROPS_LIST']))
					{
						foreach($arSettings['PROPLIST_PROPS_LIST'] as $k=>$v)
						{
							$v = (int)$v;
							if($v > 0 && !in_array($v, $this->arPropListProps)) $this->arPropListProps[] = $v;
						}
					}
				}
				$this->CheckDiscountGroup($field, $arSettings);
				if(preg_match('/^(OFFER_)?ICAT_PRICE(\d+)_PRICE$/', $field, $m))
				{
					if(isset($arSettings['PRICE_USE_EXT']) && $arSettings['PRICE_USE_EXT']=='Y')
					{
						$this->useExtPrice = true;
					}
				}
				if(isset($arSettings['REL_ELEMENT_FIELD']) && strlen($arSettings['REL_ELEMENT_FIELD']) > 0)
				{
					$fa = $arFields[$fieldIndex].'|'.$arSettings['REL_ELEMENT_FIELD'];
					if(!in_array($fa, $arFieldsAdded)) $arFieldsAdded[] = $fa;
				}
				if(isset($arSettings['REL_SECTION_FIELD']) && strlen($arSettings['REL_SECTION_FIELD']) > 0)
				{
					$fa = $arFields[$fieldIndex].'|'.$arSettings['REL_SECTION_FIELD'];
					if(!in_array($fa, $arFieldsAdded)) $arFieldsAdded[] = $fa;
				}
				if(isset($arSettings['REL_DIRECTORY_FIELD']) && strlen($arSettings['REL_DIRECTORY_FIELD']) > 0)
				{
					$fa = $arFields[$fieldIndex].'|'.$arSettings['REL_DIRECTORY_FIELD'];
					if(!in_array($fa, $arFieldsAdded)) $arFieldsAdded[] = $fa;
				}
				if(isset($arSettings['REL_PROPLIST_FIELD']) && strlen($arSettings['REL_PROPLIST_FIELD']) > 0)
				{
					$fa = $arFields[$fieldIndex].'|'.$arSettings['REL_PROPLIST_FIELD'];
					if(!in_array($fa, $arFieldsAdded)) $arFieldsAdded[] = $fa;
				}
				if(isset($arSettings['REL_USER_FIELD']) && strlen($arSettings['REL_USER_FIELD']) > 0)
				{
					$fa = $arFields[$fieldIndex].'|'.$arSettings['REL_USER_FIELD'];
					if(!in_array($fa, $arFieldsAdded)) $arFieldsAdded[] = $fa;
				}
				if(isset($arSettings['BARCODE_FIELD']) && strlen($arSettings['BARCODE_FIELD']) > 0)
				{
					$fa = (strpos($field, 'OFFER_')===0 ? 'OFFER_' : '').$arSettings['BARCODE_FIELD'];
					if(!in_array($fa, $arFieldsAdded)) $arFieldsAdded[] = $fa;
				}
				if(isset($arSettings['CONVERSION']) && is_array($arSettings['CONVERSION']) && $field)
				{
					foreach($arSettings['CONVERSION'] as $k=>$v)
					{
						$arKeys = array();
						if(preg_match_all('/#([A-Za-z0-9\_\|=]+)#/', $v['FROM'], $m)) $arKeys = array_merge($arKeys, $m[1]);
						if(preg_match_all('/#([A-Za-z0-9\_\|=]+)#/', $v['TO'], $m)) $arKeys = array_merge($arKeys, $m[1]);
						if($v['CELL'] && !in_array($v['CELL'], array('ELSE'))) $arKeys[] = $v['CELL'];
						foreach($arKeys as $key)
						{
							if(!in_array($key, $arFields) && !in_array($key, $arFieldsAdded))
							{
								$arFieldsAdded[] = $key;
								$this->CheckDiscountGroup($key, $arSettings);
							}
							if(!in_array($key, $this->arConvFields))
							{
								$this->arConvFields[] = $key;
							}
						}
					}
				}
			}
		}
		
		$arAllFields = array_merge($arFields, $arFieldsAdded);

		$bOnlySections = $bOnlyCTable = true;
		$arCurField = current($arAllFields);
		while(($bOnlySections || $bOnlyCTable) && $arCurField!==false)
		{
			if(!preg_match('/^ISECT(\d+)?_/', $arCurField) && $arCurField!='IE_SECTION_PATH' && $arCurField!='')
			{
				$bOnlySections = false;
			}
			if(!preg_match('/^CT_/', $arCurField) && $arCurField!='')
			{
				$bOnlyCTable = false;
			}
			$arCurField = next($arAllFields);
		}
		
		$arOfferFields = array();
		foreach($arAllFields as $k=>$v)
		{
			if(strpos($v, 'OFFER_')===0)
			{
				$arOfferFields[$k] = substr($v, 6);
			}
		}
		
		$arNavParams = false;
		if(is_numeric($limit) && $limit > 0)
		{
			if(!empty($arOfferFields) && $limit > $this->maxReadRowsWOffers)
			{
				$limit = $this->maxReadRowsWOffers;
			}
			if($page==0)
			{
				$arNavParams = array('nTopCount' => (int)$limit);
			}
			else
			{
				$arNavParams = array(
					'nPageSize' => (int)$limit,
					'iNumPage' => $page
				);
			}
		}
		
		$typeParam = 'ELEMENT';
		if($bOnlySections) $typeParam = 'SECTION';
		if($bOnlyCTable) $typeParam = 'CONTENT_TABLE';
		
		$arData = array();
		$arParams = array(
			'FILTER' => $arFilter,
			'SKU_FILTER' => $arSkuFilter,
			'SECTION_FILTER' => $arSectionFilter,
			'NAV_PARAMS' => $arNavParams,
			'FIELDS' => $arAllFields,
			'TYPE' => $typeParam,
			'SECTION_KEY' => $sectionKey
		);
		$arResElements = $this->GetElementsData($arData, $arParams);

		$arMultiRows = array();
		foreach($arData as $k=>$arElementData)
		{
			$skipLine = false;
			//more data in temp file
			/*foreach($arFields as $fname)
			{
				if(!isset($arElementData[$fname])) $arData[$k][$fname] = $arElementData[$fname] = '';
			}*/
			
			$arFieldSettings = array();
			if(is_array($this->fparams[$listIndex]))
			{
				foreach($this->fparams[$listIndex] as $fieldIndex=>$arSettings)
				{
					if(!is_array($arSettings)) $arSettings = array();
					if(!empty($arSettings))
					{
						$fname = $arFields[$fieldIndex];
						if(!isset($arElementData[$fname])) $arData[$k][$fname] = $arElementData[$fname] = '';
					}
					$field = $arFields[$fieldIndex];
					$arFieldSettings[$field] = $arSettings;
					$arFieldSettings[$field.'_'.$fieldIndex] = $arSettings;
					if($field=='IP_LIST_PROPS' || $field=='OFFER_IP_LIST_PROPS')
					{
						if($field=='OFFER_IP_LIST_PROPS')
						{
							$fieldPrefix = 'OFFER_';
							$arIblockProps = $this->GetIblockProperties($offersIblockId);
						}
						else
						{
							$fieldPrefix = '';
							$arIblockProps = $this->GetIblockProperties($iblockId);
						}
						$plFullVal = '';
						if(is_array($arSettings['PROPLIST_PROPS_LIST']))
						{
							$sep1 = $this->GetSeparator($arSettings['PROPLIST_PROPS_SEP_VALS']);
							if(strlen(trim($sep1))==0) $sep1 = "\r\n";
							$sep2 = $arSettings['PROPLIST_PROPS_SEP_NAMEVAL'];
							if(strlen(trim($sep2))==0) $sep2 = ": ";
							$showEmpty = (bool)($arSettings['PROPLIST_PROPS_SHOW_EMPTY']=='Y');
							foreach($arSettings['PROPLIST_PROPS_LIST'] as $plKey)
							{
								$plVal = $arElementData[$fieldPrefix.'IP_PROP'.$plKey];
								if(is_array($plVal)) $plVal = implode(', ', $plVal);
								$plVal = trim($plVal);
								if(!$showEmpty && strlen($plVal)==0) continue;
								$plFullVal .= (strlen($plFullVal) > 0 ? $sep1 : '').$arIblockProps[$plKey]['NAME'].$sep2.$plVal;
							}
						}
						$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $plFullVal;
					}
					if(strpos($field, 'PRICE')!==false && isset($arElementData[$field.'_ORIG']))
					{
						if(isset($arSettings['PRICE_USE_EXT']) && $arSettings['PRICE_USE_EXT']=='Y')
						{
							$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$arSettings['PRICE_QUANTITY_FROM'].'_'.$arSettings['PRICE_QUANTITY_TO']];
						}
						if(isset($arSettings['PRICE_CONVERT_CURRENCY']) && $arSettings['PRICE_CONVERT_CURRENCY']=='Y' && $arSettings['PRICE_CONVERT_CURRENCY_TO']!=$this->GetCFSettings($field, 'PRICE_CONVERT_CURRENCY_TO'))
						{
							$currencyField = preg_replace('/_PRICE(_|$)/', '_CURRENCY$1', $field);
							if(isset($arElementData[$currencyField]))
							{
								$pkey = 0;
								if(preg_match('/ICAT_PRICE(\d+)_/', $field, $m2)) $pkey = $m2[1];
								$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $this->GetConvertedPrice($arElementData[$field.'_ORIG'], $arElementData[$currencyField], $arSettings, array(), $pkey);
							}
						}
					}
					if(preg_match('/^(OFFER_)?ICAT_PRICE(\d+)_PRICE_DISCOUNT$/', $field, $m))
					{
						if(isset($arSettings['USER_GROUP']) && is_array($arSettings['USER_GROUP']) && !empty($arSettings['USER_GROUP']))
						{
							list($ugKey, $userGroup) = $this->GetUserGroupData($arSettings['USER_GROUP']);
							if(isset($arElementData[$field.'__'.$ugKey]))
							{
								$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $arElementData[$field.'__'.$ugKey];
							}
						}
					}
					if(isset($arSettings['REL_ELEMENT_FIELD']) || isset($arSettings['REL_SECTION_FIELD']) || isset($arSettings['REL_DIRECTORY_FIELD']) || isset($arSettings['REL_PROPLIST_FIELD']) || isset($arSettings['REL_USER_FIELD']))
					{
						if(isset($arSettings['REL_ELEMENT_FIELD'])) $fieldKey = $field.'|'.$arSettings['REL_ELEMENT_FIELD'];
						elseif(isset($arSettings['REL_SECTION_FIELD'])) $fieldKey = $field.'|'.$arSettings['REL_SECTION_FIELD'];
						elseif(isset($arSettings['REL_DIRECTORY_FIELD'])) $fieldKey = $field.'|'.$arSettings['REL_DIRECTORY_FIELD'];
						elseif(isset($arSettings['REL_PROPLIST_FIELD'])) $fieldKey = $field.'|'.$arSettings['REL_PROPLIST_FIELD'];
						elseif(isset($arSettings['REL_USER_FIELD'])) $fieldKey = $field.'|'.$arSettings['REL_USER_FIELD'];
						if(isset($arElementData[$fieldKey]) && is_array($arElementData[$fieldKey]))
						{
							foreach($arElementData[$fieldKey] as $k2=>$val)
							{
								$arData[$k][$field.'_'.$fieldIndex][$k2] = $arElementData[$field.'_'.$fieldIndex][$k2] = $val;
							}
						}
						elseif(array_key_exists($fieldKey, $arElementData))
						{
							$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $arElementData[$fieldKey];
						}
					}
					if(isset($arSettings['CONVERSION']) && is_array($arSettings['CONVERSION']) && $field && isset($arElementData[$field]))
					{
						$this->currentFieldSettings = $arSettings;
						$fieldVal = (isset($arElementData[$field.'_'.$fieldIndex]) ? $arElementData[$field.'_'.$fieldIndex] : $arElementData[$field]);
						if(is_array($fieldVal))
						{
							$isMulty = (bool)array_key_exists('TYPE', $fieldVal);
							foreach($fieldVal as $k2=>$val)
							{
								if($isMulty)
								{
									if($k2==='TYPE')
									{
										$arData[$k][$field.'_'.$fieldIndex][$k2] = $arElementData[$field.'_'.$fieldIndex][$k2] = $val;
									}
									else
									{
										$arElementData2 = $arElementData;
										foreach($arElementData2 as $k3=>$v3)
										{
											if(is_array($v3) && array_key_exists('TYPE', $v3) && array_key_exists($k2, $v3)) $arElementData2[$k3] = $v3[$k2];
										}
										$arData[$k][$field.'_'.$fieldIndex][$k2] = $arElementData[$field.'_'.$fieldIndex][$k2] = $newVal = $this->ApplyConversions($val, $arSettings['CONVERSION'], $arElementData2, $field, $k2);
										if($newVal===false) $skipLine = true;
										if(!empty($this->curCellStyle))
										{
											if(!isset($arData[$k]['CELLSTYLE_'.$fieldIndex])) $arData[$k]['CELLSTYLE_'.$fieldIndex] = array();
											$arData[$k]['CELLSTYLE_'.$fieldIndex][$k2] = array_merge(is_array($arData[$k]['CELLSTYLE_'.$fieldIndex][$k2]) ? $arData[$k]['CELLSTYLE_'.$fieldIndex][$k2] : array(), $this->curCellStyle);
										}
									}
									continue;
								}
								$arData[$k][$field.'_'.$fieldIndex][$k2] = $arElementData[$field.'_'.$fieldIndex][$k2] = $newVal = $this->ApplyConversions($val, $arSettings['CONVERSION'], $arElementData, $field, $k2);
								if($newVal===false)
								{
									if(isset($arSettings['MULTIPLE_SEPARATE_BY_ROWS']) && $arSettings['MULTIPLE_SEPARATE_BY_ROWS']=='Y')
									{
										unset($arData[$k][$field.'_'.$fieldIndex][$k2]);
									}
									else $skipLine = true;
								}
								if(!empty($this->curCellStyle))
								{
									$arData[$k]['CELLSTYLE_'.$fieldIndex] = array_merge(is_array($arData[$k]['CELLSTYLE_'.$fieldIndex]) ? $arData[$k]['CELLSTYLE_'.$fieldIndex] : array(), $this->curCellStyle);
								}
							}
						}
						else
						{
							$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $newVal = $this->ApplyConversions($fieldVal, $arSettings['CONVERSION'], $arElementData, $field);
							if($newVal===false) $skipLine = true;
							if(!empty($this->curCellStyle)) $arData[$k]['CELLSTYLE_'.$fieldIndex] = $this->curCellStyle;
							if(!empty($this->curRowStyle))
							{
								if(!isset($arData[$k]['CELLSTYLE_ROW'])) $arData[$k]['CELLSTYLE_ROW'] = $this->curRowStyle;
								else $arData[$k]['CELLSTYLE_ROW'] = array_merge($arData[$k]['CELLSTYLE_ROW'], $this->curRowStyle);
							}
						}
						if(is_array($arElementData[$field.'_'.$fieldIndex]) && isset($arElementData[$field.'_'.$fieldIndex]['TYPE']) && $arElementData[$field.'_'.$fieldIndex]['TYPE']=='MULTIROW')
						{
							$arMultiRows[$field.'_'.$fieldIndex] = $field.'_'.$fieldIndex;
						}
					}
					
					$isMultiple = (bool)($field && ((isset($arData[$k][$field]) && is_array($arData[$k][$field])) || (isset($arData[$k][$field.'_'.$fieldIndex]) && is_array($arData[$k][$field.'_'.$fieldIndex]))));
					if($isMultiple)
					{
						$fromValue = (isset($arSettings['MULTIPLE_FROM_VALUE']) ? $arSettings['MULTIPLE_FROM_VALUE'] : '');
						$toValue = (isset($arSettings['MULTIPLE_TO_VALUE']) ? $arSettings['MULTIPLE_TO_VALUE'] : '');
						if(strlen($fromValue) > 0 || strlen($toValue) > 0)
						{
							if(isset($arData[$k][$field.'_'.$fieldIndex]))
							{
								$arVals = $arData[$k][$field.'_'.$fieldIndex];
								if(!is_array($arVals)) $arVals = array($arVals);
							}
							else
							{
								$arVals = $arData[$k][$field];
							}
							
							if(is_numeric($fromValue) || is_numeric($toValue))
							{
								$from = (is_numeric($fromValue) ? ((int)$fromValue >= 0 ? ((int)$fromValue - 1) : (int)$fromValue) : 0);
								$to = (is_numeric($toValue) ? ((int)$toValue >= 0 ? ((int)$toValue - max(0, $from)) : (int)$toValue) : 0);
								if($to!=0) $arVals = array_slice($arVals, $from, $to);
								else $arVals = array_slice($arVals, $from);
							}
							elseif(strpos($fromValue, ',')!=false)
							{
								$arIndexes = array_diff(array_map('intval', explode(',', $fromValue)), array('0'));
								if(count($arIndexes) > 0)
								{
									$arNewVals = array();
									foreach($arVals as $k1=>$v1)
									{
										if(in_array($k1+1, $arIndexes)) $arNewVals[] = $v1;
									}
									$arVals = $arNewVals;
								}
							}
							$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $arVals;
						}
					}
					
					if(isset($arSettings['INSERT_PICTURE']) && $arSettings['INSERT_PICTURE']=='Y' && $this->imagedir && $this->IsPictureField($field) && in_array($this->params['FILE_EXTENSION'], array('xlsx', 'xlsm', 'xls', 'pdf')))
					{
						if(isset($arData[$k][$field.'_'.$fieldIndex]))
						{
							$arVals = $arData[$k][$field.'_'.$fieldIndex];
						}
						else
						{
							$arVals = $arData[$k][$field];
						}
						$arData[$k][$field.'_'.$fieldIndex.'_ORIG'] = $arVals;
						if(!is_array($arVals)) $arVals = array($arVals);
						foreach($arVals as $key=>$val)
						{
							$maxHeight = 0;
							if($key==='TYPE' || strlen(trim($val))==0) continue;
							if(strpos($field, 'QR_CODE_IMAGE')!==false)
							{
								if(!class_exists('\QRcode')) require_once(dirname(__FILE__).'/../../lib/phpqrcode/qrlib.php');
								$qrSize = (int)$arSettings['QRCODE_SIZE'];
								$qrpath = $this->GetNewImagePath('png');
								\QRcode::png($val, $qrpath, QR_ECLEVEL_H, $qrSize, 4);
								$val = $this->GetFileValue($qrpath, $field);
							}
							$before = $after = '';
							if(preg_match('/(<a[^>]+class="kda\-ee\-conversion\-link"[^>]*>)(.*)(<\/a>)/Uis', $val, $m))
							{
								$before = $m[1];
								$val = $m[2];
								$after = $m[3];
							}
							$arFile = $this->GetFileArray($val);
							if($arFile['tmp_name'])
							{
								$maxWidth = ((int)$arSettings['PICTURE_WIDTH'] > 0 ? (int)$arSettings['PICTURE_WIDTH'] : 100);
								$maxHeight = ((int)$arSettings['PICTURE_HEIGHT'] > 0 ? (int)$arSettings['PICTURE_HEIGHT'] : 100);
								$filePath = $arFile['tmp_name'];
								
								$loop = 0;
								while(!CFile::ResizeImage($arFile, array("width" => $maxWidth, "height" => $maxHeight)) && $loop < 10)
								{
									usleep(1000);
									$loop++;
								}
								
								if(file_exists($arFile['tmp_name']))
								{
									list($iwidth, $iheight, $itype, $iattr) = getimagesize($arFile['tmp_name']);
									$arData[$k][$field.'_'.$fieldIndex.'_MAXHEIGHT'] = $maxHeight = max($maxHeight, (int)$iheight);
								}

								if($filePath != $arFile['tmp_name'])
								{
									copy($arFile['tmp_name'], $filePath);
								}
								$arVals[$key] = $before.substr($filePath, strlen($this->imagedir)).$after;
							}
							else
							{
								$arVals[$key] = '';
							}
						}
						$arVals = array_diff($arVals, array(''));
						if(count($arVals) > 1)
						{
							$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $arVals;
						}
						else
						{
							$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = implode('', $arVals);
						}
					}

					if($isMultiple)
					{
						if(isset($arData[$k][$field.'_'.$fieldIndex]))
						{
							$arVals = $arData[$k][$field.'_'.$fieldIndex];
							if(!is_array($arVals)) $arVals = array($arVals);
						}
						else
						{
							$arVals = $arData[$k][$field];
						}
						
						if(isset($arSettings['MULTIPLE_SEPARATE_BY_ROWS']) && $arSettings['MULTIPLE_SEPARATE_BY_ROWS']=='Y')
						{
							if($this->params['FILE_EXTENSION']=='xlsx' && $arSettings['MULTIPLE_SEPARATE_BY_ROWS_MODE']!='MULTIROW')
							{
								$arVals['TYPE'] = 'MULTICELL';
								$val = $arVals;
							}
							else
							{
								$arVals['TYPE'] = 'MULTIROW';
								$val = $arVals;
								$arMultiRows[$field.'_'.$fieldIndex] = $field.'_'.$fieldIndex;
							}
						}
						elseif(isset($arVals['TYPE']) && in_array($arVals['TYPE'], array('MULTICELL', 'MULTIROW')))
						{
							$val = $arVals;
						}
						else
						{
							if(isset($arSettings['CHANGE_MULTIPLE_SEPARATOR']) && $arSettings['CHANGE_MULTIPLE_SEPARATOR']=='Y') $separator = $this->GetSeparator($arSettings['MULTIPLE_SEPARATOR']);
							else $separator = $this->params['ELEMENT_MULTIPLE_SEPARATOR'];
							$val = implode($separator, $arVals);
						}
						$arData[$k][$field.'_'.$fieldIndex] = $arElementData[$field.'_'.$fieldIndex] = $val;
					}
				}
			}

			foreach($arElementData as $k2=>$val)
			{
				if(is_array($val))
				{
					if(isset($arFieldSettings[$k2]['CHANGE_MULTIPLE_SEPARATOR']) && $arFieldSettings[$k2]['CHANGE_MULTIPLE_SEPARATOR']=='Y') $separator = $this->GetSeparator($arFieldSettings[$k2]['MULTIPLE_SEPARATOR']);
					else $separator = $this->params['ELEMENT_MULTIPLE_SEPARATOR'];	
					if(isset($val['TYPE']) && $val['TYPE']=='MULTICELL')
					{
						$arData[$k]['ROWS_COUNT'] = max(1, (int)$arData[$k]['ROWS_COUNT'], count($val)-1);
						foreach($val as $subkey=>$subval)
						{
							if(is_array($subval) && !array_key_exists('VALUE', $subval))
							{
								$arData[$k][$k2][$subkey] = implode($separator, $subval);
							}
						}
					}
					elseif(isset($val['TYPE']) && $val['TYPE']=='MULTIROW')
					{
						
					}
					else
					{				
						$arData[$k][$k2] = implode($separator, $val);
					}
				}
				elseif(is_object($val))
				{
					if(is_callable(array($val, 'ToString')))
					{
						$arData[$k][$k2] = $arElementData[$k2] = $val->ToString();
					}
					else 
					{
						$arData[$k][$k2] = $arElementData[$k2] = '';
					}
				}
			}
			
			if($skipLine && (!isset($arElementData['RTYPE']) || strpos($arElementData['RTYPE'], 'SECTION')!==0))
			{
				unset($arData[$k]);
				continue;
			}
			
			if(isset($this->stepparams['string_lengths']))
			{
				foreach($arFields as $fk=>$fv)
				{
					$val = isset($arElementData[$fv.'_'.$fk]) ? $arElementData[$fv.'_'.$fk] : $arElementData[$fv];
					$this->stepparams['string_lengths'][$listIndex][$fk] = max(0, (int)$this->stepparams['string_lengths'][$listIndex][$fk], strlen(is_array($val) ? serialize($val) : $val));
				}
			}
		}
		
		if(!empty($arMultiRows))
		{
			$arDataNew = array();
			foreach($arData as $k=>$v)
			{
				$arRows = array($v);
				foreach($arMultiRows as $v4) $arRows[0][$v4] = '';
				foreach($v as $k2=>$v2)
				{
					if(is_array($v2) && isset($v2['TYPE']) && $v2['TYPE']=='MULTIROW')
					{
						$i = 0;
						foreach($v2 as $k3=>$v3)
						{
							if($k3==='TYPE') continue;
							if(!isset($arRows[$i]))
							{
								$arRows[$i] = $v;
								foreach($arMultiRows as $v4) $arRows[$i][$v4] = '';
							}
							$arRows[$i][$k2] = $v3;
							$i++;
						}
					}
				}
				$arDataNew = array_merge($arDataNew, $arRows);
			}
			$arData = $arDataNew;
		}
		
		return array(
			'FIELDS' => $arFields,
			'DATA' => $arData,
			'PAGE_COUNT' => $arResElements['navPageCount'],
			'RECORD_COUNT' => $arResElements['navRecordCount'],
			'FULL_RECORD_COUNT' => $arResElements['navRecordCount'],
			'SECTION_KEY' => $arResElements['sectionKey'],
			'SECTION_COUNT' => $arResElements['sectionCount']
		);
	}
	
	public function CheckDiscountGroup($field, $arSettings)
	{
		if(preg_match('/^(OFFER_)?ICAT_PRICE(\d+)_PRICE_DISCOUNT$/', $field, $m))
		{
			if(isset($arSettings['USER_GROUP']) && is_array($arSettings['USER_GROUP']) && !empty($arSettings['USER_GROUP']))
			{
				list($ugKey, $userGroup) = $this->GetUserGroupData($arSettings['USER_GROUP']);
				if(!isset($this->arPricesGroup[$m[2]])) $this->arPricesGroup[$m[2]] = array();
				if(!isset($this->arPricesGroup[$m[2]][$ugKey])) $this->arPricesGroup[$m[2]][$ugKey] = $userGroup;
			}
		}
	}

	public function GetUserGroupData($userGroup)
	{
		sort($userGroup, SORT_NUMERIC);
		$ugKey = implode('_', $userGroup);
		return array($ugKey, $userGroup);
	}
	
	public function GetFieldList($listIndex, $output=false)
	{
		$arFields = array();
		if(isset($this->params['FIELDS_LIST'][$listIndex]))
		{
			$arFields = $this->params['FIELDS_LIST'][$listIndex];
		}
		if(!is_array($arFields) || count($arFields)==0)
		{
			$arFields = array('IE_ID', 'IE_NAME');
		}
		if($output && $this->params['REMOVE_EMPTY_COLUMNS']=='Y' && !$this->colListUpdated && isset($this->stepparams['string_lengths'][$listIndex]) && is_array($this->stepparams['string_lengths'][$listIndex]))
		{
			$unset = false;
			foreach($arFields as $k=>$v)
			{
				if((int)$this->stepparams['string_lengths'][$listIndex][$k]<=0)
				{
					$unset = true;
					unset($arFields[$k]);
					if(isset($this->params['FIELDS_LIST'][$listIndex][$k])) unset($this->params['FIELDS_LIST'][$listIndex][$k]);
					if(isset($this->fparams[$listIndex][$k])) unset($this->fparams[$listIndex][$k]);
				}
			}
			if($unset && is_array($this->fparams[$listIndex]))
			{
				$this->fparams[$listIndex] = array_values($this->fparams[$listIndex]);
			}
			$this->colListUpdated = true;
		}
		return $arFields;
	}
	
	public function GetElementsData(&$arData, $arParams)
	{
		if($arParams['TYPE']=='SECTION')
		{
			return $this->GetSectionsData($arData, $arParams);
		}
		elseif($arParams['TYPE']=='CONTENT_TABLE')
		{
			return $this->GetContentTableData($arData, $arParams);
		}
		
		$arFilter = $arParams['FILTER'];
		$arSkuFilter = $arParams['SKU_FILTER'];
		$arSectionFilter = $arParams['SECTION_FILTER'];
		$arNavParams = (is_array($arParams['NAV_PARAMS']) ? $arParams['NAV_PARAMS'] : false);
		$arAllFields = $arParams['FIELDS'];
		$showOnlyFilterSection = (bool)($this->params['SHOW_ONLY_SECTION_FROM_FILTER'][$this->listIndex]=='Y' && is_array($arFilter['SECTION_ID']) && count(array_diff($arFilter['SECTION_ID'], array(-1, 0))) > 0);

		$arOfferParams = false;
		$offersPropertyId = 0;
		if($arParams['TYPE'] != 'OFFER')
		{
			$arOfferFields = array();
			foreach($arAllFields as $k=>$v)
			{
				if(strpos($v, 'OFFER_')===0)
				{
					$arOfferFields[$k] = substr($v, 6);
				}
			}
			if(!empty($arOfferFields) && ($iblockOffer = $this->GetCachedOfferIblock($arFilter['IBLOCK_ID'])))
			{
				$arOfferParams = array(
					'TYPE' => 'OFFER',
					'FIELDS' => $arOfferFields,
					'NAV_PARAMS' => false,
					'FILTER' => array(
						'IBLOCK_ID' => $iblockOffer['OFFERS_IBLOCK_ID']
					)
				);
				if($this->params['EXPORT_ONE_OFFER_MIN_PRICE']=='Y')
				{
					$arOfferParams['NAV_PARAMS'] = array('nTopCount' => 1);
					if($this->params['EXPORT_ONE_OFFER_MIN_PRICE_TYPE'])
						$arOfferParams['ORDER'] = array($this->params['EXPORT_ONE_OFFER_MIN_PRICE_TYPE']=>'ASC');
					else 
						$arOfferParams['ORDER'] = array('CATALOG_PURCHASING_PRICE'=>'ASC');
				}
				elseif($this->params['EXPORT_ONE_OFFER_MAX_PRICE']=='Y')
				{
					$arOfferParams['NAV_PARAMS'] = array('nTopCount' => 1);
					if($this->params['EXPORT_ONE_OFFER_MAX_PRICE_TYPE'])
						$arOfferParams['ORDER'] = array($this->params['EXPORT_ONE_OFFER_MAX_PRICE_TYPE']=>'DESC');
					else 
						$arOfferParams['ORDER'] = array('CATALOG_PURCHASING_PRICE'=>'DESC');
				}
				if(is_array($arSkuFilter))
				{
					$arOfferParams['FILTER'] = array_merge($arOfferParams['FILTER'], $arSkuFilter);
				}
				$offersPropertyId = (int)$iblockOffer['OFFERS_PROPERTY_ID'];
				
				/*if(count($arOfferParams['FILTER']) > 1)
				{
					$arFilter['ID'] = CIBlockElement::SubQuery('PROPERTY_'.$offersPropertyId, $arOfferParams['FILTER']);
				}*/
				$cType = false;
				if(isset($arFilter['CATALOG_TYPE'])) $cType = $arFilter['CATALOG_TYPE'];
				elseif(isset($arFilter['=CATALOG_TYPE'])) $cType = $arFilter['=CATALOG_TYPE'];
				if(count($arOfferParams['FILTER']) > 1 && !array_key_exists('ID', $arFilter) && 
					(!is_array($cType) || !defined('\Bitrix\Catalog\ProductTable::TYPE_PRODUCT') || !in_array(\Bitrix\Catalog\ProductTable::TYPE_PRODUCT, $cType)))
				{
					$dbRes = \CIblockElement::GetList(array(), array_merge($arOfferParams['FILTER'], array('>PROPERTY_'.$offersPropertyId=>'0')), array('PROPERTY_'.$offersPropertyId), false, array('PROPERTY_'.$offersPropertyId));
					if($dbRes->SelectedRowsCount() > 0 && $dbRes->SelectedRowsCount() < 10000)
					{
						$arIds = array();
						while($arr = $dbRes->Fetch())
						{
							$arIds[] = $arr['PROPERTY_'.$offersPropertyId.'_VALUE'];
						}
						if(!empty($arIds)) $arFilter['ID'] = $arIds;
					}
				}
			}
		}		
		
		$arElementFields = array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID');
		$arElementFieldsRels = array();
		$arElementNameFields = array();
		$arElementPictureFields = array();
		$arPropsFields = array();
		$arPropsFieldsRels = array();
		$arFieldsIpropTemp = array();
		$arFieldsProduct = array();
		$arFieldsPrices = array();
		$arFieldsProductStores = array();
		$arFieldsDiscount = array();
		$arFieldsOrder = array();
		$arFieldsSections = array();
		$arFieldsSet = array();
		$arFieldsSet2 = array();
		$arFieldsCustom = array();
		foreach($arAllFields as $field)
		{
			if(strpos($field, 'IE_')===0)
			{
				$key = substr($field, 3);
				$arElementNameFields[] = $key;
				if($key=='SECTION_PATH') continue;
				if(preg_match('/^(PREVIEW_PICTURE|DETAIL_PICTURE)_(.+)$/', $key, $m))
				{
					if(!array_key_exists($m[1], $arElementPictureFields)) $arElementPictureFields[$m[1]] = array();
					if(!in_array($m[2], $arElementPictureFields[$m[1]])) $arElementPictureFields[$m[1]][] = $m[2];
					$key = $m[1];
				}
				if($key=='QR_CODE_IMAGE' && !in_array('DETAIL_PAGE_URL', $arElementFields))
				{
					$arElementFields[] = 'DETAIL_PAGE_URL';
				}
				if(strpos($key, '|')!==false)
				{
					list($key, $fieldRel) = explode('|', $key, 2);
					$arElementFieldsRels[$key][] = $fieldRel;
				}
				$arElementFields[] = $key;
			}
			elseif(strpos($field, 'ISECT')===0)
			{
				$arSect = explode('_', substr($field, 5), 2);
				if(strlen($arSect[0])==0) $arSect[0] = 0;
				$arFieldsSections[$arSect[0]][] = $arSect[1];
			}
			elseif(strpos($field, 'ICAT_PRICE')===0)
			{
				$arPrice = explode('_', substr($field, 10), 2);
				$arFieldsPrices[$arPrice[0]][] = $arPrice[1];
			}
			elseif(strpos($field, 'ICAT_STORE')===0)
			{
				$arStore = explode('_', substr($field, 10), 2);
				$arFieldsProductStores[$arStore[0]][] = $arStore[1];
			}
			elseif(strpos($field, 'ICAT_DISCOUNT_')===0)
			{
				$arFieldsDiscount[] = substr($field, 14);
			}
			elseif(strpos($field, 'ICAT_ORDER_')===0)
			{
				if($this->bSale)
				{
					$arFieldsOrder[] = substr($field, 11);
				}
			}
			elseif(strpos($field, 'ICAT_SET_')===0)
			{
				$arFieldsSet[] = substr($field, 9);
			}
			elseif(strpos($field, 'ICAT_SET2_')===0)
			{
				$arFieldsSet2[] = substr($field, 10);
			}
			elseif(strpos($field, 'ICAT_')===0)
			{
				$key = substr($field, 5);
				if($key=='BARCODE_IMAGE' && !in_array('BARCODE', $arFieldsProduct))
				{
					$arFieldsProduct[] = 'BARCODE';
				}
				$arFieldsProduct[] = substr($field, 5);
			}
			elseif(strpos($field, 'IP_PROP')===0)
			{
				$fieldKey = substr($field, 7);
				if(strpos($fieldKey, '|')!==false)
				{
					list($fieldKey, $fieldRel) = explode('|', $fieldKey, 2);
					$arPropsFieldsRels[$fieldKey][] = $fieldRel;
				}
				$arPropsFields[] = $fieldKey;
			}
			elseif(strpos($field, 'IP_LIST_PROPS')===0)
			{
				if(is_array($this->arPropListProps))
				{
					$arPropsFields = array_merge($arPropsFields, $this->arPropListProps);
				}
			}
			elseif(strpos($field, 'IPROP_TEMP_')===0)
			{
				$arFieldsIpropTemp[] = substr($field, 11);
			}
			elseif(strpos($field, 'CUSTOM_')===0)
			{
				$arFieldsCustom[] = substr($field, 7);
			}
		}
	
		$arSelectElementFields = $arElementFields;
		$arSelectElementFieldsForPrice = $arElementFields;
		if(!empty($arFieldsPrices))
		{
			$arPriceIds = array();
			foreach($arFieldsPrices as $k=>$v)
			{
				$arPriceIds[] = $k;
			}
			$arPriceCodes = array();
			$dbRes = CCatalogGroup::GetList(array(), array('ID'=>$arPriceIds), false, false, array('ID', 'NAME'));
			while($arCatalogGroup = $dbRes->Fetch())
			{
				$arPriceCodes[$arCatalogGroup['ID']] = $arCatalogGroup['NAME'];
			}
			$arGroupPrices = CIBlockPriceTools::GetCatalogPrices($arFilter['IBLOCK_ID'], $arPriceCodes);
			if(!is_array($arGroupPrices)) $arGroupPrices = array();
			foreach($arGroupPrices as $k=>$v)
			{
				$arGroupPrices[$k]['CAN_VIEW'] = 1;
				//$arSelectElementFields[] = $v['SELECT'];
				$arSelectElementFieldsForPrice[] = $v['SELECT'];
			}
			
			if(!in_array('VAT_ID', $arFieldsProduct)) $arFieldsProduct[] = 'VAT_ID';
			if(!in_array('VAT_INCLUDED', $arFieldsProduct)) $arFieldsProduct[] = 'VAT_INCLUDED';
		}
		
		$arSections = $this->GetSelectSections($arFilter, $arParams);
		$sCount = count($arSections);
	
		$arFilterOriginal = $arFilter;
		$sectionKeyInc = 2;
		$dbResCnt = 0;
		$dbElemResCnt = 0;
		foreach($arSections as $skey=>$arSection)
		{
			if($arParams['SECTION_KEY'] && $arParams['SECTION_KEY'] > $skey + 1) continue;
			$break = false;
			$isSection = false;
			if(!empty($arSection) && is_numeric($arSection['ID']))
			{
				$isSection = true;
				$arFilter['SECTION_ID'] = $arSection['ID'];				
			}
			if($this->params['EXPORT_SEP_SECTIONS']=='Y')
			{
				$arFilter['INCLUDE_SUBSECTIONS'] = 'N';
			}
			if($this->params['EXPORT_ELEMENT_ONE_SECTION']=='Y' && !$showOnlyFilterSection && is_numeric($arSection['ID']) && $arFilter['SECTION_ID'] > 0)
			{
				$dbESRes = Bitrix\Iblock\ElementTable::GetList(array('filter'=>array('IBLOCK_SECTION_ID'=>$arFilter['SECTION_ID']), 'select'=>array('ID')));
				$arIds = array(0);
				while($arES = $dbESRes->Fetch())
				{
					$arIds[] = $arES['ID'];
				}
				if(!is_array($arFilterOriginal['ID'])) $arFilter['ID'] = $arIds;
				else
				{
					$arFilter['ID'] = array_intersect($arFilterOriginal['ID'], $arIds);
					if(count($arFilter['ID'])==0) $arFilter['ID'] = array(0);
				}
			}

			if(isset($arParams['ORDER']) && !empty($arParams['ORDER'])) $arOrder = $arParams['ORDER'];
			else $arOrder = $this->GetElementOrder($arFilter['IBLOCK_ID'], (bool)($arParams['TYPE']=='OFFER'));
			
			
			//$dbResElements = CIblockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelectElementFields);
			$dbResElements = \Bitrix\KdaImportexcel\DataManager\IblockElementTable::GetListComp($arFilter, $arSelectElementFields, $arOrder, $arNavParams);
			$elemsCnt = \Bitrix\KdaImportexcel\DataManager\IblockElementTable::SelectedRowsCountComp($dbResElements);

			if($isSection 
				&& (!isset($arNavParams['iNumPage']) || $arNavParams['iNumPage']==1)
				&& $elemsCnt > 0 && (int)$this->currentPageCnt < 1)
			{
				if($this->params['EXPORT_SECTION_PATH']=='Y')
				{
					/*$arData[] = array(
						'RTYPE' => 'SECTION_PATH',
						'NAME' => $this->GetSectionPath($arSection['ID'])
					);
					$dbResCnt++;*/
					$arParentSections = $this->GetParentSections($arSection['ID'], $arFilter['IBLOCK_ID']);
					if($this->params['EXPORT_SECTION_PATH_MODE']=='END')
					{
						$arParentSection = end($arParentSections);
						$arData[] = array(
							'RTYPE' => 'SECTION_PATH',
							'NAME' => ($arParentSection['SECTION_PAGE_URL'] ? '<a class="kda-ee-section-link" href="'.htmlspecialcharsbx($arParentSection['SECTION_PAGE_URL']).'">' : '').$this->GetSectionPath($arParentSection['ID']).($arParentSection['SECTION_PAGE_URL'] ? '</a>' : ''),
							'ELEMENT_CNT' => $elemsCnt,
							'SECTION_PAGE_URL' => $arParentSection['SECTION_PAGE_URL']
						);
						$dbResCnt++;
					}
					else
					{
						foreach($arParentSections as $key=>$arParentSection)
						{
							$arData[] = array(
								'RTYPE' => 'SECTION_'.$key,
								'NAME' => ($arParentSection['SECTION_PAGE_URL'] ? '<a class="kda-ee-section-link" href="'.htmlspecialcharsbx($arParentSection['SECTION_PAGE_URL']).'">' : '').$this->GetSectionPath($arParentSection['ID']).($arParentSection['SECTION_PAGE_URL'] ? '</a>' : ''),
								'ELEMENT_CNT' => $elemsCnt,
								'SECTION_PAGE_URL' => $arParentSection['SECTION_PAGE_URL']
							);
							$dbResCnt++;
						}
					}
				}
				else
				{
					$arParentSections = $this->GetParentSections($arSection['ID'], $arFilter['IBLOCK_ID']);
					foreach($arParentSections as $key=>$arParentSection)
					{
						$arData[] = array(
							'RTYPE' => 'SECTION_'.$key,
							'NAME' => ($arParentSection['SECTION_PAGE_URL'] ? '<a class="kda-ee-section-link" href="'.htmlspecialcharsbx($arParentSection['SECTION_PAGE_URL']).'">' : '').$arParentSection['NAME'].($arParentSection['SECTION_PAGE_URL'] ? '</a>' : ''),
							'ELEMENT_CNT' => $elemsCnt,
							'SECTION_PAGE_URL' => $arParentSection['SECTION_PAGE_URL']
						);
						$dbResCnt++;
					}
				}
			}
			
			/*Prepare elements data*/
			$arElementList = array();
			$arElementIds = array();
			$arElementPrices = array();
			$arElementProps = array();
			$arElementProduct = array();
			$iMultiSite = (bool)(($arSites = $this->GetIblockSite($arFilter['IBLOCK_ID'])) && count($arSites) > 1);
			while($arElement = \Bitrix\KdaImportexcel\DataManager\IblockElementTable::FetchComp($dbResElements))
			{
				if($iMultiSite && isset($arElement['LANG_DIR']) && strlen($arElement['LANG_DIR']) > 1 && strpos($arElement['DETAIL_PAGE_URL'], $arElement['LANG_DIR'])===0)
				{
					$arElement['DETAIL_PAGE_URL'] = $arElement['~DETAIL_PAGE_URL'] = substr($arElement['DETAIL_PAGE_URL'], strlen(rtrim($arElement['LANG_DIR'], '/')));
				}
				if(array_key_exists('DETAIL_PAGE_URL', $arElement))
				{
					$arElement['DETAIL_PAGE_URL'] = $this->AddUrlDomain($arElement['DETAIL_PAGE_URL'], $arFilter['IBLOCK_ID']);
					$arElement['~DETAIL_PAGE_URL'] = $this->AddUrlDomain($arElement['~DETAIL_PAGE_URL'], $arFilter['IBLOCK_ID']);
				}
				$arElementList[] = $arElement;
				$arElementIds[] = $arElement['ID'];
			}
			if(!empty($arElementIds))
			{
				if(!empty($arFieldsPrices))
				{
					$arCatlogGroupIds = array();
					foreach($arFieldsPrices as $key=>$arPriceSelectField)
					{
						if(empty($arPriceSelectField)) continue;
						$arCatlogGroupIds[] = $key;
					}
					if(!empty($arCatlogGroupIds))
					{
						$arPriceFilter = array('PRODUCT_ID'=>$arElementIds, 'CATALOG_GROUP_ID'=>$arCatlogGroupIds);
						if(is_callable(array('\Bitrix\Catalog\Model\Price', 'getList')))
						{
							$dbRes2 = \Bitrix\Catalog\PriceTable::getList(array('filter'=>$arPriceFilter));
						}
						else
						{
							$dbRes2 = CPrice::GetList(array(), $arPriceFilter, false, false);
						}
						while($arPrice = $dbRes2->Fetch())
						{
							$arElementPrices[$arPrice['CATALOG_GROUP_ID']][$arPrice['PRODUCT_ID']][] = $arPrice;
						}
					}
				}
				
				if(!empty($arPropsFields))
				{
					$arPropIds = array();
					foreach($arPropsFields as $propKey)
					{
						$propKey = (int)current(explode('_', $propKey));
						$arPropIds[$propKey] = $propKey;
					}
					$arDefProps = $this->GetIblockProperties($arFilter['IBLOCK_ID']);
					
					$dbRes = CIBlockElement::GetPropertyValues($arFilter['IBLOCK_ID'], array('ID'=>$arElementIds), true, array('ID'=>$arPropIds));
					while($arr = $dbRes->Fetch())
					{
						$arCurElem = array();
						foreach($arPropIds as $propId)
						{
							if(!is_array($arr[$propId]) && strlen($arr[$propId])==0 && !is_array($arr['DESCRIPTION'][$propId]) && strlen($arr['DESCRIPTION'][$propId])==0) continue;
							$arCurProp = array(
								'ID' => $propId,
								'MULTIPLE' => $arDefProps[$propId]['MULTIPLE'],
								'PROPERTY_TYPE' => $arDefProps[$propId]['PROPERTY_TYPE'],
								'USER_TYPE' => $arDefProps[$propId]['USER_TYPE'],
								'LINK_IBLOCK_ID' => $arDefProps[$propId]['LINK_IBLOCK_ID'],
								'USER_TYPE_SETTINGS' => $arDefProps[$propId]['USER_TYPE_SETTINGS']
							);
							if($arDefProps[$propId]['MULTIPLE'] && is_array($arr[$propId]))
							{
								if(count($arr[$propId])==0)
								{
									$arr[$propId] = array('');
									$arr['DESCRIPTION'][$propId] = array('');
								}
								foreach($arr[$propId] as $k=>$v)
								{
									$arCurElem[] = array_merge($arCurProp, array('VALUE'=>$this->GetPropVal($v, $arDefProps[$propId]), 'DESCRIPTION'=>$this->GetPropDesc($arr['DESCRIPTION'][$propId][$k], $arDefProps[$propId])));
								}
							}
							else
							{
								$arCurElem[] = array_merge($arCurProp, array('VALUE'=>$this->GetPropVal($arr[$propId], $arDefProps[$propId]), 'DESCRIPTION'=>$this->GetPropDesc($arr['DESCRIPTION'][$propId], $arDefProps[$propId])));
							}
						}
						$arElementProps[$arr['IBLOCK_ELEMENT_ID']] = $arCurElem;
					}
				}
				
				if(!empty($arFieldsProduct))
				{
					$arProductFilter = array('ID'=>$arElementIds);
					if(is_callable(array('\Bitrix\Catalog\Model\Product', 'getList')))
					{
						$dbRes2 = \Bitrix\Catalog\ProductTable::getList(array('filter'=>$arProductFilter));
					}
					else
					{
						$dbRes2 = CCatalogProduct::GetList(array(), $arProductFilter);
					}
					while($arr = $dbRes2->Fetch())
					{
						if(array_key_exists('TYPE', $arr)) $arr['TYPE'] = GetMessage("KDA_EE_PRODUCT_TYPE_".$arr['TYPE']);
						$arElementProduct[$arr['ID']] = $arr;
					}
				}
			}
			/*/Prepare elements data*/
			
			if($arParams['TYPE'] != 'OFFER')
			{
				$curPageCnt = 0;
				$this->stepparams['currentPageCnt'] = 0;
			}

			foreach($arElementList as $arElement)
			{
				if($arParams['TYPE'] != 'OFFER')
				{
					$curPageCnt++;
					if($curPageCnt <= (int)$this->currentPageCnt) continue;
				}
				
				$arElement2 = array();
				foreach($arElement as $k=>$v)
				{
					if(strpos($k, '~')===0)
					{
						$arElement[substr($k, 1)] = $v;
					}
				}

				foreach($arElement as $k=>$v)
				{
					if(strpos($k, '~')!==0 && in_array($k, $arElementFields))
					{
						if($k=='PREVIEW_PICTURE' || $k=='DETAIL_PICTURE')
						{
							$v = $this->GetFileValue($v, ($arParams['TYPE']=='OFFER' ? 'OFFER_' : '').'IE_'.$k);
						}
						$arElement2['IE_'.$k] = $v;
					}
				}
				foreach($arElementPictureFields as $k=>$v)
				{
					if(!$arElement[$k] || !($arFile = \CKDAExportUtils::GetFileArray($arElement[$k]))) $arFile = array();
					foreach($v as $v2)
					{
						$arElement2['IE_'.$k.'_'.$v2] = $arFile[$v2];
					}
				}
				/*if(in_array('PREVIEW_PICTURE_DESCRIPTION', $arElementNameFields))
				{
					$arElement2['IE_PREVIEW_PICTURE_DESCRIPTION'] = $this->GetFileDescription($arElement['PREVIEW_PICTURE']);
				}
				if(in_array('DETAIL_PICTURE_DESCRIPTION', $arElementNameFields))
				{
					$arElement2['IE_DETAIL_PICTURE_DESCRIPTION'] = $this->GetFileDescription($arElement['DETAIL_PICTURE']);
				}*/
				if(in_array('QR_CODE_IMAGE', $arElementNameFields) && ((int)$this->stepparams['export_started'] > 0 || (int)$this->stepparams['qrcode_qnt'] < 10))
				{
					$obRequest = \Bitrix\Main\Context::getCurrent()->getRequest();
					$requestUri = trim($obRequest->getRequestUri());
					$arElement2['IE_QR_CODE_IMAGE'] = $arElement['DETAIL_PAGE_URL'];
					if($this->params['EXPORT_ADD_DOMAIN']!='Y') $arElement2['IE_QR_CODE_IMAGE'] = ($obRequest->isHttps() ? 'https' : 'http').'://'.$this->GetIblockDomain($arElement['IBLOCK_ID']).$arElement2['IE_QR_CODE_IMAGE'];
					$this->stepparams['qrcode_qnt']++;
				}
				
				foreach($arElementFieldsRels as $fk=>$arRels)
				{
					if(in_array($fk, array('CREATED_BY', 'MODIFIED_BY')))
					{
						foreach($arRels as $relField)
						{
							$fieldKeyOrig = 'IE_'.$fk;
							$fieldKey = $fieldKeyOrig.'|'.$relField;
							$arElement2[$fieldKey] = $this->GetUserField($arElement2[$fieldKeyOrig], $relField);
						}
					}
				}
				
				$this->GetElementSectionShare($arElement2, $arElement, $arElementNameFields, $arFieldsSections, $arFilter, $showOnlyFilterSection);
			
				if(!empty($arPropsFields))
				{
					/*$dbRes2 = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $arElement['ID'], array(), array());
					while($arProp = $dbRes2->Fetch())*/
					foreach($arElementProps[$arElement['ID']] as $k=>$arProp)
					{
						if(true /*in_array($arProp['ID'], $arPropsFields)*/)
						{
							$arRels = $arPropsFieldsRels[$arProp['ID']];
							if(!is_array($arRels) || empty($arRels)) $arRels = array('');
							foreach($arRels as $relField)
							{
								$fieldKey = $fieldKeyOrig = 'IP_PROP'.$arProp['ID'];
								if($relField) $fieldKey .= '|'.$relField;
								
								$val = $arProp['VALUE'];
								if($relField=='IE_PREVIEW_PICTURE' || $relField=='IE_DETAIL_PICTURE')
								{
									$val = $this->GetFileValue($val);
								}
								elseif(strpos($relField, 'ICAT_')===0)
								{
									$val = $this->GetProductFieldValue($val, $relField);
								}
								else
								{
									if($arProp['PROPERTY_TYPE']=='L')
									{
										$val = $this->GetPropertyListValue($arProp, $val, $relField);
									}
									elseif($arProp['PROPERTY_TYPE']=='E')
									{
										$val = $this->GetPropertyElementValue($arProp, $val, $relField);
									}
									elseif($arProp['PROPERTY_TYPE']=='G')
									{
										$val = $this->GetPropertySectionValue($arProp, $val, $relField);
									}
									elseif($arProp['PROPERTY_TYPE']=='F')
									{
										$val = $this->GetFileValue($val, ($arParams['TYPE']=='OFFER' ? 'OFFER_' : '').$fieldKeyOrig, $relField, $k);
									}
									elseif($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='directory')
									{
										$val = $this->GetHighloadBlockValue($arProp, $val, $relField);
									}
									elseif($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='HTML')
									{
										$val = $this->GetHTMLValue($arProp, $val);
									}
									elseif($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='C')
									{
										if(function_exists('json_decode') && $relField)
										{
											$val = json_decode($val, true)[$relField];
										}
									}
									elseif($arProp['PROPERTY_TYPE']=='R' && $arProp['USER_TYPE']=='RegionsList' && \Bitrix\Main\Loader::includeModule("sotbit.regions"))
									{
										if(strlen($val) > 0)
										{
											if($arRegion = \Sotbit\Regions\Internals\RegionsTable::getList(array('filter'=>array('ID'=>(int)$val), 'select'=>array('NAME')))->Fetch()) $val = $arRegion['NAME'];
											else $val = '';
										}
									}
								}
								
								if($arProp['MULTIPLE']=='Y')
								{
									if(!isset($arElement2[$fieldKey]))
									{
										$arElement2[$fieldKey] = array();
									}
									$arElement2[$fieldKey][] = $val;
								}
								else
								{
									$arElement2[$fieldKey] = $val;
								}
								
								if(!isset($arElement2[$fieldKeyOrig]))
								{
									$arElement2[$fieldKeyOrig] = $arElement2[$fieldKey];
								}
							}
						}
						
						if(in_array($arProp['ID'].'_DESCRIPTION', $arPropsFields))
						{
							$val = $arProp['DESCRIPTION'];
							$key = 'IP_PROP'.$arProp['ID'].'_DESCRIPTION';
							
							if($arProp['MULTIPLE']=='Y')
							{
								if(!isset($arElement2[$key])) $arElement2[$key] = array();
								$arElement2[$key][] = $val;
							}
							else
							{
								$arElement2[$key] = $val;
							}
						}
					}
				}
				
				if(!empty($arFieldsIpropTemp))
				{
					$arFieldsIpropTemp2 = $arFieldsIpropTemp;
					$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arElement['IBLOCK_ID'], $arElement['ID']);
					$arFieldsIpropTempCh = preg_grep('/^CH_/', $arFieldsIpropTemp2);
					if(count($arFieldsIpropTempCh) > 0) $arFieldsIpropTemp2 = array_diff($arFieldsIpropTemp2, $arFieldsIpropTempCh);
					$arFieldsIpropTempTmpl = preg_grep('/^TEMPLATE_/', $arFieldsIpropTemp2);
					if(count($arFieldsIpropTempTmpl) > 0) $arFieldsIpropTemp2 = array_diff($arFieldsIpropTemp2, $arFieldsIpropTempTmpl);
					$arPropVals = $ipropValues->queryValues();
					if(!empty($arFieldsIpropTempCh))
					{
						foreach($arFieldsIpropTempCh as $key)
						{
							$subKey = substr($key, 3);
							$v = (bool)(isset($arPropVals[$subKey]) && $arPropVals[$subKey]['ENTITY_TYPE']=='E');
							$arElement2['IPROP_TEMP_'.$key] = ($v ? 'Y' : 'N');
						}
					}
					if(!empty($arFieldsIpropTempTmpl))
					{
						foreach($arFieldsIpropTempTmpl as $key)
						{
							$subKey = substr($key, 9);
							if(isset($arPropVals[$subKey]) && $arPropVals[$subKey]['ENTITY_TYPE']=='E') $arElement2['IPROP_TEMP_'.$key] = $arPropVals[$subKey]['TEMPLATE'];
							else $arElement2['IPROP_TEMP_'.$key] = '';
						}
					}
					
					foreach($arFieldsIpropTemp2 as $key)
					{
						$arElement2['IPROP_TEMP_'.$key] = $arPropVals[$key]['VALUE'];
					}
				}
				
				if(!empty($arFieldsSet) && CBXFeatures::IsFeatureEnabled('CatCompleteSet') && CCatalogProductSet::isProductHaveSet($arElement['ID'], CCatalogProductSet::TYPE_GROUP))
				{
					$arSets = CCatalogProductSet::getAllSetsByProduct($arElement['ID'], CCatalogProductSet::TYPE_GROUP);
					$arSet = current($arSets);
					if(is_array($arSet['ITEMS']))
					{
						foreach($arFieldsSet as $setField)
						{
							if(array_key_exists('ICAT_SET_'.$setField, $arElement2)) continue;
							foreach($arSet['ITEMS'] as $arSetItem)
							{
								if($pos = mb_strpos($setField, '|'))
								{
									$arElement2['ICAT_SET_'.$setField][] = $this->GetPropertyElementValue(array('ID'=>'ICAT_SET', 'LINK_IBLOCK_ID' => $arElement['IBLOCK_ID']), $arSetItem[mb_substr($setField, 0, $pos)], mb_substr($setField, $pos + 1));
								}
								else $arElement2['ICAT_SET_'.$setField][] = $arSetItem[$setField];
							}
						}
					}
				}
				
				if(!empty($arFieldsSet2) && CBXFeatures::IsFeatureEnabled('CatCompleteSet') && CCatalogProductSet::isProductHaveSet($arElement['ID'], CCatalogProductSet::TYPE_SET))
				{
					$arSets2 = CCatalogProductSet::getAllSetsByProduct($arElement['ID'], CCatalogProductSet::TYPE_SET);
					$arSet2 = current($arSets2);
					if(is_array($arSet2['ITEMS']))
					{
						foreach($arFieldsSet2 as $set2Field)
						{
							if(array_key_exists('ICAT_SET2_'.$set2Field, $arElement2)) continue;
							foreach($arSet2['ITEMS'] as $arSet2Item)
							{
								if($pos = mb_strpos($set2Field, '|'))
								{
									$arElement2['ICAT_SET2_'.$set2Field][] = $this->GetPropertyElementValue(array('ID'=>'ICAT_SET2', 'LINK_IBLOCK_ID' => $arElement['IBLOCK_ID']), $arSet2Item[mb_substr($set2Field, 0, $pos)], mb_substr($set2Field, $pos + 1));
								}
								else $arElement2['ICAT_SET2_'.$set2Field][] = $arSet2Item[$set2Field];
							}
						}
					}
				}
				
				if(!empty($arFieldsProduct))
				{
					/*$dbRes2 = CCatalogProduct::GetList(array(), array('ID'=>$arElement['ID']), false, array('nTopCount'=>1), array());
					if($arProduct = $dbRes2->Fetch())*/
					if($arProduct = $arElementProduct[$arElement['ID']])
					{
						foreach($arProduct as $k=>$v)
						{
							if($k=='VAT_ID')
							{
								if($v)
								{
									if(!isset($this->catalogVats)) $this->catalogVats = array();
									if(!isset($this->catalogVats[$v]))
									{
										$vatPercent = '';
										$dbRes = CCatalogVat::GetList(array(), array('ID'=>$v), array('RATE'));
										if($arVat = $dbRes->Fetch())
										{
											$vatPercent = $arVat['RATE'];
										}
										$this->catalogVats[$v] = $vatPercent;
									}
									$v = $this->catalogVats[$v];
								}
								else
								{
									$v = '';
								}
							}
							elseif($k=='MEASURE')
							{
								$v = $this->GetMeasureVal($v);
							}	
							
							$elemKey = $elemParamKey = 'ICAT_'.$k;
							if($arParams['TYPE'] == 'OFFER') $elemParamKey = 'OFFER_'.$elemParamKey;
							if($k=='PURCHASING_PRICE')
							{
								$arElement2[$elemKey.'_ORIG'] = $v;
								$v = $this->GetConvertedPrice($v, $arProduct['PURCHASING_CURRENCY'], $elemParamKey);
							}
							$arElement2[$elemKey] = $v;
						}
						
						if(in_array('MEASURE_RATIO', $arFieldsProduct))
						{
							$dbRes = CCatalogMeasureRatio::getList(array(), array('PRODUCT_ID' => $arElement['ID']), false, false, array('RATIO'));
							if($arRatio = $dbRes->Fetch())
							{
								$arElement2['ICAT_MEASURE_RATIO'] = $arRatio['RATIO'];
							}
							else
							{
								$arElement2['ICAT_MEASURE_RATIO'] = '';
							}
						}
						
						if(in_array('BARCODE', $arFieldsProduct))
						{
							$dbRes = CCatalogStoreBarCode::getList(array(), array('PRODUCT_ID' => $arElement['ID']), false, false, array('ID', 'BARCODE'));
							$arElement2['ICAT_BARCODE'] = '';
							while($arBarcode = $dbRes->Fetch())
							{
								$arElement2['ICAT_BARCODE'] .= (strlen($arElement2['ICAT_BARCODE']) > 0 ? $this->params['ELEMENT_MULTIPLE_SEPARATOR'] : '').$arBarcode['BARCODE'];
							}
						}
						
						if(in_array('BARCODE_IMAGE', $arFieldsProduct) && ((int)$this->stepparams['export_started'] > 0 || (int)$this->stepparams['barcode_qnt'] < 10))
						{
							$fieldKey = ($arParams['TYPE']=='OFFER' ? 'OFFER_' : '').'ICAT_BARCODE_IMAGE';
							$barcodefield = (string)$this->fparamsByName[$this->listIndex][$fieldKey]['BARCODE_FIELD'];
							if(strlen($barcodefield)==0) $barcodefield = 'ICAT_BARCODE';
							$barcodeval = explode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $arElement2[$barcodefield]);
							$barcodeval = preg_replace('/\D/', '', current($barcodeval));
							if(strlen($barcodeval) > 0)
							{
								if(!class_exists('\Barcode\BarcodeGenerator')) require_once(dirname(__FILE__).'/../../lib/phpbarcode/BarcodeGenerator.php');
								$barcodepath = $this->GetNewImagePath('png');
								$generator = new \Barcode\BarcodeGeneratorPNG();
								$barcodeHeight = (int)$this->fparamsByName[$this->listIndex][$fieldKey]['BARCODE_HEIGHT'];
								if($generator->getBarcode($barcodepath, $barcodeval, $generator::TYPE_EAN_13, ($barcodeHeight / 80) * 2, $barcodeHeight)!==false)
								{
									$arElement2['ICAT_BARCODE_IMAGE'] = $this->GetFileValue($barcodepath, $fieldKey);
									$this->stepparams['barcode_qnt']++;
								}
							}
						}
					}
					
					if(in_array('UF_PRODUCT_GROUP', $arFieldsProduct) && ($productGroup = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFieldValue("PRODUCT", 'UF_PRODUCT_GROUP', $arElement['ID'], LANGUAGE_ID)))
					{
						if(!isset($this->arProductGroups) || !is_array($this->arProductGroups)) $this->arProductGroups = array();
						if(!array_key_exists($productGroup, $this->arProductGroups))
						{
							$this->arProductGroups[$productGroup] = '';
							if(!isset($this->$productGroupClass))
							{
								if(Loader::includeModule('highloadblock') && ($hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('NAME'=>'ProductMarkingCodeGroup')))->fetch()))
								{
									$dbRes = \CUserTypeEntity::GetList(array(), array('ENTITY_ID'=>'HLBLOCK_'.$hlblock['ID']));
									$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
									$this->$productGroupClass = $entity->getDataClass();
								}
								else $this->$productGroupClass = false;
							}
							if($this->$productGroupClass)
							{
								$entityDataClass = $this->$productGroupClass;
								if($arr = $entityDataClass::getList(array('filter'=>array('ID'=>$productGroup), 'select'=>array('UF_NAME')))->fetch())
								{
									$this->arProductGroups[$productGroup] = $arr['UF_NAME'];
								}
							}
						}
						$arElement2['ICAT_UF_PRODUCT_GROUP'] = $this->arProductGroups[$productGroup];
					}
				}

				if(!empty($arFieldsPrices))
				{
					foreach($arFieldsPrices as $key=>$arPriceSelectField)
					{
						if(empty($arPriceSelectField)) continue;
						$arNavStartParams = array('nTopCount'=>1);
						$needPriceExt = false;
						if(in_array('PRICE_EXT', $arPriceSelectField) || $this->useExtPrice)
						{
							$arNavStartParams = false;
							$needPriceExt = true;
						}
						
						if(in_array('PRICE_DISCOUNT', $arPriceSelectField))
						{
							$elemKey = $elemParamKey = 'ICAT_PRICE'.$key.'_PRICE_DISCOUNT';
							if($arParams['TYPE'] == 'OFFER') $elemParamKey = 'OFFER_'.$elemParamKey;
							$siteId = $this->GetCFSettings($elemParamKey, 'SITE_ID');
							if(!$siteId) $siteId = $this->GetIblockSite($arElement['IBLOCK_ID'], true);
							if(($optimalPrice = $this->GetProductDiscountPrice($arElement['ID'], $key, array(2), $siteId, $arElement2, $this->GetCFSettings($elemParamKey)))!==false)
							{
								$arPrice = array(
									'DISCOUNT_VALUE' => $optimalPrice['PRICE'],
									'CURRENCY' => $optimalPrice['CURRENCY']
								);
							}
							else
							{
								$dbResElementForPrice = CIblockElement::GetList(array(), array('ID'=>$arElement['ID']), false, array('nTopCount'=>1), $arSelectElementFieldsForPrice);
								$arElementForPrice = $dbResElementForPrice->Fetch();
								$arPrices = CIBlockPriceTools::GetItemPrices($arElement['IBLOCK_ID'], $arGroupPrices, $arElementForPrice, true, array(), 0, $siteId);
								$arPrice = $arPrices[$arPriceCodes[$key]];
							}
							$arElement2[$elemKey.'_ORIG'] = $arPrice['DISCOUNT_VALUE'];
							$arElement2[$elemKey] = $this->GetConvertedPrice($arPrice['DISCOUNT_VALUE'], $arPrice['CURRENCY'], $elemParamKey, $arElement2, $key);
							
							if(isset($this->arPricesGroup[$key]) && is_array($this->arPricesGroup[$key]))
							{
								$origUserId = $GLOBALS['USER']->GetID();
								foreach($this->arPricesGroup[$key] as $keyGroups=>$arGroups)
								{
									if(($optimalPrice = $this->GetProductDiscountPrice($arElement['ID'], $key, $arGroups, $siteId, $arElement2, $this->GetCFSettings($elemParamKey)))!==false)
									{
										$arPrice = array(
											'DISCOUNT_VALUE' => $optimalPrice['PRICE'],
											'CURRENCY' => $optimalPrice['CURRENCY']
										);
									}
									else
									{
										$userId = $this->GetUserByGroups($arGroups);
										if(!$userId) continue;
										//$GLOBALS['USER']->Authorize($userId);
										$arPrices = CIBlockPriceTools::GetItemPrices($arElement['IBLOCK_ID'], $arGroupPrices, $arElementForPrice, true, array(), /*0*/$userId, $siteId);
										$arPrice = $arPrices[$arPriceCodes[$key]];
										//if($origUserId > 0) $GLOBALS['USER']->Authorize($origUserId);
										//else $GLOBALS['USER']->Logout();
									}
									$arElement2[$elemKey.'__'.$keyGroups.'_ORIG'] = $arPrice['DISCOUNT_VALUE'];
									$arElement2[$elemKey.'__'.$keyGroups] = $this->GetConvertedPrice($arPrice['DISCOUNT_VALUE'], $arPrice['CURRENCY'], $elemParamKey, $arElement2, $key);
								}
							}
						}
						
						if(in_array('PRICE', $arPriceSelectField) && !in_array('CURRENCY', $arPriceSelectField)) $arPriceSelectField[] = 'CURRENCY';
						if($needPriceExt)
						{
							
							if(!in_array('PRICE', $arPriceSelectField)) $arPriceSelectField[] = 'PRICE';
							if(!in_array('CURRENCY', $arPriceSelectField)) $arPriceSelectField[] = 'CURRENCY';
							if(!in_array('QUANTITY_FROM', $arPriceSelectField)) $arPriceSelectField[] = 'QUANTITY_FROM';
							if(!in_array('QUANTITY_TO', $arPriceSelectField)) $arPriceSelectField[] = 'QUANTITY_TO';
						}					
						//if(in_array('EXTRA', $arPriceSelectField)) $arPriceSelectField[] = 'EXTRA_ID';
						$arPriceExtraSelectField = preg_grep('/^EXTRA/', $arPriceSelectField);
						if(count($arPriceExtraSelectField) > 0) $arPriceSelectField[] = 'EXTRA_ID';
						
						$arPrices = array();
						if(isset($arElementPrices[$key][$arElement['ID']]))
						{
							$arPrices = $arElementPrices[$key][$arElement['ID']];
							if($arNavStartParams['nTopCount']==1) $arPrices = array_slice($arPrices, 0, 1);
						}
						else
						{
							$dbRes2 = CPrice::GetList(array(), array('PRODUCT_ID'=>$arElement['ID'], 'CATALOG_GROUP_ID'=>$key), false, $arNavStartParams, $arPriceSelectField);
							while($arPrice = $dbRes2->Fetch())
							{
								$arPrices[] = $arPrice;
							}
						}
						//$dbRes2 = CPrice::GetList(array(), array('PRODUCT_ID'=>$arElement['ID'], 'CATALOG_GROUP_ID'=>$key), false, $arNavStartParams, $arPriceSelectField);
						//while($arPrice = $dbRes2->Fetch())
						foreach($arPrices as $arPrice)
						{
							if($needPriceExt)
							{
								$elemKey = 'ICAT_PRICE'.$key.'_PRICE_EXT';
								$elemKey2 = $elemParamKey2 = 'ICAT_PRICE'.$key.'_PRICE';
								if($arParams['TYPE'] == 'OFFER') $elemParamKey2 = 'OFFER_'.$elemParamKey;
								$firstPrice = (bool)(!isset($arElement2[$elemKey]) || strlen($arElement2[$elemKey])==0);
								$arElement2[$elemKey] .= ($firstPrice ? "" : ";\r\n").implode(':', array($arPrice['QUANTITY_FROM'], $arPrice['QUANTITY_TO'], $arPrice['PRICE'], $arPrice['CURRENCY']));
								$arElement2[$elemKey2.'_'.$arPrice['QUANTITY_FROM'].'_'.$arPrice['QUANTITY_TO'].'_ORIG'] = $arPrice['PRICE'];
								$arElement2[$elemKey2.'_'.$arPrice['QUANTITY_FROM'].'_'.$arPrice['QUANTITY_TO']] = $this->GetConvertedPrice($arPrice['PRICE'], $arPrice['CURRENCY'], $elemParamKey2, $arElement2, $key);
								if(!$firstPrice) continue;
							}
							
							foreach($arPrice as $k=>$v)
							{
								$elemKey = $elemParamKey = 'ICAT_PRICE'.$key.'_'.$k;
								if($arParams['TYPE'] == 'OFFER') $elemParamKey = 'OFFER_'.$elemParamKey;
								if($k=='PRICE')
								{
									$arElement2[$elemKey.'_ORIG'] = $v;
									$v = $this->GetConvertedPrice($v, $arPrice['CURRENCY'], $elemParamKey, $arElement2, $key);
								}
								$arElement2[$elemKey] = $v;
							}
							
							if($arPrice['EXTRA_ID'])
							{
								if(!isset($this->catalogPriceExtra)) $this->catalogPriceExtra = array();
								if(!isset($this->catalogPriceExtra[$arPrice['EXTRA_ID']]))
								{
									$extraPercent = '';
									$dbRes = CExtra::GetList(array(), array('ID'=>$arPrice['EXTRA_ID']), false, array('nTopCount'=>1)/*, array('PERCENTAGE')*/);
									/*if($arExtra = $dbRes->Fetch())
									{
										$extraPercent = $arExtra['PERCENTAGE'];
									}
									$this->catalogPriceExtra[$arPrice['EXTRA_ID']] = $extraPercent;*/
									$arExtra = $dbRes->Fetch();
									$this->catalogPriceExtra[$arPrice['EXTRA_ID']] = $arExtra;
								}
								foreach($arPriceExtraSelectField as $v)
								{
									if($v=='EXTRA') $extraKey = 'PERCENTAGE';
									else $extraKey = substr($v, 6);
									$elemKey = 'ICAT_PRICE'.$key.'_'.$v;
									$arElement2[$elemKey] = $this->catalogPriceExtra[$arPrice['EXTRA_ID']][$extraKey];
								}
								/*$elemKey = 'ICAT_PRICE'.$key.'_EXTRA';
								$arElement2[$elemKey] = $this->catalogPriceExtra[$arPrice['EXTRA_ID']];*/
							}
						}
					}
				}

				if(!empty($arFieldsProductStores))
				{
					foreach($arFieldsProductStores as $key=>$arStoreSelectField)
					{
						$dbRes2 = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID'=>$arElement['ID'], 'STORE_ID'=>$key), false, array('nTopCount'=>1), $arStoreSelectField);
						if($arStore = $dbRes2->Fetch())
						{
							foreach($arStore as $k=>$v)
							{
								$elemKey = 'ICAT_STORE'.$key.'_'.$k;
								$arElement2[$elemKey] = $v;
							}
						}
					}
				}
				
				if(!empty($arFieldsDiscount))
				{
					$groupPriceId = array();
					$userGroups = array();
					$arSites = $this->GetIblockSite($arElement['IBLOCK_ID']);
					foreach($arFieldsDiscount as $fieldName)
					{
						$fieldKey = 'ICAT_DISCOUNT_'.$fieldName;
						$arSettings = $this->GetCFSettings($fieldKey);
						if(isset($arSettings['USER_GROUP']) && is_array($arSettings['USER_GROUP']) && !empty($arSettings['USER_GROUP']))
						{
							$userGroups = $arSettings['USER_GROUP'];
						}
						if(isset($arSettings['SITE_ID']) && $arSettings['SITE_ID'])
						{
							$arSites = array($arSettings['SITE_ID']);
						}
						if(isset($arSettings['GROUP_PRICE_ID']) && $arSettings['GROUP_PRICE_ID'])
						{
							$groupPriceId = array($arSettings['GROUP_PRICE_ID']);
						}
					}
					
					$basePrice = 0;
					if(count($groupPriceId) > 0)
					{
						if($arBasePrice = \Bitrix\Catalog\PriceTable::getList(array('filter'=>array('PRODUCT_ID'=>$arElement['ID'], 'CATALOG_GROUP_ID'=>$groupPriceId), 'select'=>array('PRICE'), 'limit'=>1))->Fetch())
						$basePrice = $arBasePrice['PRICE'];
					}
					else
					{
						$arBasePrice = \CPrice::GetBasePrice($arElement['ID']);
						$basePrice = $arBasePrice['PRICE'];
					}
					
					$arDiscountList = CCatalogDiscount::GetDiscount($arElement['ID'], $arElement['IBLOCK_ID'], $groupPriceId, $userGroups, "N", $arSites);
					$maxPercent = -999999;
					$maxIndex = -1;
					if(is_array($arDiscountList))
					{
						foreach($arDiscountList as $ind=>$arDiscount)
						{
							$percent = 0;
							if($arDiscount['VALUE_TYPE']=='P') $percent = $arDiscount['VALUE'];
							elseif($arDiscount['VALUE_TYPE']=='F' && $basePrice > 0) $percent = (1 - ($basePrice - $arDiscount['VALUE']) / $basePrice) * 100;
							elseif($arDiscount['VALUE_TYPE']=='S' && $basePrice > 0) $percent = (1 - ($arDiscount['VALUE']) / $basePrice) * 100;
							if((float)$percent!=0 && $percent > $maxPercent)
							{
								$maxPercent = $percent;
								$maxIndex = $ind;
							}
							if($arDiscount['LAST_DISCOUNT']=='Y') break;
						}
					}
					if($maxIndex >= 0)
					{
						$arDiscount = $arDiscountList[$maxIndex];
						foreach($arFieldsDiscount as $fieldName)
						{
							if($fieldName=='VALUE|VALUE_TYPE=P')
							{
								$val = '';
								if($arDiscount['VALUE_TYPE']=='P') $val = $arDiscount['VALUE'];
								elseif($arDiscount['VALUE_TYPE']=='F') $val = (1 - ($basePrice - $arDiscount['VALUE']) / $basePrice) * 100;
								elseif($arDiscount['VALUE_TYPE']=='S') $val = (1 - ($arDiscount['VALUE']) / $basePrice) * 100;
								$arElement2['ICAT_DISCOUNT_'.$fieldName] = ($val ? round((float)$val, 4) : '');
							}
							elseif($fieldName=='VALUE|VALUE_TYPE=F')
							{
								$val = '';
								if($arDiscount['VALUE_TYPE']=='P') $val = $basePrice * ($arDiscount['VALUE'] / 100);
								elseif($arDiscount['VALUE_TYPE']=='F') $val = $arDiscount['VALUE'];
								elseif($arDiscount['VALUE_TYPE']=='S') $val = $basePrice - $arDiscount['VALUE'];
								$arElement2['ICAT_DISCOUNT_'.$fieldName] = ($val ? round((float)$val, 4) : '');
							}
							elseif($fieldName=='VALUE|VALUE_TYPE=S')
							{
								$val = '';
								if($arDiscount['VALUE_TYPE']=='P') $val = $basePrice * (1 - $arDiscount['VALUE'] / 100);
								elseif($arDiscount['VALUE_TYPE']=='F') $val = $basePrice - $arDiscount['VALUE'];
								elseif($arDiscount['VALUE_TYPE']=='S') $val = $arDiscount['VALUE'];
								$arElement2['ICAT_DISCOUNT_'.$fieldName] = ($val ? round((float)$val, 4) : '');
							}
							elseif(isset($arDiscount[$fieldName]))
							{
								$arElement2['ICAT_DISCOUNT_'.$fieldName] = $arDiscount[$fieldName];
							}
						}
					}
				}
				
				if(!empty($arFieldsOrder))
				{
					if(in_array('PRODUCT_QNT', $arFieldsOrder) && class_exists('\Bitrix\Sale\Internals\BasketTable'))
					{
						$arOrderFilter = array('PRODUCT_ID'=>$arElement['ID'], '!ORDER_ID'=>false);
						if(isset($this->arFilterOrders)) $arOrderFilter = array_merge($arOrderFilter, $this->arFilterOrders);
						$arElement2['ICAT_ORDER_PRODUCT_QNT'] = 0;
						if($arOrderData = \Bitrix\Sale\Internals\BasketTable::GetList(array('filter'=>$arOrderFilter, 'select'=>array('QNT'=>new \Bitrix\Main\ORM\Fields\ExpressionField('QNT', 'SUM(%s)', 'QUANTITY'))))->Fetch())
						{
							$arElement2['ICAT_ORDER_PRODUCT_QNT'] = (int)$arOrderData['QNT'];
						}
					}
				}
				
				if(count($arFieldsCustom) > 0)
				{
					$arCF = \CKDAEEFieldList::GetCustomFields((int)$arElement['IBLOCK_ID']);
					foreach($arFieldsCustom as $arFieldCustom)
					{
						if(!isset($arCF[$arFieldCustom])) continue;
						$customField = $arCF[$arFieldCustom];
						$arElement2['CUSTOM_'.$arFieldCustom] = call_user_func_array(
							$customField['callback'],
							array($arElement['ID'])
						);
					}
				}

				
				if($arParams['TYPE'] == 'OFFER')
				{
					$arElement3 = $arElement2;
					$arElement2 = array();
					foreach($arElement3 as $k=>$v)
					{
						$arElement2['OFFER_'.$k] = $v;
					}
				}
				
				if($this->params['EXPORT_SECTIONS_ONE_CELL']=='Y')
				{
					$arElementSections = $this->GetElementSectionList($arElement2, $arElement, $arFieldsSections, $arElementNameFields, $arFilter, $arParams, $showOnlyFilterSection);
					foreach($arElementSections as $arElement3)
					{
						foreach($arElement3 as $k=>$v)
						{
							if(is_array($arElement2[$k])) $arElement2[$k] = implode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $arElement2[$k]);
							if(strlen($arElement2[$k]) > 0) $arElement2[$k] .= $this->params['ELEMENT_MULTIPLE_SEPARATOR'];
							$arElement2[$k] .= (is_array($v) ? implode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $v) : $v);
						}
					}
				}
				
				$needAdd = true;
				if(is_array($arOfferParams))
				{			
					$needSku = (bool)(count($arOfferParams['FILTER']) > 1);
					$cType = false;
					if(isset($arFilter['CATALOG_TYPE'])) $cType = $arFilter['CATALOG_TYPE'];
					elseif(isset($arFilter['=CATALOG_TYPE'])) $cType = $arFilter['=CATALOG_TYPE'];
					if($needSku && defined('\Bitrix\Catalog\ProductTable::TYPE_PRODUCT') && \Bitrix\Catalog\ProductTable::TYPE_PRODUCT==$arElement['CATALOG_TYPE'] && is_array($cType) && in_array(\Bitrix\Catalog\ProductTable::TYPE_PRODUCT, $cType))
					{
						$needSku = false;
					}
					$arOfferParams['FILTER']['PROPERTY_'.$offersPropertyId] = $arElement['ID'];
					$arOfferParams['PARENTFIELDS'] = $arElement2;
					if($this->params['EXPORT_OFFERS_JOIN']=='Y' || $this->params['EXPORT_PROPUCTS_JOIN']=='Y')
					{
						$arDataOffers = array();
						$arResElements2 = $this->GetElementsData($arDataOffers, $arOfferParams);
						
						if($this->params['EXPORT_OFFERS_JOIN']=='Y')
						{
							$arDataFields = array();
							foreach($arDataOffers as $arDataOffer)
							{
								foreach($arDataOffer as $k=>$v)
								{
									if(strpos($k, 'OFFER_')===0) 
									{
										if(!is_array($arDataFields[$k])) $arDataFields[$k] = array();
										if(is_array($v))
										{
											foreach($v as $v2) $arDataFields[$k][] = $v2;
										}
										else $arDataFields[$k][] = $v;
									}
								}
							}
							foreach($arDataFields as $k=>$v)
							{
								if(is_array($v))
								{
									if(strpos($k, 'OFFER_IP_PROP')===0) $v = array_diff(array_unique($v), array(''));
								}
								$arElement2[$k] = $v;
							}
						}
						elseif($this->params['EXPORT_PROPUCTS_JOIN']=='Y')
						{
							$arDataFields = array();
							foreach($arDataOffers as $arDataOffer)
							{
								foreach($arAllFields as $fieldName)
								{
									if(strpos($fieldName, 'OFFER_')===0 && !array_key_exists($fieldName, $arDataOffer)) $arDataOffer[$fieldName] = '';
								}
								foreach($arDataOffer as $k=>$v)
								{
									if(strpos($k, 'OFFER_')===0) 
									{
										if(!is_array($arDataFields[$k])) $arDataFields[$k] = array('TYPE'=>'MULTICELL');
										$arDataFields[$k][] = $v;
									}
								}
							}
							foreach($arDataFields as $k=>$v)
							{
								$arElement2[$k] = $v;
							}
						}
						if($arResElements2['navRecordCount'] == 0 && $needSku)
						{
							$needAdd = false;
						}
					}
					else
					{
						if(isset($this->params['MAX_PREVIEW_LINES']) && $this->params['MAX_PREVIEW_LINES'] > 0 && $this->params['EXPORT_ONE_OFFER_MIN_PRICE']!='Y' && $this->params['EXPORT_ONE_OFFER_MAX_PRICE']!='Y')
						{
							$arOfferParams['NAV_PARAMS']['nTopCount'] = $this->params['MAX_PREVIEW_LINES'];
						}
						if($this->params['EXPORT_OFFERS_UNDER_ELEM']=='Y')
						{
							$arData[] = $arElement2;
							$this->ProcessMoveFiles($arElement2);
							$arOfferParams['PARENTFIELDS'] = array();
							$needAdd = false;
						}

						$arResElements2 = $this->GetElementsData($arData, $arOfferParams);
						if($arResElements2['navRecordCount'] > 0 || $needSku)
						{
							$needAdd = false;
						}
					}
					unset($arOfferParams['FILTER']['PROPERTY_'.$offersPropertyId]);
				}
				
				if($needAdd)
				{
					$arElementSections = array();
					if($this->params['EXPORT_SECTIONS_ONE_CELL']!='Y')
					{
						$arElementSections = $this->GetElementSectionList($arElement2, $arElement, $arFieldsSections, $arElementNameFields, $arFilter, $arParams, $showOnlyFilterSection);
					}
					$arData[] = $arElement2;
					$this->ProcessMoveFiles($arElement2);
					foreach($arElementSections as $arElement3)
					{
						foreach($this->arConvFields as $k)
						{
							if(array_key_exists($k, $arElement2))
							{
								$arElement3['CONV|'.$k] = $arElement2[$k];
							}
						}
						$arData[] = $arElement3;
					}
				}
				$dbResCnt++;
				$dbElemResCnt++;
				
				if($arParams['NAV_PARAMS']['nTopCount'] && $dbResCnt >= $arParams['NAV_PARAMS']['nTopCount'])
				{
					$break = true;
					break;
				}
				elseif($arParams['TYPE'] != 'OFFER' && $this->CheckTimeEnding())
				{
					$this->stepparams['currentPageCnt'] = $curPageCnt;
					$break = true;
					break;
				}
			}
			
			if(($arParams['NAV_PARAMS']['iNumPage'] && $arParams['NAV_PARAMS']['iNumPage'] < $dbResElements->NavPageCount) || $this->stepparams['currentPageCnt'] > 0)
			{
				$sectionKeyInc = 1;
			}
			if($break || ($arParams['NAV_PARAMS']['iNumPage'] && $elemsCnt > 0)) break;
		}
		
		$navRecordCount = $dbResElements->NavRecordCount;
		$navPageCount = $dbResElements->NavPageCount;

		if(is_array($arOfferParams))
		{
			$arFilter2 = $arOfferParams['FILTER'];
			unset($arFilter2['PROPERTY_'.$offersPropertyId]);
			foreach($arFilterOriginal as $k=>$v)
			{
				$arFilter2['PROPERTY_'.$offersPropertyId.'.'.$k] = $v;
			}
			$cnt = CIblockElement::GetList(array(), $arFilter2, array());
			if($cnt > 0)
			{
				if(isset($arOfferParams['NAV_PARAMS']['nTopCount']) && $arOfferParams['NAV_PARAMS']['nTopCount'] < $cnt) $cnt = $arOfferParams['NAV_PARAMS']['nTopCount'];
				$navRecordCount = $cnt;
			}
		}
		
		/*free memory*/
		if(is_callable(array('\Bitrix\Iblock\InheritedProperty\ValuesQueue', 'deleteAll')))
		{
			\Bitrix\Iblock\InheritedProperty\ValuesQueue::deleteAll();
		}
		if(!empty($arFieldsDiscount))
		{
			\CCatalogDiscount::ClearDiscountCache(array(
			   'PRODUCT' => true,
			   'SECTIONS' => true,
			   'SECTION_CHAINS' => true,
			   'PROPERTIES' => true
			));
		}
		/*/free memory*/
		
		if($dbResCnt > $navRecordCount) $navRecordCount = $dbResCnt;
		return array(
			'navRecordCount' => $navRecordCount,
			'navPageCount' => $navPageCount,
			'sectionKey' => $skey + $sectionKeyInc,
			'sectionCount' => $sCount
		);
	}
	
	public function GetPropVal($val, $arProp)
	{
		if($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='HTML')
		{
			$arVal = \KdaIE\Utils::Unserialize($val);
			if(is_array($arVal) && isset($arVal['TEXT'])) $val = $arVal['TEXT'];
			elseif($arVal!==false) $val = '';
		}
		elseif($arProp['USER_TYPE']=='DateTime' || $arProp['USER_TYPE']=='Date')
		{
			if(!is_array($val) && strlen($val) > 0)
			{
				$time = strtotime($val);
				if($time!==false)
				{
					$val = ConvertTimeStamp($time, ($arProp['USER_TYPE']=='Date' ? 'PART' : 'FULL'));
				}
			}
		}
		return $val;
	}
	
	public function GetPropDesc($val, $arProp)
	{
		if($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='maxyss_unit')
		{
			$val = $this->GetMeasureVal($val);
		}
		return $val;
	}
	
	public function GetMeasureVal($val)
	{
		if(!$this->bCatalog) return $val;
		if(!isset($this->catalogMeasure) || !is_array($this->catalogMeasure))
		{
			$this->catalogMeasure = array();
			$dbRes = CCatalogMeasure::getList(array(), array());
			while($arr = $dbRes->Fetch())
			{
				$this->catalogMeasure[$arr['ID']] = ($arr['SYMBOL_RUS'] ? $arr['SYMBOL_RUS'] : $arr['SYMBOL_INTL']);
			}
		}
		return (array_key_exists($val, $this->catalogMeasure) ? $this->catalogMeasure[$val] : '');
	}
	
	public function GetElementSectionList(&$arElement2, $arElement, $arFieldsSections, $arElementNameFields, $arFilter, $arParams, $showOnlyFilterSection)
	{
		$arElementSections = array();
		if(!empty($arFieldsSections) && $arElement['IBLOCK_SECTION_ID'])
		{
			$onlyOneSection = (bool)($this->params['CSV_YANDEX']=='Y' || $this->params['EXPORT_ELEMENT_ONE_SECTION']=='Y');
			$mainSection = $arElement['IBLOCK_SECTION_ID'];
			if(!$showOnlyFilterSection && !$onlyOneSection)
			{
				$arIds = array();
				$dbRes = CIBlockElement::GetElementGroups($arElement['ID'], true, array('ID'));
				while($arSect = $dbRes->Fetch())
				{
					if(in_array($arSect['ID'], $arIds)) continue;
					$arIds[] = $arSect['ID'];
					if($arSect['ID']!=$mainSection)
					{
						$arElement3 = array();
						$arElement['IBLOCK_SECTION_ID'] = $arSect['ID'];
						$this->GetElementSection($arElement3, $arElement, $arElementNameFields, $arFieldsSections);
						if(isset($arElement3['IE_SECTION_PATH'])) unset($arElement3['IE_SECTION_PATH']);
						$arElementSections[] = $arElement3;
					}
				}
			}
			elseif($showOnlyFilterSection)
			{
				if(!in_array($mainSection, $arFilter['SECTION_ID']) || !$onlyOneSection)
				{
					$dbRes = CIBlockElement::GetElementGroups($arElement['ID'], true, array('ID'));
					while($arSect = $dbRes->Fetch())
					{
						if(in_array($arSect['ID'], $arFilter['SECTION_ID']) || ($arFilter['INCLUDE_SUBSECTIONS']=='Y' && count(array_intersect($this->GetSectWithParents($arSect['ID']), $arFilter['SECTION_ID'])) > 0))
						{
							$arElement3 = array();
							$arElement['IBLOCK_SECTION_ID'] = $arSect['ID'];
							$this->GetElementSection($arElement3, $arElement, $arElementNameFields, $arFieldsSections);
							if(isset($arElement3['IE_SECTION_PATH'])) unset($arElement3['IE_SECTION_PATH']);
							if($arSect['ID']==$mainSection) array_unshift($arElementSections, $arElement3);
							else $arElementSections[] = $arElement3;
						}
					}
					if(/*!in_array($mainSection, $arFilter['SECTION_ID']) && */!empty($arElementSections))
					{
						$arElement3 = array_shift($arElementSections);
						$arElement2 = array_merge($arElement2, $arElement3);
					}
					if($onlyOneSection) $arElementSections = array();
				}
			}
		}
		
		if(is_array($arParams['PARENTFIELDS']))
		{
			$arElement2 = array_merge($arElement2, $arParams['PARENTFIELDS']);
		}
		return $arElementSections;
	}
	
	public function GetProductDiscountPrice($productId, $catalogGroupId, $arUserGroups, $siteId, $arElem=array(), $arSettings=array())
	{
		/*\Bitrix\Catalog\Discount\DiscountManager::preloadPriceData(array($productId), array($key=>$key));
		$optimalPrice = \CCatalogProduct::GetOptimalPrice($productId, $catalogGroupId, $arUserGroups, 'N', array(), $siteId);
		$arPrice = array(
			'DISCOUNT_VALUE' => $optimalPrice['RESULT_PRICE']['DISCOUNT_PRICE'],
			'CURRENCY' => $optimalPrice['RESULT_PRICE']['CURRENCY']
		);
		return $arPrice;*/

		if($this->discountFromBasket)
		{
			$product = array(
				'ID' => $productId,
				'MODULE' => 'catalog',
			);

			$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
			$basketClass = $registry->getBasketClassName();
			$basket = $basketClass::create($siteId);
			$basketItem = $basket->createItem($product['MODULE'], $product['ID']);

			$priceRow = \Bitrix\Catalog\Discount\DiscountManager::getPriceDataByProductId($product['ID'], $catalogGroupId);
			
			if(isset($arSettings['PRICE_USE_VAT']) && $arSettings['PRICE_USE_VAT']=='Y' && $arElem['ICAT_VAT_INCLUDED']!='Y' && (float)$arElem['ICAT_VAT_ID'] > 0)
			{
				$priceRow['PRICE'] = $priceRow['PRICE']*(1+(float)$arElem['ICAT_VAT_ID']/100);
			}
			
			if(isset($arSettings['PRICE_CONVERT_CURRENCY']) && $arSettings['PRICE_CONVERT_CURRENCY']=='Y' && isset($arSettings['PRICE_CONVERT_CURRENCY_TO']) && $arSettings['PRICE_CONVERT_CURRENCY_TO'])
			{
				$priceRow['PRICE'] = CCurrencyRates::ConvertCurrency($priceRow['PRICE'], $priceRow['CURRENCY'], $arSettings['PRICE_CONVERT_CURRENCY_TO']);
				$priceRow['CURRENCY'] = $arSettings['PRICE_CONVERT_CURRENCY_TO'];
			}

			$fields = array(
				'PRODUCT_ID' => $product['ID'],
				'QUANTITY' => 1,
				'LID' => $siteId,
				'PRODUCT_PRICE_ID' => $priceRow['ID'],
				'PRICE' => $priceRow['PRICE'],
				'BASE_PRICE' => $priceRow['PRICE'],
				'DISCOUNT_PRICE' => 0,
				'CURRENCY' => $priceRow['CURRENCY'],
				'CAN_BUY' => 'Y',
				'DELAY' => 'N',
				'PRICE_TYPE_ID' => (int)$priceRow['CATALOG_GROUP_ID']
			);

			$basketItem->setFieldsNoDemand($fields);
			$discount = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\UserGroup($arUserGroups));
			$discount->setExecuteModuleFilter(array('all', 'catalog'));
			$discount->calculate();

			$calcResults = $discount->getApplyResult(true);
			if (isset($calcResults['PRICES']['BASKET']) && !empty($calcResults['PRICES']['BASKET'])) {
				$calcResults = current($calcResults['PRICES']['BASKET']);
				$calcResults['CURRENCY'] = $priceRow['CURRENCY'];
			} else {
				$calcResults = array();
			}
			return $calcResults;
		}
		else return false;		
	}
	
	public function GetConvertedPrice($v, $currency, $elemParamKey, $arElem=array(), $priceType=0)
	{
		if(is_array($elemParamKey) || empty($elemParamKey)) $arSettings = $elemParamKey;
		else $arSettings = $this->GetCFSettings($elemParamKey);
		if(strlen(trim($v)) > 0 && $this->bCurrency && is_array($arSettings))
		{
			if(strpos($elemParamKey, 'DISCOUNT')===false && isset($arSettings['PRICE_USE_VAT']) && $arSettings['PRICE_USE_VAT']=='Y' && $arElem['ICAT_VAT_INCLUDED']!='Y' && (float)$arElem['ICAT_VAT_ID'] > 0)
			{
				$v = $v*(1+(float)$arElem['ICAT_VAT_ID']/100);
			}
			if(isset($arSettings['PRICE_CONVERT_CURRENCY']) && $arSettings['PRICE_CONVERT_CURRENCY']=='Y' && isset($arSettings['PRICE_CONVERT_CURRENCY_TO']) && $arSettings['PRICE_CONVERT_CURRENCY_TO'])
			{
				$v = CCurrencyRates::ConvertCurrency($v, $currency, $arSettings['PRICE_CONVERT_CURRENCY_TO']);
				$currency = $arSettings['PRICE_CONVERT_CURRENCY_TO'];
			}
			$showCurrency = (bool)(isset($arSettings['PRICE_SHOW_CURRENCY']) && $arSettings['PRICE_SHOW_CURRENCY']=='Y');
			$useLangSettings = (bool)(isset($arSettings['PRICE_USE_LANG_SETTINGS']) && $arSettings['PRICE_USE_LANG_SETTINGS']=='Y');
			$arFormat = CCurrencyLang::GetCurrencyFormat($currency);
			$arFormat['FORMAT_STRING'] = html_entity_decode($arFormat['FORMAT_STRING'], ENT_QUOTES | ENT_HTML5);
			if($useLangSettings)
			{
				if(is_callable(array('\Bitrix\Catalog\Product\Price', 'roundPrice')) && $priceType > 0) $v = \Bitrix\Catalog\Product\Price::roundPrice($priceType, $v, $currency);
				$v = CCurrencyLang::CurrencyFormat($v, $currency);
				$v = html_entity_decode($v, ENT_QUOTES | ENT_HTML5);
				if($arFormat['HIDE_ZERO']=='Y' && $arFormat['DECIMALS'] > 0 && ($pos = mb_strpos($v, $arFormat['DEC_POINT'].str_repeat(0, (int)$arFormat['DECIMALS']))))
				{
					$len = mb_strlen($arFormat['DEC_POINT']) + (int)$arFormat['DECIMALS'];
					$v = mb_substr($v, 0, $pos).mb_substr($v, $pos+$len);
				}
				if(!$showCurrency)
				{
					$arParts = explode('#', $arFormat['FORMAT_STRING']);
					$part1 = current($arParts);
					$part2 = end($arParts);
					if(strlen($part1) > 0) $v = mb_substr($v, mb_strlen($part1));
					if(strlen($part2) > 0) $v = mb_substr($v, 0, -mb_strlen($part2));
				}
			}
			elseif($showCurrency)
			{
				$arParts = explode('#', $arFormat['FORMAT_STRING']);
				$part1 = current($arParts);
				$part2 = end($arParts);
				$v = $part1.$v.$part2;
			}
			$v = html_entity_decode($v, ENT_QUOTES | ENT_HTML5);
		}
		return $v;
	}
	
	public function GetContentTableData(&$arData, $arParams)
	{
		$this->stepparams['rows'][$this->listIndex] = 0;
		foreach($this->params['LIST_NAME'] as $listIndex=>$v)
		{
			if($this->listIndex==$listIndex) continue;
			$arData[] = array(
				'CT_SHEET_NUMBER' => $listIndex + 1,
				'CT_SHEET_NAME' => $v,
				'CT_SHEET_LINK' => "'".$v."'!A1",
				'CT_SHEET_POS_NUMBER' => (isset($this->stepparams['rows'][$listIndex]) ? $this->stepparams['rows'][$listIndex] : ''),
			);
			$this->stepparams['rows'][$this->listIndex]++;
		}
		return array(
			'navRecordCount' => $this->stepparams['rows'][$this->listIndex],
			'navPageCount' => 1,
			'sectionKey' => 2,
			'sectionCount' => 1
		);
	}
	
	public function GetSectionsData(&$arData, $arParams)
	{
		if(is_array($arParams['SECTION_FILTER']))
		{
			$arFilter = $arParams['SECTION_FILTER'];
		}
		else
		{
			$arFilter = $arParams['FILTER'];
			if(is_array($arFilter['SECTION_ID']) && in_array('0', $arFilter['SECTION_ID'])) $arFilter['SECTION_ID'][] = false;
			if($arFilter['INCLUDE_SUBSECTIONS']=='Y' && is_array($arFilter['SECTION_ID']) && count($arFilter['SECTION_ID']) > 0 && class_exists('\Bitrix\Iblock\SectionTable'))
			{
				$dbRes = \Bitrix\Iblock\SectionTable::GetList(array(
					'filter'=>array('ID'=>$arFilter['SECTION_ID']),
					'runtime' => array(new \Bitrix\Main\Entity\ReferenceField(
						'SECTION2',
						'\Bitrix\Iblock\SectionTable',
						array(
							'<=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
							'>=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN',
							'this.IBLOCK_ID' => 'ref.IBLOCK_ID'
						)
					)), 
					'select'=>array('SID'=>'SECTION2.ID'), 
					'order'=>array('SECTION2.DEPTH_LEVEL'=>'ASC')
				));
				while($arSection = $dbRes->Fetch())
				{
					if(!in_array($arSection['SID'], $arFilter['SECTION_ID'])) $arFilter['SECTION_ID'][] = $arSection['SID'];
				}
				unset($arFilter['INCLUDE_SUBSECTIONS']);
			}
		}
		$arSkuFilter = $arParams['SKU_FILTER'];
		$arNavParams = (is_array($arParams['NAV_PARAMS']) ? $arParams['NAV_PARAMS'] : false);
		$arAllFields = $arParams['FIELDS'];
		
		/*IE_SECTION_PATH*/
		$arFieldsSections = array();
		foreach($arAllFields as $field)
		{
			if(strpos($field, 'ISECT')===0)
			{
				$arSect = explode('_', substr($field, 5), 2);
				if(strlen($arSect[0])==0) $arSect[0] = 0;
				$arFieldsSections[$arSect[0]][] = $arSect[1];
			}
		}
		ksort($arFieldsSections);
		
		/*Fix for section 1 level*/
		/*if(count($arFieldsSections)==1 && isset($arFieldsSections[1]) && (!isset($arFilter['INCLUDE_SUBSECTIONS']) || $arFilter['INCLUDE_SUBSECTIONS']!='Y') && isset($arFilter['SECTION_ID']) && !isset($arFilter['ID']))
		{
			$arFilter['ID'] = $arFilter['SECTION_ID'];
			unset($arFilter['SECTION_ID']);
		}*/
		/*/Fix for section 1 level*/

		$arResult = $this->GetSectionsLevelData($arFilter, $arFieldsSections, $arNavParams);
		$dbResSections = $arResult['dbResSections'];
		$arSubData = $arResult['data'];
		foreach($arSubData as $data)
		{
			$arData[] = $data;
		}
		
		$navRecordCount = $dbResSections->NavRecordCount;
		$navPageCount = $dbResSections->NavPageCount;
		
		$sectionKey = 2;
		if($arNavParams['iNumPage'] && $arNavParams['iNumPage'] < $navPageCount)
		{
			$sectionKey = 1;
		}
		
		return array(
			'navRecordCount' => $navRecordCount,
			'navPageCount' => $navPageCount,
			'sectionKey' => $sectionKey,
			'sectionCount' => 1
		);
	}
	
	public function GetSectionsLevelData($arFilter, $arFieldsSections, $arNavParams)
	{
		$arData = array();
		$arKeys = array_keys($arFieldsSections);
		$currentKey = array_shift($arKeys);
		$arSelectField = $arFieldsSections[$currentKey];
		unset($arFieldsSections[$currentKey]);
		$arUserFields = $this->GetSectionUserFields($arFilter['IBLOCK_ID']);
		
		$arFilter2 = array_merge($arFilter, ($currentKey > 0 ? array('DEPTH_LEVEL'=>$currentKey) : array()));
		if((array_key_exists('<=LEFT_MARGIN', $arFilter2) || array_key_exists('>=RIGHT_MARGIN', $arFilter2)) && array_key_exists('DEPTH_LEVEL', $arFilter2) && class_exists('\Bitrix\Iblock\SectionTable'))
		{
			$arFilter3 = $arFilter2;
			$arFilter3 = array_diff_key($arFilter3, array_flip(preg_grep('/^\W*(SECTION_ID|INCLUDE_SUBSECTIONS)$/', array_keys($arFilter3))));
			if($arTmpSection = \Bitrix\Iblock\SectionTable::getList(array('filter'=>$arFilter3, 'select'=>array('ID'), 'limit'=>1))->Fetch())
			{
				$arFilter2['ID'] = $arTmpSection['ID'];
				if(array_key_exists('<=LEFT_MARGIN', $arFilter2)) unset($arFilter2['<=LEFT_MARGIN']);
				if(array_key_exists('>=RIGHT_MARGIN', $arFilter2)) unset($arFilter2['>=RIGHT_MARGIN']);
			}
		}
		$arOrder = $this->GetSectionOrder($arFilter['IBLOCK_ID']);
		$dbResSections = CIblockSection::GetList($arOrder, $arFilter2, (bool)(in_array('ELEMENT_CNT', $arSelectField)), array_merge($arSelectField, array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN')), $arNavParams);
		while($arSection = $dbResSections->GetNext())
		{
			$this->GetSectionIpropTemplates($arSection, $arSelectField);
			$arSectionData = array();
			foreach($arSection as $key=>$val)
			{
				if(in_array($key, $arSelectField))
				{
					if(isset($arSection['~'.$key]) && !is_array($arSection['~'.$key]) && strlen($arSection['~'.$key]) > 0) $val = $arSection['~'.$key];
					$key2 = 'ISECT'.($currentKey > 0 ? $currentKey : '').'_'.$key;
					$val = $this->GetSectionField($val, $key, $key2, $arUserFields);
					$arSectionData[$key2] = $val;
				}
			}

			$isSubData = false;
			$arFieldsSections2 = $arFieldsSections;
			if(!empty($arFieldsSections2) && $currentKey==0)
			{
				foreach($arFieldsSections2 as $k=>$v)
				{
					if($k > $arSection['DEPTH_LEVEL'])
					{
						unset($arFieldsSections2[$k]);
					}
				}
			}
			if(!empty($arFieldsSections2))
			{
				if($currentKey > 0)
				{
					$arFilter['>LEFT_MARGIN'] = $arSection['LEFT_MARGIN'];
					$arFilter['<RIGHT_MARGIN'] = $arSection['RIGHT_MARGIN'];
				}
				else
				{
					$arFilter['<=LEFT_MARGIN'] = $arSection['LEFT_MARGIN'];
					$arFilter['>=RIGHT_MARGIN'] = $arSection['RIGHT_MARGIN'];
				}
				$arResult = $this->GetSectionsLevelData($arFilter, $arFieldsSections2, false);
				$arSubData = $arResult['data'];
				if(!empty($arSubData))
				{
					$isSubData = true;
					foreach($arSubData as $data)
					{
						$arData[] = array_merge($arSectionData, $data);
					}
				}
			}
			if(!$isSubData)
			{
				$arData[] = $arSectionData;
			}
		}
		return array('dbResSections' => $dbResSections, 'data' => $arData);
	}
	
	public function GetSectionUserFields($IBLOCK_ID)
	{
		if(!$IBLOCK_ID) return array();
		if(!isset($this->sectionUserFields[$IBLOCK_ID]))
		{
			$arFields = array();
			$dbRes = CUserTypeEntity::GetList(array(), array('ENTITY_ID'=>'IBLOCK_'.$IBLOCK_ID.'_SECTION'));
			while($arr = $dbRes->Fetch())
			{
				$arFields[$arr['FIELD_NAME']] = $arr;
			}
			$this->sectionUserFields[$IBLOCK_ID] = $arFields;
		}
		return $this->sectionUserFields[$IBLOCK_ID];
	}
	
	public function GetElementOrder($IBLOCK_ID, $isOffer=false)
	{
		$arProps = $this->GetIblockProperties($IBLOCK_ID);
		$listIndex = $this->listIndex;
		$arOrder = array();
		$arSortParams = array_diff(explode(',', $this->params['SORT'.($isOffer ? '_OFFER' : '')][$listIndex]), array(''));
		foreach($arSortParams as $sortParam)
		{
			$arSort = array_map('trim', explode('=>', $sortParam));
			if($arSort[0] && in_array($arSort[0], $this->params['FIELDS_LIST'][$listIndex]))
			{
				$sortField = $arSort[0];
				if($isOffer && strpos($sortField, 'OFFER_')===0) $sortField = substr($sortField, 6);
				$sortOrder = (ToUpper($arSort[1])=='DESC' ? 'DESC' : 'ASC');
				if(strpos($sortField, 'IE_')===0)
				{
					$arOrder[substr($sortField, 3)] = $sortOrder;
				}
				elseif(strpos($sortField, 'IP_PROP')===0)
				{
					$propId = substr($sortField, 7);
					if($arProps[$propId]['PROPERTY_TYPE']=='E')
					{
						$arOrder['PROPERTY_'.$propId.'.NAME'] = $sortOrder;
					}
					elseif($arProps[$propId]['PROPERTY_TYPE']=='L')
					{
						$arOrder['PROPERTY_'.$propId.'_SORT'] = $sortOrder;
						$arOrder['PROPERTY_'.$propId.'_VALUE'] = $sortOrder;
					}
					else
					{
						$arOrder['PROPERTY_'.$propId] = $sortOrder;
					}
				}
				elseif(strpos($sortField, 'ICAT_PRICE')===0)
				{
					$arFieldParts = explode('_', substr($sortField, 10), 2);
					$arOrder['CATALOG_'.$arFieldParts[1].'_'.$arFieldParts[0]] = $sortOrder;
				}
				elseif(strpos($sortField, 'ICAT_')===0)
				{
					$arOrder['CATALOG_'.substr($sortField, 5)] = $sortOrder;
				}
				elseif(strpos($sortField, 'ISECT')===0)
				{
					$arOrder['IBLOCK_SECTION_ID'] = $sortOrder;
				}
			}
		}
		if(count($arOrder)==0) $arOrder = array('NAME'=>'ASC', 'ID'=>'ASC');
		if(!isset($arOrder['ID'])) $arOrder['ID'] = 'ASC';
		return $arOrder;
	}
	
	public function GetSectionOrder($IBLOCK_ID)
	{
		$arProps = $this->GetIblockProperties($IBLOCK_ID);
		$listIndex = $this->listIndex;
		$arOrder = array();
		$arSortParams = array_diff(explode(',', $this->params['SORT'][$listIndex]), array(''));
		foreach($arSortParams as $sortParam)
		{
			$arSort = array_map('trim', explode('=>', $sortParam));
			if($arSort[0] && in_array($arSort[0], $this->params['FIELDS_LIST'][$listIndex]))
			{
				$sortField = $arSort[0];
				$sortOrder = (ToUpper($arSort[1])=='DESC' ? 'DESC' : 'ASC');
				if(preg_match('/^ISECT\d*_/', $sortField, $m))
				{
					$arOrder[substr($sortField, strlen($m[0]))] = $sortOrder;
				}
			}
		}
		if(count($arOrder)==0) $arOrder = array('LEFT_MARGIN'=>'ASC');
		return $arOrder;
	}
	
	public function GetParentSections($ID, $IBLOCK_ID)
	{
		$arParentSections = array();
		$parentId = $ID;
		while($parentId)
		{
			$arSelectFields = array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'SECTION_PAGE_URL');
			$dbRes = CIblockSection::GetList(array(), array('ID'=>$parentId), false, $arSelectFields, array('nTopCount'=>1));
			if($arSection = $dbRes->GetNext())
			{
				foreach($arSection as $k=>$v)
				{
					if(strpos($k, '~')===0)
					{
						$arSection[substr($k, 1)] = $v;
					}
				}
				if($this->params['EXPORT_SECTION_URL']=='Y' /*|| $this->params['EXPORT_ADD_DOMAIN']=='Y'*/)
				{
					$arSection['SECTION_PAGE_URL'] = $this->AddUrlDomain($arSection['SECTION_PAGE_URL'], $IBLOCK_ID, true);
					$arSection['~SECTION_PAGE_URL'] = $this->AddUrlDomain($arSection['~SECTION_PAGE_URL'], $IBLOCK_ID, true);
				}
				else $arSection['SECTION_PAGE_URL'] = '';
				$arParentSections[$arSection['DEPTH_LEVEL']] = $arSection;
				$this->stepparams['parentSections'][$arSection['DEPTH_LEVEL']] = $arSection['ID'];
				if($arSection['DEPTH_LEVEL'] > 1 && (!isset($this->stepparams['parentSections'][$arSection['DEPTH_LEVEL'] - 1]) || $this->stepparams['parentSections'][$arSection['DEPTH_LEVEL'] - 1]!=$arSection['IBLOCK_SECTION_ID']))
				{
					$parentId = $arSection['IBLOCK_SECTION_ID'];
				}
				else
				{
					$parentId = false;
				}
			}
			else
			{
				$parentId = false;
			}
		}
		$arParentSections = array_reverse($arParentSections, true);
		return $arParentSections;
	}
	
	public function GetSectWithParents($ID)
	{
		if(!isset($this->sectWithParents)) $this->sectWithParents = array();
		if(!isset($this->sectWithParents[$ID]))
		{
			$arSections = array();
			$parentId = $ID;
			while($parentId)
			{
				$arSelectFields = array('ID', 'IBLOCK_SECTION_ID');
				$dbRes = CIblockSection::GetList(array(), array('ID'=>$parentId), false, $arSelectFields, array('nTopCount'=>1));
				if($arSection = $dbRes->Fetch())
				{
					$arSections[] = $arSection['ID'];
					if($arSection['IBLOCK_SECTION_ID'] > 0)
					{
						$parentId = $arSection['IBLOCK_SECTION_ID'];
					}
					else
					{
						$parentId = false;
					}
				}
				else
				{
					$parentId = false;
				}
			}
			$this->sectWithParents[$ID] = array_reverse($arSections);
		}
		return $this->sectWithParents[$ID];
	}
	
	public function GetSectionPath($ID)
	{
		if(!isset($this->sectionPaths[$ID]))
		{
			$curLevel = 1;
			$parentId = $ID;
			$arSectionNames = array();
			while($curLevel > 0)
			{
				$arSelectFields = array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL');
				$dbRes = CIblockSection::GetList(array(), array('ID'=>$parentId), false, $arSelectFields, array('nTopCount'=>1));
				if($arSection = $dbRes->Fetch())
				{
					$arSectionNames[$arSection['DEPTH_LEVEL']] = $arSection['NAME'];
					$parentId = (int)$arSection['IBLOCK_SECTION_ID'];
					$curLevel = (int)$arSection['DEPTH_LEVEL'];
				}
				else
				{
					$curLevel = 0;
				}
			}
			ksort($arSectionNames, SORT_NUMERIC);
			
			$separator = trim($this->params['DISPLAY_PARAMS'][$this->listIndex]['SECTION_PATH']['SECTION_PATH_SEPARATOR']);
			if(!$separator) $separator = '/';
			$separator = ' '.$separator.' ';			
			$this->sectionPaths[$ID] = implode($separator, $arSectionNames);
		}
		return $this->sectionPaths[$ID];
	}
	
	public function GetSelectSections($arFilter, $arParams)
	{
		$arSections = array();
		if($arParams['TYPE'] != 'OFFER')
		{
			if(!isset($this->sepSectionIds))
			{
				if($this->params['EXPORT_SEP_SECTIONS']=='Y')
				{
					$addFilter = array();
					if($this->params['EXPORT_INACTIVE_SECTIONS']!='Y') $addFilter['GLOBAL_ACTIVE'] = 'Y';
					$arSort = array('LEFT_MARGIN'=>'ASC');
					if($arFilter['SECTION_ID'] > 0 || (is_array($arFilter['SECTION_ID']) && count($arFilter['SECTION_ID']) > 0))
					{
						if($arFilter['INCLUDE_SUBSECTIONS']=='Y')
						{
							$dbResMain = CIblockSection::GetList($arSort, array_merge(array('IBLOCK_ID'=>$arFilter['IBLOCK_ID'], 'ID'=>$arFilter['SECTION_ID']), $addFilter), false, array('LEFT_MARGIN', 'RIGHT_MARGIN'));
							while($arMainSect = $dbResMain->Fetch())
							{
								$dbRes = CIblockSection::GetList($arSort, array_merge(array('IBLOCK_ID'=>$arFilter['IBLOCK_ID'], '>=LEFT_MARGIN'=>$arMainSect['LEFT_MARGIN'], '<=RIGHT_MARGIN'=>$arMainSect['RIGHT_MARGIN']), $addFilter), false, array('ID', 'NAME'));
								while($arr = $dbRes->Fetch()) $arSections[$arr['ID']] = $arr;
							}
						}
						else
						{
							$dbRes = CIblockSection::GetList($arSort, array_merge(array('IBLOCK_ID'=>$arFilter['IBLOCK_ID'], 'ID'=>$arFilter['SECTION_ID']), $addFilter), false, array('ID', 'NAME'));
							while($arr = $dbRes->Fetch()) $arSections[$arr['ID']] = $arr;
						}
					}
					else
					{
						$arElementSections = array();
						$dbRes = CIblockElement::GetList(array(), $arFilter, false, false, array('ID'));
						if($dbRes->SelectedRowsCount() < 10000)
						{
							$arElementSections = array(-1);
							$arElemIds = array();
							while($arr = $dbRes->Fetch())
							{
								$arElemIds[] = $arr['ID'];
							}
							if(!empty($arElemIds))
							{
								/*$dbRes = \Bitrix\Iblock\SectionTable::GetList(array(
									'filter'=>array('SECTION_ELEMENT.IBLOCK_ELEMENT_ID'=>$arElemIds),
									'runtime' => array(
										new \Bitrix\Main\Entity\ReferenceField(
											'SECTION_ELEMENT',
											'\Bitrix\Iblock\SectionElementTable',
											array(
												'=this.ID' => 'ref.IBLOCK_SECTION_ID'
											)
										),
										new \Bitrix\Main\Entity\ReferenceField(
											'SECTION2',
											'\Bitrix\Iblock\SectionTable',
											array(
												'>=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
												'<=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN',
												'this.IBLOCK_ID' => 'ref.IBLOCK_ID'
											)
										)
									), 
									'select'=>array('SID'=>'SECTION2.ID'), 
								));
								while($arSection = $dbRes->Fetch())
								{
									if(!in_array($arSection['SID'], $arElementSections)) $arElementSections[] = $arSection['SID'];
								}*/
								
								$arElemSect = array();
								$dbRes = \Bitrix\Iblock\SectionElementTable::GetList(array(
									'filter'=>array('IBLOCK_ELEMENT_ID'=>$arElemIds),
									'select'=>array('IBLOCK_SECTION_ID'),
									'group'=>array('IBLOCK_SECTION_ID')
								));
								while($arr = $dbRes->Fetch())
								{
									$arElemSect[] = $arr['IBLOCK_SECTION_ID'];
								}
								if(count($arElemSect) > 0)
								{
									$dbRes = \Bitrix\Iblock\SectionTable::GetList(array(
										'filter'=>array('ID'=>$arElemSect),
										'runtime' => array(
											new \Bitrix\Main\Entity\ReferenceField(
												'SECTION2',
												'\Bitrix\Iblock\SectionTable',
												array(
													'>=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
													'<=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN',
													'this.IBLOCK_ID' => 'ref.IBLOCK_ID'
												)
											)
										), 
										'select'=>array('SID'=>'SECTION2.ID'), 
									));
									while($arSection = $dbRes->Fetch())
									{
										if(!in_array($arSection['SID'], $arElementSections)) $arElementSections[] = $arSection['SID'];
									}
								}
							}
						}
						
						if(!empty($arElementSections)) $addFilter['ID'] = $arElementSections;
						$dbRes = CIblockSection::GetList($arSort, array_merge(array('IBLOCK_ID'=>$arFilter['IBLOCK_ID']), $addFilter), false, array('ID', 'NAME'));
						while($arr = $dbRes->Fetch()) $arSections[$arr['ID']] = $arr;
					}

					if(!empty($arSections) && strlen($this->params['EXPORT_SEP_SECTIONS_SORT']) > 0)
					{
						$arNewSections = array();
						$this->GetSectionsStruct($arNewSections, $arSections, $this->params['EXPORT_SEP_SECTIONS_SORT']);
						$arSections = $arNewSections;
					}
				}
				$arSections = array_values($arSections);
				$this->sepSectionIds = $arSections;
			}
			else
			{
				$arSections = $this->sepSectionIds;
			}
		}
		if(empty($arSections)) $arSections[] = array();
		return $arSections;
	}
	
	public function GetSectionsStruct(&$arNewSections, &$arSections, $sortBy='NAME', $parentId=0)
	{
		if(empty($arSections)) return;
		$arFilter = array('ID'=>array_keys($arSections));
		if($parentId > 0) $arFilter['SECTION_ID'] = $parentId;
		$dbRes = CIblockSection::GetList(array('DEPTH_LEVEL'=>'ASC', $sortBy=>'ASC'), $arFilter, false, array('ID', 'NAME', 'LEFT_MARGIN', 'RIGHT_MARGIN'));
		while($arr = $dbRes->Fetch())
		{
			if(!isset($arSections[$arr['ID']])) continue;
			$arNewSections[$arr['ID']] = $arSections[$arr['ID']];
			unset($arSections[$arr['ID']]);
			if($arr['RIGHT_MARGIN'] - $arr['LEFT_MARGIN'] > 1)
			{
				$this->GetSectionsStruct($arNewSections, $arSections, $sortBy, $arr['ID']);
			}
		}
	}
	
	public function GetElementSectionShare(&$arElement2, $arElement, $arElementNameFields, $arFieldsSections, $arFilter=array(), $showOnlyFilterSection=false)
	{
		$baseSectionId = $arElement['IBLOCK_SECTION_ID'];
		$arSectionIds = $arFilter['SECTION_ID'];
		if(!is_array($arSectionIds)) $arSectionIds = (strlen($arSectionIds) > 0 ? array($arSectionIds) : array());
		if(!$showOnlyFilterSection || in_array($baseSectionId, $arSectionIds) || ($arFilter['INCLUDE_SUBSECTIONS']=='Y' && count(array_intersect($this->GetSectWithParents($baseSectionId), $arSectionIds)) > 0))
		{
			$this->GetElementSection($arElement2, $arElement, $arElementNameFields, $arFieldsSections);
		}
		
		$needSectionPath = (bool)(in_array('SECTION_PATH', $arElementNameFields));
		if($needSectionPath && $arElement['IBLOCK_SECTION_ID'])
		{
			$arElement3 = $arElement2;
			if(is_callable(array('\Bitrix\Iblock\SectionElementTable', 'getList')))
			{
				$dbRes = \Bitrix\Iblock\SectionElementTable::getList(array('filter'=>array('IBLOCK_ELEMENT_ID'=>$arElement['ID'], 'ADDITIONAL_PROPERTY_ID'=>false), 'select'=>array('ID'=>'IBLOCK_SECTION_ID')));
			}
			else
			{
				$dbRes = CIBlockElement::GetElementGroups($arElement['ID'], true, array('ID'));
			}
			while($arSect = $dbRes->Fetch())
			{
				if($arSect['ID']!=$baseSectionId && (!$showOnlyFilterSection || in_array($arSect['ID'], $arSectionIds) || ($arFilter['INCLUDE_SUBSECTIONS']=='Y' && count(array_intersect($this->GetSectWithParents($arSect['ID']), $arSectionIds)) > 0)))
				{
					$arElement['IBLOCK_SECTION_ID'] = $arSect['ID'];
					$this->GetElementSection($arElement3, $arElement, $arElementNameFields, $arFieldsSections);
				}
			}
			$arElement2['IE_SECTION_PATH'] = $arElement3['IE_SECTION_PATH'];
		}
	}
	
	public function GetElementSection(&$arElement2, $arElement, $arElementNameFields, $arFieldsSections)
	{
		$needSectionPath = (bool)(in_array('SECTION_PATH', $arElementNameFields));
		if((!empty($arFieldsSections) || $needSectionPath) && $arElement['IBLOCK_SECTION_ID'])
		{
			$arUserFields = $this->GetSectionUserFields($arElement['IBLOCK_ID']);
			if($needSectionPath) $minLevel = 1;
			else $minLevel = max(min(array_keys($arFieldsSections)), 1);
			$curLevel = 0;
			$arSectionNames = array();
			/*$dbRes2 = CIblockSection::GetList(array(), array('ID'=>$arElement['IBLOCK_SECTION_ID']), false, array('ID', 'DEPTH_LEVEL'), array('nTopCount'=>1));
			if($arSection = $dbRes2->Fetch())*/
			if($arSection = $this->GetSectionFromCache(array('ID'=>$arElement['IBLOCK_SECTION_ID']), array('ID', 'DEPTH_LEVEL')))
			{
				$curLevel = $arSection['DEPTH_LEVEL'];
			}
			$elemLevel = $curLevel;
			if(isset($arFieldsSections[0]) && is_array($arFieldsSections[0]))
			{
				if(!isset($arFieldsSections[$elemLevel]) || !is_array($arFieldsSections[$elemLevel]))
				{
					$arFieldsSections[$elemLevel] = array();
				}
				$arFieldsSections[$elemLevel] = array_merge($arFieldsSections[0], $arFieldsSections[$elemLevel]);
			}
			$parentId = $arElement['IBLOCK_SECTION_ID'];
			while($curLevel >= $minLevel)
			{
				$arSelectFields = array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL');
				if(is_array($arFieldsSections[$curLevel])) $arSelectFields = array_merge($arSelectFields, $arFieldsSections[$curLevel]);				
				/*$dbRes2 = CIblockSection::GetList(array(), array('ID'=>$parentId, 'IBLOCK_ID'=>$arElement['IBLOCK_ID']), false, $arSelectFields, array('nTopCount'=>1));
				if($arSection = $dbRes2->GetNext())*/
				if($arSection = $this->GetSectionFromCache(array('ID'=>$parentId, 'IBLOCK_ID'=>$arElement['IBLOCK_ID']), $arSelectFields))
				{
					$this->GetSectionIpropTemplates($arSection, $arSelectFields);
					foreach($arSection as $k=>$v)
					{
						if(strpos($k, '~')===0)
						{
							$arSection[substr($k, 1)] = $v;
						}
					}
					if(is_array($arFieldsSections[$curLevel]))
					{
						foreach($arFieldsSections[$curLevel] as $key)
						{
							$val = $arSection[$key];
							$key2 = 'ISECT'.$arSection['DEPTH_LEVEL'].'_'.$key;
							$val = $this->GetSectionField($val, $key, ($elemLevel==$arSection['DEPTH_LEVEL'] ? array($key2, 'ISECT_'.$key) : $key2), $arUserFields);
							$arElement2[$key2] = $val;
							if($elemLevel==$arSection['DEPTH_LEVEL'])
							{
								$arElement2['ISECT_'.$key] = $val;
							}
						}
					}
					$arSectionNames[$curLevel] = $arSection['NAME'];
				}
				$parentId = (int)$arSection['IBLOCK_SECTION_ID'];
				$curLevel--;
			}
			if($needSectionPath && !empty($arSectionNames))
			{
				ksort($arSectionNames, SORT_NUMERIC);
				$separator = $this->GetCFSettings('IE_SECTION_PATH', 'SECTION_PATH_SEPARATOR', '/');
				if(!is_array($arElement2['IE_SECTION_PATH'])) $arElement2['IE_SECTION_PATH'] = array();
				$arElement2['IE_SECTION_PATH'][] = implode(' '.$separator.' ', $arSectionNames);
			}
		}
	}
	
	public function GetSectionField($val, $key, $key2, $arUserFields)
	{
		if($key=='SECTION_PAGE_URL')
		{
			$val = $this->AddUrlDomain($val);
		}
		elseif($key=='PICTURE' || $key=='DETAIL_PICTURE' || (isset($arUserFields[$key]) && $arUserFields[$key]['USER_TYPE_ID']=='file'))
		{
			if(is_array($val))
			{
				foreach($val as $k=>$v)
				{
					$val[$k] = $this->GetFileValue($val[$k], $key2);
				}
			}
			else
			{
				$val = $this->GetFileValue($val, $key2);
			}
		}
		elseif(isset($arUserFields[$key]))
		{
			$uField = $arUserFields[$key];
			if($uField['USER_TYPE_ID']=='enumeration')
			{
				$val = $this->GetUserFieldEnum($val, $arUserFields);
			}
			elseif($uField['USER_TYPE_ID']=='iblock_element'
				|| ($uField['USER_TYPE_ID']=='grain_link' && isset($uField['SETTINGS']['DATA_SOURCE']) && $uField['SETTINGS']['DATA_SOURCE']=='iblock_element'))
			{
				$relField = '';
				if(isset($this->fparamsByName[$this->listIndex]['ISECT_'.$key]['REL_ELEMENT_FIELD'])) $relField = $this->fparamsByName[$this->listIndex]['ISECT_'.$key]['REL_ELEMENT_FIELD'];
				if(strlen($relField) > 0)
				{
					$uField['ID'] = 'S'.$uField['ID'];
					$val = $this->GetPropertyElementValue($uField, $val, $relField);
				}
			}
		}
		return $val;
	}
	
	public function GetUserFieldEnum($val, $fieldParam)
	{
		if(is_array($val))
		{
			foreach($val as $k=>$v)
			{
				$val[$k] = $this->GetUserFieldEnum($v, $fieldParam);
			}
			return $val;
		}
		
		if(!isset($this->ufEnum)) $this->ufEnum = array();
		if(!$this->ufEnum[$fieldParam['ID']])
		{
			$arEnumVals = array();
			$fenum = new \CUserFieldEnum();
			$dbRes = $fenum->GetList(array(), array('USER_FIELD_ID'=>$fieldParam['ID']));
			while($arr = $dbRes->Fetch())
			{
				$arEnumVals[$arr['ID']] = $arr['VALUE'];
			}
			$this->ufEnum[$fieldParam['ID']] = $arEnumVals;
		}
		
		$val = trim($val);
		$arEnumVals = $this->ufEnum[$fieldParam['ID']];
		return $arEnumVals[$val];
	}
	
	public function GetSectionIpropTemplates(&$arSection, $arSelectFields)
	{
		$arIpropTempKeys = preg_grep('/^IPROP_TEMP_/', $arSelectFields);
		if(count($arIpropTempKeys) > 0) $arIpropTempKeys = array_map(array(__CLASS__, 'ReplaceSymbols11'), $arIpropTempKeys);
		$arIpropTempKeys2 = preg_grep('/^TEMPLATE_/', $arIpropTempKeys);
		$arIpropTempKeys = array_diff($arIpropTempKeys, $arIpropTempKeys2);
		if(count($arIpropTempKeys2) > 0) $arIpropTempKeys2 = array_map(array(__CLASS__, 'ReplaceSymbols9'), $arIpropTempKeys2);
		if(!empty($arIpropTempKeys))
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arSection['IBLOCK_ID'], $arSection['ID']);
			$arTemplates = $ipropValues->getValues();
			foreach($arIpropTempKeys as $v)
			{
				if(isset($arTemplates[$v])) $arSection['IPROP_TEMP_'.$v] = $arTemplates[$v];
				else $arSection['IPROP_TEMP_'.$v] = '';
			}
		}
		if(!empty($arIpropTempKeys2))
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($arSection['IBLOCK_ID'], $arSection['ID']);
			$arTemplates = $ipropValues->findTemplates();
			foreach($arIpropTempKeys2 as $v)
			{
				if(isset($arTemplates[$v])) $arSection['IPROP_TEMP_TEMPLATE_'.$v] = $arTemplates[$v]['TEMPLATE'];
				else $arSection['IPROP_TEMP_TEMPLATE_'.$v] = '';
			}
		}
		
		$sectPropKey = 'SECTION_PROPERTIES';
		if(in_array($sectPropKey, $arSelectFields) && class_exists('\Bitrix\Iblock\SectionPropertyTable'))
		{
			$arCodes = array();
			$dbRes = \Bitrix\Iblock\SectionPropertyTable::getList(array('select' => array('PROPERTY_ID'), 'filter' => array('=IBLOCK_ID' => $arSection['IBLOCK_ID'], 'SECTION_ID'=>$arSection['ID'])));
			while($arr = $dbRes->Fetch())
			{
				$arProp = $this->GetCachedProperty($arr['PROPERTY_ID']);
				$arCodes[] = (strlen($arProp['CODE']) > 0 ? $arProp['CODE'] : $arr['PROPERTY_ID']);
			}
			$arSection[$sectPropKey] = implode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $arCodes);
		}
		
		$sectPathKey = 'PATH_NAMES';
		if(in_array($sectPathKey, $arSelectFields))
		{
			$curLevel = $arSection['DEPTH_LEVEL'];
			$parentId = $arSection['IBLOCK_SECTION_ID'];
			$arSectionNames = array($curLevel=>$arSection['NAME']);
			while($curLevel >= 1)
			{
				$curLevel--;
				if($arSection2 = $this->GetSectionFromCache(array('ID'=>$parentId, 'IBLOCK_ID'=>$arSection['IBLOCK_ID']), array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL')))
				{
					$arSectionNames[$curLevel] = $arSection2['NAME'];
				}
				$parentId = (int)$arSection2['IBLOCK_SECTION_ID'];
			}
			ksort($arSectionNames, SORT_NUMERIC);
			$separator = $this->GetCFSettings('ISECT_PATH_NAMES', 'SECTION_PATH_SEPARATOR', ' / ');
			$arSection[$sectPathKey] = implode($separator, $arSectionNames);
		}
	}
	
	public function IsPictureField($field)
	{
		$isOffer = false;
		if(strpos($field, 'OFFER_')===0)
		{
			$field = substr($field, 6);
			$isOffer = true;
		}

		$isPicture = false;
		if(in_array($field, array('IE_PREVIEW_PICTURE', 'IE_DETAIL_PICTURE', 'IE_QR_CODE_IMAGE', 'ICAT_BARCODE_IMAGE')) || preg_match('/^ISECT\d*(_DETAIL)?_PICTURE$/', $field)) $isPicture = true;
		if(!$isPicture && strpos($field, 'IP_PROP')===0)
		{
			$propId = substr($field, 7);
			$arProp = $this->GetCachedProperty($propId);
			$isPicture = (bool)($arProp['PROPERTY_TYPE']=='F');
		}
		return $isPicture;
	}
	
	public function IsMultipleField($field, $IBLOCK_ID=0)
	{
		$isOffer = false;
		if(strpos($field, 'OFFER_')===0)
		{
			$field = substr($field, 6);
			$isOffer = true;
		}
		
		$isMultiple = false;
		if(in_array($field, array('IE_SECTION_PATH'))) $isMultiple = true;
		if(!$isMultiple)
		{
			if(strpos($field, 'IP_PROP')===0)
			{
				$propId = substr($field, 7);
				$arProp = $this->GetCachedProperty($propId);
				$isMultiple = (bool)($arProp['MULTIPLE']=='Y');
			}
			elseif(strpos($field, 'ISECT_UF_')===0)
			{
				$propId = substr($field, 6);
				$arProp = $this->GetSectionCachedProperty($propId, $IBLOCK_ID);
				$isMultiple = (bool)($arProp['MULTIPLE']=='Y');
			}
		}
		return $isMultiple;
	}
	
	public function GetCachedProperty($propId)
	{
		if(!isset($this->dataProps)) $this->dataProps = array();
		if(!isset($this->dataProps[$propId]))
		{
			$dbRes = CIBlockProperty::GetList(array(), array('ID'=>$propId));
			if($arProp = $dbRes->Fetch())
			{
				$this->dataProps[$propId] = $arProp;
			}
		}
		return $this->dataProps[$propId];
	}
	
	public function GetSectionCachedProperty($propId, $IBLOCK_ID)
	{
		if(!isset($this->dataSectionProps)) $this->dataSectionProps = array();
		if(!isset($this->dataSectionProps[$propId]))
		{
			$dbRes = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$IBLOCK_ID.'_SECTION', 'FIELD_NAME'=>$propId, 'LANG' => LANGUAGE_ID));
			if($arProp = $dbRes->Fetch())
			{
				$this->dataSectionProps[$propId] = $arProp;
			}
		}
		return $this->dataSectionProps[$propId];
	}
	
	public function GetCachedOfferIblock($IBLOCK_ID)
	{
		if(!$this->iblockoffers || !isset($this->iblockoffers[$IBLOCK_ID]))
		{
			$this->iblockoffers[$IBLOCK_ID] = CKDAExportUtils::GetOfferIblock($IBLOCK_ID, true);
		}
		return $this->iblockoffers[$IBLOCK_ID];
	}
	
	public function GetBasePriceId()
	{
		if(!$this->catalogBasePriceId)
		{
			$arBasePrice = CCatalogGroup::GetBaseGroup();
			$this->catalogBasePriceId = $arBasePrice['ID'];
		}
		return $this->catalogBasePriceId;
	}
	
	public function GetNumberOperation(&$val, $op)
	{
		if($op=='eq') return '=';
		elseif($op=='gt') return '>';
		elseif($op=='geq') return '>=';
		elseif($op=='lt') return '<';
		elseif($op=='leq') return '<=';
		elseif($op=='from_to')
		{
			$val = array_map('trim', explode('-', $val));
			return '><';
		}
		elseif($op=='empty')
		{
			$val = false;
			return '';
		}
		elseif($op=='not_empty')
		{
			$val = false;
			return '!';
		}
		else return '';
	}
	
	public function GetStringOperation(&$val, $op)
	{
		if($op=='eq') return '=';
		elseif($op=='neq') return '!=';
		elseif($op=='contain') return '%';
		elseif($op=='not_contain') return '!%';
		elseif($op=='logical') return '?';
		elseif($op=='empty')
		{
			$val = false;
			return '';
		}
		elseif($op=='not_empty')
		{
			$val = false;
			return '!';
		}
		else return '';
	}
	
	public function GetCalculatedValue($val)
	{
		try{
			if($this->params['ELEMENT_NOT_LOAD_FORMATTING']=='Y') $val = $val->getCalculatedValue();
			else $val = $val->getFormattedValue();
		}catch(Exception $ex){}
		return self::CorrectCalculatedValue($val);
	}
	
	public static function CorrectCalculatedValue($val)
	{
		$val = str_ireplace('_x000D_', '', $val);
		if((!defined('BX_UTF') || !BX_UTF) && CUtil::DetectUTF8($val)/*function_exists('mb_detect_encoding') && (mb_detect_encoding($val) == 'UTF-8')*/)
		{
			$val = strtr($val, array('Ø'=>'&#216;', '™'=>'&#153;', '®'=>'&#174;', '©'=>'&#169;'));
			$val = \Bitrix\Main\Text\Encoding::convertEncoding($val, "UTF-8", "Windows-1251");
		}
		return $val;
	}
	
	public function GetFloatVal($val, $precision=0)
	{
		if(is_array($val)) $val = current($val);
		$val = floatval(preg_replace('/[^\d\.\-]+/', '', str_replace(',', '.', $val)));
		if($precision > 0) $val = round($val, $precision);
		return $val;
	}
	
	public static function GetFloatRoundVal($val)
	{
		if(($ar = explode('.', $val)) && count($ar)>1){$val = round($val, strlen($ar[1]));}
		return $val;
	}
	
	public function GetDateVal($val)
	{
		$time = strtotime($val);
		if($time > 0)
		{
			return ConvertTimeStamp($time, 'FULL');
		}
		return false;
	}
	
	public function GetUserByGroups($arGroups)
	{
		if(empty($arGroups)) return 0;
		$xmlId = 'kda_groups_'.implode('_', $arGroups);
		
		if(!isset($this->usersByGroups)) $this->usersByGroups = array();
		if(!isset($this->usersByGroups[$xmlId]))
		{
			$userId = 0;
			$dbRes = \CUser::GetList(($by='ID'), ($order='ASC'), array('XML_ID'=>$xmlId), array('FIELDS'=>array('ID')));
			if($arUser = $dbRes->Fetch())
			{
				$userId = $arUser['ID'];
			}
			else
			{
				$pass = substr(md5(mt_rand()), 0, 8).'.*aA2';
				$arFieldsUser = array(
					'XML_ID' => $xmlId,
					'LOGIN' => $xmlId,
					'EMAIL' => $xmlId.'@nodomain.com',
					'PASSWORD' => $pass,
					'CONFIRM_PASSWORD' => $pass,
					'GROUP_ID' => $arGroups
				);
				if((string)\Bitrix\Main\Config\Option::get('main', 'new_user_phone_required')=='Y')
				{
					$dbRes = \CUser::GetList(($by='ID'), ($order='DESC'), array(), array('FIELDS'=>array('ID'), 'NAV_PARAMS'=>array('nTopCount'=>1)));
					$num = $dbRes->Fetch()['ID'] + 1;
					$arFieldsUser['PHONE_NUMBER'] = substr('+78000000000', 0, 12-strlen($num)).$num;
				}
				$user = new \CUser;
				if($id = $user->Add($arFieldsUser))
				{
					$userId = $id;
				}
			}
			$this->usersByGroups[$xmlId] = $userId;
		}
		return $this->usersByGroups[$xmlId];
	}
	
	public function GetUserField($ID, $field)
	{
		if(!$ID) return '';
		if(!isset($this->bUserFields)) $this->bUserFields = array();
		$fieldKey = $ID.'|'.$field;
		if(!isset($this->bUserFields[$fieldKey]))
		{
			$arFields = array_diff(array_map('trim', explode(' ', $field)), array(''));
			$arUser = \Bitrix\Main\UserTable::GetList(array('filter'=>array('ID'=>$ID), 'select'=>$arFields))->Fetch();
			$arVals = array();
			foreach($arFields as $subfield)
			{
				$arVals[] = $arUser[$subfield];
			}
			$this->bUserFields[$fieldKey] = implode(' ', $arVals);
		}
		return $this->bUserFields[$fieldKey];
	}
	
	public function GetSectionFromCache($arFilter=array(), $arSelect=array())
	{
		if($this->sectionCacheSize > 10*1024*1024)
		{
			$this->sectionCache = array();
			$this->sectionCacheSize = 0;
		}
		$hash = md5(serialize(array('FILTER'=>$arFilter, 'SELECT'=>$arSelect)));
		if(!array_key_exists($hash, $this->sectionCache))
		{
			if(!in_array('IBLOCK_ID', $arSelect)) $arSelect[] = 'IBLOCK_ID';
			if(class_exists('\Bitrix\Iblock\SectionTable') && count(array_diff($arSelect, array('ID', 'MODIFIED_BY', 'CREATED_BY', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'ACTIVE', 'GLOBAL_ACTIVE', 'SORT', 'NAME', 'PICTURE', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL', 'DESCRIPTION', 'DESCRIPTION_TYPE', 'SEARCHABLE_CONTENT', 'CODE', 'XML_ID', 'TMP_ID', 'DETAIL_PICTURE', 'SOCNET_GROUP_ID')))==0)
			{
				$dbRes = \Bitrix\Iblock\SectionTable::GetList(array('filter' => $arFilter, 'select'=> $arSelect, 'limit'=>1));
			}
			else
			{
				$dbRes = \CIblockSection::GetList(array(), $arFilter, false, $arSelect, array('nTopCount'=>1));
			}
			if(in_array('SECTION_PAGE_URL', $arSelect) && is_callable(array($dbRes, 'GetNext')))
			{
				$arSection = $dbRes->GetNext();
				//$arSection['SECTION_PAGE_URL'] = $this->AddUrlDomain($arSection['SECTION_PAGE_URL'], $arSection['IBLOCK_ID']);
				//$arSection['~SECTION_PAGE_URL'] = $this->AddUrlDomain($arSection['~SECTION_PAGE_URL'], $arSection['IBLOCK_ID']);
			}
			else $arSection = $dbRes->Fetch();
			$this->sectionCache[$hash] = $arSection;
			$this->sectionCacheSize += strlen($hash.serialize($arSection));
		}
		return $this->sectionCache[$hash];
	}
	
	public function AddDateFilter(&$arFilter, $arAddFilter, $field1, $field2, $addField, $isProp=false)
	{
		/*if(isset($arAddFilter[$addField.'_from_FILTER_PERIOD']) && in_array($arAddFilter[$addField.'from_FILTER_PERIOD'], array('day', 'week', 'month', 'quarter', 'year'))
			&& isset($arAddFilter[$addField.'_from_FILTER_DIRECTION']) && in_array($arAddFilter[$addField.'from_FILTER_PERIOD'], array('previous', 'current', 'next')))
		{}*/
		if($arAddFilter[$addField.'_from_FILTER_PERIOD']=='last_days'
			&& isset($arAddFilter[$addField.'_from_FILTER_LAST_DAYS']) && strlen(trim($arAddFilter[$addField.'_from_FILTER_LAST_DAYS'])) > 0)
		{
			$days = (int)trim($arAddFilter[$addField.'_from_FILTER_LAST_DAYS']);
			$arFilter[$field1] = $arAddFilter[$addField.'_from'] = ConvertTimeStamp(time()-$days*24*60*60, "FULL");
		}
		else
		{
			if(in_array($arAddFilter[$addField.'_from_FILTER_PERIOD'], array('day', 'week', 'month', 'quarter', 'year')) && in_array($arAddFilter[$addField.'_from_FILTER_DIRECTION'], array('previous', 'current', 'next')))
			{
				$time = time();
				$d1 = $d2 = (int)date('j', $time);
				$m1 = $m2 = (int)date('n', $time);
				$y1 = $y2 = (int)date('Y', $time);
				$x1 = $x2 = false;
				$ratio = 1;
				if($arAddFilter[$addField.'_from_FILTER_PERIOD']=='day')
				{
					$x1 = &$d1;
					$x2 = &$d2;
				}
				elseif($arAddFilter[$addField.'_from_FILTER_PERIOD']=='week')
				{
					$x1 = &$d1;
					$x2 = &$d2;
					$ratio = 7;
					$x1 = $x1 - (int)date('N', $time) + 1;
					$x2 = $x2 - (int)date('N', $time) + 7;
				}
				elseif($arAddFilter[$addField.'_from_FILTER_PERIOD']=='month')
				{
					$x1 = &$m1;
					$x2 = &$m2;
					$x2 = $x2 + 1;
					$d1 = 1;
					$d2 = 0;
				}
				elseif($arAddFilter[$addField.'_from_FILTER_PERIOD']=='quarter')
				{
					$x1 = &$m1;
					$x2 = &$m2;
					$ratio = 3;
					$q = ceil($x1/3);
					$x1 = ($q-1)*3 + 1;
					$x2 = ($q-1)*3 + 4;
					$d1 = 1;
					$d2 = 0;
				}
				elseif($arAddFilter[$addField.'_from_FILTER_PERIOD']=='year')
				{
					$x1 = &$y1;
					$x2 = &$y2;
					$d1 = 1;
					$d2 = 31;
					$m1 = 1;
					$m2 = 12;
				}
				if($arAddFilter[$addField.'_from_FILTER_DIRECTION']=='previous') {$x1 = $x1 - $ratio; $x2 = $x2 - $ratio;}
				elseif($arAddFilter[$addField.'_from_FILTER_DIRECTION']=='next') {$x1 = $x1 + $ratio; $x2 = $x2 + $ratio;}
				if($x1!==false)
				{
					$arAddFilter[$addField.'_from'] = ConvertTimeStamp(mktime(0, 0, 0, $m1, $d1, $y1), "PART");
					$arAddFilter[$addField.'_to'] = ConvertTimeStamp(mktime(0, 0, 0, $m2, $d2, $y2), "PART");
				}
			}
			if(!empty($arAddFilter[$addField.'_from'])) $arFilter[$field1] = $arAddFilter[$addField.'_from'];
			if(!empty($arAddFilter[$addField.'_to'])) $arFilter[$field2] = CIBlock::isShortDate($arAddFilter[$addField.'_to'])? ConvertTimeStamp(AddTime(MakeTimeStamp($arAddFilter[$addField.'_to']), 1, "D"), "FULL"): $arAddFilter[$addField.'_to'];
		}
		if($isProp)
		{
			if(!empty($arFilter[$field1])) $arFilter[$field1] = ConvertDateTime($arFilter[$field1], 'YYYY-MM-DD HH:MI:SS');
			if(!empty($arFilter[$field2])) $arFilter[$field2] = ConvertDateTime($arFilter[$field2], 'YYYY-MM-DD HH:MI:SS');
		}
	}
	
	public function GetCFSettings($f, $k=false, $default='')
	{
		$arSettings = array();
		if(isset($this->customFieldSettings[$f]))
		{
			$arSettings = $this->customFieldSettings[$f];
		}
		elseif(strpos($f, 'OFFER_')!==0 && isset($this->customFieldSettings['OFFER_'.$f]))
		{
			$arSettings = $this->customFieldSettings['OFFER_'.$f];
		}
		elseif(strpos($f, 'OFFER_')===0 && isset($this->customFieldSettings[substr($f, 6)]))
		{
			$arSettings = $this->customFieldSettings[substr($f, 6)];
		}
		if(!is_array($arSettings)) $arSettings = array();
		if($k!==false)
		{
			return (array_key_exists($k, $arSettings) ? $arSettings[$k] : $default);
		}
		else return $arSettings;
	}
	
	public function GetDefaultSite()
	{
		if(!isset($this->defaultSite) || !is_array($this->defaultSite))
		{
			if(!($arSite = \CSite::GetList(($by='sort'), ($order='asc'), array('DEFAULT'=>'Y'))->Fetch()))
				$arSite = \CSite::GetList(($by='sort'), ($order='asc'), array())->Fetch();
			$this->defaultSite = (is_array($arSite) ? $arSite : array());
		}
		return $this->defaultSite;
	}
	
	public function GetDefaultSiteId()
	{
		$arSite = $this->GetDefaultSite();
		return $arSite['ID'];
	}
	
	public function Str2Url($string, $arParams=array())
	{
		if(!is_array($arParams)) $arParams = array();
		if($arParams['TRANSLITERATION']=='Y')
		{
			if(isset($arParams['TRANS_LEN'])) $arParams['max_len'] = $arParams['TRANS_LEN'];
			if(isset($arParams['TRANS_CASE'])) $arParams['change_case'] = $arParams['TRANS_CASE'];
			if(isset($arParams['TRANS_SPACE'])) $arParams['replace_space'] = $arParams['TRANS_SPACE'];
			if(isset($arParams['TRANS_OTHER'])) $arParams['replace_other'] = $arParams['TRANS_OTHER'];
			if(isset($arParams['TRANS_EAT']) && $arParams['TRANS_EAT']=='N') $arParams['delete_repeat_replace'] = false;
		}
		return CUtil::translit($string, LANGUAGE_ID, $arParams);
	}
	
	public function GetSeparator($sep)
	{
		return strtr((string)$sep, array('\r'=>"\r", '\n'=>"\n", '\t'=>"\t"));
	}
	
	public static function GetDateForPath($m)
	{
		return date($m[1]);
	}
	
	public static function ReplaceSubCatalogStore($n)
	{
		return preg_replace("/^find_sub_el_catalog_store(\d+)_.*$/", "$1", $n);
	}
	
	public static function ReplaceSubCatalogPrice($n)
	{
		return preg_replace("/^find_sub_el_catalog_price_(\d+)$/", "$1", $n);
	}
	
	public static function ReplaceCatalogStore($n)
	{
		return preg_replace("/^find_el_catalog_store(\d+)_.*$/", "$1", $n);
	}
	
	public static function ReplaceCatalogPrice($n)
	{
		return preg_replace("/^find_el_catalog_price_(\d+)$/", "$1", $n);
	}
	
	public static function ReplaceSymbols11($k)
	{
		return substr($k, 11);
	}
	
	public static function ReplaceSymbols9($k)
	{
		return substr($k, 9);
	}
}
?>