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

$APPLICATION->SetPageProperty("TITLE", "Купить ASIC-майнеры для майнинга в России — Прайс-лист и актуальные цены | Gis Mining");
$APPLICATION->SetTitle("Купить ASIC-майнеры для майнинга в России — Прайс-лист и актуальные цены | Gis Mining");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Прайс-лист на оборудование для майнинга от компании Gis Mining. Большой выбор ASIC-майнеров с ГТД РФ, официальной гарантией и доставкой по России. Актуальные цены, хостинг от 5,3 ₽/кВт·ч, помощь в подборе и запуске оборудования.");
$APPLICATION->SetPageProperty("keywords", "");
$APPLICATION->SetPageProperty("robots", "noindex, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Купить ASIC-майнеры для майнинга в России — Прайс-лист и актуальные цены | Gis Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Прайс-лист на оборудование для майнинга от компании Gis Mining. Большой выбор ASIC-майнеров с ГТД РФ, официальной гарантией и доставкой по России. Актуальные цены, хостинг от 5,3 ₽/кВт·ч, помощь в подборе и запуске оборудования.");
$APPLICATION->SetPageProperty("OG:TYPE", "profile"); // Для контактов хорошо подходит тип "profile" или "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Купить ASIC-майнеры для майнинга в России — Прайс-лист и актуальные цены | Gis Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Прайс-лист на оборудование для майнинга от компании Gis Mining. Большой выбор ASIC-майнеров с ГТД РФ, официальной гарантией и доставкой по России. Актуальные цены, хостинг от 5,3 ₽/кВт·ч, помощь в подборе и запуске оборудования.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-contacts");
$APPLICATION->SetPageProperty("main_class", "page-home");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header

// --- Подключаем модули ---
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\HttpClient;

Loader::includeModule('iblock');
Loader::includeModule('catalog');

// === Курсы валют ===
function getCurrencyRates(): array {
  $cache = Cache::createInstance();
  $cacheId = 'daily_currency_rates_v3';
  $cacheDir = '/price_rates/';

  date_default_timezone_set('Europe/Moscow');
  $now = time();
  $todayNoon = strtotime(date('Y-m-d 12:00:00'));
  $nextNoon = ($now < $todayNoon) ? $todayNoon : strtotime('+1 day 12:00:00');
  $ttl = max(60, $nextNoon - $now);

  if ($cache->initCache($ttl, $cacheId, $cacheDir))
    return $cache->getVars();

  elseif ($cache->startDataCache()) {
    $usd = 'N/A';
    $btc = 'N/A';

    // --- USD с ЦБ РФ ---
    try {
      $http = new HttpClient(['timeout' => 10, 'disableSslVerification' => true]);
      $resp = $http->get('https://www.cbr.ru/scripts/XML_daily.asp');
      if ($resp) {
        $xml = @simplexml_load_string($resp);
        foreach ($xml->Valute as $v) {
          if ((string)$v->CharCode === 'USD') {
            $val = floatval(str_replace(',', '.', (string)$v->Value));
            $usd = number_format($val, 2, '.', '');
            break;
          }
        }
      }
    } catch (\Throwable $e) {}

    // --- BTC/USD с Coinbase ---
    try {
      $http = new HttpClient(['timeout' => 10, 'disableSslVerification' => true]);
      $http->setHeader('User-Agent', 'Mozilla/5.0 (compatible; GISMiningBot/1.0)');
      $resp = $http->get('https://api.coinbase.com/v2/prices/BTC-USD/spot');
      if ($http->getStatus() === 200 && $resp) {
        $data = json_decode($resp, true);
        if (isset($data['data']['amount'])) {
          $price = round(floatval($data['data']['amount']));
          $btc = number_format($price, 0, ',', ' ');
        }
      }
    } catch (\Throwable $e) {}

    $result = [
      'USD' => $usd,
      'BTC' => $btc,
      'UPDATED' => date('d.m.Y'),
    ];
    $cache->endDataCache($result);
    return $result;
  }

  return ['USD' => 'N/A', 'BTC' => 'N/A', 'UPDATED' => '—'];
}

$rates = getCurrencyRates();

// === Товары ===
$products = [];
$arSelect = [
  'ID','NAME','DETAIL_PAGE_URL','DETAIL_PICTURE',
  'PROPERTY_POWER',
  'PROPERTY_HASHRATE_TH','PROPERTY_HASHRATE_MH','PROPERTY_HASHRATE_KSOL'
];
$arFilter = ['IBLOCK_ID'=>1,'ACTIVE'=>'Y'];
$res = CIBlockElement::GetList(['NAME'=>'ASC'],$arFilter,false,false,$arSelect);

while($item = $res->GetNext()) {
  $priceData = \CCatalogProduct::GetOptimalPrice($item['ID']);
  if (!$priceData || $priceData['RESULT_PRICE']['DISCOUNT_PRICE'] <= 0) continue;

  $price = number_format($priceData['RESULT_PRICE']['DISCOUNT_PRICE'], 0, ',', ' ');
  $hashrate = '';
  if ($item['PROPERTY_HASHRATE_TH_VALUE'])
    $hashrate = $item['PROPERTY_HASHRATE_TH_VALUE'].' TH/s';
  elseif ($item['PROPERTY_HASHRATE_MH_VALUE'])
    $hashrate = $item['PROPERTY_HASHRATE_MH_VALUE'].' MH/s';
  elseif ($item['PROPERTY_HASHRATE_KSOL_VALUE'])
    $hashrate = $item['PROPERTY_HASHRATE_KSOL_VALUE'].' KSol/s';

  $power = $item['PROPERTY_POWER_VALUE'] ? $item['PROPERTY_POWER_VALUE'].' Вт' : '';

  $imageSrc = '';
if (!empty($item['DETAIL_PICTURE'])) {
  $imageSrc = CFile::GetPath($item['DETAIL_PICTURE']);
} else {
  $imageSrc = SITE_TEMPLATE_PATH . '/assets/img/components/popup_form_image.png'; // запасная
}


  $products[] = [
  'NAME' => $item['NAME'],
  'URL' => $item['DETAIL_PAGE_URL'],
  'PRICE' => (float)$priceData['RESULT_PRICE']['DISCOUNT_PRICE'],
  'PRICE_FORMATTED' => "{$price} ₽ с НДС",
  'POWER' => $power,
  'HASHRATE' => $hashrate,
  'IMG' => $imageSrc, // 👈 добавляем
];

}
?>

<section class="section-contacts container">
  <h1 class="section-contacts__title section-title highlighted-color">Прайс-лист на ASIC-майнеры и оборудование для майнинга</h1>

  <!-- ======= Градиентная шапка прайса ======= -->
<div class="price-ribbon">
  <div class="ribbon-title">ПРАЙС-ЛИСТ</div>

  <div class="ribbon-right">
    <div class="ribbon-label">Используемые курсы:</div>

    <!-- BTC -->
    <div class="ribbon-rate">
      <img class="rate-icon" src="./bitcoin.svg" alt="BTC" width="28" height="28" loading="lazy">
      <div class="rate-text">
        <div class="rate-value"><?= htmlspecialcharsbx($rates['BTC']) ?> USD</div>
        <div class="rate-source">www.binance.com</div>
      </div>
    </div>

    <!-- USD -->
    <div class="ribbon-rate">
      <img class="rate-icon" src="./dollar.svg" alt="USD" width="28" height="28" loading="lazy">
      <div class="rate-text">
        <div class="rate-value"><?= htmlspecialcharsbx($rates['USD']) ?> ₽</div>
        <div class="rate-source">www.cbr.ru</div>
      </div>
    </div>
  </div>
</div>
<!-- ======= /Градиентная шапка прайса ======= -->


<div class="hosting-section">
  <div class="hosting-block">
    <img src="./star.svg" alt="Размещение" class="hosting-icon">
    <div class="hosting-text">
      <div class="hosting-text-inner">
        <span>
          Доступно размещение на хостинге в дата-центре на Калининской АЭС
          по тарифу <span class="highlight">5.3 ₽/кВт</span>
        </span>
        <a href="https://gis-mining.ru/razmeschenie/" target="_blank" class="hosting-btn">Подробнее</a>
      </div>
    </div>
  </div>
</div>




<!-- ======= Блок пояснений под тарифом ======= -->
<div class="price-info">
  <div class="info-left">
    <b>В стоимость оборудования входит:</b> Авиа-доставка из Китая, таможенное оформление (ГТД РФ), полный пакет документов для бухгалтерии, доставка,
    установка и настройка оборудования на хостинге.
  </div>

  <div class="info-right">
    * Не является публичной офертой. Цены рассчитываются по курсу на момент оплаты.
  </div>
</div>
<!-- ======= /Блок пояснений ======= -->




  

  <div class="price-top-bar">
    <div class="search-wrapper">
      <i class="fa-solid fa-magnifying-glass search-icon"></i>
      <input type="text" id="searchInput" placeholder="Поиск модели..." />
      <span class="search-spinner" id="searchSpinner"></span>
    </div>

    <div class="price-date">
      Цены актуальны на <b><?= $rates['UPDATED'] ?></b>
    </div>


  </div>

  <div class="price-wrap">
  <div id="tableLoader" class="loader-overlay" aria-hidden="true">
    <div class="loader"></div>
  </div>

  <table id="priceTable" class="price-table fade-in">
    <thead>
      <tr>
        <th class="left">Модель</th>
        <th class="sortable" data-sort="price">Цена <i class="fa-solid fa-sort"></i></th>
        <th>Потребление</th>
        <th>Хешрейт</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td class="left">
            <a href="<?= htmlspecialcharsbx($p['URL']) ?>" target="_blank"><?= htmlspecialcharsbx($p['NAME']) ?></a>
          </td>
          <td class="price" data-value="<?= $p['PRICE'] ?>"><?= $p['PRICE_FORMATTED'] ?></td>
          <td><?= htmlspecialcharsbx($p['POWER']) ?></td>
          <td><?= htmlspecialcharsbx($p['HASHRATE']) ?></td>
          <td>
  <a href="#"
     class="btn-order js-open-popup-form"
     data-metric-goal="open-order"
     data-name="<?= htmlspecialcharsbx($p['NAME']) ?>"
     data-img="<?= htmlspecialcharsbx($p['IMG']) ?>">
    Заказать
  </a>
</td>



        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="./style.css">
  <script src="./js.js"></script>


  <!-- ===== POPUP "ЗАКАЗ" ===== -->
<div class="form-popup popup-form-wrapper" id="mainPopupFormWrapper" style="display: none;">
  <div class="form-popup__items">
    <button type="button" class="form-popup__close-btn popup-form__close-btn menu-close" id="closeMainPopupFormBtn" aria-label="Закрыть">
      <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M22.9844 10L10.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M10.9844 10L22.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <div class="form-popup__title-img-wrapper">
      <h2 class="form-popup__title">Заказ оборудования</h2>
      <div class="form-popup__img-wrapper">
        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/components/popup_form_image.png"
             alt="Контейнер для майнинг фермы"
             class="form-popup__img" loading="lazy" width="300" height="200">
      </div>
    </div>

    <form class="form-popup__popup-form js-ajax-form" id="contactFormPopup" data-metric-goal="send-price-lead">
      <p class="form-popup__cta">
        Заполните форму, чтобы оформить заказ. Мы свяжемся с вами в ближайшее время.
      </p>

      <label for="popup_client_name">Имя:</label>
      <input type="text" name="client_name" id="popup_client_name" placeholder="Имя" class="form-popup__input form-input">

      <label for="popup_client_phone">Телефон*:</label>
      <input type="tel" name="client_phone" id="popup_client_phone" placeholder="Телефон*" class="form-popup__input form-input js-phone-mask" required>

      <!-- <label for="popup_client_email">Email:</label>
      <input type="email" name="client_email" id="popup_client_email"
             placeholder="your@email.com (необязательно)" class="form-popup__input form-input"> -->
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

      <input type="hidden" name="source_id" value="23">
      <input type="hidden" name="form_name" value="">
      <input type="hidden" name="page_url" value="">
      <input type="hidden" name="client_comment" id="popup_product_name" value="">


      <div class="form-group form-check mb-3">
        <input type="checkbox" id="privacy-policy-popup" name="privacy-policy" class="form-check-input" required>
         <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
      </div>

      <button type="submit" class="form-popup__submit-btn btn btn-primary" id="submitContactBtnPopup">
        Оставить заявку
      </button>

      <p class="form-popup__error-message form-error-message" style="color: red; display: none;"></p>
    </form>
  </div>
</div>
<!-- ===== /POPUP ===== -->

</section>






<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>