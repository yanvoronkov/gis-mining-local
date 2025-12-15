<?php
require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\File\Image;
Loc::loadMessages(__FILE__);

class CKDAImportExcelBase {
	protected static $moduleId = 'esol.importexportexcel';
	protected static $moduleSubDir = 'import/';
	var $rcurrencies = array();
	var $notHaveTimeSetWorksheet = false;
	var $skipSepSection = false;
	var $skipSepProp = false;
	var $skipSepSectionLevels = array();
	var $arSectionNames = array();
	var $titlesRow = false;
	var $hintsRow = false;
	var $arTmpImageDirs = array();
	var $arTmpImages = array();
	var $extraConvParams = array();
	var $tagIblocks = array();
	var $offerParentId = null;
	var $errors = array();
	var $lastError = false;
	var $breakByEvent = false;
	var $defprops = array();
	var $cloudError = array();
	var $lastOffElemId = 0;
	var $isPacket = false;
	var $packetSize = 1000;
	var $arFieldColumns = array();
	var $dbFileExtIds = array();
	var $checkDiskSpace = false;
	
	function __construct($filename, $params)
	{
		$this->filename = $_SERVER['DOCUMENT_ROOT'].$filename;
		$this->memoryLimit = max(128*1024*1024, (int)CKDAImportUtils::GetIniAbsVal('memory_limit'));
		
		if(true /*$params['ELEMENT_DISABLE_EVENTS']*/)
		{
			$arEventTypes = array(
				'iblock' => array(
					'OnStartIBlockElementAdd',
					'OnBeforeIBlockElementAdd',
					'OnAfterIBlockElementAdd',
					'OnStartIBlockElementUpdate',
					'OnBeforeIBlockElementUpdate',
					'OnAfterIBlockElementUpdate',
					'OnAfterIBlockElementDelete',
					'OnIBlockElementSetPropertyValuesEx',
					'OnAfterIBlockElementSetPropertyValuesEx',
					'OnBeforeIBlockSectionAdd',
					'OnAfterIBlockSectionAdd',
					'OnBeforeIBlockSectionUpdate',
					'OnAfterIBlockSectionUpdate'
				),
				'catalog' => array(
					'OnPriceAdd',
					'OnPriceUpdate',
					'OnProductUpdate',
					'OnBeforeProductUpdate',
					'OnBeforeProductAdd',
					'ProductOnAfterUpdate',
					'ProductOnAfterAdd',
					'PriceOnAfterUpdate',
					'PriceOnAfterAdd',
					'OnBeforeStoreProductUpdate',
					'OnStoreProductUpdate',
					'OnStoreProductAdd',
					'\Bitrix\Catalog\Product::OnBeforeUpdate',
					'\Bitrix\Catalog\Product::onAfterUpdate',
					'\Bitrix\Catalog\Price::OnBeforeUpdate',
					'\Bitrix\Catalog\Price::onAfterUpdate',
					'Bitrix\Catalog\Model\Product::OnBeforeAdd', //askaron.pro1c (without slash)
					'Bitrix\Catalog\Model\Product::OnBeforeUpdate', //askaron.pro1c (without slash)
				),
				'search' => array(
					'BeforeIndex'
				)
			);
			foreach($arEventTypes as $mod=>$arModuleTypes)
			{
				foreach($arModuleTypes as $eventType)
				{
					foreach(GetModuleEvents($mod, $eventType, true) as $eventKey=>$arEvent)
					{
						if(isset($arEvent['TO_MODULE_ID']))
						{
							if($arEvent['TO_MODULE_ID']=='catalog') continue;
							if($arEvent["TO_MODULE_ID"]!='main') \Bitrix\Main\Loader::includeModule($arEvent["TO_MODULE_ID"]);
						}
						if($params['ELEMENT_DISABLE_EVENTS']
							|| (isset($arEvent['CALLBACK']) && is_array($arEvent['CALLBACK']) && !is_callable($arEvent['CALLBACK']))
							|| (isset($arEvent['TO_CLASS']) && isset($arEvent['TO_METHOD']) && !is_callable(array($arEvent['TO_CLASS'], $arEvent['TO_METHOD']))))
						{
							RemoveEventHandler($arEvent['FROM_MODULE_ID'], $arEvent['MESSAGE_ID'], $eventKey);
						}
					}
				}
			}
		}
	}
	
	public function CheckTimeEnding($time = 0)
	{
		/*if(!$this->params['MAX_EXECUTION_TIME'])
		{
			if(!isset($this->timeStepBegin)) $this->timeStepBegin = $time;
			if(time()-$this->timeStepBegin > 10)
			{
				usleep(10000000);
				$this->timeStepBegin = time();
			}
		}*/
		if($time==0) $time = $this->timeBeginImport;
		$this->ClearIblocksTagCache(true);
		return ($this->params['MAX_EXECUTION_TIME'] && (time()-$time >= $this->params['MAX_EXECUTION_TIME'] || $this->memoryLimit - memory_get_peak_usage() < 2097152));
	}
	
	public function GetRemainingTime()
	{
		if(!$this->params['MAX_EXECUTION_TIME']) return 600;
		else return ($this->params['MAX_EXECUTION_TIME'] - (time() - $this->timeBeginImport));
	}
	
	public function GetNextImportFile()
	{
		//if($this->stepparams['worksheetCurrentRow']==0 && (!isset($this->worksheetCurrentRow) || $this->worksheetCurrentRow==0)) return false;
		$page = ++$this->stepparams['api_page'];
		//if($this->stepparams['api_page'] > 3) return false;
		if(array_key_exists('EXT_DATA_FILE', $this->params) && ($fid = \CKDAImportUtils::GetNextImportFile($this->params['EXT_DATA_FILE'], $page, $this->params['URL_DATA_FILE'], $this->pid)))
		{
			\CFile::Delete($this->params['DATA_FILE']);
			$arFile = \CKDAImportUtils::GetFileArray($fid);
			$filename = $arFile['SRC'];
			$this->filename = $_SERVER['DOCUMENT_ROOT'].$filename;
			$this->params['URL_DATA_FILE'] = $filename;
			$this->params['DATA_FILE'] = $fid;
			$oProfile = \CKDAImportProfile::getInstance()->UpdatePartSettings($this->pid, array('DATA_FILE'=>$fid, 'URL_DATA_FILE'=>$filename, 'OLD_FILE_SIZE'=>(int)filesize($this->filename)));
			$this->worksheetCurrentRow = $this->stepparams['worksheetCurrentRow'] = 0;
			$this->worksheetNum = $this->stepparams['worksheetNum'] = 0;
			if(isset($this->stepparams['csv_position'])) unset($this->stepparams['csv_position']);
			return true;
		}
		return false;
	}
	
	public function AddTmpFile($fileOrig, $file)
	{
		$ext = ToLower(CKDAImportUtils::GetFileExtension($file));
		if($ext && !in_array($ext, $this->imgExts)) return;
		if(!array_key_exists($fileOrig, $this->arTmpImages)) $this->arTmpImages[$fileOrig] = array('file'=>$file, 'size'=>filesize($file));
	}
	
	public function GetTmpFile($fileOrig, $bAdd=false)
	{
		if($bAdd)
		{
			$file = $this->CreateTmpImageDir().bx_basename($fileOrig);
			copy($fileOrig, $file);
			$this->AddTmpFile($fileOrig, $file);
		}
		if(array_key_exists($fileOrig, $this->arTmpImages))
		{
			$fn = $this->arTmpImages[$fileOrig]['file'];
			if(!file_exists($fn)) $fn = \Bitrix\Main\IO\Path::convertLogicalToPhysical($fn);
			$i = 0;
			$newFn = '';
			while(($i++)==0 || (file_exists($newFn)))
			{
				if($i > 10) return false;
				$newFn = (preg_match('/\.[^\/\.]*$/', $fn) ? preg_replace('/(\.[^\/\.]*)$/', '__imp'.mt_rand().'imp__$1', $fn) : $fn.'__imp'.mt_rand().'imp__');
			}
			if(copy($fn, $newFn)) return $newFn;
		}
		if($bAdd) return $fileOrig;
		return false;
	}
	
	public function CreateTmpImageDir()
	{
		$tmpsubdir = $this->imagedir.($this->filecnt++).'/';
		CheckDirPath($tmpsubdir);
		$this->arTmpImageDirs[] = $tmpsubdir;
		return $tmpsubdir;
	}
	
	public function RemoveTmpImageDirs()
	{
		if(count($this->arTmpImageDirs) > 20 || count($this->arTmpImages) > 20)
		{
			foreach($this->arTmpImageDirs as $k=>$v)
			{
				DeleteDirFilesEx(substr($v, strlen($_SERVER['DOCUMENT_ROOT'])));
			}
			$this->arTmpImageDirs = array();
			$this->arTmpImages = array();
		}
	}
	
	public static function MakeFileArray($p)
	{
		$a = \CFile::MakeFileArray($p);
		if(is_array($a) && isset($a['tmp_name'])) $a['tmp_name'] = preg_replace('#/{2,}#', '/', $a['tmp_name']);
		return is_array($a) ? $a : array();
	}
	
	public function AddDiskSpaceError()
	{
		if(!$this->checkDiskSpace)
		{
			$this->errors[] = Loc::getMessage("KDA_IE_FILE_ERROR_CHECK_DISK_SPACE");
			$this->checkDiskSpace = true;
		}
	}
	
	public function GetFileArray($file, $arParams=array(), $fieldName='', $oldId=0, $origVal=false)
	{
		$file = $this->Trim($file);
		$attempts = 3;
		while($attempts > 0)
		{
			try{
				$arFile = self::GetFileArrayDirect($file, $arParams, $fieldName, $oldId);
				$attempts = 0;
			}catch(\Exception $ex){
				$this->GetFileArrayCatch($attempts, $ex, $file);
			}catch(\Error $ex){
				$this->GetFileArrayCatch($attempts, $ex, $file);
			}
		}
		
		if($origVal===false && empty($arFile) && strlen($file) > 0 && $file!='-' && strpos($file, $this->params['ELEMENT_MULTIPLE_SEPARATOR'])===false)
		{
			$this->logger->AddFileError($file, $this->lastFileError);
			if($this->lastFileError)
			{
				$errorText = '';
				if(preg_match('/^STATUS_(\d+)$/', $this->lastFileError, $m))
				{
					$errorText = sprintf(Loc::getMessage("KDA_IE_FILE_ERROR"), $file).' '.sprintf(Loc::getMessage("KDA_IE_FILE_ERROR_STATUS_X"), $m[1]);
				}
				elseif(Loc::getMessage("KDA_IE_FILE_ERROR_".$this->lastFileError))
				{
					$arUrl = parse_url($file);
					$domain = $arUrl['scheme'].'://'.$arUrl['host'];
					$errorText = sprintf(Loc::getMessage("KDA_IE_FILE_ERROR"), $file).' '.sprintf(Loc::getMessage("KDA_IE_FILE_ERROR_".$this->lastFileError), $domain);
				}
				if(strlen($errorText) > 0 && !in_array($errorText, $this->errors)) $this->errors[] = $errorText;
			}
		}
		elseif($arFile && $origVal!==false)
		{
			$this->logger->RemoveFileError($origVal);
			foreach($this->errors as $k=>$v)
			{
				if(strpos($v, $origVal)!==false)
				{
					unset($this->errors[$k]);
				}
			}
		}
		return $arFile;
	}
	
	public function GetFileArrayCatch(&$attempts, $ex, $file)
	{
		$attempts--;
		if($attempts==0)
		{
			$mess = ToLower($ex->getMessage());
			$extraMess = '';
			if(!$this->checkDiskSpace && (strpos($mess, 'is not a valid file-handle resource')!==false || strpos($mess, 'cannot open the file')!==false))
			{
				$needSize = 5*1024*1024;
				$tempPath = \CFile::GetTempName('', 'test_size.txt');
				CheckDirPath($tempPath);
				file_put_contents($tempPath, str_repeat('0', $needSize));
				if($needSize > filesize($tempPath))
				{
					$this->AddDiskSpaceError();
				}
				unlink($tempPath);
				$dir = dirname($tempPath);
				if(file_exists($dir) && ($dirFiles = scandir($dir)) && count(array_diff($dirFiles, array('.', '..')))==0)
				{
					rmdir($dir);
				}
			}
			
			$this->errors[] = sprintf(Loc::getMessage("KDA_IE_FILE_ERROR"), $file).'<br>['.$ex->getMessage().']';
		}
	}
	
	public function GetFileArrayDirect($file, $arParams=array(), $fieldName='', $oldId=0)
	{
		$this->lastFileError = '';
		$fieldSettings = $this->GetShareFieldSettings($fieldName);
		$saveOriginalPath = (bool)($fieldSettings['SAVE_ORIGINAL_PATH']=='Y');
		$bMultiple = (bool)($arParams['MULTIPLE']=='Y');
		$isTmpFile = false;
		$checkSubdirs = true;
		$dirname = '';
		$fileOrig = $file;
		$file = str_replace('\\', '/', $file);
		if(strpos($file, '//')===0) $file = 'http:'.$file;
		elseif(preg_match('/^([\w\-\p{Cyrillic}]+\.[\w\-\p{Cyrillic}\.]+)\//ui', trim($file), $m) && !file_exists($_SERVER['DOCUMENT_ROOT'].$m[1])) $file = 'http://'.$file;
		if(preg_match('/^([^\/]*)#(http.*)$/', $file, $m) && substr_count($file, 'http')==1){$file = $m[2].'#filename='.$m[1];}
		$fileTypes = array();
		$bNeedImage = (bool)($arParams['FILETYPE']=='IMAGE');
		$checkFormat = false;
		if($bNeedImage) $fileTypes = $this->imgExts;
		elseif($arParams['FILE_TYPE'])
		{
			$fileTypes = array_diff(array_map('trim', explode(',', ToLower($arParams['FILE_TYPE']))), array(''));
			$checkFormat = true;
		}
		$extractZip = (bool)(!empty($fileTypes) && !in_array('zip', $fileTypes));
		//$correctZipEncoding = false;
		
		if(strlen($file)==0)
		{
			return array();
		}
		elseif($file=='-')
		{
			return array('del'=>'Y');
		}
		elseif($oldId = $this->GetOldIdImageByPath($oldId, $fileOrig))
		{
			if(is_array($oldId))
			{
				$arFiles = array();
				foreach($oldId as $vid)
				{
					$arFiles[] = array('name'=>'', 'old_id'=>$vid);
				}
				return $arFiles;
			}
			return array('name'=>'', 'old_id'=>$oldId);
		}
		elseif(preg_match_all('/<img[^>]*src=["\']([^"\']+)["\']/', $file, $m))
		{
			if($bMultiple)
			{
				$arFiles = array();
				foreach($m[1] as $k=>$v)
				{
					$arFiles[$k] = self::GetFileArray($v, $arParams, $fieldName);
				}
				return array('VALUES'=>$arFiles);
			}
			else return self::GetFileArray($m[1][0], $arParams, $fieldName);
		}
		elseif(!$saveOriginalPath && (!$bMultiple || strpos($fileOrig, '#')===false) && ($tmpFile = $this->GetTmpFile($fileOrig)))
		{
			$file = $tmpFile;
			$isTmpFile = true;
		}
		elseif($tmpFile = $this->GetFileFromArchive($fileOrig))
		{
			$file = $tmpFile;
			if($this->PathContainsMask($file)) $dirname = $file;
		}
		elseif(strpos($file, '/')===0 || (strpos($file, '://')===false && strpos($file, '/')!==false))
		{
			$basename = '';
			if(preg_match('/#filename=([^#]+)/is', $file, $m))
			{
				$file = str_replace($m[0], '', $file);
				$basename = $m[1];
			}
			$file = '/'.ltrim($file, '/');
			$file = \Bitrix\Main\IO\Path::convertLogicalToPhysical($file);
			if(is_dir($_SERVER['DOCUMENT_ROOT'].$file))
			{
				$file = $_SERVER['DOCUMENT_ROOT'].$file;
			}
			else
			{
				if($this->PathContainsMask($file) && !file_exists($file) && !file_exists($_SERVER['DOCUMENT_ROOT'].$file))
				{
					$arFiles = $this->GetFilesByMask($file);
					if($bMultiple && count($arFiles) > 1)
					{
						foreach($arFiles as $k=>$v)
						{
							$arFiles[$k] = self::GetFileArray($v, $arParams, $fieldName);
						}
						return array('VALUES'=>$arFiles);
					}
					elseif(count($arFiles) > 0)
					{
						$tmpfile = current($arFiles);
						return self::GetFileArray($tmpfile, $arParams, $fieldName);
					}
				}
				
				$arFile = self::MakeFileArray(current(explode('#', $file)));
				/*Try search other register*/
				if(strlen($arFile['tmp_name'])==0 && preg_match('/\.[\w\d]{2,5}$/', $file))
				{
					$newFile = '';
					$fileDir = dirname($file);
					if(file_exists($_SERVER['DOCUMENT_ROOT'].$fileDir))
					{
						$fileName = ToLower(basename($file));
						$i = 0;
						$dh = opendir($_SERVER['DOCUMENT_ROOT'].$fileDir);
						while($i++ < 5000 && strlen($newFile)==0 && (false !== ($fn = readdir($dh))))
						{
							if($fileName==ToLower($fn)) $newFile = $fileDir.'/'.$fn;
						}
						closedir($dh);
						if(strlen($newFile) > 0)
						{
							$file = $newFile;
							$arFile = self::MakeFileArray($file);
						}
					}
				}
				/*/Try search other register*/
				if(!is_array($arFile) || strlen($arFile['name'])==0) return array();
				if($saveOriginalPath)
				{
					$arFile['SAVE_ORIGINAL_PATH'] = 'Y';
					return $arFile;
				}
				if(strlen($basename) > 0) $arFile['name'] = $basename;
				$tmpsubdir = $this->CreateTmpImageDir();
				$file = $tmpsubdir.$arFile['name'];
				copy($arFile['tmp_name'], $file);
			}
		}
		elseif(strpos($file, 'zip://')===0)
		{
			$tmpsubdir = $this->CreateTmpImageDir();
			$oldfile = $file;
			$file = $tmpsubdir.basename($oldfile);
			copy($oldfile, $file);
		}
		elseif(preg_match('/ftp(s)?:\/\//', $file))
		{
			$tmpsubdir = $this->CreateTmpImageDir();
			$arFile = $this->sftp->MakeFileArray($file, $arParams);
			if($bMultiple && is_array($arFile) && array_key_exists('0', $arFile))
			{
				$arFiles = array();
				foreach($arFile as $subfile)
				{
					$arFiles[] = $this->GetFileArray($subfile, $arParams, $fieldName);
				}
				return array('VALUES'=>$arFiles);
			}
			if(is_array($arFile) && strlen($arFile['tmp_name']) > 0)
			{
				$file = $tmpsubdir.$arFile['name'];
				copy($arFile['tmp_name'], $file);
			}
		}
		elseif($service = $this->cloud->GetService($file))
		{
			$tmpsubdir = $this->CreateTmpImageDir();
			if($arFile = $this->cloud->MakeFileArray($service, $file, $arParams))
			{
				if($arFile['ERROR_MESSAGE'])
				{
					if(!$this->cloudError[$service])
					{
						$this->errors[] = sprintf(Loc::getMessage("KDA_IE_CUSTOM_ERROR"), $arFile['ERROR_MESSAGE'], $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
						$this->cloudError[$service] = true;
					}
					return false;
				}
				$extractZip = (bool)($extractZip || $this->cloud->NeedZipExtract());
				//$correctZipEncoding = (bool)($this->cloud->NeedZipExtract());
				if(is_array($arFile) && count(preg_grep('/^\d+$/', array_keys($arFile))) > 0)
				{
					$arFiles = $arFile;
					if($bMultiple && count($arFiles) > 1)
					{
						foreach($arFiles as $k=>$v)
						{
							$arFiles[$k] = self::GetFileArray($v, $arParams, $fieldName);
						}
						return array('VALUES'=>$arFiles);
					}
					elseif(count($arFiles) > 0)
					{
						$tmpfile = current($arFiles);
						return self::GetFileArray($tmpfile, $arParams, $fieldName);
					}
				}
				$file = $tmpsubdir.$arFile['name'];
				copy($arFile['tmp_name'], $file);
				$checkSubdirs = 1;
			}
			$this->CheckFileTimeout($fieldSettings);
		}
		elseif(preg_match('/http(s)?:\/\//', $file))
		{
			$file = rawurldecode($file);
			$arUrl = parse_url($file);
			//Cyrillic domain
			if(preg_match('/[^A-Za-z0-9\-\.]/', $arUrl['host']))
			{
				if(!class_exists('idna_convert')) require_once(dirname(__FILE__).'/../../lib/idna_convert.class.php');
				if(class_exists('idna_convert'))
				{
					$idn = new idna_convert();
					$oldHost = $arUrl['host'];
					if(!CUtil::DetectUTF8($oldHost)) $oldHost = CKDAImportUtils::Win1251Utf8($oldHost);
					$file = str_replace($arUrl['host'], $idn->encode($oldHost), $file);
				}
			}
			if(class_exists('\Bitrix\Main\Web\HttpClient'))
			{
				$tmpsubdir = $this->CreateTmpImageDir();
				$basename = preg_replace('/[\?#].*$/', '', bx_basename($file));
				$fixedBasename = false;
				if(preg_match('/#filename=([^#]+)/is', $file, $m))
				{
					$file = str_replace($m[0], '', $file);
					$basename = $m[1];
					$fixedBasename = true;
				}
				$basename = preg_replace('/[#&=\+]/', '', $basename);
				if(preg_match('/^[_+=!?]*\./', $basename) || strlen(trim($basename))==0) $basename = 'f'.$basename;
				if(mb_strlen($basename) > 255) $basename = mb_substr($basename, 0, 255);
				$tempPath = $tmpsubdir.$basename;
				$tempPath2 = $tmpsubdir.(\Bitrix\Main\IO\Path::convertLogicalToPhysical($basename));
				$ext = ToLower(CKDAImportUtils::GetFileExtension($basename));
				$arOptions = array();
				$arOptions['disableSslVerification'] = true;
				$arOptions['redirect'] = false;
				$maxTime = $this->GetRemainingTime();
				if($maxTime < -5) return array();
				$maxTime = max(1, min(30, $maxTime));
				$arOptions['socketTimeout'] = $arOptions['streamTimeout'] = $maxTime;
				$arOptions['socketTimeout'] = min(10, $arOptions['socketTimeout']);
				$arHeaders = array(
					'User-Agent' => CKDAImportUtils::GetUserAgent(),
					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
				);
				if(isset($fieldSettings['FILE_HEADERS']) && strlen($fieldSettings['FILE_HEADERS']) > 0)
				{
					$arAddHeaders = explode("\n", $fieldSettings['FILE_HEADERS']);
					foreach($arAddHeaders as $k=>$v)
					{
						$arAddHeader = array_diff(array_map('trim', explode(":", $v)), array(''));
						if(count($arAddHeader)==2) $arHeaders[$arAddHeader[0]] = $arAddHeader[1];
					}
				}
				try{
					if(!CUtil::DetectUTF8($file)) $file = CKDAImportUtils::Win1251Utf8($file);
					$file = $loc = preg_replace_callback('/([^:\/?=&#@%]|%(?![0-9A-F]{2}))+/', array('CKDAImportUtils', 'UrlEncodeCallback'), str_replace('\\', '/', $file));
					$arUrl = parse_url($loc);
					$protocol = $arUrl['scheme'];
					$host = $protocol.'://'.$arUrl['host'];
					$loop = 0;
					while(strlen($loc) > 0 && $loop < 5)
					{
						$loop++;
						if($loop > 1 && $this->cloud->GetService($loc))
						{
							return $this->GetFileArrayDirect($loc, $arParams, $fieldName, $oldId);
						}
						$locPrev = $loc;
						$ob = new \Bitrix\KdaImportexcel\HttpClient($arOptions);
						//if(is_callable(array($ob, 'setPrivateIp'))) $ob->setPrivateIp(false);
						foreach($arHeaders as $k=>$v) $ob->setHeader($k, $v);
						if(isset($this->params['LAST_COOKIES']) && is_array($this->params['LAST_COOKIES'])) $ob->setCookies($this->params['LAST_COOKIES']);
						$res = $ob->cdownload($loc, $tempPath);
						if($ob->getStatus()==400 && $loc!=$fileOrig && !$ob->getHeaders()->get("Location"))
						{
							$loc = $fileOrig;
							continue;
						}
						$loc = $ob->getHeaders()->get("Location");
						if(strlen($loc) > 0 && (strlen(trim($loc))==0 || (string)$loc==='0')) $loc = '';
						if(strlen($loc)==0 && strpos($ob->getHeaders()->get('content-type'), 'text/html')!==false && $ob->getStatus()!=404)
						{
							$fragment = '';
							if(strpos($fileOrig, '#')!==false)
							{
								$arUrl = parse_url($fileOrig);
								if(strlen($arUrl['fragment']) > 0) $fragment = $arUrl['fragment'];
								
							}elseif($bNeedImage) $fragment = 'img[itemprop=image]';
							if(strlen($fragment) > 0)
							{
								$loc = \Bitrix\KdaImportexcel\IUtils::GetHtmlDomVal(file_get_contents($tempPath2), $fragment, true, $bMultiple, $file);
								if(is_array($loc) && $bMultiple)
								{
									if(count($loc) > 0)
									{
										$arFiles = array();
										foreach($loc as $subloc)
										{
											if(strpos($subloc, '/')===0) $subloc = $host.$subloc;
											$arFiles[] = self::GetFileArray($subloc, $arParams, $fieldName);
										}
										return array('VALUES'=>$arFiles);
									}
									else $loc = '';
								}
							}
							if(($content = file_get_contents($tempPath, false, null, 0, 4096))
								&& (stripos($content, '<html>')!==false || stripos($content, '<script')!==false)
								&& preg_match('/document\.cookie\s*=\s*["\']([^"\']+)["\']/Uis', $content, $cm))
							{
								$arNewCookies = array();
								foreach(explode('&', $cm[1]) as $newCookie)
								{
									$arNewCookie = explode('=', $newCookie);
									$arNewCookies[$arNewCookie[0]] = current(explode(';', $arNewCookie[1]));
								}
								if(!empty($arNewCookies))
								{
									if(!isset($this->params['LAST_COOKIES'])) $this->params['LAST_COOKIES'] = array();
									$this->params['LAST_COOKIES'] = array_merge($this->params['LAST_COOKIES'], $arNewCookies);
									if(strlen($loc)==0)
									{
										$loc = $locPrev;
										$locPrev = '';
									}
								}
							}
						}
						elseif($ob->getStatus()==404 && strlen($loc)==0 && $fileOrig!=$file)
						{
							$loc = $file = $fileOrig;
						}
						elseif(strlen($loc) > 0)
						{
							$arNewCookies = $ob->getCookies()->toArray();
							if(!empty($arNewCookies))
							{
								if(!isset($this->params['LAST_COOKIES'])) $this->params['LAST_COOKIES'] = array();
								$this->params['LAST_COOKIES'] = array_merge($this->params['LAST_COOKIES'], $arNewCookies);
							}
						}
						if(strpos($loc, '//')===0) $loc = $protocol.':'.$loc;
						elseif(strpos($loc, '/')===0) $loc = $host.$loc;
						if($loc==$locPrev && $loop > 1) $loc = '';
						if(strlen($loc)==0 && in_array($ob->getStatus(), array(403, 505)) && $arOptions['version']!='2.0')
						{
							$arOptions['version'] = '2.0';
							$loc = $locPrev;
						}
					}
					if($res && $ob->getStatus()!=404)
					{
						if(strpos($ob->getHeaders()->get('content-type'), 'text/html')===false || in_array($ext, array('.htm', '.html')))
						{
							$file = $tempPath2;
						}
						elseif($bNeedImage
							&& ($arFile = \CFile::MakeFileArray($tempPath2))
							&& stripos($arFile['type'], 'image')===false
							&& (!preg_match('/IE_(PREVIEW|DETAIL)_PICTURE/', $fieldName) || !preg_match('/[;,\|\s'.preg_quote($this->params['ELEMENT_MULTIPLE_SEPARATOR'], '/').']/s', $fileOrig))
							&& ($fileContent = file_get_contents($tempPath2))
							&& preg_match_all('/src=[\'"]([^\'"]*)[\'"]/is', $fileContent, $m))
						{
							$img = trim(current($m[1]));
							if(strpos($img, '/')===0) $img = $host.$img;
							$ob = new \Bitrix\KdaImportexcel\HttpClient($arOptions);
							//if(is_callable(array($ob, 'setPrivateIp'))) $ob->setPrivateIp(false);
							foreach($arHeaders as $k=>$v) $ob->setHeader($k, $v);
							if($ob->cdownload($img, $tempPath) 
								&& $ob->getStatus()!=404 
								&& (strpos($ob->getHeaders()->get('content-type'), 'text/html')===false || in_array($ext, array('.htm', '.html')))) $file = $tempPath2;
							else return array();
						}
						else
						{
							if($ob->getLastError()) $this->lastFileError = $ob->getLastError();
							return array();
						}
					}
					else
					{
						if(!$res) $this->lastFileError = $ob->getLastError();
						elseif($ob->getStatus()==404) $this->lastFileError = 'STATUS_404';
						return array();
					}
				}catch(Exception $ex){}
				$hcd = $ob->getHeaders()->get('content-disposition');
				if(!$fixedBasename && $hcd && (stripos($hcd, 'filename=')!==false || stripos($hcd, 'filename*=')!==false))
				{
					$hcdParts = array_map('trim', explode(';', $hcd));
					$hcdParts1 = preg_grep('/filename\*=UTF\-8\'\'/i', $hcdParts);
					$hcdParts2 = preg_grep('/filename=/i', $hcdParts);
					$newFn = '';
					$isUtf = (bool)(defined("BX_UTF") && BX_UTF);
					if(count($hcdParts1) > 0)
					{
						$hcdParts1 = explode("''", current($hcdParts1));
						$newFn = urldecode(trim(end($hcdParts1), '"\' '));
						if(!$isUtf) $newFn = \Bitrix\Main\Text\Encoding::convertEncoding($newFn, "UTF-8", "Windows-1251");
						$newFn = \Bitrix\Main\IO\Path::convertLogicalToPhysical($newFn);
					}
					elseif(count($hcdParts2) > 0)
					{
						$hcdParts2 = explode('=', current($hcdParts2));
						$newFn = trim(end($hcdParts2), '"\' ');
						if($newFn!=urldecode($newFn))
						{
							$newFn = urldecode($newFn);
							if($isUtf && !preg_match('/\p{Cyrillic}/u', $newFn)) $newFn = \Bitrix\Main\Text\Encoding::convertEncoding($newFn, "Windows-1251", "UTF-8");
							elseif(!$isUtf && preg_match('/\p{Cyrillic}/u', $newFn)) $newFn = \Bitrix\Main\Text\Encoding::convertEncoding($newFn, "UTF-8", "Windows-1251");
						}
						/*//not rigth
						if(function_exists('iconv'))
						{
							if(iconv('CP1251', 'CP1251', $newFn)==$newFn)
							{
								if($isUtf) $newFn = \Bitrix\Main\Text\Encoding::convertEncoding($newFn, "Windows-1251", "UTF-8");
							}
							elseif(!$isUtf) $newFn = \Bitrix\Main\Text\Encoding::convertEncoding($newFn, "UTF-8", "Windows-1251");
						}
						*/
						$newFn = \Bitrix\Main\IO\Path::convertLogicalToPhysical($newFn);
					}
					if(strlen($newFn) > 0 && strpos($file, $newFn)===false)
					{
						$file = CKDAImportUtils::ReplaceFile($file, dirname($file).'/'.$newFn);
					}
				}
				$this->CheckFileTimeout($fieldSettings);
			}
		}
		if(strpos($file, '/')===false) $file = '/'.$file;
		$this->AddTmpFile($fileOrig, $file);
		if(!$isTmpFile && ($tmpFile = $this->GetTmpFile($fileOrig))) $file = $tmpFile;
		$arFile = self::MakeFileArray($file);
		if(!$arFile['name'] && !CKDAImportUtils::DetectUTF8($file))
		{
			$file = CKDAImportUtils::Win1251Utf8($file);
			$arFile = self::MakeFileArray($file);
		}
		$this->ReplaceFileName($arFile);

		if(file_exists($file) && is_dir($file))
		{
			$dirname = $file;
		}
		elseif(in_array($arFile['type'], array('application/zip', 'application/x-zip-compressed')) && $extractZip)
		{
			$archiveParams = $this->GetArchiveParams($fileOrig);
			if(!$archiveParams['exists'])
			{
				CheckDirPath($archiveParams['path']);
				if(!file_exists($archiveParams['path'])) return array();
				$isExtract = false;
				if(function_exists('exec'))
				{
					$command = 'unzip "'.$arFile['tmp_name'].'" -d '.$archiveParams['path'];
					@exec($command);
					if(count(array_diff(scandir($archiveParams['path']), array('.', '..'))) > 0) $isExtract = true;
				}
				if(!$isExtract && class_exists('ZipArchive'))
				{
					$zipObj = new ZipArchive();
					if ($zipObj->open(\Bitrix\Main\IO\Path::convertLogicalToPhysical($arFile['tmp_name']))===true)
					{
						$isExtract = (bool)($zipObj->extractTo($archiveParams['path']) && count(array_diff(scandir($archiveParams['path']), array('.', '..'))) > 0);
						$zipObj->close();
					}
				}
				if(!$isExtract)
				{
					$zipObj = CBXArchive::GetArchive($arFile['tmp_name'], 'ZIP');
					$zipObj->Unpack($archiveParams['path']);
				}
				CKDAImportUtils::CorrectEncodingForExtractDir($archiveParams['path']);
			}
			$dirname = $archiveParams['file'];
		}
		if(strlen($dirname) > 0)
		{
			$arFile = array();
			if(file_exists($dirname) && is_file($dirname)) $arFiles = array($dirname);
			elseif($this->PathContainsMask($dirname)) $arFiles = $this->GetFilesByMask($dirname);
			else $arFiles = CKDAImportUtils::GetFilesByExt($dirname, $fileTypes, $checkSubdirs);
			$arFiles = array_diff($arFiles, preg_grep('/__imp\d+imp__/', $arFiles));
			usort($arFiles, array('CKDAImportUtils', 'SortByStrlen'));
			if($bMultiple && count($arFiles) > 1)
			{
				/*foreach($arFiles as $k=>$v)
				{
					$arFiles[$k] = self::MakeFileArray($this->GetTmpFile($v, true));
					$this->ReplaceFileName($arFiles[$k]);
				}
				$arFile = array('VALUES'=>$arFiles);*/
				foreach($arFiles as $k=>$v)
				{
					$arFiles[$k] = self::GetFileArray($v, $arParams, $fieldName);
				}
				return array('VALUES'=>$arFiles);
			}
			elseif(count($arFiles) > 0)
			{
				$v = current($arFiles);
				/*$arFile = self::MakeFileArray($this->GetTmpFile($v, true));
				$this->ReplaceFileName($arFile);*/
				return self::GetFileArray($v, $arParams, $fieldName);
			}
		}
		
		if(array_key_exists('name', $arFile))
		{
			$io = \CBXVirtualIo::GetInstance();
			if(!$io->ValidateFilenameString($arFile['name']))
			{
				if(defined('BX_UTF') && BX_UTF && $io->ValidateFilenameString(\CKDAImportUtils::Win1251Utf8($arFile['name']))) $arFile['name'] = \CKDAImportUtils::Win1251Utf8($arFile['name']);
			}
		}
		
		if(is_array($arFile) && array_key_exists('type', $arFile) && $arFile['type']=='application/octet-stream' && is_callable(array('\Bitrix\Main\Web\MimeType', 'getByFilename')))
		{
			$arFile['type'] = \Bitrix\Main\Web\MimeType::getByFilename($arFile['name']);
		}
		
		if(is_array($arFile) && array_key_exists('type', $arFile))
		{
			if(strpos($arFile['type'], 'image/')===0)
			{
				$ext = ToLower(str_replace('image/', '', $arFile['type']));
				if($ext=='x-ms-bmp') $ext='bmp';
				
				/*Webp convert*/
				if($ext=='webp' && !\Bitrix\KdaImportexcel\ClassManager::VersionGeqThen('main', '20.200.100') && !empty($fileTypes) && !in_array('webp', $fileTypes) && in_array('jpg', $fileTypes) && function_exists('imagecreatefromwebp') && function_exists('imagepng'))
				{
					$tmpsubdir = $this->CreateTmpImageDir();
					$file = \Bitrix\Main\IO\Path::convertLogicalToPhysical($tmpsubdir.preg_replace('/\.[^\.]{2,5}\s*$/', '', $arFile['name']).'.jpg');
					$img = imagecreatefromwebp($arFile['tmp_name']);
					imageinterlace($img, false);
					imagepng($img, $file, 9);
					imagedestroy($img);
					$arFile = self::MakeFileArray($file);
					$ext = ToLower(str_replace('image/', '', $arFile['type']));
				}
				/*/Webp convert*/
				
				/*Imagick convert*/
				$ext2 = current(explode('+', $ext));
				$arExts = array('tiff'=>'png', 'svg'=>'webp');
				if(in_array($ext2, array_keys($arExts)) && class_exists('\Imagick') && in_array(ToUpper($ext2), \Imagick::queryFormats()) && !empty($fileTypes) && !in_array($ext2, $fileTypes) && in_array($arExts[$ext2], $fileTypes))
				{
					try{
						$tmpsubdir = $this->CreateTmpImageDir();
						$file = \Bitrix\Main\IO\Path::convertLogicalToPhysical($tmpsubdir.preg_replace('/\.[^\.]{2,5}\s*$/', '', $arFile['name']).'.'.$arExts[$ext2]);
						$ext = ($arExts[$ext2]=='jpg' ? 'jpeg' : $arExts[$ext2]);
						if($ext=='webp' && !in_array(ToUpper($ext), \Imagick::queryFormats())) $ext = 'png';
						$im = new \Imagick($arFile['tmp_name']);
						$im->setImageFormat($ext);
						$im->setImageCompressionQuality(100);
						$im->writeImage($file);
						$im->destroy();
						$arFile = self::MakeFileArray($file);
						$ext = $ext2 = ToLower(str_replace('image/', '', $arFile['type']));
					}catch(Exception $ex){}
				}
				/*/Imagick convert*/
				
				if($this->IsWrongExt($arFile['name'], $ext))
				{
					if(($ext!='jpeg' || (($ext='jpg') && $this->IsWrongExt($arFile['name'], $ext)))
						&& ($ext!='svg+xml' || (($ext='svg') && $this->IsWrongExt($arFile['name'], $ext)))
						&& (empty($fileTypes) || in_array($ext, $fileTypes))
					)
					{
						$arFile['name'] = mb_substr($arFile['name'], 0, 255-mb_strlen('.'.$ext)).'.'.$ext;
					}
				}
			}
			elseif($bNeedImage) $arFile = array();
		}

		$arDef = ($fieldSettings['PICTURE_PROCESSING'] ? $fieldSettings['PICTURE_PROCESSING'] : array());
		if(isset($arParams['PICTURE_PROCESSING']) && $fieldSettings['INCLUDE_PICTURE_PROCESSING']!='Y') $arDef = $arParams['PICTURE_PROCESSING'];
		if(!empty($arDef) && !empty($arFile))
		{
			if(isset($arFile['VALUES']))
			{
				foreach($arFile['VALUES'] as $k=>$v)
				{
					$arFile['VALUES'][$k] = $this->PictureProcessing($v, $arDef);
				}
			}
			else
			{
				$arFile = $this->PictureProcessing($arFile, $arDef);
			}
		}
		if(is_array($arFile) && array_key_exists('type', $arFile))
		{
			if(!empty($arFile) && strpos($arFile['type'], 'image/')===0)
			{
				list($width, $height, $type, $attr) = getimagesize($arFile['tmp_name']);
				$arCacheKeys = array('width'=>$width, 'height'=>$height, 'name'=>preg_replace('/__imp\d+imp__/', '', $arFile['name']), 'size'=>$arFile['size']);
				if($this->params['IMAGES_CHECK_PARAMS']=='WO_NAME' || $this->params['ELEMENT_NOT_CHECK_NAME_IMAGES']=='Y') $arCacheKeys = array('width'=>$width, 'height'=>$height, 'size'=>$arFile['size']);
				elseif($this->params['IMAGES_CHECK_PARAMS']=='WO_SIZE') $arCacheKeys = array('width'=>$width, 'height'=>$height, 'name'=>preg_replace('/__imp\d+imp__/', '', $arFile['name']));
				elseif($this->params['IMAGES_CHECK_PARAMS']=='PATH_SIZES') $arCacheKeys = array('width'=>$width, 'height'=>$height, 'path'=>$fileOrig);
				elseif($this->params['IMAGES_CHECK_PARAMS']=='MD5') $arCacheKeys = array('md5'=>md5_file($arFile['tmp_name']));
				elseif($this->params['IMAGES_CHECK_PARAMS']=='PATH') $arCacheKeys = array('md5path'=>md5($fileOrig));
				if($arCacheKeys['md5']) $arFile['external_id'] = 'md5file_'.$arCacheKeys['md5'];
				elseif($arCacheKeys['md5path']) $arFile['external_id'] = 'md5path_'.$arCacheKeys['md5path'];
				else $arFile['external_id'] = 'i_'.md5(serialize($arCacheKeys));
			}
			if(!empty($arFile) && (strpos($arFile['type'], 'html')!==false || (strpos($arFile['type'], 'text')!==false && preg_match('/\.(jpg|jpeg|png|gif|webp|pdf|zip)$/i', $arFile['name']))) && strpos($fileOrig, '/')!==0) $arFile = array();
			if(array_key_exists('size', $arFile) && $arFile['size']==0 && filesize($arFile['tmp_name'])==0) $arFile = array();
			if(!empty($arFile) && $checkFormat && !empty($fileTypes))
			{
				$ext = ToLower(CKDAImportUtils::GetFileExtension($arFile['name']));
				if(!in_array($ext, $fileTypes)) $arFile = array();
			}
			if(array_key_exists('name', $arFile))
			{
				if(preg_match('/^[\.\-_]*(\.[^\.]*)?$/', $arFile['name'])) $arFile['name'] = 'i'.$arFile['name'];
				
				//check cloud storage
				/*control_file_duplicates*/
				if ($arFile['size'] > 0 && \Bitrix\Main\Config\Option::get('main', 'control_file_duplicates', 'N') === 'Y' && is_callable(array('\CFile', 'FindDuplicate')))
				{
					$maxSize = (int)\Bitrix\Main\Config\Option::get('main', 'duplicates_max_size', '100') * 1024 * 1024; //Mbytes
					if($arFile['size'] <= $maxSize || $maxSize === 0)
					{
						$hash = hash_file("md5", $arFile['tmp_name']);
						$original = \CFile::FindDuplicate($arFile["size"], $hash);
						if($original !== null && is_callable(array($original, 'getFile')))
						{
							$originalPath = $_SERVER["DOCUMENT_ROOT"]."/".\Bitrix\Main\Config\Option::get("main", "upload_dir", "upload")."/".$original->getFile()->getSubdir()."/".$original->getFile()->getFileName();

							$originalFileName = \CBXVirtualIo::GetInstance()->GetPhysicalName($originalPath);
							if(!file_exists($originalFileName) || filesize($originalFileName)==0 && class_exists('\Bitrix\Main\File\Internal\FileDuplicateTable'))
							{
								CheckDirPath(dirname($originalFileName).'/');
								copy($arFile['tmp_name'], $originalFileName);
								
								/*
								$originalFileId = $original->getFile()->getId();
								$dbRes = \Bitrix\Main\File\Internal\FileDuplicateTable::getList(array('filter'=>array('ORIGINAL_ID'=>$originalFileId), 'select'=>array('DUPLICATE_ID')));
								while($arr = $dbRes->Fetch())
								{
									\CFile::Delete($arr['DUPLICATE_ID']);
								}
								\CFile::Delete($originalFileId);
								*/
							}
						}
					}
				}
				/*/control_file_duplicates*/
			}
		}
		return $arFile;
	}
	
	public function ReplaceFileName(&$arFile)
	{
		if(is_array($arFile) && $arFile['name']) $arFile['name'] = preg_replace('/__imp\d+imp__/', '', $arFile['name']);
	}
	
	public function IsWrongExt($name, $ext)
	{
		return (bool)(mb_substr($name, -(mb_strlen($ext) + 1))!='.'.$ext);
	}
	
	public function PathContainsMask($path)
	{
		return (bool)((strpos($path, '*')!==false || (strpos($path, '{')!==false && strpos($path, '}')!==false)));
	}
	
	public function GetFilesByMask($mask)
	{
		$arFiles = array();
		$prefix = (strpos($mask, $_SERVER['DOCUMENT_ROOT'])===0 ? '' : $_SERVER['DOCUMENT_ROOT']);
		if(strpos($mask, '/*/')===false)
		{
			$arFiles = glob($prefix.$mask, GLOB_BRACE);
		}
		else
		{
			$i = 1;
			while(empty($arFiles) && $i<8)
			{
				$arFiles = glob($prefix.str_replace('/*/', str_repeat('/*', $i).'/', $mask), GLOB_BRACE);
				$i++;
			}
		}
		/*Try search other register*/
		if(empty($arFiles) && strpos($mask, '/*/')===false)
		{
			$dn = dirname($prefix.$mask);
			$pfn = ToLower(trim(mb_substr($prefix.$mask, mb_strlen($dn)), '/'));
			if(file_exists($dn) && is_dir($dn) && $this->PathContainsMask($pfn) && count(explode('?', $pfn))<=count(explode('?', $mask)))
			{
				$p = \CKDAImportUtils::GetPatternForRegexp($pfn);
				$i = 0;
				$dh = opendir($dn);
				while($i++ < 5000 && (false !== ($fn = readdir($dh))))
				{
					if(preg_match($p, ToLower($fn))) $arFiles[] = $dn.'/'.$fn;
				}
				closedir($dh);
			}
		}
		/*/Try search other register*/
		if(empty($arFiles)) return array();
		
		$arFiles = array_map(array('CKDAImportUtils', 'RemoveDocRoot'), $arFiles);
		usort($arFiles, array('CKDAImportUtils', 'SortByStrlen'));
		return $arFiles;
	}
	
	public function GetArchiveParams($file)
	{
		$arUrl = parse_url($file);
		$fragment = (isset($arUrl['fragment']) ? $arUrl['fragment'] : '');
		if(strlen($fragment) > 0) $file = mb_substr($file, 0, -mb_strlen($fragment) - 1);
		$archivePath = $this->archivedir.md5($file).'/';
		return array(
			'path' => $archivePath, 
			'exists' => file_exists($archivePath),
			'file' => $archivePath.ltrim($fragment, '/')
		);
	}
	
	public function GetFileFromArchive($file)
	{
		$archiveParams = $this->GetArchiveParams($file);
		if(!$archiveParams['exists']) return false;
		return $archiveParams['file'];
	}
	
	public function CheckFileTimeout($arParams)
	{
		if(isset($arParams['FILE_TIMEOUT']))
		{
			$timeout = $this->GetFloatVal($arParams['FILE_TIMEOUT']);
			if($timeout > 0) usleep($timeout*1000000);
		}
	}
	
	public function PictureProcessing($arFile, $arDef)
	{
		$isChanged = false;
		if($arDef["SCALE"] === "Y" || $arDef["CROPPING"] === "Y")
		{
			/*if(isset($arDef['METHOD']) && $arDef['METHOD']=='Y') $arDef['METHOD'] = 'resample';
			elseif($arDef['METHOD'] != 'resample') $arDef['METHOD'] = '';*/
			$arDef['METHOD'] = 'resample';
			$arNewPicture = self::ResizePicture($arFile, $arDef);
			if(is_array($arNewPicture))
			{
				$arFile = $arNewPicture;
			}
			/*elseif($arDef["IGNORE_ERRORS"] !== "Y")
			{
				unset($arFile);
				$strWarning .= Loc::getMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
			}*/
			$isChanged = true;
		}

		if($arDef["USE_WATERMARK_FILE"] === "Y")
		{
			CIBLock::FilterPicture($arFile["tmp_name"], array(
				"name" => "watermark",
				"position" => $arDef["WATERMARK_FILE_POSITION"],
				"type" => "file",
				"size" => "real",
				"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
				"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
			));
			$isChanged = true;
		}

		if($arDef["USE_WATERMARK_TEXT"] === "Y")
		{
			CIBLock::FilterPicture($arFile["tmp_name"], array(
				"name" => "watermark",
				"position" => $arDef["WATERMARK_TEXT_POSITION"],
				"type" => "text",
				"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
				"text" => $arDef["WATERMARK_TEXT"],
				"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
				"color" => $arDef["WATERMARK_TEXT_COLOR"],
				"padding" => $arDef["WATERMARK_TEXT_PADDING"],
			));
			$isChanged = true;
		}
		
		if($arDef["MIRROR"] === "Y")
		{
			$arNewPicture = self::MirrorPicture($arFile);
			if(is_array($arNewPicture))
			{
				$arFile = $arNewPicture;
			}
			$isChanged = true;
		}
		
		if($arDef["CHANGE_EXTENSION"] === "Y" && $arDef["NEW_EXTENSION"])
		{
			$ext1 = ToLower(str_replace('image/', '', $arFile['type']));
			$ext1 = current(explode('+', $ext1));
			if($ext1=='jpeg') $ext1 = 'jpg';
			$ext1f = (($ext1=='jpg') ? 'jpeg' : $ext1);
			$ext2 = ToLower($arDef["NEW_EXTENSION"]);
			$ext2f = (($ext2=='jpg') ? 'jpeg' : $ext2);
			
			if($ext1!=$ext2)
			{
				$quality = 100;
				if((int)$arDef["COMPRESSION"] > 0 && in_array($ext1, array('jpg', 'webp'))) $quality = (int)$arDef["COMPRESSION"]; 
				$convert = false;
				list($width, $height) = getimagesize($arFile['tmp_name']);
				/*Imagick convert*/
				if(class_exists('\Imagick') && in_array(ToUpper($ext1), \Imagick::queryFormats()) && in_array(ToUpper($ext2), \Imagick::queryFormats()))
				{
					try{
						$tmpsubdir = $this->CreateTmpImageDir();
						$file = \Bitrix\Main\IO\Path::convertLogicalToPhysical($tmpsubdir.preg_replace('/\.[^\.]{2,5}\s*$/', '', $arFile['name']).'.'.$ext2);
						$im = new \Imagick($arFile['tmp_name']);
						if($ext2=='jpg')
						{
							$im2 = new \Imagick();
							$im2->newImage($width, $height, new \ImagickPixel('#ffffff'));
							$im2->compositeImage($im, \Imagick::COMPOSITE_DEFAULT, 0, 0);
							$im->destroy();
							$im = $im2;
						}
						$im->setImageFormat($ext2);
						$im->setImageCompressionQuality($quality);
						$im->writeImage($file);
						$im->destroy();
						$arFile = self::MakeFileArray($file);
						$convert = true;
					}catch(Exception $ex){}
				}
				/*/Imagick convert*/
			
				if(!$convert)
				{
					$imagecreateFunc = 'imagecreatefrom'.$ext1f;
					$imageFunc = 'image'.$ext2f;
					if(function_exists($imagecreateFunc) && function_exists($imageFunc) && ($img = call_user_func($imagecreateFunc, $arFile['tmp_name'])))
					{
						$tmpsubdir = $this->CreateTmpImageDir();
						$file = \Bitrix\Main\IO\Path::convertLogicalToPhysical($tmpsubdir.preg_replace('/\.[^\.]{2,5}\s*$/', '', $arFile['name']).'.'.$ext2);
						if($ext2=='jpg')
						{
							$img2 = imagecreatetruecolor($width, $height);
							imagefill($img2, 0, 0, imagecolorallocate($img2, 255, 255, 255));
							imagecopyresampled($img2, $img, 0, 0, 0, 0, $width, $height, $width, $height);
							imagedestroy($img);
							$img = $img2;
						}
						if($ext2=='png'){imageinterlace($img, false); imagepng($img, $file, 9);}
						else call_user_func($imageFunc, $img, $file, $quality);
						imagedestroy($img);
						$arFile = self::MakeFileArray($file);
						$convert = true;
					}
				}
				if($convert) $isChanged = true;
			}
		}
		
		if($isChanged && $arFile['tmp_name'] && file_exists($arFile['tmp_name']))
		{
			clearstatcache();
			$arFile['size'] = filesize($arFile['tmp_name']);
		}
		return $arFile;
	}
	
	public static function ResizePicture($arFile, $arResize)
	{
		if(!class_exists('\Bitrix\Main\File\Image')) return \CIBlock::ResizePicture($arFile, $arResize);
		
		if($arFile["tmp_name"] == '')
			return $arFile;

		if(array_key_exists("error", $arFile) && $arFile["error"] !== 0)
			return GetMessage("IBLOCK_BAD_FILE_ERROR");

		$file = $arFile["tmp_name"];

		if(!file_exists($file) && !is_file($file))
			return GetMessage("IBLOCK_BAD_FILE_NOT_FOUND");

		$width = (int)$arResize["WIDTH"];
		$height = (int)$arResize["HEIGHT"];
		$funcWidth = $funcHeight = 0;

		$image = new Image($file);
		$imageInfo = $image->getInfo(false);
		if (empty($imageInfo))
		{
			return GetMessage("IBLOCK_BAD_FILE_NOT_PICTURE");
		}
		$orig = [
			0 => $imageInfo->getWidth(),
			1 => $imageInfo->getHeight(),
			2 => $imageInfo->getFormat(),
			3 => $imageInfo->getAttributes(),
			"mime" => $imageInfo->getMime(),
		];

		$width_orig = $width_orig_r = $orig[0];
		$height_orig = $height_orig_r = $orig[1];
		
		$x1 = $y1 = $x2 = $y2 = 0;
		if($arResize['CROPPING']==='Y')
		{
			$bCrop = false;
			if(preg_match('/^\s*\d+([\.,]\d*)?\s*\:\s*\d+([\.,]\d*)?\s*$/', $arResize['CROPPING_RATIO']))
			{
				$ratio = (float)(explode(':', $arResize['CROPPING_RATIO'])[0]) / (float)(explode(':', $arResize['CROPPING_RATIO'])[1]);
				
				if($ratio != $width_orig/$height_orig)
				{
					if($ratio > $width_orig/$height_orig)
					{
						$y2 = ($height_orig - ($width_orig / $ratio)) / 2;
						$height_orig = $width_orig / $ratio;
					}
					else
					{
						$x2 = ($width_orig - ($height_orig / $ratio)) / 2;
						$width_orig = $height_orig / $ratio;
					}
					if($width <= 0 || $width > $width_orig) $width = $width_orig;
					if($height <= 0 || $height > $height_orig) $height = $height_orig;
					$bCrop = true;
				}
			}
			if(!$bCrop && $arResize['SCALE']!=='Y') $width = $height = 0;
		}
		elseif($arResize['FILLING']==='Y')
		{
			if($width/$height > $width_orig/$height_orig)
			{
				$funcHeight = $height;
				$funcWidth = round($width_orig * $height / $height_orig);
				$x1 = round(($width - $funcWidth) / 2);
			}
			else
			{
				$funcHeight = round($height_orig * $width / $width_orig);
				$funcWidth = $width;
				$y1 = round(($height - $funcHeight)/2);
			}
		}
		
		if($width <= 0 && $height <= 0)
			return $arFile;

		$orientation = 0;
		$exifData = [];
		$image_type = $orig[2];
		if($image_type == Image::FORMAT_JPEG)
		{
			$exifData = $image->getExifData();
			if (isset($exifData['Orientation']))
			{
				$orientation = $exifData['Orientation'];
				if ($orientation >= 5 && $orientation <= 8)
				{
					$width_orig = $orig[1];
					$height_orig = $orig[0];
				}
			}
		}

		if(($width > 0 && $orig[0] > $width) || ($height > 0 && $orig[1] > $height))
		{
			if($arFile["COPY_FILE"] == "Y")
			{
				$new_file = CTempFile::GetFileName(basename($file));
				CheckDirPath($new_file);
				$arFile["copy"] = true;

				if(copy($file, $new_file))
					$file = $new_file;
				else
					return GetMessage("IBLOCK_BAD_FILE_NOT_FOUND");
			}

			if($width <= 0)
				$width = $width_orig;

			if($height <= 0)
				$height = $height_orig;

			if(!$funcWidth && !$funcHeight)
			{
				$height_new = $height_orig;
				if($width_orig > $width)
					$height_new = $width * $height_orig  / $width_orig;

				if($height_new > $height)
					$width = $height * $width_orig / $height_orig;
				else
					$height = $height_new;
			}

			$image_type = $orig[2];
			if ($image_type == Image::FORMAT_JPEG)
			{
				$image = imagecreatefromjpeg($file);
				if ($image === false)
				{
					ini_set('gd.jpeg_ignore_warning', 1);
					$image = imagecreatefromjpeg($file);
				}

				if ($orientation > 1)
				{
					if ($orientation == 7 || $orientation == 8)
						$image = imagerotate($image, 90, null);
					elseif ($orientation == 3 || $orientation == 4)
						$image = imagerotate($image, 180, null);
					elseif ($orientation == 5 || $orientation == 6)
						$image = imagerotate($image, 270, null);

					if (
						$orientation == 2 || $orientation == 7
						|| $orientation == 4 || $orientation == 5
					)
					{
						$engine = new Image\Gd();
						$engine->setResource($image);
						$engine->flipHorizontal();
					}
				}
			}
			elseif ($image_type == Image::FORMAT_GIF)
			{
				$image = imagecreatefromgif($file);
			}
			elseif ($image_type == Image::FORMAT_PNG)
			{
				$image = imagecreatefrompng($file);
			}
			elseif ($image_type == Image::FORMAT_WEBP)
			{
				$image = imagecreatefromwebp($file);
			}
			else
			{
				return GetMessage("IBLOCK_ERR_BAD_FILE_UNSUPPORTED");
			}
			
			if($image===false || (int)$width <= 0 || (int)$height <= 0) return GetMessage("IBLOCK_BAD_FILE_ERROR");
			
			if($funcHeight==0) $funcHeight = $height;
			if($funcWidth==0) $funcWidth = $width;

			$image_p = imagecreatetruecolor($width, $height);
			if($image_type == Image::FORMAT_JPEG)
			{
				if($arResize['FILLING']==='Y')
					imagefilledrectangle($image_p, 0, 0, $width, $height, imagecolorallocate($image_p, 255, 255, 255));
					
				if($arResize["METHOD"] === "resample")
					imagecopyresampled($image_p, $image, $x1, $y1, $x2, $y2, $funcWidth, $funcHeight, $width_orig, $height_orig);
				else
					imagecopyresized($image_p, $image, $x1, $y1, $x2, $y2, $funcWidth, $funcHeight, $width_orig, $height_orig);

				if($arResize["COMPRESSION"] > 0)
					imagejpeg($image_p, $file, $arResize["COMPRESSION"]);
				else
					imagejpeg($image_p, $file);
			}
			elseif($image_type == Image::FORMAT_GIF && function_exists("imagegif"))
			{
				imagetruecolortopalette($image_p, true, imagecolorstotal($image));
				imagepalettecopy($image_p, $image);

				//Save transparency for GIFs
				$transparentColor = imagecolortransparent($image);
				if($transparentColor >= 0 && $transparentColor < imagecolorstotal($image))
				{
					$transparentColor = imagecolortransparent($image_p, $transparentColor);
					imagefilledrectangle($image_p, 0, 0, $width, $height, $transparentColor);
				}

				if($arResize["METHOD"] === "resample")
					imagecopyresampled($image_p, $image, $x1, $y1, $x2, $y2, $funcWidth, $funcHeight, $width_orig, $height_orig);
				else
					imagecopyresized($image_p, $image, $x1, $y1, $x2, $y2, $funcWidth, $funcHeight, $width_orig, $height_orig);
				imagegif($image_p, $file);
			}
			else
			{
				//Save transparency for PNG
				$transparentColor = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
				imagefilledrectangle($image_p, 0, 0, $width, $height, $transparentColor);
				$transparentColor = imagecolortransparent($image_p, $transparentColor);

				imagealphablending($image_p, false);
				if($arResize["METHOD"] === "resample")
					imagecopyresampled($image_p, $image, $x1, $y1, $x2, $y2, $funcWidth, $funcHeight, $width_orig, $height_orig);
				else
					imagecopyresized($image_p, $image, $x1, $y1, $x2, $y2, $funcWidth, $funcHeight, $width_orig, $height_orig);

				imagesavealpha($image_p, true);
				imageinterlace($image_p, false);
				if($image_type == Image::FORMAT_WEBP && function_exists('imagewebp')) //add for resize+convertation webp
				{
					if($arResize["COMPRESSION"] > 0)
						imagewebp($image_p, $file, $arResize["COMPRESSION"]);
					else
						imagewebp($image_p, $file);
				}
				else
				{
					imagepng($image_p, $file, 9);
					$arFile["type"] = 'image/png';
				}
			}

			imagedestroy($image);
			imagedestroy($image_p);

			$arFile["size"] = filesize($file);
			$arFile["tmp_name"] = $file;
			return $arFile;
		}
		else
		{
			return $arFile;
		}
	}
	
	public static function MirrorPicture($arFile)
	{
		if(!class_exists('\Bitrix\Main\File\Image')) return $arFile;
		if($arFile["tmp_name"] == '') return $arFile;
		if(array_key_exists("error", $arFile) && $arFile["error"] !== 0) return $arFile;

		$file = $arFile["tmp_name"];

		if(!file_exists($file) && !is_file($file)) return $arFile;

		$image = new Image($file);
		$imageInfo = $image->getInfo(false);
		if(empty($imageInfo)) return $arFile;
		$orig = [
			0 => $imageInfo->getWidth(),
			1 => $imageInfo->getHeight(),
			2 => $imageInfo->getFormat(),
			3 => $imageInfo->getAttributes(),
			"mime" => $imageInfo->getMime(),
		];

		$width = $orig[0];
		$height = $orig[1];
		$image_type = $orig[2];
		
		if($width > 0 && $height > 0)
		{
			if ($image_type == Image::FORMAT_JPEG)
			{
				$image = imagecreatefromjpeg($file);
				if ($image === false)
				{
					ini_set('gd.jpeg_ignore_warning', 1);
					$image = imagecreatefromjpeg($file);
				}
			}
			elseif ($image_type == Image::FORMAT_GIF)
			{
				$image = imagecreatefromgif($file);
			}
			elseif ($image_type == Image::FORMAT_PNG)
			{
				$image = imagecreatefrompng($file);
			}
			elseif ($image_type == Image::FORMAT_WEBP)
			{
				$image = imagecreatefromwebp($file);
			}
			else
			{
				return $arFile;
			}
			
			$image_p = imagecreatetruecolor($width, $height);
			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$color=imagecolorat($image, $x,$y);
					imagesetpixel($image_p, $width-$x, $y, $color);
				}
			}
			
			if($image_type == Image::FORMAT_JPEG)
			{
				imagejpeg($image_p, $file, 100);
			}
			elseif($image_type == Image::FORMAT_GIF && function_exists("imagegif"))
			{
				imagegif($image_p, $file);
			}
			else
			{
				imagesavealpha($image_p, true);
				imageinterlace($image_p, false);
				imagepng($image_p, $file, 9);
			}

			imagedestroy($image);
			imagedestroy($image_p);

			$arFile["size"] = filesize($file);
			$arFile["tmp_name"] = $file;
		}
		return $arFile;
	}
	
	public function GetOldIdImageByPath($arFileIds, $path)
	{
		if($this->params['ELEMENT_IMAGES_FORCE_UPDATE']=='Y' || $this->params['IMAGES_CHECK_PARAMS']!='PATH') return false;
		if(!is_array($arFileIds))
		{
			$arFileIds = array($arFileIds);
		}
		if(($cnt = count($this->dbFileExtIds)) > 100) $this->dbFileExtIds = array_slice($this->dbFileExtIds, $cnt-100, null, true);
		$id = false;
		foreach($arFileIds as $fileId)
		{
			$fileId = (int)$fileId;
			if($fileId > 0)
			{
				if(!array_key_exists($fileId, $this->dbFileExtIds))
				{
					$this->dbFileExtIds[$fileId] = '';
					if(($arFile = CKDAImportUtils::GetFileArray($fileId)) && ($imgPath = $_SERVER['DOCUMENT_ROOT'].\Bitrix\Main\IO\Path::convertLogicalToPhysical($arFile['SRC'])) && file_exists($imgPath) && filesize($imgPath) > 0)
					{
						$this->dbFileExtIds[$fileId] = $arFile['EXTERNAL_ID'];
					}
				}
				if($this->dbFileExtIds[$fileId]=='md5path_'.md5($path))
				{
					if($id===false) $id = $fileId;
					else
					{
						if(!is_array($id)) $id = array($id);
						$id[] = $fileId;
					}
				}
			}
		}
		return $id;
	}
	
	public function IsChangedImage($fileId, $arNewFile, $checkForceUpdate=true)
	{
		if(empty($arNewFile)) return false;
		if($fileId && $arNewFile['old_id']==$fileId) return false;
		if(!$fileId)
		{
			if(!empty($arNewFile))
			{
				if(array_key_exists('VALUE', $arNewFile) && empty($arNewFile['VALUE'])) unset($arNewFile['VALUE']);
			}
			if(empty($arNewFile) || $arNewFile['del']=='Y') return false;
		}
		if(($checkForceUpdate && $this->params['ELEMENT_IMAGES_FORCE_UPDATE']=='Y') || !$fileId) return true;
		if(is_array($fileId) && array_key_exists('VALUE', $fileId)) $fileId = $fileId['VALUE'];
		$arFile = CKDAImportUtils::GetFileArray($fileId);
		$arNewFileVal = $arNewFile;
		if(isset($arNewFileVal['VALUE'])) $arNewFileVal = $arNewFileVal['VALUE'];
		if(isset($arNewFileVal['DESCRIPTION'])) $arNewFile['description'] = $arNewFileVal['DESCRIPTION'];
		elseif(isset($arNewFile['DESCRIPTION'])) $arNewFile['description'] = $arNewFile['DESCRIPTION'];
		if(!isset($arNewFileVal['tmp_name']) && isset($arNewFile['description']) && $arNewFile['description']==$arFile['DESCRIPTION'])
		{
			return false;
		}
		if(is_array($arNewFileVal) && isset($arNewFileVal['tmp_name']))
		{
			$fpath = $_SERVER['DOCUMENT_ROOT'].\Bitrix\Main\IO\Path::convertLogicalToPhysical($arFile['SRC']);
			list($width, $height, $type, $attr) = getimagesize($arNewFileVal['tmp_name']);
			$md5Check = (bool)(mb_strpos($arNewFileVal['external_id'], 'md5file_')===0);
			$updateExtId = false;
			if(((array_key_exists('external_id', $arNewFileVal) && $arFile['EXTERNAL_ID']==$arNewFileVal['external_id'])
				|| ($md5Check && mb_substr($arNewFileVal['external_id'], 8)==md5_file($fpath) && ($updateExtId = true))
				|| (!$md5Check && $arFile['FILE_SIZE']==$arNewFileVal['size'] 
					&& $arFile['ORIGINAL_NAME']==$arNewFileVal['name'] 
					&& (!$arFile['WIDTH'] || !$arFile['HEIGHT'] || ($arFile['WIDTH']==$width && $arFile['HEIGHT']==$height))
					&& ($updateExtId = true)))
				&& file_exists($fpath) && filesize($fpath) > 0
				&& (!isset($arNewFile['description']) || $arNewFile['description']==$arFile['DESCRIPTION']))
			{
				if($updateExtId && strlen($arNewFileVal['external_id']) > 0)
				{
					\CFile::UpdateExternalId($fileId, $arNewFileVal['external_id']);
				}
				return false;
			}
		}
		return true;
	}
	
	public function GetImportFileDate()
	{
		if(!isset($this->importFileDate))
		{
			$this->importFileDate = '';
			if($arFile = \CFile::GetFileArray($this->params['DATA_FILE']))
			{
				if(is_callable(array('toString', $arFile['TIMESTAMP_X']))) $this->importFileDate = $arFile['TIMESTAMP_X']->toString();
				else $this->importFileDate = $arFile['TIMESTAMP_X'];
			}
		}
		return $this->importFileDate;
	}
	
	public function GetNeedFileColumns($all = true)
	{
		if(!$all && !$this->isPacket) return array();
		if(!array_key_exists($this->worksheetNum, $this->arFieldColumns))
		{
			$arColumns = array();
			if($this->isPacket)
			{
				$fieldList = $this->params['FIELDS_LIST'][$this->worksheetNum];
				$fieldPattern = '#CELL~*\d+#|#CELL\d+[\-\+]\d+#|#CELL_[A-Z]+\d+#';
				$fieldPattern = '/(\$\{[\'"]('.$fieldPattern.')[\'"]\}|('.$fieldPattern.'))/';
				$arConvKeys = array('FROM', 'TO');
				foreach($fieldList as $key=>$field)
				{
					if(strlen($field)==0) continue;
					$k = $key;
					if(strpos($k, '_')!==false) $k = substr($k, 0, strpos($k, '_'));
					//if(!array_key_exists($k, $arColumns)) $arColumns[$k] = $k;
					
					$arCols = array((int)$k);
					//$fieldSet = (isset($this->fieldSettingsExtra[$key]) ? $this->fieldSettingsExtra[$key] : $this->fieldSettings[$field]);
					$fieldSet = $this->fparams[$this->worksheetNum][$key];
					if(is_array($fieldSet))
					{
						$arConv = array();
						if(isset($fieldSet['CONVERSION']) && is_array($fieldSet['CONVERSION'])) $arConv = array_merge($arConv, $fieldSet['CONVERSION']);
						if(isset($fieldSet['EXTRA_CONVERSION']) && is_array($fieldSet['EXTRA_CONVERSION'])) $arConv = array_merge($arConv, $fieldSet['EXTRA_CONVERSION']);
						foreach($arConv as $k=>$v)
						{
							if(is_numeric($v['CELL'])) $arCols[] = $v['CELL']-1;
							foreach($arConvKeys as $ck)
							{
								$i = 0;
								if(preg_match_all($fieldPattern, $v[$ck], $m))
								{
									foreach($m[1] as $c)
									{
										if(preg_match('/#CELL(\d+)([\-\+]\d+)#/', $c, $m2) || preg_match('/\D(\d+)#/', $c, $m2))
										{
											$arCols[] = (int)$m2[1] - 1;
										}
									}
								}
							}
						}
					}
					
					foreach($arCols as $c)
					{
						if(!array_key_exists($c, $arColumns)) $arColumns[$c] = $c;
					}
				}
				sort($arColumns, SORT_NUMERIC);
				$arColumns = array_values($arColumns);
			}
			else
			{
				for($column = 0; $column < $this->worksheetColumns; $column++)
				{
					$arColumns[] = $column;
				}
			}
			$this->arFieldColumns[$this->worksheetNum] = $arColumns;
		}
		else
		{
			$arColumns = $this->arFieldColumns[$this->worksheetNum];
		}
		return $arColumns;
	}
	
	public function ConversionReplaceValuesFloat($m)
	{
		return $this->GetFloatVal($this->ConversionReplaceValues($m));
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
		
		if(preg_match('/#CELL\d+#/', $paramName))
		{
			$k = intval(substr($paramName, 5, -1)) - 1;
			if(is_array($this->currentItemValues) && isset($this->currentItemValues[$k])) $value = $this->currentItemValues[$k];
			elseif($this->worksheet && ($val = $this->worksheet->getCellByColumnAndRow($k, $this->worksheetCurrentRow)))
			{
				$value = $this->GetCalculatedValue($val);
			}
		}
		elseif(preg_match('/#CELL(\d+)([\-\+]\d+)#/', $paramName, $m2))
		{
			if($this->worksheet && ($val = $this->worksheet->getCellByColumnAndRow((int)$m2[1] - 1, $this->worksheetCurrentRow + (int)$m2[2])))
			{
				$value = $this->GetCalculatedValue($val);
			}
		}
		elseif(preg_match('/#CELL_([A-Z]+\d+)#/', $paramName, $m2))
		{
			if($this->worksheet && ($val = $this->worksheet->getCell($m2[1])))
			{
				$value = $this->GetCalculatedValue($val);
			}
		}
		elseif(preg_match('/#CELL(~+)(\d+)#/', $paramName, $m2))
		{
			$k = $m2[1].(intval($m2[2]) - 1);
			if(is_array($this->currentItemValues) && isset($this->currentItemValues[$k])) $value = $this->currentItemValues[$k];
		}
		elseif($paramName=='#VAL#')
		{
			$value = $this->currentItemFieldVal;
		}
		elseif($paramName=='#CLINK#')
		{
			if($this->useHyperlinks && strlen($this->currentFieldKey) > 0)
			{
				$value = $this->hyperlinks[$this->currentFieldKey];
			}
		}
		elseif($paramName=='#CNOTE#')
		{
			if($this->useNotes && strlen($this->currentFieldKey) > 0)
			{
				$value = $this->notes[$this->currentFieldKey];
			}
		}
		elseif($paramName=='#SEP_SECTION#')
		{
			$value = $this->stepparams['last_section'];
		}
		elseif($paramName=='#HASH#')
		{
			$hash = md5(serialize($this->currentItemValues).serialize($this->params['FIELDS_LIST'][$this->worksheetNumForSave]));
			$value = $hash;
		}
		elseif($paramName=='#FILENAME#')
		{
			$value = bx_basename($this->filename);
		}
		elseif($paramName=='#FILEDATE#')
		{
			$value = $this->GetImportFileDate();
		}
		elseif($paramName=='#SHEETNAME#')
		{
			if(array_key_exists($this->worksheetNum, $this->worksheetNames)) $value = $this->worksheetNames[$this->worksheetNum];
			else $value = (is_callable(array($this->worksheet, 'getTitle')) ? $this->worksheet->getTitle() : '');
		}
		elseif($paramName=='#ROWNUMBER#')
		{
			$value = $this->worksheetCurrentRow;
		}
		elseif($paramName=='#IMPORT_PROCESS_ID#')
		{
			$value = $this->stepparams['loggerExecId'];
		}
		elseif(preg_match('/^#DATETIME([\+\-]\d+)?#$/', $paramName, $m2))
		{
			$value = ConvertTimeStamp((strlen($m2[1]) > 0 ? time()+(int)$m2[1]*24*60*60 : false), 'FULL');
		}
		elseif(in_array($paramName, $this->rcurrencies))
		{
			$arRates = $this->GetCurrencyRates();
			$k = trim($paramName, '#');
			$value = (isset($arRates[$k]) ? floatval($arRates[$k]) : 1);
		}
		
		if($isVar)
		{
			$this->extraConvParams[$paramName] = $value;
			return '$this->extraConvParams['.$quot.$paramName.$quot.']';
		}
		else return $value;
	}
	
	public function ApplyConversions($val, $arConv, $arItem, $field=false, $iblockFields=array())
	{
		$arExpParams = array();
		$fieldName = $fieldKey = false;
		if(!is_array($field))
		{
			$fieldName = $field;
		}
		else
		{
			if($field['NAME']) $fieldName = $field['NAME'];
			if(strlen($field['KEY']) > 0) $fieldKey = $field['KEY'];
			if(strlen($field['PARENT_ID']) > 0) $arExpParams['PARENT_ID'] = $field['PARENT_ID'];
		}
		
		if(is_array($arConv))
		{
			$execConv = false;
			$this->currentItemValues = $arItem;
			$prefixPattern = '#CELL~*\d+#|#CELL\d+[\-\+]\d+#|#CELL_[A-Z]+\d+#|#VAL#|#CLINK#|#CNOTE#|#HASH#|#FILENAME#|#FILEDATE#|#SHEETNAME#|#ROWNUMBER#|#IMPORT_PROCESS_ID#|#SEP_SECTION#|#DATETIME#|#DATETIME[\+\-]\d+#|'.implode('|', $this->rcurrencies);
			$prefixPattern = '/(\$\{[\'"]('.$prefixPattern.')[\'"]\}|('.$prefixPattern.'))/';
			foreach($arConv as $k=>$v)
			{
				$this->currentItemFieldVal = $val;
				$condVal = (string)$val;
				if((int)$v['CELL'] > 0)
				{
					$numCell = (int)$v['CELL'] - 1;
					if(array_key_exists($numCell, $arItem))
					{
						$condVal = (array_key_exists('~~'.$numCell, $arItem) ? $arItem['~~'.$numCell] : $arItem[$numCell]);
					}
					else
					{
						$condVal = $this->GetCalculatedValue($this->worksheet->getCellByColumnAndRow($numCell, $this->worksheetCurrentRow));
					}
				}
				if(strlen($v['FROM']) > 0) $v['FROM'] = preg_replace_callback($prefixPattern, array($this, 'ConversionReplaceValues'), $v['FROM']);
				if($v['CELL']=='ELSE') $v['WHEN'] = '';
				if(($v['CELL']=='ELSE' && !$execConv)
					|| ($v['WHEN']=='EQ' && $condVal==$v['FROM'])
					|| ($v['WHEN']=='NEQ' && $condVal!=$v['FROM'])
					|| (in_array($v['WHEN'], array('GT', 'LT', 'GEQ', 'LEQ', 'BETWEEN')) && $this->CompareNums($condVal, $v['FROM'], $v['WHEN']))
					|| ($v['WHEN']=='CONTAIN' && strpos($condVal, $v['FROM'])!==false)
					|| ($v['WHEN']=='NOT_CONTAIN' && strpos($condVal, $v['FROM'])===false)
					|| ($v['WHEN']=='BEGIN_WITH' && strpos($condVal, $v['FROM'])===0)
					|| ($v['WHEN']=='ENDS_IN' && mb_substr($condVal, -mb_strlen($v['FROM']))===$v['FROM'])
					|| ($v['WHEN']=='REGEXP' && preg_match('/'.ToLower($v['FROM']).'/i'.CKDAImportUtils::getUtfModifier(), ToLower($condVal)))
					|| ($v['WHEN']=='NOT_REGEXP' && !preg_match('/'.ToLower($v['FROM']).'/i'.CKDAImportUtils::getUtfModifier(), ToLower($condVal)))
					|| ($v['WHEN']=='EMPTY' && strlen($condVal)==0)
					|| ($v['WHEN']=='NOT_EMPTY' && strlen($condVal) > 0)
					|| ($v['WHEN']=='ANY'))
				{
					$this->currentFieldKey = $fieldKey;
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
					elseif($v['THEN']=='LCASE') $val = ToLower($val);
					elseif($v['THEN']=='UCASE') $val = ToUpper($val);
					elseif($v['THEN']=='UFIRST') $val = preg_replace_callback('/^(\s*)(.*)$/', array('\Bitrix\KdaImportexcel\Conversion', 'UFirstCallback'), $val);
					elseif($v['THEN']=='UWORD') $val = implode(' ', array_map(array('\Bitrix\KdaImportexcel\Conversion', 'UWordCallback'), explode(' ', $val)));
					elseif($v['THEN']=='MATH_ROUND') $val = round($this->GetFloatVal($val), $this->GetFloatVal($v['TO']));
					elseif($v['THEN']=='MATH_MULTIPLY') $val = \Bitrix\KdaImportexcel\IUtils::GetFloatRoundVal($this->GetFloatVal($val) * $this->CalcFloatValue($v['TO'], true));
					elseif($v['THEN']=='MATH_DIVIDE') $val = ($this->GetFloatVal($v['TO'])==0 ? 0 : \Bitrix\KdaImportexcel\IUtils::GetFloatRoundVal($this->GetFloatVal($val) / $this->CalcFloatValue($v['TO'], true)));
					elseif($v['THEN']=='MATH_ADD') $val = \Bitrix\KdaImportexcel\IUtils::GetFloatRoundVal($this->GetFloatVal($val) + $this->CalcFloatValue($v['TO'], true));
					elseif($v['THEN']=='MATH_SUBTRACT') $val = \Bitrix\KdaImportexcel\IUtils::GetFloatRoundVal($this->GetFloatVal($val) - $this->CalcFloatValue($v['TO'], true));
					elseif($v['THEN']=='MATH_ADD_PERCENT') $val = (strlen($val) > 0 ? \Bitrix\KdaImportexcel\IUtils::GetFloatRoundVal($this->GetFloatVal($val) * (1 + $this->GetFloatVal($v['TO'])/100)) : '');
					elseif($v['THEN']=='MATH_SUBTRACT_PERCENT') $val = (strlen($val) > 0 ? \Bitrix\KdaImportexcel\IUtils::GetFloatRoundVal($this->GetFloatVal($val) * (1 - $this->GetFloatVal($v['TO'])/100)) : '');
					elseif($v['THEN']=='MATH_FORMULA') $val = $this->CalcFloatValue($v['TO']);
					elseif($v['THEN']=='NOT_LOAD') $val = false;
					elseif($v['THEN']=='EXPRESSION') $val = $this->ExecuteFilterExpression($val, $v['TO'], false, $arExpParams, $field);
					elseif($v['THEN']=='STRIP_TAGS') $val = strip_tags($val);
					elseif($v['THEN']=='CLEAR_TAGS') $val = preg_replace('/<([a-z][a-z0-9:]*)[^>]*(\/?)>/i','<$1$2>', $val);
					elseif($v['THEN']=='TRANSLIT')
					{
						$arParams = array();
						if($fieldName && !empty($iblockFields))
						{
							$paramName = '';
							if($fieldName=='IE_CODE') $paramName = 'CODE';
							if(preg_match('/^ISECT\d*_CODE$/', $fieldName)) $paramName = 'SECTION_CODE';
							if($paramName && $iblockFields[$paramName]['DEFAULT_VALUE']['TRANSLITERATION']=='Y')
							{
								$arParams = $iblockFields[$paramName]['DEFAULT_VALUE'];
							}
						}
						if(strlen($v['TO']) > 0) $val = $v['TO'];
						$val = $this->Str2Url($val, $arParams);
					}
					elseif($v['THEN']=='DOWNLOAD_BY_LINK')
					{
						$val = \Bitrix\KdaImportexcel\IUtils::DownloadTextTextByLink($val, $v['TO']);
					}
					elseif($v['THEN']=='DOWNLOAD_IMAGES')
					{
						$val = \Bitrix\KdaImportexcel\IUtils::DownloadImagesFromText($val, $v['TO']);
					}
					$execConv = true;
				}
			}
		}
		return $val;
	}
	
	public function GetConvReplaceRegexp($val, $from, $to, $substr = true)
	{
		$mod = \CKDAImportUtils::getUtfModifier();
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
	
	public function CompareNums($v1, $v2, $c)
	{
		if(preg_match('/(\d{2}\.\d{2}\.\d{4}|\d{4}\-\d{2}\-\d{2})/', $v1) && ($v1time = strtotime($v1))
			&& preg_match('/(\d{2}\.\d{2}\.\d{4}|\d{4}\-\d{2}\-\d{2})/', $v2) && ($v2time = strtotime($v2)))
		{
			$v1 = $v1time;
			$v2 = $v2time;
		}
		else
		{
			$v1 = $this->GetFloatVal($v1);
			if($c!='BETWEEN') $v2 = $this->GetFloatValWithCalc($v2);
		}
		if($c=='GT') return (bool)($v1 > $v2);
		if($c=='LT') return (bool)($v1 < $v2);
		if($c=='GEQ') return (bool)($v1 >= $v2);
		if($c=='LEQ') return (bool)($v1 <= $v2);
		if($c=='BETWEEN') return (bool)($v1 >= $this->GetFloatVal(explode('-', $v2)[0]) && $v1 <= $this->GetFloatVal(explode('-', $v2)[1]));
	}
	
	public function CalcFloatValue($val, $forceNum=false)
	{
		$val = preg_replace_callback('/(#VAL#|#CELL\d+#)/', array($this, 'ConversionReplaceValuesFloat'), $val);
		$val = preg_replace('/&#\d+;/', '', $val);
		$val = preg_replace('/[^\d\.,+\-\/*%\(\)]/', '', $val);
		if(preg_match('/[+\-\/*]/', $val))
		{
			if(/*!preg_match('/^\(.*\)$/', $val)*/ 1) $val = '('.$val.')';
			while(preg_match_all('/\(([^\(\)]*)\)/', $val, $m))
			{
				foreach($m[1] as $k=>$v)
				{
					$subval = 0;
					while(preg_match_all('/([\d\.,]+)([\/*])(\-?[\d\.,]+)/', $v, $m2))
					{
						foreach($m2[0] as $k2=>$v2)
						{
							$subval2 = 0;
							if($m2[2][$k2]=='*') $subval2 = $this->GetFloatVal($m2[1][$k2])*$this->GetFloatVal($m2[3][$k2]);
							elseif($m2[2][$k2]=='/') $subval2 = ($this->GetFloatVal($m2[3][$k2])!=0 ? ($this->GetFloatVal($m2[1][$k2])/$this->GetFloatVal($m2[3][$k2])) : 0);
							$v = str_replace($v2, $subval2, $v);
						}
					}
					if(preg_match_all('/(^|[+\-])([\d\.,]+)%?/', $v, $m2))
					{
						$subval2 = 0;
						foreach($m2[0] as $k2=>$v2)
						{
							if(strpos($v2, '%')!==false) $subval = $subval * (1 + ($this->GetFloatVal($m2[2][$k2])/100)*($m2[1][$k2]=='-' ? -1 : 1));
							else $subval += $this->GetFloatVal($v2);

						}
					}
					$v = $subval;
					$val = str_replace($m[0][$k], $subval, $val);
				}
			}
		}
		if(strlen($val) > 0 || $forceNum) $val = $this->GetFloatVal($val);
		return $val;
	}
	
	public function CalcFloatValuePhp($val)
	{
		$val = preg_replace_callback('/(#VAL#|#CELL\d+#)/', array($this, 'ConversionReplaceValues'), $val);
		if(preg_match('/[+\-\/*]/', $val))
		{
			try{
				$command = 'return '.str_replace(',', '.', $val).';';
				$val = eval($command);
			}catch(ParseError $ex){
				$val = $this->CalcFloatValue($val);
			}catch(Error $ex){
				$val = $this->CalcFloatValue($val);
			}
		}
		if(strlen(trim($val)) > 0) $val = $this->GetFloatVal($val);
		return $val;
	}
}
?>