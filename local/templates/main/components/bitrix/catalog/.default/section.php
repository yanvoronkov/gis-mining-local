<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - tech_catalog
 * Контроллер для страницы списка раздела (section.php)
 * 
 * Используется для ASIC и Videocard разделов.
 * Содержит полную разметку страницы: сайдбар, поиск, список товаров.
 * 
 * @canonical Bitrix approach - вся разметка внутри шаблона
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('catalog');

// ====================================================================
// ОПРЕДЕЛЕНИЕ ТЕКУЩЕГО РАЗДЕЛА КАТАЛОГА
// ====================================================================
$IBLOCK_ID = $arParams["IBLOCK_ID"];
$sefFolder = $arResult["FOLDER"];

// Определяем SEF-правило для фильтра
$filterSefRule = $sefFolder . "filter/#SMART_FILTER_PATH#/apply/";

// ====================================================================
// ИЗВЛЕЧЕНИЕ SMART_FILTER_PATH ДЛЯ ФИЛЬТРА
// ====================================================================
// Bitrix иногда не распознаёт URL фильтра и передаёт его как SECTION_CODE_PATH.
// Проверяем, если URL содержит /filter/.../apply/ — это фильтр!
$smartFilterPath = $arResult["VARIABLES"]["SMART_FILTER_PATH"] ?? '';

// ПРИОРИТЕТ 1: Проверяем, установлен ли SMART_FILTER_PATH в init.php (модуль SEO фильтра)
if (empty($smartFilterPath) && !empty($_REQUEST['SMART_FILTER_PATH'])) {
    $smartFilterPath = $_REQUEST['SMART_FILTER_PATH'];
    $arResult["VARIABLES"]["SMART_FILTER_PATH"] = $smartFilterPath;
    // Очищаем SECTION_CODE_PATH, т.к. это не раздел
    $arResult["VARIABLES"]["SECTION_CODE_PATH"] = '';
}

// ПРИОРИТЕТ 2: Если SMART_FILTER_PATH пустой, но URL выглядит как стандартный фильтр — извлекаем вручную
if (empty($smartFilterPath)) {
    $requestUri = $_SERVER["REQUEST_URI"];
    if (preg_match('#/filter/([^?]+)/apply/?#', $requestUri, $matches)) {
        $smartFilterPath = rtrim($matches[1], '/');
        $arResult["VARIABLES"]["SMART_FILTER_PATH"] = $smartFilterPath;
        // Очищаем SECTION_CODE_PATH, т.к. это не раздел
        $arResult["VARIABLES"]["SECTION_CODE_PATH"] = '';
    }
}

// Устанавливаем в $_REQUEST для сайдбара
if (!empty($smartFilterPath)) {
    $_REQUEST["SMART_FILTER_PATH"] = $smartFilterPath;
}

// ====================================================================
// ГЛОБАЛЬНЫЙ ФИЛЬТР ДЛЯ КОМПОНЕНТОВ
// ====================================================================
// Кастомная сортировка реализована в result_modifier.php шаблона catalog.section
// Это позволяет сохранить работу пагинации
// ====================================================================
global $arrFilter;
if (!is_array($arrFilter)) {
    $arrFilter = [];
}

// ====================================================================
// ДАННЫЕ ИНФОБЛОКА ДЛЯ ОПИСАНИЯ
// ====================================================================
$iblockData = [];
if ($iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch()) {
    $iblockData = [
        'NAME' => $iblock['NAME'],
        'DESCRIPTION' => $iblock['DESCRIPTION'],
    ];
}

// Определяем заголовок раздела
$sectionTitle = "Каталог";
if ($IBLOCK_ID == IBLOCK_CATALOG_ASICS) {
    $sectionTitle = "Каталог ASIC майнеров для добычи криптовалют";
} elseif ($IBLOCK_ID == IBLOCK_CATALOG_VIDEOCARD) {
    $sectionTitle = "Каталог видеокарт для майнинга криптовалют";
}

?>

<!-- ====================================================================
     РАЗМЕТКА СТРАНИЦЫ СПИСКА ТОВАРОВ
     ==================================================================== -->
<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
    <!-- H1 выводится глобально из header.php -->

    <!-- Компонент живого поиска (мобильный) -->
    <div class="catalog-search-mobile">
        <?php $APPLICATION->IncludeComponent(
            "custom:catalog.search",
            ".default",
            [
                "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
                "MIN_QUERY_LENGTH" => 2,
                "MAX_RESULTS" => 10,
                "SHOW_PRICE" => "Y",
                "PRICE_CODE" => "BASE",
                "CACHE_TIME" => 3600,
            ]
        ); ?>
    </div>

    <div class="catalog-page__body">
        <!-- Сайдбар с навигацией -->
        <aside class="catalog-page__sidebar">
            <?php
            // Навигация по инфоблокам каталога
            $APPLICATION->IncludeComponent(
                "custom:catalog.sidebar",
                ".default",
                [
                    "IBLOCK_ID" => $IBLOCK_ID,
                ]
            );
            ?>

            <!-- Умный фильтр (ПРЯМОЙ ВЫЗОВ для работы с модулем SEO) -->
            <div class="catalog-accordion">
                <button type="button" class="catalog-accordion__toggle btn btn-primary not-mobile-visible">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_3738_35183)">
                            <path
                                d="M0.5 2.82059H8.11266C8.34209 3.86494 9.27472 4.64897 10.387 4.64897C11.4992 4.64897 12.4318 3.86497 12.6613 2.82059H15.5C15.7761 2.82059 16 2.59672 16 2.32059C16 2.04447 15.7761 1.82059 15.5 1.82059H12.661C12.4312 0.77678 11.4972 -0.00775146 10.387 -0.00775146C9.27609 -0.00775146 8.34262 0.776655 8.11284 1.82059H0.5C0.223875 1.82059 0 2.04447 0 2.32059C0 2.59672 0.223875 2.82059 0.5 2.82059ZM9.05866 2.3219C9.05866 2.32012 9.05869 2.31831 9.05869 2.31653C9.06087 1.58631 9.65672 0.99228 10.387 0.99228C11.1162 0.99228 11.7121 1.5855 11.7152 2.31537L11.7153 2.32272C11.7142 3.05419 11.1187 3.649 10.387 3.649C9.65553 3.649 9.06028 3.05478 9.05863 2.32375L9.05866 2.3219ZM15.5 13.1794H12.661C12.4311 12.1356 11.4972 11.351 10.387 11.351C9.27609 11.351 8.34262 12.1355 8.11284 13.1794H0.5C0.223875 13.1794 0 13.4032 0 13.6794C0 13.9555 0.223875 14.1794 0.5 14.1794H8.11266C8.34209 15.2237 9.27472 16.0077 10.387 16.0077C11.4992 16.0077 12.4318 15.2237 12.6613 14.1794H15.5C15.7761 14.1794 16 13.9555 16 13.6794C16 13.4032 15.7761 13.1794 15.5 13.1794ZM10.387 15.0077C9.65553 15.0077 9.06028 14.4135 9.05863 13.6825L9.05866 13.6807C9.05866 13.6789 9.05869 13.6771 9.05869 13.6753C9.06087 12.9451 9.65672 12.351 10.387 12.351C11.1162 12.351 11.7121 12.9442 11.7152 13.6741L11.7153 13.6814C11.7143 14.413 11.1188 15.0077 10.387 15.0077ZM15.5 7.5H7.88734C7.65791 6.45566 6.72528 5.67165 5.61303 5.67165C4.50078 5.67165 3.56816 6.45566 3.33872 7.5H0.5C0.223875 7.5 0 7.72387 0 8C0 8.27615 0.223875 8.5 0.5 8.5H3.33897C3.56888 9.54378 4.50275 10.3283 5.61303 10.3283C6.72391 10.3283 7.65738 9.5439 7.88716 8.5H15.5C15.7761 8.5 16 8.27615 16 8C16 7.72387 15.7761 7.5 15.5 7.5ZM6.94134 7.99869C6.94134 8.0005 6.94131 8.00228 6.94131 8.00406C6.93912 8.73428 6.34328 9.32831 5.61303 9.32831C4.88381 9.32831 4.28794 8.73509 4.28478 8.00525L4.28469 7.99794C4.28578 7.26637 4.88125 6.67165 5.61303 6.67165C6.34447 6.67165 6.93972 7.26584 6.94137 7.9969L6.94134 7.99869Z"
                                fill="white" />
                        </g>
                        <defs>
                            <clipPath id="clip0_3738_35183">
                                <rect width="16" height="16" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>
                    <span>Фильтры</span>
                    <svg class="icon-arrow" width="10" height="5" viewBox="0 0 10 5" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 0.5L5 4.5L1 0.5" stroke="white" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="catalog-accordion__content filters">
                    <?php
                    // ПРЯМОЙ ВЫЗОВ bitrix:catalog.smart.filter
                    // Необходимо для корректной работы с модулем dwstroy.seochpulite
                    $APPLICATION->IncludeComponent(
                        "bitrix:catalog.smart.filter",
                        "smart_filter",
                        [
                            "CACHE_GROUPS" => "Y",
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "A",
                            "CONVERT_CURRENCY" => "N",
                            "DISPLAY_ELEMENT_COUNT" => "Y",
                            "FILTER_NAME" => "arrFilter",
                            "FILTER_VIEW_MODE" => "vertical",
                            "HIDE_NOT_AVAILABLE" => "N",
                            "IBLOCK_ID" => $IBLOCK_ID,
                            "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"] ?? 0,
                            "IBLOCK_TYPE" => "catalog",
                            "INSTANT_RELOAD" => "N",
                            "SEF_MODE" => "Y",
                            "SEF_RULE" => $filterSefRule,
                            "SMART_FILTER_PATH" => $_REQUEST["SMART_FILTER_PATH"] ?? "",
                            "COMPONENT_TEMPLATE" => "smart_filter",
                            "POPUP_POSITION" => "left",
                            "COMPONENT_CONTAINER_ID" => "catalog-section-container"
                        ],
                        false
                    );
                    ?>
                </div>
            </div>
        </aside>

        <!-- Основной контент -->
        <section class="catalog-page__content section-padding">
            <div class="catalog-content__header">
                <h2 class="catalog-content__title section-title"><?= $iblockData['NAME'] ?: $APPLICATION->GetTitle() ?>
                </h2>
                <!-- Компонент живого поиска (десктоп) -->
                <div class="catalog-search">
                    <?php $APPLICATION->IncludeComponent(
                        "custom:catalog.search",
                        ".default",
                        [
                            "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
                            "MIN_QUERY_LENGTH" => 2,
                            "MAX_RESULTS" => 10,
                            "SHOW_PRICE" => "Y",
                            "PRICE_CODE" => "BASE",
                            "CACHE_TIME" => 3600,
                        ]
                    ); ?>
                </div>
            </div>

            <?php
            // ====================================================================
            // КОМПОНЕНТ СПИСКА ТОВАРОВ
            // ====================================================================
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section",
                ".default",
                [
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "ELEMENT_SORT_FIELD" => "PROPERTY_SORT_PRIORITY",
                    "ELEMENT_SORT_ORDER" => "DESC",
                    "ELEMENT_SORT_FIELD2" => "CATALOG_PRICE_1",
                    "ELEMENT_SORT_ORDER2" => "ASC",
                    "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
                    "META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
                    "META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
                    "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                    "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
                    "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                    "BASKET_URL" => $arParams["BASKET_URL"],
                    "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                    "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                    "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                    "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                    "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                    "FILTER_NAME" => "arrFilter",
                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                    "SET_TITLE" => $arParams["SET_TITLE"],
                    "SET_BROWSER_TITLE" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                    "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                    "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
                    "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                    "PRICE_CODE" => $arParams["PRICE_CODE"],
                    "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                    "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                    "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                    "USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                    "PRODUCT_PROPERTIES" => [],
                    "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                    "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                    "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                    "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                    "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
                    "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                    "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
                    "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
                    "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
                    "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                    "LAZY_LOAD" => $arParams["LAZY_LOAD"],
                    "MESS_BTN_LAZY_LOAD" => $arParams["MESS_BTN_LAZY_LOAD"],
                    "LOAD_ON_SCROLL" => $arParams["LOAD_ON_SCROLL"],
                    "SHOW_ALL_WO_SECTION" => "Y",
                    "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                    "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                    "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                    "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                    "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                    "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                    "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
                    "HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
                    "LABEL_PROP" => $arParams["LABEL_PROP"],
                    "LABEL_PROP_MOBILE" => $arParams["LABEL_PROP_MOBILE"],
                    "LABEL_PROP_POSITION" => $arParams["LABEL_PROP_POSITION"],
                    "ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
                    "PRODUCT_DISPLAY_MODE" => $arParams["PRODUCT_DISPLAY_MODE"],
                    "PRODUCT_BLOCKS_ORDER" => $arParams["LIST_PRODUCT_BLOCKS_ORDER"],
                    "PRODUCT_ROW_VARIANTS" => $arParams["LIST_PRODUCT_ROW_VARIANTS"],
                    "ENLARGE_PRODUCT" => $arParams["LIST_ENLARGE_PRODUCT"],
                    "ENLARGE_PROP" => $arParams["LIST_ENLARGE_PROP"],
                    "SHOW_SLIDER" => $arParams["LIST_SHOW_SLIDER"],
                    "SLIDER_INTERVAL" => $arParams["LIST_SLIDER_INTERVAL"],
                    "SLIDER_PROGRESS" => $arParams["LIST_SLIDER_PROGRESS"],
                    "OFFER_ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
                    "OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
                    "PRODUCT_SUBSCRIPTION" => $arParams["PRODUCT_SUBSCRIPTION"],
                    "SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
                    "DISCOUNT_PERCENT_POSITION" => $arParams["DISCOUNT_PERCENT_POSITION"],
                    "SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
                    "SHOW_MAX_QUANTITY" => $arParams["SHOW_MAX_QUANTITY"],
                    "MESS_SHOW_MAX_QUANTITY" => $arParams["~MESS_SHOW_MAX_QUANTITY"],
                    "RELATIVE_QUANTITY_FACTOR" => $arParams["RELATIVE_QUANTITY_FACTOR"],
                    "MESS_RELATIVE_QUANTITY_MANY" => $arParams["~MESS_RELATIVE_QUANTITY_MANY"],
                    "MESS_RELATIVE_QUANTITY_FEW" => $arParams["~MESS_RELATIVE_QUANTITY_FEW"],
                    "MESS_BTN_BUY" => $arParams["~MESS_BTN_BUY"],
                    "MESS_BTN_ADD_TO_BASKET" => $arParams["~MESS_BTN_ADD_TO_BASKET"],
                    "MESS_BTN_SUBSCRIBE" => $arParams["~MESS_BTN_SUBSCRIBE"],
                    "MESS_BTN_DETAIL" => $arParams["~MESS_BTN_DETAIL"],
                    "MESS_NOT_AVAILABLE" => $arParams["~MESS_NOT_AVAILABLE"],
                    "MESS_BTN_COMPARE" => $arParams["~MESS_BTN_COMPARE"],
                    "USE_ENHANCED_ECOMMERCE" => $arParams["USE_ENHANCED_ECOMMERCE"],
                    "DATA_LAYER_NAME" => $arParams["DATA_LAYER_NAME"],
                    "BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"],
                    "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                    "ADD_SECTIONS_CHAIN" => "N",
                    "ADD_TO_BASKET_ACTION" => $arParams["ADD_TO_BASKET_ACTION"],
                    "SHOW_CLOSE_POPUP" => $arParams["SHOW_CLOSE_POPUP"],
                    "COMPARE_PATH" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["compare"],
                    "COMPARE_NAME" => $arParams["COMPARE_NAME"],
                    "USE_COMPARE_LIST" => "Y",
                    "BACKGROUND_IMAGE" => $arParams["SECTION_BACKGROUND_IMAGE"],
                    "COMPATIBLE_MODE" => "Y",
                    "DISABLE_INIT_JS_IN_COMPONENT" => "N",
                ],
                $component,
                ["HIDE_ICONS" => "Y"]
            );
            ?>
        </section>
    </div>

    <!-- Описание раздела -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: $sectionTitle ?></h2>
            <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
            </div>
        </div>
    </section>

    <!-- Секция "Обратная связь" -->
    <?php $APPLICATION->IncludeComponent("custom:feedback.section", ".default", []); ?>
</div>