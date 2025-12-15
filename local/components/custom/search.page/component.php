<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Main\Context;

if (!Loader::includeModule("iblock")) {
    ShowError("Модуль iblock не установлен");
    return;
}

if (!Loader::includeModule("catalog")) {
    ShowError("Модуль catalog не установлен");
    return;
}

// Параметры
$arParams["IBLOCK_IDS"] = !empty($arParams["IBLOCK_IDS"]) ? $arParams["IBLOCK_IDS"] : SearchConfig::get('IBLOCK_IDS');
$arParams["PAGE_RESULT_COUNT"] = intval($arParams["PAGE_RESULT_COUNT"]) > 0 ? intval($arParams["PAGE_RESULT_COUNT"]) : SearchConfig::get('MAX_RESULTS');
$arParams["MIN_QUERY_LENGTH"] = intval($arParams["MIN_QUERY_LENGTH"]) > 0 ? intval($arParams["MIN_QUERY_LENGTH"]) : SearchConfig::get('MIN_QUERY_LENGTH');

// Получаем запрос
$request = Context::getCurrent()->getRequest();
$query = trim($request->getQuery("q"));
$arResult["SEARCH_QUERY"] = htmlspecialcharsbx($query);

// Пагинация
$nav = new \Bitrix\Main\UI\PageNavigation("nav-search");
$nav->allowAllRecords(false)
    ->setPageSize($arParams["PAGE_RESULT_COUNT"])
    ->initFromUri();

$offset = $nav->getOffset();

// Выполняем поиск
if (strlen($query) >= $arParams["MIN_QUERY_LENGTH"]) {
    // Используем SearchHelper для поиска
    // Проверяем наличие класса
    if (!class_exists('SearchHelper')) {
        ShowError('SearchHelper не найден');
        return;
    }

    $searchResult = SearchHelper::searchProducts($query, [
        'IBLOCK_IDS' => $arParams["IBLOCK_IDS"],
        'MIN_LENGTH' => $arParams["MIN_QUERY_LENGTH"],
        'MAX_RESULTS' => $arParams["PAGE_RESULT_COUNT"],
        'OFFSET' => $offset
    ]);

    $arResult["SEARCH"] = [];
    foreach ($searchResult['items'] as $item) {
        $arResult["SEARCH"][] = [
            "IS_PRODUCT" => true,
            "PRODUCT_DATA" => $item
        ];
    }

    $arResult["PRODUCT_COUNT"] = $searchResult['total'];

    // Настройка пагинации
    $nav->setRecordCount($searchResult['total']);

    // Формируем массив для шаблона пагинации (для совместимости со старым шаблоном)
    $arResult["PAGINATION"] = [
        'TOTAL_PAGES' => $nav->getPageCount(),
        'CURRENT_PAGE' => $nav->getCurrentPage(),
        'RECORD_COUNT' => $nav->getRecordCount(),
    ];

    // Для стандартного компонента bitrix:system.pagenavigation (если вдруг понадобится)
    $arResult["NAV_OBJECT"] = $nav;
} else {
    $arResult["SEARCH"] = [];
    $arResult["PRODUCT_COUNT"] = 0;
}

$this->IncludeComponentTemplate();
