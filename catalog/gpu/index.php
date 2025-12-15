<?php
/**
 * Страница раздела "GPU"
 * Использует стандартные компоненты Битрикса
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

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

$APPLICATION->SetPageProperty("TITLE", "Газопоршневые электростанции для майнинга от GIS Mining");
$APPLICATION->SetTitle("GPU");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Автономное энергоснабжение для майнинга и ЦОДов на базе ГПУ с минимальным тарифом за кВч. Подбор, установка, запуск и сопровождение от GIS Mining по всей РФ.");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Газопоршневые электростанции для майнинга от GIS Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Автономное энергоснабжение для майнинга и ЦОДов на базе ГПУ с минимальным тарифом за кВч. Подбор, установка, запуск и сопровождение от GIS Mining по всей РФ.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Газопоршневые электростанции для майнинга от GIS Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Автономное энергоснабжение для майнинга и ЦОДов на базе ГПУ с минимальным тарифом за кВч. Подбор, установка, запуск и сопровождение от GIS Mining по всей РФ.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header

// ID инфоблока "GPU"
$IBLOCK_ID = 5;

// Получаем данные инфоблока для описания
$iblockData = array();
if (CModule::IncludeModule('iblock')) {
    $iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch();
    if ($iblock) {
        $iblockData = array(
            'NAME' => $iblock['NAME'],
            'DESCRIPTION' => $iblock['DESCRIPTION'],
            'PREVIEW_TEXT' => $iblock['DESCRIPTION'],
        );
    }
}
?>

<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
    <h1 class="catalog-page__title section-title highlighted-color">Газопоршневые электростанции (ГПУ) для майнинга</h1>

    <div class="catalog-page__body">
        <!-- Сайдбар -->
        <aside class="catalog-page__sidebar">
            <!-- АККОРДЕОН 1: КАТЕГОРИИ (Компонент списка инфоблоков) -->
            <?php
            // Компонент списка инфоблоков
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section.list",
                "iblocks_list",
                array(
                    "IBLOCK_TYPE" => "catalog",
                    "IBLOCK_ID" => $IBLOCK_ID,
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "36000000",
                    "CACHE_GROUPS" => "Y",
                ),
                false
            );
            ?>
        </aside>

        <!-- Основной контент -->
        <section class="catalog-page__content section-padding">
            <!-- Заголовок -->
            <div class="catalog-content__header">
                <h2 class="catalog-content__title section-title"><?= $APPLICATION->GetTitle() ?></h2>
            </div>

            <!-- Список товаров -->
            <?php
            // Компонент списка товаров
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section",
                "gpu_section",
                array(
                    "IBLOCK_TYPE" => "catalog",
                    "IBLOCK_ID" => $IBLOCK_ID,
                    "SECTION_ID" => "",
                    "SECTION_CODE" => "",
                    "SECTION_USER_FIELDS" => array(),
                    "ELEMENT_SORT_FIELD" => "sort",
                    "ELEMENT_SORT_ORDER" => "asc",
                    "ELEMENT_SORT_FIELD2" => "id",
                    "ELEMENT_SORT_ORDER2" => "desc",
                    "INCLUDE_SUBSECTIONS" => "Y",
                    "SHOW_ALL_WO_SECTION" => "Y",
                    "SECTION_URL" => "/catalog/gpu/",
                    "DETAIL_URL" => "/catalog/product/#ELEMENT_CODE#/",
                    "ELEMENT_URL" => "/catalog/product/#ELEMENT_CODE#/",
                    "USE_CODE_INSTEAD_ID" => "Y",
                    "SEF_MODE" => "Y",
                    "SEF_FOLDER" => "/catalog/gpu/",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "36000000",
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
                    "PAGE_ELEMENT_COUNT" => "12",
                    "LINE_ELEMENT_COUNT" => "3",
                    "PROPERTY_CODE" => array("MANUFACTURER", "CRYPTO", "ALGORITHM", "HASHRATE", "EFFICIENCY"),
                    "OFFERS_LIMIT" => "5",
                    "SECTION_ID_VARIABLE" => "SECTION_ID",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "AJAX_OPTION_HISTORY" => "N",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "CACHE_FILTER" => "Y",
                    "CACHE_USE_TAGS" => "N",
                    "USE_MAIN_ELEMENT_SECTION" => "N",
                    "ACTION_VARIABLE" => "action",
                    "PRODUCT_ID_VARIABLE" => "id",
                    "PRICE_CODE" => array("BASE"),
                    "USE_PRICE_COUNT" => "N",
                    "SHOW_PRICE_COUNT" => "1",
                    "PRICE_VAT_INCLUDE" => "Y",
                    "CONVERT_CURRENCY" => "N",
                    "BASKET_URL" => "/basket/",
                    "USE_PRODUCT_QUANTITY" => "N",
                    "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                    "PRODUCT_PROPS_VARIABLE" => "prop",
                    "PARTIAL_PRODUCT_PROPERTIES" => "N",
                    "PRODUCT_PROPERTIES" => array(),
                    "ADD_TO_BASKET_ACTION" => "ADD",
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
                    // Отключаем пагинацию
                    "DISPLAY_TOP_PAGER" => "N",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                ),
                false
            );
            ?>
        </section>
    </div>

    <!-- Описание секции -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: 'GPU' ?></h2>
            <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
            </div>
        </div>
    </section>

    <!-- Секция "Обратная связь" -->
    <? $APPLICATION->IncludeComponent(
        "custom:feedback.section", // Наш неймспейс и имя компонента
        ".default", // Имя шаблона (можно опустить, .default - по умолчанию)
        array(// Здесь пока нет параметров, оставляем пустым
        )
    ); ?>
</div>

<!-- PopUp -->
<div class="page-business__popup-form popup-form-wrapper js-cart-modal" id="mainPopupFormWrapper"
     style="display: none;">

    <!-- ВАЖНО: У формы теперь есть класс js-ajax-form, чтобы ее "подхватил" form-actions.js -->
    <form class="popup-form js-ajax-form" id="gpuCartForm" data-metric-goal="send-gpu-lead">

        <button type="button" class="popup-form__close-btn menu-close" id="closeMainPopupFormBtn" aria-label="Закрыть">
                    <span>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                        </svg>
                    </span>
        </button>

        <p class="popup-form__text">Получить КП на ГПУ</p>

        <p class="popup-form__cta">Заполните форму, чтобы оставить заявку на КП. Мы перезвоним вам в
            ближайшее время</p>

        <label for="cart_client_name">Ваше имя*:</label>
        <input type="text" name="client_name" id="cart_client_name" placeholder="Имя"
               class="contact-form__input form-input" required aria-label="Имя">

        <label for="cart_client_phone">Телефон*:</label>
        <input type="tel" name="client_phone" id="cart_client_phone" placeholder="Телефон*"
               class="contact-form__input form-input js-phone-mask" required aria-label="Номер телефона">

        <label for="cart_client_email">Email:</label>
        <input type="email" name="client_email" id="gpu_cart_client_email" placeholder="your@email.com (необязательно)"
               class="contact-form__input form-input" aria-label="Электронная почта">

        <input type="hidden" name="source_id" value="42">
        <input type="hidden" name="utm_source">
        <input type="hidden" name="utm_medium">
        <input type="hidden" name="utm_campaign">
        <input type="hidden" name="utm_content">
        <input type="hidden" name="utm_term">
        <!-- Название формы для CRM -->
        <input type="hidden" name="form_name" value="Заказ из каталога ГПУ">
        <input type="hidden" name="page_url" value="">

        <button type="submit" class="btn btn-primary">Получить КП</button>

        <div class="form-group form-check mb-3">
            <input type="checkbox" id="privacy-policy-main" name="privacy-policy"
                   class="form-check-input" required>
            <label for="privacy-policy-main" class="form-check-label">Согласен(а) с <a
                        href="/policy-confidenciales/" target="_blank"><u>политикой
                        конфиденциальности</u></a></label>
        </div>
        <p class="form-error-message" style="color: red; display: none;"></p>
    </form>
</div>

<!-- POPUP SUCCESS -->
<!--<div class="modal-overlay" id="mainSuccessModalOverlay">-->
<!--    <div class="modal" id="mainSuccessModal">-->
<!--        <button class="modal__close-btn" id="closeMainSuccessModalBtn" aria-label="Закрыть">-->
<!--            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                <path d="M2.875 22.2988L22.875 2.71741" stroke="#131315" stroke-linecap="round" />-->
<!--                <path d="M2.875 2.29883L22.875 21.8802" stroke="#131315" stroke-linecap="round" />-->
<!--            </svg>-->
<!---->
<!--        </button>-->
<!--        <div class="modal__icon">-->
<!--            Можно добавить иконку галочки SVG или Font Awesome -->
<!--            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">-->
<!--                <path fill="#10B981"-->
<!--                      d="M25,2C12.318,2,2,12.318,2,25s10.318,23,23,23s23-10.318,23-23S37.682,2,25,2z M37.12,20.432 l-13.068,13.068c-0.196,0.196-0.454,0.294-0.712,0.294s-0.516-0.098-0.712-0.294l-6.918-6.918c-0.392-0.392-0.392-1.028,0-1.42s1.028-0.392,1.42,0l6.206,6.206l12.356-12.356c0.392-0.392,1.028-0.392,1.42,0S37.512,20.04,37.12,20.432z" />-->
<!--            </svg>-->
<!--        </div>-->
<!--        <h3 class="modal__title">Заявка принята!</h3>-->
<!--        <p class="modal__text">Благодарим! Мы свяжемся с вами в ближайшее время.</p>-->
<!--    </div>-->
<!--</div>-->

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
