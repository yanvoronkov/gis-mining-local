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

$productSchema = [
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $arResult['NAME']
];

// Description
if (!empty($arResult['PREVIEW_TEXT'])) {
    $productSchema['description'] = strip_tags($arResult['PREVIEW_TEXT']);
} elseif (!empty($arResult['DETAIL_TEXT'])) {
    $productSchema['description'] = mb_substr(strip_tags($arResult['DETAIL_TEXT']), 0, 300);
}

// Image
if (!empty($arResult['DETAIL_PICTURE']['SRC'])) {
    $productSchema['image'] = $protocol . '://' . $cleanDomain . $arResult['DETAIL_PICTURE']['SRC'];
}

// Brand (MANUFACTURER для ASIC, BRAND для GPU/видеокарт)
if (!empty($arResult['PROPERTIES']['MANUFACTURER']['VALUE'])) {
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $arResult['PROPERTIES']['MANUFACTURER']['VALUE']
    ];
} elseif (!empty($arResult['PROPERTIES']['BRAND']['VALUE'])) {
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $arResult['PROPERTIES']['BRAND']['VALUE']
    ];
} else {
    // Дефолтный бренд по типу каталога
    $defaultBrand = 'GIS Mining';
    switch ($arResult['IBLOCK_ID']) {
        case 1: // ASIC
            $defaultBrand = 'ASIC Miners';
            break;
        case 11: // Видеокарты
            $defaultBrand = 'GPU';
            break;
    }
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $defaultBrand
    ];
}

// Offers (price and availability)
if (!empty($arResult['ITEM_PRICES'][0])) {
    $price = $arResult['ITEM_PRICES'][0];
    $productSchema['offers'] = [
        '@type' => 'Offer',
        'price' => $price['PRICE'],
        'priceCurrency' => $price['CURRENCY'],
        'availability' => $arResult['CAN_BUY'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => $protocol . '://' . $cleanDomain . $arResult['DETAIL_PAGE_URL']
    ];
} elseif (!empty($arResult['PRICES']['BASE'])) {
    $price = $arResult['PRICES']['BASE'];
    $productSchema['offers'] = [
        '@type' => 'Offer',
        'price' => $price['VALUE'],
        'priceCurrency' => $price['CURRENCY'],
        'availability' => $arResult['CAN_BUY'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => $protocol . '://' . $cleanDomain . $arResult['DETAIL_PAGE_URL']
    ];
}

// SKU (артикул)
if (!empty($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'])) {
    $productSchema['sku'] = $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'];
}

// Вывод схемы через ViewContent (будет показано в footer.php)
$APPLICATION->AddViewContent('json_ld_schemas', '<script type="application/ld+json">' . "\n" .
    json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
    "\n" . '</script>' . "\n");
