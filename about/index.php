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

// УКАЗЫВАЕМ УНИКАЛЬНУЮ КАРТИНКУ ДЛЯ ЭТОЙ СТРАНИЦЫ (если она есть, если нет - можно оставить от главной)
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.webp';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "О компании GIS Mining: продажа асиков, ремонт, размещение и техническое сопровождение. Строительство дата-центров под ключ");
$APPLICATION->SetTitle("О компании");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Выгодные инвестиции в майнинг вместе с компанией GIS Mining. Рассказываем о нашем проекте и услугах");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "О компании GIS Mining: продажа асиков, ремонт, размещение и техническое сопровождение. Строительство дата-центров под ключ");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Выгодные инвестиции в майнинг вместе с компанией GIS Mining. Рассказываем о нашем проекте и услугах");
$APPLICATION->SetPageProperty("OG:TYPE", "website"); // Для внутренних страниц лучше использовать "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "О компании GIS Mining: продажа асиков, ремонт, размещение и техническое сопровождение. Строительство дата-центров под ключ");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Выгодные инвестиции в майнинг вместе с компанией GIS Mining. Рассказываем о нашем проекте и услугах");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-about");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД ХЛЕБНЫХ КРОШЕК СО СТАНДАРТНЫМ ШАБЛОНОМ -----
// Хлебные крошки теперь формируются автоматически в header
?>


    
    <h1 class="section-about-seo__main-title section-title highlighted-color visually-hidden">GIS Mining - одна из крупнейших компаний на рынке майнинга в России</h1>
    <section class="section-about-seo container">
        <div class="section-about-seo__wrapper">
            <div class="section-about-seo__title-wrapper">
                <h2 class="section-about-seo__title section-title highlighted-color">Дорогие партнеры!</h2>
                <!-- <a href="#" class="section-about-seo__link section-about-seo__link-desktop">
                    Блог основателя
                    <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect id="Btn1" rx="0.000000" width="35.250000" height="35.250000"
                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0"></rect>
                        <g clip-path="url(#clip5129_113345)">
                            <circle id="Ellipse2" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                stroke-opacity="1.000000" stroke-width="0.750000"></circle>
                            <path id="Vector8" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                stroke-linejoin="round" stroke-linecap="round"></path>
                        </g>
                    </svg>
                </a> -->
            </div>
            <div class="section-about-seo__content">
                <h3 class="section-about-seo__content-title">
                    GIS Mining — одна из крупнейших компаний B2B на рынке майнинга в России
                </h3>
                <p class="section-about-seo__content-text">
                    Мы предоставляем комплекс услуг, включающий оптовые продажи оборудования, его обслуживание,
                    инвестирование в
                    майнинг-проекты, строительство площадок и техподдержку как в наших ЦОД, так и в дата-центрах
                    клиентов.
                </p>
                <p class="section-about-seo__content-text">Работаем с юридическими лицами и крупными компаниями
                </p>
                <p class="section-about-seo__content-text">
                    GIS Mining занимает лидирующие позиции на рынке майнинга в России. Наша миссия — обеспечить
                    максимальный доход каждому
                    нашему клиенту
                </p>
            </div>
            <div class="section-about-seo__slider slider-swiper">
                <div class="swiper js-swiper-slider placement-gallery-slider" data-swiper-options='{
                                    "slidesPerView": 1,
                                    "spaceBetween": 1,
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
                                    }' data-swiper-destroy-breakpoint="12000"> <!-- Уничтожать НАЧИНАЯ с 12000px -->
                    <div class="swiper-wrapper">
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide1_image.webp"
                                     alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>Генеральный директор GIS Mining Василий Гиря в беседе с RT предположил, что в
                                    ближайшие месяцы стоимость биткоина может
                                    достичь нового исторического рекорда</p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide2_image.webp"
                                     alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>Сегодня новым драйвером роста биткоина стал резкий скачок доходности казначейских
                                    облигаций США на фоне заметного
                                    снижения американского фондового рынка</p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide3_image.webp"
                                     alt="Фото дата-центра 1"  loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>Промышленный майнинг в России продолжает развиваться: растут и инвестиции, и масштабы
                                    инфраструктуры</p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide4_image.webp"
                                     alt="Фото дата-центра 1"  loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>Курс биткойна после обновления исторического максимума может продолжить рост достичь
                                    $120-150 тыс. до конца 2025 года</p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide5_image.webp"
                                     alt="Фото дата-центра 1"  loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>Эксперты не исключают, что к середине лета курс биткойна может достигнуть $130 тыс
                                </p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide6_image.webp"
                                     alt="Фото дата-центра 1"  loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>При благоприятном развитии событий, а также при условии успешного преодоления
                                    ключевых технических уровней, биткоин
                                    вполне способен достичь верхней границы</p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_slide7_image.webp"
                                     alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text">
                                <p>Генеральный директор компании GIS Mining Василий Гиря заявил, что в России цифровая
                                    валюта официально признается видом
                                    имущества, в том числе для налоговых целей</p>
                            </div>
                            <!-- <div class="swiper-slide__icon">
                                <svg width="70" height="40" viewBox="0 0 70 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                        fill="#5B61FF" />
                                </svg>
                            </div> -->
                        </div>
                    </div>
                    <div class="swiper-slide__icon">
                        <svg width="70" height="40" viewBox="0 0 70 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                    d="M23.3761 40L6.55046 40C9.50459 34.658 12.4587 27.2313 13.4862 21.6287L1.60607e-06 21.6287L3.49691e-06 -6.11959e-06L29.7982 -3.51456e-06L29.7982 16.6775C29.7982 25.9283 26.0734 35.3094 23.3761 40ZM63.578 40L46.7523 40C49.7064 34.658 52.6606 27.2313 53.6881 21.6287L40.2018 21.6287L40.2018 -2.60504e-06L70 0L70 16.6775C70 25.9283 66.2752 35.3094 63.578 40Z"
                                    fill="#5B61FF" />
                        </svg>
                    </div>
                </div>
                <div class="swiper-pagination placement-gallery-slider__pagination"></div>
            </div>
            <div class="section-about-seo__image-wrapper">
                <img class="section-about-seo__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-about-seo_image.webp"
                     alt="Фото SEO GIS Mining" width=804 height=609>
            </div>
            <p class="section-about-seo__image-description">Гиря Василий Андреевич, основатель компании</p>
            <!-- <a href="#" class="section-about-seo__link section-about-seo__link-mobile">
                Блог основателя
                <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <rect id="Btn1" rx="0.000000" width="35.250000" height="35.250000"
                        transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0"></rect>
                    <g clip-path="url(#clip5129_113345)">
                        <circle id="Ellipse2" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                            stroke-opacity="1.000000" stroke-width="0.750000"></circle>
                        <path id="Vector8" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                            stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000" stroke-linejoin="round"
                            stroke-linecap="round"></path>
                    </g>
                </svg>
            </a> -->
        </div>
    </section>

    <section class="section-company-history container section-padding">
        <div class="section-company-history__wrapper">
            <h2 class="section-company-history__title section-title">
                История компании
            </h2>
            <div class="section-company-history__text-wrapper togglable-content" data-content-group="history1">
                <p class="section-company-history__text-item">В 2015 году, когда курс Биткоина достигал всего 250
                    долларов, а
                    Эфириум только начинал набирать
                    популярность, я построил
                    свою первую серверную, в которой разместил около 180 видеокарт. Этот шаг положил начало
                    масштабированию
                    бизнеса: мы
                    начали строить новые серверные, обеспечивая партнерам и клиентам быструю окупаемость вложений.
                </p>

                <p class="section-company-history__text-item">С течением времени я переехал в Москву и переключился на
                    асик-фермы, совместно с партнером запустив
                    небольшие площадки с
                    20-50 устройствами. Мы также перевезли все наши видеокарты, что позволило расширить формат добычи.
                </p>

                <p class="section-company-history__text-item">Через некоторое время взяли в работу проект, в который
                    изначально одна из компаний вложила несколько
                    миллионов долларов:
                    до реализации он не дошел, поэтому потребовалась наша помощь. В результате запустили центр обработки
                    данных с
                    несколькими тысячами видеокарт, что принесло значительную прибыль инвесторам.
                </p>

                <p class="section-company-history__text-item">
                    Мой успех не остался незамеченным: я был повышен до технического директора и возглавил основное
                    подразделение в Туле,
                    где построил площадку на 4,5 тысячи видеокарт и установил 16 контейнеров. Эти площадки функционируют
                    до сих пор. Затем я
                    продолжил развивать бизнес, открывая новые дата-центры в различных регионах и занимая пост
                    исполнительного директора в
                    Москве.
                </p>

                <p class="section-company-history__text-item">
                    Параллельно открыл собственный бизнес по технической поддержке дата-центров. Мы работали с
                    несколькими крупными
                    клиентами, включая один из проектов на Калининской АЭС, где обслуживали 10 контейнеров. Наша команда
                    быстро росла: к
                    тому времени штат состоял уже из 16 сотрудников, работающих как удаленно, так и на местах.
                </p>

                <p class="section-company-history__text-item">
                    Вскоре мы открыли юридическое лицо в России и увеличили масштабы услуг технической поддержки
                    дата-центрам. Мы
                    реализовали сервис по эксплуатации контейнеров под ключ и начали заниматься их мониторингом и
                    ремонтом.
                </p>

                <p class="section-company-history__text-item">
                    С течением времени мы значительно расширили нашу деятельность: начали сдавать контейнеры в аренду,
                    выкупать свободные
                    мощности и перепродавать оборудование. Открытие собственного сервисного центра по ремонту асиков
                    стало следующим шагом,
                    хотя вскоре мы закрыли его из-за операционных сложностей.
                </p>

                <p class="section-company-history__text-item">
                    Компания сосредоточилась на продаже и аренде мобильных ЦОДов на Калининской АЭС, подписав
                    долгосрочный договор аренды. В
                    дальнейшем мы построили площадку в Тверской области на 10 мегаватт и арендовали дополнительные
                    мощности.
                </p>

                <p class="section-company-history__text-item is-hidden-initially">
                    Сейчас компания продолжает активно развиваться. Нас цитируют ведущие СМИ, мы консультируем
                    крупнейших участников
                    майнинговой отрасли России.
                </p>

                <p class="section-company-history__text-item is-hidden-initially">
                    Мы открыли филиал рядом с Калининской АЭС, переехали в более просторный офис и сосредоточились на
                    трех основных
                    направлениях:
                </p>
                <div class="section-company-history__text-item is-hidden-initially">
                    <ul>
                        <li>Продажа оборудования;</li>
                        <li>Хостинг (размещение оборудования в собственных дата-центрах);</li>
                        <li>Строительство дата-центров под ключ.</li>
                    </ul>
                </div>
                <p class="section-company-history__text-item is-hidden-initially">
                    Наша история — это путь от небольшого стартапа до надежного игрока на рынке IT-услуг и майнинга. Мы
                    продолжаем двигаться
                    вперед, развивая новые технологии и расширяя наше присутствие.
                </p>

                <p class="section-company-history__text-item is-hidden-initially">
                    Гиря Василий Андреевич, основатель компании
                </p>
            </div>
            <div class="section-company-history__load-more-wrapper toggle-buttons-wrapper">
                <!-- ПРОСТАЯ КНОПКА "РАЗВЕРНУТЬ" -->
                <button type="button" class="js-simple-toggle btn section-company-history__load-more read-more-button">
                    Развернуть
                    <svg class="read-more-button__icon" width="12" height="8" viewBox="0 0 12 8" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 1.4043L6 6.4043L1 1.4043" stroke="#131315" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <!-- ВАША КАСТОМНАЯ КНОПКА "СВЕРНУТЬ" (ОПЦИОНАЛЬНАЯ) -->
                <!-- <button type="button" class="js-toggle-less btn section-company-history__load-more read-less-button"
                    data-content-group="history1"
                    data-toggle-target=".section-company-history__text-wrapper[data-content-group='history1'] > .section-company-history__text-item.is-hidden-initially"
                    data-toggle-visible-initial="0" style="display: none;">
                    Свернуть
                    <svg class="read-less-button__icon" width="12" height="8" viewBox="0 0 12 8" fill="none"
                        xmlns="http://www.w3.org/2000/svg" style="transform: rotate(180deg);">
                        <path d="M11 1.4043L6 6.4043L1 1.4043" stroke="#131315" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button> -->
            </div>
        </div>
    </section>

    <section class="section-our-services container section-padding">
        <div class="section-our-services__title-wrapper">
            <h2 class="section-our-services__title section-title">Наши услуги</h2>
            <!-- <a href="#" class="section-our-services__link section-our-services__link-desktop">
                Подробнее
                <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <rect id="Btn1" rx="0.000000" width="35.250000" height="35.250000"
                        transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0"></rect>
                    <g clip-path="url(#clip5129_113345)">
                        <circle id="Ellipse2" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                            stroke-opacity="1.000000" stroke-width="0.750000"></circle>
                        <path id="Vector8" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                            stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000" stroke-linejoin="round"
                            stroke-linecap="round"></path>
                    </g>
                </svg>
            </a> -->
        </div>
        <div class="section-our-services__description">
            <p>Обеспечиваем надежное партнерство, максимальную доходность и безопасность ваших инвестиций. Предоставляем
                персонального
                консультанта и гарантируем клиентский сервис высокого уровня</p>
        </div>
        <div class="section-our-services__grid">
            <a href="https://gis-mining.ru/catalog/asics/" class="section-our-services__card animation-card">
                <h3 class="section-our-services__card-title">Продажа асиков</h3>
                <svg width="48" height="49" viewBox="0 0 48 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.046875" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3783_81368)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.6022 18.524L19.5998 18.4099C19.6189 17.9447 19.9828 17.5465 20.435 17.4717L20.5498 17.4599L29.5831 17.0886L29.6381 17.088L29.7171 17.0929L29.8341 17.1136L29.9397 17.1475L30.0287 17.1889L30.1396 17.261L30.2173 17.3294L30.262 17.3778L30.3279 17.4672L30.377 17.5559L30.4163 17.6542L30.4414 17.7496L30.4567 17.8639L30.4581 17.9636L30.0868 26.9969C30.0661 27.5009 29.6408 27.9262 29.1368 27.9469C28.6716 27.966 28.3021 27.6337 28.2642 27.1861L28.2618 27.0719L28.5413 20.2428L18.0728 30.7114C17.7018 31.0824 17.124 31.1061 16.7823 30.7644C16.4406 30.4227 16.4643 29.8449 16.8353 29.4739L27.3052 19.004L20.4748 19.2849C20.0096 19.304 19.64 18.9717 19.6022 18.524L19.5998 18.4099L19.6022 18.524Z"
                              fill="white" />
                    </g>
                </svg>
            </a>
            <a href="https://gis-mining.ru/catalog/conteynery/" class="section-our-services__card animation-card">
                <h3 class="section-our-services__card-title">Продажа контейнеров</h3>
                <svg width="48" height="49" viewBox="0 0 48 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.046875" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3783_81368)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.6022 18.524L19.5998 18.4099C19.6189 17.9447 19.9828 17.5465 20.435 17.4717L20.5498 17.4599L29.5831 17.0886L29.6381 17.088L29.7171 17.0929L29.8341 17.1136L29.9397 17.1475L30.0287 17.1889L30.1396 17.261L30.2173 17.3294L30.262 17.3778L30.3279 17.4672L30.377 17.5559L30.4163 17.6542L30.4414 17.7496L30.4567 17.8639L30.4581 17.9636L30.0868 26.9969C30.0661 27.5009 29.6408 27.9262 29.1368 27.9469C28.6716 27.966 28.3021 27.6337 28.2642 27.1861L28.2618 27.0719L28.5413 20.2428L18.0728 30.7114C17.7018 31.0824 17.124 31.1061 16.7823 30.7644C16.4406 30.4227 16.4643 29.8449 16.8353 29.4739L27.3052 19.004L20.4748 19.2849C20.0096 19.304 19.64 18.9717 19.6022 18.524L19.5998 18.4099L19.6022 18.524Z"
                              fill="white" />
                    </g>
                </svg>
            </a>
            <a href="https://gis-mining.ru/razmeschenie/" class="section-our-services__card animation-card">
                <h3 class="section-our-services__card-title">Размещение в data-центре</h3>
                <svg width="48" height="49" viewBox="0 0 48 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.046875" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3783_81368)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.6022 18.524L19.5998 18.4099C19.6189 17.9447 19.9828 17.5465 20.435 17.4717L20.5498 17.4599L29.5831 17.0886L29.6381 17.088L29.7171 17.0929L29.8341 17.1136L29.9397 17.1475L30.0287 17.1889L30.1396 17.261L30.2173 17.3294L30.262 17.3778L30.3279 17.4672L30.377 17.5559L30.4163 17.6542L30.4414 17.7496L30.4567 17.8639L30.4581 17.9636L30.0868 26.9969C30.0661 27.5009 29.6408 27.9262 29.1368 27.9469C28.6716 27.966 28.3021 27.6337 28.2642 27.1861L28.2618 27.0719L28.5413 20.2428L18.0728 30.7114C17.7018 31.0824 17.124 31.1061 16.7823 30.7644C16.4406 30.4227 16.4643 29.8449 16.8353 29.4739L27.3052 19.004L20.4748 19.2849C20.0096 19.304 19.64 18.9717 19.6022 18.524L19.5998 18.4099L19.6022 18.524Z"
                              fill="white" />
                    </g>
                </svg>
            </a>
            <a href="https://gis-mining.ru/in-development.php" class="section-our-services__card animation-card">
                <h3 class="section-our-services__card-title">Техподдержка вашего data-центра</h3>
                <svg width="48" height="49" viewBox="0 0 48 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.046875" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3783_81368)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.6022 18.524L19.5998 18.4099C19.6189 17.9447 19.9828 17.5465 20.435 17.4717L20.5498 17.4599L29.5831 17.0886L29.6381 17.088L29.7171 17.0929L29.8341 17.1136L29.9397 17.1475L30.0287 17.1889L30.1396 17.261L30.2173 17.3294L30.262 17.3778L30.3279 17.4672L30.377 17.5559L30.4163 17.6542L30.4414 17.7496L30.4567 17.8639L30.4581 17.9636L30.0868 26.9969C30.0661 27.5009 29.6408 27.9262 29.1368 27.9469C28.6716 27.966 28.3021 27.6337 28.2642 27.1861L28.2618 27.0719L28.5413 20.2428L18.0728 30.7114C17.7018 31.0824 17.124 31.1061 16.7823 30.7644C16.4406 30.4227 16.4643 29.8449 16.8353 29.4739L27.3052 19.004L20.4748 19.2849C20.0096 19.304 19.64 18.9717 19.6022 18.524L19.5998 18.4099L19.6022 18.524Z"
                              fill="white" />
                    </g>
                </svg>
            </a>
            <a href="https://gis-mining.ru/newstroitelstvo/" class="section-our-services__card animation-card">
                <h3 class="section-our-services__card-title">Инвестиции в строительство data-центров под ключ</h3>
                <svg width="48" height="49" viewBox="0 0 48 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.046875" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3783_81368)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.6022 18.524L19.5998 18.4099C19.6189 17.9447 19.9828 17.5465 20.435 17.4717L20.5498 17.4599L29.5831 17.0886L29.6381 17.088L29.7171 17.0929L29.8341 17.1136L29.9397 17.1475L30.0287 17.1889L30.1396 17.261L30.2173 17.3294L30.262 17.3778L30.3279 17.4672L30.377 17.5559L30.4163 17.6542L30.4414 17.7496L30.4567 17.8639L30.4581 17.9636L30.0868 26.9969C30.0661 27.5009 29.6408 27.9262 29.1368 27.9469C28.6716 27.966 28.3021 27.6337 28.2642 27.1861L28.2618 27.0719L28.5413 20.2428L18.0728 30.7114C17.7018 31.0824 17.124 31.1061 16.7823 30.7644C16.4406 30.4227 16.4643 29.8449 16.8353 29.4739L27.3052 19.004L20.4748 19.2849C20.0096 19.304 19.64 18.9717 19.6022 18.524L19.5998 18.4099L19.6022 18.524Z"
                              fill="white" />
                    </g>
                </svg>
            </a>
            <a href="https://gis-mining.ru/mining-investicii/" class="section-our-services__card animation-card">
                <h3 class="section-our-services__card-title">Майнинг под ключ</h3>
                <svg width="48" height="49" viewBox="0 0 48 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.046875" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3783_81368)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M19.6022 18.524L19.5998 18.4099C19.6189 17.9447 19.9828 17.5465 20.435 17.4717L20.5498 17.4599L29.5831 17.0886L29.6381 17.088L29.7171 17.0929L29.8341 17.1136L29.9397 17.1475L30.0287 17.1889L30.1396 17.261L30.2173 17.3294L30.262 17.3778L30.3279 17.4672L30.377 17.5559L30.4163 17.6542L30.4414 17.7496L30.4567 17.8639L30.4581 17.9636L30.0868 26.9969C30.0661 27.5009 29.6408 27.9262 29.1368 27.9469C28.6716 27.966 28.3021 27.6337 28.2642 27.1861L28.2618 27.0719L28.5413 20.2428L18.0728 30.7114C17.7018 31.0824 17.124 31.1061 16.7823 30.7644C16.4406 30.4227 16.4643 29.8449 16.8353 29.4739L27.3052 19.004L20.4748 19.2849C20.0096 19.304 19.64 18.9717 19.6022 18.524L19.5998 18.4099L19.6022 18.524Z"
                              fill="white" />
                    </g>
                </svg>
            </a>
        </div>
        <!-- <a href="#" class="section-our-services__link section-our-services__link-mobile">
            Подробнее
            <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
                <rect id="Btn1" rx="0.000000" width="35.250000" height="35.250000"
                    transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0"></rect>
                <g clip-path="url(#clip5129_113345)">
                    <circle id="Ellipse2" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                        stroke-opacity="1.000000" stroke-width="0.750000"></circle>
                    <path id="Vector8" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12" stroke="#131315"
                        stroke-opacity="1.000000" stroke-width="0.750000" stroke-linejoin="round"
                        stroke-linecap="round"></path>
                </g>
            </svg>
        </a> -->
    </section>

    <section class="section-our-team container section-padding">
        <h2 class="section-our-team__title section-title">Наша команда</h2>
        <div class="section-our-team__image-wrapper">
            <picture>
                <!-- Источник для больших экранов (например, десктопы) -->
                <source media="(min-width: 1200px)" srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-team_image_desktop.webp">

                <!-- Источник для средних экранов (например, планшеты) -->
                <source media="(min-width: 768px)" srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-team_image_tablet.webp">

                <!-- Изображение по умолчанию (для мобильных и браузеров без поддержки <picture>) -->
                <img class="section-about-seo__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-team_image_mobile.webp"
                     alt="Фото команды GIS Mining" loading="lazy" width="824" height="924">
            </picture>
        </div>
        <!-- <div class="section-our-team__slider-flex-wrapper">
            <div class="section-our-team__slider slider-swiper">
                <div class="swiper js-swiper-slider placement-gallery-slider" data-swiper-options='{
                                                        "slidesPerView": 1.2,
                                                        "spaceBetween": 10,
                                                        "centeredSlides": false,
                                                        "loop": true,
                                                        "autoplay": {
                                                        "delay": 3000,
                                                        "disableOnInteraction": false,
                                                        "pauseOnMouseEnter": true
                                                        },
                                                        "pagination": {
                                                        "el": "True",
                                                        "clickable": true
                                                    },
                                                    "breakpoints": {
                                                        "640": {
                                                            "slidesPerView": 2.2,
                                                            "spaceBetween": 20
                                                        },
                                                        "768": {
                                                            "slidesPerView": 2.3,
                                                            "spaceBetween": 20
                                                        },
                                                        "1024": {
                                                            "slidesPerView": 2.3,
                                                            "spaceBetween": 30
                                                        },
                                                        "1200": {
                                                            "slidesPerView": 3,
                                                            "spaceBetween": 30
                                                        }
                                                    }
                                                 }' data-swiper-destroy-breakpoint="12000">
                    <div class="swiper-wrapper">
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-team_slide1.webp"
                                    alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text-content">
                                <p class="swiper-slide__job-title">Генеральный директор</p>
                                <h3 class="swiper-slide__full-name">Макеева Виталия Алексеевна</h3>
                            </div>
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-team_slide1.webp"
                                    alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text-content">
                                <p class="swiper-slide__job-title">Генеральный директор</p>
                                <h3 class="swiper-slide__full-name">Макеева Виталия Алексеевна</h3>
                            </div>
                        </div>
                        <div class="slider-swiper__slide swiper-slide">
                            <div class="swiper-slide__image-wrapper">
                                <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-team_slide1.webp"
                                    alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                            </div>
                            <div class="swiper-slide__text-content">
                                <p class="swiper-slide__job-title">Генеральный директор</p>
                                <h3 class="swiper-slide__full-name">Макеева Виталия Алексеевна</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination placement-gallery-slider__pagination"></div>
            </div>
            <div class="section-our-team__link-wrapper">
                <a href="#" class="section-our-team__link">
                    Присоединяйтесь к команде
                    <svg width="36.000000" height="36.000000" viewBox="0 0 36 36" fill="none"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect id="Btn1" rx="0.000000" width="35.250000" height="35.250000"
                            transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0"></rect>
                        <g clip-path="url(#clip5129_113345)">
                            <circle id="Ellipse2" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                stroke-opacity="1.000000" stroke-width="0.750000"></circle>
                            <path id="Vector8" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                stroke-linejoin="round" stroke-linecap="round">
                            </path>
                        </g>
                    </svg>
                </a>
            </div>
        </div> -->
    </section>

    <section class="statistics-section container section-padding">
        <div class="statistics-section__wrapper">
            <h2 class="statistics-section__title section-title">GIS MINING в цифрах</h2>
            <div class="statistics-section__flex">
                <div class="statistics-section__description">
                    <p>Эффективная система организации внутренних бизнес-процессов, оптимальные схемы работы и
                        коммуникации — <span class="font-main-bold">элементы, от
                            которых напрямую зависит качество предоставляемых услуг</span> </p>
                </div>
                <div class="statistics-section__image-wrapper">
                    <picture>
                        <!-- 1. Для больших экранов (> 1024px): -->
                        <source media="(min-width: 1024px)" srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.webp"
                                type="image/webp">
                        <source media="(min-width: 1024px)" srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.jpg"
                                type="image/jpeg">

                        <!-- 2. Для средних экранов (768px - 1023px): -->
                        <source media="(min-width: 768px)" srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.webp"
                                type="image/webp">
                        <source media="(min-width: 768px)" srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.jpg"
                                type="image/jpeg">

                        <!--3. Для мобильных экранов (< 767px): -->
                        <source srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_mobile.webp" type="image/webp">

                        <!-- Изображение по умолчанию (для мобильных в старых браузерах)
                         и обязательный фолбэк для всех браузеров, не поддерживающих <picture>. -->
                        <img class="section-about-seo__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_mobile.jpg"
                             alt="Фото руководителя GIS Mining" loading="lazy" width="300" height="200">
                    </picture>
                </div>
            </div>
            <div class="statistics-section__grid">
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        1 000+
                    </h3>
                    <div class="statistics-section__text">
                        Довольных клиентов
                    </div>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        500 млн+
                    </h3>
                    <div class="statistics-section__text">
                        Годовой оборот за 2024 год
                    </div>
                    <a href="#" class="statistics-section__link font-main-bold highlighted-color" target="_blank" rel="noopener noreferrer nofollow">Rusprofile</a>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info font-main-bold highlighted-color">
                        100%
                    </h3>
                    <div class="statistics-section__text">
                        Открытость: вы можете приехать на объект и лично проверить работу собственного оборудования
                        в любой момент
                    </div>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        2.5 EH/s
                    </h3>
                    <div class="statistics-section__text">
                        Общая мощность всех устройств
                    </div>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        19 000+
                    </h3>
                    <div class="statistics-section__text">
                        Устройств под нашим контролем
                    </div>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        5 лет
                    </h3>
                    <div class="statistics-section__text">
                        Занимаемся продажей оборудования и обслуживанием дата-центров
                    </div>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        7 минут
                    </h3>
                    <div class="statistics-section__text">
                        Средняя скорость реагирования на вышедший из строя майнер для сохранения up-time ~99%
                    </div>
                </div>
                <div class="statistics-section__item">
                    <h3 class="statistics-section__info">
                        0 ₽
                    </h3>
                    <div class="statistics-section__text">
                        Стоимость установки асиков
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-our-achievements container section-padding">
        <h2 class="section-our-achievements__title section-title">Наши достижения</h2>
        <div class="section-our-achievements__image-wrapper">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-achievements_image1.webp" alt="Sponsor sertificate GIS Mind"
                 class="section-our-achievements__image" loading="lazy" width="300" height="200">
        </div>
        <div class="section-our-achievements__image-wrapper">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-achievements_image2.webp" alt="Sponsor sertificate GIS Mind"
                 class="section-our-achievements__image" loading="lazy" width="300" height="200">
        </div>

    </section>

    <section class="section-our-partners container section-padding">
        <h2 class="section-our-partners__title section-title">Наши партнеры</h2>

        <div class="section-our-partners__slider slider-swiper">
            <div class="swiper js-swiper-slider placement-gallery-slider" data-swiper-options='{
                "slidesPerView": 2.5,
                "spaceBetween": 10,
                "centeredSlides": false,
                "loop": true,
                "autoplay": {
                "delay": 300000,
                "disableOnInteraction": false,
                "pauseOnMouseEnter": true
                },
                "pagination": {
                "el": "none",
                "clickable": true
                }
                }' data-swiper-destroy-breakpoint="768"> <!-- Уничтожать НАЧИНАЯ с 12000px -->
                <div class="slider-swiper__wrapper swiper-wrapper">
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image1.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image2.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image3.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image4.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image5.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image6.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image7.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image8.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image9.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image10.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image11.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>
                    <div class="slider-swiper__slide swiper-slide">
                        <div class="swiper-slide__image-wrapper">
                            <img class="swiper-slide__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-our-partners_image12.webp"
                                 alt="Фото дата-центра 1" loading="lazy" width="300" height="200">
                        </div>
                    </div>


                </div>
            </div>
            <div class="swiper-pagination placement-gallery-slider__pagination"></div>
        </div>
    </section>

    <section class="section-sibscribe-to-telegram container section-padding">
        <div class="section-sibscribe-to-telegram__wrapper">
            <div class="section-sibscribe-to-telegram__content">
                <h3 class="section-sibscribe-to-telegram__title section-title">Наш Telegram</h3>
                <p class="section-sibscribe-to-telegram__description">
                    Подписывайтесь на наш Telegram-канал, чтобы оперативно узнавать о новостях,
                    акциях и
                    получать лучшие предложения от GIS
                    Mining
                </p>
                <a href="https://t.me/gismining" target="_blank" rel="noopener noreferrer nofollow"
                   class="btn section-sibscribe-to-telegram__subscribe-button">
                    Подписаться на канал
                </a>
            </div>
            <div class="section-sibscribe-to-telegram__qr-code">
                <picture>
                    <!-- Источник для больших экранов (например, десктопы) -->
                    <source media="(min-width: 1024px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-sibscribe-to-telegram_qr_desktop.webp">

                    <!-- Изображение по умолчанию (для мобильных и браузеров без поддержки <picture>) -->
                    <img class="section-sibscribe-to-telegram__image"
                         src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-sibscribe-to-telegram_qr_tablet.webp"
                         alt="QR-код для подписки на Telegram канал GIS Mining" loading="lazy" width="300" height="200">
                </picture>
            </div>
        </div>
    </section>

    <section class="section-cta container section-padding">
        <h2 class="visually-hidden">Статистика и отзывы клиентов</h2>
        <div class="section-cta__wrapper">
            <div class="section-cta__title-wrapper">
                <div class="section-cta__title">
                    <span>1</span>
                    <span>0</span>
                    <span>0</span>
                    <span>0</span>
                    <span>+</span>
                </div>
                <div class="section-cta__description">
                    <p>клиентов уже подключились к нам в формате “Бизнес майнинга под ключ”</p>
                </div>
            </div>
            <div class="section-cta__maps-rewievs-items">
                <div class="section-cta__maps-rewievs-item">
                    <div class="section-cta__maps-rewievs-item-logo">
                        <svg width="49" height="49" viewBox="0 0 49 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.5" y="0.10791" width="48" height="48" rx="24" fill="#5B61FF" />
                            <circle cx="24.75" cy="24.3579" r="24" stroke="#F7F7F7" stroke-opacity="0.15"
                                    stroke-width="0.5" />
                            <path
                                    d="M36.363 22.6268C36.5897 22.4113 36.75 22.1375 36.8256 21.8366C36.9012 21.5358 36.889 21.2201 36.7904 20.9257C36.694 20.6305 36.515 20.3679 36.2737 20.1681C36.0324 19.9683 35.7387 19.8393 35.4263 19.796L29.7225 18.9818C29.6029 18.9647 29.4893 18.9193 29.3915 18.8495C29.2937 18.7796 29.2147 18.6875 29.1612 18.581L26.6113 13.504C26.4727 13.2256 26.257 12.9912 25.9891 12.8276C25.7211 12.664 25.4117 12.5779 25.0962 12.5791C24.7808 12.578 24.4714 12.6641 24.2036 12.8277C23.9357 12.9913 23.7201 13.2257 23.5816 13.504L21.0311 18.5814C20.9227 18.7978 20.7123 18.9475 20.4694 18.9823L14.7657 19.7964C14.4532 19.8398 14.1595 19.9688 13.9182 20.1686C13.6769 20.3684 13.4979 20.631 13.4015 20.9262C13.3029 21.2205 13.2907 21.5362 13.3663 21.8371C13.4419 22.1379 13.6022 22.4118 13.8289 22.6273L17.9557 26.5791C18.1316 26.7478 18.2123 26.9906 18.1708 27.2278L17.1972 32.808C17.1532 33.0451 17.1634 33.2889 17.2271 33.5216C17.2908 33.7544 17.4065 33.9703 17.5656 34.1537C18.0684 34.7408 18.9463 34.9197 19.6481 34.5573L24.749 31.9225C24.8565 31.8686 24.9755 31.8404 25.0962 31.8404C25.2169 31.8404 25.3359 31.8686 25.4434 31.9225L30.5447 34.5573C30.7866 34.684 31.0564 34.7504 31.3306 34.7505C31.8291 34.7505 32.3018 34.5327 32.6268 34.1537C32.786 33.9703 32.9016 33.7544 32.9653 33.5216C33.029 33.2889 33.0392 33.0451 32.9952 32.808L32.0211 27.2278C32.0007 27.1107 32.0097 26.9904 32.0471 26.8774C32.0846 26.7644 32.1495 26.662 32.2362 26.5791L36.363 22.6268Z"
                                    fill="white" />
                        </svg>
                    </div>
                    <div class="section-cta__maps-rewievs-item-img">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-cta__maps-rewievs-item_logo1.webp" alt="Логотип Яндекс карты" loading="lazy">
                    </div>
                    <div class="section-cta__maps-rewievs-item-text">
                        <p>Среднее значение 5.0</p>
                    </div>
                </div>
                <div class="section-cta__maps-rewievs-item">
                    <div class="section-cta__maps-rewievs-item-logo">
                        <svg width="49" height="49" viewBox="0 0 49 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.5" y="0.10791" width="48" height="48" rx="24" fill="#5B61FF" />
                            <circle cx="24.75" cy="24.3579" r="24" stroke="#F7F7F7" stroke-opacity="0.15"
                                    stroke-width="0.5" />
                            <path
                                    d="M36.363 22.6268C36.5897 22.4113 36.75 22.1375 36.8256 21.8366C36.9012 21.5358 36.889 21.2201 36.7904 20.9257C36.694 20.6305 36.515 20.3679 36.2737 20.1681C36.0324 19.9683 35.7387 19.8393 35.4263 19.796L29.7225 18.9818C29.6029 18.9647 29.4893 18.9193 29.3915 18.8495C29.2937 18.7796 29.2147 18.6875 29.1612 18.581L26.6113 13.504C26.4727 13.2256 26.257 12.9912 25.9891 12.8276C25.7211 12.664 25.4117 12.5779 25.0962 12.5791C24.7808 12.578 24.4714 12.6641 24.2036 12.8277C23.9357 12.9913 23.7201 13.2257 23.5816 13.504L21.0311 18.5814C20.9227 18.7978 20.7123 18.9475 20.4694 18.9823L14.7657 19.7964C14.4532 19.8398 14.1595 19.9688 13.9182 20.1686C13.6769 20.3684 13.4979 20.631 13.4015 20.9262C13.3029 21.2205 13.2907 21.5362 13.3663 21.8371C13.4419 22.1379 13.6022 22.4118 13.8289 22.6273L17.9557 26.5791C18.1316 26.7478 18.2123 26.9906 18.1708 27.2278L17.1972 32.808C17.1532 33.0451 17.1634 33.2889 17.2271 33.5216C17.2908 33.7544 17.4065 33.9703 17.5656 34.1537C18.0684 34.7408 18.9463 34.9197 19.6481 34.5573L24.749 31.9225C24.8565 31.8686 24.9755 31.8404 25.0962 31.8404C25.2169 31.8404 25.3359 31.8686 25.4434 31.9225L30.5447 34.5573C30.7866 34.684 31.0564 34.7504 31.3306 34.7505C31.8291 34.7505 32.3018 34.5327 32.6268 34.1537C32.786 33.9703 32.9016 33.7544 32.9653 33.5216C33.029 33.2889 33.0392 33.0451 32.9952 32.808L32.0211 27.2278C32.0007 27.1107 32.0097 26.9904 32.0471 26.8774C32.0846 26.7644 32.1495 26.662 32.2362 26.5791L36.363 22.6268Z"
                                    fill="white" />
                        </svg>
                    </div>
                    <div class="section-cta__maps-rewievs-item-img">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-cta__maps-rewievs-item_logo2.webp" alt="Логотип Яндекс карты" loading="lazy">
                    </div>
                    <div class="section-cta__maps-rewievs-item-text">
                        <p>Среднее значение 5.0</p>
                    </div>
                </div>
            </div>
            <div class="section-cta__cta-wrapper">
                <div class="section-cta__cta-content">
                    <div class="section-cta__cta-content-icon-wrapper">
                        <svg width="44" height="45" viewBox="0 0 44 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="22" cy="22.1079" r="22" fill="#5B61FF" />
                            <g clip-path="url(#clip0_3004_51079)">
                                <mask id="mask0_3004_51079" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="6"
                                      y="6" width="32" height="33">
                                    <path d="M38 6.10791H6V38.1079H38V6.10791Z" fill="white" />
                                </mask>
                                <g mask="url(#mask0_3004_51079)">
                                    <path d="M22 12.7749V31.4416" stroke="white" stroke-width="2.83333"
                                          stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12.6641 22.1079H31.3307" stroke="white" stroke-width="2.83333"
                                          stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                            </g>
                        </svg>
                    </div>
                    <p>Запишитесь на экскурсию на наш дата-центр на Калининской АЭС и убедитесь в качестве лично.</p>
                </div>
                <div class="section-cta__cta-form">
                    <!-- Обычная форма 1 -->
                    <form id="contactForm1" class="contact-form-on-page js-ajax-form" data-metric-goal="send-consult-lead">
                        <input type="tel" name="client_phone" class="js-phone-mask form-input" placeholder="Телефон*"
                               required>
                        <input type="hidden" name="form_name" value="Запись на экскурсию">
                        <!-- Универсальные UTM поля (добавить, если их еще нет здесь) -->
                        <input type="hidden" name="utm_source">
                        <input type="hidden" name="utm_medium">
                        <input type="hidden" name="utm_campaign">
                        <input type="hidden" name="utm_content">
                        <input type="hidden" name="utm_term">
                        <input type="hidden" name="page_url" value="">
                        <input type="hidden" name="source_id" value="26">
                        <button type="submit" class="btn btn-primary contact-form-submit-btn">Записаться на
                            экскурсию</button>
                        <p class="form-error-message" style="color: red; display: none;"></p>
                    </form>

                    <div class="form-group form-check mb-3">
              <input type="checkbox" id="privacy-policy-about1" name="privacy-policy" class="form-check-input" required>
               <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
            </div>
                </div>

            </div>
        </div>
    </section>

    <section class="section-feedback container section-padding feedback-section">
        <h2 class="section-feedback__title section-title">Хотите задать вопрос?
        </h2>
        <div class="section-feedback__contact-form-wrapper">
            <p class="feedback-form-block__title">Оставьте заявку, и наш менеджер
                свяжется с вами </p>
            <!-- Обычная форма 2 -->
            <form id="contactForm2" class="contact-form-on-page js-ajax-form" data-metric-goal="send-consult-lead">
                <input type="tel" name="client_phone" class="js-phone-mask form-input" placeholder="Телефон*" required>
                <input type="hidden" name="form_name" value="Задать вопрос">
                <!-- Универсальные UTM поля (добавить, если их еще нет здесь) -->
                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="page_url" value="">
                <input type="hidden" name="source_id" value="26">
                <button type="submit" class="btn btn-primary contact-form-submit-btn">Оставить
                    заявку</button>
                <p class="form-error-message" style="color: red; display: none;"></p>
            </form>

            <div class="form-group form-check mb-3">
              <input type="checkbox" id="privacy-policy-about2" name="privacy-policy" class="form-check-input" required>
               <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
            </div>
        </div>
        <div class="section-feedback__content-block">
            <div class="section-feedback__content-block-description">
                <p>Остались вопросы по майнингу? Наша команда экспертов проведет детальную консультацию: от
                    того, стоит ли инвестировать в
                    майнинг, до юридических нюансов и подводных камней.</p>
                <p>Мы сделаем майнинг простым и понятным для вас. Забудьте о сложностях — мы обеспечим
                    безопасность и прозрачность на
                    каждом этапе.</p>
            </div>
            <h3 class="section-feedback__content-block-title highlighted-color">Отдел консалтинга и продаж компании GIS
                Mining</h3>
            <div class="section-feedback__qr-code">
                <picture>
                    <!-- Источник для больших экранов (например, десктопы) -->
                    <source media="(min-width: 1024px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-feedback__qr-code_desktop.webp">

                    <!-- Изображение по умолчанию (для мобильных и браузеров без поддержки <picture>) -->
                    <img class="section-sibscribe-to-telegram__image"
                         src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/section-feedback__qr-code_tablet.webp"
                         alt="QR-код для связи с отделом продаж GIS Mining" loading="lazy" width="300" height="200">
                </picture>
            </div>
        </div>
    </section>

    <!-- POPUP SUCCESS -->
<!--    <div class="modal-overlay" id="mainSuccessModalOverlay">-->
<!--        <div class="modal" id="mainSuccessModal">-->
<!--            <button class="modal__close-btn" id="closeMainSuccessModalBtn" aria-label="Закрыть">-->
<!--                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                    <path d="M2.875 22.2988L22.875 2.71741" stroke="#131315" stroke-linecap="round" />-->
<!--                    <path d="M2.875 2.29883L22.875 21.8802" stroke="#131315" stroke-linecap="round" />-->
<!--                </svg>-->
<!---->
<!--            </button>-->
<!--            <div class="modal__icon">-->
<!--                Можно добавить иконку галочки SVG или Font Awesome -->
<!--                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">-->
<!--                    <path fill="#10B981"-->
<!--                          d="M25,2C12.318,2,2,12.318,2,25s10.318,23,23,23s23-10.318,23-23S37.682,2,25,2z M37.12,20.432 l-13.068,13.068c-0.196,0.196-0.454,0.294-0.712,0.294s-0.516-0.098-0.712-0.294l-6.918-6.918c-0.392-0.392-0.392-1.028,0-1.42s1.028-0.392,1.42,0l6.206,6.206l12.356-12.356c0.392-0.392,1.028-0.392,1.42,0S37.512,20.04,37.12,20.432z" />-->
<!--                </svg>-->
<!--            </div>-->
<!--            <h3 class="modal__title">Заявка принята!</h3>-->
<!--            <p class="modal__text">Благодарим! Мы свяжемся с вами в ближайшее время.</p>-->
<!--        </div>-->
<!--    </div>-->

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>