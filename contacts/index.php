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

$APPLICATION->SetPageProperty("TITLE", "Контакты компании GIS Mining: свяжитесь с нами любым удобным для вас способом");
$APPLICATION->SetTitle("Контакты");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Контакты компании GIS Mining: свяжитесь с нами любым удобным для вас способом");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("OG:TYPE", "profile"); // Для контактов хорошо подходит тип "profile" или "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Контакты компании GIS Mining: свяжитесь с нами любым удобным для вас способом");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-contacts");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header
?>

    <section class="section-contacts container">        
        <h1 class="section-contacts__title section-title highlighted-color">Контакты</h1>
        <div class="section-contacts__wrapper">
            <div class="section-contacts__items">
                <div class="section-contacts__item">
                    <div class="section-contacts__item-title-wrapper">
                        <div class="section-contacts__item-icon">
                            <svg width="24" height="32" viewBox="0 0 24 32" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M12.0009 0C5.606 0 0.421875 5.18413 0.421875 11.579C0.421875 20.7958 12.0009 32 12.0009 32C12.0009 32 23.5797 20.7958 23.5797 11.579C23.5797 5.18413 18.3957 0 12.0009 0ZM12.0009 20.0282C7.33437 20.0282 3.55156 16.2454 3.55156 11.579C3.55156 6.91256 7.33437 3.12963 12.0009 3.12963C16.6672 3.12963 20.4502 6.91256 20.4502 11.579C20.4502 16.2454 16.6672 20.0282 12.0009 20.0282Z"
                                        fill="#5B61FF" />
                            </svg>
                        </div>
                        <div class="section-contacts__item-title">
                            <p>Адрес:</p>
                        </div>
                    </div>
                    <div class="section-contacts__content">
                        <a href="#" class="copyable-text">
                            <!-- Добавим класс для текста, который можно скопировать -->
                            117105, Москва, Варшавское шоссе, 1, стр. 1-2 W-Plaza
                        </a>
                        <span class="js-copy-icon" title="Скопировать адрес">
                                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/icon-copy.svg" alt="Иконка скопировать" loading="lazy" width="16" height="20">
                            </span>
                    </div>
                </div>
                <div class="section-contacts__item">
                    <div class="section-contacts__item-title-wrapper">
                        <div class="section-contacts__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M31.1287 23.4848L26.663 19.0191C25.0681 17.4242 22.3567 18.0622 21.7188 20.1355C21.2403 21.571 19.6454 22.3684 18.21 22.0494C15.0202 21.2519 10.714 17.1052 9.9166 13.7559C9.43813 12.3205 10.3951 10.7256 11.8305 10.2472C13.9038 9.60922 14.5418 6.89791 12.9469 5.30301L8.48119 0.837318C7.20528 -0.279106 5.29141 -0.279106 4.17499 0.837318L1.14469 3.86761C-1.8856 7.05739 1.46367 15.5103 8.95966 23.0063C16.4557 30.5023 24.9086 34.0111 28.0984 30.8213L31.1287 27.791C32.2451 26.5151 32.2451 24.6012 31.1287 23.4848Z"
                                        fill="#5B61FF" />
                            </svg>
                        </div>
                        <div class="section-contacts__item-title">
                            <p>Телефон:</p>
                        </div>
                    </div>
                    <div class="section-contacts__content-wrapper">
                        <div class="section-contacts__content">
                            <a href="tel:+78007777798" class="copyable-text">+ 7 800 777-77-98
                            </a>
                            <span class="js-copy-icon" title="Скопировать номер">
                                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/icon-copy.svg" alt="Иконка скопировать" loading="lazy" width="16" height="20">
                                </span>
                        </div>
                        <div class="section-contacts__content">
                            <a href="tel:+74951719874" class="copyable-text">+ 7 495 171-98-74
                            </a>
                            <span class="js-copy-icon" title="Скопировать номер">
                                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/icon-copy.svg" alt="Иконка скопировать" loading="lazy" width="16" height="20">
                                </span>
                        </div>
                    </div>
                </div>
                <div class="section-contacts__item">
                    <div class="section-contacts__item-title-wrapper">
                        <div class="section-contacts__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
     xmlns="http://www.w3.org/2000/svg">
  <path d="M21.5 8a2.5 2.5 0 11-4.9 0 2.5 2.5 0 014.9 0zM21.5 24a2.5 2.5 0 11-4.9 0 2.5 2.5 0 014.9 0zM11.5 16a2.5 2.5 0 11-4.9 0 2.5 2.5 0 014.9 0z"
        fill="#5B61FF"/>
  <path d="M13 15l6-5M13 17l6 5" stroke="#5B61FF" stroke-width="2" stroke-linecap="round"/>
</svg>


                        </div>
                        <div class="section-contacts__item-title">
                            <p>Мессенджеры:</p>
                        </div>
                    </div>
                    <div class="section-contacts__content">
    <!-- Блок соц. сетей -->
    <div class="section-contacts__socials">
        <a id="wa-link"
           href="https://api.whatsapp.com/send/?phone=%2B79311116071"
           target="_blank"
           rel="noopener noreferrer nofollow"
           class="section-contacts__social-item"
           aria-label="WhatsApp">
            <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
							xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_5008_11407)">
								<rect y="0.21875" width="24" height="24" fill="#18A53A" />
								<path
									d="M15.6015 13.3381C15.5723 13.3241 14.4787 12.7856 14.2844 12.7157C14.2051 12.6872 14.1201 12.6594 14.0297 12.6594C13.882 12.6594 13.758 12.7329 13.6613 12.8775C13.5521 13.0399 13.2214 13.4264 13.1192 13.5419C13.1059 13.5572 13.0876 13.5754 13.0767 13.5754C13.0669 13.5754 12.8977 13.5057 12.8465 13.4834C11.6733 12.9738 10.7829 11.7484 10.6608 11.5417C10.6434 11.512 10.6426 11.4985 10.6425 11.4985C10.6468 11.4828 10.6862 11.4433 10.7066 11.4229C10.7661 11.3639 10.8307 11.2862 10.8931 11.2111C10.9227 11.1755 10.9523 11.1398 10.9814 11.1062C11.0719 11.0008 11.1123 10.919 11.159 10.8242L11.1835 10.775C11.2977 10.5482 11.2002 10.3568 11.1687 10.295C11.1428 10.2433 10.681 9.12882 10.632 9.01174C10.5139 8.7292 10.3579 8.59766 10.1411 8.59766C10.121 8.59766 10.1411 8.59766 10.0568 8.60121C9.95405 8.60555 9.39468 8.67919 9.14737 8.83509C8.8851 9.00044 8.44141 9.52751 8.44141 10.4544C8.44141 11.2887 8.97082 12.0764 9.19812 12.3759C9.20377 12.3835 9.21414 12.3988 9.22919 12.4208C10.0997 13.6921 11.1848 14.6342 12.2849 15.0737C13.344 15.4967 13.8455 15.5456 14.1306 15.5456H14.1306C14.2504 15.5456 14.3463 15.5362 14.4309 15.5279L14.4846 15.5228C14.8505 15.4904 15.6545 15.0737 15.8374 14.5655C15.9814 14.1652 16.0194 13.7279 15.9236 13.5691C15.8579 13.4612 15.7448 13.4069 15.6015 13.3381Z"
									fill="white" />
								<path
									d="M12.1335 4.71875C8.07085 4.71875 4.76562 7.99914 4.76562 12.0313C4.76562 13.3354 5.11463 14.612 5.77578 15.7292L4.51031 19.4621C4.48674 19.5317 4.50427 19.6087 4.55575 19.6611C4.59292 19.699 4.64332 19.7195 4.6948 19.7195C4.71453 19.7195 4.7344 19.7165 4.75378 19.7103L8.64618 18.4735C9.71133 19.0426 10.9152 19.343 12.1336 19.343C16.1958 19.343 19.5007 16.063 19.5007 12.0313C19.5007 7.99914 16.1958 4.71875 12.1335 4.71875ZM12.1335 17.8198C10.9871 17.8198 9.87668 17.4887 8.92219 16.8624C8.89009 16.8413 8.85283 16.8305 8.81533 16.8305C8.79551 16.8305 8.77564 16.8335 8.7563 16.8396L6.80645 17.4594L7.4359 15.6024C7.45626 15.5423 7.44608 15.476 7.40857 15.4248C6.68172 14.4316 6.2975 13.2582 6.2975 12.0313C6.2975 8.83909 8.91552 6.24201 12.1335 6.24201C15.351 6.24201 17.9688 8.83909 17.9688 12.0313C17.9688 15.2231 15.3511 17.8198 12.1335 17.8198Z"
									fill="white" />
							</g>
							<defs>
								<clipPath id="clip0_5008_11408">
									<rect width="24" height="24" rx="5.25" transform="matrix(1 0 0 -1 0 24.2188)"
										fill="white" />
								</clipPath>
							</defs>
						</svg>
        </a>

        <a id="tg-link"
           href="https://t.me/gismining_official"
           target="_blank"
           rel="noopener noreferrer nofollow"
           class="section-contacts__social-item"
           aria-label="Telegram">
            <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
							xmlns="http://www.w3.org/2000/svg">
							<rect x="0.75" y="0.21875" width="24" height="24" rx="5.25" fill="#006FFF" />
							<path
								d="M18.2212 4.85117C18.2212 4.85117 19.6088 4.20189 19.4932 5.77873C19.4546 6.42802 19.1077 8.70052 18.8379 11.1585L17.9128 18.4398C17.9128 18.4398 17.8358 19.5065 17.1419 19.692C16.4481 19.8775 15.4074 19.0428 15.2147 18.8572C15.0605 18.7181 12.3239 16.6311 11.3602 15.6108C11.0904 15.3325 10.782 14.776 11.3988 14.1267L15.446 9.48896C15.9085 8.93242 16.371 7.63385 14.4438 9.21069L9.04754 13.6166C9.04754 13.6166 8.43082 14.0803 7.2745 13.6629L4.76908 12.7354C4.76908 12.7354 3.844 12.0397 5.42434 11.344C9.27883 9.16428 14.0198 6.93815 18.2212 4.85117Z"
								fill="white" />
						</svg>
        </a>
    </div>
</div>

                </div>
                <div class="section-contacts__item">
                    <div class="section-contacts__item-title-wrapper">
                        <div class="section-contacts__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M18.6743 19.6061C17.8782 20.1368 16.9534 20.4174 16 20.4174C15.0466 20.4174 14.1219 20.1368 13.3257 19.6061L0.213062 10.864C0.140312 10.8155 0.0694375 10.765 0 10.713V25.0378C0 26.6801 1.33281 27.9836 2.94581 27.9836H29.0541C30.6965 27.9836 31.9999 26.6508 31.9999 25.0378V10.7129C31.9304 10.765 31.8594 10.8157 31.7864 10.8643L18.6743 19.6061Z"
                                        fill="#5B61FF" />
                                <path
                                        d="M1.25312 9.3036L14.3658 18.0457C14.8622 18.3767 15.4311 18.5421 15.9999 18.5421C16.5689 18.5421 17.1378 18.3766 17.6342 18.0457L30.7469 9.3036C31.5316 8.78079 32 7.90579 32 6.96141C32 5.3376 30.6789 4.0166 29.0552 4.0166H2.94481C1.32106 4.01666 0 5.33766 0 6.96298C0 7.90579 0.4685 8.78079 1.25312 9.3036Z"
                                        fill="#5B61FF" />
                            </svg>
                        </div>
                        <div class="section-contacts__item-title">
                            <p>E-mail:</p>
                        </div>
                    </div>
                    <div class="section-contacts__content">
                        <a href="mailto:info@gis-corp.ru" class="copyable-text">info@gis-corp.ru
                        </a>
                        <span class="js-copy-icon" title="Скопировать E-mail">
                                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/icon-copy.svg" alt="Иконка скопировать" loading="lazy" width="16" height="20">
                            </span>
                    </div>
                </div>
                <!-- Уведомление "Скопировано!" (одно на все иконки) -->
                <div class="copy-feedback-popup"
                     style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: #4CAF50; color: white; padding: 15px 25px; border-radius: 5px; z-index: 1000;">
                    Скопировано!
                </div>
            </div>

            <div class="section-contacts__map-block">
                <div style="position:relative;overflow:hidden;height: 100%;">
                    <a href="https://yandex.by/maps/org/gis_mayning/105753191121/?utm_medium=mapframe&utm_source=maps"
                       style="color:#eee;font-size:12px;position:absolute;top:0px;" rel="nofollow">ГИС майнинг</a>
                    <a href="https://yandex.by/maps/213/moscow/category/it_company/184106174/?utm_medium=mapframe&utm_source=maps"
                       style="color:#eee;font-size:12px;position:absolute;top:14px;" rel="nofollow">IT-компания в Москве</a>
                    <iframe
                            src="https://yandex.by/map-widget/v1/?ll=37.625909%2C55.703992&mode=search&no-distribution=1&oid=105753191121&ol=biz&sll=52.674253%2C58.140801&source=wizbiz_new_map_single&sspn=0.123253%2C0.037083&text=117105%2C%20%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%2C%20%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5%2C%201%2C%20%D1%81%D1%82%D1%80.%201-2%20W-Plaza&utm_source=share&z=17"
                            width="560" height="400" 
                            allowfullscreen 
                            style="position:relative;"
                            loading="lazy">
                    </iframe>
                </div>
            </div>


        </div>
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
                        <source media="(min-width: 1024px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/statistics-section_image_desktop.webp" type="image/webp">
                        <source media="(min-width: 1024px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/statistics-section_image_desktop.jpg" type="image/jpeg">

                        <!-- 2. Для средних экранов (768px - 1023px): -->
                        <source media="(min-width: 768px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/statistics-section_image_desktop.webp" type="image/webp">
                        <source media="(min-width: 768px)"
                                srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/statistics-section_image_desktop.jpg" type="image/jpeg">

                        <!--3. Для мобильных экранов (< 767px): -->
                        <source srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/statistics-section_image_mobile.webp" type="image/webp">

                        <!-- Изображение по умолчанию (для мобильных в старых браузерах)
                                 и обязательный фолбэк для всех браузеров, не поддерживающих <picture>. -->
                        <img class="section-about-seo__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/about/statistics-section_image_mobile.jpg"
                             alt="Фото руководителя GIS Mining" loading="lazy" width="399" height="447">
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

    <section class="section-client-servise container section-padding">
        <h2 class="section-client-servise__title section-title">Клиентский сервис</h2>
        <h3 class="section-client-servise__subtitle">
            Персональный консультант<br>+ индивидуальный финансовый план
        </h3>
        <div class="section-client-servise__content-block feedback-block">
            <div class="feedback-block__description">
                <p>Остались вопросы по майнингу? Наша команда экспертов проведет детальную консультацию: от
                    того, стоит ли инвестировать в
                    майнинг, до юридических нюансов и подводных камней.</p>
                <p>Мы сделаем майнинг простым и понятным для вас. Забудьте о сложностях — мы обеспечим
                    безопасность и прозрачность на
                    каждом этапе.</p>
                <h3 class="feedback-block__title highlighted-color">Отдел консалтинга и продаж компании
                    GIS
                    Mining</h3>
            </div>

            <div class="feedback-block__qr-code">
                <picture>
                    <!-- Источник для больших экранов (например, десктопы) -->
                    <source media="(min-width: 1024px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/section-client-servise-qr-code_desktop.webp">

                    <!-- Изображение по умолчанию (для мобильных и браузеров без поддержки <picture>) -->
                    <img class="section-sibscribe-to-telegram__image"
                         src="<?= SITE_TEMPLATE_PATH ?>/assets/img/contacts/section-client-servise-qr-code_tablet.webp"
                         alt="QR-код для связи с отделом продаж GIS Mining" loading="lazy" width="150" height="150">
                </picture>
            </div>
        </div>
        <div class="section-client-servise__list-wrapper">
            <ol class="section-client-servise__list-items list-steps">
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Проведем консультации по вопросам инвестирования в майнинг:
                            подробно разберем все предстоящие шаги и действия</span>
                </li>
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Разработаем индивидуальную финансовую модель, в рамках которой
                            ваш доход не будет привязан к курсу криптовалют</span>
                </li>
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Поможем оценить потенциальный доход от инвестирования в майнинг
                            криптовалют и инфраструктуру</span>
                </li>
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Подберем наиболее эффективное оборудование, актуальное
                            сегодня</span>
                </li>
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Предложим лучшие условия размещения на хостинге</span>
                </li>
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Спрогнозируем оптимальное время для фиксации прибыли</span>
                </li>
                <li>
                    <span class="list-steps__title"></span>
                    <span class="list-steps__text">Организуем юридическое сопровождение деятельности</span>
                </li>
            </ol>
        </div>
    </section>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>