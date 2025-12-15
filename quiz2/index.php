<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GIS Mining</title>


  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lightgallery-bundle.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/lightgallery.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>



  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="style.css">

<script src="/local/templates/main/assets/vendor/js/imask.min.js"></script>


<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(102682922, 'init', {webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/102682922" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->



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

<script>
document.addEventListener("DOMContentLoaded", function() {
  const metrikaId = 102682922;

  ym(metrikaId, 'getClientID', function(clientID) {
    if (!clientID) return;

    // WhatsApp — проставляем всем элементам с id="wa-link"
    document.querySelectorAll('[id="wa-link"]').forEach(function(waLink) {
      const base = "https://api.whatsapp.com/send/?phone=%2B79311116071&text=";
      const msg  = encodeURIComponent(
        "Здравствуйте! Мой промокод: " + clientID + "\n" +
        "У меня вопрос по оборудованию для майнинга\n" +
        "Сможете помочь?"
      );
      waLink.href = base + msg;
    });

    // Telegram — теперь это БОТ
    document.querySelectorAll('[id="tg-link"]').forEach(function(tgLink) {
      const base = "https://t.me/gismining_chat_bot?start=cid_";
      tgLink.href = base + encodeURIComponent(clientID);
    });

  });
});
</script>


<!-- Cookie Banner -->
<div class="cookie-banner" id="cookieBanner">
  <p>
    Этот сайт использует файлы cookies для улучшения работы и аналитики. 
    Продолжая пользоваться сайтом, вы соглашаетесь с 
    <a href="/policy-confidenciales/" target="_blank">политикой конфиденциальности</a>.
  </p>
  <button class="cookie-close" id="cookieClose" aria-label="Закрыть">&times;</button>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    const swiper = new Swiper('.gallery-swiper', {
      slidesPerView: "auto",
      centeredSlides: true,
      loop: true,
      spaceBetween: 20,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      watchSlidesProgress: true,
      on: {
        progress(swiper) {
          swiper.slides.forEach(slide => {
            const slideRect = slide.getBoundingClientRect();
            const vpWidth = window.innerWidth;

            // Убираем класс faded
            slide.classList.remove('faded');

            // Проверяем: если полностью вышло за левый край или за правый
            if (slideRect.right < 0 || slideRect.left > vpWidth) {
              slide.classList.add('faded');
            }
          });
        },
        setTranslate(swiper) {
          swiper.emit('progress'); // пересчёт при изменении
        }
      }
    });

    // LightGallery
    const wrapper = document.querySelector('.gallery-swiper .swiper-wrapper');
    if (wrapper && typeof lightGallery === 'function') {
      lightGallery(wrapper, {
        selector: 'a',
        plugins: (typeof lgZoom !== 'undefined' && typeof lgThumbnail !== 'undefined') ? [lgZoom, lgThumbnail] : [],
        speed: 400,
        download: false,
        thumbnail: true,
        zoom: true
      });
    }
  });
</script>








</head>
<body>

  <!-- Общий фон: и шапка, и hero -->
  <section class="masthead">
    <div class="container">
      <!-- Header -->
      <header>
        <div class="header-left">
          <div class="logo">
            <img src="/local/templates/main/assets/img/header/logo-wh-kv.svg" alt="GIS Mining">
            <div class="logo-text">
              <span>Топовая компания<br>на рынке майнинга в России</span>
            </div>
          </div>
          <div class="registry">
            <img src="/quiz/img/fns.jpg" alt="ФНС">
            <span>В реестре операторов майнинга РФ</span>
          </div>
        </div>
        <div class="header-right">
          <div class="socials">
            <a id="wa-link" href="https://wa.me/79311116071" rel="noopener noreferrer nofollow" target="_blank" class="whatsapp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <a id="tg-link" href="https://t.me/gismining_official" rel="noopener noreferrer nofollow" target="_blank" rel="nofollow" class="telegram" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
          </div>
          <div class="phone-block">
            <div class="phone"><a href="tel:+78007777798">8 (800) 777-77-98</a></div>
            <div class="call-link">Звонок бесплатный</div>
          </div>
          <a href="#" class="btn" id="openCallbackBtn">Перезвоните мне</a>

        </div>
      </header>

      <!-- Hero Section -->
      <section class="hero">
        <div class="hero-text">
          <h1>
            Хостинг для размещения Asic-майнеров<br>
            Тариф “Все включено” <span>за 5.3 ₽/кВт включая НДС</span>
          </h1>
          <p>
            Майнинг-отель на базе Калиниской АЭС <span>UpTime до 99,99%</span>
          </p>
          <ul class="hero-list">
            <li>Тариф фиксируется в договоре</li>
            <li>12 инженеров дежурят 24/7, время реакции до 7 мин.</li>
            <li>Удаленный контроль через приложение</li>
            <li>Безопасность стратегического объекта</li>
            <li>Страхование оборудования  (пожар, кража, аварии)</li>
            <li>Фактический UpTime 99,99% по договору 97%</li>
          </ul>
          
          <a href="#" class="btn" onclick="Quiznaya.showModal()">Получить предложение</a>
        </div>
        <div class="hero-image video-wrapper">
  <video id="heroVideo" class="wrapper-video__feature-video" muted loop playsinline preload="metadata"
         poster="/upload/dev2fun.imagecompress/webp/local/templates/main/assets/img/razmeschenie/hero_section_preview_img.webp">
    <source src="/local/templates/main/assets/img/razmeschenie/company-intro-section_video.webm" type="video/webm">
  </video>
  <div class="video-play-icon"></div>
   <p class="video-caption">Обзорное видео о дата-центре</p>
</div>


<!-- Video Popup -->
<div id="videoOverlay" class="video-overlay" aria-hidden="true"></div>
<div id="videoModal" class="video-modal" aria-hidden="true" role="dialog">
  <button class="video-close" id="closeVideoPopup" aria-label="Закрыть">&times;</button>
  <video id="popupVideo" class="video-player" controls autoplay playsinline>
    <source src="/local/templates/main/assets/img/razmeschenie/company-intro-section_video.webm" type="video/webm">
  </video>
</div>

        </div>
      </section>
    </div>
  </section>

  <section class="locations">
  <div class="container">
    <div class="locations__container">
      <div class="locations__left"></div>
      <div class="locations__right">
        <h3 class="locations__title">
          Если вы хотите распределить оборудование по территории страны мы предлагаем 5 дополнительных локаций с тарифом от 4,39 ₽/кВт
        </h3>

        <ul class="locations__list">
          <li class="location-item" onclick="Quiznaya.showModal()">
            <i class="fas fa-map-marker-alt"></i> Мурманск
          </li>
          <li class="location-item" onclick="Quiznaya.showModal()">
            <i class="fas fa-map-marker-alt"></i> Челябинск
          </li>
          <li class="location-item" onclick="Quiznaya.showModal()">
            <i class="fas fa-map-marker-alt"></i> Красноярск
          </li>
          <li class="location-item" onclick="Quiznaya.showModal()">
            <i class="fas fa-map-marker-alt"></i> Тверская область
          </li>
          <li class="location-item" onclick="Quiznaya.showModal()">
            <i class="fas fa-map-marker-alt"></i> Московская область
          </li>
        </ul>
      </div>
    </div>

    <!-- Кнопка по центру -->
    <div class="locations__button">
      <a href="#" class="btn" onclick="Quiznaya.showModal()">Получить предложение</a>
    </div>
  </div>
</section>




  <section class="contact-options">
  <div class="container">
    <h2 class="contact-options__title">
      МОЖНО СВЯЗАТЬСЯ С НАМИ В УДОБНОМ<br>
      МЕССЕНДЖЕРЕ, ОТВЕТИМ СРАЗУ
    </h2>

    <div class="contact-options__buttons">
      <a id="wa-link" href="https://wa.me/79311116071?text=Добрый%20день!%20Хочу%20закрепить%20тариф%20до%20конца%20года%20на%20размещение%20асиков.%20Какие%20условия?%20" target="_blank" class="contact-btn whatsapp" target="_blank">
        <i class="fab fa-whatsapp"></i> Написать в WhatsApp
      </a>
      <a id="tg-link" href="https://t.me/gismining_official?text=Добрый%20день!%20Хочу%20закрепить%20тариф%20до%20конца%20года%20на%20размещение%20асиков.%20Какие%20условия?%20" target="_blank" rel="nofollow" class="contact-btn telegram" target="_blank">
        <i class="fab fa-telegram"></i> Написать в Telegram
      </a>
    </div>

    <div class="contact-options__phones">
      <p>Или позвоните по номеру:</p>
      <a href="tel:+78007777798">+7 (800) 777-77-98</a>
    </div>
  </div>
</section>


<section class="quote-banner">
  <div class="container">
    <img src="/quiz/img/cita2.jpg" alt="Цитата о создании дата-центров" class="quote-banner__image">
  </div>
</section>







<section class="photo-gallery">
  <div class="container">
    <h3 class="gallery__title">Галерея ЦОД</h3>

    <div class="swiper gallery-swiper">
      <div class="swiper-wrapper">
        <!-- 20 фото -->
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(1).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(1).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(2).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(2).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(3).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(3).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(4).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(4).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(5).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(5).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(6).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(6).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(7).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(7).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(8).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(8).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(9).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(9).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(10).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(10).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(11).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(11).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(12).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(12).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(13).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(13).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(14).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(14).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(15).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(15).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(16).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(16).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(18).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(18).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(19).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(19).jpg" loading="lazy"></a></div>
        <div class="swiper-slide"><a href="/quiz/img/mining-hotel/mining_hotel(20).jpg"><img src="/quiz/img/mining-hotel/mining_hotel(20).jpg" loading="lazy"></a></div>
      </div>

      <!-- Стрелки -->
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
  </div>
</section>




<!-- Лайтбокс (вставить прямо один раз, ниже внизу страницы) -->
<div id="galleryOverlay" class="gallery-overlay" aria-hidden="true" hidden>
  <div class="gallery-modal" role="dialog" aria-modal="true" aria-labelledby="galleryCaption">
    <button class="gallery-close" id="galleryClose" aria-label="Закрыть">&times;</button>
    <button class="gallery-prev" id="galleryPrev" aria-label="Предыдущее">&#10094;</button>
    <button class="gallery-next" id="galleryNext" aria-label="Следующее">&#10095;</button>
    <img id="galleryImage" src="" alt="">
    <div id="galleryCaption" class="gallery-caption"></div>
  </div>
</div>



<footer class="site-footer">
  <div class="container footer-container">
    <!-- Левая колонка -->
    <div class="footer-col">
      <div class="footer-logo">
        <img src="/local/templates/main/assets/img/header/logo_header_white.webp" alt="B-Power">
      </div>
      <p class="footer-desc">
        Топовая компания
на рынке майнинга в России. Работаем по всей РФ с&nbsp;2015&nbsp;г.
      </p>
      <p class="footer-copy">© 2025 Все права защищены</p>
      <ul class="footer-links">
        <li><a href="/policy-confidenciales/" target="_blank">Политика конфиденциальности</a></li>
      </ul>
    </div>

    <!-- Центральная колонка -->
    <div class="footer-col">
      <p class="footer-title">Адрес:</p>
      <p>117105, Москва, Варшавское шоссе, 1, стр. 1-2 W-Plaza</p>
      <p>ООО «ГИС»<br>
        ИНН 7733361459<br>
        ОГРН 1207700422104<br>
      </p>
    </div>

    <!-- Правая колонка -->
    <div class="footer-col">
      <p>
        <a href="tel:+78007777798" class="footer-phone">8 (800) 777-77-98</a><br>
        <span class="footer-free">Звонок бесплатный</span><br>
        <span class="footer-status">Сейчас работаем</span>
      </p>
      <p class="footer-note">
        Любая информация, представленная на данном сайте,<br>
        носит исключительно информационный характер и ни при<br>
        каких условиях не является публичной офертой,<br>
        определяемой положениями статьи 437 ГК РФ.<br>
        Отправляя заполненную форму обратной связи или заявку,<br>
        вы даёте согласие на обработку ваших персональных данных.
      </p>
    </div>
  </div>
</footer>





<!-- Затемнение (скрыто по умолчанию) -->
<div id="gmOverlay" class="gm-overlay" aria-hidden="true" hidden></div>

<!-- Попап (скрыт по умолчанию) -->
<div id="gmModal" class="gm-modal" role="dialog" aria-modal="true" aria-labelledby="gmTitle" aria-hidden="true" hidden>
  <button class="gm-modal__close" id="gmClose" aria-label="Закрыть">&times;</button>
  <h2 class="gm-modal__title" id="gmTitle">Заказать звонок</h2>

  <form id="callbackForm" class="gm-modal__form">
  <input type="text"  name="client_name"  placeholder="Ваше имя" class="gm-input">
  <input type="tel"   name="client_phone" placeholder="Телефон*" required class="gm-input js-phone-mask">

  <input type="hidden" name="form_name" value="Перезвоните мне">
  <input type="hidden" name="source_id" value="31">
  <input type="hidden" name="page_url"  value="">

  <!-- UTM метки -->
  <input type="hidden" name="utm_source">
  <input type="hidden" name="utm_medium">
  <input type="hidden" name="utm_campaign">
  <input type="hidden" name="utm_content">
  <input type="hidden" name="utm_term">

  <div class="gm-policy">
    <input type="checkbox" id="gmPolicy" required>
    <label for="gmPolicy">Согласен(а) с 
      <a href="/policy-confidenciales/" target="_blank">политикой конфиденциальности</a>
    </label>
  </div>

  <button type="submit" class="btn">Отправить</button>
  <p class="gm-msg gm-msg--error"   id="popupError"></p>
  <p class="gm-msg gm-msg--success" id="popupSuccess"></p>
</form>

</div>

<!-- JS попапа (с версией для обхода кэша) -->
<script src="popup.js?v=3"></script>










</body>
</html>
