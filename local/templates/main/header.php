<?php
// Проверка на прямое подключение файла. Обязательная строка безопасности Битрикс.
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

?>
<!DOCTYPE html>
<html lang="ru">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    <!--    <meta http-equiv="x-rim-auto-match" content="none">-->
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <style>
        <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/assets/css/critical.css'); ?>
    </style>

    <script>
        window.YM_ID = 102682922; // Замените на ваш реальный ID метрики
    </script>

    <?php

    // --- НАЧАЛО БЛОКА УПРАВЛЕНИЯ АССЕТАМИ ---
    use Bitrix\Main\Page\Asset;


    // 3. РЕГИСТРАЦИЯ ВНЕШНИХ РЕСУРСОВ И СТРОК
    Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
    Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');

    // 1. РЕГИСТРАЦИЯ CSS ФАЙЛОВ
    
    // Основной CSS
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/main.css?v=1.1.48", false, true);
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/blocks/product-card.css?v=1.0.0", false, true);

    //	Asset::getInstance()->addCss("https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css");
//    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/vendor/css/bootstrap.min.css");
    
    //    Asset::getInstance()->addCss("https://unpkg.com/swiper/swiper-bundle.min.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/vendor/css/swiper-bundle.min.css");

    // 2. РЕГИСТРАЦИЯ JAVASCRIPT ФАЙЛОВ В ПРАВИЛЬНОМ ПОРЯДКЕ
    // Второй параметр "true" указывает Битриксу переместить тег <script> в конец <body>
    
    // --- ШАГ 1: Основные библиотеки ---
//	Asset::getInstance()->addString('<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>', true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/vendor/js/jquery-3.6.0.min.js", true);
    //
//	// --- ШАГ 2: Дополнительные библиотеки ---
//	Asset::getInstance()->addJs("https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/vendor/js/bootstrap.bundle.min.js", true);
    //	Asset::getInstance()->addJs("https://unpkg.com/swiper/swiper-bundle.min.js", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/vendor/js/swiper-bundle.min.js", true);
    //	Asset::getInstance()->addJs("https://unpkg.com/imask", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/vendor/js/imask.min.js", true);


    // --- ШАГ 3: Ваши кастомные скрипты, которые используют эти библиотеки ---
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/form-actions.js", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/cart.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/main.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/native-slider.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/sliders.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/load-more.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/components.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/media-mentions.js?v=1.1.1", true);
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/copy-text.js?v=1.1.1", true);

    // 4. РЕГИСТРАЦИЯ СКРИПТОВ-СЧЕТЧИКОВ
    // Оборачиваем внешние скрипты в addString, чтобы централизовать управление ими.
    // Одинарные кавычки '...' используются, чтобы не экранировать кавычки внутри скриптов.
    Asset::getInstance()->addString('<script>(function(w,d,u){var s=d.createElement(\'script\');s.async=true;s.src=u+\'?\'+(Date.now()/60000|0);var h=d.getElementsByTagName(\'script\')[0];h.parentNode.insertBefore(s,h);})(window,document,\'https://cdn-ru.bitrix24.ru/b26737746/crm/site_button/loader_2_j29xo7.js\');</script>');
    //	Asset::getInstance()->addString('<script>(function(w,d,n,c){w.CalltouchDataObject=n;w[n]=function(){w[n]["callbacks"].push(arguments);};if(!w[n]["callbacks"]){w[n]["callbacks"]=[];}w[n]["loaded"]=false;if(typeof c!=="object"){c=[c];}w[n]["counters"]=c;for(var i=0;i<c.length;i+=1){p(c[i]);}function p(cId){var a=d.getElementsByTagName("script")[0],s=d.createElement("script"),ins=function(){a&&a.parentNode?a.parentNode.insertBefore(s,a):(d.head||d.body||d.documentElement).appendChild(s);},m=typeof Array.prototype.find==="function",fN=m?"init-min.js":"init.js";s.async=true;s.src="https://mod.calltouch.ru/"+fN+"?id="+cId;if(w.opera=="[object Opera]"){d.addEventListener("DOMContentLoaded",ins,false);}else{ins();}}})(window,document,"ct","wqdys6ni");</script>');
//	Asset::getInstance()->addString('<script type="text/javascript">window._ab_id_=158561</script><script src="https://cdn.botfaqtor.ru/one.js" async></script>');
//	Asset::getInstance()->addString('<script>(function(w,d,s,o){var j=d.createElement(s);j.async=true;j.src=\'//script.marquiz.ru/v2.js\';j.onload=function(){if(document.readyState!==\'loading\')Marquiz.init(o);else document.addEventListener("DOMContentLoaded",function(){Marquiz.init(o);});};d.head.insertBefore(j,d.head.firstElementChild);})(window,document,\'script\',{host:\'//quiz.marquiz.ru\',region:\'ru\',id:\'681f07f0f9beb50019de31fc\',autoOpen:false,autoOpenFreq:\'once\',openOnExit:false,disableOnMobile:false});</script>');
//	Asset::getInstance()->addString('<script type="text/javascript">(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();for(var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r){return;}}k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");ym(102682922,"init",{clickmap:true,trackLinks:true,accurateTrackBounce:true,webvisor:true});</script>');
    
    // --- КОНЕЦ БЛОКА УПРАВЛЕНИЯ АССЕТАМИ ---
    
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

    // Также формируем URL для OG-картинки, чтобы он тоже был правильным
    $ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';

    // --- ФОРМИРОВАНИЕ CANONICAL URL ---
    // Canonical должен указывать на базовую версию страницы без:
    // - GET-параметров (фильтры, utm и т.д.)
    // - Пагинации (/page-2/, /page-3/)
    // - Суффикса калькулятора (/calculator-dohodnosti/)
    
    $canonicalUrl = $pageUrl;

    // 1. Удаляем пагинацию (если есть)
    $canonicalUrl = preg_replace('#/page-\d+/?$#', '/', $canonicalUrl);

    // 2. Удаляем суффикс калькулятора (если есть)
    $canonicalUrl = preg_replace('#/calculator-dohodnosti/?$#', '/', $canonicalUrl);

    // 3. Нормализуем - убираем двойные слеши и добавляем trailing slash
    $canonicalUrl = preg_replace('#/+#', '/', $canonicalUrl); // Убираем двойные слеши
    if ($canonicalUrl !== '/' && substr($canonicalUrl, -1) !== '/') {
        $canonicalUrl .= '/'; // Добавляем trailing slash
    }

    $fullPageUrl = $protocol . '://' . $serverName . $canonicalUrl;

    // --- УСТАНОВКА CANONICAL URL ---
    // Устанавливаем через AddHeadString
    // ShowLink("canonical") может вывести canonical из админки, поэтому используем только AddHeadString
    $APPLICATION->AddHeadString('<link rel="canonical" href="' . $fullPageUrl . '">', true);


    // --- РЕГИСТРАЦИЯ ОТЛОЖЕННОЙ ФУНКЦИИ ДЛЯ МЕТА-ТЕГОВ ---
    $APPLICATION->AddBufferContent('renderCustomMetaTags');

    // JSON-LD схемы выводятся в footer.php, после выполнения всех компонентов страницы
    
    // ГЛАВНАЯ ФУНКЦИЯ БИТРИКСА. Выводит все, что мы зарегистрировали выше,
    // а также динамические мета-теги (description, keywords, robots, canonical), которые вы зададите в админке.
    ob_start();

    $headContent = ob_get_clean();

    // Убираем мета-тег keywords и выводим остальные, заменяя ShowHead() набором отдельных вызовов
    $APPLICATION->ShowMeta("robots");
    $APPLICATION->ShowMeta("description");
    // ShowLink("canonical") убран - canonical уже установлен через AddHeadString выше
    $APPLICATION->ShowCSS();
    $APPLICATION->ShowHeadStrings();
    $APPLICATION->ShowHeadScripts();
    ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <!-- Favicon. Их можно оставить как есть, это не критично. -->
    <link rel="icon" type="image/svg+xml" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">



    <!-- Yandex.Metrika counter -->
    <script>
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) { if (document.scripts[j].src === r) { return; } }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

        ym(102682922, 'init', { webvisor: true, clickmap: true, ecommerce: "dataLayer", accurateTrackBounce: true, trackLinks: true });
    </script>
    <!-- /Yandex.Metrika counter -->

    <script src='https://senchukov.ru/include/actions-visit.js'></script>

    <!-- B242YA start -->
    <script>
        (function (w, d, u) {
            var s = d.createElement('script'); s.defer = false; s.async = false; s.id = 'b242ya-script'; s.src = u + '?' + (Date.now() / 60000 | 0);
            var h = d.getElementsByTagName('script')[0]; h.parentNode.insertBefore(s, h);
        })(window, document, 'https://67p.b242ya.ru/static/js/b242ya.js');
        var b242yaScript = document.querySelector('#b242ya-script');
        b242yaScript.addEventListener('load', function () {
            B242YAInit({
                portal: 'https://gis-mining.bitrix24.ru/',
                pid: '2695fb2ece163b6a16fa9eca9fe14be8',
                presets: {}
            });
        });
    </script>
    <!-- B242YA end -->




    <!-- calltouch -->
    <script>
        (function (w, d, n, c) { w.CalltouchDataObject = n; w[n] = function () { w[n]["callbacks"].push(arguments) }; if (!w[n]["callbacks"]) { w[n]["callbacks"] = [] } w[n]["loaded"] = false; if (typeof c !== "object") { c = [c] } w[n]["counters"] = c; for (var i = 0; i < c.length; i += 1) { p(c[i]) } function p(cId) { var a = d.getElementsByTagName("script")[0], s = d.createElement("script"), i = function () { a.parentNode.insertBefore(s, a) }, m = typeof Array.prototype.find === 'function', n = m ? "init-min.js" : "init.js"; s.async = true; s.src = "https://mod.calltouch.ru/" + n + "?id=" + cId; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", i, false) } else { i() } } })(window, document, "ct", "wqdys6ni");
    </script>
    <!-- calltouch -->






</head>

<body>
    <?php
    // Вывод панели администратора Битрикс. Обязательная функция.
    $APPLICATION->ShowPanel();
    ?>

    <noscript>
        <div><img src="https://mc.yandex.ru/watch/102682922" style="position:absolute; left:-9999px;" alt=""></div>
    </noscript>

    <!-- HEADER -->
    <header class="site-header">
        <div class="container header__container <?php $APPLICATION->ShowProperty("header_right_class", ""); ?>">

            <div class="site-header__left-side">
                <a href="/" class="logo header__logo">
                    <picture>
                        <source media="(min-width: 768px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/header/logo_header_white.webp"
                            type="image/webp">
                        <source media="(min-width: 768px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/header/logo_header_white.png">
                        <source media="(max-width: 767px)"
                            srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/header/logo_header_white_mobile.webp"
                            type="image/webp">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/header/logo_header_white_mobile.png"
                            alt="GIS Mining - Логотип компании" width="100" height="40">
                    </picture>
                </a>
                <div class="header__main-content">

                    <?php $APPLICATION->IncludeComponent( // <-- ЭТО ЕДИНСТВЕННОЕ, ЧТО ДОЛЖНО ЗДЕСЬ ОСТАТЬСЯ ОТ МЕНЮ
                        "bitrix:menu",
                        "header_desktop",
                        array(
                            "ALLOW_MULTI_SELECT" => "N",
                            "CHILD_MENU_TYPE" => "left",
                            "DELAY" => "N",
                            "MAX_LEVEL" => "2",
                            "MENU_CACHE_GET_VARS" => array(""),
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "ROOT_MENU_TYPE" => "top",
                            "USE_EXT" => "N", // Это должно быть "N"
                        )
                    ); ?>
                </div>
            </div>

            <div class="site-header__right-side <?php $APPLICATION->ShowProperty("header_right_class", ""); ?>">
                <div class="header__contacts-social">

                    <!-- 1. ИКОНКА КОРЗИНЫ -->
                    <a href="#" class="catalog-page_cart-link js-open-cart" data-metric-goal="open-cart">
                        <div class="catalog-page__cart-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M3.5 4.5H5.05848C5.7542 4.5 6.10206 4.5 6.36395 4.68876C6.62584 4.87752 6.73584 5.20753 6.95585 5.86754L7.5 7.5"
                                        stroke="#545454" stroke-linecap="round"></path>
                                    <path
                                        d="M17.5 17.5H8.05091C7.90471 17.5 7.83162 17.5 7.77616 17.4938C7.18857 17.428 6.78605 16.8695 6.90945 16.2913C6.92109 16.2367 6.94421 16.1674 6.99044 16.0287V16.0287C7.04177 15.8747 7.06743 15.7977 7.09579 15.7298C7.38607 15.0342 8.04277 14.5608 8.79448 14.5054C8.8679 14.5 8.94906 14.5 9.11137 14.5H14.5"
                                        stroke="#545454" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path
                                        d="M14.1787 14.5H11.1376C9.85836 14.5 9.21875 14.5 8.71781 14.1697C8.21687 13.8394 7.96492 13.2515 7.461 12.0757L7.29218 11.6818C6.48269 9.79294 6.07794 8.84853 6.52255 8.17426C6.96715 7.5 7.99464 7.5 10.0496 7.5H15.3305C17.6295 7.5 18.779 7.5 19.2126 8.24711C19.6462 8.99422 19.0758 9.99229 17.9352 11.9884L17.6517 12.4846C17.0897 13.4679 16.8088 13.9596 16.3432 14.2298C15.8776 14.5 15.3113 14.5 14.1787 14.5Z"
                                        stroke="#545454" stroke-linecap="round"></path>
                                    <circle cx="17" cy="20" r="1" fill="#545454"></circle>
                                    <circle cx="9" cy="20" r="1" fill="#545454"></circle>
                                </g>
                            </svg>
                        </div>
                        <!-- Этот кружок будет показывать количество товаров -->
                        <span class="header-cart-badge js-cart-badge">0</span>
                    </a>

                    <div class="header__subscribe">
                        <!-- <a href="https://t.me/gismining" target="_blank" rel="noopener noreferrer"
                                class="header__subscribe-icon header__subscribe-icon-telegram"
                                aria-label="Telegram новости">
                                <span class="header__subscribe-text">Подписывайтесь на нас</span>
                                <svg width="33" height="31" viewBox="0 0 33 31" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M30.1469 0.414263C30.1469 0.414263 33.1071 -0.884308 32.8604 2.26937C32.7782 3.56796 32.0381 8.11295 31.4625 13.029L29.489 27.5916C29.489 27.5916 29.3246 29.7249 27.8445 30.096C26.3643 30.467 24.1442 28.7974 23.733 28.4264C23.4041 28.1481 17.5659 23.9741 15.5101 21.9335C14.9345 21.377 14.2767 20.2639 15.5923 18.9653L24.2264 9.68984C25.2132 8.57675 26.1999 5.97961 22.0884 9.13329L10.5764 17.9451C10.5764 17.9451 9.26076 18.8726 6.79394 18.0378L1.44904 16.1827C1.44904 16.1827 -0.524459 14.7914 2.84693 13.4C11.0698 9.04046 21.184 4.58821 30.1469 0.414263Z"
                                        fill="#5B61FF" />
                                </svg>
                            </a> -->
                        <a href="#" target="_blank" rel="noopener noreferrer"
                            class="header__subscribe-icon header__subscribe-icon--calc" aria-label="VC.ru">
                            <span class="header__subscribe-text">Калькулятор</span>
                            <svg width="33" height="33" viewBox="0 0 33 33" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.4905 23.5339V26.6494C9.4905 26.9051 9.57383 27.1164 9.7405 27.2834C9.90717 27.4504 10.118 27.5339 10.373 27.5339C10.6283 27.5339 10.8398 27.4504 11.0075 27.2834C11.1755 27.1164 11.2595 26.9051 11.2595 26.6494V23.5339H14.375C14.6307 23.5339 14.842 23.4506 15.009 23.2839C15.176 23.1172 15.2595 22.9064 15.2595 22.6514C15.2595 22.3961 15.176 22.1846 15.009 22.0169C14.842 21.8489 14.6307 21.7649 14.375 21.7649H11.2595V18.6494C11.2595 18.3937 11.1762 18.1824 11.0095 18.0154C10.8428 17.8484 10.632 17.7649 10.377 17.7649C10.1217 17.7649 9.91017 17.8484 9.7425 18.0154C9.5745 18.1824 9.4905 18.3937 9.4905 18.6494V21.7649H6.375C6.11933 21.7649 5.908 21.8482 5.741 22.0149C5.574 22.1816 5.4905 22.3924 5.4905 22.6474C5.4905 22.9027 5.574 23.1142 5.741 23.2819C5.908 23.4499 6.11933 23.5339 6.375 23.5339H9.4905ZM20.375 26.0339H27.375C27.6307 26.0339 27.842 25.9506 28.009 25.7839C28.176 25.6172 28.2595 25.4064 28.2595 25.1514C28.2595 24.8961 28.176 24.6846 28.009 24.5169C27.842 24.3489 27.6307 24.2649 27.375 24.2649H20.375C20.1193 24.2649 19.908 24.3482 19.741 24.5149C19.574 24.6816 19.4905 24.8924 19.4905 25.1474C19.4905 25.4027 19.574 25.6142 19.741 25.7819C19.908 25.9499 20.1193 26.0339 20.375 26.0339ZM20.375 21.0339H27.375C27.6307 21.0339 27.842 20.9506 28.009 20.7839C28.176 20.6172 28.2595 20.4064 28.2595 20.1514C28.2595 19.8961 28.176 19.6846 28.009 19.5169C27.842 19.3489 27.6307 19.2649 27.375 19.2649H20.375C20.1193 19.2649 19.908 19.3482 19.741 19.5149C19.574 19.6816 19.4905 19.8924 19.4905 20.1474C19.4905 20.4027 19.574 20.6142 19.741 20.7819C19.908 20.9499 20.1193 21.0339 20.375 21.0339ZM6.875 9.93391H13.875C14.1307 9.93391 14.342 9.85058 14.509 9.68391C14.676 9.51725 14.7595 9.30641 14.7595 9.05141C14.7595 8.79608 14.676 8.58458 14.509 8.41691C14.342 8.24891 14.1307 8.16491 13.875 8.16491H6.875C6.61933 8.16491 6.408 8.24825 6.241 8.41491C6.074 8.58158 5.9905 8.79242 5.9905 9.04742C5.9905 9.30275 6.074 9.51425 6.241 9.68192C6.408 9.84992 6.61933 9.93391 6.875 9.93391ZM4.106 32.1494C3.18533 32.1494 2.41667 31.8411 1.8 31.2244C1.18333 30.6077 0.875 29.8391 0.875 28.9184V3.38041C0.875 2.45975 1.18333 1.69108 1.8 1.07441C2.41667 0.457747 3.18533 0.149414 4.106 0.149414H29.644C30.5647 0.149414 31.3333 0.457747 31.95 1.07441C32.5667 1.69108 32.875 2.45975 32.875 3.38041V28.9184C32.875 29.8391 32.5667 30.6077 31.95 31.2244C31.3333 31.8411 30.5647 32.1494 29.644 32.1494H4.106ZM23.875 10.3264L26.048 12.4994C26.22 12.6711 26.4227 12.7634 26.656 12.7764C26.8893 12.7891 27.1083 12.6929 27.313 12.4879C27.4953 12.3059 27.5885 12.1027 27.5925 11.8784C27.5962 11.6541 27.5032 11.4431 27.3135 11.2454L25.129 9.04941L27.302 6.87641C27.4737 6.70441 27.566 6.50175 27.579 6.26841C27.5917 6.03508 27.4993 5.81975 27.302 5.62242C27.1047 5.42508 26.8957 5.32641 26.675 5.32641C26.4543 5.32641 26.2453 5.42508 26.048 5.62242L23.875 7.79541L21.702 5.62242C21.53 5.45075 21.3273 5.35841 21.094 5.34541C20.8607 5.33275 20.6453 5.42508 20.448 5.62242C20.2507 5.81975 20.152 6.02875 20.152 6.24941C20.152 6.47008 20.2507 6.67908 20.448 6.87641L22.621 9.04941L20.4365 11.2454C20.2725 11.4174 20.184 11.6201 20.171 11.8534C20.1583 12.0867 20.2507 12.3021 20.448 12.4994C20.6453 12.6967 20.8543 12.7954 21.075 12.7954C21.2957 12.7954 21.5047 12.6967 21.702 12.4994L23.875 10.3264Z"
                                    fill="white" />
                            </svg>

                        </a>
                    </div>

                    <div class="header__phone-social-links">

                        <div class="header__phone-numbers-desktop">
                            <a href="tel:+74951919874" class="header__phone-number">
                                +7 (495) 191-98-74
                            </a>
                            <a href="tel:+78007777798" class="header__phone-number">
                                +7 (800) 777-77-98
                            </a>
                            <a href="mailto:info@gis-corp.ru" class="header__phone-number">
                                info@gis-corp.ru
                            </a>
                        </div>


                        <div class="header__phone-mobile">

                            <input type="checkbox" id="phone-toggle" class="header__phone-toggle-checkbox">


                            <label for="phone-toggle" class="header__phone-icon-trigger"
                                aria-label="Показать номера телефонов">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">

                                    <rect width="25" height="25" fill="url(#pattern_phone_icon_nojs)"></rect>
                                    <defs>
                                        <pattern id="pattern_phone_icon_nojs" patternContentUnits="objectBoundingBox"
                                            width="1" height="1">
                                            <use xlink:href="#image_phone_icon_nojs" transform="scale(0.02)"></use>
                                        </pattern>
                                        <image id="image_phone_icon_nojs" width="50" height="50"
                                            preserveAspectRatio="none"
                                            xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAACEElEQVR4nO3ZzYtNYRzA8Z/XUWJDGKJk5WUpWUrsyEYUNanBn6D8BbLDCv+BxcR4jSxQkxoLRnmLImXl5UZIqI9OcxY3Zu7c59zuuc9kPn/B/fac+5zn+Z2IGTN6B3swii94gK0xnWAezvpXA6tiOsAiXDe5a5E7rMQTUxuIXGE5XmjPJ/RHbjAfI9IMR25wXDUHIyd4UzHkA1ZELvBVdUORC7zUmf2RAwx1GPI8coCjHYZ8jxxgGX53EHI/coHbHYQM5HbSreIRZkcuMAtjFUJ2Rm6wLzHicuTI+KrcSwgZjFxhI34mnILXRa5wMmFVHmJh5Ah95Q9sV7F190WOsAHfEmIuYm7kCEekOV9sGJEjE09TWjkVGY+G7iTGnM5yZYwPJl5VeMzmRG6wBm8TYy5hQeQGm/AxMebmRO+ZYrVwqBzF/sKz4ohUZ8yWchacYqz5BIDtLd5TV4pBYV0xO/AjMaY4zgziapvTmQN13l9SY1T4j/XXEbOtwmOWqlHMFOqI2Yz3uu8GVtdx9H9XQ0yj2Gy6HbO2wkuzitGuhjSNlVJumFV87npI0yeKc10MGaklpCnocOJ9pl27ag0pY9Yn3jSncqb2iL+uzScSBhqttt/e30DLLfquah5jcWQ2N9ub+Lg9Le5DkaMyaDdulUf4yRRb+dKYDrCkPBVfKD4YlZ8BX+NYcc3u9e+b8d/6A8BzVur0abPMAAAAAElFTkSuQmCC">
                                        </image>
                                    </defs>
                                </svg>
                            </label>


                            <div class="header__phone-dropdown">
                                <a href="tel:+74951919874" class="header__phone-dropdown__link">+7 (495) 191-98-74</a>
                                <a href="tel:+78007777798" class="header__phone-dropdown__link">+7 (800) 777-77-98</a>
                            </div>


                            <label for="phone-toggle" class="header__phone-dropdown-overlay"></label>
                        </div>

                        <!-- Иконка почты -->
                        <a href="mailto:info@gis-corp.ru" class="header__email-mobile" aria-label="Написать на почту">
                            <svg width="25" height="25" viewBox="0 0 24 24" fill="white"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 
                 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 6V7.17L12 
                 13L4 7.17V6H20ZM4 18V9L12 14.83L20 9V18H4Z" />
                            </svg>
                        </a>



                        <div class="header__social-icons">
                            <a id="wa-link-header-desktop" href="https://api.whatsapp.com/send/?phone=%2B79311116071"
                                target="_blank" rel="noopener noreferrer nofollow"
                                class="header__social-icon header__social-icon--whatsapp" aria-label="WhatsApp">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_5008_10889)">
                                        <rect y="3.05176e-05" width="32" height="32" fill="white" />
                                        <path
                                            d="M20.8007 17.4924C20.7618 17.4737 19.3037 16.7557 19.0446 16.6625C18.9388 16.6245 18.8255 16.5874 18.7049 16.5874C18.508 16.5874 18.3426 16.6855 18.2138 16.8783C18.0681 17.0948 17.6272 17.6102 17.491 17.7641C17.4732 17.7845 17.4489 17.8088 17.4343 17.8088C17.4213 17.8088 17.1956 17.7158 17.1273 17.6861C15.5631 17.0067 14.3759 15.3728 14.2131 15.0973C14.1898 15.0576 14.1889 15.0397 14.1887 15.0397C14.1944 15.0187 14.247 14.9659 14.2741 14.9387C14.3535 14.8602 14.4396 14.7566 14.5228 14.6564C14.5623 14.6089 14.6017 14.5614 14.6405 14.5166C14.7613 14.376 14.8151 14.267 14.8774 14.1406L14.9101 14.0749C15.0623 13.7725 14.9323 13.5173 14.8903 13.4349C14.8558 13.3659 14.2401 11.88 14.1746 11.7239C14.0172 11.3472 13.8092 11.1718 13.5202 11.1718C13.4934 11.1718 13.5202 11.1718 13.4077 11.1765C13.2708 11.1823 12.5249 11.2805 12.1952 11.4884C11.8455 11.7088 11.2539 12.4116 11.2539 13.6475C11.2539 14.7598 11.9598 15.8101 12.2629 16.2095C12.2704 16.2196 12.2842 16.24 12.3043 16.2694C13.4649 17.9644 14.9118 19.2206 16.3786 19.8065C17.7907 20.3706 18.4593 20.4358 18.8395 20.4358H18.8395C18.9993 20.4358 19.1272 20.4232 19.24 20.4121L19.3115 20.4053C19.7993 20.3621 20.8713 19.8066 21.1152 19.1289C21.3073 18.5952 21.3579 18.0121 21.2301 17.8004C21.1426 17.6565 20.9917 17.5841 20.8007 17.4924Z"
                                            fill="#18A53A" />
                                        <path
                                            d="M16.178 5.99951C10.7611 5.99951 6.35416 10.3734 6.35416 15.7495C6.35416 17.4884 6.81951 19.1905 7.70104 20.6802L6.01375 25.6574C5.98232 25.7502 6.0057 25.8528 6.07434 25.9226C6.12389 25.9732 6.1911 26.0005 6.25974 26.0005C6.28604 26.0005 6.31253 25.9965 6.33838 25.9883L11.5282 24.3391C12.9484 25.0979 14.5536 25.4985 16.1781 25.4985C21.5945 25.4985 26.001 21.1251 26.001 15.7495C26.001 10.3734 21.5945 5.99951 16.178 5.99951ZM16.178 23.4675C14.6494 23.4675 13.1689 23.0261 11.8963 22.191C11.8535 22.1629 11.8038 22.1485 11.7538 22.1485C11.7273 22.1485 11.7009 22.1525 11.6751 22.1607L9.07527 22.9871L9.91453 20.5111C9.94167 20.4309 9.9281 20.3426 9.8781 20.2742C8.90896 18.95 8.39666 17.3855 8.39666 15.7495C8.39666 11.4933 11.8874 8.03052 16.178 8.03052C20.468 8.03052 23.9583 11.4933 23.9583 15.7495C23.9584 20.0053 20.4682 23.4675 16.178 23.4675Z"
                                            fill="#18A53A" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_5008_10889">
                                            <rect width="32" height="32" rx="7" transform="matrix(1 0 0 -1 0 32)"
                                                fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                            <a id="tg-link-header-desktop" href="https://t.me/gismining_official" target="_blank"
                                rel="noopener noreferrer nofollow"
                                class="header__social-icon header__social-icon--telegram-contact"
                                aria-label="Telegram контакт">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect width="32" height="32" rx="7" fill="white" />
                                    <path
                                        d="M23.2949 6.17657C23.2949 6.17657 25.1451 5.31085 24.9909 7.4133C24.9395 8.27903 24.477 11.309 24.1172 14.5864L22.8838 24.2948C22.8838 24.2948 22.781 25.717 21.8559 25.9644C20.9308 26.2117 19.5432 25.0987 19.2862 24.8513C19.0807 24.6658 15.4318 21.8831 14.147 20.5227C13.7872 20.1517 13.3761 19.4096 14.1983 18.5439L19.5946 12.3603C20.2113 11.6182 20.8281 9.8868 18.2584 11.9893L11.0634 17.8638C11.0634 17.8638 10.2411 18.4821 8.69934 17.9256L5.35877 16.6888C5.35877 16.6888 4.12534 15.7613 6.23245 14.8337C11.3718 11.9274 17.6931 8.9592 23.2949 6.17657Z"
                                        fill="#006FFF" />
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <button class="header__mobile-menu-toggle" type="button" aria-expanded="false"
                aria-controls="mobile-menu-panel" aria-label="Открыть меню">
                <span></span><span></span><span></span>
            </button>
        </div>



        <div class="mobile-menu-panel" id="mobile-menu-panel">
            <!-- ВАЖНО: Мобильное меню также нужно сделать динамическим через второй вызов компонента bitrix:menu, но с другим шаблоном -->
            <div class="mobile-menu-panel__header">
                <a href="/" class="logo mobile-menu-panel__logo">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/header/logo_header_blue.png"
                        alt="GIS Mining - Логотип компании" width="168" height="25">
                </a>
                <div class="mobile-menu-panel__top-icons">
                    <a id="wa-link-header-mobile" href="https://api.whatsapp.com/send/?phone=%2B79311116071"
                        target="_blank" rel="noopener noreferrer nofollow"
                        class="mobile-menu-panel__icon mobile-menu-panel__icon--whatsapp" aria-label="WhatsApp">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_header_whatsapp)">
                                <rect x="-3.125" y="-3.70117" width="32" height="32" fill="#18A53A" />
                                <path
                                    d="M15.7974 13.2084C15.7737 13.197 14.8853 12.7595 14.7274 12.7027C14.6629 12.6795 14.5939 12.6569 14.5204 12.6569C14.4005 12.6569 14.2997 12.7167 14.2212 12.8342C14.1324 12.9661 13.8638 13.2801 13.7808 13.3739C13.7699 13.3863 13.7551 13.4011 13.7463 13.4011C13.7383 13.4011 13.6008 13.3445 13.5592 13.3264C12.6061 12.9124 11.8827 11.9169 11.7835 11.749C11.7694 11.7249 11.7688 11.7139 11.7686 11.7139C11.7721 11.7011 11.8042 11.669 11.8207 11.6524C11.8691 11.6045 11.9215 11.5414 11.9723 11.4804C11.9963 11.4514 12.0203 11.4225 12.044 11.3952C12.1175 11.3096 12.1503 11.2431 12.1883 11.1661L12.2082 11.1261C12.3009 10.9418 12.2217 10.7863 12.1961 10.7361C12.1751 10.6941 11.8 9.7887 11.7601 9.69358C11.6642 9.46405 11.5374 9.35718 11.3613 9.35718C11.345 9.35718 11.3613 9.35718 11.2928 9.36007C11.2094 9.36359 10.7549 9.42341 10.554 9.55007C10.3409 9.6844 9.98047 10.1126 9.98047 10.8656C9.98047 11.5434 10.4106 12.1833 10.5952 12.4267C10.5998 12.4328 10.6082 12.4453 10.6205 12.4632C11.3277 13.496 12.2093 14.2614 13.103 14.6184C13.9634 14.9621 14.3708 15.0018 14.6024 15.0018H14.6025C14.6998 15.0018 14.7777 14.9942 14.8464 14.9874L14.89 14.9832C15.1873 14.9569 15.8404 14.6184 15.989 14.2055C16.1061 13.8803 16.1369 13.525 16.0591 13.3961C16.0057 13.3084 15.9138 13.2643 15.7974 13.2084Z"
                                    fill="white" />
                                <path
                                    d="M12.9828 6.20557C9.68225 6.20557 6.99704 8.87059 6.99704 12.1463C6.99704 13.2058 7.28058 14.2429 7.81771 15.1506L6.78963 18.1833C6.77048 18.2398 6.78472 18.3023 6.82655 18.3449C6.85674 18.3757 6.89769 18.3923 6.93951 18.3923C6.95554 18.3923 6.97168 18.3899 6.98743 18.3849L10.1497 17.38C11.015 17.8424 11.993 18.0864 12.9828 18.0864C16.2831 18.0865 18.968 15.4217 18.968 12.1463C18.968 8.87059 16.2831 6.20557 12.9828 6.20557ZM12.9828 16.849C12.0514 16.849 11.1493 16.58 10.3739 16.0712C10.3478 16.0541 10.3175 16.0453 10.2871 16.0453C10.271 16.0453 10.2548 16.0477 10.2391 16.0527L8.65504 16.5563L9.16641 15.0476C9.18295 14.9988 9.17468 14.9449 9.14421 14.9033C8.5537 14.0964 8.24156 13.1431 8.24156 12.1463C8.24156 9.55297 10.3685 7.44308 12.9828 7.44308C15.5968 7.44308 17.7234 9.55297 17.7234 12.1463C17.7235 14.7394 15.5968 16.849 12.9828 16.849Z"
                                    fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_header_whatsapp">
                                    <rect width="24" height="24" rx="7" transform="matrix(1 0 0 -1 0.875 24.2988)"
                                        fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </a>
                    <a id="tg-link-header-mobile" href="https://t.me/gismining_official" target="_blank"
                        rel="noopener noreferrer nofollow"
                        class="mobile-menu-panel__icon mobile-menu-panel__icon--telegram" aria-label="Telegram">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.875" y="0.298828" width="24" height="24" rx="7" fill="#0093DE" />
                            <ellipse cx="12.8749" cy="12.2988" rx="11.9999" ry="12" fill="#0093DE" />
                            <path
                                d="M16.0224 7.38711C16.0224 7.38711 16.9474 6.95426 16.8703 8.00547C16.8446 8.43833 16.6134 9.95331 16.4335 11.592L15.8168 16.4461C15.8168 16.4461 15.7654 17.1572 15.3029 17.2809C14.8403 17.4046 14.1466 16.8481 14.0181 16.7244C13.9153 16.6316 12.0909 15.2403 11.4484 14.5601C11.2686 14.3746 11.063 14.0036 11.4741 13.5707L14.1722 10.4789C14.4806 10.1079 14.7889 9.24221 13.5041 10.2934L9.90666 13.2307C9.90666 13.2307 9.49552 13.5398 8.72465 13.2616L7.05438 12.6432C7.05438 12.6432 6.43767 12.1794 7.49122 11.7156C10.0609 10.2625 13.2215 8.77841 16.0224 7.38711Z"
                                fill="white" />
                        </svg>

                    </a>
                    <button type="button" class="mobile-menu-panel__close-button" aria-label="Закрыть меню">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- <div class="mobile-menu-panel__search">
                    <form action="/search/" method="get" role="search">
                        <input type="search" name="q" placeholder="Что вы хотите найти?" aria-label="Поиск по сайту">
                        <button type="submit" aria-label="Найти"><svg aria-hidden="true" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.5 14H14.71L14.43 13.73C15.41 12.59 16 11.11 16 9.5C16 5.91 13.09 3 9.5 3S3 5.91 3 9.5S5.91 16 9.5 16C11.11 16 12.59 15.41 13.73 14.43L14 14.71V15.5L19 20.49L20.49 19L15.5 14ZM9.5 14C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14Z"
                                    fill="currentColor" />
                            </svg></button>
                    </form>
                </div> -->
            <?php $APPLICATION->IncludeComponent(
                "bitrix:menu",
                "header_mobile", // Название вашего кастомного шаблона меню
                array(
                    "ALLOW_MULTI_SELECT" => "N",
                    "CHILD_MENU_TYPE" => "left", // Тип меню для дочерних пунктов (если есть)
                    "DELAY" => "N",
                    "MAX_LEVEL" => "2", // Указываем, что меню может быть двухуровневым (для мегаменю)
                    "MENU_CACHE_GET_VARS" => array(""),
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_TYPE" => "A",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "ROOT_MENU_TYPE" => "top", // Указываем, какой файл .php использовать для меню (.top.menu.php)
                    "USE_EXT" => "Y", // "Y" - подключать файлы .тип_меню.menu_ext.php (для автоматического добавления разделов инфоблоков)
                )
            ); ?>
            <div class="mobile-menu-panel__footer">
                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.62 10.79C8.06 13.62 10.38 15.94 13.21 17.38L15.41 15.18C15.68 14.91 16.08 14.82 16.43 14.93C17.55 15.3 18.76 15.51 20 15.51C20.55 15.51 21 15.96 21 16.51V20C21 20.55 20.55 21 20 21C10.61 21 3 13.39 3 4C3 3.45 3.45 3 4 3H7.5C8.05 3 8.5 3.45 8.5 4C8.5 5.24 8.7 6.45 9.07 7.57C9.18 7.92 9.09 8.31 8.82 8.59L6.62 10.79Z"
                        fill="currentColor" />
                </svg>
                <div class="mobile-menu-panel__phones-block">
                    <a href="tel:+74951919874" class="mobile-menu-panel__phone">
                        +7 (495) 191-98-74
                    </a>
                    <a href="tel:+78007777798" class="mobile-menu-panel__phone">
                        +7 (800) 777-77-98
                    </a>
                </div>
                <!-- <div class="mobile-menu-panel__btc-rate">
                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 15c-.83 0-1.5-.67-1.5-1.5V14h-1v-2h1v-1.5c0-.83.67-1.5 1.5-1.5h1.5c.83 0 1.5.67 1.5 1.5S14.33 12 13.5 12h-1.5v1.5c0 .83-.67 1.5-1.5 1.5zm2.5-5.5H13V10h1.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5H13V7.5c0-.28-.22-.5-.5-.5S12 7.22 12 7.5V9h-.5c-.28 0-.5.22-.5.5s.22.5.5.5H12v1.5c0 .28.22.5.5.5S13 13.28 13 13v-1.5h1.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5z" />
                        </svg>
                        23 076$
                    </div> -->
            </div>
        </div>







    </header>

    <!-- MAIN CONTENT -->
    <main class="site-main <?php $APPLICATION->ShowProperty("main_class", ""); ?>">

        <!-- Глобальные хлебные крошки (кроме главной страницы, mining-investicii и mining-hotel) -->
        <?php
        $currentPage = $APPLICATION->GetCurPage(false);
        if ($currentPage !== '/' && $currentPage !== '/index.php' && $currentPage !== '/mining-investicii/'):
            ?>

            <?php $APPLICATION->IncludeComponent(
                "bitrix:breadcrumb",
                "standard",
                array(
                    "SHOW_HOME" => "Y",
                    "HOME_TEXT" => "Главная",
                    "SHOW_SEPARATOR" => "Y",
                    "SEPARATOR" => "/",
                    "EXCLUDE_PAGES" => "404.php",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600"
                ),
                false
            ); ?>

        <?php endif; ?>

        <?php
        // Вывод заголовка H1 (используем отложенную функцию, чтобы учитывать свойства, установленные в теле страницы)
        $APPLICATION->AddBufferContent(function () use ($APPLICATION) {
            if ($APPLICATION->GetProperty("HIDE_TITLE") != "Y") {
                $h1Class = $APPLICATION->GetProperty("h1_class");
                return '<div class="container standard-header"><h1 class="section-title ' . $h1Class . '">' . $APPLICATION->GetTitle(false) . '</h1></div>';
            }
            return '';
        });
        ?>