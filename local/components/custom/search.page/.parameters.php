<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if (!CModule::IncludeModule("iblock"))
    return;

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "IBLOCK_IDS" => array(
            "PARENT" => "BASE",
            "NAME" => "ID инфоблоков для поиска",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "DEFAULT" => IBLOCK_IDS_ALL_CATALOG,
        ),
        "PAGE_RESULT_COUNT" => array(
            "PARENT" => "BASE",
            "NAME" => "Количество результатов на странице",
            "TYPE" => "STRING",
            "DEFAULT" => "20",
        ),
        "MIN_QUERY_LENGTH" => array(
            "PARENT" => "BASE",
            "NAME" => "Минимальная длина запроса",
            "TYPE" => "STRING",
            "DEFAULT" => "2",
        ),
        "CACHE_TIME" => array("DEFAULT" => 36000000),
    ),
);
