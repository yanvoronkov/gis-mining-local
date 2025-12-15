<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("kda.exportexcel") && !CModule::IncludeModule("esol.importexportexcel"))
	return;

$oProfile = new CKDAExportProfile();
$arProfiles = $oProfile->GetList();

$arComponentParameters = array(
	"GROUPS" => array(
		"FILTER_SETTINGS" => array(
			"NAME" => GetMessage("ESOL_EE_PROFILE_FILTER_SETTINGS"),
			"SORT" => 200
		),
		"ADDITIONAL_SETTINGS" => array(
			"NAME" => GetMessage("ESOL_EE_PROFILE_ADDITIONAL_SETTINGS"),
			"SORT" => 300
		),
	),
	"PARAMETERS" => array(
		"PROFILE_ID" => Array(
			"NAME" => GetMessage("ESOL_EE_PROFILE"),
			"PARENT" => "BASE",
			"TYPE" => "LIST",
			"DEFAULT" => "", 
			"VALUES" => $arProfiles,
			"ADDITIONAL_VALUES" => "N"
		),
		"SECTION_ID" => array(
			"NAME" => GetMessage("ESOL_EE_SECTION_ID"),
			"PARENT" => "FILTER_SETTINGS",
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"SECTION_CODE" => array(
			"NAME" => GetMessage("ESOL_EE_SECTION_CODE"),
			"PARENT" => "FILTER_SETTINGS",
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		'FILTER_NAME' => array(
			'PARENT' => 'FILTER_SETTINGS',
			'NAME' => GetMessage('ESOL_EE_FILTER_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		/*"REDIRECT" => array(
			"NAME" => GetMessage("ESOL_EE_REDIRECT"),
			"PARENT" => "BASE",
			"TYPE" => "CHECKBOX",
			'REFRESH' => 'Y',
			"DEFAULT" => "N",	
		),*/
		"DOWNLOAD" => array(
			"NAME" => GetMessage("ESOL_EE_DOWNLOAD"),
			"PARENT" => "ADDITIONAL_SETTINGS",
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",	
		),
		"CACHE_TIME" => Array("DEFAULT"=>"3600"),
	)
);

/*if(isset($arCurrentValues['REDIRECT']) && $arCurrentValues['REDIRECT'] === 'Y')
{
	$arComponentParameters["PARAMETERS"]["REDIRECT_URL"] = array(
		"NAME" => GetMessage("ESOL_EE_REDIRECT_URL"),
		"PARENT" => "BASE",
		"TYPE" => "STRING",
	);
}*/