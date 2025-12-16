<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - invest_catalog
 * Контроллер для страницы списка раздела (section.php)
 * 
 * Используется для контентных инфоблоков БЕЗ детальных страниц:
 * - GPU
 * - Инвестиции
 * - Готовый бизнес (с группировкой по разделам!)
 * - Контейнеры
 * 
 * НЕ выводит фильтр и поиск в сайдбар!
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

// Подключаем необходимые модули
Loader::includeModule('iblock');

// ====================================================================
// ВЫБОР ШАБЛОНА СПИСКА
// ====================================================================
// По умолчанию .default, для "Готового бизнеса" - business_grouped
// ====================================================================

$listTemplate = ".default";

// Проверяем наличие параметра SECTION_LIST_TEMPLATE
if (isset($arParams["SECTION_LIST_TEMPLATE"]) && !empty($arParams["SECTION_LIST_TEMPLATE"])) {
    $listTemplate = $arParams["SECTION_LIST_TEMPLATE"];
}

// ====================================================================
// ДАННЫЕ ИНФОБЛОКА ДЛЯ ОПИСАНИЯ
// ====================================================================
$IBLOCK_ID = $arParams["IBLOCK_ID"];
$iblockData = [];
if ($iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch()) {
    $iblockData = [
        'NAME' => $iblock['NAME'],
        'DESCRIPTION' => $iblock['DESCRIPTION'],
    ];
}

?>

<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
    <div class="catalog-page__body">
        <!-- Сайдбар -->
        <?php $APPLICATION->IncludeComponent(
            "custom:catalog.sidebar",
            ".default",
            [
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "SHOW_FILTER" => "N", // В контентных разделах фильтр обычно не нужен, или включить по необходимости
                "SEF_RULE" => "", // Если нужен фильтр, передать $arResult["FOLDER"] . ...
                "FILTER_NAME" => "arrFilter",
            ]
        ); ?>

        <!-- Основной контент -->
        <div class="catalog-page__content">
            <?php
            // ====================================================================
            // ВЫБОР ШАБЛОНА СПИСКА
            // ====================================================================
            if ($listTemplate === "business_grouped") {
                // Получаем разделы инфоблока для группировки
                $sections = [];
                $dbSections = CIBlockSection::GetList(
                    ['SORT' => 'ASC', 'NAME' => 'ASC'],
                    [
                        'IBLOCK_ID' => $arParams["IBLOCK_ID"],
                        'ACTIVE' => 'Y',
                        'GLOBAL_ACTIVE' => 'Y',
                    ],
                    false,
                    ['ID', 'NAME', 'CODE', 'DESCRIPTION', 'PICTURE']
                );

                while ($section = $dbSections->Fetch()) {
                    $sections[] = [
                        'ID' => (int) $section['ID'],
                        'NAME' => $section['NAME'],
                        'CODE' => $section['CODE'],
                        'DESCRIPTION' => $section['DESCRIPTION'],
                        'PICTURE' => $section['PICTURE'] ? CFile::GetPath($section['PICTURE']) : null,
                    ];
                }

                // Выводим товары сгруппированные по разделам
                if (!empty($sections)):
                    foreach ($sections as $section): ?>
                        <div class="catalog-page__content business-section">
                            <h2 class="catalog-content__title"><?= htmlspecialchars($section['NAME']) ?></h2>
                            <?php if (!empty($section['DESCRIPTION'])): ?>
                                <p class="business-section__description"><?= $section['DESCRIPTION'] ?></p>
                            <?php endif; ?>

                            <?php
                            $APPLICATION->IncludeComponent(
                                "bitrix:catalog.section",
                                "business_grouped",
                                [
                                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                    "SECTION_ID" => $section['ID'],
                                    "SECTION_CODE" => $section['CODE'],
                                    "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"] ?: "sort",
                                    "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"] ?: "asc",
                                    "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"] ?: "id",
                                    "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"] ?: "desc",
                                    "PROPERTY_CODE" => [
                                        "DEVICES_COUNT",
                                        "YEARLY_PROFIT",
                                        "MONTHLY_INCOME",
                                        "PAYBACK_PERIOD",
                                        "CRYPTO",
                                    ],
                                    "DISPLAY_TOP_PAGER" => "N",
                                    "DISPLAY_BOTTOM_PAGER" => "N",
                                    "SHOW_404" => "N",
                                    "SECTION_URL" => $arResult["FOLDER"],
                                    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                    "CACHE_FILTER" => "N",
                                    "PRICE_CODE" => $arParams["PRICE_CODE"] ?: ["BASE"],
                                    "USE_PRICE_COUNT" => "N",
                                    "SHOW_PRICE_COUNT" => "1",
                                    "PRICE_VAT_INCLUDE" => "Y",
                                    "USE_PRODUCT_QUANTITY" => "N",
                                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                                    "COMPATIBLE_MODE" => "Y",
                                    "INCLUDE_SUBSECTIONS" => "Y",
                                    "SHOW_ALL_WO_SECTION" => "N",
                                    "SET_TITLE" => $arParams["SET_TITLE"],
                                ],
                                $component,
                                ["HIDE_ICONS" => "Y"]
                            );
                            ?>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p>Разделы не найдены</p>
                <?php endif;

            } else {
                // ====================================================================
                // СТАНДАРТНЫЙ РЕЖИМ (для остальных разделов)
                // ====================================================================
            
                // Вызов компонента списка элементов с выбранным шаблоном
                $APPLICATION->IncludeComponent(
                    "bitrix:catalog.section",
                    $listTemplate,
                [
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                    "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                    "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                    "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
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
                    "FILTER_NAME" => $arParams["FILTER_NAME"],
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
            } // end else (стандартный режим)
            ?>
        </div>
    </div>

    <!-- Описание раздела -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: $APPLICATION->GetTitle() ?></h2>
            <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
            </div>
        </div>
    </section>

    <!-- Секция "Обратная связь" -->
    <?php $APPLICATION->IncludeComponent("custom:feedback.section", ".default", []); ?>

    <?php
    // ====================================================================
    // ПОПАП ФОРМА ДЛЯ КОНТЕНТНЫХ РАЗДЕЛОВ
    // ====================================================================
    // Параметры попапа на основе ID инфоблока
    $popupParams = [
        IBLOCK_CONTENT_GPU => [
            'POPUP_TITLE' => 'Получить КП на ГПУ',
            'SOURCE_ID' => '42',
            'FORM_NAME' => 'Заказ из каталога ГПУ',
            'METRIC_GOAL' => 'send-gpu-lead',
            'FORM_ID' => 'gpuCartForm',
        ],
        IBLOCK_CONTENT_INVESTICII => [
            'POPUP_TITLE' => 'Получить КП на инвестиции',
            'SOURCE_ID' => '43',
            'FORM_NAME' => 'Заказ из каталога инвестиций',
            'METRIC_GOAL' => 'send-invest-lead',
            'FORM_ID' => 'investCartForm',
        ],
        IBLOCK_CONTENT_CONTAINERS => [
            'POPUP_TITLE' => 'Получить КП на контейнеры',
            'SOURCE_ID' => '44',
            'FORM_NAME' => 'Заказ из каталога контейнеров',
            'METRIC_GOAL' => 'send-container-lead',
            'FORM_ID' => 'conteyneryCartForm',
        ],
        IBLOCK_CONTENT_BUSINESS => [
            'POPUP_TITLE' => 'Получить КП на готовый бизнес',
            'SOURCE_ID' => '41',
            'FORM_NAME' => 'Заказ из каталога предложений для бизнеса',
            'METRIC_GOAL' => 'send-biznes-lead',
            'FORM_ID' => 'businessCartForm',
        ],
    ];

    $currentPopupParams = $popupParams[$IBLOCK_ID] ?? [
        'POPUP_TITLE' => 'Получить КП',
        'SOURCE_ID' => '40',
        'FORM_NAME' => 'Заказ из каталога',
        'METRIC_GOAL' => 'send-catalog-lead',
        'FORM_ID' => 'catalogCartForm',
    ];

    $APPLICATION->IncludeComponent(
        "custom:catalog.popup",
        ".default",
        array_merge($currentPopupParams, [
            'POPUP_CTA' => 'Заполните форму, чтобы оставить заявку на КП. Мы перезвоним вам в ближайшее время',
            'BUTTON_TEXT' => 'Получить КП',
        ])
    );
    ?>
</div>