<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

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

$APPLICATION->SetPageProperty("TITLE", "GIS Mining1 – продажа оборудования для майнинга и размещение на собственных площадках по выгодным тарифам");
$APPLICATION->SetTitle("Главная");
$APPLICATION->SetPageProperty("description", "GIS Mining – ваш надежный партнер в мире майнинга. Продажа асиков, площадки для размещение, строительство ЦОД, инвестиции");
$APPLICATION->SetPageProperty("keywords", "майнинг оборудование, асик майнеры, криптовалюта, биткоин, майнинг ферма");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ (ДЛЯ СОЦСЕТЕЙ) ---
$APPLICATION->SetPageProperty("og:title", "GIS Mining – продажа оборудования для майнинга и размещение на собственных площадках по выгодным тарифам");
$APPLICATION->SetPageProperty("og:description", "GIS Mining – ваш надежный партнер в мире майнинга. Продажа асиков, площадки для размещение, строительство ЦОД, инвестиции");
$APPLICATION->SetPageProperty("og:type", "website");
$APPLICATION->SetPageProperty("og:url", $fullPageUrl);
$APPLICATION->SetPageProperty("og:site_name", "GIS Mining");
$APPLICATION->SetPageProperty("og:locale", "ru_RU");
$APPLICATION->SetPageProperty("og:image", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---
$APPLICATION->SetPageProperty("twitter:card", "summary_large_image");
$APPLICATION->SetPageProperty("twitter:title", "GIS Mining – продажа оборудования для майнинга и размещение на собственных площадках по выгодным тарифам");
$APPLICATION->SetPageProperty("twitter:description", "GIS Mining – ваш надежный партнер в мире майнинга. Продажа асиков, площадки для размещение, строительство ЦОД, инвестиции");
$APPLICATION->SetPageProperty("twitter:image", $ogImageUrl); // <-- Используем локальную переменную

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-home");

// Хлебные крошки теперь формируются автоматически в header
?>

<!-- Секция "Hero" (первый экран) -->
<section class="hero-section">
    <div class="hero-section__background-layer">
        <div class="container hero-section__container">
            <!-- Элементы теперь прямые дети .hero-section__container -->

            <div class="hero-section__image-wrapper">


                <div class="hero-section__adaptive-media-container">
                    <video autoplay loop muted playsinline
                        poster="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/hero_section_image.png">
                        <!--
                                                        Источник №1: HEVC (H.265) в MP4 для Safari (iOS, macOS).
                                                        Это видео с альфа-каналом. Safari выберет его и проигнорирует остальные.
                                                    -->
                        <source src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/hero-section_main_video-new.mp4"
                            type='video/mp4; codecs="hvc1"'>

                        <!--
                                                        Источник №2: WebM для Chrome, Firefox, Edge.
                                                        Это видео с альфа-каналом (если WebM тоже с альфа-каналом).
                                                        Эти браузеры проигнорировали первый <source> и возьмут этот.
                                                    -->
                        <source src="<?= SITE_TEMPLATE_PATH ?>/assets/img/hero-section_main_video.webm"
                            type="video/webm">

                        <!--
                                                        Фолбэк для очень старых браузеров: Картинка.
                                                        Она будет показана, если браузер вообще не понимает тег <video>.
                                                        Также, атрибут 'poster' выше покажет эту картинку, пока видео загружается.
                                                    -->
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/hero_section_image.png"
                            alt="Анимация майнинга GIS Mining" loading="lazy" width="800" height="600">
                    </video>
                </div>


                <!-- Видео для не-iOS устройств -->
                <!-- <video id="heroVideo" class="hero-section__video hero-section__video-desktop" autoplay loop
                        muted playsinline preload="metadata" poster="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/hero_section_image.png">
                        <source src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/hero-section_main_video.mp4" type="video/mp4">
                        Ваш браузер не поддерживает тег video.
                    </video> -->


                <!--Изображение-заглушка для iOS-->
                <!-- <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/hero_section_image.png" alt="Анимация оборудования GIS Mining"
                        class="hero-section__image hero-section__image-ios-fallback" id="heroImageIos"> -->
                <!-- Убедитесь, что это изображение имеет прозрачный фон, если нужно -->
            </div>



            <h1 class="hero-section__title">
                Оборудование для майнинга <br>
                от производителей <br>
                с запуском на АЭС
            </h1>

            <p class="hero-section__subtitle">
                Заказываем майнинг-оборудование напрямую у производителей, легально доставляем в Россию со всей
                необходимой
                документацией и размещаем в собственных дата-центрах на выгодных тарифах
            </p>

            <div class="hero-section__actions">
                <!-- <a href="#popup:marquiz_681f07f0f9beb50019de31fc"
                    class="btn btn-primary hero-section__button">
                    Получить бизнес-модель
                    <svg width="18.000000" height="18.000000" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <defs>
                            <clipPath id="clip5129_112486">
                                <rect rx="0.000000" width="17.250004" height="17.250002"
                                    transform="translate(0.375000 0.375000)" fill="white" fill-opacity="0" />
                            </clipPath>
                        </defs>
                        <rect rx="0.000000" width="17.250004" height="17.250002"
                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                        <g clip-path="url(#clip5129_112486)">
                            <path id="Vector"
                                d="M10.23 4L10.29 3.94C10.55 3.7 10.95 3.68 11.23 3.88L11.3 3.94L16.29 8.53L16.32 8.56L16.35 8.6L16.41 8.68L16.44 8.75L16.47 8.82L16.49 8.92L16.5 9L16.49 9.04L16.48 9.13L16.46 9.2L16.43 9.27L16.39 9.34L16.34 9.41L16.29 9.46L11.3 14.05C11.02 14.31 10.57 14.31 10.29 14.05C10.03 13.82 10.01 13.44 10.23 13.19L10.29 13.12L14.06 9.65L2.96 9.65C2.57 9.65 2.25 9.36 2.25 9C2.25 8.63 2.57 8.34 2.96 8.34L14.06 8.34L10.29 4.87C10.03 4.63 10.01 4.26 10.23 4L10.29 3.94L10.23 4Z"
                                fill="#FFFFFF" fill-opacity="1.000000" fill-rule="evenodd" />
                        </g>
                    </svg>

                </a> -->
                <a href="#" class="btn button--secondary hero-section__button js-open-popup-form" id="openPopupFormBtn"
                    data-metric-goal="open-consult">
                    Получить консультацию
                    <svg width="18.000000" height="18.000000" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect rx="0.000000" width="17.250004" height="17.250002"
                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                        <g clip-path="url(#clip5129_112489)">
                            <path id="Vector1"
                                d="M10.23 4L10.29 3.94C10.55 3.7 10.95 3.68 11.23 3.88L11.3 3.94L16.29 8.53L16.32 8.56L16.35 8.6L16.41 8.68L16.44 8.75L16.47 8.82L16.49 8.92L16.5 9L16.49 9.04L16.48 9.13L16.46 9.2L16.43 9.27L16.39 9.34L16.34 9.41L16.29 9.46L11.3 14.05C11.02 14.31 10.57 14.31 10.29 14.05C10.03 13.82 10.01 13.44 10.23 13.19L10.29 13.12L14.06 9.65L2.96 9.65C2.57 9.65 2.25 9.36 2.25 9C2.25 8.63 2.57 8.34 2.96 8.34L14.06 8.34L10.29 4.87C10.03 4.63 10.01 4.26 10.23 4L10.29 3.94L10.23 4Z"
                                fill="#131315" fill-opacity="1.000000" fill-rule="evenodd" />
                        </g>
                    </svg>
                </a>
            </div>

        </div>
        <div class="hero-section__background-pattern" aria-hidden="true">

        </div>
    </div>
</section>

<!-- Секция Партнерство -->
<div class="partnership-section section-padding change-color-section">
    <div class="container">

        <div class="partnership__legal-info-items">
            <div class="legal-info-items_item">
                <div class="legal-info-items__icon">
                    <svg width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.664062" width="74" height="75" rx="37" fill="white" />
                        <path
                            d="M31.5403 45.375L52.7278 24.1875C53.2278 23.6875 53.8111 23.4375 54.4778 23.4375C55.1445 23.4375 55.7278 23.6875 56.2278 24.1875C56.7278 24.6875 56.9778 25.2812 56.9778 25.9688C56.9778 26.6562 56.7278 27.25 56.2278 27.75L33.2903 50.75C32.7903 51.25 32.207 51.5 31.5403 51.5C30.8736 51.5 30.2903 51.25 29.7903 50.75L19.0403 40C18.5403 39.5 18.3007 38.9062 18.3216 38.2188C18.3424 37.5312 18.6028 36.9375 19.1028 36.4375C19.6028 35.9375 20.1966 35.6875 20.8841 35.6875C21.5716 35.6875 22.1653 35.9375 22.6653 36.4375L31.5403 45.375Z"
                            fill="#3238E0" />
                    </svg>
                </div>
                <p class="legal-info-items__text">
                    СОСТОИМ В РЕЕСТРЕ МАЙНЕРОВ
                </p>
            </div>
            <div class="legal-info-items_item">
                <div class="legal-info-items__icon">
                    <svg width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.664062" width="74" height="75" rx="37" fill="white" />
                        <path
                            d="M31.5403 45.375L52.7278 24.1875C53.2278 23.6875 53.8111 23.4375 54.4778 23.4375C55.1445 23.4375 55.7278 23.6875 56.2278 24.1875C56.7278 24.6875 56.9778 25.2812 56.9778 25.9688C56.9778 26.6562 56.7278 27.25 56.2278 27.75L33.2903 50.75C32.7903 51.25 32.207 51.5 31.5403 51.5C30.8736 51.5 30.2903 51.25 29.7903 50.75L19.0403 40C18.5403 39.5 18.3007 38.9062 18.3216 38.2188C18.3424 37.5312 18.6028 36.9375 19.1028 36.4375C19.6028 35.9375 20.1966 35.6875 20.8841 35.6875C21.5716 35.6875 22.1653 35.9375 22.6653 36.4375L31.5403 45.375Z"
                            fill="#3238E0" />
                    </svg>
                </div>
                <p class="legal-info-items__text">
                    РАБОТАЕМ ПОЛНОСТЬЮ ОФИЦИАЛЬНО
                </p>
            </div>
            <div class="legal-info-items_item">
                <div class="legal-info-items__icon">
                    <svg width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.664062" width="74" height="75" rx="37" fill="white" />
                        <path
                            d="M31.5403 45.375L52.7278 24.1875C53.2278 23.6875 53.8111 23.4375 54.4778 23.4375C55.1445 23.4375 55.7278 23.6875 56.2278 24.1875C56.7278 24.6875 56.9778 25.2812 56.9778 25.9688C56.9778 26.6562 56.7278 27.25 56.2278 27.75L33.2903 50.75C32.7903 51.25 32.207 51.5 31.5403 51.5C30.8736 51.5 30.2903 51.25 29.7903 50.75L19.0403 40C18.5403 39.5 18.3007 38.9062 18.3216 38.2188C18.3424 37.5312 18.6028 36.9375 19.1028 36.4375C19.6028 35.9375 20.1966 35.6875 20.8841 35.6875C21.5716 35.6875 22.1653 35.9375 22.6653 36.4375L31.5403 45.375Z"
                            fill="#3238E0" />
                    </svg>
                </div>
                <p class="legal-info-items__text">
                    ВХОДИМ В ТОП-10 МАЙНИНГ КОМПАНИЙ РФ
                </p>
            </div>
        </div>

        <div class="partnership-grid">
            <!-- Карточка 1: Партнерство с Росэнергоатомом -->
            <div class="partnership-item">
                <div class="partnership-item__icon-wrapper">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-grid_image1.png"
                        alt="Иконка Росатом" class="partnership-item__icon" loading="lazy" width="200" height="200">
                </div>
                <div class="partnership-item__content">
                    <div class="partnership-item__title">СОТРУДНИЧЕСТВО С РОСАТОМОМ</div>
                    <p class="partnership-item__description">
                        ЦОД «Калининский» расположен на территории Калининской АЭС: Тверская область, 380 км от Москвы.
                        Наша площадка для майнинга построена по строгим стандартам атомной отрасли и считается одним из
                        самых безопасных дата-центров России.<br>
                        <strong>Официальные поставки оборудования с полным таможенным оформлением (ГТД РФ) и
                            НДС.</strong><br>
                        Питание от АЭС по выгодным тарифам, стабильный интернет, круглосуточная поддержка и современные
                        системы безопасности
                        гарантируют бесперебойную работу вашего майнинг-оборудования в формате 24/7.
                    </p>
                    <a href="<?= SITE_TEMPLATE_PATH ?>/assets/docs/dogovor_rosatom.pdf" class="partnership-item__link"
                        target="_blank" rel="noopener noreferrer">
                        <svg width="17.367188" height="17.382812" viewBox="0 0 17.3672 17.3828" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs />
                            <path id="Vector2"
                                d="M6.88 13.6L5.33 15.1C4.93 15.51 4.38 15.74 3.8 15.74C3.23 15.74 2.68 15.51 2.27 15.1C1.87 14.7 1.64 14.15 1.64 13.57C1.64 13 1.87 12.45 2.27 12.04L6.25 8.06C6.63 7.67 7.16 7.44 7.71 7.43C8.26 7.41 8.8 7.61 9.21 7.98L9.32 8.07C9.48 8.23 9.71 8.32 9.94 8.32C10.17 8.31 10.39 8.22 10.55 8.05C10.71 7.89 10.8 7.66 10.8 7.43C10.8 7.2 10.71 6.98 10.54 6.82C10.49 6.75 10.44 6.69 10.38 6.63C9.64 5.98 8.67 5.64 7.68 5.68C6.69 5.71 5.75 6.12 5.05 6.82L1.03 10.81C0.34 11.55 -0.02 12.53 0 13.54C0.02 14.55 0.43 15.51 1.14 16.23C1.86 16.94 2.82 17.36 3.83 17.38C4.84 17.4 5.82 17.03 6.57 16.34L8.08 14.87C8.23 14.7 8.31 14.49 8.31 14.27C8.31 14.05 8.23 13.83 8.07 13.67C7.92 13.51 7.71 13.42 7.49 13.4C7.27 13.39 7.05 13.46 6.88 13.6ZM16.24 1.14C15.5 0.41 14.5 0 13.47 0C12.43 0 11.43 0.41 10.7 1.14L9.18 2.62C9.03 2.78 8.95 2.99 8.95 3.21C8.96 3.43 9.04 3.65 9.19 3.81C9.34 3.97 9.55 4.07 9.77 4.08C9.99 4.09 10.21 4.02 10.38 3.88L11.9 2.38C12.3 1.97 12.85 1.75 13.43 1.75C14 1.75 14.55 1.97 14.96 2.38C15.37 2.79 15.59 3.34 15.59 3.91C15.59 4.49 15.37 5.04 14.96 5.44L10.99 9.42C10.6 9.81 10.07 10.04 9.52 10.06C8.97 10.07 8.43 9.87 8.02 9.5L7.91 9.41C7.75 9.25 7.53 9.16 7.29 9.16C7.06 9.17 6.84 9.26 6.67 9.43C6.51 9.59 6.42 9.82 6.42 10.05C6.43 10.28 6.52 10.5 6.69 10.67C6.75 10.73 6.82 10.79 6.89 10.85C7.64 11.5 8.6 11.84 9.59 11.8C10.58 11.77 11.52 11.36 12.22 10.66L16.2 6.68C16.94 5.94 17.35 4.95 17.36 3.91C17.37 2.88 16.96 1.88 16.24 1.14Z"
                                fill="#5B61FF" fill-opacity="1.000000" fill-rule="nonzero" />
                        </svg>
                        <p>Договор с РОСАТОМ
                        </p>
                    </a>
                </div>
            </div>

            <!-- Карточка 2: Оборудование застраховано -->
            <div class="partnership-item">
                <div class="partnership-item__icon-wrapper">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-grid_image2.png"
                        alt="Иконка Росатом" class="partnership-item__icon" loading="lazy" width="200" height="200">
                </div>
                <div class="partnership-item__content">
                    <div class="partnership-item__title">НАШИ ДАТА-ЦЕНТРЫ ЗАСТРАХОВАНЫ</div>
                    <p class="partnership-item__description">
                        Оборудование для майнинга, размещенное в наших майнинг-отелях, застраховано. Страховка
                        защищает от всех актуальных
                        рисков: кражи со взломом, пожар, противоправные действия третьих лиц, падение
                        летательных аппаратов и прочие факторы
                    </p>
                    <a href="<?= SITE_TEMPLATE_PATH ?>/assets/docs/polis_dogovor_strahovanija.webp"
                        class="partnership-item__link" target="_blank" rel="noopener noreferrer">
                        <svg width="17.367188" height="17.382812" viewBox="0 0 17.3672 17.3828" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs />
                            <path id="Vector3"
                                d="M6.88 13.6L5.33 15.1C4.93 15.51 4.38 15.74 3.8 15.74C3.23 15.74 2.68 15.51 2.27 15.1C1.87 14.7 1.64 14.15 1.64 13.57C1.64 13 1.87 12.45 2.27 12.04L6.25 8.06C6.63 7.67 7.16 7.44 7.71 7.43C8.26 7.41 8.8 7.61 9.21 7.98L9.32 8.07C9.48 8.23 9.71 8.32 9.94 8.32C10.17 8.31 10.39 8.22 10.55 8.05C10.71 7.89 10.8 7.66 10.8 7.43C10.8 7.2 10.71 6.98 10.54 6.82C10.49 6.75 10.44 6.69 10.38 6.63C9.64 5.98 8.67 5.64 7.68 5.68C6.69 5.71 5.75 6.12 5.05 6.82L1.03 10.81C0.34 11.55 -0.02 12.53 0 13.54C0.02 14.55 0.43 15.51 1.14 16.23C1.86 16.94 2.82 17.36 3.83 17.38C4.84 17.4 5.82 17.03 6.57 16.34L8.08 14.87C8.23 14.7 8.31 14.49 8.31 14.27C8.31 14.05 8.23 13.83 8.07 13.67C7.92 13.51 7.71 13.42 7.49 13.4C7.27 13.39 7.05 13.46 6.88 13.6ZM16.24 1.14C15.5 0.41 14.5 0 13.47 0C12.43 0 11.43 0.41 10.7 1.14L9.18 2.62C9.03 2.78 8.95 2.99 8.95 3.21C8.96 3.43 9.04 3.65 9.19 3.81C9.34 3.97 9.55 4.07 9.77 4.08C9.99 4.09 10.21 4.02 10.38 3.88L11.9 2.38C12.3 1.97 12.85 1.75 13.43 1.75C14 1.75 14.55 1.97 14.96 2.38C15.37 2.79 15.59 3.34 15.59 3.91C15.59 4.49 15.37 5.04 14.96 5.44L10.99 9.42C10.6 9.81 10.07 10.04 9.52 10.06C8.97 10.07 8.43 9.87 8.02 9.5L7.91 9.41C7.75 9.25 7.53 9.16 7.29 9.16C7.06 9.17 6.84 9.26 6.67 9.43C6.51 9.59 6.42 9.82 6.42 10.05C6.43 10.28 6.52 10.5 6.69 10.67C6.75 10.73 6.82 10.79 6.89 10.85C7.64 11.5 8.6 11.84 9.59 11.8C10.58 11.77 11.52 11.36 12.22 10.66L16.2 6.68C16.94 5.94 17.35 4.95 17.36 3.91C17.37 2.88 16.96 1.88 16.24 1.14Z"
                                fill="#5B61FF" fill-opacity="1.000000" fill-rule="nonzero" />
                        </svg>
                        <p>Полис (договор)
                        </p>
                    </a>
                </div>
            </div>

            <!-- Карточка 3: Тех поддержка высокого уровня -->
            <div class="partnership-item">
                <div class="partnership-item__icon-wrapper">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-grid_image3.png"
                        alt="Иконка Росатом" class="partnership-item__icon" loading="lazy" width="200" height="200">
                </div>
                <div class="partnership-item__content">
                    <div class="partnership-item__title">О НАС</div>
                    <p class="partnership-item__description">
                        GIS Mining – стабильная компания, работающая с 2020 года и обладающая безупречной
                        репутацией. Наша команда обслуживает
                        инфраструктуру для майнинга, демонстрируя устойчивое развитие и прозрачную отчетность.
                        Благодаря официальному статусу,
                        прямым контрактам с энергетиками и размещению на охраняемых объектах, GIS Mining
                        обеспечивает надежность на всех этапах:
                        от покупки асиков до обслуживания оборудования
                    </p>
                    <a href="https://gis-mining.ru/about/" class="partnership-item__link" target="_blank"
                        rel="noopener noreferrer">
                        <svg width="17.367188" height="17.382812" viewBox="0 0 17.3672 17.3828" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs />
                            <path id="Vector4"
                                d="M6.88 13.6L5.33 15.1C4.93 15.51 4.38 15.74 3.8 15.74C3.23 15.74 2.68 15.51 2.27 15.1C1.87 14.7 1.64 14.15 1.64 13.57C1.64 13 1.87 12.45 2.27 12.04L6.25 8.06C6.63 7.67 7.16 7.44 7.71 7.43C8.26 7.41 8.8 7.61 9.21 7.98L9.32 8.07C9.48 8.23 9.71 8.32 9.94 8.32C10.17 8.31 10.39 8.22 10.55 8.05C10.71 7.89 10.8 7.66 10.8 7.43C10.8 7.2 10.71 6.98 10.54 6.82C10.49 6.75 10.44 6.69 10.38 6.63C9.64 5.98 8.67 5.64 7.68 5.68C6.69 5.71 5.75 6.12 5.05 6.82L1.03 10.81C0.34 11.55 -0.02 12.53 0 13.54C0.02 14.55 0.43 15.51 1.14 16.23C1.86 16.94 2.82 17.36 3.83 17.38C4.84 17.4 5.82 17.03 6.57 16.34L8.08 14.87C8.23 14.7 8.31 14.49 8.31 14.27C8.31 14.05 8.23 13.83 8.07 13.67C7.92 13.51 7.71 13.42 7.49 13.4C7.27 13.39 7.05 13.46 6.88 13.6ZM16.24 1.14C15.5 0.41 14.5 0 13.47 0C12.43 0 11.43 0.41 10.7 1.14L9.18 2.62C9.03 2.78 8.95 2.99 8.95 3.21C8.96 3.43 9.04 3.65 9.19 3.81C9.34 3.97 9.55 4.07 9.77 4.08C9.99 4.09 10.21 4.02 10.38 3.88L11.9 2.38C12.3 1.97 12.85 1.75 13.43 1.75C14 1.75 14.55 1.97 14.96 2.38C15.37 2.79 15.59 3.34 15.59 3.91C15.59 4.49 15.37 5.04 14.96 5.44L10.99 9.42C10.6 9.81 10.07 10.04 9.52 10.06C8.97 10.07 8.43 9.87 8.02 9.5L7.91 9.41C7.75 9.25 7.53 9.16 7.29 9.16C7.06 9.17 6.84 9.26 6.67 9.43C6.51 9.59 6.42 9.82 6.42 10.05C6.43 10.28 6.52 10.5 6.69 10.67C6.75 10.73 6.82 10.79 6.89 10.85C7.64 11.5 8.6 11.84 9.59 11.8C10.58 11.77 11.52 11.36 12.22 10.66L16.2 6.68C16.94 5.94 17.35 4.95 17.36 3.91C17.37 2.88 16.96 1.88 16.24 1.14Z"
                                fill="#5B61FF" fill-opacity="1.000000" fill-rule="nonzero" />
                        </svg>
                        <p>Ссылка на статью
                        </p>
                    </a>
                </div>
            </div>

            <!-- Карточка 4: Реестр операторов -->
            <div class="partnership-item">
                <div class="partnership-item__icon-wrapper">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-grid_image4.png"
                        alt="Иконка Росатом" class="partnership-item__icon" loading="lazy" width="200" height="200">
                </div>
                <div class="partnership-item__content">
                    <div class="partnership-item__title">МЫ СОСТОИМ В РЕЕСТРЕ ОПЕРАТОРОВ МАЙНИНГА РФ</div>
                    <p class="partnership-item__description">
                        Компания GIS Mining официально предоставляет инфраструктуру для размещения оборудования
                        майнеров и майнинг-пулов на
                        территории России. Мы официально внесены в реестр операторов майнинговой инфраструктуры,
                        что подтверждает легальность
                        нашей деятельности и дает право оказывать услуги в полном соответствии с действующим
                        законодательством
                    </p>
                    <a href="<?= SITE_TEMPLATE_PATH ?>/assets/docs/uvedomlenie_vnesenija_v_reestr_mainingovoi_infrastructury.pdf"
                        class="partnership-item__link" target="_blank" rel="noopener noreferrer">
                        <svg width="17.367188" height="17.382812" viewBox="0 0 17.3672 17.3828" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs />
                            <path id="Vector5"
                                d="M6.88 13.6L5.33 15.1C4.93 15.51 4.38 15.74 3.8 15.74C3.23 15.74 2.68 15.51 2.27 15.1C1.87 14.7 1.64 14.15 1.64 13.57C1.64 13 1.87 12.45 2.27 12.04L6.25 8.06C6.63 7.67 7.16 7.44 7.71 7.43C8.26 7.41 8.8 7.61 9.21 7.98L9.32 8.07C9.48 8.23 9.71 8.32 9.94 8.32C10.17 8.31 10.39 8.22 10.55 8.05C10.71 7.89 10.8 7.66 10.8 7.43C10.8 7.2 10.71 6.98 10.54 6.82C10.49 6.75 10.44 6.69 10.38 6.63C9.64 5.98 8.67 5.64 7.68 5.68C6.69 5.71 5.75 6.12 5.05 6.82L1.03 10.81C0.34 11.55 -0.02 12.53 0 13.54C0.02 14.55 0.43 15.51 1.14 16.23C1.86 16.94 2.82 17.36 3.83 17.38C4.84 17.4 5.82 17.03 6.57 16.34L8.08 14.87C8.23 14.7 8.31 14.49 8.31 14.27C8.31 14.05 8.23 13.83 8.07 13.67C7.92 13.51 7.71 13.42 7.49 13.4C7.27 13.39 7.05 13.46 6.88 13.6ZM16.24 1.14C15.5 0.41 14.5 0 13.47 0C12.43 0 11.43 0.41 10.7 1.14L9.18 2.62C9.03 2.78 8.95 2.99 8.95 3.21C8.96 3.43 9.04 3.65 9.19 3.81C9.34 3.97 9.55 4.07 9.77 4.08C9.99 4.09 10.21 4.02 10.38 3.88L11.9 2.38C12.3 1.97 12.85 1.75 13.43 1.75C14 1.75 14.55 1.97 14.96 2.38C15.37 2.79 15.59 3.34 15.59 3.91C15.59 4.49 15.37 5.04 14.96 5.44L10.99 9.42C10.6 9.81 10.07 10.04 9.52 10.06C8.97 10.07 8.43 9.87 8.02 9.5L7.91 9.41C7.75 9.25 7.53 9.16 7.29 9.16C7.06 9.17 6.84 9.26 6.67 9.43C6.51 9.59 6.42 9.82 6.42 10.05C6.43 10.28 6.52 10.5 6.69 10.67C6.75 10.73 6.82 10.79 6.89 10.85C7.64 11.5 8.6 11.84 9.59 11.8C10.58 11.77 11.52 11.36 12.22 10.66L16.2 6.68C16.94 5.94 17.35 4.95 17.36 3.91C17.37 2.88 16.96 1.88 16.24 1.14Z"
                                fill="#5B61FF" fill-opacity="1.000000" fill-rule="nonzero" />
                        </svg>
                        <p>Сведения об операторе майнинговой инфраструктуры
                        </p>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Баннер -->
    <!-- <div class="partnership-section__banner container">

            <h2 class="partnership-section__banner-title">Мы в сми</h2>

            <div class="marquee-container container marquee-container-js">

                <div class="marquee-content marquee-content-js">
                    <div class="marquee-item">
                        <a href="https://ria.ru/20250603/evro-2020052054.html" target="_blank"
                            rel="noopener noreferrer nofollow">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_marquee_image1.png" alt="Логотип Риа Новости" loading="lazy" width="150" height="80">
                        </a>
                    </div>
                    <div class="marquee-item">
                        <a href="https://tass.ru/ekonomika/24025323" target="_blank" rel="noopener noreferrer nofollow">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_marquee_image2.png" alt="Логотип ТАСС" loading="lazy" width="150" height="80">
                        </a>
                    </div>
                    <div class="marquee-item">
                        <a href="https://www.rbc.ru/crypto/news/64e5ba809a794753ddc0a810" target="_blank"
                            rel="noopener noreferrer nofollow">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_marquee_image3.png" alt="Логотип РБК" loading="lazy" width="150" height="80">
                        </a>
                    </div>
                    <div class="marquee-item">
                        <a href="https://iz.ru/search?text=Gis-mining&op=" target="_blank"
                            rel="noopener noreferrer nofollow">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_marquee_image4.png" alt="Логотип Известия" loading="lazy" width="150" height="80">
                        </a>
                    </div>
                    <div class="marquee-item">
                        <a href="https://www.kommersant.ru/doc/7737736?query=Gis-mining" target="_blank"
                            rel="noopener noreferrer nofollow">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_marquee_image5.png" alt="Логотип Коммерсант" loading="lazy" width="150" height="80">
                        </a>
                    </div>
                </div>
            </div>
        </div> -->
</div>

<section class="media-mentions container section-padding">
    <div class="media-mentions__container">
        <h2 class="media-mentions__title section-title visually-hidden">СМИ о нас</h2>
        <div class="media-mentions__wrapper">
            <!-- Левая часть с контентом -->
            <div class="media-mentions__content">
                <!-- Блок с логотипами-фильтрами -->
                <div class="media-mentions__filters">
                    <!--
                          Каждая кнопка-логотип имеет data-filter атрибут,
                          который должен совпадать с data-filter у соответствующих слайдов.
                          Внутри два изображения: Ч/Б и цветное.
                        -->
                    <button class="media-filter-btn is-active" data-filter="rbk">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-bw4.png"
                            alt="РБК" class="logo-bw" loading="lazy" width="120" height="60">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-color4.png"
                            alt="РБК" class="logo-color" loading="lazy" width="120" height="60">
                    </button>
                    <button class="media-filter-btn" data-filter="izvestiya">
                        <span class="logo-bw">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/iz-ru-social-preview-bw.png"
                                alt="Известия" loading="lazy" width="120" height="60">
                        </span>
                        <span class="logo-color">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/iz-ru-social-preview-color.png"
                                alt="Известия" loading="lazy" width="120" height="60">
                        </span>
                    </button>
                    <button class="media-filter-btn" data-filter="tacc">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-bw2.png"
                            alt="ТАСС" class="logo-bw" loading="lazy" width="120" height="60">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-color2.png"
                            alt="ТАСС" class="logo-color" loading="lazy" width="120" height="60">
                    </button>
                    <button class="media-filter-btn" data-filter="prime">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-bw3.png"
                            alt="Прайм" class="logo-bw" loading="lazy" width="120" height="60">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-color3.png"
                            alt="Прайм" class="logo-color" loading="lazy" width="120" height="60">
                    </button>
                    <button class="media-filter-btn" data-filter="kommersant">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-bw5.png"
                            alt="Коммерсант" class="logo-bw" loading="lazy" width="120" height="60">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/partnership-section_slider_logo-color5.png"
                            alt="Коммерсант" class="logo-color" loading="lazy" width="120" height="60">
                    </button>
                </div>

                <!-- Слайдер со статьями -->
                <div class="slider-wrapper">
                    <div class="media-mentions__slider">
                        <div class="slider-item" data-filter="rbk">
                            <a href="https://www.rbc.ru/crypto/news/68b01a1b9a794776fcf7d8fd" class="load-more-button"
                                target="_blank" rel="nofollow">
                                <p>Оборудование для майнинга стало доступнее. Топ-модели по доходности</p>
                                <p class="smi_text">Эксперты GIS Mining оценили текущую доходность устройств для добычи
                                    критповалют и дали прогноз до конца 2025 года.
                                    Стоимость добычи биткоина промышленными майнерами в России почти на 35–40% ниже, чем
                                    его покупка по биржевой цене, рассказали «РБК-Крипто» в
                                    GIS Mining. В компании отметили, что в этом году высокотехнологичное современное
                                    вычислительное оборудование стало заметно доступнее для частных и
                                    институциональных заказчиков.</p>
                                <span>
                                    Читать статью полностью
                                    <svg class="safety-section__icon" width="36.000000" height="36.000000"
                                        viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <rect rx="0.000000" width="35.250000" height="35.250000"
                                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0">
                                        </rect>
                                        <g clip-path="url(#clip5129_113345)">
                                            <circle cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                                stroke-opacity="1.000000" stroke-width="0.750000">
                                            </circle>
                                            <path d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                                stroke-linejoin="round" stroke-linecap="round"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                        </div>
                        <div class="slider-item" data-filter="kommersant">
                            <a href="https://www.kommersant.ru/doc/7737736" class="load-more-button" target="_blank"
                                rel="nofollow">
                                <p class="smi_text">По данным Coinmarketcap.com, курс биткойна в конце дня 21 мая
                                    достигал отметки $109,85 тыс…
                                    Как отмечает гендиректор GIS Mining Василий Гиря, «доверие институциональных
                                    инвесторов к первой криптовалюте
                                    остается на очень высоком уровне, несмотря на проявляющиеся признаки замедления
                                    темпов роста экономики США».</p>
                                <span>
                                    Читать статью полностью
                                    <svg class="safety-section__icon" width="36.000000" height="36.000000"
                                        viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <rect rx="0.000000" width="35.250000" height="35.250000"
                                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0">
                                        </rect>
                                        <g clip-path="url(#clip5129_113345)">
                                            <circle cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                                stroke-opacity="1.000000" stroke-width="0.750000">
                                            </circle>
                                            <path d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                                stroke-linejoin="round" stroke-linecap="round"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                        </div>
                        <div class="slider-item" data-filter="tacc">
                            <a href="https://tass.ru/interviews/24295979" class="load-more-button" target="_blank"
                                rel="nofollow">
                                <p>Гендиректор GIS Mining: российская криптоиндустрия все еще проходит период
                                    трансформации</p>
                                <p class="smi_text">Криптовалюты все активнее входят в российскую финансовую систему, а
                                    их регулирование
                                    открывает новые возможности для инвесторов. В интервью ТАСС на полях Петербургского
                                    международного экономического форума
                                    (ПМЭФ) владелец и генеральный директор GIS Mining Василий Гиря рассказал, как планы
                                    по установке экспериментального правового режима,
                                    предложенные Банком России, могут изменить ландшафт майнинга и крипторынка в стране
                                    в ближайшие несколько лет.</p>
                                <span>
                                    Читать статью полностью
                                    <svg class="safety-section__icon" width="36.000000" height="36.000000"
                                        viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <rect rx="0.000000" width="35.250000" height="35.250000"
                                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0">
                                        </rect>
                                        <g clip-path="url(#clip5129_113345)">
                                            <circle cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                                stroke-opacity="1.000000" stroke-width="0.750000">
                                            </circle>
                                            <path d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                                stroke-linejoin="round" stroke-linecap="round"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                        </div>

                        <div class="slider-item" data-filter="izvestiya">
                            <a href="https://iz.ru/1953071/2025-09-11/ekspert-rasskazal-o-perspektivakh-promyshlennogo-maininga-rf-i-tcifrovykh-valiut"
                                class="load-more-button" target="_blank" rel="nofollow">
                                <p>Эксперт рассказал о перспективах промышленного майнинга РФ и цифровых валют</p>
                                <p class="smi_text">Принятие базового регулирования в августе 2024 года открыло новые
                                    горизонты для российского
                                    рынка промышленного майнинга и цифровых валют. В 2025 году рынок фиатных и
                                    криптовалютных инструментов продолжит
                                    интеграцию. Квалифицированные инвесторы в России получили доступ к инвестиционным
                                    продуктам, основанным на цифровых валютах.
                                    Согласно оценкам GIS Mining, объем сегмента инвестиционных инструментов, таких
                                    как монетизация добытой криптовалюты, использование вычислительных мощностей
                                    майнинговых дата-центров,
                                    а также ПИФы, ЦФА и бессрочные цифровые финансовые активы, может превысить 2 трлн
                                    рублей в течение следующего года.
                                </p>
                                <span>
                                    Читать статью полностью
                                    <svg class="safety-section__icon" width="36.000000" height="36.000000"
                                        viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <rect rx="0.000000" width="35.250000" height="35.250000"
                                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0">
                                        </rect>
                                        <g clip-path="url(#clip5129_113345)">
                                            <circle cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                                stroke-opacity="1.000000" stroke-width="0.750000">
                                            </circle>
                                            <path d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                                stroke-linejoin="round" stroke-linecap="round"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                        </div>

                        <div class="slider-item" data-filter="prime">
                            <a href="https://1prime.ru/20250623/kripto-858811138.html" class="load-more-button"
                                target="_blank" rel="nofollow">
                                <p class="smi_text">Спрос на новые майнинговые дата-центры мощностью от нескольких мВт
                                    до 100
                                    мВт и оборудование для энергоемких
                                    блокчейн-вычислений в России бьет рекорды. Об этом агентству "Прайм" сообщил
                                    Василий Гиря, владелец и генеральный
                                    директор GIS Mining
                                </p>
                                <span>
                                    Читать статью полностью
                                    <svg class="safety-section__icon" width="36.000000" height="36.000000"
                                        viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <rect rx="0.000000" width="35.250000" height="35.250000"
                                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0">
                                        </rect>
                                        <g clip-path="url(#clip5129_113345)">
                                            <circle cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                                stroke-opacity="1.000000" stroke-width="0.750000">
                                            </circle>
                                            <path d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                                stroke-linejoin="round" stroke-linecap="round"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                        </div>
                        <!-- И так далее для всех статей -->
                    </div>
                </div>
            </div>

            <!-- Правая часть с фото основателя -->
            <div class="media-mentions__founder-photo">
                <picture>
                    <!-- 1. Для больших экранов (> 1024px): -->
                    <source media="(min-width: 1024px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/media-mentions-section_seo-img_desktop.webp"
                        type="image/webp">
                    <source media="(min-width: 1024px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/media-mentions-section_seo-img_desktop.jpg"
                        type="image/jpeg">

                    <!-- 2. Для средних экранов (768px - 1023px): -->
                    <source media="(min-width: 446px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/media-mentions-section_seo-img_desktop.webp"
                        type="image/webp">
                    <source media="(min-width: 446px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/media-mentions-section_seo-img_desktop.jpg"
                        type="image/jpeg">

                    <!--3. Для мобильных экранов (< 767px): -->
                    <source
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/media-mentions-section_seo-img_mobile.webp"
                        type="image/webp">

                    <!-- Изображение по умолчанию (для мобильных в старых браузерах)
                                                 и обязательный фолбэк для всех браузеров, не поддерживающих <picture>. -->
                    <img class="section-about-seo__image"
                        src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/media-mentions-section_seo-img_mobile.jpg"
                        alt="Фото SEO GIS Mining" loading="lazy" width="400" height="300">
                </picture>
            </div>
        </div>
    </div>
</section>

<!-- Секция "GIS Mining - проверенная компания" -->
<section class="company-intro-section section-padding">
    <div class="container company-intro-grid">
        <div class="company-intro-section__video-wrapper wrapper-video">
            <!--                <video id="myFeatureVideo" class="wrapper-video__feature-video" width="640" height="360" muted loop-->
            <!--                       playsinline preload="metadata" poster="--><?php //= SITE_TEMPLATE_PATH ?><!--/assets/img/home/company-intro-section_image-poster.png" controls>-->
            <!--                    <source src="--><?php //= SITE_TEMPLATE_PATH ?><!--/assets/img/home/welcome_gis.webm" type="video/webm">-->
            <!--                </video>-->
            <div style="position: relative; padding-top: 56.25%; width: 100%"><iframe
                    src="https://kinescope.io/embed/oG8qwFQJ1VZtw3MEMRpSYK"
                    allow="autoplay; fullscreen; picture-in-picture; encrypted-media; gyroscope; accelerometer; clipboard-write; screen-wake-lock;"
                    allowfullscreen
                    style="border: none; position: absolute; width: 100%; height: 100%; top: 0; left: 0;"></iframe>
            </div>

        </div>
        <div class="company-intro__text-block">
            <h2>GIS Mining – проверенная компания</h2>
            <p>GIS Mining – это современная ИТ-компания, занимающаяся продажей оборудования для промышленного
                майнинга, строительством
                и эксплуатацией data-центров, ремонтом майнинг-оборудования и IT-разработками</p>
            <p>Специализируемся на официальных поставках майнинг-оборудования с полным пакетом документов (ГТД РФ, НДС)
                для российского рынка.</p>
            <p>Основная специализация нашей команды – прямой импорт оборудования из Китая без посредников, а
                также строительство и
                эксплуатация инфраструктурных объектов для последующего размещения майнинговых ферм в России</p>
            <p>Прямые контракты с такими стратегическими партнерами, как АО «Концерн Росэнергоатом», позволяют
                нам размещать все
                оборудование в собственных дата-центрах на территории особо охраняемых объектов. Клиентам
                доступны выгодные тарифы и
                максимально возможный Up-time</p>
            <a href="https://www.rusprofile.ru/id/1207700422104" class="btn" target="_blank"
                rel="noopener noreferrer nofollow">
                <svg width="15.183594" height="15.184082" viewBox="0 0 15.1836 15.1841" fill="none"
                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs />
                    <path id="Vector6"
                        d="M6.21 8.97L0.54 6.67C0.35 6.6 0.21 6.48 0.13 6.33C0.04 6.18 0 6.02 0 5.86C0 5.7 0.04 5.54 0.14 5.39C0.23 5.24 0.37 5.12 0.57 5.05L14 0.06C14.17 -0.01 14.34 -0.02 14.5 0.02C14.66 0.06 14.8 0.14 14.91 0.26C15.03 0.38 15.11 0.52 15.16 0.68C15.2 0.84 15.18 1 15.11 1.18L10.12 14.61C10.05 14.8 9.94 14.94 9.78 15.04C9.63 15.13 9.48 15.18 9.32 15.18C9.16 15.18 9 15.14 8.84 15.05C8.69 14.96 8.58 14.82 8.5 14.63L6.21 8.97ZM9.27 11.9L12.82 2.36L3.28 5.9L7.57 7.61L9.27 11.9Z"
                        fill="#5B61FF" fill-opacity="1.000000" fill-rule="nonzero" />
                </svg>
                <p>Информация о нашем Юр. лице</p>
            </a>
        </div>
    </div>
</section>

<!-- Секция "Размещение в наших дата-центрах" -->
<section class="placement-section section-padding">
    <div class="container">
        <div class="placement-section__wrapper">
            <!-- Заголовок и ссылка "Подробнее" (общие для секции) -->
            <div class="section-header placement-section__header">
                <h2 class="section-title">Размещение <br class="placement-section__desktop-br"> в
                    дата-центре
                </h2>
                <a href="https://gis-mining.ru/razmeschenie/"
                    class="section-header__link placement-section__header-link-desktop">
                    Подробнее
                    <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect id="Btn" rx="0.000000" width="35.250000" height="35.250000"
                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                        <g clip-path="url(#clip5129_113345)">
                            <circle id="Ellipse1" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                stroke-opacity="1.000000" stroke-width="0.750000" />
                            <path id="Vector7" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                stroke-linejoin="round" stroke-linecap="round" />
                        </g>
                    </svg>
                </a>
            </div>

            <!-- Основной грид для контента и галереи -->
            <div class="placement-section__main-grid">
                <div class="placement-section__content">
                    <div class="placement-section__partner_items">
                        <div class="placement-section__partner_item">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement-section_rosatom-logo.png"
                                alt="Логотип Росатом" class="placement-section__partner-logo" loading="lazy" width="150"
                                height="80">
                        </div>
                        <div class="placement-section__partner_item">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement-section_atomenegrosbit-logo.png"
                                alt="Логотип Атомэнергосбыт" class="placement-section__partner-logo" loading="lazy"
                                width="150" height="80">
                        </div>
                        <div class="placement-section__partner_item">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement-section_atomedata-logo.png"
                                alt="Логотип Атомдатацентр Росатом" class="placement-section__partner-logo"
                                loading="lazy" width="150" height="80">
                        </div>
                    </div>

                    <p class="placement-section__description">
                        Обеспечиваем безопасное и выгодное размещение оборудования для майнинга в дата-центре на
                        нашей площадке. У нас соблюдены
                        все нормы электробезопасности и стандарты атомной отрасли. Обеспечиваем безопасное и
                        выгодное размещение оборудования
                        для майнинга в дата-центре, где соблюдены все нормы электробезопасности и стандарты
                        атомной отрасли
                    </p>
                    <div class="placement-section__features-list">
                        <div class="placement-section__feature-item">Физическая, пожарная и электробезопасность
                        </div>
                        <div class="placement-section__feature-item">Максимальная юридическая чистота и работа в
                            рамках реестра майнинга РФ</div>
                        <div class="placement-section__feature-item">Круглосуточная поддержка клиентов</div>
                        <div class="placement-section__uptime-banner">
                            <h3 class="uptime-banner__title">Максимальный Up time</h3>
                            <p class="uptime-banner__text">Мы подтверждаем реальный UP-TIME на встрече в нашем
                                офисе
                            </p>
                        </div>
                    </div>

                </div>

                <!-- <div class="placement-section__gallery">
                        <div class="swiper js-swiper-slider placement-gallery-slider" data-swiper-options='{
                            "slidesPerView": 1.2,
                            "spaceBetween": 15,
                            "centeredSlides": true,
                            "loop": true,
                            "autoplay": {
                            "delay": 3000,
                            "disableOnInteraction": false,
                            "pauseOnMouseEnter": true
                            },
                            "pagination": {
                            "el": "none",
                            "clickable": true
                            }
                            }' data-swiper-destroy-breakpoint="768">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide placement-gallery__card"><img
                                        src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image1.png" alt="Фото дата-центра 1"
                                        loading="lazy" width="400" height="300">
                                </div>
                                <div class="swiper-slide placement-gallery__card"><img
                                        src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image2.png" alt="Фото дата-центра 2"
                                        loading="lazy" width="400" height="300">
                                </div>
                                <div class="swiper-slide placement-gallery__card"><img
                                        src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image3.png" alt="Фото дата-центра 3"
                                        loading="lazy" width="400" height="300">
                                </div>
                                <div class="swiper-slide placement-gallery__card"><img
                                        src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image4.png" alt="Фото дата-центра 4"
                                        loading="lazy" width="400" height="300">
                                </div>
                            </div>
                            <div class="swiper-pagination placement-gallery-slider__pagination"></div>
                        </div>

                        <a href="https://gis-mining.ru/razmeschenie/"
                            class="section-header__link placement-section__header-link-mobile">
                            Подробнее
                            <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect rx="0.000000" width="35.250000" height="35.250000"
                                    transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                                <g clip-path="url(#clip5129_113345)">
                                    <circle cx="18.000000" cy="18.000000" r="17.625000"
                                        stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000" />
                                    <path                                         d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                        stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                        stroke-linejoin="round" stroke-linecap="round" />
                                </g>
                            </svg>

                        </a>
                    </div> -->

                <div class="placement-section__gallery-test">
                    <div class="native-slider js-native-slider placement-gallery-slider slider--centered slider--partial-view"
                        data-native-slider-options='{
                                    "loop": true,
                                    "autoplay": { "delay": 3000, "pauseOnMouseEnter": true },
                                    "pagination": { "el": ".placement-gallery-slider__pagination", "clickable": false },
                                    "navigation": {"nextEl": ".slider-button-next", "prevEl": ".slider-button-prev"}
                                 }'>

                        <div class="native-slider__wrapper">
                            <div class="native-slider__slide placement-gallery__card"><img
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image1.png"
                                    alt="Фото дата-центра 1" loading="lazy" width="400" height="300">
                            </div>
                            <div class="native-slider__slide placement-gallery__card"><img
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image2.png"
                                    alt="Фото дата-центра 2" loading="lazy" width="400" height="300">
                            </div>
                            <div class="native-slider__slide placement-gallery__card"><img
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image3.png"
                                    alt="Фото дата-центра 3" loading="lazy" width="400" height="300">
                            </div>
                            <div class="native-slider__slide placement-gallery__card"><img
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/placement_gallery_image4.png"
                                    alt="Фото дата-центра 4" loading="lazy" width="400" height="300">
                            </div>
                        </div>

                        <div class="native-slider__pagination placement-gallery-slider__pagination">
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                        </div>
                        <!--                            <div class="native-slider__nav slider-button-prev">PREV</div>-->
                        <!--                            <div class="native-slider__nav slider-button-next">NEXT</div>-->
                    </div>

                    <a href="https://gis-mining.ru/razmeschenie/"
                        class="section-header__link placement-section__header-link-mobile">
                        Подробнее
                        <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect rx="0.000000" width="35.250000" height="35.250000"
                                transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                            <g clip-path="url(#clip5129_113345)">
                                <circle cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                    stroke-opacity="1.000000" stroke-width="0.750000" />
                                <path d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12" stroke="#131315"
                                    stroke-opacity="1.000000" stroke-width="0.750000" stroke-linejoin="round"
                                    stroke-linecap="round" />
                            </g>
                        </svg>

                    </a>
                </div>

                <!-- Блок с формой заявки (располагается под всем вышеперечисленным) -->
                <div class="placement-section__cta-form">
                    <h3 class="cta-form__title">Прямая закупка электроэнергии у генерирующих компаний
                        гарантирует минимальный тариф</h3>
                    <!-- Обычная форма 1 -->
                    <form id="contactForm1" class="contact-form-on-page js-ajax-form"
                        data-metric-goal="data-centr-lead">
                        <div class="form-group">
                            <input type="tel" name="client_phone" class="js-phone-mask form-input"
                                placeholder="Телефон*" required>
                        </div>
                        <input type="hidden" name="form_name" value="Размещение в нашем дата-центре">
                        <!-- Универсальные UTM поля (добавить, если их еще нет здесь) -->
                        <input type="hidden" name="utm_source">
                        <input type="hidden" name="utm_medium">
                        <input type="hidden" name="utm_campaign">
                        <input type="hidden" name="utm_content">
                        <input type="hidden" name="utm_term">
                        <input type="hidden" name="source_id" value="24">
                        <input type="hidden" name="page_url" value="">
                        <button type="submit" class="btn btn-primary contact-form-submit-btn">Оставить
                            заявку</button>
                        <div class="form-group form-check mb-3">
                            <input type="checkbox" id="privacy-policy-main" name="privacy-policy"
                                class="form-check-input" required>
                            <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a
                                    href="/policy-confidenciales/" target="_blank"><u>политикой
                                        конфиденциальности</u></a> и с <a href="/soglasie-s-obrabotkoy/"
                                    target="_blank"><u>обработкой персональных данных</u></a></label>
                        </div>
                        <p class="form-error-message" style="color: red; display: none;"></p>
                    </form>
                </div>
            </div>



        </div>
    </div>
</section>

<!-- Секция "Пакетные предложения" -->
<section class="package-deals section-padding">
    <div class="container">

        <div class="swiper js-swiper-slider package-deals-slider" data-swiper-options='{
                    "slidesPerView": 1.2,
                    "spaceBetween": 15,
                    "centeredSlides": true,
                    "loop": true,
                    "autoplay": {
                    "delay": 3000,
                    "disableOnInteraction": false,
                    "pauseOnMouseEnter": true
                    },
                    "pagination": {
                    "el": ".package-deals-slider__pagination",
                    "clickable": true
                    }
                    }' data-swiper-destroy-breakpoint="768"> <!-- Уничтожать НАЧИНАЯ с 992px -->
            <div class="swiper-wrapper">

                <div class="swiper-slide package-deal-card" data-href="/catalog/asics/">
                    <!-- Используем свой класс для карточки пакета -->
                    <div class="slider__item">
                        <div class="slider__item_img">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/package-deals_slide_1.png"
                                alt="Асик для майнинга" loading="lazy" width="400" height="300">
                        </div>
                        <div class="slider__item_content">
                            <h3 class="slider__item-title">Продажа<br>Асиков</h3>
                            <div class="slider__item__text">
                                Поставка асиков под заказ с официальной гарантией и полным пакетом документов (ГТД, НДС)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="swiper-slide package-deal-card" data-href="/razmeschenie/">
                    <div class="slider__item">
                        <div class="slider__item_img">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/package-deals_slide_2.png"
                                alt="Фото ферм для майнинга" loading="lazy" width="400" height="300">
                        </div>
                        <div class="slider__item_content">
                            <h3 class="slider__item-title">Размещение<br>и обслуживание</h3>
                            <div class="slider__item__text">
                                Покупка асиков с гарантией, размещение на АЭС и оплата электроэнергии. К вашим
                                услугам персональный менеджер и
                                финансовая модель с консалтингом, юридическая помощь
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide package-deal-card" data-href="/catalog/conteynery/">
                    <div class="slider__item">
                        <div class="slider__item_img">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/package-deals_slide_3.png"
                                alt="Фото фермы для майнинга в разрезе" loading="lazy" width="400" height="300">
                        </div>
                        <div class="slider__item_content">
                            <h3 class="slider__item-title">Готовый<br>бизнес</h3>
                            <div class="slider__item__text">
                                Покупка оборудования для майнинга с контейнером и размещением в data-центре.
                                Обслуживание, юридический консалтинг,
                                фиксированный тариф на электроэнергию. Разработка персональной бизнес-стратегии
                                под ключ
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide package-deal-card" data-href="/newstroitelstvo/">
                    <div class="slider__item">
                        <div class="slider__item_img">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/home/package-deals_slide_4.png"
                                alt="Фото дата центра для майнинга" loading="lazy" width="400" height="300">
                        </div>
                        <div class="slider__item_content">
                            <h3 class="slider__item-title">Инвестиции<br>в инфраструктуру</h3>
                            <div class="slider__item__text">
                                Строительство, наполнение, комплексное обслуживание дата-центра для майнинга.
                                Окупаемость – от 3х лет (с удорожанием
                                стоимости актива)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination package-deals-slider__pagination"></div> <!-- УНИКАЛЬНЫЙ КЛАСС -->
        </div>

    </div>
</section>

<!-- Секция "Сопровождение на всех этапах" -->
<section class="support-stages-section section-padding">
    <div class="container">
        <div class="support-stages__wrapper">
            <div class="section-header">
                <h2 class="section-header__title">Сопровождение клиента <br> на всех этапах</h2>
                <!-- <a href="#" class="section-header__link placement-section__header-link-desktop">
                        Подробнее
                        <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect id="Btn2" rx="0.000000" width="35.250000" height="35.250000"
                                transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                            <g clip-path="url(#clip5129_113345)">
                                <circle id="Ellipse3" cx="18.000000" cy="18.000000" r="17.625000"
                                    stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000" />
                                <path id="Vector9"
                                    d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                    stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                    stroke-linejoin="round" stroke-linecap="round" />
                            </g>
                        </svg>
                    </a> -->
            </div>
            <div class="section-header__text-wrapper">
                <p>Сопровождение клиента на всех этапах</p>
                <p>Работа с GIS Mining – это возможность комфортного майнинга с полным сопровождением наших
                    специалистов: от регистрации
                    юридического лица до сдачи отчетности ФНС</p>
                <p>Юридическое сопровождение и полный пакет документов для бухгалтерии (ГТД, НДС).</p>
                <p>Обеспечиваем безопасное и выгодное размещение оборудования для майнинга в дата-центре на
                    нашей
                    площадке</p>
            </div>

            <div class="support-stages-grid">
                <!-- Карточка 1-->
                <div class="support-stages-item">
                    <div class="support-stages-item__icon-wrapper">
                        <svg width="74" height="76" viewBox="0 0 74 76" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="0.5" width="74" height="75" rx="37" fill="#5B61FF" />
                            <path
                                d="M35.7938 36.108H30.1298V31.276C34.9298 31.276 36.4018 30.572 37.4258 27.692H42.3538V51.5H35.7938V36.108Z"
                                fill="white" />
                        </svg>
                    </div>
                    <div class="support-stages-item__content">
                        <h3 class="support-stages-item__title">Выбор оптимальной бизнес-модели</h3>
                        <p class="support-stages-item__description">
                            Расскажем, какой формат майнинга подходит именно для вас
                        </p>
                    </div>
                </div>

                <!-- Карточка 2-->
                <div class="support-stages-item">
                    <div class="support-stages-item__icon-wrapper">
                        <svg width="74" height="74" viewBox="0 0 74 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="74" height="74" rx="37" fill="#5B61FF" />
                            <path
                                d="M49.4856 50.5H24.7496V47.044C24.7496 43.652 25.1976 40.516 34.1576 37.7L37.9656 36.548C41.1336 35.62 41.9656 34.98 41.9656 33.796C41.9656 32.516 41.1336 31.492 37.1976 31.492C32.5256 31.492 32.1416 34.116 32.0456 35.268H25.1656V35.044C25.1656 31.812 26.7656 26.404 36.3656 26.404H37.8056C46.3176 26.404 48.9416 29.7 48.9416 33.764V34.02C48.9416 38.148 46.6376 39.876 40.0136 41.412C38.7336 41.7 37.3256 42.02 36.2056 42.308C31.5016 43.492 30.6696 44.292 30.6696 44.804H49.4856V50.5Z"
                                fill="white" />
                        </svg>
                    </div>
                    <div class="support-stages-item__content">
                        <h3 class="support-stages-item__title">Подбор оборудования</h3>
                        <p class="support-stages-item__description">
                            Предоставим выгодные условия на покупку техники от ведущих производителей
                        </p>
                    </div>
                </div>

                <!-- Карточка 3-->
                <div class="support-stages-item">
                    <div class="support-stages-item__icon-wrapper">
                        <svg width="74" height="74" viewBox="0 0 74 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="74" height="74" rx="37" fill="#5B61FF" />
                            <path
                                d="M37.313 50.852H35.969C25.889 50.852 24.289 45.156 24.289 42.692V42.404H31.329C31.425 43.044 31.745 45.54 36.801 45.54C41.889 45.54 42.561 43.908 42.561 42.692C42.561 41.444 41.793 40.612 39.361 40.612H31.745V35.78H39.329C41.665 35.78 42.241 34.852 42.241 33.86C42.241 32.74 41.697 31.524 36.737 31.524C31.777 31.524 31.553 33.412 31.457 33.988H24.609V33.636C24.609 31.684 25.761 26.404 35.521 26.404H37.313C46.977 26.404 48.801 29.828 48.801 32.516V32.74C48.801 35.3 47.041 36.548 45.249 37.156C47.297 37.668 49.537 39.364 49.537 42.596V42.82C49.537 46.404 47.393 50.852 37.313 50.852Z"
                                fill="white" />
                        </svg>
                    </div>
                    <div class="support-stages-item__content">
                        <h3 class="support-stages-item__title">Сопровождение запуска фермы</h3>
                        <p class="support-stages-item__description">
                            Подключим и настроим оборудование, обеспечим устойчивую доходность
                        </p>
                    </div>
                </div>

                <!-- Карточка 4-->
                <div class="support-stages-item is-hidden-initially">
                    <div class="support-stages-item__icon-wrapper">
                        <svg width="74" height="75" viewBox="0 0 74 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="0.5" width="74" height="74" rx="37" fill="#5B61FF" />
                            <path
                                d="M23.8218 45.816V41.624L36.8458 27.032H45.5498V40.696H50.6058V45.816H45.5498V51H39.0218V45.816H23.8218ZM39.0218 40.696V31.512L30.8618 40.696H39.0218Z"
                                fill="white" />
                        </svg>
                    </div>
                    <div class="support-stages-item__content">
                        <h3 class="support-stages-item__title">Юридическое сопровождение</h3>
                        <p class="support-stages-item__description">
                            Подключим аппаратуру, настроим оборудование, обеспечим устойчивую доходность в
                            строгом
                            соответствии с действующим
                            законодательством
                        </p>
                    </div>
                </div>

                <!-- Карточка 5-->
                <div class="support-stages-item is-hidden-initially">
                    <div class="support-stages-item__icon-wrapper">
                        <svg width="74" height="75" viewBox="0 0 74 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="0.5" width="74" height="74" rx="37" fill="#5B61FF" />
                            <path
                                d="M37.8994 51.352H36.6194C26.6674 51.352 25.1954 46.328 25.1954 44.344V43.896H32.1394C32.2354 44.44 32.9394 46.04 37.3234 46.04C41.4514 46.04 42.7634 44.696 42.7634 43.096V42.904C42.7634 41.208 41.5474 40.056 37.3234 40.056C33.0674 40.056 32.1714 41.304 32.0754 41.912H25.5474V27.192H47.6594V32.6H32.0754V37.016C32.3314 36.76 34.4754 35.16 38.7634 35.16H39.0834C47.3714 35.16 49.3234 39.032 49.3234 42.552V42.936C49.3234 46.552 47.0514 51.352 37.8994 51.352Z"
                                fill="white" />
                        </svg>
                    </div>
                    <div class="support-stages-item__content">
                        <h3 class="support-stages-item__title">Настройка пула и кошелька</h3>
                        <p class="support-stages-item__description">
                            Осуществим все технические работы с ПО для запуска и стабильного функционирования
                            вашей
                            фермы
                        </p>
                    </div>
                </div>

                <!-- Карточка 6-->
                <div class="support-stages-item is-hidden-initially">
                    <div class="support-stages-item__icon-wrapper">
                        <svg width="74" height="75" viewBox="0 0 74 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="0.5" width="74" height="74" rx="37" fill="#5B61FF" />
                            <path
                                d="M38.1287 51.352H36.5608C25.5528 51.352 23.9528 43.992 23.9528 39.704V38.968C23.9528 35.128 25.6808 26.904 36.5608 26.904H37.5208C47.4088 26.904 49.3928 31.128 49.3928 34.104V34.136H42.4808C42.2248 33.528 41.6488 31.992 37.1688 31.992C31.3128 31.992 30.6728 36.088 30.6728 38.04V38.264C31.2808 37.752 33.3288 35.96 38.8647 35.96H39.5688C47.3768 35.96 50.0968 39.128 50.0968 42.744V43.288C50.0968 46.424 48.3368 51.352 38.1287 51.352ZM31.5688 43.288V43.448C31.5688 44.6 32.5288 46.296 37.3928 46.296C42.6728 46.296 43.3128 44.536 43.3128 43.48V43.288C43.3128 42.136 42.5128 40.6 37.3928 40.6C32.4648 40.6 31.5688 42.136 31.5688 43.288Z"
                                fill="white" />
                        </svg>
                    </div>
                    <div class="support-stages-item__content">
                        <h3 class="support-stages-item__title">Техническое обслуживание</h3>
                        <p class="support-stages-item__description">
                            Полное сопровождение фермы «под ключ»
                        </p>
                    </div>
                </div>
            </div>
            <button class=" btn support-stages__show-more-link load-more-button"
                data-load-more-target=".support-stages-grid .support-stages-item.is-hidden-initially"
                data-load-more-count="6" id="faqLoadMoreBtn">Показать ещё
                <svg width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.25 9.9043L9.5 14.6543L4.75 9.9043" stroke="#131315" stroke-width="1.2"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <!-- <a href="#"
                    class="section-header__link placement-section__header-link-mobile">
                    Подробнее
                    <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect id="Btn3" rx="0.000000" width="35.250000" height="35.250000"
                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0" />
                        <g clip-path="url(#clip5129_113345)">
                            <circle id="Ellipse4" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                stroke-opacity="1.000000" stroke-width="0.750000" />
                            <path id="Vector10" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                stroke-linejoin="round" stroke-linecap="round" />
                        </g>
                    </svg>
                </a> -->
        </div>
    </div>
</section>

<!-- Секция "Часто задаваемые вопросы" (FAQ) -->
<section class="faq-section section-padding">
    <div class="container">
        <h2>Часто задаваемые вопросы</h2>
        <div class="faq-list">
            <!-- Аккордеон -->

            <details class="faq-item">
                <summary class="faq-item__question">
                    Какие способы оплаты у вас есть?
                </summary>
                <div class="faq-item__answer">
                    <p>Наличный и безналичный расчет, оплата по договору.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Поставка под заказ: сроки индивидуальны
                </summary>
                <div class="faq-item__answer">
                    <p>До 30 рабочих дней с момента оплаты.<br>
                        И до 30 рабочих дней с момента отгрузки заводом в Китае (Для фьючерсов).
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Есть ли у вас мастерские? Какие сроки ремонта оборудования на хостинге?
                </summary>
                <div class="faq-item__answer">
                    <p>Наши дата-центры оборудованы собственными мастерскими с квалифицированной командой
                        специалистов. При
                        невозможности оперативного устранения поломки, в рамках гарантийного срока предоставляем
                        подменное устройство на весь
                        срок ремонта.</p>
                </div>
            </details>

            <details class="faq-item is-hidden-initially">
                <summary class="faq-item__question">
                    Можно купить оборудование в рассрочку? Какие условия?
                </summary>
                <div class="faq-item__answer">
                    <p>Условия рассрочки и лизинга обсуждаются индивидуально. Для получения коммерческого
                        предложения свяжитесь с нашими
                        менеджерами любым удобным способом.</p>
                </div>
            </details>

            <details class="faq-item is-hidden-initially">
                <summary class="faq-item__question">
                    Где располагается дата-центр?
                </summary>
                <div class="faq-item__answer">
                    <p>Хостинг запущен на территории Калининской АЭС в Тверской области. ЦОД
                        «Калининский» располагается всего в 380
                        км. от Москвы, что упрощает логистику и позволяет организовывать доступные экскурсии для
                        действующих и потенциальных
                        клиентов.</p>
                </div>
            </details>

            <details class="faq-item is-hidden-initially">
                <summary class="faq-item__question">
                    Вы состоите в реестре майнеров РФ?
                </summary>
                <div class="faq-item__answer">
                    <p>Да, компания GIS Mining является участником реестра в строгом соответствии с
                        действующим законодательством
                        России.</p>
                </div>
            </details>
            <!-- Кнопка "Смотреть еще" -->
            <div class="faq-actions">
                <button type="button" class="btn faq-actions__load-more load-more-button"
                    data-load-more-target=".faq-section .faq-item.is-hidden-initially" data-load-more-count="6"
                    id="faqLoadMoreBtn2">Показать ещё
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 1.4043L6 6.4043L1 1.4043" stroke="#131315" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Секция "Обратная связь" -->
<? $APPLICATION->IncludeComponent(
    "custom:feedback.section", // Наш неймспейс и имя компонента
    ".default", // Имя шаблона (можно опустить, .default - по умолчанию)
    array(
        // Здесь пока нет параметров, оставляем пустым
    )
); ?>

<!-- PopUp -->
<div class="form-popup popup-form-wrapper" id="mainPopupFormWrapper" style="display: none;">
    <div class="form-popup__items">
        <button type="button" class="form-popup__close-btn popup-form__close-btn menu-close" id="closeMainPopupFormBtn"
            aria-label="Закрыть">
            <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.9844 10L10.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10.9844 10L22.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <div class="form-popup__title-img-wrapper">
            <h2 class="form-popup__title">Консультация</h2>
            <div class="form-popup__img-wrapper">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/components/popup_form_image.png"
                    alt="Контейнер для майнинг фермы" class="form-popup__img" loading="lazy" width="300" height="200">
            </div>
        </div>
        <form class="form-popup__popup-form js-ajax-form" id="contactFormPopup" data-metric-goal="send-consult-lead">

            <p class="form-popup__cta">Заполните форму, чтобы оставить заявку на консультацию. Мы перезвоним вам в
                ближайшее время
            </p>

            <label for="popup_client_name">Имя:</label> <!-- Изменил id для уникальности -->
            <input type="text" name="client_name" id="popup_client_name" placeholder="Имя"
                class="form-popup__input form-input" aria-label="Имя">

            <label for="popup_client_phone">Телефон*:</label> <!-- Изменил id для уникальности -->
            <input type="tel" name="client_phone" id="popup_client_phone" placeholder="Телефон*"
                class="form-popup__input form-input js-phone-mask" required aria-label="Номер телефона">

            <!-- НОВОЕ ПОЛЕ: Ник в Telegram (необязательное) -->
            <!-- <label for="popup_client_telegram">Ник в Telegram:</label>
            <input type="text" name="client_telegram" id="popup_client_telegram"
                placeholder="@ваш_ник (необязательно)" class="contact-form__input form-input"
                aria-label="Ник в Telegram"> -->

            <!-- НОВОЕ ПОЛЕ: Электронная почта (необязательное) -->
            <!-- <label for="popup_client_email">Email:</label>
            <input type="email" name="client_email" id="popup_client_email"
                   placeholder="your@email.com (необязательно)" class="form-popup__input form-input"
                   aria-label="Электронная почта"> -->
            <!-- БЛОК: Социальные сети -->
            <div class="form-popup__socials">
                <h3 class="form-popup__subtitle">Или напишите нам в мессенджеры</h3>

                <div class="form-popup__socials-list">

                    <!-- Telegram -->
                    <a href="https://t.me/gismining_chat_bot" id="tg-link-footer" target="_blank"
                        rel="noopener noreferrer nofollow" class="form-popup__social-square">
                        <svg width="48" height="48" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.75" y="0.21875" width="24" height="24" rx="5.25" fill="#006FFF"></rect>
                            <path
                                d="M18.2212 4.85117C18.2212 4.85117 19.6088 4.20189 19.4932 5.77873C19.4546 6.42802 19.1077 8.70052 18.8379 11.1585L17.9128 18.4398C17.9128 18.4398 17.8358 19.5065 17.1419 19.692C16.4481 19.8775 15.4074 19.0428 15.2147 18.8572C15.0605 18.7181 12.3239 16.6311 11.3602 15.6108C11.0904 15.3325 10.782 14.776 11.3988 14.1267L15.446 9.48896C15.9085 8.93242 16.371 7.63385 14.4438 9.21069L9.04754 13.6166C9.04754 13.6166 8.43082 14.0803 7.2745 13.6629L4.76908 12.7354C4.76908 12.7354 3.844 12.0397 5.42434 11.344C9.27883 9.16428 14.0198 6.93815 18.2212 4.85117Z"
                                fill="white"></path>
                        </svg>
                    </a>

                    <!-- WhatsApp — полностью корректный SVG из референса -->
                    <a href="https://api.whatsapp.com/send/?phone=%2B79311116071" id="wa-link-footer" target="_blank"
                        rel="noopener noreferrer nofollow" class="form-popup__social-square">
                        <svg width="48" height="48" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_5008_11409)">
                                <rect y="0.21875" width="24" height="24" fill="#18A53A"></rect>
                                <path
                                    d="M15.6015 13.3381C15.5723 13.3241 14.4787 12.7856 14.2844 12.7157C14.2051 12.6872 14.1201 12.6594 14.0297 12.6594C13.882 12.6594 13.758 12.7329 13.6613 12.8775C13.5521 13.0399 13.2214 13.4264 13.1192 13.5419C13.1059 13.5572 13.0876 13.5754 13.0767 13.5754C13.0669 13.5754 12.8977 13.5057 12.8465 13.4834C11.6733 12.9738 10.7829 11.7484 10.6608 11.5417C10.6434 11.512 10.6426 11.4985 10.6425 11.4985C10.6468 11.4828 10.6862 11.4433 10.7066 11.4229C10.7661 11.3639 10.8307 11.2862 10.8931 11.2111C10.9227 11.1755 10.9523 11.1398 10.9814 11.1062C11.0719 11.0008 11.1123 10.919 11.159 10.8242L11.1835 10.775C11.2977 10.5482 11.2002 10.3568 11.1687 10.295C11.1428 10.2433 10.681 9.12882 10.632 9.01174C10.5139 8.7292 10.3579 8.59766 10.1411 8.59766C10.121 8.59766 10.1411 8.59766 10.0568 8.60121C9.95405 8.60555 9.39468 8.67919 9.14737 8.83509C8.8851 9.00044 8.44141 9.52751 8.44141 10.4544C8.44141 11.2887 8.97082 12.0764 9.19812 12.3759C9.20377 12.3835 9.21414 12.3988 9.22919 12.4208C10.0997 13.6921 11.1848 14.6342 12.2849 15.0737C13.344 15.4967 13.8455 15.5456 14.1306 15.5456H14.1306C14.2504 15.5456 14.3463 15.5362 14.4309 15.5279L14.4846 15.5228C14.8505 15.4904 15.6545 15.0737 15.8374 14.5655C15.9814 14.1652 16.0194 13.7279 15.9236 13.5691C15.8579 13.4612 15.7448 13.4069 15.6015 13.3381Z"
                                    fill="white"></path>
                                <path
                                    d="M12.1335 4.71875C8.07085 4.71875 4.76562 7.99914 4.76562 12.0313C4.76562 13.3354 5.11463 14.612 5.77578 15.7292L4.51031 19.4621C4.48674 19.5317 4.50427 19.6087 4.55575 19.6611C4.59292 19.699 4.64332 19.7195 4.6948 19.7195C4.71453 19.7195 4.7344 19.7165 4.75378 19.7103L8.64618 18.4735C9.71133 19.0426 10.9152 19.343 12.1336 19.343C16.1958 19.343 19.5007 16.063 19.5007 12.0313C19.5007 7.99914 16.1958 4.71875 12.1335 4.71875ZM12.1335 17.8198C10.9871 17.8198 9.87668 17.4887 8.92219 16.8624C8.89009 16.8413 8.85283 16.8305 8.81533 16.8305C8.79551 16.8305 8.77564 16.8335 8.7563 16.8396L6.80645 17.4594L7.4359 15.6024C7.45626 15.5423 7.44608 15.476 7.40857 15.4248C6.68172 14.4316 6.2975 13.2582 6.2975 12.0313C6.2975 8.83909 8.91552 6.24201 12.1335 6.24201C15.351 6.24201 17.9688 8.83909 17.9688 12.0313C17.9688 15.2231 15.3511 17.8198 12.1335 17.8198Z"
                                    fill="white"></path>
                            </g>
                            <defs>
                                <clipPath id="clip0_5008_11409">
                                    <rect width="24" height="24" rx="5.25" transform="matrix(1 0 0 -1 0 24.2188)"
                                        fill="white"></rect>
                                </clipPath>
                            </defs>
                        </svg>
                    </a>

                </div>
            </div>


            <input type="hidden" name="source_id" value="23">
            <input type="hidden" name="utm_source">
            <input type="hidden" name="utm_medium">
            <input type="hidden" name="utm_campaign">
            <input type="hidden" name="utm_content">
            <input type="hidden" name="utm_term">
            <input type="hidden" name="form_name" value="Консультация">
            <input type="hidden" name="page_url" value="">

            <div class="form-group form-check mb-3">
                <input type="checkbox" id="privacy-policy-popup" name="privacy-policy" class="form-check-input"
                    required>
                <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a
                        href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a
                        href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
            </div>

            <button type="submit" class="form-popup__submit-btn btn btn-primary" id="submitContactBtnPopup">Оставить
                заявку</button>

            <p class="form-popup__error-message form-error-message" style="color: red; display: none;"></p>
        </form>
    </div>
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
//Создаем управляемую область для микроразметки и другого контента.
$APPLICATION->IncludeComponent(
    "bitrix:main.include",
    "", // Шаблон компонента (оставляем пустым)
    array(
        "AREA_FILE_SHOW" => "file", // Показываем содержимое из файла
        "PATH" => "/include/main_page_schema.php", // Путь к файлу, который будет создан
        "EDIT_TEMPLATE" => "" // Оставляем пустым
    )
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>