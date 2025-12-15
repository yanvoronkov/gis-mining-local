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

$APPLICATION->SetPageProperty("TITLE", "Оставьте ваш отзыв");
$APPLICATION->SetTitle("Контакты");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("keywords", "размещение майнинг оборудования в дата центре, цод для майнинга, дата центр для майнинга, цод майнинг");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Оставьте ваш отзыв");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("OG:TYPE", "profile"); // Для контактов хорошо подходит тип "profile" или "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Оставьте ваш отзыв");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "GIS Mining - лучший клиентский сервис среди майнинговых компаний России");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-contacts");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header
?>

<section class="section-contacts container">
  <h1 class="section-contacts__title section-title highlighted-color">Оставьте отзыв о GIS-MINING</h1>

  <!-- === ВИДЖЕТ ОТЗЫВА === -->
  <div class="feedback-widget" id="feedbackWidget">
    <!-- ШАГ 1: Рейтинг -->
    <div class="feedback-step feedback-step--active" data-step="rate">
      <p class="feedback-title">Оцените опыт работы с нашей компанией</p>
      <div class="feedback-stars">
        <button class="feedback-star" data-value="1" aria-label="1 звезда">★</button>
        <button class="feedback-star" data-value="2" aria-label="2 звезды">★</button>
        <button class="feedback-star" data-value="3" aria-label="3 звезды">★</button>
        <button class="feedback-star" data-value="4" aria-label="4 звезды">★</button>
        <button class="feedback-star" data-value="5" aria-label="5 звёзд">★</button>
      </div>
      <p class="feedback-hint">Нажмите на звезду — дальше всё произойдёт автоматически</p>
    </div>

    <!-- ШАГ 2: Площадки -->
    <div class="feedback-step" data-step="platforms">
      <p class="feedback-title">Спасибо! Выберите площадку, где удобнее оставить отзыв:</p>
      <div class="feedback-links">
        <a href="https://yandex.ru/profile/105753191121?intent=reviews" target="_blank" class="feedback-link">Яндекс Карты</a>
        <a href="https://search.google.com/local/writereview?placeid=ChIJn2gawMJLtUYRT-uSVw9r53E" target="_blank" class="feedback-link">Google Карты</a>
        <a href="https://zoon.ru/msk/internet/kompaniya_gis_mining/#reviews" target="_blank" class="feedback-link">Zoon</a>
        <a href="https://2gis.ru/reviews/70000001058749813/addReview" target="_blank" class="feedback-link">2ГИС</a>
      </div>
      <button type="button" class="btn btn-outline back-btn" data-action="back">Изменить оценку</button>
    </div>

    <!-- ШАГ 3: Форма -->
    <div class="feedback-step" data-step="form">
      <p class="feedback-title">Нам очень жаль, что опыт оказался неидеальным. Помогите стать лучше:</p>

      <div class="feedback-tags">
        <button type="button" class="feedback-tag" data-tag="Долгий ответ ТП">Долгий ответ ТП</button>
        <button type="button" class="feedback-tag" data-tag="Дорогой тариф">Дорогой тариф</button>
        <button type="button" class="feedback-tag" data-tag="Долгая доставка">Долгая доставка</button>
        <button type="button" class="feedback-tag" data-tag="Качество оборудования">Качество оборудования</button>
        <button type="button" class="feedback-tag" data-tag="Ошибка в счёте">Ошибка в счёте</button>
        <button type="button" class="feedback-tag" data-tag="Работа дата-центра">Работа дата-центра</button>
        <button type="button" class="feedback-tag" data-tag="Другое">Другое</button>
      </div>

      <form class="form-popup__popup-form js-ajax-form" id="feedbackForm" data-metric-goal="send-feedback-lead">
        <label for="fb_name">Имя:</label>
        <input type="text" name="client_name" id="fb_name" class="form-popup__input form-input" placeholder="Имя">

        <label for="fb_phone">Телефон*:</label>
        <input type="tel" name="client_phone" id="fb_phone" class="form-popup__input form-input js-phone-mask" required placeholder="Телефон*">

        <label for="fb_comment">Комментарий:</label>
        <textarea name="client_comment" id="fb_comment" class="form-popup__input form-input" rows="4" placeholder="Что именно пошло не так?"></textarea>

        <input type="hidden" name="form_name" value="Отзывы — негатив">
        <input type="hidden" name="page_url" value="<?=htmlspecialchars($APPLICATION->GetCurPage(false))?>">
        <input type="hidden" name="tags" id="fb_tags">
        <input type="hidden" name="rating" id="fb_rating">

        <div class="form-group form-check mb-3">
          <input type="checkbox" id="privacy-policy-feedback" name="privacy-policy" class="form-check-input" required>
          <label for="privacy-policy-feedback" class="form-check-label">
            Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a>
          </label>
        </div>

        <button type="submit" class="btn btn-primary">Отправить</button>
        <button type="button" class="btn btn-outline back-btn" data-action="back">Изменить оценку</button>

        <p class="feedback-message"></p>
      </form>
    </div>
  </div>
  <!-- === /ВИДЖЕТ ОТЗЫВА === -->

  <link rel="stylesheet" href="./style.css">
  <script src="./js.js"></script>
</section>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>