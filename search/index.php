<?php
/**
 * Страница результатов поиска по каталогу
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Получаем поисковый запрос
$searchQuery = htmlspecialchars(trim($_GET['q'] ?? ''));

// --- ПОДГОТОВКА ДАННЫХ ---
$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];
$pageUrl = $APPLICATION->GetCurPage(false);
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';

// --- SEO ---
$pageTitle = !empty($searchQuery) 
    ? "Результаты поиска: «{$searchQuery}» — GIS Mining" 
    : "Поиск по каталогу — GIS Mining";

$APPLICATION->SetPageProperty("TITLE", $pageTitle);
$APPLICATION->SetTitle("Поиск по каталогу");
$APPLICATION->SetPageProperty("description", "Результаты поиска товаров в каталоге GIS Mining");
$APPLICATION->SetPageProperty("robots", "noindex, follow");

// --- OPEN GRAPH ---
$APPLICATION->SetPageProperty("OG:TITLE", $pageTitle);
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Результаты поиска товаров в каталоге");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

$APPLICATION->SetPageProperty("header_right_class", "color-block");
?>

<div class="search-page catalog-page container">
    <h1 class="search-page__title section-title highlighted-color">
        <?php if (!empty($searchQuery)): ?>
            Результаты поиска: «<?= $searchQuery ?>»
        <?php else: ?>
            Поиск по каталогу
        <?php endif; ?>
    </h1>
    
    <!-- Поле поиска для повторного запроса -->
    <div class="search-page__search-box">
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
                "INITIAL_QUERY" => $searchQuery, // Заполняем поле текущим запросом
            )
        );
        ?>
    </div>
    
    <!-- Результаты поиска через bitrix:search.page -->
    <?php if (!empty($searchQuery)): ?>
        <div class="search-page__results">
            <?php
            $APPLICATION->IncludeComponent(
                "custom:search.page",
                ".default",
                array(
                    "IBLOCK_IDS" => array(1, 3, 4, 5, 6, 11),
                    "PAGE_RESULT_COUNT" => 20,
                    "CACHE_TIME" => "3600",
                ),
                false
            );
            ?>
        </div>
    <?php else: ?>
        <div class="search-page__empty">
            <p>Введите поисковый запрос для поиска товаров в каталоге</p>
        </div>
    <?php endif; ?>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>

