<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

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

// Используем общую картинку, так как уникальная не была предоставлена.
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.webp';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Актуальный курсы криптовалют");
$APPLICATION->SetTitle("Курсы криптовалют");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Актуальный курсы криптовалют");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "noindex, nofollow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Актуальный курсы криптовалют");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("OG:TYPE", "profile"); // Для контактов хорошо подходит тип "profile" или "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Актуальный курсы криптовалют");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-contacts");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header
?>

<section class="section-contacts container">

    <h1 class="section-contacts__title section-title highlighted-color">
        Курсы криптовалют
    </h1>

    <!-- Панель: вкладки + время обновления -->
    <div class="crypto-toolbar">
        <div class="crypto-filters">
            <button data-filter="all" class="crypto-filter-btn active">Все</button>
            <button data-filter="up" class="crypto-filter-btn">Растут</button>
            <button data-filter="down" class="crypto-filter-btn">Падают</button>
        </div>

        <div class="crypto-updated" id="cryptoUpdated">
            Обновлено: —
        </div>
    </div>

    <!-- Сетка криптовалют / скелетоны -->
    <div class="crypto-grid" id="cryptoList"></div>

    <!-- Кнопка встраивания -->
    <div class="crypto-embed-wrapper">
        <button id="openEmbedPopup" class="crypto-embed-btn">
            Встроить на свой сайт
        </button>
    </div>

    <!-- Подключение CSS & JS именно для этой страницы -->
    <link rel="stylesheet" href="./style.css">
    <script src="./js.js"></script>

</section>

<!-- POPUP: подробная инфа по криптовалюте -->
<div id="cryptoPopup" class="crypto-popup">
    <div class="crypto-popup__overlay"></div>

    <div class="crypto-popup__window">
        <button class="crypto-popup__close" id="cryptoPopupClose">×</button>

        <div class="crypto-popup__header">
            <img id="cryptoPopupLogo" class="crypto-popup__logo" src="" alt="">
            <h2 id="cryptoPopupTitle" class="crypto-popup__title">Crypto</h2>
        </div>

        <div id="cryptoPopupContent" class="crypto-popup__content"></div>

        <div id="cryptoPopupChart" class="crypto-popup__chart"></div>
    </div>
</div>

<!-- POPUP: код для встраивания -->
<div id="embedPopup" class="crypto-popup">
    <div class="crypto-popup__overlay"></div>

    <div class="crypto-popup__window">
        <button class="crypto-popup__close" id="embedPopupClose">×</button>

        <h2 class="crypto-popup__title">Встроить на свой сайт</h2>

        <p style="margin-top:12px;">
            Скопируйте код ниже и вставьте его в HTML вашего сайта.
        </p>

        <textarea id="embedCodeArea" class="crypto-embed-code" readonly></textarea>

        <button id="copyEmbedCode" class="crypto-embed-copy-btn">
            Скопировать код
        </button>
    </div>
</div>







<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>