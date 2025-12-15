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

$APPLICATION->SetPageProperty("TITLE", "Блог — Gis Mining");
$APPLICATION->SetTitle("Блог");
$APPLICATION->SetPageProperty("description", "В блоге «Gis Mining» вы найдете полезную информацию о майнинге, актуальные новости индустрии, обзоры оборудования и советы по настройке. Подписывайтесь на наш блог, чтобы не пропустить важные обновления и предложения.");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Блог — Gis Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "В блоге «Gis Mining» вы найдете полезную информацию о майнинге, актуальные новости индустрии, обзоры оборудования и советы по настройке. Подписывайтесь на наш блог, чтобы не пропустить важные обновления и предложения.");
$APPLICATION->SetPageProperty("OG:TYPE", "website"); // Для внутренних страниц лучше использовать "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Блог — Gis Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "В блоге «Gis Mining» вы найдете полезную информацию о майнинге, актуальные новости индустрии, обзоры оборудования и советы по настройке. Подписывайтесь на наш блог, чтобы не пропустить важные обновления и предложения.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-blog");
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
// Получаем ID инфоблока "blog" (замените на реальный ID)
$blogIblockId = 8; // Здесь нужно указать реальный ID инфоблока blog

// Определяем ID инфоблока blog
$iblock = CIBlock::GetList(
    array("SORT" => "ASC"),
    array("CODE" => "blog", "ACTIVE" => "Y")
)->GetNext();

if ($iblock) {
    $blogIblockId = $iblock["ID"];
}
?>

<?php 
// Создаем фильтр для раздела
$arrFilter = array();
if (!empty($_GET["SECTION_ID"])) {
    $arrFilter["SECTION_ID"] = $_GET["SECTION_ID"];
}

if ($blogIblockId > 0): ?>
    <?php
    // Основной компонент списка статей
    $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "blog",
        array(
            "IBLOCK_TYPE" => "content",
            "IBLOCK_ID" => $blogIblockId,
            "NEWS_COUNT" => "10",
            "SORT_BY1" => "ACTIVE_FROM",
            "SORT_ORDER1" => "DESC",
            "SORT_BY2" => "SORT",
            "SORT_ORDER2" => "ASC",
            "FILTER_NAME" => "arrFilter",
            "FIELD_CODE" => array(
                0 => "ID",
                1 => "NAME",
                2 => "PREVIEW_TEXT",
                3 => "PREVIEW_PICTURE",
                4 => "ACTIVE_FROM",
                5 => "CODE",
            ),
            "PROPERTY_CODE" => array(
                0 => "TAGS",
            ),
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "/our-blog/#ELEMENT_CODE#/",
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
            "PAGER_SHOW_ALWAYS" => "Y",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "N",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "PAGER_BASE_LINK" => "",
            "PAGER_PARAMS_NAME" => "PAGEN_",
            "SHOW_FEATURED_ARTICLE" => "Y",
            "SHOW_FILTER" => "Y",
        ),
        false
    );
    ?>

    <!-- Секция "Обратная связь" -->
    <?$APPLICATION->IncludeComponent(
        "custom:feedback.section", // Наш неймспейс и имя компонента
        ".default", // Имя шаблона (можно опустить, .default - по умолчанию)
        array(
            // Здесь пока нет параметров, оставляем пустым
        )
    );?>
<?php else: ?>


    <div class="no-blog-message">
        <h2>Блог не настроен</h2>
        <p>Пожалуйста, создайте инфоблок "blog" в административной панели Битрикса.</p>
    </div>
    
    <style>
    .no-blog-message {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .no-blog-message h2 {
        color: #333;
        margin-bottom: 20px;
    }
    </style>
<?php endif; ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>