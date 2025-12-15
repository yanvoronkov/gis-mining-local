<?php
require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
require_once(dirname(__FILE__).'/import.php');
IncludeModuleLangFile(__FILE__);

class CKDAImportExcelHighloadData extends CKDAImportExcelHighloadBase {
	protected static $moduleId = 'kda.importexcel';
	protected static $moduleSubDir = '';
	var $rcurrencies = array('#USD#', '#EUR#');
	var $extraConvParams = array();
	var $arTmpImageDirs = array();
	var $isPacket = false;
	
	public function SaveRecordAdd($HIGHLOADBLOCK_ID, $arFieldsElement, $arItem, $arFilter)
	{
		if($this->params['ONLY_UPDATE_MODE']!='Y' && $this->params['ONLY_DELETE_MODE']!='Y')
		{
			$entityDataClass = $this->GetHighloadBlockClass($HIGHLOADBLOCK_ID);
			$iblockFields = $this->fl->GetHigloadBlockFields($HIGHLOADBLOCK_ID);
		
			if(array_key_exists('ID', $arFieldsElement)) unset($arFieldsElement['ID']);
			$dbRes2 = $entityDataClass::Add($arFieldsElement, false, true, true);
			$ID = $dbRes2->GetID();
			
			if($dbRes2->isSuccess())
			{
				$ID = $dbRes2->GetID();
				//$this->SetTimeBegin($ID);
				$this->stepparams['element_added_line']++;
				$this->SaveElementId($ID, $HIGHLOADBLOCK_ID);
				$this->SaveRecordAfter($ID);
			}
			else
			{
				$this->stepparams['error_line']++;
				$this->errors[] = sprintf(GetMessage("KDA_IE_ADD_ELEMENT_ERROR"), implode(', ',$dbRes2->GetErrorMessages()), $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
				return false;
			}
		}
		
		return true;
	}
	
	public function SaveRecordUpdate($entityDataClass, $ID, $arFieldsElement2)
	{
		$dbRes2 = $entityDataClass::Update($ID, $arFieldsElement2);
		if($dbRes2->isSuccess())
		{
			$this->SaveRecordAfter($ID);
			//$this->SetTimeBegin($ID);
		}
		else
		{
			$this->stepparams['error_line']++;
			$this->errors[] = sprintf(GetMessage("KDA_IE_UPDATE_ELEMENT_ERROR"), implode(', ',$dbRes2->GetErrorMessages()), $this->worksheetNumForSave+1, $this->worksheetCurrentRow, $ID);
		}
	}
}
?>