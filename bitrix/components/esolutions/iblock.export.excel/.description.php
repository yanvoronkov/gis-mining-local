<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("ESOL_EE_IBLOCK_EXPORT"),
	"DESCRIPTION" => GetMessage("ESOL_EE_IBLOCK_EXPORT_DESC"),
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("ESOL_EE_IBLOCK_EXPORT_NAME")
		)
	),
);
?>