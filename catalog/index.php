<?php
/**
 * Страница раздела ASIC-майнеры
 * Использует стандартные компоненты Битрикса
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

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
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';
// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Купить оборудование для майнинга — Каталог продукции от «Gis Mining»");
$APPLICATION->SetTitle("ASIC-майнеры");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Каталог асик-майнеров от компании «Gis Mining». Доступные цены, высокое качество, широкий ассортимент. ");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Купить оборудование для майнинга — Каталог продукции от «Gis Mining»");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Каталог асик-майнеров от компании «Gis Mining». Доступные цены, высокое качество, широкий ассортимент. ");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Купить оборудование для майнинга — Каталог продукции от «Gis Mining»");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Каталог асик-майнеров от компании «Gis Mining». Доступные цены, высокое качество, широкий ассортимент. ");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header

// ID инфоблока ASICS
$IBLOCK_ID = 1;

// Подключаем модули
if (!CModule::IncludeModule('iblock')) {
    CModule::IncludeModule('iblock');
}
if (!CModule::IncludeModule('catalog')) {
    CModule::IncludeModule('catalog');
}

// Получаем данные инфоблока для описания
$iblockData = array();
if (CModule::IncludeModule('iblock')) {
    $iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch();
    if ($iblock) {
        $iblockData = array(
            'NAME' => $iblock['NAME'],
            'DESCRIPTION' => $iblock['DESCRIPTION'],
            'PREVIEW_TEXT' => $iblock['DESCRIPTION'], // Используем описание инфоблока
        );
    }
}

// Данные для меню инфоблоков теперь получаются через компонент catalog.section.list
?>

    <!-- Хлебные крошки теперь выводятся глобально в header.php -->


<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
    <h1 class="catalog-page__title section-title highlighted-color">Каталог оборудования для майнинга</h1>

    <!-- Компонент живого поиска по каталогу -->
    <?php
    $APPLICATION->IncludeComponent(
        "custom:catalog.search",
        ".default",
        array(
            "IBLOCK_IDS" => array(1, 3, 4, 5, 6, 11),
            "MIN_QUERY_LENGTH" => 2,
            "MAX_RESULTS" => 10,
            "SHOW_PRICE" => "Y",
            "PRICE_CODE" => "BASE",
            "CACHE_TIME" => 3600,
        )
    );
    ?>

    <div class="catalog-page__body">
        <!-- Сайдбар -->
        <aside class="catalog-page__full">
            <!-- АККОРДЕОН 1: КАТЕГОРИИ (Компонент списка инфоблоков) -->
            <?php
            // Компонент списка инфоблоков
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section.list",
                "category",
                array(
                    "IBLOCK_TYPE" => "catalog",
                    "IBLOCK_ID" => $IBLOCK_ID,
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                    "CACHE_GROUPS" => "Y",
                ),
                false
            );
            ?>

    <!-- Описание секции -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: 'ASIC-майнеры' ?></h2>
            <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
            </div>
        </div>
    </section>

    <!-- Секция "Обратная связь" -->
            <?$APPLICATION->IncludeComponent(
                "custom:feedback.section", // Наш неймспейс и имя компонента
                ".default", // Имя шаблона (можно опустить, .default - по умолчанию)
                array(
                    // Здесь пока нет параметров, оставляем пустым
                )
            );?>
        </aside>
    </div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>