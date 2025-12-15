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

$APPLICATION->SetPageProperty("TITLE", "Квиз GIS-Mining");
$APPLICATION->SetTitle("Квиз");
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

<!-- Quiznaya script start -->
<script>
(function(w, d, s, o){
var j = d.createElement(s); j.async = true; j.src = '//s.quiznaya.ru/v1.js';j.onload = function() {
   if (document.readyState !== 'loading') Quiznaya.init(o);
   else document.addEventListener("DOMContentLoaded", function() {
      Quiznaya.init(o);
   });
};
d.head.insertBefore(j, d.head.firstElementChild);
})(window, document, 'script', {
   id: 'a2bc39c0-055a-4ab6-8ee9-7255a701e82e',
   autoOpen: false,
   autoOpenFrequency: 'once',
   openBeforeLeave: false,
   autoOpenMobile: false
}
);
</script>
<!-- Quiznaya script end -->


    <section class="offer-section container">
 <div data-qya-id="a2bc39c0-055a-4ab6-8ee9-7255a701e82e"></div>
    </section>

   

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
                <label for="popup_client_email">Email:</label>
                <input type="email" name="client_email" id="popup_client_email"
                       placeholder="your@email.com (необязательно)" class="form-popup__input form-input"
                       aria-label="Электронная почта">


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
              <label for="privacy-policy-construction" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a></label>
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