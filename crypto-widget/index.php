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

    <script src="https://gis-mining.ru/crypto-widget/widget.js"></script>
<div id="gis-crypto"></div>
<script>
    new GisCryptoWidget({
        selector: '#gis-crypto',
        lang: 'ru'
    });
</script>

<!-- Кнопка встраивания -->
<div class="crypto-embed-wrapper">
    <button id="openEmbedPopup" class="crypto-embed-btn">
        Встроить на свой сайт
    </button>
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

    <script>
document.addEventListener("DOMContentLoaded", () => {

    const embedPopup = document.getElementById("embedPopup");
    const embedPopupClose = document.getElementById("embedPopupClose");
    const openEmbedPopupBtn = document.getElementById("openEmbedPopup");
    const embedCodeArea = document.getElementById("embedCodeArea");
    const copyEmbedCodeBtn = document.getElementById("copyEmbedCode");

    // Открыть popup
function openEmbedPopup() {
    const widgetCode =
        '<scr' + 'ipt src="https://gis-mining.ru/crypto-widget/widget.js"></scr' + 'ipt>\n' +
        '<div id="gis-crypto"></div>\n' +
        '<scr' + 'ipt>\n' +
        "  new GisCryptoWidget({ selector: '#gis-crypto', lang: 'ru' });\n" +
        '</scr' + 'ipt>';

    embedCodeArea.value = widgetCode;

    embedPopup.style.display = "block";
    document.body.style.overflow = "hidden";
}



    // Закрыть popup
    function closeEmbedPopup() {
        embedPopup.style.display = "none";
        document.body.style.overflow = "";
    }

    // События
    openEmbedPopupBtn.addEventListener("click", openEmbedPopup);
    embedPopupClose.addEventListener("click", closeEmbedPopup);

    embedPopup.addEventListener("click", e => {
        if (e.target.classList.contains("crypto-popup__overlay")) {
            closeEmbedPopup();
        }
    });

    // Копирование кода
    copyEmbedCodeBtn.addEventListener("click", () => {
        embedCodeArea.select();
        navigator.clipboard.writeText(embedCodeArea.value).then(() => {
            copyEmbedCodeBtn.textContent = "Скопировано!";
            setTimeout(() => copyEmbedCodeBtn.textContent = "Скопировать код", 1500);
        });
    });

});
</script>

<link rel="stylesheet" href="https://gis-mining.ru/crypto-widget/widget.css">







<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>