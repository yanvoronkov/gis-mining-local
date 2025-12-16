<?php
/**
 * Раздел Видеокарты
 * 
 * Использует комплексный компонент bitrix:catalog с шаблоном tech_catalog.
 * Вся логика разметки находится в шаблонах компонента:
 * - section.php — список товаров (с сайдбаром, поиском, описанием)
 * - element.php — детальная страница товара
 * - smart_filter.php — отфильтрованный список
 * 
 * @canonical Bitrix approach — минимальный index.php
 */

use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// --- Подключение модулей ---
if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    echo "Не удалось подключить модули iblock или catalog";
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

// --- ID инфоблока ---
$IBLOCK_ID = IBLOCK_CATALOG_VIDEOCARD;

// ====================================================================
// SEO-НАСТРОЙКИ ДЛЯ СТРАНИЦЫ РАЗДЕЛА
// (Для детальной страницы SEO управляется в element.php)
// ====================================================================

setCatalogSeo([
    'title' => 'Купить видеокарты для ИИ или 3D — Каталог продукции от «Gis Mining»',
    'h1' => 'Видеокарты',
    'description' => 'Каталог видеокарт от компании «Gis Mining». Доступные цены, высокое качество, широкий ассортимент. ',
    'robots' => 'index, follow',
]);

$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title section-title highlighted-color");

// ====================================================================
// ОБРАБОТКА КРАСИВЫХ URL МОДУЛЯ SEO ФИЛЬТРА
// ====================================================================
if (Loader::includeModule('dwstroy.seochpulite')) {
    \Dwstroy\SeoChpuLite\Helper::makeOldUrl();
}

// ====================================================================
// SEF URL ШАБЛОНЫ
// ВАЖНО: Порядок критичен! Более специфичные должны идти ПЕРВЫМИ!
// ====================================================================
$sefUrlTemplates = [
	"smart_filter" => "filter/#SMART_FILTER_PATH#/apply/", // ПЕРВЫМ!
	"calculator" => "#ELEMENT_CODE#/calculator-dohodnosti",
	"sections" => "",
	"section" => "#SECTION_CODE_PATH#/",
	"element" => "#ELEMENT_CODE#/",
];

// ====================================================================
// КОМПЛЕКСНЫЙ КОМПОНЕНТ КАТАЛОГА
// Вся разметка — внутри шаблонов (section.php, element.php)
// ====================================================================
$APPLICATION->IncludeComponent(
    "bitrix:catalog",
    ".default",
    [
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => $IBLOCK_ID,
        "BASKET_URL" => "/basket/",
        "ACTION_VARIABLE" => "action",
        "PRODUCT_ID_VARIABLE" => "id",
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "PRODUCT_PROPS_VARIABLE" => "prop",

        // SEF режим
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/catalog/videocard/",
        "SEF_URL_TEMPLATES" => $sefUrlTemplates,
        "DETAIL_STRICT_SECTION_CHECK" => "N",

        // Настройки отображения
        "USE_COMPARE" => "N",
        "USE_FILTER" => "Y",
        "USE_REVIEW" => "N",
        "USE_ELEMENT_COUNTER" => "Y",

        // Цены
        "PRICE_CODE" => ["BASE"],
        "USE_PRICE_COUNT" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "PRICE_VAT_INCLUDE" => "Y",

        // Кэш
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "CACHE_GROUPS" => "Y",
        "CACHE_FILTER" => "Y",  // Включен для производительности

        // Мета-теги
        "SET_TITLE" => "N",  // Управляется в index.php
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_LAST_MODIFIED" => "N",
        // 404 отключен - иначе компонент не вызывает шаблон для URL фильтра
        "SET_STATUS_404" => "N",
        "SHOW_404" => "N",
        "FILE_404" => "",

        // Количество элементов
        "PAGE_ELEMENT_COUNT" => "12",
        "LINE_ELEMENT_COUNT" => "3",

        // Свойства
        "LIST_PROPERTY_CODE" => [
            "MANUFACTURER",
            "CRYPTO",
            "ALGORITHM",
            "HASHRATE",
            "HASHRATE_TH",
            "HASHRATE_MH",
            "HASHRATE_KSOL",
            "CONSUMPTION",
            "EFFICIENCY",
        ],
        "DETAIL_PROPERTY_CODE" => [
            "MANUFACTURER",
            "CRYPTO",
            "ALGORITHM",
            "HASHRATE",
            "HASHRATE_TH",
            "HASHRATE_MH",
            "HASHRATE_KSOL",
            "CONSUMPTION",
            "EFFICIENCY",
            "NOISE_LEVEL",
            "DIMENSIONS",
            "WEIGHT",
            "TEMPERATURE",
            "INTERFACE",
            "MORE_PHOTO",
            "WARRANTY",
            "FEATURED",
        ],

        // Пагинация
        "PAGER_TEMPLATE" => PAGER_TEMPLATE,
        "PAGER_TITLE" => "Товары",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "Y",

        // Прочее
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "TEMPLATE_THEME" => "blue",
        "COMPATIBLE_MODE" => "Y",
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
