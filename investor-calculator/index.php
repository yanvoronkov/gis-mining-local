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

$APPLICATION->SetPageProperty("TITLE", "Калькулятор инвестора");
$APPLICATION->SetTitle("О компании");
$APPLICATION->SetPageProperty("description", "Калькулятор инвестора");
$APPLICATION->SetPageProperty("keywords", "калькулятор, инвестора");
$APPLICATION->SetPageProperty("robots", "noindex, nofollow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Калькулятор инвестора");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Калькулятор инвестора");
$APPLICATION->SetPageProperty("OG:TYPE", "website"); // Для внутренних страниц лучше использовать "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Калькулятор инвестора");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Калькулятор инвестора");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-about");
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("main_class", "page-home");

// ----- ВЫВОД ХЛЕБНЫХ КРОШЕК СО СТАНДАРТНЫМ ШАБЛОНОМ -----
// Хлебные крошки теперь формируются автоматически в header
?>

<link rel="stylesheet" href="style.css">

<h1 class="section-about-seo__main-title section-title highlighted-color visually-hidden">Калькулятор инвестора</h1>

<div class="container section-padding">
 <!-- СТИЛИ СТРАНИЦЫ -->
<link rel="stylesheet" href="style.css">

<div class="gis-invest-calc">
  <div class="gis-invest-calc__inner">

    <!-- ЛЕВАЯ КОЛОНКА — ВВОД ДАННЫХ -->
    <section class="gis-invest-calc__left">

      <h1 class="gis-invest-calc__title">Инвестиционный калькулятор майнинга</h1>
      <p class="gis-invest-calc__subtitle">
        Подбор оптимального набора ASIC-оборудования под ваш бюджет и тариф на электроэнергию.
      </p>

      <!-- ФИЛЬТРЫ УРОВНЯ -->
<div id="gic-investor-levels" class="gic-tabs">
    <button class="gic-level-btn" data-level="novice">Новичок</button>
    <button class="gic-level-btn" data-level="advanced">Продвинутый</button>
    <button class="gic-level-btn" data-level="pro">Профессионал</button>
</div>




      <!-- СУММА ИНВЕСТИЦИЙ -->
      <div class="gic-card">
        <div class="gic-card__label-row">
          <span class="gic-card__label">Сумма инвестиций</span>
          <span class="gic-card__hint">Минимально разумный бюджет &mdash; от 200&nbsp;000 ₽</span>
        </div>
        <div class="gic-input-with-prefix">
          <span class="gic-input-with-prefix__prefix">₽</span>
          <input
            id="gic-invest-amount"
            type="text"
            inputmode="decimal"
            placeholder="Например, 1 500 000"
            class="gic-input-with-prefix__input"
          />
        </div>
      </div>

      <!-- ТАРИФ / ХОСТИНГ -->
      <div class="gic-card">
        <div class="gic-card__label-row">
          <span class="gic-card__label">Цена электроэнергии</span>
        </div>

        <div class="gic-radio-row">
          <label class="gic-radio">
            <label class="gic-radio">
            <input type="radio" name="gic-tariff-mode" value="hosting" checked />
            <span>Хостинг GIS Mining</span>
          </label>

            <input type="radio" name="gic-tariff-mode" value="custom"/>
            <span>Свой тариф</span>
          </label>
        </div>

        <div class="gic-input-with-suffix" id="gic-tariff-wrapper">
          <input
            id="gic-tariff-input"
            type="number"
            min="0"
            step="0.1"
            value="5.3"
            class="gic-input-with-suffix__input"
          />
          <span class="gic-input-with-suffix__suffix">₽/кВт⋅ч</span>
        </div>

        <div class="gic-note" id="gic-tariff-note-hosting" style="display:none;">
          Для хостинга GIS Mining используется фиксированный тариф 5,3 ₽/кВт⋅ч.
        </div>
      </div>

    

      <!-- КНОПКИ УПРАВЛЕНИЯ -->
      <div class="gic-actions">
        <button id="gic-calc-btn" class="gic-btn gic-btn--primary">
          Рассчитать портфель
        </button>
        <button id="gic-reset-btn" class="gic-btn gic-btn--ghost">
          Сбросить данные
        </button>
      </div>

      <div class="gic-small-note">
        Расчёт выполняется по текущим значениям курса и сложности сети и носит предварительный характер.
      </div>
    </section>

    <!-- ПРАВАЯ КОЛОНКА — РЕЗУЛЬТАТ -->
    <section class="gis-invest-calc__right">
      <div class="gic-result-header">
        <div>
          <h2 class="gic-result-title">Результат расчёта</h2>
          <p class="gic-result-subtitle" id="gic-result-subtitle">
            Укажите сумму инвестиций и тариф, затем нажмите «Рассчитать портфель».
          </p>
        </div>
        <div class="gic-result-meta" id="gic-market-meta"></div>
      </div>

      <!-- ТАБЫ ПОРТФЕЛЕЙ -->
      <div class="gic-tabs" id="gic-portfolio-tabs">
        <button class="gic-tab-btn gic-tab-btn--active" data-segment="sha">
          BTC (SHA-256)
        </button>
        <button class="gic-tab-btn" data-segment="scrypt">
          LTC+DOGE (Scrypt)
        </button>
        <button class="gic-tab-btn" data-segment="mixed">
          Смешанный портфель
        </button>
      </div>

      <!-- ОСНОВНАЯ КАРТОЧКА ПОРТФЕЛЯ -->
      <div class="gic-portfolio gic-portfolio--active" id="gic-main-portfolio">
        <div class="gic-portfolio-header">
          <div>
            <div class="gic-portfolio-title" id="gic-main-portfolio-label">Портфель BTC (SHA-256)</div>
            <div class="gic-portfolio-subtitle" id="gic-main-portfolio-tagline">
              Укажите параметры и нажмите «Рассчитать портфель».
            </div>
          </div>
          <div class="gic-portfolio-badge" id="gic-main-portfolio-roi-badge">– % годовых</div>
        </div>

        <div class="gic-portfolio-body" id="gic-main-portfolio-body">
          <div class="gic-muted">Портфель ещё не рассчитан.</div>
        </div>

        <div class="gic-portfolio-footer">
          <button
    class="gic-btn gic-btn--primary gic-btn--full js-open-popup-form"
    data-form-type="main"
    data-metric-goal="invest-kp"
    id="gic-main-kp-btn"
>
    Получить КП по этому набору
</button>

        </div>
      </div>
    </section>
  </div>
</div>

<!-- ПОДКЛЮЧЕНИЕ JS -->
<script src="inv.js"></script>




</div>



<!-- POPUP: Коммерческое предложение -->
<div class="form-popup popup-form-wrapper" id="mainPopupFormWrapper" style="display: none;">

    <div class="form-popup__items">
        <button type="button" class="form-popup__close-btn popup-form__close-btn menu-close"
                id="closeMainPopupFormBtn" aria-label="Закрыть">
            <svg width="33" height="32" viewBox="0 0 33 32" fill="none">
                <path d="M22.9844 10L10.9844 22" stroke="#6F7682" stroke-linecap="round" />
                <path d="M10.9844 10L22.9844 22" stroke="#6F7682" stroke-linecap="round" />
            </svg>
        </button>

        <div class="form-popup__title-img-wrapper">
            <h2 class="form-popup__title">Получить КП</h2>
            <div class="form-popup__img-wrapper">
                <img src="/local/templates/gis/assets/img/components/popup_form_image.png"
                     alt="Контейнер для майнинг фермы"
                     class="form-popup__img"
                     loading="lazy" width="300" height="200">
            </div>
        </div>

        <form class="form-popup__popup-form js-ajax-form"
              id="contactFormPopup"
              data-metric-goal="send-consult-lead">

            <p class="form-popup__cta">
                Заполните форму, чтобы оставить заявку на консультацию. Мы перезвоним вам.
            </p>

            <label for="popup_client_name">Имя:</label>
            <input type="text" name="client_name" id="popup_client_name"
                   placeholder="Имя"
                   class="form-popup__input form-input">

            <label for="popup_client_phone">Телефон*:</label>
            <input type="tel" name="client_phone" id="popup_client_phone"
                   placeholder="Телефон*"
                   class="form-popup__input form-input js-phone-mask" required>


            <!-- ВАЖНО: Добавляю скрытое поле, чтобы передать тип портфеля -->
            <input type="hidden" name="portfolio_type" id="popup_portfolio_type">

            <input type="hidden" name="source_id" value="23">
            <input type="hidden" name="utm_source">
            <input type="hidden" name="utm_medium">
            <input type="hidden" name="utm_campaign">
            <input type="hidden" name="utm_content">
            <input type="hidden" name="utm_term">
            <input type="hidden" name="form_name" value="Коммерческое предложение">
            <input type="hidden" name="page_url" value="">

            <div class="form-group form-check mb-3">
                <input type="checkbox" id="privacy-policy-popup" name="privacy-policy"
                       class="form-check-input" required>
                <label for="privacy-policy-popup" class="form-check-label">
                    Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с
                    <a href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a>
                </label>
            </div>

            <button type="submit"
                    class="form-popup__submit-btn btn btn-primary"
                    id="submitContactBtnPopup">
                Оставить заявку
            </button>

            <p class="form-popup__error-message form-error-message"
               style="color: red; display: none;"></p>
        </form>

    </div>
</div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
