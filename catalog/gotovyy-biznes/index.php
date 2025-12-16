<?php
/**
 * Раздел Готовый бизнес
 * Использует комплексный компонент bitrix:catalog с шаблоном invest_catalog
 * ВАЖНО: Использует СПЕЦИАЛЬНЫЙ шаблон business_grouped для списка
 * ДЕТАЛЬНЫЕ СТРАНИЦЫ ОТСУТСТВУЮТ (возврат 404)
 * 
 * @canonical Bitrix approach — минимальный index.php
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// --- ID инфоблока ---
$IBLOCK_ID = IBLOCK_CONTENT_BUSINESS;

// --- SEO-ТЕГИ ---
$APPLICATION->SetTitle('Готовый бизнес по майнингу от GIS Mining');
$APPLICATION->SetPageProperty('title', 'Готовый бизнес для майнинга от GIS Mining');
$APPLICATION->SetPageProperty('description', 'Готовые решения для майнингового бизнеса от компании GIS Mining.');
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
        "SEF_FOLDER" => "/catalog/gotovyy-biznes/",
        "SEF_URL_TEMPLATES" => [
            "sections" => "",
            "element" => "#ELEMENT_CODE#/",
        ],

        // !!! ВАЖНЫЙ ПАРАМЕТР: ВЫБОР ШАБЛОНА СПИСКА !!!
        "SECTION_LIST_TEMPLATE" => "business_grouped",

        // Настройки сортировки
        "ELEMENT_SORT_FIELD" => "sort",
        "ELEMENT_SORT_ORDER" => "asc",

        // Кэш
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_GROUPS" => "Y",

        // Мета-теги
        "SET_TITLE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "Y",

        // Количество элементов
        "PAGE_ELEMENT_COUNT" => "12",
        "LINE_ELEMENT_COUNT" => "3",

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