<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetAdditionalCSS("/top-asics/style.css");

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

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Искали самый доходный ASIC для майнинга?");
$APPLICATION->SetTitle("Cамые доходные ASIC'и для майнинга");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "3 самые быстроокупаемые модели асиковв 2025 по нашим исследованиям");
$APPLICATION->SetPageProperty("keywords", "");
$APPLICATION->SetPageProperty("robots", "index, follow");
/*
// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Размещение оборудования в дата-центре компании GIS Mining: одна из лучших площадок для майнинга");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Ищете лучший майнинг-отель? Дата-центр компании GIS Mining предлагает оптимальные условия среди всех хостингов");
$APPLICATION->SetPageProperty("OG:TYPE", "article"); // Рекомендуемый тип для внутренних страниц
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);
$APPLICATION->SetPageProperty("OG:IMAGE:WIDTH", "1200");
$APPLICATION->SetPageProperty("OG:IMAGE:HEIGHT", "630");

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Размещение оборудования в дата-центре компании GIS Mining: одна из лучших площадок для майнинга");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Ищете лучший майнинг-отель? Дата-центр компании GIS Mining предлагает оптимальные условия среди всех хостингов");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);
*/
// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-razmeschenie");
$APPLICATION->SetPageProperty("header_right_class", "color-block");


// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <section class="hero-section container">
  <div class="hero-section__grid">

    <!-- Левая колонка -->
    <div class="hero-section__left">
      <h1 class="hero-section__title section-title">
        ИСКАЛИ САМЫЙ ДОХОДНЫЙ <span class="highlighted-color">ASIC</span> ДЛЯ МАЙНИНГА?
      </h1>

      <!-- 📱 Мобильное фото -->
      <div class="hero-section__image hero-section__image--mobile">
        <img src="/upload/as3.jpg" alt="ASIC">
      </div>

      <p class="hero-section__rate">
        Оставьте ваш <strong>WhatsApp</strong>, и мы пришлём вам в личку 3 самые быстроокупаемые модели асиков 2025 по нашим исследованиям
      </p>

      <p class="hero-section__note">
        <strong>Расчет производился по параметрам:</strong><br>
        Заявленная производителем доходность устройства, прогнозный и текущий курсы Bitcoin, курс доллара к рублю на конец квартала, прогнозный курс доллара к рублю на конец года. Минус налоги.
      </p>

      <form id="contactForm2" class="contact-form-retarg js-ajax-form" data-metric-goal="send-consult-lead">
        <input class="ret" type="text" name="client_name" id="popup_client_name" placeholder="Имя"
                   class="form-popup__input form-input" aria-label="Имя">
        <input class="ret" type="tel" name="client_phone" class="js-phone-mask form-input" placeholder="Телефон*" required>
        <input type="hidden" name="form_name" value="Ретаргет">
        <button type="submit" class="btn btn-primary contact-form-submit-btn">Получить подборку</button>
        <p class="form-error-message" style="color: red; display: none;"></p>
      </form>

      <div class="form-group form-check mb-3" style="margin-top: 10px;">
        <input type="checkbox" id="privacy-policy-asics" name="privacy-policy" class="form-check-input" required>
        <label for="privacy-policy-asics" class="form-check-label">
          Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a>
        </label>
      </div>
    </div>

    <!-- Правая колонка -->
    <div class="hero-section__right">

      <!-- 💻 Фото для ПК -->
      <div class="hero-section__image hero-section__image--desktop">
        <img src="/upload/as3.jpg" alt="ASIC">
      </div>

      <section class="contact-messengers">
        <h2 class="contact-messengers__title">
          НАПИШИТЕ НАМ В УДОБНОМ МЕССЕНДЖЕРЕ, ОТВЕТИМ СРАЗУ
        </h2>
        <div class="contact-messengers__buttons">
          <a id="wa-link" href="https://api.whatsapp.com/send/?phone=%2B79311116071" target="_blank" rel="nofollow" class="contact-messengers__btn contact-messengers__btn--whatsapp">
            <i class="fa-brands fa-whatsapp"></i> Написать в WhatsApp
          </a>
          <a id="tg-link" href="https://t.me/gismining_official" target="_blank" rel="nofollow" class="contact-messengers__btn contact-messengers__btn--telegram">
            <i class="fa-brands fa-telegram"></i> Написать в Telegram
          </a>
        </div>
        <p class="contact-messengers__phone-text">Или позвоните по номеру:</p>
        <p class="contact-messengers__phone">
          <a href="tel:+78007777798">+7 (800) 777-77-98</a>
        </p>
      </section>
    </div>

  </div>
</section>




    


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>