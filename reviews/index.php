<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Запускаем сессию для чтения просмотренных статей
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- ПОДГОТОВКА ДАННЫХ (НАДЕЖНЫЙ СПОСОБ С ИСПОЛЬЗОВАНИЕМ НАСТРОЕК БИТРИКСА) ---

// Определяем протокол
$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";

// Получаем имя сервера из настроек сайта. Это самый надежный способ.
// Константа SITE_SERVER_NAME определяется на основе поля "URL сервера", которое мы настроили.
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];

// Получаем чистый URL страницы без GET-параметров
$pageUrl = $APPLICATION->GetCurPage(false);

// Собираем полный канонический URL
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;

// Также формируем URL для OG-картинки, чтобы он тоже был правильным
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.webp';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Отзывы о компании — Gis Mining");
$APPLICATION->SetTitle("Отзывы о компании");
$APPLICATION->SetPageProperty("description", "Читайте отзывы клиентов о работе «Gis Mining» на Яндекс Картах, Google Картах, 2ГИС, Отзовик и Авито. Мы ценим мнение каждого клиента и стремимся к улучшению качества наших услуг.");
$APPLICATION->SetPageProperty("keywords", "отзывы gis mining, отзывы о майнинге, размещение майнинг оборудования отзывы");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Отзывы о компании — Gis Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Читайте отзывы клиентов о работе «Gis Mining» на Яндекс Картах, Google Картах, 2ГИС, Отзовик и Авито. Мы ценим мнение каждого клиента и стремимся к улучшению качества наших услуг.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Отзывы о компании — Gis Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Читайте отзывы клиентов о работе «Gis Mining» на Яндекс Картах, Google Картах, 2ГИС, Отзовик и Авито. Мы ценим мнение каждого клиента и стремимся к улучшению качества наших услуг.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-reviews");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД ХЛЕБНЫХ КРОШЕК СО СТАНДАРТНЫМ ШАБЛОНОМ -----
// Хлебные крошки теперь формируются автоматически в header

// Подключаем модуль информационных блоков
if (!CModule::IncludeModule("iblock")) {
    ShowError("Модуль информационных блоков не установлен");
    return;
}
?>

<?php
// Определяем ID инфоблока reviews
$reviewsIblockId = 0;

$iblock = CIBlock::GetList(
    array("SORT" => "ASC"),
    array("CODE" => "reviews", "ACTIVE" => "Y")
)->GetNext();

if ($iblock) {
    $reviewsIblockId = $iblock["ID"];
}
?>

<?php
// Получаем код раздела из параметра URL
$sectionCode = !empty($_GET["SECTION_CODE"]) ? $_GET["SECTION_CODE"] : "";

// Если раздел не указан, делаем редирект на первый раздел (Яндекс)
if (empty($sectionCode) && $reviewsIblockId > 0) {
    // Получаем первый раздел
    $rsFirstSection = CIBlockSection::GetList(
        array("SORT" => "ASC", "NAME" => "ASC"),
        array(
            "IBLOCK_ID" => $reviewsIblockId,
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y"
        ),
        false,
        array("CODE"),
        array("nTopCount" => 1)
    );
    
    if ($arFirstSection = $rsFirstSection->GetNext()) {
        $firstSectionCode = $arFirstSection["CODE"];
        LocalRedirect("/reviews/?SECTION_CODE=" . $firstSectionCode);
    }
}

// Создаем фильтр для раздела
$arrFilter = array("ACTIVE" => "Y");
if (!empty($sectionCode)) {
    $arrFilter["SECTION_CODE"] = $sectionCode;
}

if ($reviewsIblockId > 0): ?>
    <?php
    // Основной компонент списка отзывов
    $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "reviews",
        array(
            "IBLOCK_TYPE" => "content",
            "IBLOCK_ID" => $reviewsIblockId,
            "NEWS_COUNT" => "20",
            "SORT_BY1" => "SORT",
            "SORT_ORDER1" => "ASC",
            "SORT_BY2" => "ID",
            "SORT_ORDER2" => "ASC",
            "FILTER_NAME" => "arrFilter",
            "FIELD_CODE" => array(
                0 => "ID",
                1 => "CODE",
                2 => "NAME",
                3 => "PREVIEW_TEXT",
                4 => "PREVIEW_PICTURE",
                5 => "SORT",
                6 => "",
            ),
            "PROPERTY_CODE" => array(
                0 => "REVIEW_DATE",
                1 => "SOURCE_URL",
                2 => "",
            ),
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "AJAX_OPTION_HISTORY" => "N",
            "CACHE_TYPE" => "A",
            "CACHE_TIME" => "36000000",
            "CACHE_FILTER" => "N",
            "CACHE_GROUPS" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "PAGER_TEMPLATE" => "catalog_new",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "N",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "PAGER_BASE_LINK" => "",
            "PAGER_PARAMS_NAME" => "PAGEN_",
            "COMPONENT_TEMPLATE" => "reviews",
            "AJAX_OPTION_ADDITIONAL" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "SET_TITLE" => "N",
            "SET_BROWSER_TITLE" => "N",
            "SET_META_KEYWORDS" => "N",
            "SET_META_DESCRIPTION" => "N",
            "SET_LAST_MODIFIED" => "N",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
            "ADD_SECTIONS_CHAIN" => "N",
            "HIDE_LINK_WHEN_NO_DETAIL" => "Y",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => $sectionCode,
            "INCLUDE_SUBSECTIONS" => "N",
            "STRICT_SECTION_CHECK" => "N",
            "COMPOSITE_FRAME_MODE" => "A",
            "COMPOSITE_FRAME_TYPE" => "AUTO",
            "PAGER_TITLE" => "Отзывы",
            "SET_STATUS_404" => "N",
            "SHOW_404" => "N",
            "MESSAGE_404" => ""
        ),
        false
    );
    ?>

<?php else: ?>

    <div class="no-reviews-message">
        <h2>Отзывы не настроены</h2>
        <p>Пожалуйста, создайте инфоблок "reviews" в административной панели Битрикса.</p>
    </div>

    <style>
        .no-reviews-message {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-reviews-message h2 {
            color: #333;
            margin-bottom: 20px;
        }
    </style>
<?php endif; ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>