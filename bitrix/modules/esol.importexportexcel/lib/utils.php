<?php
namespace KdaIE;

class Utils {
	public static function Unserialize($val, $allowedClasses=false)
	{
		if($allowedClasses===true)
		{
			if(preg_match_all('/O:\d+:"([^"]+)"/', $val, $m))
			{
				$allowedClasses = array_unique($m[1]);
			}
		}
		elseif($allowedClasses!==false && !is_array($allowedClasses))
		{
			$allowedClasses = array($allowedClasses);
		}
		if(!is_array($allowedClasses)) $allowedClasses = [];
		return unserialize($val, ['allowed_classes'=>$allowedClasses]);
	}
	
	public static function PhpToJSObject($arData)
	{
		$data = '';
		if(is_callable(array('\Bitrix\Main\Web\Json', 'encode')))
		{
			$data = \Bitrix\Main\Web\Json::encode($arData);
		}
		else
		{
			$data = \CUtil::PhpToJSObject($arData);
		}
		return $data;
	}
	
	public static function JsObjectToPhp($data)
	{
		if(strlen(trim($data))==0) return array();
		$arResult = null;
		if(is_callable(array('\Bitrix\Main\Web\Json', 'decode')))
		{
			try
			{
				$arResult = \Bitrix\Main\Web\Json::decode($data);
			}
			catch(\Throwable $exception)
			{
				//echo $exception->getMessage();
			}
		}
		if($arResult === null)
		{
			try
			{
				$arResult = \CUtil::JsObjectToPhp($data, true);
			}
			catch(\Throwable $exception)
			{
				//echo $exception->getMessage();
			}
		}
		if($arResult === null)
		{
			$arResult = array();
		}
		return $arResult;
	}
	
	public static function SortByNumStr($a, $b)
	{
		$a1 = preg_replace('/\.[\w\d]{2,5}$/', '', $a);
		$b1 = preg_replace('/\.[\w\d]{2,5}$/', '', $b);
		if($a1!=$b1)
		{
			$a = $a1;
			$b = $b1;
		}
		if(is_numeric($a) || is_numeric($b))
		{
			if(is_numeric($a) && is_numeric($b)) return (float)$a<(float)$b ? -1 : 1;
			else return is_numeric($a) ? -1 : 1;
		}
		return $a<$b ? -1 : 1;
	}
}
?>