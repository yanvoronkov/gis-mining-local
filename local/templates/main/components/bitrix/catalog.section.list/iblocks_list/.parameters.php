<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
    "GROUPS" => array(
        "SETTINGS" => array(
            "NAME" => GetMessage("IBLOCK_SECTION_LIST_SETTINGS"),
            "SORT" => 100,
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "SETTINGS",
            "NAME" => GetMessage("IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "SETTINGS",
            "NAME" => GetMessage("IBLOCK_IBLOCK"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlock,
            "REFRESH" => "Y",
            "ADDITIONAL_VALUES" => "Y",
        ),
        "CACHE_TYPE" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("T_IBLOCK_DESC_CACHE_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => array(
                "A" => GetMessage("T_IBLOCK_DESC_CACHE_TYPE_AUTO"),
                "Y" => GetMessage("T_IBLOCK_DESC_CACHE_TYPE_YES"),
                "N" => GetMessage("T_IBLOCK_DESC_CACHE_TYPE_NO"),
            ),
            "DEFAULT" => "A",
        ),
        "CACHE_TIME" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("T_IBLOCK_DESC_CACHE_TIME"),
            "TYPE" => "STRING",
            "DEFAULT" => "36000000",
        ),
        "CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("T_IBLOCK_DESC_CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
    ),
);
?>
