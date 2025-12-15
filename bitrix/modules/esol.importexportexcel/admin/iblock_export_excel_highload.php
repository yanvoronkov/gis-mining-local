<?
if(!defined('NO_AGENT_CHECK')) define('NO_AGENT_CHECK', true);
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$moduleId = 'esol.importexportexcel';
$moduleFilePrefix = 'esol_export_excel';
$moduleJsId = 'esol_exportexcel';
$moduleJsId2 = str_replace('.', '_', $moduleId);
$moduleDemoExpiredFunc = $moduleJsId2.'_demo_expired';
$moduleShowDemoFunc = $moduleJsId2.'_show_demo';
$moduleRunnerClass = 'CEsolImpExpExcelRunner';
CModule::IncludeModule("iblock");
CModule::IncludeModule('highloadblock');
CModule::IncludeModule($moduleId);
CJSCore::Init(array($moduleJsId.'_highload'));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

include_once(dirname(__FILE__).'/../install/demo.php');
if (call_user_func($moduleDemoExpiredFunc)) {
	require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	call_user_func($moduleShowDemoFunc);
	require ($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if($MODULE_RIGHT <= "R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$SETTINGS_DEFAULT = $SETTINGS = null;
if($_POST)
{
	if(isset($_POST['SETTINGS'])) $SETTINGS = $_POST['SETTINGS'];
	if(isset($_POST['SETTINGS_DEFAULT'])) $SETTINGS_DEFAULT = $_POST['SETTINGS_DEFAULT'];
	$arFilterKeys = preg_grep('/^filter_\d+_/', array_keys($_POST));
	foreach($arFilterKeys as $key)
	{
		$arKey = explode('_', $key, 3);
		$SETTINGS['FILTER'][$arKey[1]][$arKey[2]] = $_POST[$key];
	}
	
	if(isset($SETTINGS) && !isset($SETTINGS['LIST_NAME']))
	{
		unset($SETTINGS);
	}
}

if(isset($_FILES) && is_array($_FILES))
{
	$arFileKeys = preg_grep('/^NEW_PICTURE_.+_\d+_\d+$/', array_keys($_FILES));
	foreach($arFileKeys as $fileKey)
	{
		if(!empty($_FILES[$fileKey]))
		{
			$fid = CFile::SaveFile($_FILES[$fileKey], $moduleId);
			$arSubKeys = explode('_', substr($fileKey, 12));
			$blockKey = array_pop($arSubKeys);
			$listKey = array_pop($arSubKeys);
			$blockTextKey = implode('_', $arSubKeys);
			$SETTINGS[$blockTextKey][$listKey][$blockKey] = '[['.$fid.']]';
		}
	}
}

if(($ACTION=='SHOW_PREVIEW' || $ACTION=='DO_EXPORT') && (!defined('BX_UTF') || !BX_UTF))
{
	$SETTINGS = $APPLICATION->ConvertCharsetArray($SETTINGS, 'UTF-8', 'CP1251');
	if($EXTRASETTINGS) $EXTRASETTINGS = $APPLICATION->ConvertCharsetArray($EXTRASETTINGS, 'UTF-8', 'CP1251');
}

$oProfile = new CKDAExportProfile('highload');
if(strlen($PROFILE_ID) > 0 && $PROFILE_ID!=='new')
{
	if($MODULE_RIGHT <= 'T')
	{
		$SETTINGS = $SETTINGS_DEFAULT = $EXTRASETTINGS = null;
	}
	$PROFILE_ID = (int)$PROFILE_ID;
	$oProfile->Apply($SETTINGS_DEFAULT, $SETTINGS, $PROFILE_ID);
	if($EXTRASETTINGS)
	{
		foreach($EXTRASETTINGS as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				if($v2 && !is_array($v2))
				{
					$EXTRASETTINGS[$k][$k2] = \KdaIE\Utils::JsObjectToPhp($v2);
				}
			}
		}
	}
	$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);
	
	if($MODULE_RIGHT <= 'T' && is_array($_POST['SETTINGS']))
	{
		$arFilterKeys = array('HLFILTER');
		foreach($arFilterKeys as $filterKey)
		{
			if(array_key_exists($filterKey, $_POST['SETTINGS'])) $SETTINGS[$filterKey] = $_POST['SETTINGS'][$filterKey];
			elseif(array_key_exists($filterKey, $SETTINGS)) unset($SETTINGS[$filterKey]);
		}
	}
}

$SHOW_FIRST_LINES = 10;
$SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'] = intval($SETTINGS_DEFAULT['HIGHLOADBLOCK_ID']);
if(!isset($HIGHLOADBLOCK_ID)) $HIGHLOADBLOCK_ID = $SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'];
$STEP = intval($STEP);
if ($STEP <= 0)
	$STEP = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if(isset($_POST["backButton"]) && strlen($_POST["backButton"]) > 0) $STEP = $STEP - 2;
	if(isset($_POST["saveConfigButton"]) && strlen($_POST["saveConfigButton"]) > 0) $STEP = $STEP - 1;
	if(isset($_POST["backButton2"]) && strlen($_POST["backButton2"]) > 0) $STEP = 1;
}

$strErrorProfile = $oProfile->GetErrors();
$strError = '';
$io = CBXVirtualIo::GetInstance();

function ShowSheetData($list, $SETTINGS, $SETTINGS_DEFAULT, $EXTRASETTINGS, $MODULE_RIGHT)
{
	ob_start();
	if(!is_array($SETTINGS)) $SETTINGS = array();
	$iblockId = $SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'];
	$fl = new CKDAEEFieldList($SETTINGS_DEFAULT);
	$changeIblockId = (bool)($SETTINGS['CHANGE_HLBL_ID'][$list]=='Y');
	if($changeIblockId && $SETTINGS['LIST_HLBL_ID'][$list])
	{
		$iblockId = $SETTINGS['LIST_HLBL_ID'][$list];
	}
	
	$fl = new CKDAEEFieldList($SETTINGS_DEFAULT);
	$arIblocks = $fl->GetHighloadBlocks();
	$listName = ($SETTINGS['LIST_NAME'][$list] ? $SETTINGS['LIST_NAME'][$list] : sprintf(GetMessage("KDA_EE_SHEET_NAME"), $list+1));
	
	/*$filterId = 'kda_exportexcel_'.$PROFILE_ID.'_'.$list;
	CKDAExportUtils::ShowFilter($filterId, $list, $SETTINGS, $SETTINGS_DEFAULT);*/
	
	$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
	$ee = CKDAExportExcelHighload::getInstance($params, $EXTRASETTINGS, false, $PROFILE_ID);

	$arRes = $ee->GetExportData($list, 15, 0);
	$arFields = $arRes['FIELDS'];
	$arData = $arRes['DATA'];		
	
	if(!isset($SETTINGS['DISPLAY_PARAMS'][$list])) $SETTINGS['DISPLAY_PARAMS'][$list] = array();
	if(!isset($SETTINGS['DISPLAY_PARAMS'][$list]['COLUMN_TITLES'])) $SETTINGS['DISPLAY_PARAMS'][$list]['COLUMN_TITLES'] = array('STYLE_BOLD' => 'Y');
	
	/*Additionals rows*/
	$textKeys = array('TEXT_ROWS_TOP', 'TEXT_ROWS_TOP2');
	$additionalRows = array();
	foreach($textKeys as $textKey)
	{
		if(!empty($SETTINGS[$textKey][$list]))
		{
			foreach($SETTINGS[$textKey][$list] as $k=>$v)
			{
				$rowContent = '';
				$dataType = 'text';
				if(preg_match('/^\[\[(\d+)\]\]$/', $v, $m))
				{
					$dataType = 'image';
					$fileId = $m[1];
				}
				$v = trim($v);
				$dataKey = $textKey.'_'.$k;
				$dSettings = $SETTINGS['DISPLAY_PARAMS'][$list][$dataKey];
				$rowContent .= '<tr>';
				$rowContent .= '<td>'.($MODULE_RIGHT > 'T' ? '<span class="sandwich" data-key="'.$dataKey.'" data-type="'.$dataType.'" title="'.GetMessage("KDA_EE_ACTIONS_BTN").'"></span>' : '').'</td>';
				if($dataType == 'image')
				{
					$rowContent .= '<td colspan="'.count($arFields).'"><div class="cell cell_wide80"><div class="cell_inner" '.CKDAExportUtils::GetCellStyleFormatted($dSettings, $SETTINGS_DEFAULT).'>';
					$maxWidth = ((int)$dSettings['PICTURE_WIDTH'] > 0 ? (int)$dSettings['PICTURE_WIDTH'] : 0);
					$maxHeight = ((int)$dSettings['PICTURE_HEIGHT'] > 0 ? (int)$dSettings['PICTURE_HEIGHT'] : 0);
					$arFile = CFile::GetFileArray($fileId);
					$rowContent .= '<img src="'.htmlspecialcharsex($arFile['SRC']).'" style="'.($maxWidth > 0 ? 'max-width: '.$maxWidth.'px;' : '').($maxHeight > 0 ? 'max-height: '.$maxHeight.'px;' : '').'">';
					$rowContent .= '<input type="hidden" name="SETTINGS['.$textKey.']['.$list.']['.$k.']" value="'.htmlspecialcharsex($v).'">';
					$rowContent .= '</div></div></td>';
				}
				else
				{
					$rowContent .= '<td colspan="'.count($arFields).'"><div class="cell cell_wide80"><div class="cell_inner">';
					$rowContent .= '<textarea class="kda-ee-text-block" name="SETTINGS['.$textKey.']['.$list.']['.$k.']" '.CKDAExportUtils::GetCellStyleFormatted($dSettings, $SETTINGS_DEFAULT).($MODULE_RIGHT <= 'T' ? ' disabled' : '').'>'.$v.'</textarea>';
					$rowContent .= '</div></div></td>';
				}
				$rowContent .= '</tr>';
				$additionalRows[$textKey][] = $rowContent;
			}
		}
	}
	/*/Additionals rows*/
	
	$sortVal = ($SETTINGS['SORT'][$list] ? $SETTINGS['SORT'][$list] : 'ID=>ASC');
	list($sortBy, $sortOrder) = explode('=>', $sortVal);
	$arSortableFields = $fl->GetHlblSortableFields($iblockId);
	
	ob_end_clean();
	?>
	<div class="kda-ee-title">
		<input type="text" name="SETTINGS[LIST_NAME][<?echo $list?>]" value="<?echo htmlspecialcharsbx($listName)?>" maxlength="31"<?if($MODULE_RIGHT <= 'T'){echo ' disabled';}?>>
		<?if($list > 0 && $MODULE_RIGHT > 'T'){?>
			<a href="javascript:void(0)" class="kda-ee-remove-list" onclick="EList.RemoveList(this);" title="<?echo GetMessage("KDA_EE_REMOVE_LIST"); ?>"></a>
		<?}?>
	</div>
	<div class="kda-ee-hidden-settings">
		<?echo $fl->ShowSelectFieldsHighload($iblockId, 'FIELDS_LIST['.$list.']');?>
		<?if(isset($SETTINGS['DISPLAY_PARAMS'][$list]) && !empty($SETTINGS['DISPLAY_PARAMS'][$list])){?>
			<input type="hidden" name="SETTINGS[DISPLAY_PARAMS][<?echo $list;?>]" value="">
			<script>EList.SetDisplayParams("<?echo $list?>", <?echo \KdaIE\Utils::PhpToJSObject($SETTINGS['DISPLAY_PARAMS'][$list])?>)</script>
		<?}?>
		<input type="hidden" name="SETTINGS[SORT][<?echo $list;?>]" value="<?echo htmlspecialcharsbx($sortVal);?>">
		<input type="hidden" name="SETTINGS[SORT_OFFER][<?echo $list;?>]" value="<?echo htmlspecialcharsbx($sortOfferVal);?>">
	</div>
	<?
	if($MODULE_RIGHT > 'T')
	{?>
	<div class="kda-ee-additional-settings">
		<a href="javascript:void(0)" class="addsettings_link" onclick="EList.ToggleAddSettingsBlock(this)"><span><?echo GetMessage("KDA_EE_ADDITIONAL_SETTINGS"); ?></span></a>
		<div class="addsettings_inner">
			<table class="additional">
				<col><col width="400px">
				<tr>
					<td><?echo GetMessage("KDA_EE_LIST_LABEL_COLOR"); ?>:</td>
					<td>
						<input type="text" name="SETTINGS[LIST_LABEL_COLOR][<?echo $list;?>]" value="<?=htmlspecialcharsbx($SETTINGS['LIST_LABEL_COLOR'][$list])?>" placeholder="#ffffff" size="10">
					</td>
				</tr>
				<tr>
					<td><?echo GetMessage("KDA_EE_HIDE_COLUMN_TITLES"); ?>:</td>
					<td>
						<input type="hidden" name="SETTINGS[HIDE_COLUMN_TITLES][<?echo $list;?>]" value="N">
						<input type="checkbox" name="SETTINGS[HIDE_COLUMN_TITLES][<?echo $list;?>]" value="Y" <?if($SETTINGS['HIDE_COLUMN_TITLES'][$list]=='Y'){echo 'checked';}?>>
					</td>
				</tr>
				<tr>
					<td><?echo GetMessage("KDA_EE_ENABLE_AUTOFILTER"); ?>:</td>
					<td>
						<input type="hidden" name="SETTINGS[ENABLE_AUTOFILTER][<?echo $list;?>]" value="N">
						<input type="checkbox" name="SETTINGS[ENABLE_AUTOFILTER][<?echo $list;?>]" value="Y" <?if($SETTINGS['ENABLE_AUTOFILTER'][$list]=='Y'){echo 'checked';}?>>
					</td>
				</tr>
				<tr>
					<td><?echo GetMessage("KDA_EE_ENABLE_PROTECTION"); ?>:</td>
					<td>
						<input type="hidden" name="SETTINGS[ENABLE_PROTECTION][<?echo $list;?>]" value="N">
						<input type="checkbox" name="SETTINGS[ENABLE_PROTECTION][<?echo $list;?>]" value="Y" <?if($SETTINGS['ENABLE_PROTECTION'][$list]=='Y'){echo 'checked';}?>>
					</td>
				</tr>
				<tr>
					<td><?echo GetMessage("KDA_EE_CHANGE_IBLOCK_ID"); ?>:</td>
					<td>
						<input type="hidden" name="SETTINGS[CHANGE_HLBL_ID][<?echo $list;?>]" value="N">
						<input type="checkbox" name="SETTINGS[CHANGE_HLBL_ID][<?echo $list;?>]" value="Y" <?if($changeIblockId){echo 'checked';}?> onchange="EList.ToggleAddSettings(this); EList.ChooseChangeIblock(this);">
					</td>
				</tr>
				
				<tr class="subfield" <?if(!$changeIblockId){echo 'style="display: none;"';}?>>
					<td><?echo GetMessage("KDA_EE_HIGHLOADBLOCK"); ?></td>
					<td>
						<select name="SETTINGS[LIST_HLBL_ID][<?echo $list;?>]" onchange="EList.ChooseIblock(this);">
							<?
							foreach($arIblocks as $iblock)
							{
								?><option value="<?echo $iblock["ID"];?>" <?if($iblock["ID"]==$iblockId){echo 'selected';}?>><?echo htmlspecialcharsbx($iblock["NAME"]); ?></option><?
							}
							?>
						</select>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?
	}	
	echo '<div class="kda-ee-tbl-scroll"><div></div></div>';
	echo '<div class="kda-ee-tbl-wrap">';
	echo '<table class="kda-ee-tbl" data-iblock-id="'.$iblockId.'">';
	
	$textKey = 'TEXT_ROWS_TOP';
	if(isset($additionalRows[$textKey]) && is_array($additionalRows[$textKey]))
	{
		echo implode('', $additionalRows[$textKey]);
	}
	
	echo '<tr class="kda-ee-tbl-titles">';
	echo '<th>'.($MODULE_RIGHT > 'T' ? '<span class="sandwich" data-key="COLUMN_TITLES" title="'.GetMessage("KDA_EE_ACTIONS_BTN").'"></span>' : '').'</th>';
	foreach($arFields as $k=>$field)
	{
		$fieldName = $SETTINGS['FIELDS_LIST_NAMES'][$list][$k];
		$isSortable = (bool)in_array($field, $arSortableFields);
		if($isSortable)
		{
			$sortClass = 'sort_up';
			$sortTitle = GetMessage("KDA_EE_SETTINGS_SORT_ASC");
			if($sortBy==$field)
			{
				if($sortOrder!='DESC')
				{
					$sortClass = 'sort_down';
					$sortTitle = GetMessage("KDA_EE_SETTINGS_SORTED_ASC")."\r\n".GetMessage("KDA_EE_SETTINGS_SORT_DESC");
				}
				else
				{
					$sortTitle = GetMessage("KDA_EE_SETTINGS_SORTED_DESC")."\r\n".$sortTitle;
				}
				$sortClass .= ' active';
			}
		}
		echo '<th>'.
				'<div>'.
					'<input type="hidden" name="SETTINGS[FIELDS_LIST]['.$list.']['.$k.']" value="'.htmlspecialcharsbx($field).'" >'.
					'<input type="text" name="FIELDS_LIST_SHOW['.$list.']['.$k.']" value="" class="fieldval"'.($MODULE_RIGHT <= 'T' ? ' disabled' : '').'>'.
					(
						$MODULE_RIGHT > 'T' 
						?
						(
						'<a href="javascript:void(0)" class="field_settings '.(empty($EXTRASETTINGS[$list][$k]) ? 'inactive' : '').'" id="field_settings_'.$list.'_'.$k.'" title="'.GetMessage("KDA_EE_SETTINGS_FIELD").'" onclick="EList.ShowFieldSettings(this);">'.
							'<input type="hidden" name="EXTRASETTINGS['.$list.']['.$k.']" value="">'.
							'<script>EList.SetExtraParams("field_settings_'.$list.'_'.$k.'", '.(empty($EXTRASETTINGS[$list][$k]) ? '""' : \KdaIE\Utils::PhpToJSObject($EXTRASETTINGS[$list][$k])).')</script>'.
						'</a>'.
						'<a href="javascript:void(0)" class="field_delete" title="'.GetMessage("KDA_EE_SETTINGS_DELETE_FIELD").'" onclick="EList.DeleteColumn(this);"></a>'.
						'<a href="javascript:void(0)" onclick="EList.AddColumn(this);" class="kda-ee-new-column" title="'.GetMessage("KDA_EE_SETTINGS_ADD_FIELD").'"></a>'
						)
						:
						''
					).
				'</div>'.
				'<div>'.
					'<input type="text" name="SETTINGS[FIELDS_LIST_NAMES]['.$list.']['.$k.']" value="'.htmlspecialcharsbx($fieldName).'" class="fieldname" '.CKDAExportUtils::GetCellStyleFormatted($SETTINGS['DISPLAY_PARAMS'][$list]['COLUMN_TITLES'], $SETTINGS_DEFAULT).($MODULE_RIGHT <= 'T' ? ' disabled' : '').'>'.
					($MODULE_RIGHT > 'T' && $isSortable ? '<a href="javascript:void(0);" class="'.$sortClass.'" onclick="EList.Sort(this, \'\');" title="'.htmlspecialcharsbx($sortTitle).'"></a>' : '').
				'</div>'.
			'</th>';
	}
	echo '</tr>';
	
	$textKey = 'TEXT_ROWS_TOP2';
	if(isset($additionalRows[$textKey]) && is_array($additionalRows[$textKey]))
	{
		echo implode('', $additionalRows[$textKey]);
	}
		
	foreach($arData as $arElement)
	{
		echo '<tr>';
		echo '<td></td>';
		foreach($arFields as $key=>$field)
		{
			$val = (isset($arElement[$field.'_'.$key]) ? $arElement[$field.'_'.$key] : $arElement[$field]);
			$fSettings = $EXTRASETTINGS[$list][$key];
			if(isset($fSettings['INSERT_PICTURE']) && $fSettings['INSERT_PICTURE']=='Y' && $ee->IsPictureField($field))
			{
				$maxWidth = ((int)$fSettings['PICTURE_WIDTH'] > 0 ? (int)$fSettings['PICTURE_WIDTH'] : 100);
				$maxHeight = ((int)$fSettings['PICTURE_HEIGHT'] > 0 ? (int)$fSettings['PICTURE_HEIGHT'] : 100);
				if($ee->IsMultipleField($field))
				{
					if($fSettings['CHANGE_MULTIPLE_SEPARATOR']=='Y') $separator = $fSettings['MULTIPLE_SEPARATOR'];
					else $separator = $SETTINGS_DEFAULT['ELEMENT_MULTIPLE_SEPARATOR'];
					$arVals = explode($separator, $val);
					$val = '';
					foreach($arVals as $mval)
					{
						$val .= '<img src="'.htmlspecialcharsex($mval).'" style="max-width: '.$maxWidth.'px; max-height: '.$maxHeight.'px;">';
					}
				}
				else
				{
					$val = '<img src="'.htmlspecialcharsex($val).'" style="max-width: '.$maxWidth.'px; max-height: '.$maxHeight.'px;">';
				}
			}
			echo '<td><div class="cell"><div class="cell_inner">'.$val.'</div></div></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

/////////////////////////////////////////////////////////////////////
if ($REQUEST_METHOD == "POST" && $MODE=='AJAX')
{
	define('PUBLIC_AJAX_MODE', 'Y');
	
	if($ACTION=='GET_FILTER_FIELD_VALS')
	{
		$oFilter = new CKDAEEFilter($_POST['IBLOCK_ID'], 'hl');
		$arValues = $oFilter->GetListValues($_POST['FIELD'], array(
			'query' => (isset($_POST['q']) ? $_POST['q'] : ''),
			'inputname' => (isset($_POST['inputname']) ? $_POST['inputname'] : ''),
			'oldvalue' => (isset($_POST['oldvalue']) ? $_POST['oldvalue'] : '')
		));
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		echo \KdaIE\Utils::PhpToJSObject($arValues);
		die();
	}
	
	if($ACTION=='REMOVE_PROCESS_PROFILE')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$oProfile = new CKDAExportProfile('highload');
		$oProfile->RemoveProcessedProfile($PROCCESS_PROFILE_ID);
		die();
	}
	
	if($ACTION=='GET_SECTION_LIST')
	{
		$fl = new CKDAEEFieldList($SETTINGS_DEFAULT);
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		?><div><?
		//$fl->ShowSelectSections($IBLOCK_ID, 'sections');
		$fl->ShowSelectFieldsHighload($HLBL_ID, 'fields');
		?></div><?
		die();
	}
	
	if($ACTION=='DELETE_PROFILE')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$fl = new CKDAExportProfile('highload');
		$fl->Delete($_REQUEST['ID']);
		die();
	}
	
	if($ACTION=='COPY_PROFILE')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$fl = new CKDAExportProfile('highload');
		$id = $fl->Copy($_REQUEST['ID']);
		echo \KdaIE\Utils::PhpToJSObject(array('id'=>$id));
		die();
	}
	
	if($ACTION=='RENAME_PROFILE')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$newName = $_REQUEST['NAME'];
		if((!defined('BX_UTF') || !BX_UTF)) $newName = $APPLICATION->ConvertCharset($newName, 'UTF-8', 'CP1251');
		$fl = new CKDAExportProfile('highload');
		$fl->Rename($_REQUEST['ID'], $newName);
		die();
	}
	
	if($ACTION=='APPLY_TO_LISTS')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$fl = new CKDAExportProfile('highload');
		$fl->ApplyToLists($_REQUEST['PROFILE_ID'], $_REQUEST['LIST_FROM'], $_REQUEST['LIST_TO']);
		die();
	}
	
	if($ACTION=='GET_SESSID')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		echo bitrix_sessid_post();
		die();
	}	
}

if ($REQUEST_METHOD == "POST" && $STEP > 1 && check_bitrix_sessid())
{
	if($ACTION) define('PUBLIC_AJAX_MODE', 'Y');
	
	//*****************************************************************//	
	if ($STEP > 1)
	{
		//*****************************************************************//		
		if(strlen($PROFILE_ID)==0)
		{
			$strError.= GetMessage("KDA_EE_PROFILE_NOT_CHOOSE")."<br>";
		}
		
		if (strlen($strError) <= 0)
		{
			if (!$SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'])
				$strError.= GetMessage("KDA_EE_NO_IBLOCK")."<br>";
			if(!$SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'])
			{
				$strError.= GetMessage("KDA_EE_NO_HIGHLOADBLOCK")."<br>";
			}
		}
		
		if (strlen($strError) <= 0)
		{
			$fpath = $SETTINGS_DEFAULT['FILE_PATH'];
			if(strpos($fpath, '/')!==0)
				$strError.= GetMessage("KDA_EE_FILE_PATH_INCORRECT")."<br>";
		}
		
		if (strlen($strError) <= 0)
		{
			/*Write profile*/
			$oProfile = new CKDAExportProfile('highload');
			if($PROFILE_ID === 'new')
			{
				$PID = $oProfile->Add($NEW_PROFILE_NAME);
				if($PID===false)
				{
					if($ex = $APPLICATION->GetException())
					{
						$strError .= $ex->GetString().'<br>';
					}
				}
				else
				{
					$PROFILE_ID = $PID;
				}
			}
			/*/Write profile*/
		}

		if (strlen($strError) > 0)
			$STEP = 1;
		//*****************************************************************//

	}
	
	if($ACTION=='SHOW_PREVIEW')
	{
		$APPLICATION->RestartBuffer();
		if(array_key_exists('SHEET_INDEXES', $_POST))
		{
			$time = time();
			echo '<div>';
			$arIndexes = explode(',', $_POST['SHEET_INDEXES']);
			foreach($arIndexes as $k=>$index)
			{
				//if((1 || time() - $time > 5) && $k > 0) continue;
				//sleep(2);
				if(time() - $time > 5 && $k > 0) continue;
				echo '<div id="kda-ee-sheet-'.$index.'">';
				ShowSheetData($index, $SETTINGS, $SETTINGS_DEFAULT, $EXTRASETTINGS, $MODULE_RIGHT);
				echo '</div>';
			}
			echo '</div>';
		}
		else
		{
			ShowSheetData($_POST['SHEET_INDEX'], $SETTINGS, $SETTINGS_DEFAULT, $EXTRASETTINGS, $MODULE_RIGHT);
		}
		
		die();
	}
	
	if($ACTION == 'DO_EXPORT')
	{
		unset($EXTRASETTINGS);
		$oProfile = new CKDAExportProfile('highload');
		$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);
		$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
		//$params = $SETTINGS_DEFAULT + $SETTINGS;
		$stepparams = $_POST['stepparams'];
		if(!is_array($stepparams)) $stepparams = array();
		$sess = $_SESSION;
		session_write_close();
		$_SESSION = $sess;
		$arResult = $moduleRunnerClass::ExportHighloadblock($params, $EXTRASETTINGS, $stepparams, $PROFILE_ID);
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		echo '<!--module_return_data-->'.\KdaIE\Utils::PhpToJSObject($arResult).'<!--/module_return_data-->';
		die();
	}
	
	/*Update profile*/
	if(strlen($PROFILE_ID) > 0 && $PROFILE_ID!=='new')
	{
		$oProfile->Update($PROFILE_ID, $SETTINGS_DEFAULT, $SETTINGS, $EXTRASETTINGS);
		//if(is_array($EXTRASETTINGS)) $oProfile->UpdateExtra($PROFILE_ID, $EXTRASETTINGS);
	}
	/*/Update profile*/
	
	if ($STEP > 2)
	{
		/*$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
		$ie = new CKDAExportExcelHighload($DATA_FILE_NAME, $params);
		$ie->Import();
		die();*/
	}
	//*****************************************************************//

}

/////////////////////////////////////////////////////////////////////
$APPLICATION->SetTitle(GetMessage("KDA_EE_PAGE_TITLE").$STEP);
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

if (!call_user_func($moduleDemoExpiredFunc)) {
	call_user_func($moduleShowDemoFunc);
}

$aMenu = array(
	array(
		/*"TEXT"=>GetMessage("KDA_EE_MENU_HELP"),
		"TITLE"=>GetMessage("KDA_EE_MENU_HELP"),
		"ONCLICK" => "EHelper.ShowHelp();",
		"ICON" => "",*/
		"HTML" => '<a href="https://esolutions.su/solutions/'.$moduleId.'/?tab=video" target="blank" class="adm-btn" title="'.GetMessage("KDA_EE_MENU_VIDEO").'">'.GetMessage("KDA_EE_MENU_VIDEO").'</a>'
	),
	array(
		"HTML" => '<a href="https://esolutions.su/solutions/'.$moduleId.'/?tab=faq" target="blank" class="adm-btn" title="'.GetMessage("KDA_EE_MENU_FAQ").'">'.GetMessage("KDA_EE_MENU_FAQ").'</a>'
	),
	array(
		"TEXT"=>GetMessage("KDA_EE_SHOW_CRONTAB"),
		"TITLE"=>GetMessage("KDA_EE_SHOW_CRONTAB"),
		"ONCLICK" => "EProfile.ShowCron();",
		"ICON" => "btn_green",
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();


if ($STEP < 2)
{
	/*$oProfile = new CKDAExportProfile('highload');
	$arProfiles = $oProfile->GetProcessedProfiles();
	if(!empty($arProfiles))
	{
		$message = '';
		foreach($arProfiles as $k=>$v)
		{
			$message .= '<div class="kda-proccess-item">'.GetMessage("KDA_EE_PROCESSED_PROFILE").': '.$v['name'].' ('.GetMessage("KDA_EE_PROCESSED_PERCENT_LOADED").' '.$v['percent'].'%). &nbsp; &nbsp; &nbsp; &nbsp; <a href="javascript:void(0)" onclick="EProfile.ContinueProccess(this, '.$v['key'].')">'.GetMessage("KDA_EE_PROCESSED_CONTINUE").'</a> &nbsp; <a href="javascript:void(0)" onclick="EProfile.RemoveProccess(this, '.$v['key'].')">'.GetMessage("KDA_EE_PROCESSED_DELETE").'</a></div>';
		}
		CAdminMessage::ShowMessage(array(
			'TYPE' => 'error',
			'MESSAGE' => GetMessage("KDA_EE_PROCESSED_TITLE"),
			'DETAILS' => $message,
			'HTML' => true
		));
	}*/
}

if(strlen($strErrorProfile.$strError) > 0)
{
	CAdminMessage::ShowMessage($strErrorProfile.$strError);
}
?>

<form method="POST" action="<?echo $sDocPath ?>?<?if(strlen($PROFILE_ID) > 0){echo 'PROFILE_ID='.htmlspecialcharsbx($PROFILE_ID).'&';}?>lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload" class="kda-ee-s1-form">

<?
$arProfile = (strlen($PROFILE_ID) > 0 ? $oProfile->GetFieldsByID($PROFILE_ID) : array());
$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("KDA_EE_TAB1") ,
		"ICON" => "iblock",
		"TITLE" => GetMessage("KDA_EE_TAB1_ALT"),
	) ,
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("KDA_EE_TAB2") ,
		"ICON" => "iblock",
		"TITLE" => sprintf(GetMessage("KDA_EE_TAB2_ALT"), (isset($arProfile['NAME']) ? $arProfile['NAME'] : '')),
	) ,
	array(
		"DIV" => "edit3",
		"TAB" => GetMessage("KDA_EE_TAB3") ,
		"ICON" => "iblock",
		"TITLE" => sprintf(GetMessage("KDA_EE_TAB3_ALT"), (isset($arProfile['NAME']) ? $arProfile['NAME'] : '')),
	) ,
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();
?>

<?$tabControl->BeginNextTab();
if ($STEP == 1)
{
	$fl = new CKDAEEFieldList($SETTINGS_DEFAULT);
	$oProfile = new CKDAExportProfile('highload');
?>

	<tr class="heading">
		<td colspan="2" class="kda-ee-profile-header">
			<div>
				<?echo GetMessage("KDA_EE_PROFILE_HEADER"); ?>
				<a href="javascript:void(0)" onclick="EHelper.ShowHelp();" title="<?echo GetMessage("KDA_EE_MENU_HELP"); ?>" class="kda-ee-help-link"></a>
			</div>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("KDA_EE_PROFILE"); ?>:</td>
		<td>
			<?$oProfile->ShowProfileList('PROFILE_ID', $PROFILE_ID, (bool)($MODULE_RIGHT > 'T'));?>
			
			<?if(strlen($PROFILE_ID) > 0 && $PROFILE_ID!='new' && $MODULE_RIGHT > 'T'){?>
				<span class="kda-ee-edit-btns">
					<a href="javascript:void(0)" class="adm-table-btn-edit" onclick="EProfile.ShowRename();" title="<?echo GetMessage("KDA_EE_RENAME_PROFILE");?>" id="action_edit_button"></a>
					<a href="javascript:void(0);" class="adm-table-btn-copy" onclick="EProfile.Copy();" title="<?echo GetMessage("KDA_EE_COPY_PROFILE");?>" id="action_copy_button"></a>
					<a href="javascript:void(0);" class="adm-table-btn-delete" onclick="if(confirm('<?echo GetMessage("KDA_EE_DELETE_PROFILE_CONFIRM");?>')){EProfile.Delete();}" title="<?echo GetMessage("KDA_EE_DELETE_PROFILE");?>" id="action_delete_button"></a>
				</span>
			<?}?>
		</td>
	</tr>
	
	<tr id="new_profile_name">
		<td><?echo GetMessage("KDA_EE_NEW_PROFILE_NAME"); ?>:</td>
		<td>
			<input type="text" name="NEW_PROFILE_NAME" value="<?echo htmlspecialcharsbx($NEW_PROFILE_NAME)?>" size="50">
		</td>
	</tr>

	<?
	if(strlen($PROFILE_ID) > 0)
	{
		$isDescription = (bool)(strlen(trim($SETTINGS_DEFAULT['PROFILE_DESCRIPTION'])) > 0);
		if(!$isDescription)
		{
			if($MODULE_RIGHT > 'T')
			{
	?>
		<tr>
			<td class="kda-ee-settings-margin-container" colspan="2" align="center">
				<a class="kda-ee-grey" href="javascript:void(0)" onclick="ESettings.AddProfileDescription(this)"><?echo GetMessage("KDA_EE_PROFILE_DESCRIPTION_ADD");?></a>
			</td>
		</tr>
		<?
			}
		}
		?>
		<tr <?if(!$isDescription){echo ' style="display: none;"';}?>>
			<td><?echo GetMessage("KDA_EE_PROFILE_DESCRIPTION"); ?>:</td>
			<td>
				<textarea name="SETTINGS_DEFAULT[PROFILE_DESCRIPTION]" cols="50" rows="3"<?if($MODULE_RIGHT <= 'T'){echo ' disabled';}?>><?echo htmlspecialcharsbx($SETTINGS_DEFAULT['PROFILE_DESCRIPTION'])?></textarea>
			</td>
		</tr>
		
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_EE_DEFAULT_SETTINGS"); ?></td>
		</tr>
		
		<tr>
			<?
			//$xlsxShow = (bool)(class_exists('XMLWriter'));
			$xlsxShow = true;
			?>
			<td width="40%"><?echo GetMessage("KDA_EE_FILE_EXT"); ?></td>
			<td width="60%" class="kda-ie-file-choose">
				<select name="SETTINGS_DEFAULT[FILE_EXTENSION]" id="kda-ee-file-extension"<?if($MODULE_RIGHT <= 'T'){echo ' disabled';}?>>
					<?if($xlsxShow){?>
						<option value="xlsx" <?if($SETTINGS_DEFAULT['FILE_EXTENSION']=='xlsx'){echo 'selected';}?>>.XLSX</option>
					<?}?>
					<option value="xls" <?if($SETTINGS_DEFAULT['FILE_EXTENSION']=='xls'){echo 'selected';}?>>.XLS</option>
					<option value="csv" <?if($SETTINGS_DEFAULT['FILE_EXTENSION']=='csv'){echo 'selected';}?>>.CSV</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_EE_FILE_PATH"); ?>:  </td>
			<td>
				<?
				$path = $SETTINGS_DEFAULT['FILE_PATH'];
				if(!$path)
				{
					$defaultExt = ($xlsxShow ? 'xlsx' : 'xls');
					$ext = ($SETTINGS_DEFAULT['FILE_EXTENSION'] ? $SETTINGS_DEFAULT['FILE_EXTENSION'] : $defaultExt);
					while(($path = '/upload/export_'.mt_rand().'.'.$ext) && file_exists($_SERVER['DOCUMENT_ROOT'].$path)){}
				}
				?>
				<input type="text" name="SETTINGS_DEFAULT[FILE_PATH]" id="kda-ee-file-path" value="<?echo htmlspecialcharsbx($path); ?>" size="55"<?if($MODULE_RIGHT <= 'T'){echo ' disabled';}?>>
			</td>
		</tr>

		<tr>
			<td><?echo GetMessage("KDA_EE_HIGHLOADBLOCK"); ?></td>
			<td>
				<select id="SETTINGS_DEFAULT[HIGHLOADBLOCK_ID]" name="SETTINGS_DEFAULT[HIGHLOADBLOCK_ID]" class="adm-detail-iblock-list"<?if($MODULE_RIGHT <= 'T'){echo ' disabled';}?>>
					<option value=""><?echo GetMessage("KDA_EE_CHOOSE_HIGHLOADBLOCK"); ?></option>
					<?
					$arHighloadBlock = $fl->GetHighloadBlocks();
					foreach($arHighloadBlock as $arBlock)
					{
						?><option value="<?echo $arBlock['ID']?>" <?if($SETTINGS_DEFAULT['HIGHLOADBLOCK_ID']==$arBlock['ID']){echo 'selected';}?>><?echo $arBlock['NAME']; ?></option><?
					}
					?>
				</select>
			</td>
		</tr>
		
		<?
		if($MODULE_RIGHT > 'T')
		{
		?>
		<tr>
			<td><?echo GetMessage("KDA_EE_ELEMENT_MULTIPLE_SEPARATOR"); ?>:</td>
			<td>
				<input type="text" name="SETTINGS_DEFAULT[ELEMENT_MULTIPLE_SEPARATOR]" size="3" value="<?echo ($SETTINGS_DEFAULT['ELEMENT_MULTIPLE_SEPARATOR'] ? htmlspecialcharsbx($SETTINGS_DEFAULT['ELEMENT_MULTIPLE_SEPARATOR']) : ';'); ?>">
			</td>
		</tr>
		
		<tr class="heading" id="csv_settings_block">
			<td colspan="2"><?echo GetMessage("KDA_EE_SETTINGS_CSV"); ?></td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_EE_CSV_SEPARATOR"); ?>:</td>
			<td>
				<select name="SETTINGS_DEFAULT[CSV_SEPARATOR]">
					<option value=";" <?if($SETTINGS_DEFAULT['CSV_SEPARATOR']==';'){echo 'selected';}?>><?echo GetMessage("KDA_EE_CSV_SEPARATOR_SEMICOLON"); ?></option>
					<option value="," <?if($SETTINGS_DEFAULT['CSV_SEPARATOR']==','){echo 'selected';}?>><?echo GetMessage("KDA_EE_CSV_SEPARATOR_COMMA"); ?></option>
					<option value="\t" <?if($SETTINGS_DEFAULT['CSV_SEPARATOR']=='\t'){echo 'selected';}?>><?echo GetMessage("KDA_EE_CSV_SEPARATOR_TAB"); ?></option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_EE_CSV_ENCODING"); ?>:</td>
			<td>
				<select name="SETTINGS_DEFAULT[CSV_ENCODING]">
					<option value="UTF-8" <?if($SETTINGS_DEFAULT['CSV_ENCODING']=='UTF-8'){echo 'selected';}?>>UTF-8 (<?echo GetMessage("KDA_EE_CSV_ENCODING_WITH")?> BOM)</option>
					<option value="UTF-8_WO_BOM" <?if($SETTINGS_DEFAULT['CSV_ENCODING']=='UTF-8_WO_BOM'){echo 'selected';}?>>UTF-8 (<?echo GetMessage("KDA_EE_CSV_ENCODING_WITHOUT")?> BOM)</option>
					<option value="CP1251" <?if($SETTINGS_DEFAULT['CSV_ENCODING']=='CP1251'){echo 'selected';}?>>CP1251</option>
				</select>
			</td>
		</tr>
		
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_EE_SETTINGS_DISPLAY"); ?></td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN"); ?>:</td>
			<td>
				<select name="SETTINGS_DEFAULT[DISPLAY_TEXT_ALIGN]">
					<option value="LEFT" <?if($SETTINGS_DEFAULT['DISPLAY_TEXT_ALIGN']=='LEFT'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN_LEFT"); ?></option>
					<option value="CENTER" <?if($SETTINGS_DEFAULT['DISPLAY_TEXT_ALIGN']=='CENTER'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN_CENTER"); ?></option>
					<option value="RIGHT" <?if($SETTINGS_DEFAULT['DISPLAY_TEXT_ALIGN']=='RIGHT'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_TEXT_ALIGN_RIGHT"); ?></option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN"); ?>:</td>
			<td>
				<select name="SETTINGS_DEFAULT[DISPLAY_VERTICAL_ALIGN]">
					<option value="TOP" <?if($SETTINGS_DEFAULT['DISPLAY_VERTICAL_ALIGN']=='TOP'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN_TOP"); ?></option>
					<option value="CENTER" <?if($SETTINGS_DEFAULT['DISPLAY_VERTICAL_ALIGN']=='CENTER'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN_CENTER"); ?></option>
					<option value="BOTTOM" <?if($SETTINGS_DEFAULT['DISPLAY_VERTICAL_ALIGN']=='BOTTOM'){echo 'selected';}?>><?echo GetMessage("KDA_EE_DISPLAY_VERTICAL_ALIGN_BOTTOM"); ?></option>
				</select>
			</td>
		</tr>
		
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_EE_OTHER_SETTINGS"); ?> <a href="javascript:void(0)" onclick="EProfile.ToggleAdditionalSettings(this)" class="kda-head-more show"><?echo GetMessage("KDA_EE_SETTINGS_ADDITONAL_SHOW_HIDE"); ?></a></td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_EE_OTHER_USE_NEW_FILTER");?>: <span id="hint_USE_NEW_FILTER"></span><script>BX.hint_replace(BX('hint_USE_NEW_FILTER'), '<?echo GetMessage("KDA_EE_OTHER_USE_NEW_FILTER_HINT"); ?>');</script></td>
			<td>
				<input type="hidden" name="SETTINGS_DEFAULT[USE_NEW_FILTER]" value="N">
				<input type="checkbox" name="SETTINGS_DEFAULT[USE_NEW_FILTER]" value="Y" <?=htmlspecialcharsbx($SETTINGS_DEFAULT['USE_NEW_FILTER']=='Y' || ($PROFILE_ID=='new' && $SETTINGS_DEFAULT['USE_NEW_FILTER']!='Y') ? 'checked' : '')?>>
			</td>
		</tr>
	<?
		}
	}
}
$tabControl->EndTab();
?>

<?$tabControl->BeginNextTab();
if ($STEP == 2)
{
?>
	
	<tr>
		<td colspan="2" id="kda-ee-sheet-list">
			<?
			$arKeys = array(0);
			if(is_array($SETTINGS['LIST_NAME']) && count($SETTINGS['LIST_NAME']) > 0)
			{
				$arKeys = array_keys($SETTINGS['LIST_NAME']);
			}
			
			$ind = 0;
			foreach($arKeys as $list)
			{
				?>
				<div class="kda-ee-sheet-wrap<?if($ind > 0){echo ' withmargin';} if($MODULE_RIGHT <= 'T'){echo ' minrigths';}?>">
					<?
					if($SETTINGS_DEFAULT['USE_NEW_FILTER']=='Y')
					{
						/* new filter */
						if(!is_array($SETTINGS)) $SETTINGS = array();
						$iblockId = $SETTINGS_DEFAULT['HIGHLOADBLOCK_ID'];						
						$changeIblockId = (bool)($SETTINGS['CHANGE_HLBL_ID'][$list]=='Y');
						if($changeIblockId && $SETTINGS['LIST_HLBL_ID'][$list])
						{
							$iblockId = $SETTINGS['LIST_HLBL_ID'][$list];
						}
						?>
						<?$fl = new CKDAEEFieldList($SETTINGS_DEFAULT);?>
						
						<div class="kda-ee-sheet-cfilter-wrap">
							<div class="kda-ee-sheet-cfilter-title-wrap">
								<span class="kda-ee-sheet-cfilter-title kda-ee-sheet-cfilter-title-active" onclick="/*EProfile.ChangeFilter(this)*/"><?echo GetMessage('KDA_EE_PF_FILTER')?></span>
							</div>
							<div class="kda-ee-sheet-cfilter-wrap-inner">
								<?								
								$hlFilter = new CKDAEEFilter($iblockId, 'hl');
								$hlFilter->ShowFilterBlock('kda-ee-sheet-hlfilter-'.$list, (isset($SETTINGS['HLFILTER'][$list]) ? $SETTINGS['HLFILTER'][$list] : array()), $fl);
								?>
							
								<div class="kda-ee-sheet-cfilter-bottom">
									<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="set_filter" value="<?echo GetMessage('KDA_EE_PF_FILTER_APPLY')?>" onclick="return EList.ApplyFilter(this);"></span>
									<?/*?><span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="del_filter" value="Cancel" onclick="return EList.DeleteFilter(this);"></span><?*/?>
								</div>
							</div>
						</div>
						<?
						/*/new filter */
					}
					else
					{
						$filterId = 'kda_exportexcel_highload_'.$PROFILE_ID.'_'.$list;
						CKDAExportUtils::ShowFilterHighload($filterId, $list, $SETTINGS, $SETTINGS_DEFAULT);
					}
					$showCodes = ($SETTINGS_DEFAULT['EXPORT_FIELD_CODES']=='Y' ? 1 : 0);
					?>
					<div class="kda-ee-sheet" 
						id="kda-ee-sheet-<?echo $list;?>" 
						data-sheet-index="<?echo $list;?>" 
						data-show-field-codes="<?echo $showCodes;?>">
					</div>
					<?
					if($MODULE_RIGHT > 'T')
					{
					?>
					<div class="kda-ee-new-list-wrap" style="display: none;">
						<input type="button" value="<?echo GetMessage("KDA_EE_ADD_NEW_LIST"); ?>" title="<?echo GetMessage("KDA_EE_ADD_NEW_LIST_HINT");?>">
					</div>
					<?
					}
					?>
				</div>
				<?
				$ind++;
			}
			?>
		</td>
	</tr>
	
	<?
}
$tabControl->EndTab();
?>


<?$tabControl->BeginNextTab();
if ($STEP == 3)
{
?>
	<tr>
		<td id="resblock" class="kda-ee-result">
		 <table width="100%"><tr><td width="50%">
			<div id="progressbar"><span class="pline"></span><span class="presult load"><b>0%</b><span 
				data-prefix="<?echo GetMessage("KDA_EE_READ_LINES"); ?>" 
				data-export="<?echo GetMessage("KDA_EE_STATUS_EXPORT"); ?>" 
			><?echo GetMessage("KDA_EE_EXPORT_INIT"); ?></span></span></div>

			<div id="block_error_import" style="display: none;">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "ERROR",
					"MESSAGE" => GetMessage("KDA_EE_IMPORT_ERROR_CONNECT"),
					/*"DETAILS" => '<div><a href="javascript:void(0)" onclick="EProfile.ContinueProccess(this, '.$PROFILE_ID.');">'.GetMessage("KDA_EE_PROCESSED_CONTINUE").'</a><br><br>'.sprintf(GetMessage("KDA_EE_IMPORT_ERROR_CONNECT_COMMENT"), '/bitrix/admin/settings.php?lang=ru&mid='.$moduleId.'&mid_menu=1').'</div>',*/
					"DETAILS" => '<div><a href="javascript:void(0)" onclick="EProfile.ContinueProccess(this, '.$PROFILE_ID.');">'.GetMessage("KDA_EE_PROCESSED_CONTINUE").'</a></div>',
					"HTML" => true,
				))?>
			</div>
			
			<div id="block_error" style="display: none;">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "ERROR",
					"MESSAGE" => GetMessage("KDA_EE_IMPORT_ERROR"),
					"DETAILS" => '<div id="res_error"></div>',
					"HTML" => true,
				))?>
			</div>
		 </td><td>
			<div class="detail_status">
				<?
				$outputFile = CKDAExportUtils::PrepareExportFileName($SETTINGS_DEFAULT['FILE_PATH']);
				if(strpos($outputFile, $_SERVER['DOCUMENT_ROOT'])===0)
				{
					$outputFile = substr($outputFile, strlen($_SERVER['DOCUMENT_ROOT']));
				}
				echo CAdminMessage::ShowMessage(array(
					"TYPE" => "PROGRESS",
					"MESSAGE" => '<!--<div id="res_continue">'.GetMessage("KDA_EE_AUTO_REFRESH_CONTINUE").'</div><div id="res_finish" style="display: none;">'.GetMessage("KDA_EE_SUCCESS").'</div>-->',
					"DETAILS" =>

					GetMessage("KDA_EE_SU_ALL").' <b id="total_read_line">0</b><br>'.
					/*.GetMessage("KDA_EE_SU_ELEMENT_ADDED").' <b id="element_added_line">0</b><br>'.
					(!empty($SETTINGS_DEFAULT['ELEMENT_UID_SKU']) ? (GetMessage("KDA_EE_SU_SKU_ADDED").' <b id="sku_added_line">0</b><br>') : '').
					GetMessage("KDA_EE_SU_SECTION_ADDED").' <b id="section_added_line">0</b><br>'.*/
					' <span id="kda_ee_ready_file" style="visibility: hidden;"><br>'.GetMessage("KDA_EE_READY_FILE_LINK").' <br><a href="'.htmlspecialcharsex($outputFile).'?hash='.md5(mt_rand()).'">'.$outputFile.'</a> '.($GLOBALS['USER']->CanDoFileOperation('fm_download_file', array('', htmlspecialcharsbx($outputFile))) ? '<br><br><a href="/bitrix/admin/fileman_file_download.php?path='.htmlspecialcharsbx($outputFile).'&lang='.LANGUAGE_ID.'&hash='.md5(mt_rand()).'">'.GetMessage("KDA_EE_DOWNLOAD_FILE").'</a>' : '').'</span><br>',
					"HTML" => true,
				));?>
			</div>
		 </td></tr></table>
		</td>
	</tr>
<?
}
$tabControl->EndTab();
?>

<?$tabControl->Buttons();
?>


<?echo bitrix_sessid_post(); ?>
<?
if($STEP > 1)
{
	if(strlen($PROFILE_ID) > 0)
	{
		?><input type="hidden" name="PROFILE_ID" value="<?echo htmlspecialcharsbx($PROFILE_ID) ?>"><?
	}
	else
	{
		foreach($SETTINGS_DEFAULT as $k=>$v)
		{
			?><input type="hidden" name="SETTINGS_DEFAULT[<?echo $k?>]" value="<?echo htmlspecialcharsbx($v) ?>"><?
		}
	}
}
?>


<?
if($STEP == 2){ ?>
	<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("KDA_EE_BACK"); ?>">
	<?
	if($MODULE_RIGHT > 'T')
	{
	?>
		<input type="submit" name="saveConfigButton" value="<?echo GetMessage("KDA_EE_SAVE_CONFIGURATION"); ?>" style="float: right;">
	<?
	}
}

if($STEP < 3)
{
?>
	<input type="hidden" name="STEP" value="<?echo $STEP + 1; ?>">
	<input type="submit" value="<?echo ($STEP == 2) ? GetMessage("KDA_EE_NEXT_STEP_F") : GetMessage("KDA_EE_NEXT_STEP"); ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">
<? 
}
else
{
?>
	<input type="hidden" name="STEP" value="1">
	<input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage("KDA_EE_2_1_STEP"); ?>" class="adm-btn-save">
<?
}
?>

<?$tabControl->End();
?>

</form>

<script language="JavaScript">
<?if ($STEP < 2): 
?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?elseif ($STEP == 2): 

?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");

<?elseif ($STEP > 2): ?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");

<?
$arPost = $_POST;
unset($arPost['SETTINGS']);
if($_POST['PROCESS_CONTINUE']=='Y'){
	$oProfile = new CKDAExportProfile('highload');
?>
	EImport.Init(<?=\KdaIE\Utils::PhpToJSObject($arPost);?>, <?=\KdaIE\Utils::PhpToJSObject($oProfile->GetProccessParams($_POST['PROFILE_ID']));?>);
<?}else{?>
	EImport.Init(<?=\KdaIE\Utils::PhpToJSObject($arPost);?>);
<?}?>
<?endif; ?>
//-->
</script>

<?
require ($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
?>
