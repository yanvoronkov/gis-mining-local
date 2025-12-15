<?
if (!CModule::IncludeModule("iblock"))
	return false;

IncludeModuleLangFile(__FILE__);
$moduleId = 'esol.importexportexcel';
$moduleIdUl = 'esol_importexportexcel';
$moduleFileImportPrefix = 'esol_import_excel';
$moduleFileExportPrefix = 'esol_export_excel';

$aMenu = array();

global $USER;
$bUserIsAdmin = $USER->IsAdmin();

$bHasWRight = false;
$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array("MIN_PERMISSION" => "U"));
if($arIBlock = $rsIBlocks->Fetch())
{
	$bHasWRight = true;
}

if($APPLICATION->GetGroupRight($moduleId) <= "R")
{
	$bHasWRight = false;
}

if($bUserIsAdmin || $bHasWRight)
{
	if(is_callable(array('\Bitrix\Main\Context', 'getCurrent'))) $requestUri = \Bitrix\Main\Context::getCurrent()->getRequest()->getRequestUri();
	else $requestUri = $_SERVER['REQUEST_URI'];
	
	$showProfiles = (bool)(\Bitrix\Main\Config\Option::get($moduleId, 'SHOW_PROFILES_IN_MAIN_MENU', 'N')=='Y');

	$itemsId = "menu_".$moduleIdUl."_import";
	$itemUrlFile = $moduleFileImportPrefix.".php";
	$arProfilesMenu = array();
	if($showProfiles && \Bitrix\Main\Loader::IncludeModule($moduleId) && ($_REQUEST['admin_mnu_menu_id']==$itemsId || (strpos($requestUri, '/'.$itemUrlFile)!==false /*&& array_key_exists('PROFILE_ID', $_REQUEST) && strlen($_REQUEST) > 0*/)))
	{
		$oProfile = new CKDAImportProfile();
		$arProfiles = $oProfile->GetProfileListForMenu(array_key_exists('PROFILE_ID', $_REQUEST) ? $_REQUEST['PROFILE_ID'] : '');
		foreach($arProfiles as $groupId=>$arGroup)
		{
			if(strlen($arGroup['NAME']) > 0)
			{
				$arSubProfilesMenu = array();
				foreach($arGroup['LIST'] as $profileId=>$profileName)
				{
					$arSubProfilesMenu[] = array(
						"text" => '['.$profileId.'] '.$profileName,
						"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
						"title" => '['.$profileId.'] '.$profileName,
						"module_id" => $moduleId,
						"items_id" => $itemsId.'_'.$profileId,
						"sort" => 100,
						"section" => $moduleIdUl,
					);
				}
				$arProfilesMenu[] = array(
					"text" => $arGroup['NAME'],
					//"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
					"title" => $arGroup['NAME'],
					"module_id" => $moduleId,
					"items_id" => $itemsId.'_'.$profileId,
					"sort" => 100,
					"section" => $moduleIdUl,
					"icon" => "fileman_menu_icon_sections",
					"items" => $arSubProfilesMenu,
				);		
			}
			else
			{
				foreach($arGroup['LIST'] as $profileId=>$profileName)
				{
					$arProfilesMenu[] = array(
						"text" => '['.$profileId.'] '.$profileName,
						"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
						"title" => '['.$profileId.'] '.$profileName,
						"module_id" => $moduleId,
						"items_id" => $itemsId.'_'.$profileId,
						"sort" => 100,
						"section" => $moduleIdUl,
					);
				}	
			}
		}
	}

	$aSubMenu = array();
	$aSubMenu[] = array(
		"text" => GetMessage("KDA_MENU_IMPORT_TITLE"),
		"url" => $itemUrlFile."?lang=".LANGUAGE_ID,
		"more_url" => array(
			$moduleFileImportPrefix."_profile_list.php", 
			$moduleFileImportPrefix."_rollback.php"
		),
		"title" => GetMessage("KDA_MENU_IMPORT_TITLE"),
		"module_id" => $moduleId,
		"items_id" => $itemsId,
		"sort" => 100,
		"section" => $moduleIdUl."_import",
		"dynamic" => $showProfiles,
		"items" => $arProfilesMenu,
	);
	
	if(CModule::IncludeModule('highloadblock'))
	{
		$itemsId = "menu_".$moduleIdUl."_import_highload";
		$itemUrlFile = $moduleFileImportPrefix."_highload.php";
		$arProfilesMenu = array();
		if($showProfiles && \Bitrix\Main\Loader::IncludeModule($moduleId) && ($_REQUEST['admin_mnu_menu_id']==$itemsId || (strpos($requestUri, '/'.$itemUrlFile)!==false /*&& array_key_exists('PROFILE_ID', $_REQUEST) && strlen($_REQUEST) > 0*/)))
		{
			$oProfile = new CKDAImportProfile('highload');
			$arProfiles = $oProfile->GetProfileListForMenu(array_key_exists('PROFILE_ID', $_REQUEST) ? $_REQUEST['PROFILE_ID'] : '');
			foreach($arProfiles as $groupId=>$arGroup)
			{
				if(strlen($arGroup['NAME']) > 0)
				{
					$arSubProfilesMenu = array();
					foreach($arGroup['LIST'] as $profileId=>$profileName)
					{
						$arSubProfilesMenu[] = array(
							"text" => '['.$profileId.'] '.$profileName,
							"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
							"title" => '['.$profileId.'] '.$profileName,
							"module_id" => $moduleId,
							"items_id" => $itemsId.'_'.$profileId,
							"sort" => 100,
							"section" => $moduleIdUl,
						);
					}
					$arProfilesMenu[] = array(
						"text" => $arGroup['NAME'],
						//"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
						"title" => $arGroup['NAME'],
						"module_id" => $moduleId,
						"items_id" => $itemsId.'_'.$profileId,
						"sort" => 100,
						"section" => $moduleIdUl,
						"icon" => "fileman_menu_icon_sections",
						"items" => $arSubProfilesMenu,
					);		
				}
				else
				{
					foreach($arGroup['LIST'] as $profileId=>$profileName)
					{
						$arProfilesMenu[] = array(
							"text" => '['.$profileId.'] '.$profileName,
							"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
							"title" => '['.$profileId.'] '.$profileName,
							"module_id" => $moduleId,
							"items_id" => $itemsId.'_'.$profileId,
							"sort" => 100,
							"section" => $moduleIdUl,
						);
					}	
				}
			}
		}
		
		$aSubMenu[] = array(
			"text" => GetMessage("KDA_MENU_IMPORT_TITLE_HIGHLOAD"),
			"url" => $itemUrlFile."?lang=".LANGUAGE_ID,
			"title" => GetMessage("KDA_MENU_IMPORT_TITLE_HIGHLOAD"),
			"module_id" => $moduleId,
			"items_id" => $itemsId,
			"sort" => 200,
			"section" => $moduleIdUl."_import",
			"dynamic" => $showProfiles,
			"items" => $arProfilesMenu,
		);			
	}
	
	$aSubMenu[] = array(
		"text" => GetMessage("KDA_MENU_IMPORT_TITLE_STAT_WRAP"),
		"title" => GetMessage("KDA_MENU_IMPORT_TITLE_STAT_WRAP"),
		"module_id" => $moduleId,
		"items_id" => "menu_".$moduleIdUl,
		"sort" => 300,
		"section" => $moduleIdUl."_import",
		'items' => array(
			array(
				"text" => GetMessage("KDA_MENU_IMPORT_TITLE_STAT"),
				"url" => "esol_import_excel_event_stat.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("KDA_MENU_IMPORT_TITLE_STAT"),
				"module_id" => $moduleId,
				"items_id" => "menu_".$moduleIdUl,
				"sort" => 100,
				"section" => $moduleIdUl."_import",
			),
			array(
				"text" => GetMessage("KDA_MENU_IMPORT_TITLE_STAT_DETAIL"),
				"url" => "esol_import_excel_event_log.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("KDA_MENU_IMPORT_TITLE_STAT_DETAIL"),
				"module_id" => $moduleId,
				"items_id" => "menu_".$moduleIdUl,
				"sort" => 200,
				"section" => $moduleIdUl."_import",
			)
		)
	);
	
	$aMenu[] = array(
		"parent_menu" => "global_menu_content",
		"section" => $moduleIdUl."_import",
		"sort" => 1400,
		"text" => GetMessage("KDA_MENU_IMPORT_TITLE_PARENT"),
		"title" => GetMessage("KDA_MENU_IMPORT_TITLE_PARENT"),
		"icon" => "esol_importexportexcel_menu_import_icon",
		"items_id" => "menu_".$moduleIdUl."_parent_import",
		"module_id" => $moduleId,
		"items" => $aSubMenu,
	);
	
	
	$itemsId = "menu_".$moduleIdUl."_export";
	$itemUrlFile = $moduleFileExportPrefix.".php";
	$arProfilesMenu = array();
	if($showProfiles && \Bitrix\Main\Loader::IncludeModule($moduleId) && ($_REQUEST['admin_mnu_menu_id']==$itemsId || (strpos($requestUri, '/'.$itemUrlFile)!==false /*&& array_key_exists('PROFILE_ID', $_REQUEST) && strlen($_REQUEST) > 0*/)))
	{
		$oProfile = new CKDAExportProfile();
		$arProfiles = $oProfile->GetProfileListForMenu(array_key_exists('PROFILE_ID', $_REQUEST) ? $_REQUEST['PROFILE_ID'] : '');
		foreach($arProfiles as $groupId=>$arGroup)
		{
			if(strlen($arGroup['NAME']) > 0)
			{
				$arSubProfilesMenu = array();
				foreach($arGroup['LIST'] as $profileId=>$profileName)
				{
					$arSubProfilesMenu[] = array(
						"text" => '['.$profileId.'] '.$profileName,
						"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
						"title" => '['.$profileId.'] '.$profileName,
						"module_id" => $moduleId,
						"items_id" => $itemsId.'_'.$profileId,
						"sort" => 100,
						"section" => $moduleIdUl,
					);
				}
				$arProfilesMenu[] = array(
					"text" => $arGroup['NAME'],
					//"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
					"title" => $arGroup['NAME'],
					"module_id" => $moduleId,
					"items_id" => $itemsId.'_'.$profileId,
					"sort" => 100,
					"section" => $moduleIdUl,
					"icon" => "fileman_menu_icon_sections",
					"items" => $arSubProfilesMenu,
				);		
			}
			else
			{
				foreach($arGroup['LIST'] as $profileId=>$profileName)
				{
					$arProfilesMenu[] = array(
						"text" => '['.$profileId.'] '.$profileName,
						"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
						"title" => '['.$profileId.'] '.$profileName,
						"module_id" => $moduleId,
						"items_id" => $itemsId.'_'.$profileId,
						"sort" => 100,
						"section" => $moduleIdUl,
					);
				}	
			}
		}
	}
	
	$aSubMenu = array();
	$aSubMenu[] = array(
		"text" => GetMessage("KDA_MENU_EXPORT_TITLE"),
		"url" => $itemUrlFile."?lang=".LANGUAGE_ID,
		"more_url" => array($moduleFileExportPrefix."_profile_list.php"),
		"title" => GetMessage("KDA_MENU_EXPORT_TITLE"),
		"module_id" => $moduleId,
		"items_id" => $itemsId,
		"sort" => 100,
		"section" => $moduleIdUl."_export",
		"dynamic" => $showProfiles,
		"items" => $arProfilesMenu,
	);
	
	if(CModule::IncludeModule('highloadblock'))
	{
		$itemsId = "menu_".$moduleIdUl."_export_highload";
		$itemUrlFile = $moduleFileExportPrefix."_highload.php";
		$arProfilesMenu = array();
		if($showProfiles && \Bitrix\Main\Loader::IncludeModule($moduleId) && ($_REQUEST['admin_mnu_menu_id']==$itemsId || (strpos($requestUri, '/'.$itemUrlFile)!==false /*&& array_key_exists('PROFILE_ID', $_REQUEST) && strlen($_REQUEST) > 0*/)))
		{
			$oProfile = new CKDAExportProfile('highload');
			$arProfiles = $oProfile->GetProfileListForMenu(array_key_exists('PROFILE_ID', $_REQUEST) ? $_REQUEST['PROFILE_ID'] : '');
			foreach($arProfiles as $groupId=>$arGroup)
			{
				if(strlen($arGroup['NAME']) > 0)
				{
					$arSubProfilesMenu = array();
					foreach($arGroup['LIST'] as $profileId=>$profileName)
					{
						$arSubProfilesMenu[] = array(
							"text" => '['.$profileId.'] '.$profileName,
							"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
							"title" => '['.$profileId.'] '.$profileName,
							"module_id" => $moduleId,
							"items_id" => $itemsId.'_'.$profileId,
							"sort" => 100,
							"section" => $moduleIdUl,
						);
					}
					$arProfilesMenu[] = array(
						"text" => $arGroup['NAME'],
						//"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
						"title" => $arGroup['NAME'],
						"module_id" => $moduleId,
						"items_id" => $itemsId.'_'.$profileId,
						"sort" => 100,
						"section" => $moduleIdUl,
						"icon" => "fileman_menu_icon_sections",
						"items" => $arSubProfilesMenu,
					);		
				}
				else
				{
					foreach($arGroup['LIST'] as $profileId=>$profileName)
					{
						$arProfilesMenu[] = array(
							"text" => '['.$profileId.'] '.$profileName,
							"url" => $itemUrlFile."?lang=".LANGUAGE_ID."&PROFILE_ID=".$profileId,
							"title" => '['.$profileId.'] '.$profileName,
							"module_id" => $moduleId,
							"items_id" => $itemsId.'_'.$profileId,
							"sort" => 100,
							"section" => $moduleIdUl,
						);
					}	
				}
			}
		}
		
		$aSubMenu[] = array(
			"text" => GetMessage("KDA_MENU_EXPORT_TITLE_HIGHLOAD"),
			"url" => $itemUrlFile."?lang=".LANGUAGE_ID,
			"title" => GetMessage("KDA_MENU_EXPORT_TITLE_HIGHLOAD"),
			"module_id" => $moduleId,
			"items_id" => $itemsId,
			"sort" => 200,
			"section" => $moduleIdUl."_export",
			"dynamic" => $showProfiles,
			"items" => $arProfilesMenu,
		);			
	}
	
	$aMenu[] = array(
		"parent_menu" => "global_menu_content",
		"section" => $moduleIdUl."_export",
		"sort" => 1401,
		"text" => GetMessage("KDA_MENU_EXPORT_TITLE_PARENT"),
		"title" => GetMessage("KDA_MENU_EXPORT_TITLE_PARENT"),
		"icon" => "esol_importexportexcel_menu_import_icon",
		"items_id" => "menu_".$moduleIdUl."_parent_export",
		"module_id" => $moduleId,
		"items" => $aSubMenu,
	);
}

return $aMenu;
?>