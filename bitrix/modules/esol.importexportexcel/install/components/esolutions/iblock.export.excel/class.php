<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class EsolSaleBasketImport extends \CBitrixComponent
{
	/** @var CPHPCache $obCache */
	protected $obCache;
	protected $cache_id;
	protected $cache_path;
	protected $templateCachedData;
	protected $arVars;

	public function onPrepareComponentParams($params)
	{
		$params['PROFILE_ID'] = intval($params['PROFILE_ID']);
		if($params['DOWNLOAD']=='Y') $params['CACHE_TYPE'] = 'N';

		return $params;
	}

	protected function checkModules()
	{
		if (!\Bitrix\Main\Loader::includeModule('kda.exportexcel') && !\Bitrix\Main\Loader::includeModule('esol.importexportexcel'))
		{
			ShowError(Loc::getMessage('MODULE_IS_NOT_INSTALLED'));
			return false;
		}

		return true;
	}

	public function executeComponent()
	{
		if(!$this->checkModules()) return;
		if(!isset($this->arParams['PROFILE_ID']) || strlen($this->arParams['PROFILE_ID'])==0) return;
		$PROFILE_ID = $this->arParams['PROFILE_ID'];
		
		if($this->startResultCache())
		{
			$oProfile = new \CKDAExportProfile();
			$arProfile = $oProfile->GetByID($PROFILE_ID);
			if(empty($arProfile)) return;
			$arAddFilter = array();
			if($this->arParams['FILTER_NAME'] && isset($GLOBALS[$this->arParams['FILTER_NAME']]) && is_array($GLOBALS[$this->arParams['FILTER_NAME']])) $arAddFilter = $GLOBALS[$this->arParams['FILTER_NAME']];
			
			$outputFile = $outputFileOrig = \CKDAExportUtils::PrepareExportFileName($arProfile['SETTINGS_DEFAULT']['FILE_PATH']);
			if(isset($this->arParams['SECTION_CODE']) && strlen($this->arParams['SECTION_CODE']) > 0) $outputFile = preg_replace('/(\.[\w]+)$/', '_'.ToLower($this->arParams['SECTION_CODE']).'$1', $outputFile);
			elseif(isset($this->arParams['SECTION_ID']) && strlen($this->arParams['SECTION_ID']) > 0) $outputFile = preg_replace('/(\.[\w]+)$/', '_s'.$this->arParams['SECTION_ID'].'$1', $outputFile);
			if(count($arAddFilter) > 0) $outputFile = preg_replace('/(\.[\w]+)$/', '_'.md5(serialize($arAddFilter)).'$1', $outputFile);


			if(true /*$oProfile->GetProccessParamsFromPidFile($PROFILE_ID)!==false*/)
			{
				if(\Bitrix\Main\Loader::includeModule('kda.exportexcel'))
				{
					$moduleRunnerClass = '\CKDAExportExcelRunner';
				}
				elseif(\Bitrix\Main\Loader::includeModule('esol.importexportexcel'))
				{
					$moduleRunnerClass = '\CEsolImpExpExcelRunner';
				}
				
				$SETTINGS_DEFAULT = $SETTINGS = $EXTRASETTINGS = null;
				$oProfile->Apply($SETTINGS_DEFAULT, $SETTINGS, $PROFILE_ID);
				$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);
				$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
				$params['MAX_EXECUTION_TIME'] = 0;

				$arParams = array(
					'EXPORT_MODE'=>'COMPONENT',
					'OUTPUTFILE'=>$outputFile,
					'ADDFILTER'=>array_merge(array(
						'SECTION_ID'=>$this->arParams['SECTION_ID'],
						'SECTION_CODE'=>$this->arParams['SECTION_CODE']
					), $arAddFilter)
				);

				$arResult = $moduleRunnerClass::ExportIblock($params, $EXTRASETTINGS, $arParams, $PROFILE_ID);
				
				if($this->arParams['DOWNLOAD']=='Y')
				{
					$fpath = $_SERVER['DOCUMENT_ROOT'].$outputFile;
					if(!file_exists($fpath)) $fpath = $outputFile;
					$fn = end(explode('/', $outputFileOrig));
					$ext = ToLower(GetFileExtension($fpath));
					$GLOBALS['APPLICATION']->RestartBuffer();
					
					if($ext=='xlsx') header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					elseif($ext=='xls') header('Content-Type: application/vnd.ms-excel');
					elseif($ext=='csv') header('Content-Type: text/csv');
					elseif($ext=='pdf') header('Content-Type: application/pdf');
					
					//header("Content-Type: application/force-download; name=\"".$fn."\"");
					header("Content-Transfer-Encoding: binary");
					if(file_exists($fpath)) header("Content-Length: ".filesize($fpath));
					header('Content-Disposition: attachment; filename="'.$fn.'"');
					header("Expires: 0");
					header("Cache-Control: no-cache, must-revalidate");
					header("Pragma: no-cache");
					header('Connection: close');
					
					if(file_exists($fpath))
					{
						echo file_get_contents($fpath);
						unlink($fpath);
					}
					//\CMain::FinalActions();
					die();
				}
			}
			
			$filePath = $outputFile;
			if(strlen($filePath) > 0 && file_exists($_SERVER['DOCUMENT_ROOT'].$filePath) && is_file($_SERVER['DOCUMENT_ROOT'].$filePath))
			{
				$filePath = $filePath.'?'.filemtime($_SERVER['DOCUMENT_ROOT'].$filePath); 
			}
			else $filePath = '';

			$this->arResult = array(
				'FILE_PATH' => $filePath
			);

			$this->includeComponentTemplate();
		}
	}
}