<?
if(!defined('NO_AGENT_CHECK')) define('NO_AGENT_CHECK', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
$moduleId = 'esol.importexportexcel';
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule($moduleId);
$bCurrency = CModule::IncludeModule("currency");
IncludeModuleLangFile(__FILE__);

$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if($MODULE_RIGHT <= "R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$IBLOCK_ID = $_REQUEST['IBLOCK_ID'];
$fieldName = htmlspecialcharsex($_GET['field_name']);

$oProfile = new CKDAExportProfile();
$arProfile = $oProfile->GetByID($_REQUEST['PROFILE_ID']);
$SETTINGS_DEFAULT = $arProfile['SETTINGS_DEFAULT'];

function GetFieldEextraVal($arSettings, $name)
{
	$fnBase = str_replace('[FIELDS_LIST]', '', htmlspecialcharsbx($_GET['field_name']));
	$fName = 'EXTRA'.$fnBase.'['.$name.']';
	if(preg_match_all('/\[([^\]]*)\]/Us', $fnBase, $m))
	{
		foreach($m[1] as $key) $arSettings = (isset($arSettings[$key]) ? $arSettings[$key] : array());
	}
	$val = (isset($arSettings[$name]) ? $arSettings[$name] : '');
	return array($fName, $val);
}

$fl = new CKDAEEFieldList($SETTINGS_DEFAULT);
$arFieldGroups = $fl->GetFields($IBLOCK_ID);
$arFields = array();
if(is_array($arFieldGroups))
{
	foreach($arFieldGroups as $arGroup)
	{
		if(is_array($arGroup['items']))
		{
			$arFields = array_merge($arFields, $arGroup['items']);
		}
	}
}

$isOffer = false;
$field = $_REQUEST['field'];
if(strpos($field, 'OFFER_')===0)
{
	$OFFER_IBLOCK_ID = CKDAExportUtils::GetOfferIblock($IBLOCK_ID);
	$field = substr($field, 6);
	$isOffer = true;
}

$addField = '';
if(strpos($field, '|') !== false)
{
	list($field, $addField) = explode('|', $field);
}

/*$obJSPopup = new CJSPopup();
$obJSPopup->ShowTitlebar(GetMessage("KDA_EE_SETTING_UPLOAD_FIELD").($arFields[$field] ? ' "'.$arFields[$field].'"' : ''));*/

$oProfile = new CKDAExportProfile();
$oProfile->ApplyExtra($PEXTRASETTINGS, $_REQUEST['PROFILE_ID']);
if(array_key_exists('POSTEXTRA', $_POST))
{
	$arFieldParams = $_POST['POSTEXTRA'];
	if(!defined('BX_UTF') || !BX_UTF)
	{
		$arFieldParams = $APPLICATION->ConvertCharset($arFieldParams, 'UTF-8', 'CP1251');
	}
	if($arFieldParams) $arFieldParams = \KdaIE\Utils::JsObjectToPhp($arFieldParams);
	if(!$arFieldParams) $arFieldParams = array();
	$fName = 'EXTRA'.str_replace('[FIELDS_LIST]', '', $fieldName);
	//$fNameEval = strtr($fName, array("["=>"['", "]"=>"']"));
	//eval('$arFieldsParamsInArray = &$P'.$fNameEval.';');
	$arFieldsParamsInArray = &$PEXTRASETTINGS;
	if(preg_match_all('/\[([^\]]*)\]/Us', $fName, $m))
	{
		foreach($m[1] as $key)
		{
			if(!is_array($arFieldsParamsInArray) || !array_key_exists($key, $arFieldsParamsInArray)) $arFieldsParamsInArray[$key] = array();
			$arFieldsParamsInArray = &$arFieldsParamsInArray[$key];
		}
	}
	$arFieldsParamsInArray = $arFieldParams;
}

if($_POST['action']) define('PUBLIC_AJAX_MODE', 'Y');

if($_POST['action']=='export_conv_csv')
{
	$arExtra = array();
	\CKDAExportExtrasettings::HandleParams($arExtra, array(array(array('CONVERSION'=>$_POST['CONVERSION'], 'EXTRA_CONVERSION'=>$_POST['EXTRA_CONVERSION']))), false);
	while(is_array($arExtra) && isset($arExtra[0])) $arExtra = $arExtra[0];
	$arConv = $arExtraConv = array();
	if(is_array($arExtra))
	{
		if(isset($arExtra['CONVERSION']) && is_array($arExtra['CONVERSION'])) $arConv = $arExtra['CONVERSION']; 
		if(isset($arExtra['EXTRA_CONVERSION']) && is_array($arExtra['EXTRA_CONVERSION'])) $arExtraConv = $arExtra['EXTRA_CONVERSION']; 
	}
	$arConv = array_map(array('CKDAExportUtils', 'SetConvType0'), $arConv);
	$arExtraConv = array_map(array('CKDAExportUtils', 'SetConvType1'), $arExtraConv);
	\CKDAExportUtils::ExportCsv(array_merge($arConv, $arExtraConv));
	die();
}
elseif($_POST['action']=='import_conv_csv')
{
	$arImportConv = array();
	if(isset($_FILES["import_file"]) && $_FILES["import_file"]["tmp_name"] && is_uploaded_file($_FILES["import_file"]["tmp_name"]))
	{
		$arFile = \CKDAExportUtils::MakeFileArray($_FILES["import_file"]);
		$arImportConv = \CKDAExportUtils::ImportCsv($arFile['tmp_name']);
	}
	$arConv = $arExtraConv = array();
	foreach($arImportConv as $conv)
	{
		$ctype = array_pop($conv);
		$conv = array_combine(array('CELL', 'WHEN', 'FROM', 'THEN', 'TO'), $conv);
		if($conv['CELL']=='0') $conv['CELL'] = '';
		if($ctype==0) $arConv[] = $conv;
		elseif($ctype==1) $arExtraConv[] = $conv;
	}
	
	$key1 = $key2 = 0;
	if(preg_match('/\[FIELDS_LIST\]\[([^\]]*)\]\[([^\]]*)\]/', $fieldName, $m))
	{
		$key1 = $m[1];
		$key2 = $m[2];
	}
	$PEXTRASETTINGS[$key1][$key2] = array(
		'CONVERSION' => $arConv,
		'EXTRA_CONVERSION' => $arExtraConv
	);
	/*$APPLICATION->RestartBuffer();
	ob_end_clean();
	echo \KdaIE\Utils::PhpToJSObject(array('CONV'=>$arConv, 'EXTRA_CONV'=>$arExtraConv));
	die();*/
}
elseif($_POST['action']=='save' && is_array($_POST['EXTRASETTINGS']))
{
	define('PUBLIC_AJAX_MODE', 'Y');
	CKDAExportExtrasettings::HandleParams($PEXTRASETTINGS, $_POST['EXTRASETTINGS']);
	preg_match_all('/\[([_\d]+)\]/', $_GET['field_name'], $keys);
	$oid = 'field_settings_'.$keys[1][0].'_'.$keys[1][1];

	$APPLICATION->RestartBuffer();
	ob_end_clean();
	
	if($_GET['return_data'])
	{
		$returnJson = (empty($PEXTRASETTINGS[$keys[1][0]][$keys[1][1]]) ? '""' : \KdaIE\Utils::PhpToJSObject($PEXTRASETTINGS[$keys[1][0]][$keys[1][1]]));
		echo '<script>EList.SetExtraParams("'.$oid.'", '.$returnJson.')</script>';
	}
	else
	{
		$oProfile->UpdateExtra($_REQUEST['PROFILE_ID'], $PEXTRASETTINGS);
		$isEmpty = (empty($PEXTRASETTINGS[$keys[1][0]][$keys[1][1]]));
		echo '<script>ESettings.OnSettingsSave("'.$oid.'", '.($isEmpty ? 'false' : 'true').');</script>';
	}
	die();
}

$ee = new CKDAExportExcel();
$bPicture = $ee->IsPictureField($field);
$bMultipleProp = $ee->IsMultipleField($field, $IBLOCK_ID);

$bPrice = false;
if((strncmp($field, "ICAT_PRICE", 10) == 0 && (substr($field, -6)=='_PRICE') || substr($field, -15)=='_PRICE_DISCOUNT') || $field=="ICAT_PURCHASING_PRICE")
{
	$bPrice = true;
	if($bCurrency)
	{
		$arCurrency = array();
		$lcur = CCurrency::GetList(($by="sort"), ($order1="asc"), LANGUAGE_ID);
		while($arr = $lcur->Fetch())
		{
			$arCurrency[] = array(
				'CURRENCY' => $arr['CURRENCY'],
				'FULL_NAME' => $arr['FULL_NAME']
			);
		}
	}
}

$bIblockElementDefField = '';
$bIblockElement = false;
$bIblockSection = false;
$bPropTypeList = false;
$bDirectory = false;
if(strncmp($field, "IP_PROP", 7) == 0 && is_numeric(substr($field, 7)))
{
	$propId = intval(substr($field, 7));
	$dbRes = CIBlockProperty::GetList(array(), array('ID'=>$propId));
	if($arProp = $dbRes->Fetch())
	{
		if($arProp['PROPERTY_TYPE']=='L')
		{
			$bPropTypeList = true;
		}
		elseif($arProp['PROPERTY_TYPE']=='E')
		{
			$bIblockElement = true;
			$iblockElementIblock = ($arProp['LINK_IBLOCK_ID'] ? $arProp['LINK_IBLOCK_ID'] : $IBLOCK_ID);
		}
		elseif($arProp['PROPERTY_TYPE']=='G')
		{
			$bIblockSection = true;
			$iblockSectionIblock = ($arProp['LINK_IBLOCK_ID'] ? $arProp['LINK_IBLOCK_ID'] : $IBLOCK_ID);
		}
		elseif($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='directory' && $arProp['USER_TYPE_SETTINGS']['TABLE_NAME'] && \CModule::IncludeModule('highloadblock'))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('TABLE_NAME'=>$arProp['USER_TYPE_SETTINGS']['TABLE_NAME'])))->fetch();
			$dbRes = \CUserTypeEntity::GetList(array('SORT'=>'ASC', 'ID'=>'ASC'), array('ENTITY_ID'=>'HLBLOCK_'.$hlblock['ID'], 'LANG'=>LANGUAGE_ID));
			$arHLFields = array();
			while($arHLField = $dbRes->Fetch())
			{
				$arHLFields[$arHLField['FIELD_NAME']] = ($arHLField['EDIT_FORM_LABEL'] ? $arHLField['EDIT_FORM_LABEL'] : $arHLField['FIELD_NAME']);
			}
			$bDirectory = true;
		}
	}
}
elseif(strncmp($field, "ISECT_UF_", 9)==0)
{
	$dbRes = \CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$IBLOCK_ID.'_SECTION', 'FIELD_NAME' => mb_substr($field, 6)));
	$arProps = array();
	while($arProp = $dbRes->Fetch())
	{
		if($arProp['USER_TYPE_ID']=='iblock_element' 
			|| ($arProp['USER_TYPE_ID']=='grain_link' && isset($arProp['SETTINGS']['DATA_SOURCE']) && $arProp['SETTINGS']['DATA_SOURCE']=='iblock_element'))
		{
			$bIblockElement = true;
			$iblockElementIblock = (isset($arProp['SETTINGS']['IBLOCK_ID']) ? $arProp['SETTINGS']['IBLOCK_ID'] : $IBLOCK_ID);
		}
	}
}

if($field=='ICAT_SET_ITEM_ID' || $field=='ICAT_SET2_ITEM_ID')
{
	$bIblockElement = true;
	$bIblockElementDefField = 'IE_ID';
	$iblockElementIblock = $IBLOCK_ID;
}

$bUser = (bool)($field=='IE_CREATED_BY' || $field=='IE_MODIFIED_BY');

$arFields = $fl->GetSettingsFields($IBLOCK_ID);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<form action="" method="post" enctype="multipart/form-data" name="field_settings">
	<input type="hidden" name="action" value="save">
	<table width="100%" class="kda-ee-field-settings">
		<col width="50%">
		<col width="50%">
		
		<?if($field=="IP_LIST_PROPS"){?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_PROPS_LIST");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PROPLIST_PROPS_LIST');
					if(!is_array($val)) $val = array();
					
					$arPropFieldParams = array('ONLYPROPS' => 'Y');
					if($_POST['onlysectionprops'] || $_POST['onlysectionpropswoiblock'])
					{
						if($_POST['onlysectionprops']) $arPropFieldParams['SHOW_ONLY_SECTION_PROPERTY'] = true; 
						if($_POST['onlysectionpropswoiblock']) $arPropFieldParams['SHOW_ONLY_SECTION_PROPERTY_WO_IBLOCK'] = true; 
						$arPropFieldParams['SECTIONS'] = $_POST['sections'];
						$arPropFieldParams['ISSUBSECTIONS'] = (bool)($_POST['issubsections']);
					}
					?>
					<select name="<?=$fName?>[]" multiple>
						<?
						$arProps = $fl->GetIblockProperties(($isOffer ? $OFFER_IBLOCK_ID : $IBLOCK_ID), $arPropFieldParams);
						if(!is_array($arProps)) $arProps = array();
						foreach($arProps as $arProp)
						{
							?><option value="<?=$arProp['ID']?>"<?if(in_array($arProp['ID'], $val)){echo ' selected';}?>><?=$arProp['NAME']?></option><?
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_PROPS_SEP_VALS");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PROPLIST_PROPS_SEP_VALS');
					?>
					<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" size="3">
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_PROPS_SEP_NAMEVAL");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PROPLIST_PROPS_SEP_NAMEVAL');
					?>
					<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" size="3">
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_PROPS_SHOW_EMPTY");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PROPLIST_PROPS_SHOW_EMPTY');
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y"<?if($val=='Y'){echo ' checked';}?>>
				</td>
			</tr>
		<?}?>
		
		<?if(preg_match('/^ICAT_PRICE\d+_PRICE_DISCOUNT$/', $field) || preg_match('/^ICAT_DISCOUNT_/', $field)){?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_DISCOUNT_USER_GROUP");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'USER_GROUP');
					
					$arGroups = array();
					$dbRes = \CGroup::GetList(($by="ID"), ($order="ASC"), array());
					while($arGroup = $dbRes->Fetch())
					{
						$arGroups[$arGroup['ID']] = $arGroup['NAME'];
					}
					?>
					<select name="<?echo $fName;?>[]" style="max-width: 450px;" multiple>
						<?foreach($arGroups as $groupId=>$groupName){?>
							<option value="<?echo $groupId;?>"<?if(is_array($val) && in_array($groupId, $val)){echo ' selected';}?>><?echo $groupName;?></option>
						<?}?>
					</select>
				</td>
			</tr>
			
			<?
			$arPriceGroups = array();
			if(class_exists('\Bitrix\Catalog\GroupTable'))
			{
				$dbRes = \Bitrix\Catalog\GroupTable::GetList(array('select'=>array('ID', 'BASE', 'LANG_NAME'=>'CURRENT_LANG.NAME')));
				while($arPriceGroup = $dbRes->Fetch())
				{
					if($arPriceGroup['BASE']=='Y') array_unshift($arPriceGroups, $arPriceGroup);
					else array_push($arPriceGroups, $arPriceGroup);
				}
			}
			if(preg_match('/^ICAT_DISCOUNT_/', $field) && count($arPriceGroups) > 1)
			{
			?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_DISCOUNT_GROUP_PRICE");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'GROUP_PRICE_ID');
					?>
					<select name="<?echo $fName;?>" style="max-width: 450px;">
						<?foreach($arPriceGroups as $arPriceGroup){?>
							<option value="<?echo $arPriceGroup['ID'];?>"<?if($val==$arPriceGroup['ID']){echo ' selected';}?>>[<?echo $arPriceGroup['ID'];?>] <?echo $arPriceGroup['LANG_NAME'];?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?
			}
			
			$arSites = array();
			if(class_exists('\Bitrix\Iblock\IblockSiteTable'))
			{
				$dbRes = \Bitrix\Iblock\IblockSiteTable::GetList(array('filter'=>array('IBLOCK_ID'=>$IBLOCK_ID), 'select'=>array('SITE_ID', 'SITE_NAME'=>'SITE.NAME')));
				while($arSite = $dbRes->Fetch())
				{
					$arSites[] = $arSite;
				}
			}
			if(count($arSites) > 1)
			{
			?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_DISCOUNT_SITE");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'SITE_ID');
					$dbRes = CIBlock::GetList(array(), array('ID'=>$IBLOCK_ID));
					$arIblock = $dbRes->Fetch();
					?>
					<select name="<?echo $fName;?>" style="max-width: 450px;">
						<?foreach($arSites as $arSite){?>
							<option value="<?echo $arSite['SITE_ID'];?>"<?if((!$val && $arSite['SITE_ID']==$arIblock['LID']) || $val==$arSite['SITE_ID']){echo ' selected';}?>>[<?echo $arSite['SITE_ID'];?>] <?echo $arSite['SITE_NAME'];?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?
			}
			?>
		<?}?>
		
		
		<?if(in_array($field, array('IE_SECTION_PATH', 'ISECT_PATH_NAMES'))){?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_SECTION_PATH_SEPARATOR");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'SECTION_PATH_SEPARATOR');
					?>
					<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" placeholder="<?echo GetMessage("KDA_EE_SETTINGS_SECTION_PATH_SEPARATOR_PLACEHOLDER");?>">
				</td>
			</tr>
		<?}?>
		
		<?if($bIblockElement){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_REL_ELEMENT_FIELD");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'REL_ELEMENT_FIELD');
					$strOptions = $fl->GetRelatedFields($iblockElementIblock, $val, $bIblockElementDefField);
					?>
					<select name="<?echo $fName;?>" class="chosen" style="max-width: 450px;"><?echo $strOptions;?></select>
				</td>
			</tr>
		<?}?>
		
		<?if($bIblockSection){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_REL_SECTION_FIELD");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'REL_SECTION_FIELD');
					$strOptions = $fl->GetRelatedSectionFields($iblockSectionIblock, $val);
					?>
					<select name="<?echo $fName;?>" class="chosen" style="max-width: 450px;"><?echo $strOptions;?></select>
				</td>
			</tr>
		<?}?>
		
		<?if($bPropTypeList){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_FIELD");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'REL_PROPLIST_FIELD');
					?>
					<select name="<?echo $fName;?>">
						<option value="VALUE" <?if($val=='VALUE'){echo 'selected';}?>><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_FIELD_VALUE");?></option>
						<option value="XML_ID" <?if($val=='XML_ID'){echo 'selected';}?>><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_FIELD_XML_ID");?></option>
						<option value="SORT" <?if($val=='SORT'){echo 'selected';}?>><?echo GetMessage("KDA_EE_SETTINGS_PROPLIST_FIELD_SORT");?></option>
					</select>
				</td>
			</tr>
		<?}?>
		
		<?if($bDirectory){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_REL_DIRECTORY_FIELD");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'REL_DIRECTORY_FIELD');
					?>
					<select name="<?echo $fName;?>" class="chosen" style="max-width: 450px;">
						<?
						if(is_array($arHLFields))
						{
							foreach($arHLFields as $k=>$v)
							{
								echo '<option value="'.htmlspecialcharsbx($k).'"'.($val==$k || (strlen($val)==0 && $k=='UF_NAME') ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
							}
						}
						?>
					</select>
				</td>
			</tr>
		<?}?>
		
		<?if($bUser){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_REL_USER_FIELD");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'REL_USER_FIELD');
					?>
					<select name="<?echo $fName;?>" class="chosen" style="max-width: 450px;">
						<option value="ID"<?if($val=='ID'){echo ' selected';}?>><?echo GetMessage("KDA_EE_FIELD_USER_ID");?></option>
						<option value="XML_ID"<?if($val=='XML_ID'){echo ' selected';}?>><?echo GetMessage("KDA_EE_FIELD_XML_ID");?></option>
						<option value="LOGIN"<?if($val=='LOGIN'){echo ' selected';}?>><?echo GetMessage("KDA_EE_FIELD_LOGIN");?></option>
						<option value="EMAIL"<?if($val=='EMAIL'){echo ' selected';}?>><?echo GetMessage("KDA_EE_FIELD_EMAIL");?></option>
						<option value="LAST_NAME NAME"<?if($val=='LAST_NAME NAME'){echo ' selected';}?>><?echo GetMessage("KDA_EE_FIELD_LAST_NAME_NAME");?></option>
						<option value="WORK_COMPANY"<?if($val=='WORK_COMPANY'){echo ' selected';}?>><?echo GetMessage("KDA_EE_FIELD_WORK_COMPANY");?></option>
					</select>
				</td>
			</tr>
		<?}?>
		
		<?if($field=="IE_QR_CODE_IMAGE"){?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_QRCODE_CODE");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'QRCODE_SIZE');
					$arSizes = array();
					$sizeStep = 41;
					for($i=1; $i<25; $i++)
					{
						$arSizes[$i] = ($i*$sizeStep).'x'.($i*$sizeStep).'px';
					}
					?>
					<select name="<?=$fName?>">
						<?
						foreach($arSizes as $k=>$v)
						{
							?><option value="<?=$k?>"<?if(($val && $k==$val) || (!$val && $k==3)){echo ' selected';}?>><?=$v?></option><?
						}
						?>
					</select>
				</td>
			</tr>
		<?}elseif($field=="ICAT_BARCODE_IMAGE"){?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_BARCODE_FIELD");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'BARCODE_FIELD');
					?>
					<select name="<?=$fName?>">
						<option value="ICAT_BARCODE"<?if($val=='ICAT_BARCODE'){echo ' selected';}?>><?=GetMessage("KDA_EE_SETTINGS_BARCODE_FIELD_BARCODE")?></option>
						<?
						$dbRes = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => ($isOffer ? $OFFER_IBLOCK_ID : $IBLOCK_ID), "CHECK_PERMISSIONS" => "N"));
						while($arProp = $dbRes->Fetch())
						{
							if(!in_array($arProp['PROPERTY_TYPE'], array('S', "N")) || strlen($arProp['USER_TYPE']) > 0) continue;
							?><option value="IP_PROP<?=$arProp['ID']?>"<?if($val=='IP_PROP'.$arProp['ID']){echo ' selected';}?>><?=GetMessage("KDA_EE_SETTINGS_BARCODE_FIELD_PROP")?> &laquo;<?=$arProp['NAME']?>&raquo;</option><?
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_BARCODE_HEIGHT");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'BARCODE_HEIGHT');
					?>
					<input type="text" name="<?=$fName?>"  value="<?=htmlspecialcharsbx($val)?>" placeholder="80">
				</td>
			</tr>
		<?}elseif($bPicture){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_INSERT_PICTURE");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'INSERT_PICTURE');
					$insertPic = $val;
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?> onchange="ESettings.ToggleSubfields(this)">
					&nbsp; <?echo GetMessage("KDA_EE_SETTINGS_INSERT_PICTURE_NOTE");?>
				</td>
			</tr>
			<tr class="subfield" <?if($insertPic!='Y'){echo 'style="display: none;"';}?>>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PICTURE_WIDTH");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PICTURE_WIDTH');
					?>
					<input type="text" name="<?=$fName?>"  value="<?=htmlspecialcharsbx($val)?>" placeholder="100">
				</td>
			</tr>
			<tr class="subfield" <?if($insertPic!='Y'){echo 'style="display: none;"';}?>>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PICTURE_HEIGHT");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PICTURE_HEIGHT');
					?>
					<input type="text" name="<?=$fName?>"  value="<?=htmlspecialcharsbx($val)?>" placeholder="100">
				</td>
			</tr>
		<?}?>
		
		<?if($bMultipleProp){?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_CHANGE_MULTIPLE_SEPARATOR");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'CHANGE_MULTIPLE_SEPARATOR');
					list($fName2, $val2) = GetFieldEextraVal($PEXTRASETTINGS, 'MULTIPLE_SEPARATOR');
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?> onchange="$('#multiple_separator').css('display', (this.checked ? '' : 'none'));"><br>
					<input type="text" id="multiple_separator" name="<?=$fName2?>" value="<?=htmlspecialcharsbx($val2)?>" placeholder="<?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_SEPARATOR_PLACEHOLDER");?>" <?=($val!='Y' ? 'style="display: none"' : '')?>>
				</td>
			</tr>
			
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_SEPARATE_BY_ROWS");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'MULTIPLE_SEPARATE_BY_ROWS');
					list($fName2, $val2) = GetFieldEextraVal($PEXTRASETTINGS, 'MULTIPLE_SEPARATE_BY_ROWS_MODE');
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?> onchange="if(this.checked){$('#multiple_separate_mode').show();}else{$('#multiple_separate_mode').hide();}">
					&nbsp;
					<select name="<?=$fName2?>" id="multiple_separate_mode" style="margin-bottom: -10px; <?if($val!='Y'){echo 'display: none;"';}?>">
						<option value="MULTICELL"<?if($val2=='MULTICELL'){echo ' selected';}?>><?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_SEPARATE_BY_ROWS_MODE_MULTICELL");?></option>
						<option value="MULTIROW"<?if($val2=='MULTIROW'){echo ' selected';}?>><?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_SEPARATE_BY_ROWS_MODE_MULTIROW");?></option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td class="adm-detail-content-cell-l" valign="top"><?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_FROM_VALUE");?>:<br><small><?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_FROM_VALUE_COMMENT");?></small></td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName1, $val1) = GetFieldEextraVal($PEXTRASETTINGS, 'MULTIPLE_FROM_VALUE');
					list($fName2, $val2) = GetFieldEextraVal($PEXTRASETTINGS, 'MULTIPLE_TO_VALUE');
					?>
					<input type="text" size="5" name="<?=$fName1?>" value="<?echo htmlspecialcharsbx($val1);?>" placeholder="1">
					<?echo GetMessage("KDA_EE_SETTINGS_MULTIPLE_TO_VALUE");?>
					<input type="text" size="5" name="<?=$fName2?>" value="<?echo htmlspecialcharsbx($val2);?>">
				</td>
			</tr>
		<?}?>
		
		<tr class="heading">
			<td colspan="2">
					<div class="kda-ee-settings-header-links">
						<div class="kda-ee-settings-header-links-inner">
							<a href="javascript:void(0)" onclick="ESettings.ExportConvCSV(this)"><?echo GetMessage("KDA_EE_SETTINGS_EXPORT_CSV"); ?></a> /
							<a href="javascript:void(0)" onclick="ESettings.ImportConvCSV(this)"><?echo GetMessage("KDA_EE_SETTINGS_IMPORT_CSV"); ?></a>
						</div>
					</div>
				<?echo GetMessage("KDA_EE_SETTINGS_CONVERSION_TITLE");?>
			</td>
		</tr>
		<tr>
			<td class="kda-ee-settings-margin-container kda-ee-conv-share-wrap" colspan="2" id="kda-ee-conv-wrap0">
				<?
				list($fName, $arVals) = GetFieldEextraVal($PEXTRASETTINGS, 'CONVERSION');
				$showCondition = true;
				if(!is_array($arVals) || count($arVals)==0)
				{
					$showCondition = false;
					$arVals = array(
						array(
							'WHEN' => '',
							'FROM' => '',
							'THEN' => '',
							'TO' => ''
						)
					);
				}
				
				$arCellGroupOptions = array(
					array(
						'NAME' => '',
						'ITEMS' => array(
							0 => GetMessage("KDA_EE_SETTINGS_CONVERSION_CELL_CURRENT"),
						)
					)
				);
				foreach($arFields as $k=>$arGroup)
				{
					if(is_array($arGroup['FIELDS']))
					{
						$key = count($arCellGroupOptions);
						$arCellGroupOptions[$key] = array(
							'NAME' => $arGroup['TITLE'],
							'ITEMS' => array()
						);
						foreach($arGroup['FIELDS'] as $gkey=>$gfield)
						{
							$arCellGroupOptions[$key]['ITEMS'][$gkey] = $gfield;
						}
					}
				}
				$arCellGroupOptions[] = array(
					'NAME' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CELL_GROUP_OTHER"),
					'ITEMS' => array(
						'ELSE' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CELL_ELSE"),
					)
				);

				$arGroupOptions = array(
					'CELL' => $arCellGroupOptions,
					'WHEN' => array(
						array(
							'NAME' => '',
							'ITEMS' => array(
								'EQ' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_EQ"),
								'NEQ' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NEQ"),
								'GT' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_GT"),
								'LT' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_LT"),
								'GEQ' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_GEQ"),
								'LEQ' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_LEQ"),
								'BETWEEN' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_BETWEEN"),
								'CONTAIN' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_CONTAIN"),
								'NOT_CONTAIN' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NOT_CONTAIN"),
								'EMPTY' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_EMPTY"),
								'NOT_EMPTY' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NOT_EMPTY"),
								'REGEXP' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_REGEXP"),
								'NOT_REGEXP' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NOT_REGEXP"),
								'ANY' => GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_ANY")
							)
						)
					),
					'THEN' => array(
						array(
							'NAME' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_GROUP_STRING"),
							'ITEMS' => array(
								'REPLACE_TO' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_REPLACE_TO"),
								'REMOVE_SUBSTRING' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_REMOVE_SUBSTRING"),
								'REPLACE_SUBSTRING_TO' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_REPLACE_SUBSTRING_TO"),
								'ADD_TO_BEGIN' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_ADD_TO_BEGIN"),
								'ADD_TO_END' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_ADD_TO_END"),
								'TRANSLIT' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_TRANSLIT"),
								'STRIP_TAGS' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_STRIP_TAGS"),
								'CLEAR_TAGS' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_CLEAR_TAGS")
							)
						),
						array(
							'NAME' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_GROUP_MATH"),
							'ITEMS' => array(
								'MATH_ROUND' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_ROUND"),
								'MATH_MULTIPLY' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_MULTIPLY"),
								'MATH_DIVIDE' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_DIVIDE"),
								'MATH_ADD' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_ADD"),
								'MATH_SUBTRACT' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_SUBTRACT")
							)
						),
						array(
							'NAME' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_GROUP_OTHER"),
							'ITEMS' => array(
								'SKIP_LINE' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SKIP_LINE"),
								'SET_BG_COLOR' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SET_BG_COLOR"),
								'SET_TEXT_COLOR' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SET_TEXT_COLOR"),
								'SET_BG_COLOR_STR' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SET_BG_COLOR_STR"),
								'ADD_LINK' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_ADD_LINK"),
								'EXPRESSION' => GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_EXPRESSION")
							)
						)
					)
				);
				
				$arSelects = array();
				$arOptions = array();
				echo '<span style="display: none;">';
				foreach($arGroupOptions as $k=>$v)
				{
					$arOptions[$k] = array();
					$arSelects[$k] = '<select name="SHARE_'.htmlspecialcharsbx($k).'">';
					foreach($v as $k2=>$v2)
					{
						if(strlen($v2['NAME']) > 0 ) $arSelects[$k] .= '<optgroup label="'.htmlspecialcharsbx($v2['NAME']).'">';
						foreach($v2['ITEMS'] as $k3=>$v3)
						{
							$arOptions[$k][$k3] = preg_replace('/\(.*\)/', '', $v3);
							$arSelects[$k] .= '<option value="'.htmlspecialcharsbx($k3!==0 ? $k3 : '').'">'.htmlspecialcharsbx($v3).'</option>';
						}
						if(strlen($v2['NAME']) > 0 ) $arSelects[$k] .= '</optgroup>';
					}
					$arSelects[$k] .= '</select>';
					echo $arSelects[$k];
				}
				echo '</span>';
				
				
				foreach($arVals as $k=>$v)
				{
					/*$cellsOptions = '<option value="">'.sprintf(GetMessage("KDA_EE_SETTINGS_CONVERSION_CELL_CURRENT"), $i).'</option>';
					foreach($arFields as $k=>$arGroup)
					{
						if(is_array($arGroup['FIELDS']))
						{
							$cellsOptions .= '<optgroup label="'.$arGroup['TITLE'].'">';
							foreach($arGroup['FIELDS'] as $gkey=>$gfield)
							{
								$cellsOptions .= '<option value="'.$gkey.'"'.($v['CELL']==$gkey ? ' selected' : '').'>'.htmlspecialcharsbx($gfield).'</option>';
							}
							$cellsOptions .= '</optgroup>';
						}
					}
					$cellsOptions .= '<optgroup label="'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CELL_GROUP_OTHER").'">';
					$cellsOptions .= '<option value="ELSE"'.($v['CELL']=='ELSE' ? ' selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CELL_ELSE").'</option>';
					$cellsOptions .= '</optgroup>';*/
					
					
					echo '<div class="kda-ee-settings-conversion" '.(!$showCondition ? 'style="display: none;"' : '').'>'.
							GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_TITLE").
							' <span class="field_cell kda-ee-conv-select" data-select-name="SHARE_CELL"><input type="hidden" name="'.$fName.'[CELL][]" value="'.htmlspecialcharsbx($v['CELL']).'"><span class="kda-ee-conv-select-value" data-default-val="'.htmlspecialcharsbx(current($arOptions['CELL'])).'">'.(array_key_exists($v['CELL'], $arOptions['CELL']) ? $arOptions['CELL'][$v['CELL']] : current($arOptions['CELL'])).'</span></span>'.
							/*' <select name="'.$fName.'[CELL][]" class="field_cell">'.
								$cellsOptions.
							'</select> '.*/
							' <span class="field_when kda-ee-conv-select" data-select-name="SHARE_WHEN"><input type="hidden" name="'.$fName.'[WHEN][]" value="'.htmlspecialcharsbx($v['WHEN']).'"><span class="kda-ee-conv-select-value" data-default-val="'.htmlspecialcharsbx(current($arOptions['WHEN'])).'">'.(array_key_exists($v['WHEN'], $arOptions['WHEN']) ? $arOptions['WHEN'][$v['WHEN']] : current($arOptions['WHEN'])).'</span></span>'.
							/*' <select name="'.$fName.'[WHEN][]" class="field_when">'.
								'<option value="EQ" '.($v['WHEN']=='EQ' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_EQ").'</option>'.
								'<option value="NEQ" '.($v['WHEN']=='NEQ' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NEQ").'</option>'.
								'<option value="GT" '.($v['WHEN']=='GT' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_GT").'</option>'.
								'<option value="LT" '.($v['WHEN']=='LT' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_LT").'</option>'.
								'<option value="GEQ" '.($v['WHEN']=='GEQ' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_GEQ").'</option>'.
								'<option value="LEQ" '.($v['WHEN']=='LEQ' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_LEQ").'</option>'.
								'<option value="BETWEEN" '.($v['WHEN']=='BETWEEN' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_BETWEEN").'</option>'.
								'<option value="CONTAIN" '.($v['WHEN']=='CONTAIN' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_CONTAIN").'</option>'.
								'<option value="NOT_CONTAIN" '.($v['WHEN']=='NOT_CONTAIN' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NOT_CONTAIN").'</option>'.
								'<option value="EMPTY" '.($v['WHEN']=='EMPTY' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_EMPTY").'</option>'.
								'<option value="NOT_EMPTY" '.($v['WHEN']=='NOT_EMPTY' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NOT_EMPTY").'</option>'.
								'<option value="REGEXP" '.($v['WHEN']=='REGEXP' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_REGEXP").'</option>'.
								'<option value="NOT_REGEXP" '.($v['WHEN']=='NOT_REGEXP' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_NOT_REGEXP").'</option>'.
								'<option value="ANY" '.($v['WHEN']=='ANY' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_ANY").'</option>'.
							'</select> '.*/
							/*' <input type="text" class="field_from" name="'.$fName.'[FROM][]" value="'.htmlspecialcharsbx($v['FROM']).'"> '.*/
							' <span class="kda-ee-conv-field field_from">'.
								'<textarea name="'.$fName.'[FROM][]" rows="1">'.(strpos($v['FROM'], "\n")===0 ? "\n" : '').htmlspecialcharsbx($v['FROM']).'</textarea>'.
								'<input class="choose_val" value="..." type="button" onclick="ESettings.ShowChooseVal(this)">'.
							'</span>'.
							' '.GetMessage("KDA_EE_SETTINGS_CONVERSION_CONDITION_THEN").
							' <span class="field_then kda-ee-conv-select" data-select-name="SHARE_THEN"><input type="hidden" name="'.$fName.'[THEN][]" value="'.htmlspecialcharsbx($v['THEN']).'"><span class="kda-ee-conv-select-value" data-default-val="'.htmlspecialcharsbx(current($arOptions['THEN'])).'">'.(array_key_exists($v['THEN'], $arOptions['THEN']) ? $arOptions['THEN'][$v['THEN']] : current($arOptions['THEN'])).'</span></span>'.
							/*' <select name="'.$fName.'[THEN][]">'.
								'<optgroup label="'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_GROUP_STRING").'">'.
									'<option value="REPLACE_TO" '.($v['THEN']=='REPLACE_TO' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_REPLACE_TO").'</option>'.
									'<option value="REMOVE_SUBSTRING" '.($v['THEN']=='REMOVE_SUBSTRING' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_REMOVE_SUBSTRING").'</option>'.
									'<option value="REPLACE_SUBSTRING_TO" '.($v['THEN']=='REPLACE_SUBSTRING_TO' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_REPLACE_SUBSTRING_TO").'</option>'.
									'<option value="ADD_TO_BEGIN" '.($v['THEN']=='ADD_TO_BEGIN' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_ADD_TO_BEGIN").'</option>'.
									'<option value="ADD_TO_END" '.($v['THEN']=='ADD_TO_END' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_ADD_TO_END").'</option>'.
									'<option value="TRANSLIT" '.($v['THEN']=='TRANSLIT' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_TRANSLIT").'</option>'.
									'<option value="STRIP_TAGS" '.($v['THEN']=='STRIP_TAGS' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_STRIP_TAGS").'</option>'.
									'<option value="CLEAR_TAGS" '.($v['THEN']=='CLEAR_TAGS' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_CLEAR_TAGS").'</option>'.
								'</optgroup>'.
								'<optgroup label="'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_GROUP_MATH").'">'.
									'<option value="MATH_ROUND" '.($v['THEN']=='MATH_ROUND' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_ROUND").'</option>'.
									'<option value="MATH_MULTIPLY" '.($v['THEN']=='MATH_MULTIPLY' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_MULTIPLY").'</option>'.
									'<option value="MATH_DIVIDE" '.($v['THEN']=='MATH_DIVIDE' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_DIVIDE").'</option>'.
									'<option value="MATH_ADD" '.($v['THEN']=='MATH_ADD' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_ADD").'</option>'.
									'<option value="MATH_SUBTRACT" '.($v['THEN']=='MATH_SUBTRACT' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_MATH_SUBTRACT").'</option>'.
								'</optgroup>'.
								'<optgroup label="'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_GROUP_OTHER").'">'.
									'<option value="SKIP_LINE" '.($v['THEN']=='SKIP_LINE' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SKIP_LINE").'</option>'.
									'<option value="SET_BG_COLOR" '.($v['THEN']=='SET_BG_COLOR' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SET_BG_COLOR").'</option>'.
									'<option value="SET_TEXT_COLOR" '.($v['THEN']=='SET_TEXT_COLOR' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SET_TEXT_COLOR").'</option>'.
									'<option value="SET_BG_COLOR_STR" '.($v['THEN']=='SET_BG_COLOR_STR' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_SET_BG_COLOR_STR").'</option>'.
									'<option value="ADD_LINK" '.($v['THEN']=='ADD_LINK' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_ADD_LINK").'</option>'.
									'<option value="EXPRESSION" '.($v['THEN']=='EXPRESSION' ? 'selected' : '').'>'.GetMessage("KDA_EE_SETTINGS_CONVERSION_THEN_EXPRESSION").'</option>'.
								'</optgroup>'.
							'</select> '.*/
							/*' <input type="text" class="field_to" name="'.$fName.'[TO][]" value="'.htmlspecialcharsbx($v['TO']).'">'.*/
							' <span class="kda-ee-conv-field field_to">'.
								'<textarea name="'.$fName.'[TO][]" rows="1">'.(strpos($v['TO'], "\n")===0 ? "\n" : '').htmlspecialcharsbx($v['TO']).'</textarea>'.
								'<input class="choose_val" value="..." type="button" onclick="ESettings.ShowChooseVal(this)">'.
							'</span>'.
							'<a href="javascript:void(0)" onclick="ESettings.ConversionUp(this)" title="'.GetMessage("KDA_EE_SETTINGS_UP").'" class="up"></a>'.
							'<a href="javascript:void(0)" onclick="ESettings.ConversionDown(this)" title="'.GetMessage("KDA_EE_SETTINGS_DOWN").'" class="down"></a>'.
							'<a href="javascript:void(0)" onclick="ESettings.RemoveConversion(this)" title="'.GetMessage("KDA_EE_SETTINGS_DELETE").'" class="delete"></a>'.
						 '</div>';
				}
				?>
				<a href="javascript:void(0)" onclick="return ESettings.AddConversion(this);"><?echo GetMessage("KDA_EE_SETTINGS_CONVERSION_ADD_VALUE");?></a>
			</td>
		</tr>
		
		<?if($bPrice){?>
			<tr class="heading">
				<td colspan="2"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_TITLE");?></td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_USE_LANG_SETTINGS");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_USE_LANG_SETTINGS');
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_SHOW_CURRENCY");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_SHOW_CURRENCY');
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_CONVERT_CURRENCY");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_CONVERT_CURRENCY');
					$convertCurrency = $val;
					?>
					<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?> onchange="ESettings.ToggleSubfields(this)">
				</td>
			</tr>
			<tr class="subfield" <?if($convertCurrency!='Y'){echo 'style="display: none;"';}?>>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_CONVERT_CURRENCY_TO");?>:</td>
				<td class="adm-detail-content-cell-r">
					<?
					list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_CONVERT_CURRENCY_TO');
					?>
					<select name="<?=$fName?>">
					<?
					foreach($arCurrency as $item)
					{
						?><option value="<?echo $item['CURRENCY']?>"<?if($val==$item['CURRENCY']){echo 'selected';}?>>[<?echo $item['CURRENCY']?>] <?echo $item['FULL_NAME']?></option><?
					}
					?>
					</select>
				</td>
			</tr>
			
			<?if($field!="ICAT_PURCHASING_PRICE"){?>
				<tr>
					<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_USE_VAT");?>:</td>
					<td class="adm-detail-content-cell-r">
						<?
						list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_USE_VAT');
						$convertCurrency = $val;
						?>
						<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
					</td>
				</tr>
			
				<tr>
					<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_PRICE_USE_EXT");?>:</td>
					<td class="adm-detail-content-cell-r">
						<?
						list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_USE_EXT');
						$priceExt = $val;
						?>
						<input type="checkbox" name="<?=$fName?>" value="Y" <?echo ($val=='Y' ? 'checked' : '')?> onchange="$('#price_ext').css('display', (this.checked ? '' : 'none'));">
					</td>
				</tr>
				<tr id="price_ext" <?if($priceExt!='Y'){echo 'style="display: none;"';}?>>
					<td class="adm-detail-content-cell-l"></td>
					<td class="adm-detail-content-cell-r">
						<?
						list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_QUANTITY_FROM');
						?>
						<?echo GetMessage("KDA_EE_SETTINGS_PRICE_QUANTITY_FROM");?>
						<input type="text" name="<?=$fName?>" value="<?echo htmlspecialcharsbx($val)?>" size="5">
						&nbsp; &nbsp;
						<?
						list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PRICE_QUANTITY_TO');
						?>
						<?echo GetMessage("KDA_EE_SETTINGS_PRICE_QUANTITY_TO");?>
						<input type="text" name="<?=$fName?>" value="<?echo htmlspecialcharsbx($val)?>" size="5">
					</td>
				</tr>
			<?}?>
		<?}?>
		
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_EE_SETTINGS_DISPLAY_TITLE");?></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_SETTINGS_DISPLAY_WIDTH");?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'DISPLAY_WIDTH');
				?>
				<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" placeholder="200">
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'TEXT_ALIGN');
				?>
				<select name="<?=$fName?>">
					<option value=""><?echo GetMessage("KDA_EE_NOT_CHANGE"); ?></option>
					<option value="LEFT" <?if($val=='LEFT'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN_LEFT"); ?></option>
					<option value="CENTER" <?if($val=='CENTER'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN_CENTER"); ?></option>
					<option value="RIGHT" <?if($val=='RIGHT'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN_RIGHT"); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'VERTICAL_ALIGN');
				?>
				<select name="<?=$fName?>">
					<option value=""><?echo GetMessage("KDA_EE_NOT_CHANGE"); ?></option>
					<option value="TOP" <?if($val=='TOP'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN_TOP"); ?></option>
					<option value="CENTER" <?if($val=='CENTER'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN_CENTER"); ?></option>
					<option value="BOTTOM" <?if($val=='BOTTOM'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN_BOTTOM"); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_FONT_COLOR"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'FONT_COLOR');
				?>
				<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" placeholder="#ffffff">
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_BACKGROUND_COLOR"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'BACKGROUND_COLOR');
				?>
				<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" placeholder="#ffffff">
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_FONT_FAMILY"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'FONT_FAMILY');
				?>
				<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" placeholder="Calibri">
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_FONT_SIZE"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'FONT_SIZE');
				?>
				<input type="text" name="<?=$fName?>" value="<?=htmlspecialcharsbx($val)?>" placeholder="11">
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_FONT_STYLE_BOLD"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'STYLE_BOLD');
				?>
				<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_FONT_STYLE_ITALIC"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'STYLE_ITALIC');
				?>
				<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'NUMBER_FORMAT');
				?>
				<select name="<?=$fName?>">
					<option value=""><?echo GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_GENERAL"); ?></option>
					<option value="49"<?if($val=='49'){echo ' selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_TEXT"); ?></option>
					<option value="1"<?if($val=='1'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_NUMERIC"), '1234'); ?></option>
					<option value="3"<?if($val=='3'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_NUMERIC"), '1 234'); ?></option>
					<option value="2"<?if($val=='2'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_NUMERIC"), '1234,10'); ?></option>
					<option value="4"<?if($val=='4'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_NUMERIC"), '1 234,10'); ?></option>
					<option value="5"<?if($val=='5'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_FINANCIAL"), '1 234 P'); ?></option>
					<option value="7"<?if($val=='7'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_FINANCIAL"), '1 234,10 P'); ?></option>
					<option value="[$$-409]#,##0"<?if($val=='[$$-409]#,##0'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_FINANCIAL"), '$1 234'); ?></option>
					<option value="[$$-409]#,##0.00"<?if($val=='[$$-409]#,##0.00'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_FINANCIAL"), '$1 234,10'); ?></option>
					<option value="[$€-2]\ #,##0"<?if($val=='[$€-2]\ #,##0'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_FINANCIAL"), '€1 234'); ?></option>
					<option value="[$€-2]\ #,##0.00"<?if($val=='[$€-2]\ #,##0.00'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_FINANCIAL"), '€1 234,10'); ?></option>
					<option value="14"<?if($val=='14'){echo ' selected';}?>><?echo sprintf(GetMessage("KDA_EE_DISPLAY_NUMBER_FORMAT_DATE"), date('d.m.Y')); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_NUMBER_DECIMALS"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'NUMBER_DECIMALS');
				if(strlen($val)==0 || (int)$val < 0 || (int)$val > 30) $val = 2;
				$val = (int)$val;
				?>
				<select name="<?=$fName?>">
					<?
					for($i=0; $i<=30; $i++)
					{
						echo '<option value="'.($i==2 ? '' : $i).'"'.($val==$i ? ' selected' : '').'>'.$i.'</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_PROTECTION"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PROTECTION');
				?>
				<select name="<?=$fName?>">
					<option value=""><?echo GetMessage("KDA_EE_DISPLAY_PROTECTION_ENABLE"); ?></option>
					<option value="N"<?if($val=='N'){echo ' selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_PROTECTION_DISABLE"); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_PROTECTION_HIDDEN"); ?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'PROTECTION_HIDDEN');
				?>
				<select name="<?=$fName?>">
					<option value=""><?echo GetMessage("KDA_EE_DISPLAY_PROTECTION_HIDDEN_DISABLE"); ?></option>
					<option value="Y"<?if($val=='Y'){echo ' selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_PROTECTION_HIDDEN_ENABLE"); ?></option>
				</select>
			</td>
		</tr>
		<tr>
		  <td colspan="2">
			<table cellspacing="0" width="100%">
				<col width="50%">
				<col width="50%">
				<tr>
					<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_MAKE_DROPDOWN"); ?>:</td>
					<td class="adm-detail-content-cell-r">
						<?
						list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'MAKE_DROPDOWN');
						$valMakeDropdown = $val;
						?>
						<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?> onchange="$('#make_dropdown_full').css('display', this.checked ? '' : 'none')">
					</td>
				</tr>
				<?if($bIblockElement && strpos($field, "IP_PROP")===0){?>
				<tr id="make_dropdown_full"<?if($valMakeDropdown!='Y'){echo ' style="display: none;"';}?>>
					<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_EE_DISPLAY_MAKE_DROPDOWN_FULL"); ?>:</td>
					<td class="adm-detail-content-cell-r">
						<?
						list($fName, $val) = GetFieldEextraVal($PEXTRASETTINGS, 'MAKE_DROPDOWN_FULL');
						?>
						<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
					</td>
				</tr>
				<?}?>
			</table>
		  </td>
		</tr>
		
	</table>
</form>
<script>
var admKDASettingMessages = <?echo \KdaIE\Utils::PhpToJSObject($arFields)?>;
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>