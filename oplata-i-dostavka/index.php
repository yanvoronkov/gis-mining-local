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

$APPLICATION->SetPageProperty("TITLE", "Оплата и доставка заказов в GIS Mining: любые удобные форматы для максимального комфорта наших клиентов");
$APPLICATION->SetTitle("Оплата и доставка");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Наличный расчет, лизинг, безнал и рассрочка - удобные способы оплаты заказа в GIS Mining");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Оплата и доставка заказов в GIS Mining: любые удобные форматы для максимального комфорта наших клиентов");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Наличный расчет, лизинг, безнал и рассрочка - удобные способы оплаты заказа в GIS Mining");
$APPLICATION->SetPageProperty("OG:TYPE", "article");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Оплата и доставка заказов в GIS Mining: любые удобные форматы для максимального комфорта наших клиентов");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Наличный расчет, лизинг, безнал и рассрочка - удобные способы оплаты заказа в GIS Mining");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-oplata-i-dostavka");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header
?>


    <section class="hero container section-padding">
        <h1 class="hero__title">
            <span class="highlighted-color">Оплата и доставка</span>
            майнинг оборудования
        </h1>

        <!-- Контактная форма 1 -->
        <form id="contactForm1" class="hero__form contact-form js-ajax-form" data-metric-goal="send-consult-lead">
            <p class="contact-form__description">
                Оставьте заявку, чтобы получить консультацию
            </p>
            <div class="input-group">
                <input type="tel" name="client_phone" class="js-phone-mask form-input" placeholder="Телефон*"
                       required="">
                <input type="hidden" name="form_name" value="Консультация">
                <!-- Универсальные UTM поля -->
                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="source_id" value="23">
                <input type="hidden" name="page_url" value="">
                <button type="submit" class="btn btn-primary contact-form-submit-btn">Оставить
                    заявку</button>
            </div>
            <!-- Сообщения об ошибке -->
            <p class="form-error-message"></p>
            <div class="form-group form-check mb-3">
              <input type="checkbox" id="privacy-policy-payment" name="privacy-policy" class="form-check-input" required>
              <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
            </div>
        </form>


        <!-- Правая часть с изображением -->
        <div class="hero__image-wrapper">
            <picture>
                <!-- 1. Для больших экранов (> 1024px): -->
                <source media="(min-width: 992px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_desktop.webp" type="image/webp">
                <source media="(min-width: 992px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_desktop.png" type="image/png">

                <!-- 2. Для средних экранов (768px - 1023px): -->
                <source media="(min-width: 768px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_tablet.webp" type="image/webp">
                <source media="(min-width: 768px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_tablet.png" type="image/png">

                <!--3. Для мобильных экранов (< 767px): -->
                <source srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_mobile.webp" type="image/webp">

                <!-- Изображение по умолчанию (для мобильных в старых браузерах)
                 и обязательный фолбэк для всех браузеров, не поддерживающих <picture>. -->
                <img class="hero__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_mobile.png"
                     alt="Инфографика" loading="lazy">
            </picture>
        </div>

    </section>

    <section class="payment-methods container section-padding">
        <div class="payment-methods__wrapper">
            <h2 class="payment-methods__title section-title">Способы оплаты</h2>
            <p class="payment-methods__info">
                Способ оплаты заказа вы выбираете самостоятельно при его оформлении. Оплата в Интернет-магазине
                временно
                не производится. После подтверждения заказа оператором Интернет-магазина способ оплаты изменить
                невозможно.
            </p>

            <div class="payment-methods__grid">

                <!-- Карточка 1: Наличный расчет -->
                <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для физических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Наличный расчет</h3>
                        <p class="payment-card__description">Вы можете оплатить заказ в центральном офисе компании
                            по
                            адресу: Москва, Варшавское шоссе, 1, стр. 1-2 W-Plaza</p>
                        <div class="payment-card__map-links links-map">
                            <a href="https://yandex.by/maps/213/moscow/house/varshavskoye_shosse_1s1_2/Z04YcAVlQEUOQFtvfXtxcnVqZg==/?ll=37.625909%2C55.703992&z=17"
                               class="payment-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="payment-card__map-icon"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo1.svg"
                                     alt="Иконка Yandex карты" loading="lazy" width=22 height=32>
                                Яндекс.Карты
                            </a>
                            <a href="https://maps.app.goo.gl/nNaHLjx3ZrHkxrh87" class="payment-card__map-link"
                               target="_blank" rel="noopener noreferrer nofollow">
                                <img class="payment-card__map-icon"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo2.svg"
                                     alt="Иконка Google карты" loading="lazy" width=22 height="32">
                                Google Карты
                            </a>
                            <a href="https://maps.apple.com/?address=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5,%20%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0,%20%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F,%20117216&ll=55.561882,37.593305&q=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5&_ext=EiYpfQh0k1jHS0AxMO0WJu3KQkA5+92Z737IS0BBCMImqvXMQkBQBA%3D%3D&t=m"
                               class="payment-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="payment-card__map-icon"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo3.svg"
                                     alt="Иконка Apple карты" loading="lazy" width="32" height="32">
                                Apple Карты
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Карточка 2: Безналичный расчет -->
                <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для физических лиц</span>
                        <span class="payment-card__tag">для юридических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Безналичный расчет</h3>
                        <div class="payment-card__content-group">
                            <h4 class="payment-card__subtitle">Кредитная или дебетовая карта</h4>
                            <p class="payment-card__description">Картами Visa, MasterCard, Мир. Без комиссии</p>
                            <p class="payment-card__description">Мы подготавливаем договор и согласовываем основные
                                условия</p>
                        </div>
                        <div class="payment-card__content-group">
                            <h4 class="payment-card__subtitle">Банковский перевод</h4>
                            <p class="payment-card__description">Вы осуществляете перевод денежных средств на
                                расчетный
                                счет нашей организации</p>
                        </div>
                    </div>
                </article>

                <!-- Карточка 3: Лизинг -->
                <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для ИП</span>
                        <span class="payment-card__tag">для юридических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Лизинг</h3>
                        <p class="payment-card__description">Лизинг позволяет получить современное оборудование уже сегодня, оплачивая его стоимость постепенно, удобными ежемесячными платежами.
                        <ul class="payment-card__features-list">
                            <!-- <li class="payment-card__feature-item"><b>20%</b> возмещение НДС</li>
                            <li class="payment-card__feature-item"><b>20%</b> оптимизация расходов на размещение и
                                электроэнергию</li>
                            <li class="payment-card__feature-item"><b>20%</b> экономия по налогу на прибыль</li> -->
                        </ul>
                    </div>
                    <!-- <footer class="payment-card__footer">
                        <a href="#" class="payment-card__more-link">
                            Подробнее
                            <svg class="cta-banner-section__icon" width="36" height="36" viewBox="0 0 36 36"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_7003_2908)">
                                    <circle cx="18" cy="18" r="17.625" stroke="#131315" stroke-width="0.75">
                                    </circle>
                                    <path
                                        d="M18.7496 13.875H22.125M22.125 13.875L13.875 22.1235M22.125 13.875L22.1246 17.25"
                                        stroke="#131315" stroke-width="0.75" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_7003_2908">
                                        <rect width="36" height="36" fill="white"></rect>
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    </footer> -->
                </article>

                <!-- Карточка 4: Рассрочка -->
                <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для физических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Рассрочка</h3>
                        <p class="payment-card__description">Оплата заказа частями</p>
                    </div>
                    <!-- <footer class="payment-card__footer">
                        <a href="#" class="payment-card__more-link">
                            Подробнее
                            <svg class="cta-banner-section__icon" width="36" height="36" viewBox="0 0 36 36"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_7003_2908)">
                                    <circle cx="18" cy="18" r="17.625" stroke="#131315" stroke-width="0.75">
                                    </circle>
                                    <path
                                        d="M18.7496 13.875H22.125M22.125 13.875L13.875 22.1235M22.125 13.875L22.1246 17.25"
                                        stroke="#131315" stroke-width="0.75" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_7003_2908">
                                        <rect width="36" height="36" fill="white"></rect>
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    </footer> -->
                </article>

            </div>
        </div>
    </section>

    <section class="delivery-methods container section-padding">
        <div class="delivery-methods__wrapper">
            <h2 class="delivery-methods__title section-title">Способы доставки</h2>
            <p class="delivery-methods__info">
                С 2020 года компания GIS Mining осуществила более 50 000 отгрузок самыми различными способами.
            </p>

            <div class="delivery-methods__grid">

                <!-- Карточка 1: Самовывоз -->
                <article class="delivery-methods__card delivery-card">

                    <div class="delivery-card__body">
                        <h3 class="delivery-card__title">Самовывоз</h3>
                        <p class="delivery-card__description">Чтобы получить заказ от GIS Mining самостоятельно, 
вы можете выбрать вариант самовывоза. 
После того как оборудование поступит на наш логистический пункт и будет готово к передаче, менеджер свяжется с вами для согласования даты и времени визита.</p>
                        <p class="delivery-card__description">Центральный офис компании GIS Mining находится в
                            Москве, по адресу: Варшавское шоссе, 1, стр. 1-2 W-Plaza</p>
                        <div class="delivery-card__map-links links-map">
                            <a href="https://yandex.by/maps/213/moscow/house/varshavskoye_shosse_1s1_2/Z04YcAVlQEUOQFtvfXtxcnVqZg==/?ll=37.625909%2C55.703992&z=17"
                               class="delivery-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="delivery-card__map-icon"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo1.svg"
                                     alt="Иконка Yandex карты" loading="lazy" width="22" height="32">
                                Яндекс.Карты
                            </a>
                            <a href="https://maps.app.goo.gl/nNaHLjx3ZrHkxrh87" class="delivery-card__map-link"
                               target="_blank" rel="noopener noreferrer nofollow">
                                <img class="delivery-card__map-icon"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo2.svg"
                                     alt="Иконка Google карты" loading="lazy" width="22" height="32">
                                Google Карты
                            </a>
                            <a href="https://maps.apple.com/?address=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5,%20%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0,%20%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F,%20117216&ll=55.561882,37.593305&q=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5&_ext=EiYpfQh0k1jHS0AxMO0WJu3KQkA5+92Z737IS0BBCMImqvXMQkBQBA%3D%3D&t=m"
                               class="delivery-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="delivery-card__map-icon"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo3.svg"
                                     alt="Иконка Apple карты" loading="lazy" width="32" height="32">
                                Apple Карты
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Карточка 2: Транспортные компании -->
                <article class="delivery-methods__card delivery-card">
                    <div class="delivery-card__body">
                        <h3 class="delivery-card__title">Транспортные компании</h3>
                        <p class="delivery-card__description">Организуем доставку в любой регион РФ через транспортные компании (СДЭК, Деловые линии, ПЭК и др.).</p>
                        <div class="delivery-card__carriers">
                            <div class="delivery-card__carrier-logo-wrap">
                                <img class="delivery-card__carrier-logo"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_delivery-method_logo1.webp"
                                     alt="Логотип CDEK" loading="lazy" width="142" height="32">
                            </div>
                            <div class="delivery-card__carrier-logo-wrap">
                                <img class="delivery-card__carrier-logo"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_delivery-method_logo2.webp"
                                     alt="Логотип DHL" loading="lazy" width="55" height="32">
                            </div>
                            <div class="delivery-card__carrier-logo-wrap">
                                <img class="delivery-card__carrier-logo"
                                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_delivery-method_logo3.webp"
                                     alt="Логотип Деловые линии" loading="lazy" width="210" height="32">
                            </div>

                        </div>
                        <div class="delivery-card__content-group">
                            <h4 class="delivery-card__subtitle">Сроки доставки</h4>
                            <p class="delivery-card__description">Стандартная доставка от заводов-производителей в
                                РФ занимает до 21 дня (точный срок зависит от объема партии). Далее мы
                                доставляем оборудование до заказчика или отправляем асики на наш склад, откуда вы
                                можете забрать аппаратуру
                                самостоятельно.</p>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </section>

    <section class="guarantee container section-padding">
        <div class="guarantee__wrapper">
            <h2 class="guarantee__title section-title">
                Гарантия <br>на купленное оборудование
            </h2>

            <div class="guarantee__description">
                <p class="guarantee__description-item">Гарантийный срок составляет от 6 до 12 месяцев, начиная с даты
                    доставки.</p>
                <p class="guarantee__description-item">GIS Mining компенсирует транспортные расходы при доставке
                    нового
                    устройства в течение гарантийного срока.</p>
            </div>


            <!-- Правая часть с изображением -->
            <div class="guarantee__image-wrapper">
                <picture>
                    <!-- 1. Для больших экранов (> 1024px): -->
                    <source media="(min-width: 992px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_guarantee_desktop.webp"
                            type="image/webp">
                    <source media="(min-width: 992px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_guarantee_desktop.png" type="image/png">

                    <!-- 2. Для средних экранов (768px - 1023px): -->
                    <source media="(min-width: 768px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_guarantee_tablet.webp" type="image/webp">
                    <source media="(min-width: 768px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_guarantee_tablet.png" type="image/png">

                    <!--3. Для мобильных экранов (< 767px): -->
                    <source srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_guarantee_mobile.webp"
                            type="image/webp">

                    <!-- Изображение по умолчанию (для мобильных в старых браузерах)
                         и обязательный фолбэк для всех браузеров, не поддерживающих <picture>. -->
                    <img class="guarantee__image" src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_hero_mobile.png"
                         alt="Инфографика" loading="lazy" width="345" height="271">
                </picture>
            </div>

            <a href="https://gis-mining.ru/catalog/asics/" class="guarantee__more-link">
                В каталог
                <svg class="cta-banner-section__icon" width="36" height="36" viewBox="0 0 36 36" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_7003_2908)">
                        <circle cx="18" cy="18" r="17.625" stroke="#131315" stroke-width="0.75">
                        </circle>
                        <path d="M18.7496 13.875H22.125M22.125 13.875L13.875 22.1235M22.125 13.875L22.1246 17.25"
                              stroke="#131315" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round">
                        </path>
                    </g>
                    <defs>
                        <clipPath id="clip0_7003_2908">
                            <rect width="36" height="36" fill="white"></rect>
                        </clipPath>
                    </defs>
                </svg>
            </a>
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

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>