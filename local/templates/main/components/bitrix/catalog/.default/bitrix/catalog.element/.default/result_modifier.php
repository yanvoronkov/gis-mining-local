<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */

// ======================================================================
// PRODUCT SCHEMA (JSON-LD) - Генерация в result_modifier для доступа к полным данным
// ======================================================================

$protocol = CMain::IsHTTPS() ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$cleanDomain = preg_replace('/:\d+$/', '', $domain);

// Product Schema
$productSchema = [
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $arResult['NAME']
];

// Description
$description = '';
if (!empty($arResult['DETAIL_TEXT'])) {
    $description = $arResult['DETAIL_TEXT'];
} elseif (!empty($arResult['PREVIEW_TEXT'])) {
    $description = $arResult['PREVIEW_TEXT'];
}

if ($description) {
    // 1. Decode HTML entities (like &nbsp;)
    $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5);
    // 2. Strip HTML tags
    $description = strip_tags($description);
    // 3. Replace multiple whitespace/newlines/tabs with single space
    $description = preg_replace('/\s+/', ' ', $description);
    // 4. Trim result
    $productSchema['description'] = trim($description);
}

// Image
if (!empty($arResult['DETAIL_PICTURE']['SRC'])) {
    $productSchema['image'] = $protocol . '://' . $cleanDomain . $arResult['DETAIL_PICTURE']['SRC'];
}

// SKU (артикул)
if (!empty($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'])) {
    $productSchema['sku'] = $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'];
}

// Brand
$brandName = null;
if (!empty($arResult['PROPERTIES']['MANUFACTURER']['VALUE'])) {
    $brandName = $arResult['PROPERTIES']['MANUFACTURER']['VALUE'];
} elseif (!empty($arResult['PROPERTIES']['BRAND']['VALUE'])) {
    $brandName = $arResult['PROPERTIES']['BRAND']['VALUE'];
}

if ($brandName) {
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $brandName
    ];
}

// Offers
$priceData = null;
if (!empty($arResult['ITEM_PRICES'][0])) {
    $priceData = $arResult['ITEM_PRICES'][0];
    $priceValue = $priceData['PRICE'];
    $currency = $priceData['CURRENCY'];
} elseif (!empty($arResult['PRICES']['BASE'])) {
    $priceData = $arResult['PRICES']['BASE'];
    $priceValue = $priceData['VALUE'];
    $currency = $priceData['CURRENCY'];
}

if ($priceData) {
    $productSchema['offers'] = [
        '@type' => 'Offer',
        'priceCurrency' => $currency,
        'price' => $priceValue,
        'url' => $protocol . '://' . $cleanDomain . $arResult['DETAIL_PAGE_URL'],
        'availability' => $arResult['CAN_BUY'] ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder', // PreOrder is better fallback than OutOfStock for mining calls
        'seller' => [
            '@type' => 'Organization',
            'name' => 'gis-mining.ru'
        ]
    ];
}

// Save to Result and Cache Keys
$arResult['PRODUCT_SCHEMA'] = $productSchema;
$this->__component->SetResultCacheKeys(['PRODUCT_SCHEMA']);
