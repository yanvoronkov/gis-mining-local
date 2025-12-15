<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CKDAExportUtils {
	protected static $moduleId = 'esol.importexportexcel';
	protected static $moduleSubDir = 'export/';
	protected static $colLetters = array();
	protected static $currencyRates = null;
	protected static $zipArchiveOption = 'ZIPARCHIVE_WRITE_MODE';
	protected static $lastDataRow = 0;
	protected static $jsCounter = 0;
	protected static $offerIblocks = array();
	protected static $parentIblocks = array();
	
	public static function GetModuleId()
	{
		return static::$moduleId;
	}
	
	public static function GetOfferIblock($IBLOCK_ID, $retarray=false)
	{
		if(!$IBLOCK_ID || !Loader::includeModule('catalog')) return false;
		if(!isset(self::$offerIblocks[$IBLOCK_ID]))
		{
			$arFields = array();
			if(is_callable(array('\CCatalogSku', 'GetInfoByIBlock')) && defined('\CCatalogSku::TYPE_FULL') && defined('\CCatalogSku::TYPE_PRODUCT') && ($arCatalog = \CCatalogSku::GetInfoByIBlock($IBLOCK_ID)) && in_array($arCatalog['CATALOG_TYPE'], array(\CCatalogSku::TYPE_FULL, \CCatalogSku::TYPE_PRODUCT)) && $arCatalog['PRODUCT_IBLOCK_ID'] > 0)
			{
				$arFields = Array(
					'IBLOCK_ID' => $arCatalog['PRODUCT_IBLOCK_ID'],
					'YANDEX_EXPORT' => $arCatalog['YANDEX_EXPORT'],
					'SUBSCRIPTION' => $arCatalog['SUBSCRIPTION'],
					'VAT_ID' => $arCatalog['VAT_ID'],
					'PRODUCT_IBLOCK_ID' => 0,
					'SKU_PROPERTY_ID' => 0,
					'OFFERS_PROPERTY_ID' => $arCatalog['SKU_PROPERTY_ID'],
					'OFFERS_IBLOCK_ID' => $arCatalog['IBLOCK_ID'],
					'ID' => $arCatalog['PRODUCT_IBLOCK_ID']
				);
			}
			else
			{
				$dbRes = CCatalog::GetList(array(), array('IBLOCK_ID'=>$IBLOCK_ID));
				$arFields = $dbRes->Fetch();
				if(!$arFields['OFFERS_IBLOCK_ID'])
				{
					$dbRes = CCatalog::GetList(array(), array('PRODUCT_IBLOCK_ID'=>$IBLOCK_ID));
					if($arFields2 = $dbRes->Fetch())
					{
						$arFields = array_merge($arFields2, array(
							'IBLOCK_ID' => $arFields2['PRODUCT_IBLOCK_ID'],
							'YANDEX_EXPORT' => $arFields2['YANDEX_EXPORT'],
							'SUBSCRIPTION' => $arFields2['SUBSCRIPTION'],
							'VAT_ID' => $arFields2['VAT_ID'],
							'PRODUCT_IBLOCK_ID' => 0,
							'SKU_PROPERTY_ID' => 0,
							'OFFERS_PROPERTY_ID' => $arFields2['SKU_PROPERTY_ID'],
							'OFFERS_IBLOCK_ID' => $arFields2['IBLOCK_ID'],
							'ID' => $arFields2['PRODUCT_IBLOCK_ID'],
						));
					}
				}
			}
			if(!$arFields['OFFERS_IBLOCK_ID'])
			{
				$arFields = array();
				foreach(GetModuleEvents(static::$moduleId, "OnGetOfferIblock", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array($arFields, $IBLOCK_ID));
				}
			}
			self::$offerIblocks[$IBLOCK_ID] = $arFields;
		}
		else
		{
			$arFields = self::$offerIblocks[$IBLOCK_ID];
		}

		if($arFields['OFFERS_IBLOCK_ID'])
		{
			if($retarray) return $arFields;
			else return $arFields['OFFERS_IBLOCK_ID'];
		}
		return false;
	}
	
	public static function GetParentIblock($IBLOCK_ID, $retarray=false)
	{
		if(!$IBLOCK_ID || !Loader::includeModule('catalog')) return false;
		if(!isset(self::$parentIblocks[$IBLOCK_ID]))
		{
			$dbRes = \CCatalog::GetList(array(), array('IBLOCK_ID'=>$IBLOCK_ID));
			$arFields = $dbRes->Fetch();
			self::$parentIblocks[$IBLOCK_ID] = $arFields;
		}
		else
		{
			$arFields = self::$parentIblocks[$IBLOCK_ID];
		}
		if($arFields['PRODUCT_IBLOCK_ID'])
		{
			if($retarray) return $arFields;
			else return $arFields['PRODUCT_IBLOCK_ID'];
		}
		return false;
	}
	
	public static function GetFileName($fn)
	{
		global $APPLICATION;
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$fn)) return $fn;
		
		if(defined("BX_UTF")) $tmpfile = $APPLICATION->ConvertCharsetArray($fn, LANG_CHARSET, 'CP1251');
		else $tmpfile = $APPLICATION->ConvertCharsetArray($fn, LANG_CHARSET, 'UTF-8');
		
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$tmpfile)) return $tmpfile;
		
		return false;
	}
	
	public static function Win1251Utf8($str)
	{
		global $APPLICATION;
		return $APPLICATION->ConvertCharset($str, "Windows-1251", "UTF-8");
	}
	
	public static function GetFileLinesCount($fn)
	{
		if(!file_exists($fn)) return 0;
		
		$cnt = 0;
		$handle = fopen($fn, 'r');
		while (!feof($handle)) {
			$buffer = trim(fgets($handle));
			if($buffer) $cnt++;
		}
		fclose($handle);
		return $cnt;
	}
	
	public static function SortFileIds($fn)
	{
		if(!file_exists($fn)) return 0;

		$arIds = array();
		$handle = fopen($fn, 'r');
		while (!feof($handle)) {
			$buffer = trim(fgets($handle, 128));
			if($buffer) $arIds[] = (int)$buffer;
		}
		fclose($handle);
		sort($arIds, SORT_NUMERIC);

		unlink($fn);

		$handle = fopen($fn, 'a');
		$cnt = count($arIds);
		$step = 10000;
		for($i=0; $i<$cnt; $i+=$step)
		{
			fwrite($handle, implode("\r\n", array_slice($arIds, $i, $step))."\r\n");
		}
		fclose($handle);
		
		if($cnt > 0) return end($arIds);
		else return 0;
	}
	
	public static function GetPartIdsFromFile($fn, $min)
	{
		if(!file_exists($fn)) return array();

		$cnt = 0;
		$maxCnt = 5000;
		$arIds = array();
		$handle = fopen($fn, 'r');
		while (!feof($handle) && $maxCnt>$cnt) {
			$buffer = (int)trim(fgets($handle, 128));
			if($buffer > $min)
			{
				$arIds[] = (int)$buffer;
				$cnt++;
			}
		}
		fclose($handle);
		return $arIds;
	}
	
	public static function GetFileArray($id)
	{
		if(class_exists('\Bitrix\Main\FileTable'))
		{
			if($arFile = \Bitrix\Main\FileTable::getList(array('filter'=>array('ID'=>$id)))->fetch())
			{
				if(is_callable(array($arFile['TIMESTAMP_X'], 'toString'))) $arFile['TIMESTAMP_X'] = $arFile['TIMESTAMP_X']->toString();
				$arFile['SRC'] = \CFile::GetFileSRC($arFile, false, false);
			}
		}
		else
		{
			$arFile = \CFile::GetFileArray($id);
		}
		return $arFile;
	}
	
	public static function SaveFile($arFile, $strSavePath, $bForceMD5=false, $bSkipExt=false, $bForceTranslit=false)
	{
		$strFileName = GetFileName($arFile["name"]);	/* filename.gif */

		if(isset($arFile["del"]) && $arFile["del"] <> '')
		{
			CFile::Delete($arFile["old_file"]);
			if($strFileName == '')
				return "NULL";
		}

		if($arFile["name"] == '')
		{
			if(isset($arFile["description"]) && intval($arFile["old_file"])>0)
			{
				CFile::UpdateDesc($arFile["old_file"], $arFile["description"]);
			}
			return false;
		}

		if (isset($arFile["content"]))
		{
			if (!isset($arFile["size"]))
			{
				$arFile["size"] = self::BinStrlen($arFile["content"]);
			}
		}
		else
		{
			try
			{
				$file = new \Bitrix\Main\IO\File(\Bitrix\Main\IO\Path::convertPhysicalToLogical($arFile["tmp_name"]));
				$arFile["size"] = $file->getSize();
			}
			catch(IO\IoException $e)
			{
				$arFile["size"] = 0;
			}
		}

		$arFile["ORIGINAL_NAME"] = $strFileName;

		//translit, replace unsafe chars, etc.
		$strFileName = self::transformName($strFileName, $bForceMD5, $bSkipExt, $bForceTranslit);

		//transformed name must be valid, check disk quota, etc.
		if (self::validateFile($strFileName, $arFile) !== "")
		{
			return false;
		}

		if($arFile["type"] == "image/pjpeg" || $arFile["type"] == "image/jpg")
		{
			$arFile["type"] = "image/jpeg";
		}

		$bExternalStorage = false;
		/*foreach(GetModuleEvents("main", "OnFileSave", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFile, $strFileName, $strSavePath, $bForceMD5, $bSkipExt)))
			{
				$bExternalStorage = true;
				break;
			}
		}*/

		if(!$bExternalStorage)
		{
			$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
			$io = CBXVirtualIo::GetInstance();
			if($bForceMD5 != true)
			{
				$dir_add = '';
				$i=0;
				while(true)
				{
					$dir_add = substr(md5(uniqid("", true)), 0, 3);
					if(!$io->FileExists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$dir_add."/".$strFileName))
					{
						break;
					}
					if($i >= 25)
					{
						$j=0;
						while(true)
						{
							$dir_add = substr(md5(mt_rand()), 0, 3)."/".substr(md5(mt_rand()), 0, 3);
							if(!$io->FileExists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$dir_add."/".$strFileName))
							{
								break;
							}
							if($j >= 25)
							{
								$dir_add = substr(md5(mt_rand()), 0, 3)."/".md5(mt_rand());
								break;
							}
							$j++;
						}
						break;
					}
					$i++;
				}
				if(substr($strSavePath, -1, 1) <> "/")
					$strSavePath .= "/".$dir_add;
				else
					$strSavePath .= $dir_add."/";
			}
			else
			{
				$strFileExt = ($bSkipExt == true || ($ext = GetFileExtension($strFileName)) == ''? '' : ".".$ext);
				while(true)
				{
					if(substr($strSavePath, -1, 1) <> "/")
						$strSavePath .= "/".substr($strFileName, 0, 3);
					else
						$strSavePath .= substr($strFileName, 0, 3)."/";

					if(!$io->FileExists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$strFileName))
						break;

					//try the new name
					$strFileName = md5(uniqid("", true)).$strFileExt;
				}
			}

			$arFile["SUBDIR"] = $strSavePath;
			$arFile["FILE_NAME"] = $strFileName;
			$strDirName = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/";
			$strDbFileNameX = $strDirName.$strFileName;
			$strPhysicalFileNameX = $io->GetPhysicalName($strDbFileNameX);

			CheckDirPath($strDirName);

			if(is_set($arFile, "content"))
			{
				$f = fopen($strPhysicalFileNameX, "ab");
				if(!$f)
					return false;
				if(fwrite($f, $arFile["content"]) === false)
					return false;
				fclose($f);
			}
			elseif(
				!copy($arFile["tmp_name"], $strPhysicalFileNameX)
				&& !move_uploaded_file($arFile["tmp_name"], $strPhysicalFileNameX)
			)
			{
				CFile::Delete($arFile["old_file"]);
				return false;
			}

			if(isset($arFile["old_file"]))
				CFile::Delete($arFile["old_file"]);

			@chmod($strPhysicalFileNameX, BX_FILE_PERMISSIONS);

			//flash is not an image
			$flashEnabled = !CFile::IsImage($arFile["ORIGINAL_NAME"], $arFile["type"]);

			$imgArray = CFile::GetImageSize($strDbFileNameX, false, $flashEnabled);

			if(is_array($imgArray))
			{
				$arFile["WIDTH"] = $imgArray[0];
				$arFile["HEIGHT"] = $imgArray[1];

				if($imgArray[2] == IMAGETYPE_JPEG)
				{
					$exifData = CFile::ExtractImageExif($io->GetPhysicalName($strDbFileNameX));
					if ($exifData  && isset($exifData['Orientation']))
					{
						//swap width and height
						if ($exifData['Orientation'] >= 5 && $exifData['Orientation'] <= 8)
						{
							$arFile["WIDTH"] = $imgArray[1];
							$arFile["HEIGHT"] = $imgArray[0];
						}

						$properlyOriented = CFile::ImageHandleOrientation($exifData['Orientation'], $io->GetPhysicalName($strDbFileNameX));
						if ($properlyOriented)
						{
							$jpgQuality = intval(COption::GetOptionString('main', 'image_resize_quality', '95'));
							if($jpgQuality <= 0 || $jpgQuality > 100)
								$jpgQuality = 95;
							imagejpeg($properlyOriented, $io->GetPhysicalName($strDbFileNameX), $jpgQuality);
						}
					}
				}
			}
			else
			{
				$arFile["WIDTH"] = 0;
				$arFile["HEIGHT"] = 0;
			}
		}

		if($arFile["WIDTH"] == 0 || $arFile["HEIGHT"] == 0)
		{
			//mock image because we got false from CFile::GetImageSize()
			if(strpos($arFile["type"], "image/") === 0)
			{
				$arFile["type"] = "application/octet-stream";
			}
		}

		if($arFile["type"] == '' || !is_string($arFile["type"]))
		{
			$arFile["type"] = "application/octet-stream";
		}

		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			CDiskQuota::updateDiskQuota("file", $arFile["size"], "insert");
		}
		/****************************** QUOTA ******************************/

		$NEW_IMAGE_ID = CFile::DoInsert(array(
			"HEIGHT" => $arFile["HEIGHT"],
			"WIDTH" => $arFile["WIDTH"],
			"FILE_SIZE" => $arFile["size"],
			"CONTENT_TYPE" => $arFile["type"],
			"SUBDIR" => $arFile["SUBDIR"],
			"FILE_NAME" => $arFile["FILE_NAME"],
			"MODULE_ID" => $arFile["MODULE_ID"],
			"ORIGINAL_NAME" => $arFile["ORIGINAL_NAME"],
			"DESCRIPTION" => isset($arFile["description"])? $arFile["description"]: '',
			"HANDLER_ID" => isset($arFile["HANDLER_ID"])? $arFile["HANDLER_ID"]: '',
			"EXTERNAL_ID" => isset($arFile["external_id"])? $arFile["external_id"]: md5(mt_rand()),
		));

		CFile::CleanCache($NEW_IMAGE_ID);
		return $NEW_IMAGE_ID;
	}
	
	public static function transformName($name, $bForceMD5 = false, $bSkipExt = false, $bForceTranslit = false)
	{
		//safe filename without path
		$fileName = GetFileName($name);

		$originalName = ($bForceMD5 != true);
		if($originalName)
		{
			//transforming original name:

			//transliteration
			if($bForceTranslit || COption::GetOptionString("main", "translit_original_file_name", "N") == "Y")
			{
				$fileName = CUtil::translit($fileName, LANGUAGE_ID, array("max_len"=>1024, "safe_chars"=>".", "replace_space" => '-'));
			}

			//replace invalid characters
			if(COption::GetOptionString("main", "convert_original_file_name", "Y") == "Y")
			{
				$io = CBXVirtualIo::GetInstance();
				$fileName = $io->RandomizeInvalidFilename($fileName);
			}
		}

		//.jpe is not image type on many systems
		if($bSkipExt == false && strtolower(GetFileExtension($fileName)) == "jpe")
		{
			$fileName = substr($fileName, 0, -4).".jpg";
		}

		//double extension vulnerability
		$fileName = RemoveScriptExtension($fileName);

		if(!$originalName)
		{
			//name is md5-generated:
			$fileName = md5(uniqid("", true)).($bSkipExt == true || ($ext = GetFileExtension($fileName)) == ''? '' : ".".$ext);
		}

		return $fileName;
	}

	protected static function validateFile($strFileName, $arFile)
	{
		if($strFileName == '')
			return Loc::getMessage("FILE_BAD_FILENAME");

		$io = CBXVirtualIo::GetInstance();
		if(!$io->ValidateFilenameString($strFileName))
			return Loc::getMessage("MAIN_BAD_FILENAME1");

		if(strlen($strFileName) > 255)
			return Loc::getMessage("MAIN_BAD_FILENAME_LEN");

		//check .htaccess etc.
		if(IsFileUnsafe($strFileName))
			return Loc::getMessage("FILE_BAD_TYPE");

		//nginx returns octet-stream for .jpg
		if(GetFileNameWithoutExtension($strFileName) == '')
			return Loc::getMessage("FILE_BAD_FILENAME");

		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$quota = new CDiskQuota();
			if (!$quota->checkDiskQuota($arFile))
				return Loc::getMessage("FILE_BAD_QUOTA");
		}

		return "";
	}
	
	public static function ShowNewFilter($listIndex, $SETTINGS, $SETTINGS_DEFAULT)
	{
		$IBLOCK_ID = $SETTINGS_DEFAULT['IBLOCK_ID'];
		$changeIblockId = (bool)($SETTINGS['CHANGE_IBLOCK_ID'][$listIndex]=='Y');
		if($changeIblockId && $SETTINGS['LIST_IBLOCK_ID'][$listIndex])
		{
			$IBLOCK_ID = $SETTINGS['LIST_IBLOCK_ID'][$listIndex];
		}
		$arFilter = (is_array($SETTINGS['FILTER'][$listIndex]) ? $SETTINGS['FILTER'][$listIndex] : array());
		$fl = new \CKDAEEFieldList();
		echo '<div class="kda-ee-cfilter-wrap" id="kda-ee-cfilter-'.(int)$listIndex.'">';
		echo '<div class="kda-ee-cfilter-hidden">';
		$fl->ShowSelectFilterFields($IBLOCK_ID, 'S_FIELD');
		echo '<input type="hidden" name="IBLOCK_ID" value="'.htmlspecialcharsbx($IBLOCK_ID).'">';
		echo '<input type="hidden" name="OLD_FILTER" value="'.htmlspecialcharsbx(\KdaIE\Utils::PhpToJSObject($arFilter)).'">'; 
		echo '</div>';
		echo '<div class="kda-ee-cfilter-field-list"></div>';
		echo '<a class="kda-ee-cfilter-add-field" href="javascript:void(0)">'.Loc::getMessage('KDA_EE_FILTER_ADD_FIELD').'</a>';
		echo '</div>';
	}
	
	public static function ShowFilter($sTableID, $listIndex, $SETTINGS, $SETTINGS_DEFAULT)
	{
		/*self::ShowNewFilter($listIndex, $SETTINGS, $SETTINGS_DEFAULT);
		return;*/
		
		global $APPLICATION;
		CJSCore::Init('file_input');
		$IBLOCK_ID = $SETTINGS_DEFAULT['IBLOCK_ID'];
		$changeIblockId = (bool)($SETTINGS['CHANGE_IBLOCK_ID'][$listIndex]=='Y');
		if($changeIblockId && $SETTINGS['LIST_IBLOCK_ID'][$listIndex])
		{
			$IBLOCK_ID = $SETTINGS['LIST_IBLOCK_ID'][$listIndex];
		}
		
		Loader::includeModule('iblock');
		$bCatalog = Loader::includeModule('catalog');
		$bSale = Loader::includeModule('sale');
		if($bCatalog)
		{
			$arCatalog = CCatalog::GetByID($IBLOCK_ID);
			if($arCatalog)
			{
				if(is_callable(array('CCatalogAdminTools', 'getIblockProductTypeList')))
				{
					$productTypeList = CCatalogAdminTools::getIblockProductTypeList($IBLOCK_ID, true);
				}
				
				$arStores = array();
				$dbRes = CCatalogStore::GetList(array("SORT"=>"ID"), array(), false, false, array("ID", "TITLE", "ADDRESS"));
				while($arStore = $dbRes->Fetch())
				{
					if(strlen($arStore['TITLE'])==0 && $arStore['ADDRESS']) $arStore['TITLE'] = $arStore['ADDRESS'];
					$arStores[] = $arStore;
				}
				
				$arPrices = array();
				$dbPriceType = CCatalogGroup::GetList(array("SORT" => "ASC"));
				while($arPriceType = $dbPriceType->Fetch())
				{
					if(strlen($arPriceType["NAME_LANG"])==0 && $arPriceType['NAME']) $arPriceType['NAME_LANG'] = $arPriceType['NAME'];
					$arPrices[] = $arPriceType;
				}
			}
			if(!$arCatalog) $bCatalog = false;
		}
		
		$dbrFProps = CIBlockProperty::GetList(
			array(
				"SORT"=>"ASC",
				"NAME"=>"ASC"
			),
			array(
				"IBLOCK_ID"=>$IBLOCK_ID,
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
		
		$boolSKU = false;
		$strSKUName = '';
		if($OFFERS_IBLOCK_ID = self::GetOfferIblock($IBLOCK_ID))
		{
			$boolSKU = true;
			$strSKUName = Loc::getMessage('KDA_EE_IBLIST_A_OFFERS');
			
			$dbrFProps = CIBlockProperty::GetList(
				array(
					"SORT"=>"ASC",
					"NAME"=>"ASC"
				),
				array(
					"IBLOCK_ID"=>$OFFERS_IBLOCK_ID,
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
		}
		
		$arFields = (is_array($SETTINGS['FILTER'][$listIndex]) ? $SETTINGS['FILTER'][$listIndex] : array());
		
		?>
		<script>var arClearHiddenFields = [];</script>
		<!--<form method="GET" name="find_form" id="find_form" action="">-->
		<div class="find_form_inner">
		<?
		$arFindFields = Array();
		$arFindFields["IBEL_A_F_ID"] = Loc::getMessage("KDA_EE_IBEL_A_F_ID");
		$arFindFields["IBEL_A_F_PARENT"] = Loc::getMessage("KDA_EE_IBEL_A_F_PARENT");

		$arFindFields["IBEL_A_F_MODIFIED_WHEN"] = Loc::getMessage("KDA_EE_IBEL_A_F_MODIFIED_WHEN");
		$arFindFields["IBEL_A_F_MODIFIED_BY"] = Loc::getMessage("KDA_EE_IBEL_A_F_MODIFIED_BY");
		$arFindFields["IBEL_A_F_CREATED_WHEN"] = Loc::getMessage("KDA_EE_IBEL_A_F_CREATED_WHEN");
		$arFindFields["IBEL_A_F_CREATED_BY"] = Loc::getMessage("KDA_EE_IBEL_A_F_CREATED_BY");

		$arFindFields["IBEL_A_F_ACTIVE_FROM"] = Loc::getMessage("KDA_EE_IBEL_A_ACTFROM");
		$arFindFields["IBEL_A_F_ACTIVE_TO"] = Loc::getMessage("KDA_EE_IBEL_A_ACTTO");
		$arFindFields["IBEL_A_F_ACT"] = Loc::getMessage("KDA_EE_IBEL_A_F_ACT");
		$arFindFields["IBEL_A_F_SORT"] = Loc::getMessage("KDA_EE_IBEL_A_F_SORT");
		$arFindFields["IBEL_A_F_NAME"] = Loc::getMessage("KDA_EE_IBEL_A_F_NAME");
		$arFindFields["IBEL_A_F_PREDESC"] = Loc::getMessage("KDA_EE_IBEL_A_F_PREDESC");
		$arFindFields["IBEL_A_F_DESC"] = Loc::getMessage("KDA_EE_IBEL_A_F_DESC");
		$arFindFields["IBEL_A_CODE"] = Loc::getMessage("KDA_EE_IBEL_A_CODE");
		$arFindFields["IBEL_A_EXTERNAL_ID"] = Loc::getMessage("KDA_EE_IBEL_A_EXTERNAL_ID");
		$arFindFields["IBEL_A_PREVIEW_PICTURE"] = Loc::getMessage("KDA_EE_IBEL_A_PREVIEW_PICTURE");
		$arFindFields["IBEL_A_DETAIL_PICTURE"] = Loc::getMessage("KDA_EE_IBEL_A_DETAIL_PICTURE");
		$arFindFields["IBEL_A_TAGS"] = Loc::getMessage("KDA_EE_IBEL_A_TAGS");
		
		if ($bCatalog)
		{
			if(is_array($productTypeList)) $arFindFields["CATALOG_TYPE"] = Loc::getMessage("KDA_EE_CATALOG_TYPE");
			$arFindFields["CATALOG_BUNDLE"] = Loc::getMessage("KDA_EE_CATALOG_BUNDLE");
			$arFindFields["CATALOG_AVAILABLE"] = Loc::getMessage("KDA_EE_CATALOG_AVAILABLE");
			$arFindFields["CATALOG_QUANTITY"] = Loc::getMessage("KDA_EE_CATALOG_QUANTITY");
			if(is_array($arStores))
			{
				foreach($arStores as $arStore)
				{
					$arFindFields["CATALOG_STORE".$arStore['ID']."_QUANTITY"] = sprintf(Loc::getMessage("KDA_EE_CATALOG_STORE_QUANTITY"), $arStore['TITLE']);
				}
				if(count($arStores) > 0) $arFindFields["CATALOG_STORE_ANY_QUANTITY"] = Loc::getMessage("KDA_EE_CATALOG_STORE_ANY_QUANTITY");
			}
			$arFindFields["CATALOG_PURCHASING_PRICE"] = Loc::getMessage("KDA_EE_CATALOG_PURCHASING_PRICE");
			if(is_array($arPrices))
			{
				foreach($arPrices as $arPrice)
				{
					$arFindFields["CATALOG_PRICE_".$arPrice['ID']] = sprintf(Loc::getMessage("KDA_EE_CATALOG_PRICE"), $arPrice['NAME_LANG']);
				}
			}
			$arFindFields["CATALOG_WEIGHT"] = Loc::getMessage("KDA_EE_CATALOG_WEIGHT");
			$arFindFields["CATALOG_LENGTH"] = Loc::getMessage("KDA_EE_CATALOG_LENGTH");
			$arFindFields["CATALOG_WIDTH"] = Loc::getMessage("KDA_EE_CATALOG_WIDTH");
			$arFindFields["CATALOG_HEIGHT"] = Loc::getMessage("KDA_EE_CATALOG_HEIGHT");
			$arFindFields["CATALOG_VAT_INCLUDED"] = Loc::getMessage("KDA_EE_CATALOG_VAT_INCLUDED");
			
			if($bSale)
			{
				$arFindFields["SALE_ORDER"] = Loc::getMessage("KDA_EE_EL_A_SALE_ORDER");
				$arFindFields["SALE_ORDER_DATE_INSERT"] = Loc::getMessage("KDA_EE_EL_A_SALE_ORDER_DATE_INSERT");
			}
		}

		foreach($arProps as $arProp)
			if($arProp["FILTRABLE"]=="Y" || $arProp["PROPERTY_TYPE"]=="F")
				$arFindFields["IBEL_A_PROP_".$arProp["ID"]] = $arProp["NAME"];

		if($boolSKU)
		{
			$arFindFields["IBEL_A_SUB_F_ID"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_IBEL_A_F_ID");
			$arFindFields["IBEL_A_SUB_F_MODIFIED_WHEN"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_IBEL_A_F_MODIFIED_WHEN");
			$arFindFields["IBEL_A_SUB_F_ACT"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_IBEL_A_F_ACT");
			$arFindFields["IBEL_A_SUB_F_SORT"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_IBEL_A_F_SORT");
			if(1 || $bCatalog)
			{
				$arFindFields["SUB_CATALOG_QUANTITY"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_QUANTITY");
				if(is_array($arStores))
				{
					foreach($arStores as $arStore)
					{
						$arFindFields["SUB_CATALOG_STORE".$arStore['ID']."_QUANTITY"] = ('' != $strSKUName ? $strSKUName.' - ' : '').sprintf(Loc::getMessage("KDA_EE_CATALOG_STORE_QUANTITY"), $arStore['TITLE']);
					}
					if(count($arStores) > 0) $arFindFields["SUB_CATALOG_STORE_ANY_QUANTITY"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_STORE_ANY_QUANTITY");
				}
				$arFindFields["SUB_CATALOG_PURCHASING_PRICE"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_PURCHASING_PRICE");
				if(is_array($arPrices))
				{
					foreach($arPrices as $arPrice)
					{
						$arFindFields["SUB_CATALOG_PRICE_".$arPrice['ID']] = ('' != $strSKUName ? $strSKUName.' - ' : '').sprintf(Loc::getMessage("KDA_EE_CATALOG_PRICE"), $arPrice['NAME_LANG']);
					}
				}
				$arFindFields["SUB_CATALOG_WEIGHT"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_WEIGHT");
				$arFindFields["SUB_CATALOG_LENGTH"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_LENGTH");
				$arFindFields["SUB_CATALOG_WIDTH"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_WIDTH");
				$arFindFields["SUB_CATALOG_HEIGHT"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_HEIGHT");
				$arFindFields["SUB_CATALOG_VAT_INCLUDED"] = ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_VAT_INCLUDED");
			}
			
			if (isset($arSKUProps) && is_array($arSKUProps))
			{
				foreach($arSKUProps as $arProp)
					if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F")
						$arFindFields["IBEL_A_SUB_PROP_".$arProp["ID"]] = ('' != $strSKUName ? $strSKUName.' - ' : '').$arProp["NAME"];
			}
		}
		
		$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
		
		$oFilter->Begin();
		?>
			<tr>
				<td><?echo Loc::getMessage("KDA_EE_FILTER_FROMTO_ID")?>:</td>
				<td nowrap>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_id_start]" size="10" value="<?echo htmlspecialcharsex($arFields['find_el_id_start'])?>">
					...
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_id_end]" size="10" value="<?echo htmlspecialcharsex($arFields['find_el_id_end'])?>">
				</td>
			</tr>

			<tr>
				<td><?echo Loc::getMessage("KDA_EE_FIELD_SECTION_ID")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_section_section][]" multiple size="5">
						<option value="-1"><?echo Loc::getMessage("KDA_EE_VALUE_ANY")?></option>
						<option value="0"<?if((is_array($arFields['find_section_section']) && in_array("0", $arFields['find_section_section'])) || $arFields['find_section_section']=="0")echo" selected"?>><?echo Loc::getMessage("KDA_EE_UPPER_LEVEL")?></option>
						<?
						$bsections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
						while($ar = $bsections->GetNext()):
							?><option value="<?echo $ar["ID"]?>"<?if((is_array($arFields['find_section_section']) && in_array($ar["ID"], $arFields['find_section_section'])) || $ar["ID"]==$arFields['find_section_section'])echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $ar["DEPTH_LEVEL"])?><?echo $ar["NAME"]?></option><?
						endwhile;
						?>
					</select><br>
					<input type="checkbox" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_subsections]" value="Y"<?if($arFields['find_el_subsections']=="Y")echo" checked"?>> <?echo Loc::getMessage("KDA_EE_INCLUDING_SUBSECTIONS")?>
				</td>
			</tr>

			<?
			$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_el_timestamp_from]_FILTER_PERIOD"] = $arFields['find_el_timestamp_from_FILTER_PERIOD'];
			$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_el_timestamp_from]_FILTER_DIRECTION"] = $arFields['find_el_timestamp_from_FILTER_DIRECTION'];
			?>
			<tr>
				<td><?echo Loc::getMessage("KDA_EE_FIELD_TIMESTAMP_X")?>:</td>
				<td data-filter-period="<?echo htmlspecialcharsex($arFields['find_el_timestamp_from_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields['find_el_timestamp_from_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_el_timestamp_from]", htmlspecialcharsex($arFields['find_el_timestamp_from']), "SETTINGS[FILTER][".$listIndex."][find_el_timestamp_to]", htmlspecialcharsex($arFields['find_el_timestamp_to']), "dataload", "Y")?></td>
			</tr>

			<tr>
				<td><?=Loc::getMessage("KDA_EE_FIELD_MODIFIED_BY")?>:</td>
				<td>
					<?echo FindUserID(
						"SETTINGS[FILTER][".$listIndex."][find_el_modified_user_id]",
						$arFields['find_el_modified_user_id'],
						"",
						"dataload",
						"5",
						"",
						" ... ",
						"",
						""
					);?>
				</td>
			</tr>

			<?
			$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_el_created_from]_FILTER_PERIOD"] = $arFields['find_el_created_from_FILTER_PERIOD'];
			$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_el_created_from]_FILTER_DIRECTION"] = $arFields['find_el_created_from_FILTER_DIRECTION'];
			?>
			<tr>
				<td><?echo Loc::getMessage("KDA_EE_EL_ADMIN_DCREATE")?>:</td>
				<td data-filter-period="<?echo htmlspecialcharsex($arFields['find_el_created_from_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields['find_el_created_from_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_el_created_from]", htmlspecialcharsex($arFields['find_el_created_from']), "SETTINGS[FILTER][".$listIndex."][find_el_created_to]", htmlspecialcharsex($arFields['find_el_created_to']), "dataload", "Y")?></td>
			</tr>

			<tr>
				<td><?echo Loc::getMessage("KDA_EE_EL_ADMIN_WCREATE")?></td>
				<td>
					<?echo FindUserID(
						"SETTINGS[FILTER][".$listIndex."][find_el_created_user_id]",
						$arFields['find_el_created_user_id'],
						"",
						"dataload",
						"5",
						"",
						" ... ",
						"",
						""
					);?>
				</td>
			</tr>

			<tr class="kda-ee-filter-date-wrap">
				<td><?echo Loc::getMessage("KDA_EE_EL_A_ACTFROM")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_vtype_active_from]"><option value=""><?echo Loc::getMessage("KDA_EE_IS_VALUE_FROM_TO")?></option><option value="empty"<?if($arFields['find_el_vtype_active_from']=='empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_EMPTY")?></option><option value="not_empty"<?if($arFields['find_el_vtype_active_from']=='not_empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_NOT_EMPTY")?></option></select></select>
					<?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_el_date_active_from_from]", htmlspecialcharsex($arFields['find_el_date_active_from_from']), "SETTINGS[FILTER][".$listIndex."][find_el_date_active_from_to]", htmlspecialcharsex($arFields['find_el_date_active_from_to']), "dataload")?>
				</td>
			</tr>

			<tr class="kda-ee-filter-date-wrap">
				<td><?echo Loc::getMessage("KDA_EE_EL_A_ACTTO")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_vtype_date_active_to]"><option value=""><?echo Loc::getMessage("KDA_EE_IS_VALUE_FROM_TO")?></option><option value="empty"<?if($arFields['find_el_vtype_date_active_to']=='empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_EMPTY")?></option><option value="not_empty"<?if($arFields['find_el_vtype_date_active_to']=='not_empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_NOT_EMPTY")?></option></select></select>
					<?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_el_date_active_to_from]", htmlspecialcharsex($arFields['find_el_date_active_to_from']), "SETTINGS[FILTER][".$listIndex."][find_el_date_active_to_to]", htmlspecialcharsex($arFields['find_el_date_active_to_to']), "dataload")?>
				</td>
			</tr>

			<tr>
				<td><?echo Loc::getMessage("KDA_EE_FIELD_ACTIVE")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_active]">
						<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
						<option value="Y"<?if($arFields['find_el_active']=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_YES"))?></option>
						<option value="N"<?if($arFields['find_el_active']=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_NO"))?></option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td><?echo Loc::getMessage("KDA_EE_FIELD_SORT")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_sort_comp]">
						<option value="eq" <?if($arFields['find_el_sort_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_el_sort_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_el_sort_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_el_sort_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_el_sort_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_sort]" value="<?echo htmlspecialcharsex($arFields['find_el_sort'])?>" size="10">
				</td>
			</tr>

			<tr>
				<td><?echo Loc::getMessage("KDA_EE_FIELD_NAME")?>:</td>
				<td><input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_name]" value="<?echo htmlspecialcharsex($arFields['find_el_name'])?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
			</tr>
			<tr>
				<td><?echo Loc::getMessage("KDA_EE_EL_ADMIN_PREDESC")?></td>
				<td><select class="kda-ee-filter-chval" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_vtype_pretext]"><option value=""><?echo Loc::getMessage("KDA_EE_IS_VALUE")?></option><option value="empty"<?if($arFields['find_el_vtype_pretext']=='empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_EMPTY")?></option><option value="not_empty"<?if($arFields['find_el_vtype_pretext']=='not_empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_NOT_EMPTY")?></option></select><input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_pretext]" value="<?echo htmlspecialcharsex($arFields['find_el_pretext'])?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
			</tr>
			<tr>
				<td><?echo Loc::getMessage("KDA_EE_EL_ADMIN_DESC")?></td>
				<td><select class="kda-ee-filter-chval" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_vtype_intext]"><option value=""><?echo Loc::getMessage("KDA_EE_IS_VALUE")?></option><option value="empty"<?if($arFields['find_el_vtype_intext']=='empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_EMPTY")?></option><option value="not_empty"<?if($arFields['find_el_vtype_intext']=='not_empty'){echo ' selected';}?>><?echo Loc::getMessage("KDA_EE_IS_NOT_EMPTY")?></option></select><input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_intext]" value="<?echo htmlspecialcharsex($arFields['find_el_intext'])?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
			</tr>

			<tr>
				<td><?=Loc::getMessage("KDA_EE_EL_A_CODE")?>:</td>
				<td><input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_code]" value="<?echo htmlspecialcharsex($arFields['find_el_code'])?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
			</tr>
			<tr>
				<td><?=Loc::getMessage("KDA_EE_EL_A_EXTERNAL_ID")?>:</td>
				<td><input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_external_id]" value="<?echo htmlspecialcharsex($arFields['find_el_external_id'])?>" size="30"></td>
			</tr>
			<tr>
				<td><?=Loc::getMessage("KDA_EE_EL_A_PREVIEW_PICTURE")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_preview_picture]">
						<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
						<option value="Y"<?if($arFields['find_el_preview_picture']=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_IS_NOT_EMPTY"))?></option>
						<option value="N"<?if($arFields['find_el_preview_picture']=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_IS_EMPTY"))?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?=Loc::getMessage("KDA_EE_EL_A_DETAIL_PICTURE")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_detail_picture]">
						<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
						<option value="Y"<?if($arFields['find_el_detail_picture']=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_IS_NOT_EMPTY"))?></option>
						<option value="N"<?if($arFields['find_el_detail_picture']=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_IS_EMPTY"))?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?=Loc::getMessage("KDA_EE_EL_A_TAGS")?>:</td>
				<td>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_tags]" value="<?echo htmlspecialcharsex($arFields['find_el_tags'])?>" size="30">
				</td>
			</tr>
			<?
			if ($bCatalog)
			{
				if(is_array($productTypeList))
				{
				?><tr>
					<td><?=Loc::getMessage("KDA_EE_CATALOG_TYPE"); ?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_type][]" multiple>
							<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
							<?
							$catalogTypes = (!empty($arFields['find_el_catalog_type']) ? $arFields['find_el_catalog_type'] : array());
							foreach ($productTypeList as $productType => $productTypeName)
							{
								?>
								<option value="<? echo $productType; ?>"<? echo (in_array($productType, $catalogTypes) ? ' selected' : ''); ?>><? echo htmlspecialcharsex($productTypeName); ?></option><?
							}
							unset($productType, $productTypeName, $catalogTypes);
							?>
						</select>
					</td>
				</tr>
				<?
				}
				?>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_BUNDLE")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_bundle]">
							<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
							<option value="Y"<?if($arFields['find_el_catalog_bundle']=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_YES"))?></option>
							<option value="N"<?if($arFields['find_el_catalog_bundle']=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_NO"))?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_AVAILABLE")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_available]">
							<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
							<option value="Y"<?if($arFields['find_el_catalog_available']=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_YES"))?></option>
							<option value="N"<?if($arFields['find_el_catalog_available']=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_NO"))?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_QUANTITY")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_quantity_comp]">
							<option value="eq" <?if($arFields['find_el_catalog_quantity_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="gt" <?if($arFields['find_el_catalog_quantity_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
							<option value="geq" <?if($arFields['find_el_catalog_quantity_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
							<option value="lt" <?if($arFields['find_el_catalog_quantity_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
							<option value="leq" <?if($arFields['find_el_catalog_quantity_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						</select>
						<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_quantity]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_quantity'])?>" size="10">
					</td>
				</tr>
				
				<?
				if(is_array($arStores))
				{
					foreach($arStores as $arStore)
					{
						?>
						<tr>
							<td><?echo sprintf(Loc::getMessage("KDA_EE_CATALOG_STORE_QUANTITY"), $arStore['TITLE'])?>:</td>
							<td>
								<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_store<?echo $arStore['ID'];?>_quantity_comp]">
									<option value="eq" <?if($arFields['find_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
									<option value="gt" <?if($arFields['find_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
									<option value="geq" <?if($arFields['find_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
									<option value="lt" <?if($arFields['find_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
									<option value="leq" <?if($arFields['find_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
								</select>
								<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_store<?echo $arStore['ID'];?>_quantity]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_store'.$arStore['ID'].'_quantity'])?>" size="10">
							</td>
						</tr>
						<?
					}
					
					if(count($arStores) > 0)
					{
					?>
						<tr>
							<td><?echo Loc::getMessage("KDA_EE_CATALOG_STORE_ANY_QUANTITY")?>:</td>
							<td>
								<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_store_any_quantity_stores][]" multiple>
									<?foreach($arStores as $arStore){?>
										<option value="<?echo $arStore['ID']?>" <?if(is_array($arFields['find_el_catalog_store_any_quantity_stores']) && in_array($arStore['ID'], $arFields['find_el_catalog_store_any_quantity_stores'])){echo 'selected';}?>><?echo $arStore['TITLE']?></option>
									<?}?>
								</select>
								<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_store_any_quantity_comp]">
									<option value="eq" <?if($arFields['find_el_catalog_store_any_quantity_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
									<option value="gt" <?if($arFields['find_el_catalog_store_any_quantity_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
									<option value="geq" <?if($arFields['find_el_catalog_store_any_quantity_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
									<option value="lt" <?if($arFields['find_el_catalog_store_any_quantity_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
									<option value="leq" <?if($arFields['find_el_catalog_store_any_quantity_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
								</select>
								<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_store_any_quantity]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_store_any_quantity'])?>" size="10">
							</td>
						</tr>
					<?
					}
				}
				?>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_PURCHASING_PRICE")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_purchasing_price_comp]">
							<option value="eq" <?if($arFields['find_el_catalog_purchasing_price_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="gt" <?if($arFields['find_el_catalog_purchasing_price_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
							<option value="geq" <?if($arFields['find_el_catalog_purchasing_price_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
							<option value="lt" <?if($arFields['find_el_catalog_purchasing_price_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
							<option value="leq" <?if($arFields['find_el_catalog_purchasing_price_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							<option value="from_to" <?if($arFields['find_el_catalog_purchasing_price_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
						</select>
						<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_purchasing_price]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_purchasing_price'])?>" size="10">
					</td>
				</tr>
				<?
				if(is_array($arPrices))
				{
					foreach($arPrices as $arPrice)
					{
						?>
						<tr>
							<td><?echo sprintf(Loc::getMessage("KDA_EE_CATALOG_PRICE"), $arPrice['NAME_LANG'])?>:</td>
							<td>
								<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_price_<?echo $arPrice['ID'];?>_comp]">
									<option value="eq" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
									<option value="empty" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EMPTY')?></option>
									<option value="gt" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
									<option value="geq" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
									<option value="lt" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
									<option value="leq" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
									<option value="from_to" <?if($arFields['find_el_catalog_price_'.$arPrice['ID'].'_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
								</select>
								<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_price_<?echo $arPrice['ID'];?>]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_price_'.$arPrice['ID']])?>" size="10">
							</td>
						</tr>
						<?
					}
				}
				?>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_WEIGHT")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_weight_comp]">
							<option value="eq" <?if($arFields['find_el_catalog_weight_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="gt" <?if($arFields['find_el_catalog_weight_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
							<option value="geq" <?if($arFields['find_el_catalog_weight_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
							<option value="lt" <?if($arFields['find_el_catalog_weight_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
							<option value="leq" <?if($arFields['find_el_catalog_weight_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							<option value="from_to" <?if($arFields['find_el_catalog_weight_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
						</select>
						<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_weight]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_weight'])?>" size="10">
					</td>
				</tr>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_LENGTH")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_length_comp]">
							<option value="eq" <?if($arFields['find_el_catalog_length_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="gt" <?if($arFields['find_el_catalog_length_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
							<option value="geq" <?if($arFields['find_el_catalog_length_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
							<option value="lt" <?if($arFields['find_el_catalog_length_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
							<option value="leq" <?if($arFields['find_el_catalog_length_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							<option value="from_to" <?if($arFields['find_el_catalog_length_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
							<option value="empty" <?if($arFields['find_el_catalog_length_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
							<option value="not_empty" <?if($arFields['find_el_catalog_length_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
						</select>
						<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_length]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_length'])?>" size="10">
					</td>
				</tr>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_WIDTH")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_width_comp]">
							<option value="eq" <?if($arFields['find_el_catalog_width_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="gt" <?if($arFields['find_el_catalog_width_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
							<option value="geq" <?if($arFields['find_el_catalog_width_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
							<option value="lt" <?if($arFields['find_el_catalog_width_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
							<option value="leq" <?if($arFields['find_el_catalog_width_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							<option value="from_to" <?if($arFields['find_el_catalog_width_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
							<option value="empty" <?if($arFields['find_el_catalog_width_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
							<option value="not_empty" <?if($arFields['find_el_catalog_width_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
						</select>
						<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_width]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_width'])?>" size="10">
					</td>
				</tr>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_HEIGHT")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_height_comp]">
							<option value="eq" <?if($arFields['find_el_catalog_height_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="gt" <?if($arFields['find_el_catalog_height_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
							<option value="geq" <?if($arFields['find_el_catalog_height_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
							<option value="lt" <?if($arFields['find_el_catalog_height_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
							<option value="leq" <?if($arFields['find_el_catalog_height_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							<option value="from_to" <?if($arFields['find_el_catalog_height_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
							<option value="empty" <?if($arFields['find_el_catalog_height_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
							<option value="not_empty" <?if($arFields['find_el_catalog_height_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
						</select>
						<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_height]" value="<?echo htmlspecialcharsex($arFields['find_el_catalog_height'])?>" size="10">
					</td>
				</tr>
				<tr>
					<td><?echo Loc::getMessage("KDA_EE_CATALOG_VAT_INCLUDED")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_catalog_vat_included]">
							<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
							<option value="Y"<?if($arFields["find_el_catalog_vat_included"]=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_YES"))?></option>
							<option value="N"<?if($arFields["find_el_catalog_vat_included"]=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_NO"))?></option>
						</select>
					</td>
				</tr>
				<?
				
				if($bSale)
				{
					?>
					<tr>
						<td><?=Loc::getMessage("KDA_EE_EL_A_SALE_ORDER")?>:</td>
						<td>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_sale_order]" value="<?echo htmlspecialcharsex($arFields['find_el_sale_order'])?>" size="30">
						</td>
					</tr>
					
					<?
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_el_sale_order_date_insert_from]_FILTER_PERIOD"] = $arFields['find_el_sale_order_date_insert_from_FILTER_PERIOD'];
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_el_sale_order_date_insert_from]_FILTER_DIRECTION"] = $arFields['find_el_sale_order_date_insert_from_FILTER_DIRECTION'];
					?>
					<tr>
						<td><?echo Loc::getMessage("KDA_EE_EL_A_SALE_ORDER_DATE_INSERT")?>:</td>
						<td data-filter-period="<?echo htmlspecialcharsex($arFields['find_el_sale_order_date_insert_from_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields['find_el_sale_order_date_insert_from_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_el_sale_order_date_insert_from]", htmlspecialcharsex($arFields['find_el_sale_order_date_insert_from']), "SETTINGS[FILTER][".$listIndex."][find_el_sale_order_date_insert_to]", htmlspecialcharsex($arFields['find_el_sale_order_date_insert_to']), "dataload", "Y")?></td>
					</tr>
					<?
				}
			}
			
		foreach($arProps as $arProp):
			if($arProp["FILTRABLE"]=="Y" || $arProp["PROPERTY_TYPE"]=="F"):
		?>
		<tr>
			<td><?=$arProp["NAME"]?>:</td>
			<?
			if($arProp["PROPERTY_TYPE"]=='S' && in_array($arProp['USER_TYPE'], array('Date', 'DateTime')))
			{
				$fieldName = "find_el_property_".$arProp["ID"];
				$GLOBALS["SETTINGS[FILTER][".$listIndex."][".$fieldName."_from]_FILTER_PERIOD"] = $arFields[$fieldName.'_from_FILTER_PERIOD'];
				$GLOBALS["SETTINGS[FILTER][".$listIndex."][".$fieldName."_from]_FILTER_DIRECTION"] = $arFields[$fieldName.'_from_FILTER_DIRECTION'];
				?>
				<td data-filter-period="<?echo htmlspecialcharsex($arFields[$fieldName.'_from_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields[$fieldName.'_from_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][".$fieldName."_from]", htmlspecialcharsex($arFields[$fieldName.'_from']), "SETTINGS[FILTER][".$listIndex."][".$fieldName."_to]", htmlspecialcharsex($arFields[$fieldName.'_to']), "dataload", "Y")?></td>
				<?
			}
			else
			{
			?>
			<td>
				<?if(array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
					$fieldName = "filter_".$listIndex."_find_el_property_".$arProp["ID"];
					if(isset($arFields["find_el_property_".$arProp["ID"]."_from"])) $GLOBALS[$fieldName."_from"] = $arFields["find_el_property_".$arProp["ID"]."_from"];
					if(isset($arFields["find_el_property_".$arProp["ID"]."_to"])) $GLOBALS[$fieldName."_to"] = $arFields["find_el_property_".$arProp["ID"]."_to"];
					$GLOBALS[$fieldName] = $arFields["find_el_property_".$arProp["ID"]];
					$GLOBALS['set_filter'] = 'Y';
					echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
						$arProp,
						array(
							"VALUE" => $fieldName,
							"TABLE_ID" => $sTableID,
						),
					));
				elseif($arProp["PROPERTY_TYPE"]=='S'):
					if(is_array($arFields["find_el_property_".$arProp["ID"]]) && isset($arFields["find_el_property_".$arProp["ID"]]['TYPE'])) $arFields["find_el_property_".$arProp["ID"]] = '';
				?>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>_comp]">
						<option value="eq" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="neq" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='neq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_NEQ')?></option>
						<option value="contain" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='contain'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_CONTAIN')?></option>
						<option value="not_contain" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='not_contain'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_NOT_CONTAIN')?></option>
						<option value="empty" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
						<option value="not_empty" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
						<option value="logical" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='logical'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LOGICAL')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>]" value="<?echo htmlspecialcharsex(is_array($arFields["find_el_property_".$arProp["ID"]]) ? '' : $arFields["find_el_property_".$arProp["ID"]])?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
				<?elseif($arProp["PROPERTY_TYPE"]=='N'):?>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>_comp]">
						<option value="eq" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						<option value="from_to" <?if($arFields['find_el_property_'.$arProp["ID"].'_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>]" value="<?echo htmlspecialcharsex(is_array($arFields["find_el_property_".$arProp["ID"]]) ? '' : $arFields["find_el_property_".$arProp["ID"]])?>" size="10">
				<?elseif($arProp["PROPERTY_TYPE"]=='E'):?>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>]" value="<?echo htmlspecialcharsex(is_array($arFields["find_el_property_".$arProp["ID"]]) ? '' : $arFields["find_el_property_".$arProp["ID"]])?>" size="30">
				<?elseif($arProp["PROPERTY_TYPE"]=='L'):?>
					<?
					$propVal = $arFields["find_el_property_".$arProp["ID"]];
					if(!is_array($propVal)) $propVal = array($propVal);
					?>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>][]" multiple size="5">
						<option value=""><?echo Loc::getMessage("KDA_EE_VALUE_ANY")?></option>
						<option value="NOT_REF"<?if(in_array("NOT_REF", $propVal))echo " selected"?>><?echo Loc::getMessage("KDA_EE_ELEMENT_EDIT_NOT_SET")?></option><?
						$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC", "NAME"=>"ASC"), Array("PROPERTY_ID"=>$arProp["ID"]));
						while($arPEnum = $dbrPEnum->GetNext()):
						?>
							<option value="<?=$arPEnum["ID"]?>"<?if(in_array($arPEnum["ID"], $propVal))echo " selected"?>><?=$arPEnum["VALUE"]?></option>
						<?
						endwhile;
				?></select>
				<?
				elseif($arProp["PROPERTY_TYPE"]=='G'):
					echo self::ShowGroupPropertyField2('SETTINGS[FILTER]['.$listIndex.'][find_el_property_'.$arProp["ID"].']', $arProp, $arFields["find_el_property_".$arProp["ID"]]);
				elseif($arProp["PROPERTY_TYPE"]=='F'):
				?>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_el_property_<?=$arProp["ID"]?>]">
						<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
						<option value="Y"<?if($arFields["find_el_property_".$arProp["ID"]]=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_IS_NOT_EMPTY"))?></option>
						<option value="N"<?if($arFields["find_el_property_".$arProp["ID"]]=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_IS_EMPTY"))?></option>
					</select>
				<?
				elseif(array_key_exists("GetPropertyFieldHtml", $arProp["PROPERTY_USER_TYPE"])):
					$inputHTML = call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetPropertyFieldHtml"], array(
						$arProp,
						array(
							"VALUE" =>  $arFields["find_el_property_".$arProp["ID"]],
							"DESCRIPTION" => '',
						),
						array(
							"VALUE" => "filter_".$listIndex."_find_el_property_".$arProp["ID"],
							"DESCRIPTION" => '',
							"MODE"=>"iblock_element_admin",
							"FORM_NAME"=>"dataload"
						),
					));
					$inputHTML = '<table style="margin: 0 0 5px 12px;"><tr id="tr_PROPERTY_'.$arProp["ID"].'"><td>'.$inputHTML.'</td></tr></table>';
					//$inputHTML = '<span class="adm-select-wrap">'.$inputHTML.'</span>';
					if(class_exists('\Bitrix\Main\Page\Asset') && class_exists('\Bitrix\Main\Page\AssetShowTargetType'))
					{
						$inputHTML = \Bitrix\Main\Page\Asset::getInstance()->GetJs(\Bitrix\Main\Page\AssetShowTargetType::TEMPLATE_PAGE).\Bitrix\Main\Page\Asset::getInstance()->GetCss(\Bitrix\Main\Page\AssetShowTargetType::TEMPLATE_PAGE).$inputHTML;
					}
					echo $inputHTML;
				endif;
				?>
			</td>
		</tr>
		<?
			}
			endif;
		endforeach;

		if($boolSKU){
		?>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_FILTER_FROMTO_ID")?>:</td>
				<td nowrap>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_id_start]" size="10" value="<?echo htmlspecialcharsex($arFields['find_sub_el_id_start'])?>">
					...
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_id_end]" size="10" value="<?echo htmlspecialcharsex($arFields['find_sub_el_id_end'])?>">
				</td>
			</tr>
			<?
			$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_sub_el_timestamp_from]_FILTER_PERIOD"] = $arFields['find_sub_el_timestamp_from_FILTER_PERIOD'];
			$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_sub_el_timestamp_from]_FILTER_DIRECTION"] = $arFields['find_sub_el_timestamp_from_FILTER_DIRECTION'];
			?>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_FIELD_TIMESTAMP_X")?>:</td>
				<td data-filter-period="<?echo htmlspecialcharsex($arFields['find_sub_el_timestamp_from_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields['find_sub_el_timestamp_from_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_sub_el_timestamp_from]", htmlspecialcharsex($arFields['find_sub_el_timestamp_from']), "SETTINGS[FILTER][".$listIndex."][find_sub_el_timestamp_to]", htmlspecialcharsex($arFields['find_sub_el_timestamp_to']), "dataload", "Y")?></td>
			</tr>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_FIELD_ACTIVE")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_active]">
						<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
						<option value="Y"<?if($arFields['find_sub_el_active']=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_YES"))?></option>
						<option value="N"<?if($arFields['find_sub_el_active']=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_NO"))?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_FIELD_SORT")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_sort_comp]">
						<option value="eq" <?if($arFields['find_sub_el_sort_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_sort_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_sort_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_sort_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_sort_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_sort]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_sort'])?>" size="10">
				</td>
			</tr>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_QUANTITY")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_quantity_comp]">
						<option value="eq" <?if($arFields['find_sub_el_catalog_quantity_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_catalog_quantity_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_catalog_quantity_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_catalog_quantity_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_catalog_quantity_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_quantity]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_quantity'])?>" size="10">
				</td>
			</tr>
			
			<?
			if(is_array($arStores))
			{
				foreach($arStores as $arStore)
				{
					?>
					<tr>
						<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').sprintf(Loc::getMessage("KDA_EE_CATALOG_STORE_QUANTITY"), $arStore['TITLE'])?>:</td>
						<td>
							<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_store<?echo $arStore['ID'];?>_quantity_comp]">
								<option value="eq" <?if($arFields['find_sub_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
								<option value="gt" <?if($arFields['find_sub_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
								<option value="geq" <?if($arFields['find_sub_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
								<option value="lt" <?if($arFields['find_sub_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
								<option value="leq" <?if($arFields['find_sub_el_catalog_store'.$arStore['ID'].'_quantity_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							</select>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_store<?echo $arStore['ID'];?>_quantity]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_store'.$arStore['ID'].'_quantity'])?>" size="10">
						</td>
					</tr>
					<?
				}
				
				if(count($arStores) > 0) 
				{
				?>
					<tr>
						<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_STORE_ANY_QUANTITY")?>:</td>
						<td>
							<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_store_any_quantity_stores][]" multiple>
								<?foreach($arStores as $arStore){?>
									<option value="<?echo $arStore['ID']?>" <?if(is_array($arFields['find_sub_el_catalog_store_any_quantity_stores']) && in_array($arStore['ID'], $arFields['find_sub_el_catalog_store_any_quantity_stores'])){echo 'selected';}?>><?echo $arStore['TITLE']?></option>
								<?}?>
							</select>
							<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_store_any_quantity_comp]">
								<option value="eq" <?if($arFields['find_sub_el_catalog_store_any_quantity_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
								<option value="gt" <?if($arFields['find_sub_el_catalog_store_any_quantity_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
								<option value="geq" <?if($arFields['find_sub_el_catalog_store_any_quantity_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
								<option value="lt" <?if($arFields['find_sub_el_catalog_store_any_quantity_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
								<option value="leq" <?if($arFields['find_sub_el_catalog_store_any_quantity_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
							</select>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_store_any_quantity]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_store_any_quantity'])?>" size="10">
						</td>
					</tr>
				<?
				}
			}
			?>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_PURCHASING_PRICE")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_purchasing_price_comp]">
						<option value="eq" <?if($arFields['find_sub_el_catalog_purchasing_price_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_catalog_purchasing_price_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_catalog_purchasing_price_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_catalog_purchasing_price_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_catalog_purchasing_price_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						<option value="from_to" <?if($arFields['find_sub_el_catalog_purchasing_price_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_purchasing_price]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_purchasing_price'])?>" size="10">
				</td>
			</tr>
			<?
			if(is_array($arPrices))
			{
				foreach($arPrices as $arPrice)
				{
					?>
					<tr>
						<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').sprintf(Loc::getMessage("KDA_EE_CATALOG_PRICE"), $arPrice['NAME_LANG'])?>:</td>
						<td>
							<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_price_<?echo $arPrice['ID'];?>_comp]">
								<option value="eq" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
								<option value="empty" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EMPTY')?></option>
								<option value="gt" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
								<option value="geq" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
								<option value="lt" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
								<option value="leq" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
								<option value="from_to" <?if($arFields['find_sub_el_catalog_price_'.$arPrice['ID'].'_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
							</select>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_price_<?echo $arPrice['ID'];?>]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_price_'.$arPrice['ID']])?>" size="10">
						</td>
					</tr>
					<?
				}
			}
			?>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_WEIGHT")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_weight_comp]">
						<option value="eq" <?if($arFields['find_sub_el_catalog_weight_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_catalog_weight_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_catalog_weight_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_catalog_weight_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_catalog_weight_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						<option value="from_to" <?if($arFields['find_sub_el_catalog_weight_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_weight]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_weight'])?>" size="10">
				</td>
			</tr>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_LENGTH")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_length_comp]">
						<option value="eq" <?if($arFields['find_sub_el_catalog_length_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_catalog_length_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_catalog_length_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_catalog_length_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_catalog_length_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						<option value="from_to" <?if($arFields['find_sub_el_catalog_length_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
						<option value="empty" <?if($arFields['find_sub_el_catalog_length_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
						<option value="not_empty" <?if($arFields['find_sub_el_catalog_length_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_length]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_length'])?>" size="10">
				</td>
			</tr>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_WIDTH")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_width_comp]">
						<option value="eq" <?if($arFields['find_sub_el_catalog_width_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_catalog_width_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_catalog_width_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_catalog_width_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_catalog_width_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						<option value="from_to" <?if($arFields['find_sub_el_catalog_width_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
						<option value="empty" <?if($arFields['find_sub_el_catalog_width_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
						<option value="not_empty" <?if($arFields['find_sub_el_catalog_width_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_width]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_width'])?>" size="10">
				</td>
			</tr>
			<tr>
				<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_HEIGHT")?>:</td>
				<td>
					<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_height_comp]">
						<option value="eq" <?if($arFields['find_sub_el_catalog_height_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
						<option value="gt" <?if($arFields['find_sub_el_catalog_height_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
						<option value="geq" <?if($arFields['find_sub_el_catalog_height_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
						<option value="lt" <?if($arFields['find_sub_el_catalog_height_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
						<option value="leq" <?if($arFields['find_sub_el_catalog_height_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
						<option value="from_to" <?if($arFields['find_sub_el_catalog_height_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
						<option value="empty" <?if($arFields['find_sub_el_catalog_height_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
						<option value="not_empty" <?if($arFields['find_sub_el_catalog_height_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
					</select>
					<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_height]" value="<?echo htmlspecialcharsex($arFields['find_sub_el_catalog_height'])?>" size="10">
				</td>
			</tr>
				<tr>
					<td><?echo ('' != $strSKUName ? $strSKUName.' - ' : '').Loc::getMessage("KDA_EE_CATALOG_VAT_INCLUDED")?>:</td>
					<td>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_catalog_vat_included]">
							<option value=""><?=htmlspecialcharsex(Loc::getMessage('KDA_EE_VALUE_ANY'))?></option>
							<option value="Y"<?if($arFields["find_sub_el_catalog_vat_included"]=="Y")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_YES"))?></option>
							<option value="N"<?if($arFields["find_sub_el_catalog_vat_included"]=="N")echo " selected"?>><?=htmlspecialcharsex(Loc::getMessage("KDA_EE_NO"))?></option>
						</select>
					</td>
				</tr>
			<?
			
			if(isset($arSKUProps) && is_array($arSKUProps))
			{
				foreach($arSKUProps as $arProp):
					if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F"):
				?>
				<tr>
					<td><? echo ('' != $strSKUName ? $strSKUName.' - ' : ''), $arProp["NAME"]; ?>:</td>
					<?
					if($arProp["PROPERTY_TYPE"]=='S' && in_array($arProp['USER_TYPE'], array('Date', 'DateTime')))
					{
						$fieldName = "find_sub_el_property_".$arProp["ID"];
						$GLOBALS["SETTINGS[FILTER][".$listIndex."][".$fieldName."_from]_FILTER_PERIOD"] = $arFields[$fieldName.'_from_FILTER_PERIOD'];
						$GLOBALS["SETTINGS[FILTER][".$listIndex."][".$fieldName."_from]_FILTER_DIRECTION"] = $arFields[$fieldName.'_from_FILTER_DIRECTION'];
						?>
						<td data-filter-period="<?echo htmlspecialcharsex($arFields[$fieldName.'_from_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields[$fieldName.'_from_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][".$fieldName."_from]", htmlspecialcharsex($arFields[$fieldName.'_from']), "SETTINGS[FILTER][".$listIndex."][".$fieldName."_to]", htmlspecialcharsex($arFields[$fieldName.'_to']), "dataload", "Y")?></td>
						<?
					}
					else
					{
					?>
					<td>
						<?if(array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
							$fieldName = "filter_".$listIndex."_find_sub_el_property_".$arProp["ID"];
							if(isset($arFields["find_sub_el_property_".$arProp["ID"]."_from"])) $GLOBALS[$fieldName."_from"] = $arFields["find_sub_el_property_".$arProp["ID"]."_from"];
							if(isset($arFields["find_sub_el_property_".$arProp["ID"]."_to"])) $GLOBALS[$fieldName."_to"] = $arFields["find_sub_el_property_".$arProp["ID"]."_to"];
							$GLOBALS[$fieldName] = $arFields["find_sub_el_property_".$arProp["ID"]];
							$GLOBALS['set_filter'] = 'Y';
							echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
								$arProp,
								array(
									"VALUE" => $fieldName,
									"TABLE_ID" => $sTableID,
								),
							));
						elseif($arProp["PROPERTY_TYPE"]=='S'):
							if(is_array($arFields["find_sub_el_property_".$arProp["ID"]]) && isset($arFields["find_sub_el_property_".$arProp["ID"]]['TYPE'])) $arFields["find_sub_el_property_".$arProp["ID"]] = '';
						?>
						<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_property_<?=$arProp["ID"]?>_comp]">
							<option value="eq" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
							<option value="neq" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='neq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_NEQ')?></option>
							<option value="contain" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='contain'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_CONTAIN')?></option>
							<option value="not_contain" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='not_contain'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_NOT_CONTAIN')?></option>
							<option value="empty" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_EMPTY')?></option>
							<option value="not_empty" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='not_empty'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_IS_NOT_EMPTY')?></option>
							<option value="logical" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='logical'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LOGICAL')?></option>
						</select>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_property_<?=$arProp["ID"]?>]" value="<?echo htmlspecialcharsex(is_array($arFields["find_sub_el_property_".$arProp["ID"]]) ? '' : $arFields["find_sub_el_property_".$arProp["ID"]])?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
						<?elseif($arProp["PROPERTY_TYPE"]=='N'):?>
							<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_property_<?=$arProp["ID"]?>_comp]">
								<option value="eq" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='eq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_EQ')?></option>
								<option value="gt" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='gt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GT')?></option>
								<option value="geq" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='geq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_GEQ')?></option>
								<option value="lt" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='lt'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LT')?></option>
								<option value="leq" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='leq'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_LEQ')?></option>
								<option value="from_to" <?if($arFields['find_sub_el_property_'.$arProp["ID"].'_comp']=='from_to'){echo 'selected';}?>><?=Loc::getMessage('KDA_EE_COMPARE_FROM_TO')?></option>
							</select>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_property_<?=$arProp["ID"]?>]" value="<?echo htmlspecialcharsex(is_array($arFields["find_sub_el_property_".$arProp["ID"]]) ? '' : $arFields["find_sub_el_property_".$arProp["ID"]])?>" size="10">
						<?elseif($arProp["PROPERTY_TYPE"]=='E'):?>
							<input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_property_<?=$arProp["ID"]?>]" value="<?echo htmlspecialcharsex(is_array($arFields["find_sub_el_property_".$arProp["ID"]]) ? '' : $arFields["find_sub_el_property_".$arProp["ID"]])?>" size="30">
						<?elseif($arProp["PROPERTY_TYPE"]=='L'):?>
							<?
							$propVal = $arFields["find_sub_el_property_".$arProp["ID"]];
							if(!is_array($propVal)) $propVal = array($propVal);
							?>
							<select name="SETTINGS[FILTER][<?=$listIndex?>][find_sub_el_property_<?=$arProp["ID"]?>][]" multiple size="5">
								<option value=""><?echo Loc::getMessage("KDA_EE_VALUE_ANY")?></option>
								<option value="NOT_REF"<?if(in_array("NOT_REF", $propVal))echo " selected"?>><?echo Loc::getMessage("KDA_EE_ELEMENT_EDIT_NOT_SET")?></option><?
								$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC", "NAME"=>"ASC"), Array("PROPERTY_ID"=>$arProp["ID"]));
								while($arPEnum = $dbrPEnum->GetNext()):
								?>
									<option value="<?=$arPEnum["ID"]?>"<?if(in_array($arPEnum["ID"], $propVal))echo " selected"?>><?=$arPEnum["VALUE"]?></option>
								<?
								endwhile;
						?></select>
						<?
						elseif($arProp["PROPERTY_TYPE"]=='G'):
							echo self::ShowGroupPropertyField2('SETTINGS[FILTER]['.$listIndex.'][find_sub_el_property_'.$arProp["ID"].']', $arProp, $arFields["find_sub_el_property_".$arProp["ID"]]);
						elseif(array_key_exists("GetPropertyFieldHtml", $arProp["PROPERTY_USER_TYPE"])):
							$inputHTML = call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetPropertyFieldHtml"], array(
								$arProp,
								array(
									"VALUE" =>  $arFields["find_sub_el_property_".$arProp["ID"]],
									"DESCRIPTION" => '',
								),
								array(
									"VALUE" => "filter_".$listIndex."_find_sub_el_property_".$arProp["ID"],
									"DESCRIPTION" => '',
									"MODE"=>"iblock_element_admin",
									"FORM_NAME"=>"dataload"
								),
							));
							$inputHTML = '<table style="margin: 0 0 5px 12px;"><tr id="tr_PROPERTY_'.$arProp["ID"].'"><td>'.$inputHTML.'</td></tr></table>';
							//$inputHTML = '<span class="adm-select-wrap">'.$inputHTML.'</span>';
							if(class_exists('\Bitrix\Main\Page\Asset') && class_exists('\Bitrix\Main\Page\AssetShowTargetType'))
							{
								$inputHTML = \Bitrix\Main\Page\Asset::getInstance()->GetJs(\Bitrix\Main\Page\AssetShowTargetType::TEMPLATE_PAGE).\Bitrix\Main\Page\Asset::getInstance()->GetCss(\Bitrix\Main\Page\AssetShowTargetType::TEMPLATE_PAGE).$inputHTML;
							}
							echo $inputHTML;
						endif;
						?>
					</td>
					<?}?>
				</tr>
				<?
					endif;
				endforeach;
			}
		}

		$oFilter->Buttons();
		?><span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="set_filter" value="<? echo Loc::getMessage("admin_lib_filter_set_butt"); ?>" title="<? echo Loc::getMessage("admin_lib_filter_set_butt_title"); ?>" onClick="return EList.ApplyFilter(this);"></span>
		<span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="del_filter" value="<? echo Loc::getMessage("admin_lib_filter_clear_butt"); ?>" title="<? echo Loc::getMessage("admin_lib_filter_clear_butt_title"); ?>" onClick="return EList.DeleteFilter(this);"></span>
		<?
		$oFilter->End();

		?>
		<!--</form>-->
		</div>
		<?
	}
	
	public static function ShowFilterHighload($sTableID, $listIndex, $SETTINGS, $SETTINGS_DEFAULT)
	{
		global $APPLICATION, $USER_FIELD_MANAGER;
		CJSCore::Init('file_input');
		$HLBL_ID = $SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'];
		
		$arFields = (is_array($SETTINGS['FILTER'][$listIndex]) ? $SETTINGS['FILTER'][$listIndex] : array());
		
		$ufEntityId = 'HLBLOCK_'.$HLBL_ID;
		
		?>
		<!--<form method="GET" name="find_form" id="find_form" action="">-->
		<div class="find_form_inner">
		<?
			
		$filterValues = array();
		$arFindFields = array('ID');
		
		$USER_FIELD_MANAGER->AdminListAddFilterFields($ufEntityId, $filterFields);
		$USER_FIELD_MANAGER->AddFindFields($ufEntityId, $arFindFields);

		
		$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
		
		$oFilter->Begin();
		
		?>
		<tr>
			<td>ID</td>
			<td><input type="text" name="SETTINGS[FILTER][<?=$listIndex?>][find_ID]" size="47" value="<?echo htmlspecialcharsbx($arFields['find_ID'])?>"><?=ShowFilterLogicHelp()?></td>
		</tr>
		<?
		//$USER_FIELD_MANAGER->AdminListShowFilter($ufEntityId);
		$arUserFields = $USER_FIELD_MANAGER->GetUserFields($ufEntityId, 0, LANGUAGE_ID);
		foreach($arUserFields as $FIELD_NAME=>$arUserField)
		{
			if($arUserField["SHOW_FILTER"]!="N" && $arUserField["USER_TYPE"]["BASE_TYPE"]!="file")
			{
				if(in_array($arUserField["USER_TYPE_ID"], array('date', 'datetime')))
				{
					/*$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."]_from"] = $arFields['find_'.$FIELD_NAME.'_from'];
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."]_to"] = $arFields['find_'.$FIELD_NAME.'_to'];
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."]_from_FILTER_PERIOD"] = $arFields['find_'.$FIELD_NAME.'_from_FILTER_PERIOD'];
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."]_from_FILTER_DIRECTION"] = $arFields['find_'.$FIELD_NAME.'_from_FILTER_DIRECTION'];
					$inputHTML = $USER_FIELD_MANAGER->GetFilterHTML($arUserField, 'SETTINGS[FILTER]['.$listIndex.'][find_'.$FIELD_NAME.']', $arFields['find_'.$FIELD_NAME]);
					$inputHTML = preg_replace('/(name="[^"]*)\](_[^\]]*)"/Uis', '$1$2]"', $inputHTML);
					//$inputHTML = preg_replace('/(id="[^"]*)_calendar_from([^"]*")/Uis', '$1$2', $inputHTML);
					$inputHTML = //preg_replace('/(field\s*:\s*\''.preg_quote('SETTINGS[FILTER]['.$listIndex.'][find_UF_DATE'.$FIELD_NAME.']_from').')(\')/Uis', '$1_calendar_from$2', $inputHTML);
					$inputHTML = preg_replace('/^(\s*<tr[^>]*>\s*<td[^>]*>[^<]*<\/td>\s*<td[^>]*)/Uis', '$1  data-filter-period="'.htmlspecialcharsex($arFields['find_'.$FIELD_NAME.'_from_FILTER_PERIOD']).'" data-filter-last-days="'.htmlspecialcharsex($arFields['find_'.$FIELD_NAME.'_from_FILTER_LAST_DAYS']).'"', $inputHTML);*/
					
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."]_FILTER_PERIOD"] = $arFields['find_".$FIELD_NAME."_FILTER_PERIOD'];
					$GLOBALS["SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."]_FILTER_DIRECTION"] = $arFields['find_".$FIELD_NAME."_DIRECTION'];
					?>
					<tr><td><?echo ($arUserField['LIST_FILTER_LABEL'] ? $arUserField['LIST_FILTER_LABEL'] : $arUserField['FIELD_NAME']);?>:</td>
					<td data-filter-period="<?echo htmlspecialcharsex($arFields['find_'.$FIELD_NAME.'_FILTER_PERIOD'])?>" data-filter-last-days="<?echo htmlspecialcharsex($arFields['find_'.$FIELD_NAME.'_FILTER_LAST_DAYS'])?>"><?echo CalendarPeriod("SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."_from]", htmlspecialcharsex($arFields['find_".$FIELD_NAME."_from']), "SETTINGS[FILTER][".$listIndex."][find_".$FIELD_NAME."_to]", htmlspecialcharsex($arFields['find_".$FIELD_NAME."_to']), "dataload", "Y")?></td></tr>
					<?
				}
				else
				{
					$inputHTML = $USER_FIELD_MANAGER->GetFilterHTML($arUserField, 'SETTINGS[FILTER]['.$listIndex.'][find_'.$FIELD_NAME.']', $arFields['find_'.$FIELD_NAME]);
				}
				echo $inputHTML;
			}
		}
	
		$oFilter->Buttons();
		?><span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="set_filter" value="<? echo Loc::getMessage("admin_lib_filter_set_butt"); ?>" title="<? echo Loc::getMessage("admin_lib_filter_set_butt_title"); ?>" onClick="return EList.ApplyFilter(this);"></span>
		<span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="del_filter" value="<? echo Loc::getMessage("admin_lib_filter_clear_butt"); ?>" title="<? echo Loc::getMessage("admin_lib_filter_clear_butt_title"); ?>" onClick="return EList.DeleteFilter(this);"></span>
		<?
		$oFilter->End();

		?>
		<!--</form>-->
		</div>
		<?
	}
	
	public static function ShowGroupPropertyField2($name, $property_fields, $values)
	{
		if(!is_array($values)) $values = Array();

		$res = "";
		$result = "";
		$bWas = false;
		$sections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$property_fields["LINK_IBLOCK_ID"]), array("ID", "NAME", "DEPTH_LEVEL"));
		while($ar = $sections->GetNext())
		{
			$res .= '<option value="'.$ar["ID"].'"';
			if(in_array($ar["ID"], $values))
			{
				$bWas = true;
				$res .= ' selected';
			}
			$res .= '>'.str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"].'</option>';
		}
		$result .= '<select name="'.$name.'[]" size="'.($property_fields["MULTIPLE"]=="Y" ? "5":"1").'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>';
		$result .= '<option value=""'.(!$bWas?' selected':'').'>'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
		$result .= $res;
		$result .= '</select>';
		return $result;
	}
	
	public static function GetCellStyleFormatted($arStyles = array(), $arParams = array(), $arCellStyles = array(), $arRowStyles = array())
	{
		if(!is_array($arStyles)) $arStyles = array();
		if(is_array($arRowStyles)) $arStyles = array_merge($arStyles, $arRowStyles);
		if(is_array($arCellStyles)) $arStyles = array_merge($arStyles, $arCellStyles);
		//if(empty($arStyles)) return '';
		$style = '';
		if(!$arStyles['FONT_FAMILY'] && $arParams['FONT_FAMILY']) $arStyles['FONT_FAMILY'] = $arParams['FONT_FAMILY'];
		if(!$arStyles['FONT_SIZE'] && $arParams['FONT_SIZE']) $arStyles['FONT_SIZE'] = $arParams['FONT_SIZE'];
		if(!$arStyles['FONT_COLOR'] && $arParams['FONT_COLOR']) $arStyles['FONT_COLOR'] = $arParams['FONT_COLOR'];
		if(!$arStyles['STYLE_BOLD'] && $arParams['STYLE_BOLD']) $arStyles['STYLE_BOLD'] = $arParams['STYLE_BOLD'];
		if(!$arStyles['STYLE_ITALIC'] && $arParams['STYLE_ITALIC']) $arStyles['STYLE_ITALIC'] = $arParams['STYLE_ITALIC'];
		
		if($arStyles['FONT_FAMILY']) $style .= 'font-family:'.htmlspecialcharsex($arStyles['FONT_FAMILY']).';';
		if((int)$arStyles['FONT_SIZE'] > 0) $style .= 'font-size:'.((int)$arStyles['FONT_SIZE'] + 2).'px;';
		if($arStyles['FONT_COLOR']) $style .= 'color:'.htmlspecialcharsex($arStyles['FONT_COLOR']).';';
		if($arStyles['STYLE_BOLD']=='Y') $style .= 'font-weight:bold;';
		if($arStyles['STYLE_ITALIC']=='Y') $style .= 'font-style:italic;';
		if($arStyles['BACKGROUND_COLOR']) $style .= 'background-color:'.htmlspecialcharsex($arStyles['BACKGROUND_COLOR']).';';
		if($arStyles['INDENT']) $style .= 'padding-left:'.(intval($arStyles['INDENT'])*15).'px;';
		
		$textAlign = ToLower($arStyles['TEXT_ALIGN'] ? $arStyles['TEXT_ALIGN'] : $arParams['DISPLAY_TEXT_ALIGN']);
		if(!$textAlign) $textAlign = 'left';
		$style .= 'text-align:'.htmlspecialcharsex($textAlign).';';
		$verticalAlign = ToLower($arStyles['VERTICAL_ALIGN'] ? $arStyles['VERTICAL_ALIGN'] : $arParams['DISPLAY_VERTICAL_ALIGN']);
		if(!$verticalAlign) $verticalAlign = 'top';
		$style .= 'vertical-align:'.htmlspecialcharsex($verticalAlign).';';
		
		if(strlen($style) > 0) $style = 'style="'.$style.'"';
		return $style;
	}
	
	public static function PrepareTextRows(&$rows, $arParams=array(), $arStepParams=array())
	{
		if(is_array($rows))
		{
			foreach($rows as $listIndex=>$arRows)
			{
				if(is_array($rows[$listIndex]))
				{
					$rowsCount = (int)$arStepParams['rows2'][$listIndex];
					if(is_array($arParams['TEXT_ROWS_TOP']) && is_array($arParams['TEXT_ROWS_TOP'][$listIndex])) $rowsCount += count($arParams['TEXT_ROWS_TOP'][$listIndex]);
					if($arParams['HIDE_COLUMN_TITLES'][$listIndex]!='Y') $rowsCount += 1;
					if(is_array($arParams['TEXT_ROWS_TOP2']) && is_array($arParams['TEXT_ROWS_TOP2'][$listIndex])) $rowsCount += count($arParams['TEXT_ROWS_TOP2'][$listIndex]);
					foreach($rows[$listIndex] as $k=>$row)
					{
						if($arParams['FILE_EXTENSION']!='xlsx')
						{
							$row = str_replace('{MAX_ROW_NUM}', $rowsCount, $row);
						}
						$row = preg_replace_callback('/\{DATE_(\S*)\}/', array('CKDAExportUtils', 'GetDateFormat'), $row);
						$row = preg_replace_callback('/\{RATE_SITE\.(\S*)\}/', array('CKDAExportUtils', 'GetCurrenyRateSite'), $row);
						$row = preg_replace_callback('/\{RATE_CBR\.(\S*)\}/', array('CKDAExportUtils', 'GetCurrenyRateCbr'), $row);
						$rows[$listIndex][$k] = $row;
					}
				}
			}
		}
	}
	
	public static function PrepareTextRows2(&$rows, $lastDataRow)
	{
		static::$lastDataRow = $lastDataRow;
		if(is_array($rows))
		{
			foreach($rows as $k=>$row)
			{
				$rows[$k] = preg_replace_callback('/\{MAX_ROW_NUM([+-]\d+)?\}/', array('CKDAExportUtils', 'PrepareTextRows2Callback'), $row);
			}
		}
	}
	
	public static function PrepareTextRows2Callback($m)
	{
		$cnt = static::$lastDataRow; 
		if(isset($m[1])) $cnt += (int)$m[1]; 
		return max(0, $cnt);
	}
	
	public static function PrepareExportFileName($name)
	{
		return preg_replace_callback('/\{DATE_(\S*)\}/', array('CKDAExportUtils', 'GetDateFormat'), $name);
	}
	
	public static function GetDateFormat($m)
	{
		$format = str_replace('_', ' ', $m[1]);
		$time = time();
		if(preg_match_all('/([jdmyY])([\-+]\d+)/', $format, $m2))
		{
			foreach($m2[1] as $k=>$key)
			{
				if($key=='j' || $key=='d') $time = mktime((int)date('h', $time), (int)date('i', $time), (int)date('s', $time), (int)date('n', $time), (int)date('j', $time) + (int)$m2[2][$k], (int)date('Y', $time));
				elseif($key=='m') $time = mktime((int)date('h', $time), (int)date('i', $time), (int)date('s', $time), (int)date('n', $time) + (int)$m2[2][$k], (int)date('j', $time), (int)date('Y', $time));
				elseif($key=='y' || $key=='Y') $time = mktime((int)date('h', $time), (int)date('i', $time), (int)date('s', $time), (int)date('n', $time), (int)date('j', $time), (int)date('Y', $time) + (int)$m2[2][$k]);
				$format = str_replace($m2[0][$k], $key, $format);
			}
		}
		return ToLower(CIBlockFormatProperties::DateFormat($format, $time));
	}
	
	public static function GetCurrenyRate($m)
	{
		return \CCurrencyRates::ConvertCurrency(1, $m[1], 'RUB');
	}
	
	public static function PrepareJs()
	{
		$curFilename = end(explode('/', $_SERVER['SCRIPT_NAME']));
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/'.static::$moduleId.'/install/admin/'.$curFilename))
		{
			foreach(GetModuleEvents("main", "OnEndBufferContent", true) as $eventKey=>$arEvent)
			{
				if(!isset($arEvent['TO_MODULE_ID']) || $arEvent['TO_MODULE_ID']=='security')
				{
					RemoveEventHandler($arEvent['FROM_MODULE_ID'], $arEvent['MESSAGE_ID'], $eventKey);
				}
			}
			AddEventHandler("main", "OnEndBufferContent", Array("CKDAExportUtils", "PrepareJsDirect"));
		}
	}
	
	public static function PrepareJsDirect(&$content)
	{
		static::$jsCounter = 0;
		$content = preg_replace_callback('/<script[^>]+src="[^"]*\/js\/main\/jquery\/jquery\-[\d\.]+(\.min)+\.js[^"]*"[^>]*>\s*<\/script>/Uis', Array("CKDAExportUtils", "DeleteExcellJs"), $content);
		$content = preg_replace('/<script[^>]+src="https?:\/\/code\.jquery\.com\/jquery-[\d\.]+\.(min\.)?js"[^>]*>\s*<\/script>/Uis', '', $content);
	}
	
	public static function DeleteExcellJs($m)
	{
		if(static::$jsCounter++==0) return $m[0];
		else return '';
	}
	
	public static function GetCurrenyRateSite($m)
	{
		if(Loader::includeModule("currency"))
		{
			$dbRes = \CCurrencyRates::GetList(($by="date"), ($order="desc"), array("CURRENCY" => $m[1]));
			if($arr = $dbRes->Fetch())
			{
				return $arr['RATE'];
			}
		}
		return '';
	}
	
	public static function GetFileExtension($filename)
	{
		$filename = end(explode('/', $filename));
		$arParts = explode('.', $filename);
		if(count($arParts) > 1) 
		{
			$ext = trim(array_pop($arParts));
			if(strlen($ext)==0 || strlen($ext)>4 || preg_match('/^(\d+)$/', $ext)) return '';
			if(ToLower($ext)=='gz' && count($arParts) > 1)
			{
				$ext = array_pop($arParts).'.'.$ext;
			}
			return $ext;
		}
		else return '';
	}
	
	public static function GetColLetterByIndex($index)
	{
		if(empty(static::$colLetters))
		{
			$arLetters = range('A', 'Z');
			foreach(range('A', 'Z') as $v1)
			{
				foreach(range('A', 'Z') as $v2)
				{
					$arLetters[] = $v1.$v2;
				}
			}
			foreach(range('A', 'Z') as $v1)
			{
				foreach(range('A', 'Z') as $v2)
				{
					foreach(range('A', 'Z') as $v3)
					{
						$arLetters[] = $v1.$v2.$v3;
					}
				}
			}
			static::$colLetters = $arLetters;
		}
		return static::$colLetters[$index];
	}
	
	public static function GetCurrenyRateCbr($m)
	{
		$arRates = static::GetCurrencyRates();
		if(isset($arRates[$m[1]])) return $arRates[$m[1]];
		return '';
	}
	
	public static function GetCurrencyRates()
	{
		if(!isset(static::$currencyRates))
		{
			$arRates = \KdaIE\Utils::Unserialize(\Bitrix\Main\Config\Option::get(static::$moduleId, 'CURRENCY_RATES', ''));
			if(!is_array($arRates)) $arRates = array();
			if(!isset($arRates['TIME']) || $arRates['TIME'] < time() - 6*60*60)
			{
				$arRates2 = array();
				$client = new \Bitrix\Main\Web\HttpClient(array('socketTimeout'=>20));
				$res = $client->get('http://www.cbr.ru/scripts/XML_daily.asp');
				if($res)
				{
					$xml = simplexml_load_string($res);
					if($xml->Valute)
					{
						foreach($xml->Valute as $val)
						{
							$numVal = static::GetFloatVal((string)$val->Value);
							if($numVal > 0)$arRates2[(string)$val->CharCode] = (string)$numVal;
						}
					}
				}
				if(count($arRates2) > 1)
				{
					$arRates = $arRates2;
					$arRates['TIME'] = time();
					\Bitrix\Main\Config\Option::set(static::$moduleId, 'CURRENCY_RATES', serialize($arRates));
				}
			}
			if(Loader::includeModule('currency'))
			{
				if(!isset($arRates['USD'])) $arRates['USD'] = CCurrencyRates::ConvertCurrency(1, 'USD', 'RUB');
				if(!isset($arRates['EUR'])) $arRates['EUR'] = CCurrencyRates::ConvertCurrency(1, 'EUR', 'RUB');
			}
			static::$currencyRates = $arRates;
		}
		return static::$currencyRates;
	}
	
	public static function GetFloatVal($val, $precision=0)
	{
		$val = floatval(preg_replace('/[^\d\.\-]+/', '', str_replace(',', '.', $val)));
		if($precision > 0) $val = round($val, $precision);
		return $val;
	}
	
	public static function RemoveTmpFiles($maxTime = 5)
	{
		/*Check cron settings*/
		if(\Bitrix\Main\Config\Option::get(static::$moduleId, 'CRON_WO_MBSTRING', '')!='Y' && \Bitrix\KdaImportexcel\ClassManager::VersionGeqThen('main', '20.100.0'))
		{
			\Bitrix\Main\Config\Option::set(static::$moduleId, 'CRON_WO_MBSTRING', 'Y');
			$arLines = array();
			if(function_exists('exec')) @exec('crontab -l', $arLines);
			if(is_array($arLines) && count($arLines) > 0)
			{
				$isChange = false;
				foreach($arLines as $k=>$v)
				{
					if(strpos($v, static::$moduleId)!==false && preg_match('/\-d\s+mbstring.func_overload=\d+/', $v))
					{
						$v = preg_replace('/\-d\s+mbstring.func_overload=\d+/', '-d default_charset='.self::getSiteEncoding(), $v);
						$v = preg_replace('/\s+\-d\s+mbstring.internal_encoding=\S+/', '', $v);
						$arLines[$k] = $v;
						$isChange = true;
					}
				}
				if($isChange)
				{
					$cfg_data = implode("\n", $arLines);
					$cfg_data = preg_replace("#\n{3,}#im", "\n\n", $cfg_data);
					$cfg_data = trim($cfg_data, "\r\n ")."\n";
					if(true /*file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg")*/)
					{
						CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/");
						file_put_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", $cfg_data);
					}
					$arRetval = array();
					if(function_exists('exec'))
					{
						$command = "crontab ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg";
						@exec($command);
					}
				}
			}
		}
		/*/Check cron settings*/
		
		$timeBegin = time();
		$docRoot = $_SERVER["DOCUMENT_ROOT"];
		$tmpDir = $docRoot.'/upload/tmp/'.static::$moduleId.'/'.static::$moduleSubDir;
		$arOldDirs = array();
		$arActDirs = array();
		if(file_exists($tmpDir) && ($dh = opendir($tmpDir))) 
		{
			while(($file = readdir($dh)) !== false) 
			{
				if(in_array($file, array('.', '..'))) continue;
				if(is_dir($tmpDir.$file))
				{
					if(!in_array($file, $arActDirs) && (time() - filemtime($tmpDir.$file) > 24*60*60))
					{
						$arOldDirs[] = $file;
					}
				}
				elseif(substr($file, -4)=='.txt')
				{
					$arParams = \KdaIE\Utils::JsObjectToPhp(file_get_contents($tmpDir.$file));
					if(is_array($arParams) && isset($arParams['tmpdir']))
					{
						$actDir = preg_replace('/^.*\/([^\/]+)$/', '$1', trim($arParams['tmpdir'], '/'));
						$arActDirs[] = $actDir;
					}
				}
			}
			$arOldDirs = array_diff($arOldDirs, $arActDirs);
			foreach($arOldDirs as $subdir)
			{
				$oldDir = substr($tmpDir, strlen($docRoot)).$subdir;
				DeleteDirFilesEx($oldDir);
				if(($maxTime > 0) && (time() - $timeBegin >= $maxTime)) return;
			}
			closedir($dh);
		}
		
		$tmpDir = $docRoot.'/upload/tmp/';
		if(file_exists($tmpDir) && ($dh = opendir($tmpDir))) 
		{
			while(($file = readdir($dh)) !== false) 
			{
				if(!preg_match('/^[0-9a-z]{3}$/', $file) && !preg_match('/^[0-9a-z]{32}$/', $file)) continue;
				$subdir = $tmpDir.$file;
				if(is_dir($subdir))
				{
					$subdir .= '/';
					if(time() - filemtime($subdir) > 24*60*60)
					{
						if($dh2 = opendir($subdir))
						{
							$emptyDir = true;
							while(($file2 = readdir($dh2)) !== false)
							{
								if(in_array($file2, array('.', '..'))) continue;
								if(time() - filemtime($subdir) > 24*60*60)
								{
									if(is_dir($subdir.$file2))
									{
										$oldDir = substr($subdir.$file2, strlen($docRoot));
										DeleteDirFilesEx($oldDir);
									}
									else
									{
										unlink($subdir.$file2);
									}
								}
								else
								{
									$emptyDir = false;
								}
							}
							closedir($dh2);
							if($emptyDir)
							{
								//unlink($subdir);
								rmdir($subdir);
							}
						}
						
						if(($maxTime > 0) && (time() - $timeBegin >= $maxTime)) return;
					}
				}
			}
			closedir($dh);
		}
	}
	
	public static function CheckZipArchive()
	{
		$optionName = static::$zipArchiveOption;
		if(class_exists('\ZipArchive'))
		{
			$tmpDir = $_SERVER["DOCUMENT_ROOT"].'/upload/tmp/'.static::$moduleId.'/'.static::$moduleSubDir;
			CheckDirPath($tmpDir);
			$tempPathZip = $tmpDir.'test.zip';
			$tempPathTxt = $tmpDir.'test.txt';
			file_put_contents($tempPathTxt, 'test');
			\Bitrix\Main\Config\Option::set(static::$moduleId, $optionName, 'NONE');
			if(($zipObj = new \ZipArchive()) && $zipObj->open($tempPathZip, \ZipArchive::OVERWRITE|\ZipArchive::CREATE)===true)
			{
				$zipObj->addFile($tempPathTxt, 'test.txt');
				$zipObj->close();
				if(file_exists($tempPathZip))
				{
					\Bitrix\Main\Config\Option::set(static::$moduleId, $optionName, 'OVERWRITE_CREATE');
					unlink($tempPathZip);
				}
			}
			unlink($tempPathTxt);
		}
	}
	
	public static function CanUseZipArchive()
	{
		if(!class_exists('\ZipArchive')) return false;
		$optionName = static::$zipArchiveOption;
		if(\Bitrix\Main\Config\Option::get(static::$moduleId, $optionName)=='NONE') return false;
		return true;
	}
	
	public static function GetIniAbsVal($param)
	{
		$val = ToUpper(ini_get($param));
		if(substr($val, -1)=='K') $val = (float)$val*1024;
		elseif(substr($val, -1)=='M') $val = (float)$val*1024*1024;
		elseif(substr($val, -1)=='G') $val = (float)$val*1024*1024*1024;
		else $val = (float)$val;
		return $val;
	}
	
	public static function getUtfModifier()
	{
		if(self::getSiteEncoding()=='utf-8') return 'u';
		else return '';
	}
	
	public static function getSiteEncoding()
	{
		if (defined('BX_UTF'))
			$logicalEncoding = "utf-8";
		elseif (defined("SITE_CHARSET") && (strlen(SITE_CHARSET) > 0))
			$logicalEncoding = SITE_CHARSET;
		elseif (defined("LANG_CHARSET") && (strlen(LANG_CHARSET) > 0))
			$logicalEncoding = LANG_CHARSET;
		elseif (defined("BX_DEFAULT_CHARSET"))
			$logicalEncoding = BX_DEFAULT_CHARSET;
		else
			$logicalEncoding = "windows-1251";

		return strtolower($logicalEncoding);
	}
	
	public static function getfileSystemEncoding()
	{
		$fileSystemEncoding = strtolower(defined("BX_FILE_SYSTEM_ENCODING") ? BX_FILE_SYSTEM_ENCODING : "");

		if (empty($fileSystemEncoding))
		{
			if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN")
				$fileSystemEncoding =  "windows-1251";
			else
				$fileSystemEncoding = "utf-8";
		}

		return $fileSystemEncoding;
	}
	
	public static function MakeFileArray($path, $maxTime = 0)
	{
		$arFile = array();
		if(is_array($path))
		{
			$arFile = $path;
			$temp_path = CFile::GetTempName('', \Bitrix\Main\IO\Path::convertLogicalToPhysical($arFile["name"]));
			CheckDirPath($temp_path);
			if(!copy($arFile["tmp_name"], $temp_path)
				&& !move_uploaded_file($arFile["tmp_name"], $temp_path))
			{
				return false;
			}
			$arFile = CFile::MakeFileArray($temp_path);
			if(isset($path['type'])) $arFile['type'] = $path['type'];
		}
		return $arFile;
	}
	
	public static function BinStrlen($val)
	{
		return mb_strlen($val, 'latin1');
	}
	
    public static function BinStrpos($haystack, $needle, $offset = 0)
    {
        return mb_strpos($haystack, $needle, $offset, 'latin1');
    }
	
    public static function BinSubstr($buf, $start, $length=null)
    {
		return mb_substr($buf, $start, ($length===null ? 2000000000 : $length), 'latin1');
    }
	
	public static function ExportCsv($arResult)
	{
		require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
		$objPHPExcel = new \KDAPHPExcel();
		$arCols = range('A', 'Z');
		
		$row = 1;
		$worksheet = $objPHPExcel->getActiveSheet();
		foreach($arResult as $k=>$arFields)
		{
			$col = 0;
			foreach($arFields as $k=>$field)
			{
				$worksheet->setCellValueExplicit($arCols[$col++].$row, self::GetCsvCellValue($field, 'UTF-8'));
			}
			$row++;
		}
		$objWriter = KDAPHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->setDelimiter(';');
		$objWriter->setEnclosure('"');
		$objWriter->setUseBOM(true);
		
		$tempPath = CFile::GetTempName('', 'export.csv');
		$dir = \Bitrix\Main\IO\Path::getDirectory($tempPath);
		\Bitrix\Main\IO\Directory::createDirectory($dir);
		$objWriter->save($tempPath);
		
		$GLOBALS['APPLICATION']->RestartBuffer();
		ob_end_clean();
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=export.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($tempPath);
		die();
	}
	
	public static function ImportCsv($file)
	{
		require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
		$maxLine = 10000;
		$arLines = array();
		$objReader = \KDAPHPExcel_IOFactory::createReaderForFile($file);
		$efile = $objReader->load($file);
		foreach($efile->getWorksheetIterator() as $worksheet) 
		{
			$columns_count = max(KDAPHPExcel_Cell::columnIndexFromString($worksheet->getHighestDataColumn()), $maxDrawCol);
			$columns_count = min($columns_count, 5000);
			$rows_count = $worksheet->getHighestDataRow();

			for ($row = 0; ($row < $rows_count && count($arLines) < $maxLine); $row++) 
			{
				$arLine = array();
				for($column = 0; $column < $columns_count; $column++) 
				{
					$val = $worksheet->getCellByColumnAndRow($column, $row+1);					
					$valText = $val->getCalculatedValue();
					$arLine[] = $valText;
				}

				if(count(array_diff($arLine, array(''))) > 0)
				{
					$arLines[] = $arLine;
				}
			}
		}
		return $arLines;
	}
	
	public static function GetCsvCellValue($val, $encoding='CP1251')
	{
		if($encoding=='CP1251')
		{
			if(defined('BX_UTF') && BX_UTF)
			{
				$val = $GLOBALS['APPLICATION']->ConvertCharset($val, 'UTF-8', 'CP1251');
			}
		}
		else
		{
			if(!defined('BX_UTF') || !BX_UTF)
			{
				$val = $GLOBALS['APPLICATION']->ConvertCharset($val, 'CP1251', 'UTF-8');
			}
		}
		return $val;
	}
	
	public static function WordWithNum($num, $word)
	{
		list($word1, $word2, $word3) = array_map('trim', explode(',', $word));
		if($num%10==0 || $num%10>4 || ($num%100>10 && $num%100<20)) return $word3;
		elseif($num%10==1) return $word1;
		else return $word2;
	}
	
	public static function SetConvType0($c)
	{
		if(strlen(trim($c["CELL"]))==0) $c["CELL"] = '0';
		$c["CONV_TYPE"]=0; return $c;
	}
	
	public static function SetConvType1($c)
	{
		if(strlen(trim($c["CELL"]))==0) $c["CELL"] = '0';
		$c["CONV_TYPE"]=1; return $c;
	}
}
?>