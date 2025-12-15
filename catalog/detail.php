<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

if (!Loader::includeModule('iblock')) {
    die('Модуль iblock не подключен');
}

$elementCode = $_REQUEST["ELEMENT_CODE"] ?? '';
if (!$elementCode) {
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
    \Bitrix\Iblock\Component\Tools::process404('Элемент не найден', true, true, true);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

$IBLOCK_ID = $element['IBLOCK_ID'];

$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("main_class", "product-page");

?>

<div class="catalog-page catalog-new">
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.element",
        "catalog_element",
        [
            "IBLOCK_TYPE" => "catalog",
            "IBLOCK_ID" => $IBLOCK_ID,
            "ELEMENT_CODE" => $elementCode,

            "SEF_MODE" => "Y",
            "SEF_FOLDER" => "/catalog/product/",
            "DETAIL_URL" => "/catalog/product/#ELEMENT_CODE#/",
            "ELEMENT_URL" => "/catalog/product/#ELEMENT_CODE#/",

            "SET_TITLE" => "Y",
            "SET_BROWSER_TITLE" => "Y",
            "SET_META_KEYWORDS" => "Y",
            "SET_META_DESCRIPTION" => "Y",
            "SET_CANONICAL_URL" => "Y",

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
