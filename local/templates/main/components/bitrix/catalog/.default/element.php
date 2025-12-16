<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - tech_catalog
 * Контроллер для детальной страницы товара (element.php)
 * 
 * Используется для ASIC и Videocard разделов
 * Поддерживает переключение на страницу калькулятора доходности
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

// Подключаем необходимые модули
Loader::includeModule('iblock');
Loader::includeModule('catalog');

// ====================================================================
// НАСТРОЙКА СТРАНИЦЫ ДЛЯ ДЕТАЛЬНОЙ СТРАНИЦЫ ТОВАРА
// ====================================================================
// Устанавливаем класс для элемента main в шаблоне сайта
$APPLICATION->SetPageProperty("main_class", "product-page");
// Скрываем стандартный заголовок H1 (будет свой в шаблоне)
$APPLICATION->SetPageProperty("HIDE_TITLE", "Y");

// ====================================================================
// ПРОВЕРКА ФЛАГА КАЛЬКУЛЯТОРА ДОХОДНОСТИ
// ====================================================================
if (isset($_REQUEST['SHOW_CALCULATOR']) && $_REQUEST['SHOW_CALCULATOR'] === 'Y') {
    include(__DIR__ . '/calculator.php');
    return;
}

// ====================================================================
// СТАНДАРТНАЯ ДЕТАЛЬНАЯ СТРАНИЦА ТОВАРА
// ====================================================================

// Вызов компонента детального просмотра товара
$APPLICATION->IncludeComponent(
    "bitrix:catalog.element",
    ".default",
    [
        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
        "META_KEYWORDS" => $arParams["DETAIL_META_KEYWORDS"],
        "META_DESCRIPTION" => $arParams["DETAIL_META_DESCRIPTION"],
        "BROWSER_TITLE" => $arParams["DETAIL_BROWSER_TITLE"],
        "SET_CANONICAL_URL" => $arParams["DETAIL_SET_CANONICAL_URL"],
        "BASKET_URL" => $arParams["BASKET_URL"],
        "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
        "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
        "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
        "CHECK_SECTION_ID_VARIABLE" => "N",
        "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
        "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
        "CACHE_TIME" => $arParams["CACHE_TIME"],
        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
        "SET_TITLE" => $arParams["SET_TITLE"],
        "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
        "MESSAGE_404" => $arParams["~MESSAGE_404"],
        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
        "SHOW_404" => $arParams["SHOW_404"],
        "FILE_404" => $arParams["FILE_404"],
        "PRICE_CODE" => $arParams["PRICE_CODE"],
        "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
        "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
        "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
        "PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"],
        "USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
        "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "LINK_IBLOCK_TYPE" => $arParams["LINK_IBLOCK_TYPE"],
        "LINK_IBLOCK_ID" => $arParams["LINK_IBLOCK_ID"],
        "LINK_PROPERTY_SID" => $arParams["LINK_PROPERTY_SID"],
        "LINK_ELEMENTS_URL" => $arParams["LINK_ELEMENTS_URL"],
        "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
        "OFFERS_FIELD_CODE" => $arParams["DETAIL_OFFERS_FIELD_CODE"],
        "OFFERS_PROPERTY_CODE" => $arParams["DETAIL_OFFERS_PROPERTY_CODE"],
        "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
        "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
        "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
        "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
        "ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
        "ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
        "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
        "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
        "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
        "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
        "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
        "CURRENCY_ID" => $arParams["CURRENCY_ID"],
        "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
        "HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
        "USE_ELEMENT_COUNTER" => $arParams["USE_ELEMENT_COUNTER"],
        "SHOW_DEACTIVATED" => $arParams["SHOW_DEACTIVATED"],
        "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
        "STRICT_SECTION_CHECK" => $arParams["DETAIL_STRICT_SECTION_CHECK"],
        "ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
        "LABEL_PROP" => $arParams["LABEL_PROP"],
        "LABEL_PROP_MOBILE" => $arParams["LABEL_PROP_MOBILE"],
        "LABEL_PROP_POSITION" => $arParams["LABEL_PROP_POSITION"],
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
        "MESS_BTN_COMPARE" => $arParams["~MESS_BTN_COMPARE"],
        "MESS_NOT_AVAILABLE" => $arParams["~MESS_NOT_AVAILABLE"],
        "USE_VOTE_RATING" => $arParams["DETAIL_USE_VOTE_RATING"],
        "VOTE_DISPLAY_AS_RATING" => $arParams["DETAIL_VOTE_DISPLAY_AS_RATING"],
        "USE_COMMENTS" => $arParams["DETAIL_USE_COMMENTS"],
        "BLOG_USE" => $arParams["DETAIL_BLOG_USE"],
        "BLOG_URL" => $arParams["DETAIL_BLOG_URL"],
        "BLOG_EMAIL_NOTIFY" => $arParams["DETAIL_BLOG_EMAIL_NOTIFY"],
        "VK_USE" => $arParams["DETAIL_VK_USE"],
        "VK_API_ID" => $arParams["DETAIL_VK_API_ID"],
        "FB_USE" => $arParams["DETAIL_FB_USE"],
        "FB_APP_ID" => $arParams["DETAIL_FB_APP_ID"],
        "BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"],
        "DISPLAY_NAME" => $arParams["DETAIL_DISPLAY_NAME"],
        "IMAGE_RESOLUTION" => $arParams["DETAIL_IMAGE_RESOLUTION"],
        "PRODUCT_INFO_BLOCK_ORDER" => $arParams["DETAIL_PRODUCT_INFO_BLOCK_ORDER"],
        "PRODUCT_PAY_BLOCK_ORDER" => $arParams["DETAIL_PRODUCT_PAY_BLOCK_ORDER"],
        "ADD_DETAIL_TO_SLIDER" => $arParams["DETAIL_ADD_DETAIL_TO_SLIDER"],
        "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
        "ADD_SECTIONS_CHAIN" => "Y",
        "ADD_ELEMENT_CHAIN" => "N",  // Отключено — добавляем вручную в component_epilog.php
        "DISPLAY_PREVIEW_TEXT_MODE" => $arParams["DETAIL_DISPLAY_PREVIEW_TEXT_MODE"],
        "DETAIL_PICTURE_MODE" => $arParams["DETAIL_DETAIL_PICTURE_MODE"],
        "ADD_TO_BASKET_ACTION" => $arParams["ADD_TO_BASKET_ACTION"],
        "ADD_TO_BASKET_ACTION_PRIMARY" => $arParams["ADD_TO_BASKET_ACTION_PRIMARY"],
        "SHOW_CLOSE_POPUP" => $arParams["SHOW_CLOSE_POPUP"],
        "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
        "COMPARE_PATH" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["compare"],
        "USE_COMPARE_LIST" => "Y",
        "BACKGROUND_IMAGE" => $arParams["DETAIL_BACKGROUND_IMAGE"],
        "COMPATIBLE_MODE" => "Y",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "SET_VIEWED_IN_COMPONENT" => "Y",
        "SHOW_SLIDER" => $arParams["DETAIL_SHOW_SLIDER"],
        "SLIDER_INTERVAL" => $arParams["DETAIL_SLIDER_INTERVAL"],
        "SLIDER_PROGRESS" => $arParams["DETAIL_SLIDER_PROGRESS"],
        "USE_ENHANCED_ECOMMERCE" => $arParams["USE_ENHANCED_ECOMMERCE"],
        "DATA_LAYER_NAME" => $arParams["DATA_LAYER_NAME"],
        "BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"],
    ],
    $component,
    ["HIDE_ICONS" => "Y"]
);

// ====================================================================
// БЛОК "ТОВАРЫ ТОГО ЖЕ ПРОИЗВОДИТЕЛЯ" (SIMILAR PRODUCTS)
// ====================================================================

// Получаем ID/Code текущего товара из переменных
$currentElementId = 0;
$filter = ["IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y"];

if (!empty($arResult["VARIABLES"]["ELEMENT_ID"])) {
    $filter["ID"] = $arResult["VARIABLES"]["ELEMENT_ID"];
} elseif (!empty($arResult["VARIABLES"]["ELEMENT_CODE"])) {
    $filter["CODE"] = $arResult["VARIABLES"]["ELEMENT_CODE"];
}

// Запрашиваем свойство MANUFACTURER
if (!empty($filter["ID"]) || !empty($filter["CODE"])) {
    $res = CIBlockElement::GetList([], $filter, false, false, ["ID", "IBLOCK_ID", "PROPERTY_MANUFACTURER"]);
    if ($ob = $res->GetNextElement()) {
        $fields = $ob->GetFields();
        $props = $ob->GetProperties();

        $currentElementId = $fields["ID"];
        $manufacturerValue = $props["MANUFACTURER"]["VALUE"];
        $manufacturerEnumId = $props["MANUFACTURER"]["VALUE_ENUM_ID"];

        // ID для фильтрации (если список - то ENUM_ID, если нет - VALUE)
        $manuPropFilterId = $manufacturerEnumId ?: $manufacturerValue;

        if (!empty($manuPropFilterId)) {
            global $arrFilterManufacturer;
            $arrFilterManufacturer = [
                "PROPERTY_MANUFACTURER" => $manuPropFilterId,
                "!ID" => $currentElementId
            ];

            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section",
                "similar_products",
                [
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "FILTER_NAME" => "arrFilterManufacturer",
                    "PAGE_ELEMENT_COUNT" => 4,
                    "PRICE_CODE" => $arParams["PRICE_CODE"],
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                    "CACHE_GROUPS" => "Y",
                    "SHOW_ALL_WO_SECTION" => "Y",
                    "HIDE_NOT_AVAILABLE" => "Y",
                    "SET_TITLE" => "N",
                    "SET_BROWSER_TITLE" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "DISPLAY_COMPARE" => "N",
                    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                ],
                $component
            );
        }
    }
}
?>

<?php
// ====================================================================
// БЛОК FAQ
// ====================================================================
$APPLICATION->IncludeComponent(
    "custom:faq.section",
    ".default",
    [],
    $component
);
?>