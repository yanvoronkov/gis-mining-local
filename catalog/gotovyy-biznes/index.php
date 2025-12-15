<?php
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

$APPLICATION->SetPageProperty("TITLE", "Готовый бизнес для майнинга под ключ – GIS Mining");
$APPLICATION->SetTitle("Готовый бизнес");
//$APPLICATION->AddChainItem($APPLICATION->GetTitle());
$APPLICATION->SetPageProperty("description", "Решения под ключ для майнинга Bitcoin, Dogecoin и Litecoin.  Полный цикл от проектирования и оборудования до запуска и поддержки. Высокая окупаемость и надёжность.");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Готовый бизнес по майнингу криптовалют — «Gis Mining»");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Купить готовый бизнес по майнингу криптовалют от «Gis Mining». Установленные фермы, настроенные системы и готовые решения для успешного старта.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Готовый бизнес по майнингу криптовалют — «Gis Mining»");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Купить готовый бизнес по майнингу криптовалют от «Gis Mining». Установленные фермы, настроенные системы и готовые решения для успешного старта.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);


// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// Хлебные крошки теперь формируются автоматически в header

// ID инфоблока для "Готовый бизнес"
$IBLOCK_ID = 3;

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

// Получаем разделы инфоблока для группировки
$sections = [];
if (CModule::IncludeModule('iblock')) {
    $dbSections = CIBlockSection::GetList(
        ['SORT' => 'ASC', 'NAME' => 'ASC'],
        [
            'IBLOCK_ID' => $IBLOCK_ID,
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y',
        ],
        false,
        ['ID', 'NAME', 'CODE', 'DESCRIPTION', 'PICTURE']
    );

    while ($section = $dbSections->Fetch()) {
        $sections[] = [
            'ID' => (int)$section['ID'],
            'NAME' => $section['NAME'],
            'CODE' => $section['CODE'],
            'DESCRIPTION' => $section['DESCRIPTION'],
            'PICTURE' => $section['PICTURE'] ? CFile::GetPath($section['PICTURE']) : null,
        ];
    }
}
?>

<div class="catalog-page container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>" data-catalog-type="business">
    <h1 class="catalog-page__title section-title highlighted-color">Готовый бизнес по майнингу от GIS Mining</h1>

    <div class="catalog-page__body">
        <!-- Сайдбар с инфоблоками -->
        <aside class="catalog-page__sidebar">
            <?php
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
        <section class="catalog-page__content section-padding business-sections-container">
            <?php if (!empty($sections)): ?>
                <?php foreach ($sections as $section): ?>
                    <div class="catalog-page__content section-padding">
                        <h2 class="catalog-content__title"><?= $section['NAME'] ?></h2>
                        <?php if (!empty($section['DESCRIPTION'])): ?>
                            <p class="business-section__description"><?= $section['DESCRIPTION'] ?></p>
                        <?php endif; ?>

                        <?php
                        $APPLICATION->IncludeComponent(
                            "bitrix:catalog.section",
                            "business_section",
                            array(
                                "IBLOCK_TYPE" => "catalog",
                                "IBLOCK_ID" => $IBLOCK_ID,
                                "SECTION_ID" => $section['ID'],
                                "SECTION_CODE" => $section['CODE'],
                                "DISPLAY_TOP_PAGER" => "N",
                                "DISPLAY_BOTTOM_PAGER" => "N",
                                "PAGER_TEMPLATE" => "main",
                                "PAGER_SHOW_ALWAYS" => "N",
                                "PAGER_DESC_NUMBERING" => "N",
                                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                "PAGER_SHOW_ALL" => "N",
                                "PAGER_BASE_LINK_ENABLE" => "N",
                                "SHOW_404" => "N",
                                "MESSAGE_404" => "",
                                "ELEMENT_SORT_FIELD" => "sort",
                                "ELEMENT_SORT_ORDER" => "asc",
                                "ELEMENT_SORT_FIELD2" => "id",
                                "ELEMENT_SORT_ORDER2" => "desc",
                                "PROPERTY_CODE" => array(
                                    0 => "DEVICES_COUNT",
                                    1 => "YEARLY_PROFIT",
                                    2 => "MONTHLY_INCOME",
                                    3 => "PAYBACK_PERIOD",
                                    4 => "CRYPTO",
                                ),
                                "OFFERS_LIMIT" => "5",
                                "SECTION_URL" => "/catalog/gotovyy-biznes/",
                                "DETAIL_URL" => "/catalog/product/#ELEMENT_CODE#/",
                                "ELEMENT_URL" => "/catalog/product/#ELEMENT_CODE#/",
                                "USE_CODE_INSTEAD_ID" => "Y",
                                "SEF_MODE" => "Y",
                                "SEF_FOLDER" => "/catalog/gotovyy-biznes/",
                                "CACHE_TYPE" => "A",
                                "CACHE_TIME" => "36000000",
                                "CACHE_GROUPS" => "Y",
                                "CACHE_FILTER" => "N",
                                "ACTION_VARIABLE" => "action",
                                "PRODUCT_ID_VARIABLE" => "id",
                                "PRICE_CODE" => array(
                                    0 => "BASE",
                                ),
                                "USE_PRICE_COUNT" => "N",
                                "SHOW_PRICE_COUNT" => "1",
                                "PRICE_VAT_INCLUDE" => "Y",
                                "CONVERT_CURRENCY" => "N",
                                "BASKET_URL" => "/personal/basket.php",
                                "USE_PRODUCT_QUANTITY" => "N",
                                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                                "ADD_PROPERTIES_TO_BASKET" => "Y",
                                "PRODUCT_PROPS_VARIABLE" => "prop",
                                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                                "PRODUCT_PROPERTIES" => array(),
                                "CACHE_GROUPS" => "Y",
                                "SECTION_USER_FIELDS" => array(),
                                "SECTION_FIELDS" => array("ID", "NAME", "DESCRIPTION"),
                            ),
                            false
                        );
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Разделы не найдены</p>
            <?php endif; ?>
        </section>
    </div>

    <!-- Описание секции -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: 'Готовый бизнес' ?></h2>
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
    <form class="popup-form js-ajax-form" id="businessCartForm" data-metric-goal="send-biznes-lead">

        <button type="button" class="popup-form__close-btn menu-close" id="closeMainPopupFormBtn" aria-label="Закрыть">
                    <span>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                        </svg>
                    </span>
        </button>

        <p class="popup-form__text">Получить КП на готовый бизнес</p>

        <p class="popup-form__cta">Заполните форму, чтобы оставить заявку на КП. Мы перезвоним вам в
            ближайшее время</p>

        <label for="cart_client_name">Ваше имя*:</label>
        <input type="text" name="client_name" id="cart_client_name" placeholder="Имя"
               class="contact-form__input form-input" required aria-label="Имя">

        <label for="cart_client_phone">Телефон*:</label>
        <input type="tel" name="client_phone" id="cart_client_phone" placeholder="Телефон*"
               class="contact-form__input form-input js-phone-mask" required aria-label="Номер телефона">

        <label for="business_cart_client_email">Email:</label>
        <input type="email" name="client_email" id="business_cart_client_email"
               placeholder="your@email.com (необязательно)" class="contact-form__input form-input"
               aria-label="Электронная почта">


        <input type="hidden" name="source_id" value="41">
        <input type="hidden" name="utm_source">
        <input type="hidden" name="utm_medium">
        <input type="hidden" name="utm_campaign">
        <input type="hidden" name="utm_content">
        <input type="hidden" name="utm_term">
        <!-- Название формы для CRM -->
        <input type="hidden" name="form_name" value="Заказ из каталога предложений для бизнеса">
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
