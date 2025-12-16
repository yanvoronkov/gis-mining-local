<?php
/**
 * Раздел Контейнеры
 * Использует комплексный компонент bitrix:catalog с шаблоном invest_catalog
 * ДЕТАЛЬНЫЕ СТРАНИЦЫ ОТСУТСТВУЮТ (возврат 404)
 * 
 * @canonical Bitrix approach — минимальный index.php
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// --- ID инфоблока ---
$IBLOCK_ID = IBLOCK_CONTENT_CONTAINERS;

// --- SEO-ТЕГИ ---
$APPLICATION->SetTitle('Контейнеры');
$APPLICATION->SetPageProperty('title', 'Контейнеры для майнинга от GIS Mining');
$APPLICATION->SetPageProperty('description', 'Майнинг-контейнеры под ключ от компании GIS Mining.');
$APPLICATION->SetPageProperty('robots', 'index, follow');

// --- СЛУЖЕБНЫЕ СВОЙСТВА ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title section-title highlighted-color");

// ====================================================================
// КОМПЛЕКСНЫЙ КОМПОНЕНТ КАТАЛОГА
// Вся разметка — внутри шаблона invest_catalog (section.php)
// ====================================================================
$APPLICATION->IncludeComponent(
    "bitrix:catalog",
    "invest_catalog",
    [
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => $IBLOCK_ID,

        // SEF режим
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/catalog/conteynery/",
        "SEF_URL_TEMPLATES" => [
            "sections" => "",
            "element" => "#ELEMENT_CODE#/",
        ],

        // Настройки сортировки
        "ELEMENT_SORT_FIELD" => "sort",
        "ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER2" => "desc",

        // Кэш
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_GROUPS" => "Y",

        // Мета-теги
        "SET_TITLE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "Y",

        // Количество элементов
        "PAGE_ELEMENT_COUNT" => "12",
        "LINE_ELEMENT_COUNT" => "3",

        // Свойства для отображения в карточке
        "LIST_PROPERTY_CODE" => [
            "CONTAINER_SIZE",
            "CONTAINER_TYPE",
            "DEVICES_COUNT",
            "MAX_DEVICES",
            "COOLING_TYPE",
            "POWER",
        ],

        // Пагинация
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",

        // Прочее
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "USE_COMPARE" => "N",
        "COMPATIBLE_MODE" => "Y",
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");