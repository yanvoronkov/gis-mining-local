<?php
require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
require_once(dirname(__FILE__).'/import.php');
IncludeModuleLangFile(__FILE__);

class CKDAImportExcelHighloadBase {
	protected static $moduleId = 'esol.importexportexcel';
	protected static $moduleSubDir = 'import/';
	var $rcurrencies = array('#USD#', '#EUR#');
	var $extraConvParams = array();
	var $arTmpImageDirs = array();
	var $isPacket = false;
	
	public function ExecuteOnAfterSaveHandler($handler, $ID)
	{
		try{
			$command = $handler.';';
			eval($command);
		}catch(Exception $ex){}
	}
	
	public function OnShutdown()
	{
		$arError = error_get_last();
		if(!is_array($arError) || !isset($arError['type']) || !in_array($arError['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR))) return;
		
		if($this->worksheetCurrentRow > 0)
		{
			$this->EndWithError(sprintf(Loc::getMessage("KDA_IE_FATAL_ERROR_IN_LINE"), $this->worksheetNumForSave+1, $this->worksheetCurrentRow, $arError['type'], $arError['message'], $arError['file'], $arError['line']));
		}
		else
		{
			$this->EndWithError(sprintf(Loc::getMessage("KDA_IE_FATAL_ERROR"), $arError['type'], $arError['message'], $arError['file'], $arError['line']));
		}
	}
	
	public function HandleError($code, $message, $file, $line)
	{
		return true;
	}
	
	public function HandleException($exception)
	{
		if(is_callable(array('\Bitrix\Main\Diag\ExceptionHandlerFormatter', 'format')))
		{
			$this->EndWithError(\Bitrix\Main\Diag\ExceptionHandlerFormatter::format($exception));
		}
		$this->EndWithError(sprintf(Loc::getMessage("KDA_IE_FATAL_ERROR"), '', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
	}
	
	public function EndWithError($error)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$this->errors[] = $error;
		$this->SaveStatusImport();
		echo '<!--module_return_data-->'.\KdaIE\Utils::PhpToJSObject($this->GetBreakParams());
		die();
	}
}
?>