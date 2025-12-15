<?php
use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// --- Модули ---
if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    echo "Не удалось подключить модули iblock или catalog";
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

// --- ПОДГОТОВКА ДАННЫХ (НАДЕЖНЫЙ СПОСОБ С ИСПОЛЬЗОВАНИЕМ НАСТРОЕК БИТРИКСА) ---

// Определяем протокол
$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";

// Получаем имя сервера из настроек сайта
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];

// Получаем чистый URL страницы без GET-параметров
$pageUrl = $APPLICATION->GetCurPage(false);

// Собираем полный канонический URL
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;

// URL для OG-картинки
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---
$APPLICATION->SetPageProperty("TITLE", "Купить ASIC-майнер для майнинга криптовалют — каталог GIS Mining 2025");
$APPLICATION->SetTitle("ASIC-майнеры");
$APPLICATION->SetPageProperty("description", "Официальные поставки ASIC-майнеров от Bitmain, WhatsMiner и Goldshell под заказ с оплатой НДС и ГТД РФ. Размещение оборудования на нашем официальном хостинге. GIS Mining - ваш надежный поставщик под ключ.");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH ---
$APPLICATION->SetPageProperty("OG:TITLE", "Купить ASIC-майнер для майнинга криптовалют — каталог GIS Mining 2025");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Официальные поставки ASIC-майнеров от Bitmain, WhatsMiner и Goldshell под заказ с оплатой НДС и ГТД РФ. Размещение оборудования на нашем официальном хостинге. GIS Mining - ваш надежный поставщик под ключ.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD ---
$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Купить ASIC-майнер для майнинга криптовалют — каталог GIS Mining 2025");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Официальные поставки ASIC-майнеров от Bitmain, WhatsMiner и Goldshell под заказ с оплатой НДС и ГТД РФ. Размещение оборудования на нашем официальном хостинге. GIS Mining - ваш надежный поставщик под ключ.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ШАБЛОНА) ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ID инфоблока ASICS
$IBLOCK_ID = 1;

// Получаем данные инфоблока для описания
$iblockData = array();
if ($iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch()) {
    $iblockData = array(
        'NAME' => $iblock['NAME'],
        'DESCRIPTION' => $iblock['DESCRIPTION'],
        'PREVIEW_TEXT' => $iblock['DESCRIPTION'],
    );
}
?>

    <div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
        <h1 class="catalog-page__title section-title highlighted-color">Каталог ASIC майнеров для добычи криптовалют</h1>
        <!-- Компонент живого поиска по каталогу -->
        <div class="catalog-search-mobile">
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
        </div>
        <div class="catalog-page__body">
            <!-- Сайдбар -->
            <aside class="catalog-page__sidebar">
                <!-- АККОРДЕОН 1: КАТЕГОРИИ -->
                <?php
                $APPLICATION->IncludeComponent(
                    "bitrix:catalog.section.list",
                    "iblocks_list",
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

            <!-- АККОРДЕОН 2: ФИЛЬТРЫ -->
            <div class="catalog-accordion">
                <button type="button" class="catalog-accordion__toggle btn btn-primary not-mobile-visible">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_3738_35183)">
                            <path d="M0.5 2.82059H8.11266C8.34209 3.86494 9.27472 4.64897 10.387 4.64897C11.4992 4.64897 12.4318 3.86497 12.6613 2.82059H15.5C15.7761 2.82059 16 2.59672 16 2.32059C16 2.04447 15.7761 1.82059 15.5 1.82059H12.661C12.4312 0.77678 11.4972 -0.00775146 10.387 -0.00775146C9.27609 -0.00775146 8.34262 0.776655 8.11284 1.82059H0.5C0.223875 1.82059 0 2.04447 0 2.32059C0 2.59672 0.223875 2.82059 0.5 2.82059ZM9.05866 2.3219C9.05866 2.32012 9.05869 2.31831 9.05869 2.31653C9.06087 1.58631 9.65672 0.99228 10.387 0.99228C11.1162 0.99228 11.7121 1.5855 11.7152 2.31537L11.7153 2.32272C11.7142 3.05419 11.1187 3.649 10.387 3.649C9.65553 3.649 9.06028 3.05478 9.05863 2.32375L9.05866 2.3219ZM15.5 13.1794H12.661C12.4311 12.1356 11.4972 11.351 10.387 11.351C9.27609 11.351 8.34262 12.1355 8.11284 13.1794H0.5C0.223875 13.1794 0 13.4032 0 13.6794C0 13.9555 0.223875 14.1794 0.5 14.1794H8.11266C8.34209 15.2237 9.27472 16.0077 10.387 16.0077C11.4992 16.0077 12.4318 15.2237 12.6613 14.1794H15.5C15.7761 14.1794 16 13.9555 16 13.6794C16 13.4032 15.7761 13.1794 15.5 13.1794ZM10.387 15.0077C9.65553 15.0077 9.06028 14.4135 9.05863 13.6825L9.05866 13.6807C9.05866 13.6789 9.05869 13.6771 9.05869 13.6753C9.06087 12.9451 9.65672 12.351 10.387 12.351C11.1162 12.351 11.7121 12.9442 11.7152 13.6741L11.7153 13.6814C11.7143 14.413 11.1188 15.0077 10.387 15.0077ZM15.5 7.5H7.88734C7.65791 6.45566 6.72528 5.67165 5.61303 5.67165C4.50078 5.67165 3.56816 6.45566 3.33872 7.5H0.5C0.223875 7.5 0 7.72387 0 8C0 8.27615 0.223875 8.5 0.5 8.5H3.33897C3.56888 9.54378 4.50275 10.3283 5.61303 10.3283C6.72391 10.3283 7.65738 9.5439 7.88716 8.5H15.5C15.7761 8.5 16 8.27615 16 8C16 7.72387 15.7761 7.5 15.5 7.5ZM6.94134 7.99869C6.94134 8.0005 6.94131 8.00228 6.94131 8.00406C6.93912 8.73428 6.34328 9.32831 5.61303 9.32831C4.88381 9.32831 4.28794 8.73509 4.28478 8.00525L4.28469 7.99794C4.28578 7.26637 4.88125 6.67165 5.61303 6.67165C6.34447 6.67165 6.93972 7.26584 6.94137 7.9969L6.94134 7.99869Z" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_3738_35183">
                                <rect width="16" height="16" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                    <span>Фильтры</span>
                    <svg class="icon-arrow" width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 0.5L5 4.5L1 0.5" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="catalog-accordion__content filters">
                    <?php
                    // ВАЖНО: НЕ обнуляем $arrFilter здесь!
                    $APPLICATION->IncludeComponent(
                        "bitrix:catalog.smart.filter",
                        "smart_filter",
                        array(
                            "CACHE_GROUPS" => "Y",
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "A",
                            "CONVERT_CURRENCY" => "N",
                            "DISPLAY_ELEMENT_COUNT" => "Y",
                            "FILTER_NAME" => "arrFilter",        // фильтр заполняется ЗДЕСЬ
                            "FILTER_VIEW_MODE" => "vertical",
                            "HIDE_NOT_AVAILABLE" => "N",
                            "IBLOCK_ID" => $IBLOCK_ID,
                            "IBLOCK_TYPE" => "catalog",
                            "INSTANT_RELOAD" => "Y",
                            "SEF_MODE" => "Y",
                            "SEF_RULE" => "/catalog/asics/filter/#SMART_FILTER_PATH#/apply/",
                            "SMART_FILTER_PATH" => $_REQUEST["SMART_FILTER_PATH"],
                            "COMPONENT_TEMPLATE" => "smart_filter",
                            "POPUP_POSITION" => "left",
                            "COMPONENT_CONTAINER_ID" => "catalog-section-container"
                        ),
                        false
                    );?>
                </div>
            </div>
        </aside>

            <!-- Основной контент -->
            <section class="catalog-page__content section-padding">
                <div class="catalog-content__header">
                    <h2 class="catalog-content__title section-title"><?= $APPLICATION->GetTitle() ?></h2>
                    <!-- Компонент живого поиска по каталогу -->
                    <div class="catalog-search">
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
                    </div>
                </div>

            <div id="catalog-section-container">
            <?php
            // ============================
            // 1) Берём ГЛОБАЛЬНЫЙ $arrFilter, уже собранный smart.filter
            // 2) Делаем выборку → делим на FEATURED / с ценой / без цены
            // 3) Сортируем, считаем пагинацию по отфильтрованным товарам
            // 4) Передаём ID текущей страницы обратно в $arrFilter['ID']
            // ============================
            global $arrFilter;
            if (!is_array($arrFilter)) { $arrFilter = []; }

            $arSelect = ['ID', 'NAME', 'PROPERTY_FEATURED', 'CATALOG_PRICE_1'];
            $baseFilter = array_merge([
                'IBLOCK_ID' => $IBLOCK_ID,
                'ACTIVE' => 'Y',
            ], $arrFilter);

            $res = CIBlockElement::GetList([], $baseFilter, false, false, $arSelect);

            $featured = [];
            $withPrice = [];
            $withoutPrice = [];

            while ($el = $res->GetNext()) {
                $price = (float)($el['CATALOG_PRICE_1']);
                $isFeatured = ($el['PROPERTY_FEATURED_VALUE'] === 'Да' || $el['PROPERTY_FEATURED_VALUE'] === 'Y');

                if ($isFeatured) {
                    $featured[] = $el;
                } elseif ($price > 0) {
                    $withPrice[] = $el;
                } else {
                    $withoutPrice[] = $el;
                }
            }

            if ($withPrice) {
                usort($withPrice, fn($a, $b) => $a['CATALOG_PRICE_1'] <=> $b['CATALOG_PRICE_1']);
            }

            $sorted = array_merge($featured, $withPrice, $withoutPrice);
            $sortedIds = array_column($sorted, 'ID');

            // --- Пагинация (по отфильтрованным элементам) ---
            $perPage = 12;
            $page = isset($_GET['PAGEN_1']) ? max(1, intval($_GET['PAGEN_1'])) : 1;
            $total = count($sortedIds);
            $totalPages = max(1, (int)ceil($total / $perPage));
            $chunks = array_chunk($sortedIds, $perPage);
            $currentIds = $chunks[$page - 1] ?? ($chunks[0] ?? []);

            // Передаём ID текущей страницы в фильтр для catalog.section
            $arrFilter['ID'] = $currentIds;

            // Компонент списка товаров
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section",
                "catalog_section",
                [
                    "IBLOCK_TYPE" => "1",
                    "IBLOCK_ID" => $IBLOCK_ID,

                    "SECTION_ID" => "",
                    "SECTION_CODE" => "",

                    "FILTER_NAME" => "arrFilter",
                    "ELEMENT_SORT_FIELD" => "ID",   // уже отсортировано «снаружи», нам только их вывести
                    "ELEMENT_SORT_ORDER" => "asc",

                    "INCLUDE_SUBSECTIONS" => "Y",
                    "SHOW_ALL_WO_SECTION" => "Y",

                    "SECTION_URL" => "/catalog/asics/",
                    "DETAIL_URL" => "/catalog/asics/#ELEMENT_CODE#/",
                    "ELEMENT_URL" => "/catalog/asics/#ELEMENT_CODE#/",
                    "USE_CODE_INSTEAD_ID" => "Y",
                    "SEF_MODE" => "Y",
                    "SEF_FOLDER" => "/catalog/asics/",

                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                    "CACHE_GROUPS" => "Y",

                    "SET_TITLE" => "N",
                    "SET_BROWSER_TITLE" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_LAST_MODIFIED" => "N",

                    "MESSAGE_404" => "",
                    "SET_STATUS_404" => "N",
                    "SHOW_404" => "N",
                    "FILE_404" => "",

                    "DISPLAY_COMPARE" => "N",

                    "PAGE_ELEMENT_COUNT" => $perPage,
                    "LINE_ELEMENT_COUNT" => "3",

                    "PROPERTY_CODE" => [
                        "MANUFACTURER","CRYPTO","ALGORITHM","HASHRATE","EFFICIENCY","POWER","FEATURED","AVAILABILITY","PRIORITET"
                    ],
                    "FIELD_CODE" => ["ID","NAME","CODE","PREVIEW_PICTURE","DETAIL_PICTURE"],

                    "CACHE_FILTER" => "Y",
                    "CACHE_USE_TAGS" => "N",

                    "AJAX_MODE" => "Y",
                    "AJAX_OPTION_JUMP" => "Y",
                    "AJAX_OPTION_STYLE" => "Y",
                    "AJAX_OPTION_HISTORY" => "Y",

                    "USE_MAIN_ELEMENT_SECTION" => "N",
                    "ACTION_VARIABLE" => "action",
                    "PRODUCT_ID_VARIABLE" => "id",

                    "PRICE_CODE" => ["BASE"],
                    "USE_PRICE_COUNT" => "N",
                    "SHOW_PRICE_COUNT" => "1",
                    "SHOW_PRICE" => "Y",
                    "PRICE_VAT_INCLUDE" => "Y",
                    "CONVERT_CURRENCY" => "N",

                    "SHOW_DISCOUNT_PERCENT" => "N",
                    "SHOW_OLD_PRICE" => "N",
                    "SHOW_RATING" => "N",
                    "SHOW_DISCOUNT_TIME" => "N",
                    "SHOW_CATALOG_QUANTITY" => "N",
                    "SHOW_CATALOG_QUANTITY_CNT" => "N",
                    "SHOW_QUANTITY" => "N",
                    "SHOW_QUANTITY_CNT" => "N",

                    "BASKET_URL" => "/basket/",
                    "USE_PRODUCT_QUANTITY" => "N",
                    "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                    "PRODUCT_PROPS_VARIABLE" => "prop",
                    "PARTIAL_PRODUCT_PROPERTIES" => "N",
                    "PRODUCT_PROPERTIES" => "",

                    "ADD_TO_BASKET_ACTION" => "ADD",
                    "SHOW_PRODUCTS" => "Y",
                    "SHOW_TOP_ELEMENTS" => "N",
                    "SECTION_COUNT_ELEMENTS" => "N",
                    "SECTION_TOP_DEPTH" => "2",
                    "SECTIONS_SHOW_PARENT_NAME" => "Y",
                    "SECTIONS_HIDE_SECTION_NAME" => "N",
                    "SHOW_SECTION_NAME" => "Y",
                    "SHOW_SECTION_PREVIEW_DESCRIPTION" => "Y",
                    "SHOW_SECTION" => "Y",
                    "SHOW_SECTION_BANNER" => "N",
                    "SHOW_SECTION_DESCRIPTION" => "Y",
                    "SHOW_SECTION_DESCRIPTION_PREVIEW" => "N",
                    "SHOW_SECTION_ELEMENTS" => "Y",
                    "SHOW_SECTION_ELEMENTS_PREVIEW" => "N",
                    "SHOW_SECTION_SORT" => "N",

                    "DISPLAY_TOP_PAGER" => "N",
                    "DISPLAY_BOTTOM_PAGER" => "N", // ручная пагинация ниже

                    "PAGER_TEMPLATE" => "catalog_new",
                    "PAGER_TITLE" => "Товары",
                    "PAGER_PARAMS_NAME" => "arrPager",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                    "PAGER_SHOW_ALL" => "N",
                    "PAGER_BASE_LINK_ENABLE" => "N",

                    "COMPONENT_TEMPLATE" => "catalog_section",
                    "HIDE_NOT_AVAILABLE" => "N",
                    "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                    "BACKGROUND_IMAGE" => "-",
                    "SEF_RULE" => "",
                    "SECTION_CODE_PATH" => "",
                    "BROWSER_TITLE" => "-",
                    "META_KEYWORDS" => "-",
                    "META_DESCRIPTION" => "-",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "COMPOSITE_FRAME_MODE" => "A",
                    "COMPOSITE_FRAME_TYPE" => "AUTO",
                    "COMPATIBLE_MODE" => "N",
                    "DISABLE_INIT_JS_IN_COMPONENT" => "N"
                ],
                false
            );

            // --- Ручная пагинация: сохраняем параметры фильтра в ссылках ---
            if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    // сохраняем все текущие GET-параметры (включая фильтр), меняем только PAGEN_1
                    $queryParams = $_GET;
                    unset($queryParams['PAGEN_1']);
                    $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
                    for ($i = 1; $i <= $totalPages; $i++):
                        $queryParams['PAGEN_1'] = $i;
                        $url = $baseUrl . '?' . http_build_query($queryParams);
                        if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($url) ?>"><?= $i ?></a>
                        <?php endif;
                    endfor; ?>
                </div>
            <?php endif; ?>
            </div><!-- /#catalog-section-container -->
        </section>
    </div>

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
        "custom:feedback.section",
        ".default",
        []
    );?>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
