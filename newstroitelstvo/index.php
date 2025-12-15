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

// Уникальная картинка для этой страницы
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/stroitelstvo/stroitelstvo_open-graph_image.jpg';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "GIS Mining: строительство хостингов и площадок для майнинга в России - ваш дата-центр становится значительно ближе");
$APPLICATION->SetTitle("Строительство");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Как построить дата-центр, хостинг или ЦОД для майнинга? GIS Mining предлагает лучшие условия");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "GIS Mining: строительство хостингов и площадок для майнинга в России - ваш дата-центр становится значительно ближе");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Как построить дата-центр, хостинг или ЦОД для майнинга? GIS Mining предлагает лучшие условия");
$APPLICATION->SetPageProperty("OG:TYPE", "article");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);
$APPLICATION->SetPageProperty("OG:IMAGE:WIDTH", "1200");
$APPLICATION->SetPageProperty("OG:IMAGE:HEIGHT", "630");

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "GIS Mining: строительство хостингов и площадок для майнинга в России - ваш дата-центр становится значительно ближе");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Как построить дата-центр, хостинг или ЦОД для майнинга? GIS Mining предлагает лучшие условия");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-stroitelstvo");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header
?>

    <section class="hero-section change-color-section container">
        <h1 class="hero-section__title section-title"><span class="highlighted-color">Готовая инфраструктура
					<br></span>
            для промышленного майнинга
        </h1>
        <h2 class="hero-section__subtitle">Запустите собственный дата-центр с доходностью от 40% годовых
        </h2>
        <h3 class="hero-section__text">После ввода в эксплуатацию ваш объект увеличивается в цене
            до +30%!
        </h3>
        <div class="hero-section__video-wrapper wrapper-video">
            <video id="myFeatureVideo" class="wrapper-video__feature-video" width="640" height="360" muted loop
                   playsinline preload="metadata" poster="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/hero_section_image_desktop.webp">
                <!-- poster - это изображение, которое будет показано до загрузки видео -->
                <source src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/stroitelstvo_hero_section_video.mp4" type="video/mp4">
            </video>
        </div>
        <div class="hero-section__conditions-points">
            <p class="hero-section__condition-point"><span>от 24 мес</span> окупаемость проекта</p>
            <p class="hero-section__condition-point"><span>от 115 млн ₽</span> размер инвестиций</p>
            <div class="hero-section__condition-point-long-box">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/hero_section_fns_logo.webp" alt="Иконка ФНС" loading="lazy" width="48" height="51">
                <p>ООО «ГИС» внесен в реестр ФНС как оператор майнинговой
                    инфраструктуры</p>
            </div>
        </div>
    </section>

    <section class="offer-section container">
        <h2 class="visually-hidden">Наши преимущества и услуги</h2>
        <div class="offer-section__wrapper">
            <div class="offer-section__items">
                <div class="offer-section__item">
                    <div class="offer-section__icon">
                        <svg width="51" height="51" viewBox="0 0 51 51" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.320312" y="0.349365" width="50" height="50" rx="13.3929" fill="#5B61FF" />
                            <path
                                    d="M19.3105 32.7712H21.9551C22.3551 32.7712 22.6905 32.636 22.9613 32.3654C23.2319 32.0945 23.3672 31.7591 23.3672 31.3591V28.7146C23.3672 28.3146 23.2319 27.9792 22.9613 27.7083C22.6905 27.4378 22.3551 27.3025 21.9551 27.3025H19.3105C18.9105 27.3025 18.5751 27.4378 18.3043 27.7083C18.0337 27.9792 17.8984 28.3146 17.8984 28.7146V31.3591C17.8984 31.7591 18.0337 32.0945 18.3043 32.3654C18.5751 32.636 18.9105 32.7712 19.3105 32.7712ZM28.6855 32.7712H31.3301C31.7301 32.7712 32.0655 32.636 32.3363 32.3654C32.6069 32.0945 32.7422 31.7591 32.7422 31.3591V28.7146C32.7422 28.3146 32.6069 27.9792 32.3363 27.7083C32.0655 27.4378 31.7301 27.3025 31.3301 27.3025H28.6855C28.2855 27.3025 27.9501 27.4378 27.6793 27.7083C27.4087 27.9792 27.2734 28.3146 27.2734 28.7146V31.3591C27.2734 31.7591 27.4087 32.0945 27.6793 32.3654C27.9501 32.636 28.2855 32.7712 28.6855 32.7712ZM19.3105 23.3962H21.9551C22.3551 23.3962 22.6905 23.261 22.9613 22.9904C23.2319 22.7195 23.3672 22.3841 23.3672 21.9841V19.3396C23.3672 18.9396 23.2319 18.6042 22.9613 18.3333C22.6905 18.0628 22.3551 17.9275 21.9551 17.9275H19.3105C18.9105 17.9275 18.5751 18.0628 18.3043 18.3333C18.0337 18.6042 17.8984 18.9396 17.8984 19.3396V21.9841C17.8984 22.3841 18.0337 22.7195 18.3043 22.9904C18.5751 23.261 18.9105 23.3962 19.3105 23.3962ZM28.6855 23.3962H31.3301C31.7301 23.3962 32.0655 23.261 32.3363 22.9904C32.6069 22.7195 32.7422 22.3841 32.7422 21.9841V19.3396C32.7422 18.9396 32.6069 18.6042 32.3363 18.3333C32.0655 18.0628 31.7301 17.9275 31.3301 17.9275H28.6855C28.2855 17.9275 27.9501 18.0628 27.6793 18.3333C27.4087 18.6042 27.2734 18.9396 27.2734 19.3396V21.9841C27.2734 22.3841 27.4087 22.7195 27.6793 22.9904C27.9501 23.261 28.2855 23.3962 28.6855 23.3962ZM14.8637 38.6306C14.0743 38.6306 13.4062 38.3572 12.8594 37.8103C12.3125 37.2634 12.0391 36.5953 12.0391 35.806V14.8927C12.0391 14.1034 12.3125 13.4353 12.8594 12.8884C13.4062 12.3416 14.0743 12.0681 14.8637 12.0681H35.777C36.5663 12.0681 37.2344 12.3416 37.7812 12.8884C38.3281 13.4353 38.6016 14.1034 38.6016 14.8927V35.806C38.6016 36.5953 38.3281 37.2634 37.7812 37.8103C37.2344 38.3572 36.5663 38.6306 35.777 38.6306H14.8637Z"
                                    fill="white" />
                        </svg>
                    </div>
                    <div class="offer-section__details">
                        <p>Мы предлагаем комплексные решения для реализации индивидуальных проектов, адаптированных
                            именно под вашу инфраструктуру</p>
                    </div>
                </div>
                <div class="offer-section__item">
                    <div class="offer-section__icon">
                        <svg width="51" height="51" viewBox="0 0 51 51" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.320312" y="0.349365" width="50" height="50" rx="13.3929" fill="#5B61FF" />
                            <path
                                    d="M17.5082 29.1353C16.4566 29.1353 15.5628 28.7672 14.8266 28.031C14.0904 27.2948 13.7223 26.401 13.7223 25.3494C13.7223 24.2978 14.0904 23.4039 14.8266 22.6677C15.5628 21.9315 16.4566 21.5635 17.5082 21.5635C18.5598 21.5635 19.4536 21.9315 20.1898 22.6677C20.926 23.4039 21.2941 24.2978 21.2941 25.3494C21.2941 26.401 20.926 27.2948 20.1898 28.031C19.4536 28.7672 18.5598 29.1353 17.5082 29.1353ZM17.5082 33.7326C19.3832 33.7326 21.0504 33.1998 22.5098 32.1342C23.9691 31.0685 24.9542 29.7184 25.4648 28.0838H27.9648L29.816 29.7392C29.9522 29.8554 30.0965 29.9411 30.2488 29.9963C30.4009 30.0515 30.5661 30.0791 30.7445 30.0791C30.9227 30.0791 31.0879 30.0515 31.2402 29.9963C31.3926 29.9411 31.5368 29.8504 31.673 29.7244L33.7492 27.8885L36.0629 29.6912C36.2092 29.8013 36.3701 29.8865 36.5453 29.9467C36.7206 30.0068 36.8973 30.0239 37.0754 29.9978C37.2538 29.9718 37.4201 29.9207 37.5742 29.8447C37.7286 29.7684 37.8659 29.6622 37.9859 29.526L40.8707 26.3138C41.0009 26.1677 41.096 26.0136 41.1559 25.8513C41.216 25.6891 41.2461 25.5137 41.2461 25.3252C41.2461 25.1369 41.2121 24.9657 41.1441 24.8115C41.0759 24.6573 40.9767 24.5151 40.8465 24.3849L39.5004 23.0388C39.354 22.8925 39.1932 22.7853 39.018 22.7174C38.8427 22.6491 38.6609 22.615 38.4727 22.615H25.4648C24.9701 21.0444 24.0051 19.7103 22.5699 18.6127C21.1345 17.515 19.4473 16.9662 17.5082 16.9662C15.1645 16.9662 13.1813 17.7774 11.5586 19.3998C9.9362 21.0224 9.125 23.0056 9.125 25.3494C9.125 27.6931 9.9362 29.6763 11.5586 31.299C13.1813 32.9214 15.1645 33.7326 17.5082 33.7326Z"
                                    fill="white" />
                        </svg>

                    </div>
                    <div class="offer-section__details">
                        <p>Наша команда обеспечивает полный цикл услуг: от строительства майнинговых ферм и
                            технического
                            оснащения до комплектации
                            ЦОД асиками</p>
                    </div>
                </div>
                <div class="offer-section__item">
                    <div class="offer-section__icon">
                        <svg width="51" height="51" viewBox="0 0 51 51" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.320312" y="0.349365" width="50" height="50" rx="13.3929" fill="#5B61FF" />
                            <path
                                    d="M23.5891 40.1931C23.0563 40.1931 22.5961 40.0163 22.2086 39.6626C21.8209 39.3093 21.5849 38.8741 21.5008 38.3572L21.1192 35.4333C20.7007 35.2932 20.2715 35.097 19.8317 34.8447C19.3921 34.5921 18.999 34.3216 18.6524 34.0333L15.945 35.1841C15.4541 35.4005 14.9609 35.4215 14.4653 35.247C13.9694 35.0728 13.5843 34.7564 13.3098 34.2978L11.5489 31.2447C11.2744 30.7861 11.1954 30.3008 11.3118 29.7888C11.4279 29.2771 11.6943 28.855 12.111 28.5224L14.4516 26.7646C14.4157 26.5323 14.3902 26.299 14.375 26.0646C14.3599 25.8302 14.3524 25.5968 14.3524 25.3642C14.3524 25.1421 14.3599 24.9163 14.375 24.6869C14.3902 24.4574 14.4157 24.2065 14.4516 23.9341L12.111 22.1763C11.6943 21.8438 11.4304 21.419 11.3192 20.9021C11.208 20.3854 11.2896 19.8977 11.5641 19.4388L13.3098 16.431C13.5843 15.9823 13.9694 15.6684 14.4653 15.4892C14.9609 15.3098 15.4541 15.3283 15.945 15.5447L18.6372 16.6806C19.014 16.3822 19.4162 16.1093 19.8438 15.8619C20.2714 15.6145 20.6916 15.4156 21.1043 15.2654L21.5008 12.3416C21.5849 11.8246 21.8209 11.3895 22.2086 11.0361C22.5961 10.6824 23.0563 10.5056 23.5891 10.5056H27.0508C27.5836 10.5056 28.0438 10.6824 28.4313 11.0361C28.8191 11.3895 29.055 11.8246 29.1391 12.3416L29.5208 15.2802C29.9895 15.4505 30.4136 15.6494 30.793 15.8767C31.1727 16.1041 31.5559 16.372 31.9426 16.6806L34.7102 15.5447C35.2008 15.3283 35.6941 15.3098 36.1899 15.4892C36.6857 15.6684 37.0708 15.9823 37.345 16.431L39.0911 19.4541C39.3655 19.9126 39.4446 20.3979 39.3282 20.9099C39.212 21.4216 38.9456 21.8438 38.529 22.1763L36.1282 23.9791C36.1842 24.2314 36.2148 24.4673 36.22 24.6869C36.2249 24.9061 36.2274 25.127 36.2274 25.3494C36.2274 25.5616 36.2223 25.7775 36.2122 25.997C36.2023 26.2163 36.1663 26.4672 36.1043 26.7498L38.4598 28.5224C38.8765 28.855 39.1455 29.2771 39.2668 29.7888C39.3879 30.3008 39.3112 30.7861 39.0368 31.2447L37.2668 34.2826C36.9926 34.7414 36.6051 35.058 36.1043 35.2322C35.6036 35.4064 35.1077 35.3853 34.6168 35.1689L31.9426 34.0181C31.5559 34.3267 31.1612 34.5996 30.7586 34.8369C30.356 35.0744 29.9434 35.2682 29.5208 35.4185L29.1391 38.3572C29.055 38.8741 28.8191 39.3093 28.4313 39.6626C28.0438 40.0163 27.5836 40.1931 27.0508 40.1931H23.5891ZM25.3379 30.0369C26.6379 30.0369 27.7442 29.5806 28.6567 28.6681C29.5692 27.7556 30.0254 26.6494 30.0254 25.3494C30.0254 24.0494 29.5692 22.9431 28.6567 22.0306C27.7442 21.1181 26.6379 20.6619 25.3379 20.6619C24.0218 20.6619 22.9115 21.1181 22.0071 22.0306C21.1027 22.9431 20.6504 24.0494 20.6504 25.3494C20.6504 26.6494 21.1027 27.7556 22.0071 28.6681C22.9115 29.5806 24.0218 30.0369 25.3379 30.0369Z"
                                    fill="white" />
                        </svg>
                    </div>
                    <div class="offer-section__details">
                        <p>Мы гарантируем ввод в эксплуатацию и оперативное управление от профессионального
                            оператора
                            майнинговой инфраструктуры</p>
                    </div>
                </div>
                <div class="offer-section__description">
                    <p>Инвестируйте в будущее уже сегодня и получайте не только стабильный доход, но и пассивный
                        прирост капитала. Станьте
                        частью быстрорастущей индустрии: свяжитесь с нами, и мы поможем построить вашу
                        майнинг-площадку максимально быстро и
                        выгодно</p>
                </div>
                <button class=" btn offer-section__btn btn-primary js-open-popup-form">Получить
                    консультацию</button>
            </div>
        </div>
    </section>

    <section class="project-implementation-section container">
        <div class="project-implementation-section__wrapper">
            <div class="project-implementation-section__title-wrapper">
                <h2 class="project-implementation-section__title section-title">Реализация проекта:<br>
                    <span>строительство
							дата-центра для майнинга вместе с GIS Mining</span>
                </h2>
            </div>
            <ol class="project-implementation-section__list-steps list-steps">
                <li>
                    <span class="list-steps__title">Формирование требований</span>
                    <span class="list-steps__text">Определение необходимого количества свободных мощностей. 1 МВт =
							300 асиков = 1 контейнер</span>
                </li>
                <li>
                    <span class="list-steps__title">Подбор объекта</span>
                    <span class="list-steps__text">Поиск свободных мощностей и подходящей инстраструктуры</span>
                </li>
                <li>
                    <span class="list-steps__title">Договор и юридические согласования</span>
                    <span class="list-steps__text">Фиксируем ключевые параметры: цена за электричество и формат
							сделки (аренда или выкуп юр. лица)</span>
                </li>
                <li>
                    <span class="list-steps__title">Строительство</span>
                    <span class="list-steps__text">Монтаж линии электропередач, установка контейнеров и
							трансформаторов, обеспечение физической, пожарной и электробезопасности</span>
                </li>
                <li>
                    <span class="list-steps__title">Поиск и найм команды</span>
                    <span class="list-steps__text">Комплектование команды технической службы для обслуживания
							дата-центра</span>
                </li>
                <li>
                    <span class="list-steps__title">Ввод дата-центра в эксплуатацию</span>
                    <span class="list-steps__text">Ввод дата-центра в эксплуатацию</span>
                </li>
                <li>
                    <span class="list-steps__title">Поиск клиентов</span>
                    <span class="list-steps__text">Поиск клиентов, которые хотят разместить асики в вашем
							дата-центре</span>
                </li>
            </ol>
        </div>
    </section>

    <section class="benefits-section container">
        <div class="benefits-section__wrapper">
            <h2 class="benefits-section__title section-title">Почему <span class="highlighted-color">выгодно</span>
                строить свой
                дата-центр для
                майнинга?
            </h2>
            <div class="benefits-section__items">
                <div class="benefits-section__item-description description-item">
                    <div class="description-item__icon">
                        <svg width="41" height="41" viewBox="0 0 41 41" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                    d="M22.5869 0.349365C22.6893 0.349365 22.7931 0.363937 22.8955 0.394287C23.392 0.541672 23.7107 1.07437 23.6514 1.58862L21.9062 16.7585H30.0127C30.3832 16.7586 30.7272 16.8843 30.9209 17.2C31.1148 17.5158 31.1308 17.8793 30.9629 18.2097L20 39.7664C19.8143 40.1315 19.4416 40.3494 19.0488 40.3494C18.9475 40.3494 18.8445 40.3407 18.7432 40.3103C18.2495 40.1625 17.932 39.7316 17.9893 39.2195L19.6289 24.6208H11.6309C11.2616 24.6208 10.9181 24.3654 10.7236 24.0515C10.5296 23.7376 10.5115 23.2977 10.6758 22.9675L21.6377 0.941162C21.8209 0.573245 22.1933 0.349368 22.5869 0.349365ZM25.6016 3.29468C33.0562 5.38591 38.539 12.2425 38.5391 20.3562C38.5391 29.2704 31.9211 36.6673 23.3408 37.8943L25.9297 32.8005C30.8215 30.785 34.2744 25.9667 34.2744 20.3562C34.2743 14.4359 30.4298 9.3967 25.1064 7.60327L25.6016 3.29468ZM15.7988 7.87671C10.8615 9.87081 7.36817 14.7128 7.36816 20.3562C7.36816 26.2745 11.2098 31.312 16.5303 33.1072L16.0459 37.4197C8.5887 35.33 3.10352 28.4716 3.10352 20.3562C3.10352 11.4363 9.73012 4.03624 18.3184 2.81616L15.7988 7.87671Z"
                                    fill="#5B61FF" />
                        </svg>
                    </div>
                    <div class="description-item__container">
                        <h3 class="description-item__title">
                            Входной тариф на электроэнергию
                        </h3>
                        <p class="description-item__text">
                            <span class="highlighted-color">Вы экономите</span><br> на электричестве ~19 млн₽ в год
                        </p>
                    </div>
                </div>
                <div class="benefits-section__item-info info-item">
                    <div class="info-item__grid">
                        <div class="info-item__title"></div>
                        <div class="info-item__title"><span class="text-grey">Размещение</span>
                            в собственном <span class="text-grey">data-центре</span>
                        </div>
                        <div class="info-item__title"><span class="text-grey">Размещение
									в data-центре </span>компании Х</div>
                        <div class="info-item__points-column">
                            <p class="info-item__point">Входной тариф на ЭЭ</p>
                            <p class="info-item__point">Потребление 1 ASIC-майнера</p>
                            <p class="info-item__point">Всего ASIC-майнеров</p>
                            <p class="info-item__point">Общее потребление</p>
                            <p class="info-item__point">Общая стоимость размещения, мес.</p>
                        </div>

                        <div class="info-item__points-column">
                            <p class="info-item__point font-main-bold">3,7₽ за 1 кВт/ч</p>
                            <p class="info-item__point text-grey">3,5 кВт/ч</p>
                            <p class="info-item__point text-grey">300 (1 контейнер)</p>
                            <p class="info-item__point text-grey">1,05 МВт/ч</p>
                            <p class="info-item__point font-heading">2 797 200₽</p>
                        </div>

                        <div class="info-item__points-column">
                            <p class="info-item__point font-main-bold">5,5₽ за 1 кВт/ч</p>
                            <p class="info-item__point text-grey">3,5 кВт/ч</p>
                            <p class="info-item__point text-grey">300 (1 контейнер)</p>
                            <p class="info-item__point text-grey">1,05 МВт/ч</p>
                            <p class="info-item__point info-item__point font-heading">4 384 800₽</p>
                        </div>

                    </div>
                </div>

                <div class="benefits-section__item-description description-item">
                    <div class="description-item__icon">
                        <svg width="43" height="43" viewBox="0 0 43 43" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_5283_73817)">
                                <path
                                        d="M21.3203 0.707764C17.1669 0.707764 13.1068 1.93939 9.65335 4.2469C6.19992 6.55441 3.50829 9.83417 1.91885 13.6714C0.329411 17.5087 -0.0864588 21.7311 0.723831 25.8047C1.53412 29.8783 3.53418 33.6201 6.47108 36.557C9.40799 39.4939 13.1498 41.494 17.2234 42.3043C21.297 43.1146 25.5194 42.6987 29.3567 41.1093C33.1939 39.5198 36.4737 36.8282 38.7812 33.3748C41.0887 29.9213 42.3203 25.8612 42.3203 21.7078C42.3152 16.1398 40.101 10.8014 36.1639 6.86424C32.2267 2.92709 26.8883 0.712936 21.3203 0.707764ZM12.9008 13.2882C13.2154 12.9675 13.5908 12.7127 14.0051 12.5388C14.4193 12.3649 14.8641 12.2753 15.3134 12.2753C15.7626 12.2753 16.2074 12.3649 16.6216 12.5388C17.0359 12.7127 17.4113 12.9675 17.7259 13.2882C18.0762 13.5971 18.3595 13.9744 18.5583 14.397C18.7571 14.8196 18.8673 15.2784 18.8819 15.7452C18.8966 16.2119 18.8154 16.6767 18.6435 17.1109C18.4716 17.5452 18.2125 17.9395 17.8823 18.2697C17.5521 18.6 17.1577 18.859 16.7235 19.0309C16.2893 19.2029 15.8245 19.284 15.3577 19.2694C14.8909 19.2547 14.4321 19.1446 14.0096 18.9457C13.587 18.7469 13.2096 18.4636 12.9008 18.1134C12.5801 17.7987 12.3253 17.4233 12.1514 17.0091C11.9774 16.5948 11.8879 16.1501 11.8879 15.7008C11.8879 15.2515 11.9774 14.8068 12.1514 14.3925C12.3253 13.9783 12.5801 13.6029 12.9008 13.2882ZM16.8078 30.4399C16.6258 30.6701 16.3944 30.8564 16.1306 30.9849C15.8668 31.1134 15.5775 31.1808 15.284 31.1822C14.9144 31.1835 14.5519 31.0798 14.2388 30.8833C13.9257 30.6867 13.6748 30.4054 13.5153 30.0719C13.3557 29.7385 13.294 29.3666 13.3374 28.9995C13.3808 28.6324 13.5274 28.2851 13.7603 27.998L25.8134 12.9757C26.1357 12.5732 26.6041 12.3144 27.1164 12.2559C27.6287 12.1973 28.1434 12.3437 28.5482 12.6631C28.7504 12.8229 28.9189 13.0211 29.0441 13.2463C29.1693 13.4715 29.2487 13.7193 29.2777 13.9753C29.3068 14.2313 29.2849 14.4905 29.2134 14.7381C29.1418 14.9856 29.022 15.2165 28.8608 15.4175L16.8078 30.4399ZM29.7203 30.1273C29.4057 30.448 29.0303 30.7028 28.6161 30.8767C28.2018 31.0507 27.7571 31.1402 27.3078 31.1402C26.8585 31.1402 26.4137 31.0507 25.9995 30.8767C25.5852 30.7028 25.2098 30.448 24.8952 30.1273C24.3201 29.4751 24.0152 28.6282 24.0425 27.7591C24.0698 26.89 24.4272 26.0639 25.0421 25.4491C25.657 24.8342 26.483 24.4767 27.3521 24.4495C28.2213 24.4222 29.0681 24.7271 29.7203 25.3022C30.0423 25.6155 30.2986 25.9899 30.4742 26.4034C30.6498 26.817 30.7412 27.2614 30.743 27.7107C30.7449 28.1599 30.6571 28.6051 30.4848 29.02C30.3126 29.435 30.0593 29.8114 29.7399 30.1273H29.7203Z"
                                        fill="#5B61FF" />
                            </g>
                            <defs>
                                <clipPath id="clip0_5283_73817">
                                    <rect width="42" height="42" fill="white"
                                          transform="translate(0.320312 0.707764)" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div class="description-item__container">
                        <h3 class="description-item__title">
                            Входная комиссия на размещение
                        </h3>
                        <p class="description-item__text">
                            <span class="highlighted-color">Вы экономите</span><br> на размещении ~4,5 млн ₽
                        </p>
                    </div>
                </div>
                <div class="benefits-section__item-info info-item">
                    <div class="info-item__grid">
                        <div class="info-item__title"></div>
                        <div class="info-item__title"><span class="text-grey">Размещение</span>
                            в собственном <span class="text-grey">data-центре</span>
                        </div>
                        <div class="info-item__title"><span class="text-grey">Размещение
									в data-центре </span>компании Х
                        </div>
                        <div class="info-item__points-column">
                            <p class="info-item__point">Входная комиссия на размещение</p>
                            <p class="info-item__point">Всего ASIC-майнеров</p>
                            <p class="info-item__point">Общая комиссия</p>
                        </div>
                        <div class="info-item__points-column">
                            <p class="info-item__point font-main-bold">0 ₽</p>
                            <p class="info-item__point text-grey">300 (1 контейнер)</p>
                            <p class="info-item__point info-item__point font-heading">0 ₽</p>
                        </div>
                        <div class="info-item__points-column">
                            <p class="info-item__point font-main-bold">~15 000₽ / 1 асик</p>
                            <p class="info-item__point text-grey">300 (1 контейнер)</p>
                            <p class="info-item__point info-item__point font-heading">4 500 000 ₽</p>
                        </div>

                    </div>
                </div>


                <div class="benefits-section__item-description description-item">
                    <div class="description-item__icon">
                        <svg width="57" height="57" viewBox="0 0 57 57" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                    d="M28.3203 0.438965C18.4804 0.438965 10.2734 8.36537 10.2734 18.4858C10.2734 22.3361 11.4304 25.7693 13.6508 28.9865L26.9392 49.7218C27.5839 50.7298 29.058 50.7278 29.7014 49.7218L43.0474 28.9159C45.2201 25.8446 46.3672 22.2382 46.3672 18.4858C46.3672 8.53479 38.2714 0.438965 28.3203 0.438965ZM28.3203 26.689C23.7973 26.689 20.1172 23.0088 20.1172 18.4858C20.1172 13.9629 23.7973 10.2827 28.3203 10.2827C32.8433 10.2827 36.5234 13.9629 36.5234 18.4858C36.5234 23.0088 32.8433 26.689 28.3203 26.689Z"
                                    fill="#5B61FF" />
                            <path
                                    d="M41.1461 38.1399L32.8849 51.0557C30.7465 54.3896 25.8822 54.3787 23.7541 51.0588L15.4795 38.1433C8.19903 39.8266 3.71094 42.9102 3.71094 46.5951C3.71094 52.9894 16.3906 56.4389 28.3203 56.4389C40.2501 56.4389 52.9297 52.9894 52.9297 46.5951C52.9297 42.9075 48.4354 39.8223 41.1461 38.1399Z"
                                    fill="#5B61FF" />
                        </svg>
                    </div>
                    <div class="description-item__container">
                        <h3 class="description-item__title">
                            Места для размещения
                        </h3>
                        <p class="description-item__text">
                            <span class="highlighted-color">Гибкость,</span><br> возможность масштабирования ~4,5
                            млн ₽
                        </p>
                        <p class="description-item__text">
                            <span class="highlighted-color">Снижение расходов</span><br> на электроэнергию и
                            обслуживание
                        </p>
                    </div>
                </div>
                <div class="benefits-section__item-info info-item-two-columns">
                    <div class="info-item-two-columns__grid">
                        <div class="info-item-two-columns__title"><span class="text-grey">Размещение</span>
                            в собственном <span class="text-grey">data-центре</span>
                        </div>
                        <div class="info-item-two-columns__title"><span class="text-grey">Размещение
									в data-центре </span>компании Х
                        </div>
                        <div class="info-item-two-columns__points-column">
                            <p class="info-item-two-columns__point font-main-bold">Гибкость, возможность
                                масштабирования
                            </p>
                            <p class="info-item-two-columns__point">Вы можете самостоятельно настраивать и изменять
                                расположение оборудования, а также масштабировать ферму в соответствии с
                                потребностями бизнеса
                            </p>
                        </div>
                        <div class="info-item-two-columns__points-column">
                            <p class="info-item-two-columns__point font-main-bold">Снижение затрат на
                                электроэнергию и
                                обслуживание
                            </p>
                            <p class="info-item-two-columns__point">Есть возможность размещения большого количества
                                асиков в одном месте, что снижает затраты на э/э и сопровождение</p>
                        </div>



                        <div class="info-item-two-columns__points-column">
                            <p class="info-item-two-columns__point font-main-bold">Мало места</p>
                            <p class="info-item-two-columns__point">В последние 4 года у большинства дата-центров
                                наполняемость
                                превышает 120%: нет возможности настраивать и изменять
                                расположение оборудования, а также масштабировать ферму</p>
                        </div>
                        <div class="info-item-two-columns__points-column">
                            <p class="info-item-two-columns__point info-item__point font-main-bold">Переплата за
                                электроэнергию
                                и
                                обслуживание</p>
                            <p class="info-item-two-columns__point">Часто отсутствуют варианты для размещения
                                большого
                                количества асиков в одном месте, что повышает затраты на
                                электроэнергию и обслуживание</p>
                        </div>

                    </div>
                </div>

                <div class="benefits-section__item-description description-item">
                    <div class="description-item__icon">
                        <svg width="41" height="43" viewBox="0 0 41 43" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                    d="M39.0946 26.2646L38.1791 25.7363C35.1497 23.9874 35.1423 19.4322 38.1791 17.6792L39.0946 17.1509C40.2701 16.4717 40.6737 14.964 39.9953 13.7893L37.0151 8.62624C36.3367 7.45155 34.8281 7.04796 33.6534 7.72554L32.7412 8.25218C29.7332 9.98878 25.7604 7.75671 25.7604 4.23019V3.1687C25.7604 1.8119 24.6562 0.707764 23.2994 0.707764H17.3382C15.9814 0.707764 14.8773 1.8119 14.8773 3.1687V4.23019C14.8773 7.75753 10.9029 9.98878 7.89562 8.25218L6.98425 7.72554C5.80628 7.0455 4.30265 7.44827 3.62261 8.62624L0.641595 13.7893C-0.0368033 14.964 0.367611 16.4717 1.5423 17.1509L2.45859 17.6792C5.48718 19.4281 5.49456 23.9833 2.45859 25.7363L1.5423 26.2646C0.367611 26.9438 -0.0368033 28.4516 0.641595 29.6262L3.62261 34.7893C4.30101 35.964 5.80874 36.3684 6.98425 35.69L7.89562 35.1633C10.9053 33.4259 14.8773 35.6637 14.8773 39.1853V40.2468C14.8773 41.6036 15.9814 42.7078 17.3382 42.7078H23.2994C24.6562 42.7078 25.7604 41.6036 25.7604 40.2468V39.1853C25.7604 35.6588 29.734 33.4267 32.7412 35.1633L33.6534 35.69C34.8281 36.3684 36.3367 35.964 37.0151 34.7893L39.9953 29.6262C40.6753 28.4491 40.2725 26.9455 39.0946 26.2646ZM20.3184 32.2586C14.5008 32.2586 9.76757 27.5254 9.76757 21.7078C9.76757 15.8901 14.5008 11.1569 20.3184 11.1569C26.1369 11.1569 30.8701 15.8901 30.8701 21.7078C30.8701 27.5254 26.1369 32.2586 20.3184 32.2586Z"
                                    fill="#5B61FF" />
                            <path
                                    d="M20.3184 13.6174C15.8576 13.6174 12.2285 17.2465 12.2285 21.7074C12.2285 26.1682 15.8576 29.7973 20.3184 29.7973C24.7793 29.7973 28.4092 26.1682 28.4092 21.7074C28.4092 17.2465 24.7793 13.6174 20.3184 13.6174ZM24.6275 20.8222L19.581 24.5686C19.0798 24.9402 18.3784 24.8787 17.9502 24.4226L15.8461 22.1799C15.381 21.6844 15.4056 20.9051 15.9011 20.4408C16.3965 19.9757 17.175 20.0003 17.6401 20.4958L18.9945 21.9387L23.1608 18.8461C23.7063 18.4417 24.4774 18.5557 24.8827 19.1012C25.2871 19.6467 25.173 20.4178 24.6275 20.8222Z"
                                    fill="#5B61FF" />
                        </svg>
                    </div>
                    <div class="description-item__container">
                        <h3 class="description-item__title">
                            Управление и контроль
                        </h3>
                        <p class="description-item__text">
                            <span class="highlighted-color">Полный контроль</span>
                        </p>
                    </div>
                </div>
                <div class="benefits-section__item-info info-item-two-columns">
                    <div class="info-item-two-columns__grid">
                        <div class="info-item-two-columns__title"><span class="text-grey">Размещение</span>
                            в собственном <span class="text-grey">data-центре</span>
                        </div>
                        <div class="info-item-two-columns__title"><span class="text-grey">Размещение
									в data-центре </span>компании Х</div>
                        <div class="info-item-two-columns__points-column">
                            <p class="info-item-two-columns__point font-main-bold">Полный контроль</p>
                            <p class="info-item-two-columns__point">Если вы размещаете оборудование на своей
                                площадке, у вас
                                остается полный контроль над всеми аспектами его управления. Вы
                                можете самостоятельно управлять условиями эксплуатации, безопасностью и доступом
                                к
                                оборудованию</p>
                        </div>
                        <div class="info-item-two-columns__points-column">
                            <p class="info-item-two-columns__point font-main-bold">Риски</p>
                            <p class="info-item-two-columns__point">Возгорание одного асика или сегмента - пожар на
                                всей
                                ферме.
                                Конфискация асиков отдельных арендаторов - риски потерять также и ваши майнеры
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="statistics-section container">
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
                        <source media="(min-width: 1024px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.webp" type="image/webp">
                        <source media="(min-width: 1024px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.jpg" type="image/jpeg">

                        <!-- 2. Для средних экранов (768px - 1023px): -->
                        <source media="(min-width: 768px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.webp" type="image/webp">
                        <source media="(min-width: 768px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_desktop.jpg" type="image/jpeg">

                        <!--3. Для мобильных экранов (< 767px): -->
                        <source srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_mobile.webp" type="image/webp">

                        <!-- Изображение по умолчанию (для мобильных в старых браузерах)
                                                 и обязательный фолбэк для всех браузеров, не поддерживающих <picture>. -->
                        <img class="section-about-seo__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_mobile.jpg"
                             alt="Фото руководителя GIS Mining" loading="lazy" width="399" height="447">
                    </picture>
                </div>
            </div>
            <div class="statistics-section__grid">
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        1 000+
                    </div>
                    <div class="statistics-section__text">
                        Довольных клиентов
                    </div>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        500 млн+
                    </div>
                    <div class="statistics-section__text">
                        Годовой оборот за 2024 год
                    </div>
                    <a href="#" class="statistics-section__link font-main-bold highlighted-color">Rusprofile</a>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info font-main-bold highlighted-color">
                        100%
                    </div>
                    <div class="statistics-section__text">
                        Открытость: вы можете приехать на объект и лично проверить работу собственного оборудования
                        в любой момент
                    </div>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        2.5 EH/s
                    </div>
                    <div class="statistics-section__text">
                        Общая мощность всех устройств
                    </div>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        19 000+
                    </div>
                    <div class="statistics-section__text">
                        Устройств под нашим контролем
                    </div>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        5 лет
                    </div>
                    <div class="statistics-section__text">
                        Занимаемся продажей оборудования и обслуживанием дата-центров
                    </div>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        7 минут
                    </div>
                    <div class="statistics-section__text">
                        Средняя скорость реагирования на вышедший из строя майнер для сохранения up-time ~99%
                    </div>
                </div>
                <div class="statistics-section__item">
                    <div class="statistics-section__info">
                        0 ₽
                    </div>
                    <div class="statistics-section__text">
                        Стоимость установки асиков
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gallery-section container">
        <h2 class="gallery-section__title section-title">Галерея</h2>
        <div class="gallery-section__big-title">
            <p>Галерея</p>
        </div>
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
							}' data-swiper-destroy-breakpoint="768"> <!-- Уничтожать НАЧИНАЯ с 768px -->
            <div class="swiper-wrapper">
                <div class="swiper-slide placement-gallery__card"><img
                            src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide1.webp" alt="Фото дата-центра 1"
                            loading="lazy" width="554" height="407">
                </div>
                <div class="swiper-slide placement-gallery__card"><img
                            src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide2.webp" alt="Фото дата-центра 2"
                            loading="lazy" width="410" height="407">
                </div>
                <div class="swiper-slide placement-gallery__card"><img
                            src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide3.webp" alt="Фото дата-центра 3"
                            loading="lazy" width="409" height="385">
                </div>
                <div class="swiper-slide placement-gallery__card"><img
                            src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide4.webp" alt="Фото дата-центра 4"
                            loading="lazy" width="554" height="486">
                </div>
                <div class="swiper-slide placement-gallery__card"><img
                            src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide5.webp" alt="Фото дата-центра 5"
                            loading="lazy" width="411" height="634">
                </div>
                <div class="swiper-slide placement-gallery__card"><img
                            src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide6.webp" alt="Фото дата-центра 6"
                            loading="lazy" width="686" height="476">
                </div>
            </div>
            <div class="swiper-pagination placement-gallery-slider__pagination"></div>
        </div>
        <div class="gallery-section__gallery-image image-gallery-1">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide1.webp" alt="Фото дата-центра	" loading="lazy" width="554" height="407">
        </div>
        <div class="gallery-section__gallery-image image-gallery-2">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide2.webp" alt="Фото дата-центра	" loading="lazy" width="410" height="407">
        </div>
        <div class="gallery-section__gallery-image image-gallery-3">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide3.webp" alt="Фото дата-центра	" loading="lazy" width="409" height="385">
        </div>
        <div class="gallery-section__gallery-image image-gallery-4">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide4.webp" alt="Фото дата-центра	" loading="lazy" width="554" height="486">
        </div>
        <div class="gallery-section__gallery-image image-gallery-5">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide5.webp" alt="Фото дата-центра	" loading="lazy" width="411" height="634">
        </div>
        <div class="gallery-section__gallery-image image-gallery-6">
            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/stroitelstvo/gallery-section_slide6.webp" alt="Фото дата-центра	" loading="lazy" width="686" height="476">
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
                <h2 class="form-popup__title">Инвестиции в свой дата-центр</h2>
                <div class="form-popup__img-wrapper">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/components/popup_form_image.png" alt="Контейнер для майнинг фермы"
                         class="form-popup__img" loading="lazy" width="390" height="170">
                </div>
            </div>
            <form class="form-popup__popup-form js-ajax-form" id="contactFormPopup" data-metric-goal="send-consult-lead">

                <p class="form-popup__cta">Заполните форму, чтобы оставить заявку на инвестирование в data-центр. Мы перезвоним вам в ближайшее время
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
        <a href="https://t.me/gismining_chat_bot"
           id="tg-link-footer"
           target="_blank"
           rel="noopener noreferrer nofollow"
           class="form-popup__social-square">
            <svg width="48" height="48" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="0.75" y="0.21875" width="24" height="24" rx="5.25" fill="#006FFF"></rect>
                <path d="M18.2212 4.85117C18.2212 4.85117 19.6088 4.20189 19.4932 5.77873C19.4546 6.42802 19.1077 8.70052 18.8379 11.1585L17.9128 18.4398C17.9128 18.4398 17.8358 19.5065 17.1419 19.692C16.4481 19.8775 15.4074 19.0428 15.2147 18.8572C15.0605 18.7181 12.3239 16.6311 11.3602 15.6108C11.0904 15.3325 10.782 14.776 11.3988 14.1267L15.446 9.48896C15.9085 8.93242 16.371 7.63385 14.4438 9.21069L9.04754 13.6166C9.04754 13.6166 8.43082 14.0803 7.2745 13.6629L4.76908 12.7354C4.76908 12.7354 3.844 12.0397 5.42434 11.344C9.27883 9.16428 14.0198 6.93815 18.2212 4.85117Z" fill="white"></path>
            </svg>
        </a>

        <!-- WhatsApp — полностью корректный SVG из референса -->
        <a href="https://api.whatsapp.com/send/?phone=%2B79311116071"
            id="wa-link-footer"
           target="_blank"
           rel="noopener noreferrer nofollow"
           class="form-popup__social-square">
            <svg width="48" height="48" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_5008_11409)">
                    <rect y="0.21875" width="24" height="24" fill="#18A53A"></rect>
                    <path d="M15.6015 13.3381C15.5723 13.3241 14.4787 12.7856 14.2844 12.7157C14.2051 12.6872 14.1201 12.6594 14.0297 12.6594C13.882 12.6594 13.758 12.7329 13.6613 12.8775C13.5521 13.0399 13.2214 13.4264 13.1192 13.5419C13.1059 13.5572 13.0876 13.5754 13.0767 13.5754C13.0669 13.5754 12.8977 13.5057 12.8465 13.4834C11.6733 12.9738 10.7829 11.7484 10.6608 11.5417C10.6434 11.512 10.6426 11.4985 10.6425 11.4985C10.6468 11.4828 10.6862 11.4433 10.7066 11.4229C10.7661 11.3639 10.8307 11.2862 10.8931 11.2111C10.9227 11.1755 10.9523 11.1398 10.9814 11.1062C11.0719 11.0008 11.1123 10.919 11.159 10.8242L11.1835 10.775C11.2977 10.5482 11.2002 10.3568 11.1687 10.295C11.1428 10.2433 10.681 9.12882 10.632 9.01174C10.5139 8.7292 10.3579 8.59766 10.1411 8.59766C10.121 8.59766 10.1411 8.59766 10.0568 8.60121C9.95405 8.60555 9.39468 8.67919 9.14737 8.83509C8.8851 9.00044 8.44141 9.52751 8.44141 10.4544C8.44141 11.2887 8.97082 12.0764 9.19812 12.3759C9.20377 12.3835 9.21414 12.3988 9.22919 12.4208C10.0997 13.6921 11.1848 14.6342 12.2849 15.0737C13.344 15.4967 13.8455 15.5456 14.1306 15.5456H14.1306C14.2504 15.5456 14.3463 15.5362 14.4309 15.5279L14.4846 15.5228C14.8505 15.4904 15.6545 15.0737 15.8374 14.5655C15.9814 14.1652 16.0194 13.7279 15.9236 13.5691C15.8579 13.4612 15.7448 13.4069 15.6015 13.3381Z" fill="white"></path>
                    <path d="M12.1335 4.71875C8.07085 4.71875 4.76562 7.99914 4.76562 12.0313C4.76562 13.3354 5.11463 14.612 5.77578 15.7292L4.51031 19.4621C4.48674 19.5317 4.50427 19.6087 4.55575 19.6611C4.59292 19.699 4.64332 19.7195 4.6948 19.7195C4.71453 19.7195 4.7344 19.7165 4.75378 19.7103L8.64618 18.4735C9.71133 19.0426 10.9152 19.343 12.1336 19.343C16.1958 19.343 19.5007 16.063 19.5007 12.0313C19.5007 7.99914 16.1958 4.71875 12.1335 4.71875ZM12.1335 17.8198C10.9871 17.8198 9.87668 17.4887 8.92219 16.8624C8.89009 16.8413 8.85283 16.8305 8.81533 16.8305C8.79551 16.8305 8.77564 16.8335 8.7563 16.8396L6.80645 17.4594L7.4359 15.6024C7.45626 15.5423 7.44608 15.476 7.40857 15.4248C6.68172 14.4316 6.2975 13.2582 6.2975 12.0313C6.2975 8.83909 8.91552 6.24201 12.1335 6.24201C15.351 6.24201 17.9688 8.83909 17.9688 12.0313C17.9688 15.2231 15.3511 17.8198 12.1335 17.8198Z" fill="white"></path>
                </g>
                <defs>
                    <clipPath id="clip0_5008_11409">
                        <rect width="24" height="24" rx="5.25" transform="matrix(1 0 0 -1 0 24.2188)" fill="white"></rect>
                    </clipPath>
                </defs>
            </svg>
        </a>

    </div>
</div>


                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="form_name" value="Инвестиции в свой дата-центр">
                <input type="hidden" name="page_url" value="">
                <input type="hidden" name="source_id" value="23">


                <button type="submit" class="form-popup__submit-btn btn btn-primary"
                        id="submitContactBtnPopup">Оставить заявку</button>

                <div class="form-group form-check mb-3">
              <input type="checkbox" id="privacy-policy-construction" name="privacy-policy" class="form-check-input" required>
               <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
            </div>
                <p class="form-popup__error-message form-error-message" style="color: red; display: none;"></p>
            </form>
        </div>
    </div>

    <!-- PopUp Success -->
    <div class="modal-overlay" id="successModalOverlay">
        <div class="modal" id="successModal">
            <button class="modal__close-btn" id="closeSuccessModalBtn" aria-label="Закрыть">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.875 22.2988L22.875 2.71741" stroke="#131315" stroke-linecap="round" />
                    <path d="M2.875 2.29883L22.875 21.8802" stroke="#131315" stroke-linecap="round" />
                </svg>

            </button>
            <div class="modal__icon">
                <!-- Можно добавить иконку галочки SVG или Font Awesome -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">
                    <path fill="#10B981"
                          d="M25,2C12.318,2,2,12.318,2,25s10.318,23,23,23s23-10.318,23-23S37.682,2,25,2z M37.12,20.432 l-13.068,13.068c-0.196,0.196-0.454,0.294-0.712,0.294s-0.516-0.098-0.712-0.294l-6.918-6.918c-0.392-0.392-0.392-1.028,0-1.42s1.028-0.392,1.42,0l6.206,6.206l12.356-12.356c0.392-0.392,1.028-0.392,1.42,0S37.512,20.04,37.12,20.432z" />
                </svg>
            </div>
            <div class="modal__title">Заявка принята!</div>
            <p class="modal__text">Благодарим! Мы свяжемся с вами в ближайшее время.</p>
        </div>
    </div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>