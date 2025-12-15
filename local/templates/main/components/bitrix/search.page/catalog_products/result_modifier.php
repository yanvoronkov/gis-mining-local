<?php
/**
 * Модификатор результатов поиска
 * Обогащает результаты данными через SearchHelper
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('catalog');

// SearchHelper уже подключен через init.php

// Дополнительная информация для шаблона
$arResult["SEARCH_QUERY"] = $_GET['q'] ?? '';

// Собираем ID всех товаров из результатов поиска
$allProductIds = [];
$productItems = [];
foreach ($arResult["SEARCH"] as $key => $arItem) {
    // Проверяем, что это элемент инфоблока каталога
    if ($arItem["MODULE_ID"] == "iblock" && 
        in_array($arItem["PARAM2"], [1, 3, 4, 5, 6, 11])) {
        $allProductIds[] = $arItem["ITEM_ID"];
        $productItems[] = $arItem;
    }
}

// --- Ручная пагинация (аналогично каталогу ASIC) ---
$perPage = 15; // Товаров на странице
$page = isset($_GET['PAGEN_1']) ? max(1, intval($_GET['PAGEN_1'])) : 1;
$total = count($allProductIds);
$totalPages = max(1, (int)ceil($total / $perPage));

// Передаем данные пагинации в шаблон
$arResult["PAGINATION"] = [
    'TOTAL_PAGES' => $totalPages,
    'CURRENT_PAGE' => $page,
    'PER_PAGE' => $perPage,
    'TOTAL_ITEMS' => $total,
];

// Получаем ID товаров только для текущей страницы
$offset = ($page - 1) * $perPage;
$currentPageProductIds = array_slice($allProductIds, $offset, $perPage);

// Обогащаем данные ТОЛЬКО для текущей страницы
$enrichedProducts = [];
if (!empty($currentPageProductIds)) {
    try {
        $enrichedProducts = SearchHelper::enrichProducts($currentPageProductIds);
    } catch (Exception $e) {
        // Логируем ошибку, но не останавливаем выполнение
    }
}

// Фильтруем результаты - оставляем только товары текущей страницы
$currentPageItems = array_slice($productItems, $offset, $perPage);

// Заменяем $arResult["SEARCH"] только товарами текущей страницы
$arResult["SEARCH"] = [];
foreach ($currentPageItems as $arItem) {
    if (isset($enrichedProducts[$arItem["ITEM_ID"]])) {
        $arItem["PRODUCT_DATA"] = $enrichedProducts[$arItem["ITEM_ID"]];
        $arItem["IS_PRODUCT"] = true;
    } else {
        $arItem["IS_PRODUCT"] = false;
    }
    $arResult["SEARCH"][] = $arItem;
}

// Дополнительная информация для шаблона
$arResult["PRODUCT_COUNT"] = $total; // Общее количество товаров

