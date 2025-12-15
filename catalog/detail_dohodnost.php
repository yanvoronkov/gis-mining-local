<?php
// Подключаем prolog_before для инициализации $APPLICATION БЕЗ вывода HTML
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

if (!Loader::includeModule('iblock')) {
    die('Модуль iblock не подключен');
}

$elementCode = $_REQUEST["ELEMENT_CODE"] ?? '';
if (!$elementCode) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
    \Bitrix\Iblock\Component\Tools::process404('Нет кода элемента', true, true, true);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

// Ищем элемент по символьному коду во всех активных инфоблоках типа "catalog"
$element = ElementTable::getList([
    'select' => ['ID', 'IBLOCK_ID', 'CODE', 'NAME'],
    'filter' => [
        '=CODE' => $elementCode,
        '=ACTIVE' => 'Y',
    ],
    'limit' => 1,
])->fetch();

if (!$element) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
    \Bitrix\Iblock\Component\Tools::process404('Элемент не найден', true, true, true);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

$IBLOCK_ID = $element['IBLOCK_ID'];

// --- Устанавливаем Title и Description ДО вывода HTML ---
$productNamePrefix = "Доходность ";
$pageTitle = $productNamePrefix . $element['NAME'];
$pageDescription = "Калькулятор прибыльности асика " . $element['NAME'] . " - GIS-MINING 2025";

// Устанавливаем Title (теперь $APPLICATION уже определён)
$APPLICATION->SetTitle($pageTitle);
$APPLICATION->SetPageProperty("title", $pageTitle);
$APPLICATION->SetPageProperty("description", $pageDescription);

$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("main_class", "product-page");

// Теперь подключаем prolog_after (вывод <head> и начало <body>)
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

?>

<div class="catalog-page catalog-new">
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.element",
        "catalog_element_dohodnost",
        [
            "IBLOCK_TYPE" => "catalog",
            "IBLOCK_ID" => $IBLOCK_ID,
            "ELEMENT_CODE" => $elementCode,

            "SEF_MODE" => "Y",
            "SEF_FOLDER" => "/catalog/product/",
            "DETAIL_URL" => "/catalog/product/#ELEMENT_CODE#/calculator-dohodnosti/",
            "ELEMENT_URL" => "/catalog/product/#ELEMENT_CODE#/calculator-dohodnosti/",

            "SET_TITLE" => "N",
            "SET_BROWSER_TITLE" => "N",
            "SET_META_KEYWORDS" => "N",
            "SET_META_DESCRIPTION" => "N",
            "SET_CANONICAL_URL" => "N",
            "ADD_SECTIONS_CHAIN" => "N",
            "ADD_ELEMENT_CHAIN" => "N",

            "PROPERTY_CODE" => [
                "MANUFACTURER", "CRYPTO", "ALGORITHM", "HASHRATE",
                "EFFICIENCY", "POWER", "GUARANTEE", "HIT", "MORE_PHOTO"
            ],
            "PRICE_CODE" => ["BASE"],

            "SET_STATUS_404" => "Y",
            "SHOW_404" => "Y",
            "FILE_404" => "/404.php",
        ],
        false
    );
    ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

