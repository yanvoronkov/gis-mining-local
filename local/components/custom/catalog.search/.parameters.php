<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;

// Подключаем модули
Loader::includeModule('iblock');

// Получаем список всех инфоблоков типа catalog
$arIBlocks = [];
$rsIBlock = CIBlock::GetList(
    ["SORT" => "ASC"],
    ["TYPE" => "catalog", "ACTIVE" => "Y"]
);
while ($arIBlock = $rsIBlock->Fetch()) {
    $arIBlocks[$arIBlock["ID"]] = "[" . $arIBlock["ID"] . "] " . $arIBlock["NAME"];
}

$arComponentParameters = array(
    "GROUPS" => array(
        "SEARCH_SETTINGS" => array(
            "NAME" => "Настройки поиска",
        ),
        "DISPLAY_SETTINGS" => array(
            "NAME" => "Настройки отображения",
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_IDS" => array(
            "PARENT" => "SEARCH_SETTINGS",
            "NAME" => "Инфоблоки для поиска",
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arIBlocks,
            "DEFAULT" => IBLOCK_IDS_ALL_CATALOG,
            "ADDITIONAL_VALUES" => "N",
        ),
        "MIN_QUERY_LENGTH" => array(
            "PARENT" => "SEARCH_SETTINGS",
            "NAME" => "Минимальная длина запроса",
            "TYPE" => "STRING",
            "DEFAULT" => "2",
        ),
        "MAX_RESULTS" => array(
            "PARENT" => "SEARCH_SETTINGS",
            "NAME" => "Максимальное количество результатов",
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),
        "SHOW_PRICE" => array(
            "PARENT" => "DISPLAY_SETTINGS",
            "NAME" => "Показывать цену",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "PRICE_CODE" => array(
            "PARENT" => "DISPLAY_SETTINGS",
            "NAME" => "Тип цены",
            "TYPE" => "LIST",
            "VALUES" => array("BASE" => "Базовая"),
            "DEFAULT" => "BASE",
        ),
        "CACHE_TIME" => array(
            "DEFAULT" => 3600
        ),
    ),
);

