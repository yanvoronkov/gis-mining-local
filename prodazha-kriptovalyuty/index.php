<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

// --- ПРОВЕРКА ПРАВ ДОСТУПА (ТОЛЬКО ДЛЯ АДМИНИСТРАТОРА) ---
global $USER;
if (!$USER->IsAdmin()) {
    // Редирект на главную или показываем 404
    LocalRedirect("/404.php");
    die();
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

// Уникальная картинка для этой страницы
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/prodazha-kriptovalyuty/hero_image_desktop.jpg';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Легальная продажа криптовалюты с комиссией 1,5% | GIS Mining");
$APPLICATION->SetTitle("Продажа криптовалюты");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Легальная продажа криптовалюты с комиссией 1.5%. Моментальное зачисление на расчетный счет, полный пакет документов по требованиям ФНС. Порог входа от 300 000 руб.");
$APPLICATION->SetPageProperty("keywords", "продажа криптовалюты, обмен криптовалюты, вывод криптовалюты, легальный обмен криптовалюты, продажа биткоин");
$APPLICATION->SetPageProperty("robots", "noindex, nofollow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Легальная продажа криптовалюты с комиссией 1,5% | GIS Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Легальная продажа криптовалюты с комиссией 1.5%. Моментальное зачисление на расчетный счет, полный пакет документов по требованиям ФНС. Порог входа от 300 000 руб.");
$APPLICATION->SetPageProperty("OG:TYPE", "article");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);
$APPLICATION->SetPageProperty("OG:IMAGE:WIDTH", "1200");
$APPLICATION->SetPageProperty("OG:IMAGE:HEIGHT", "630");

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Легальная продажа криптовалюты с комиссией 1,5% | GIS Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Легальная продажа криптовалюты с комиссией 1.5%. Моментальное зачисление на расчетный счет, полный пакет документов по требованиям ФНС. Порог входа от 300 000 руб.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-prodazha-kriptovalyuty");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

?>


<div class="container">

    <!-- Блок "Hero": главный экран -->
    <section class="hero section-padding">
        <header class="hero__header">
            <h1 class="hero__title section-title"><span class="highlighted-color">Легальная</span> продажа<br>
                криптовалюты <span class="hero__commission-badge">Комиссия 1,5%</span></h1>
        </header>

        <div class="hero__content">
            <div class="hero__image-wrapper">
                <picture>
                    <!-- Источник для больших экранов (например, десктопы) -->
                    <source media="(min-width: 1024px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/prodazha-kriptovalyuty/hero_image_desktop.jpg">

                    <!-- Источник для средних экранов (например, планшеты) -->
                    <source media="(min-width: 426px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/prodazha-kriptovalyuty/hero_image_desktop.jpg">

                    <!-- Изображение по умолчанию (для мобильных и браузеров без поддержки <picture>) -->
                    <img class="hero__image"
                         src="<?= SITE_TEMPLATE_PATH ?>/assets/img/prodazha-kriptovalyuty/hero_image_mobile.jpg"
                         alt="Легальная продажа криптовалюты"
                         width="854" height="748">
                </picture>
            </div>

            <ul class="hero__features-list">
                <li class="hero__feature-item">
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <rect width="17" height="17" rx="8.5" fill="#5B61FF"/>
                        <rect width="8.90476" height="8.90476" transform="translate(4.04688 4.04761)"
                              fill="#5B61FF"/>
                        <path
                                d="M7.28924 10.6328L12.856 5.06608C12.9175 5.00453 12.9901 4.97169 13.0737 4.96758C13.1572 4.96358 13.2338 4.99641 13.3034 5.06608C13.3732 5.13586 13.4081 5.21107 13.4081 5.29171C13.4081 5.37245 13.3732 5.44766 13.3034 5.51733L7.64688 11.1777C7.54464 11.2798 7.42542 11.3309 7.28924 11.3309C7.15305 11.3309 7.03384 11.2798 6.93159 11.1777L4.37021 8.61629C4.30865 8.55473 4.27645 8.48158 4.2736 8.39683C4.27076 8.31209 4.30423 8.23482 4.374 8.16504C4.44368 8.09537 4.51883 8.06053 4.59947 8.06053C4.68021 8.06053 4.75542 8.09537 4.82509 8.16504L7.28924 10.6328Z"
                                fill="white"/>
                    </svg>
                    <p class="hero__feature-text">работаем в соответствии с требованиями ФНС и готовим весь
                        комплект закрывающих документов: договор, УПД или акт, платежное поручение</p>
                </li>
                <li class="hero__feature-item">
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <rect width="17" height="17" rx="8.5" fill="#5B61FF"/>
                        <rect width="8.90476" height="8.90476" transform="translate(4.04688 4.04761)"
                              fill="#5B61FF"/>
                        <path
                                d="M7.28924 10.6328L12.856 5.06608C12.9175 5.00453 12.9901 4.97169 13.0737 4.96758C13.1572 4.96358 13.2338 4.99641 13.3034 5.06608C13.3732 5.13586 13.4081 5.21107 13.4081 5.29171C13.4081 5.37245 13.3732 5.44766 13.3034 5.51733L7.64688 11.1777C7.54464 11.2798 7.42542 11.3309 7.28924 11.3309C7.15305 11.3309 7.03384 11.2798 6.93159 11.1777L4.37021 8.61629C4.30865 8.55473 4.27645 8.48158 4.2736 8.39683C4.27076 8.31209 4.30423 8.23482 4.374 8.16504C4.44368 8.09537 4.51883 8.06053 4.59947 8.06053C4.68021 8.06053 4.75542 8.09537 4.82509 8.16504L7.28924 10.6328Z"
                                fill="white"/>
                    </svg>
                    <p class="hero__feature-text">моментальное зачисление на расчетный счет</p>
                </li>
                <li class="hero__feature-item">
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <rect width="17" height="17" rx="8.5" fill="#5B61FF"/>
                        <rect width="8.90476" height="8.90476" transform="translate(4.04688 4.04761)"
                              fill="#5B61FF"/>
                        <path
                                d="M7.28924 10.6328L12.856 5.06608C12.9175 5.00453 12.9901 4.97169 13.0737 4.96758C13.1572 4.96358 13.2338 4.99641 13.3034 5.06608C13.3732 5.13586 13.4081 5.21107 13.4081 5.29171C13.4081 5.37245 13.3732 5.44766 13.3034 5.51733L7.64688 11.1777C7.54464 11.2798 7.42542 11.3309 7.28924 11.3309C7.15305 11.3309 7.03384 11.2798 6.93159 11.1777L4.37021 8.61629C4.30865 8.55473 4.27645 8.48158 4.2736 8.39683C4.27076 8.31209 4.30423 8.23482 4.374 8.16504C4.44368 8.09537 4.51883 8.06053 4.59947 8.06053C4.68021 8.06053 4.75542 8.09537 4.82509 8.16504L7.28924 10.6328Z"
                                fill="white"/>
                    </svg>
                    <p class="hero__feature-text">фиксируем курс и итоговую сумму до начала сделки</p>
                </li>
                <li class="hero__feature-item">
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <rect width="17" height="17" rx="8.5" fill="#5B61FF"/>
                        <rect width="8.90476" height="8.90476" transform="translate(4.04688 4.04761)"
                              fill="#5B61FF"/>
                        <path
                                d="M7.28924 10.6328L12.856 5.06608C12.9175 5.00453 12.9901 4.97169 13.0737 4.96758C13.1572 4.96358 13.2338 4.99641 13.3034 5.06608C13.3732 5.13586 13.4081 5.21107 13.4081 5.29171C13.4081 5.37245 13.3732 5.44766 13.3034 5.51733L7.64688 11.1777C7.54464 11.2798 7.42542 11.3309 7.28924 11.3309C7.15305 11.3309 7.03384 11.2798 6.93159 11.1777L4.37021 8.61629C4.30865 8.55473 4.27645 8.48158 4.2736 8.39683C4.27076 8.31209 4.30423 8.23482 4.374 8.16504C4.44368 8.09537 4.51883 8.06053 4.59947 8.06053C4.68021 8.06053 4.75542 8.09537 4.82509 8.16504L7.28924 10.6328Z"
                                fill="white"/>
                    </svg>
                    <p class="hero__feature-text">порог входа от 300 000 руб.</p>
                </li>
            </ul>
        </div>
    </section>

    <!-- Блок "Процесс работы" -->
    <section class="process section-padding">
        <h2 class="process__title section-title">Как работаем?</h2>
        <ol class="process__steps-list">
            <li class="process__step">
                <div class="process__step-number">1</div>
                <p class="process__step-description">Присылаете какой объем интересно обменять - получаете
                    расчет</p>
            </li>
            <li class="process__step">
                <div class="process__step-number">2</div>
                <p class="process__step-description">Заключаем Договор</p>
            </li>
            <li class="process__step">
                <div class="process__step-number">3</div>
                <p class="process__step-description">Фиксируем условия по Спецификации</p>
            </li>
            <li class="process__step">
                <div class="process__step-number">4</div>
                <p class="process__step-description">Отправляете криптовалюты и получаете рубли на расчетный
                    счет + закрывающие документы</p>
            </li>
        </ol>
    </section>

    <!-- Блок "Форма обратной связи" (Call to Action) -->
    <section class="cta">
        <div class="cta__image-wrapper">
            <picture>
                <!-- Источник для больших экранов (например, десктопы) -->
                <source media="(min-width: 1024px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/prodazha-kriptovalyuty/cta_image_desktop.jpg">

                <!-- Источник для средних экранов (например, планшеты) -->
                <source media="(min-width: 426px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/prodazha-kriptovalyuty/cta_image_desktop.jpg">

                <!-- Изображение по умолчанию (для мобильных и браузеров без поддержки <picture>) -->
                <img class="cta__image"
                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/prodazha-kriptovalyuty/cta_image_mobile.jpg"
                     alt="Иконка криптомонеты с графиком курса"
                     loading="lazy" width="440" height="440">
            </picture>
        </div>

        <div class="cta__form-container">
            <form id="cryptoSaleForm" class="feedback-form js-ajax-form" data-metric-goal="crypto-sale-lead">
                <div class="feedback-form__header">
                    <h3 class="feedback-form__title">Рассчитайте сумму сделки</h3>
                    <p class="feedback-form__subtitle">Оставьте заявку, и наш менеджер свяжется с вами, чтобы
                        зафиксировать курс и подготовить точный расчет</p>
                </div>

                <div class="feedback-form__group">
                    <label for="crypto-phone" class="feedback-form__label">Введите номер телефона</label>
                    <input type="tel" id="crypto-phone" name="client_phone"
                           class="feedback-form__input form-input js-phone-mask"
                           placeholder="+7-000-000-00-00" required>
                </div>

                <div class="feedback-form__group">
                    <label for="crypto-email" class="feedback-form__label">Введите почту</label>
                    <input type="email" id="crypto-email" name="client_email"
                           class="feedback-form__input form-input"
                           placeholder="info@company.ru">
                </div>

                <!-- Скрытые поля -->
                <input type="hidden" name="form_name" value="Продажа криптовалюты">
                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="source_id" value="WEB">
                <input type="hidden" name="page_url" value="">

                <button type="submit" class="feedback-form__button btn btn-primary">Отправить заявку</button>

                <div class="form-group form-check mb-3">
                    <input type="checkbox" id="privacy-policy-about2" name="privacy-policy" class="form-check-input" required>
                    <label for="privacy-policy-about2" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a></label>
                </div>

                <p class="form-error-message" style="color: red; display: none;"></p>
            </form>
        </div>
    </section>

</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>

