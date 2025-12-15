<?php
/**
 * Страница раздела каталога - стандартная реализация Битрикса
 * Использует компонент bitrix:catalog.section
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Получаем символьный код раздела из URL
$sectionCode = $_REQUEST["SECTION_CODE"] ?? '';

// Если символьный код раздела не найден, показываем 404
if (empty($sectionCode)) {
    \Bitrix\Iblock\Component\Tools::process404(
        'Раздел не найден',
        true,
        true,
        true
    );
    return;
}

// Устанавливаем общие свойства страницы
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("main_class", "catalog-section-page");

?>

<div class="catalog-page catalog-new container">
    <?php
    // Компонент раздела каталога
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.section",
        "catalog_section",
        array(
            "IBLOCK_TYPE" => "catalog",
            "IBLOCK_ID" => 1, // ID инфоблока ASICS

            // Основные параметры
            "SECTION_ID" => "",
            "SECTION_CODE" => $sectionCode,
            "SECTION_USER_FIELDS" => array(),

            // Сортировка
            "ELEMENT_SORT_FIELD" => "catalog_PRICE_1",
            "ELEMENT_SORT_ORDER" => "desc",
            "ELEMENT_SORT_FIELD2" => "sort",
            "ELEMENT_SORT_ORDER2" => "asc",

            // Фильтрация
            "FILTER_NAME" => "arrFilter",
            "INCLUDE_SUBSECTIONS" => "Y",
            "SHOW_ALL_WO_SECTION" => "Y",

            // URL
            "SECTION_URL" => "/catalog/{$sectionCode}/",
            "DETAIL_URL" => "/catalog/{$sectionCode}/#ELEMENT_CODE#/",
            "ELEMENT_URL" => "/catalog/{$sectionCode}/#ELEMENT_CODE#/",

            // SEO настройки
            "SET_TITLE" => "Y",
            "SET_BROWSER_TITLE" => "Y",
            "SET_META_KEYWORDS" => "Y",
            "SET_META_DESCRIPTION" => "Y",
            "SET_LAST_MODIFIED" => "N",

            // 404 ошибка
            "SET_STATUS_404" => "Y",
            "SHOW_404" => "Y",
            "FILE_404" => "/404.php",

            // Отображение
            "DISPLAY_COMPARE" => "N",
            "PAGE_ELEMENT_COUNT" => "12",
            "LINE_ELEMENT_COUNT" => "3",

            // Свойства
            "PROPERTY_CODE" => array("MANUFACTURER", "CRYPTO", "ALGORITHM", "HASHRATE", "EFFICIENCY", "POWER", "HIT", "AVAILABILITY"),
            "OFFERS_LIMIT" => "5",
            "FIELD_CODE" => array("ID", "NAME", "CODE", "PREVIEW_PICTURE", "DETAIL_PICTURE"),

            // Кеширование
            "CACHE_TYPE" => "A",
            "CACHE_TIME" => "36000000",
            "CACHE_GROUPS" => "Y",

            // Пагинация
            "DISPLAY_TOP_PAGER" => "N",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "PAGER_TEMPLATE" => "main",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "N",
            "PAGER_BASE_LINK_ENABLE" => "N",
        ),
        false
    );
    ?>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
