<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
define("BX_COMPOSITE_FRAME_MODE", false);
define("BX_COMPOSITE_FRAME_TYPE", "DYNAMIC_WITHOUT_STUB");



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

// УКАЗЫВАЕМ УНИКАЛЬНУЮ КАРТИНКУ ДЛЯ ЭТОЙ СТРАНИЦЫ (если она есть, если нет - можно оставить от главной)
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.webp';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Калькулятор майнинга");
$APPLICATION->SetTitle("О компании");
$APPLICATION->SetPageProperty("description", "Калькулятор майнинга");
$APPLICATION->SetPageProperty("keywords", "калькулятор, майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Калькулятор майнинга");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Калькулятор майнинга");
$APPLICATION->SetPageProperty("OG:TYPE", "website"); // Для внутренних страниц лучше использовать "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Калькулятор майнинга");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Калькулятор майнинга");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-about");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД ХЛЕБНЫХ КРОШЕК СО СТАНДАРТНЫМ ШАБЛОНОМ -----
// Хлебные крошки теперь формируются автоматически в header
?>

<h1 class="section-about-seo__main-title section-title highlighted-color visually-hidden">Калькулятор майнинга</h1>

<style>.site-main{overflow-x:hidden;}</style>
<div class="container section-padding">
  <div id="app1760940257" class="app1760940257"></div>
  <?php include __DIR__ . '/assets.php'; ?>
</div>

<?php include __DIR__ . '/calculator-guide.php'; ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
