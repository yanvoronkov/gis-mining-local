<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// --- ПОДГОТОВКА ДАННЫХ (НАДЕЖНЫЙ СПОСОБ) ---
$protocol = CMain::IsHTTPS() ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$fullPageUrl = $protocol . '://' . $domain . $APPLICATION->GetCurPage(false);
// УКАЗЫВАЕМ УНИКАЛЬНУЮ КАРТИНКУ ДЛЯ ЭТОЙ СТРАНИЦЫ (если она есть, если нет - можно оставить от главной)
$ogImageUrl = $protocol . '://' . $domain . '/local/templates/main/assets/img/home/home_open-graph_image.webp';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "404 - Страница не найдена | GIS Mining");
$APPLICATION->SetTitle("404 - Страница не найдена");
// Добавляем страницу в хлебные крошки
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Запрашиваемая страница не найдена. Вернитесь на главную страницу GIS Mining или воспользуйтесь навигацией сайта.");
$APPLICATION->SetPageProperty("keywords", "404, страница не найдена, ошибка, GIS Mining");
$APPLICATION->SetPageProperty("robots", "noindex, nofollow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "404 - Страница не найдена | GIS Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Запрашиваемая страница не найдена. Вернитесь на главную страницу GIS Mining или воспользуйтесь навигацией сайта.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "404 - Страница не найдена | GIS Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Запрашиваемая страница не найдена. Вернитесь на главную страницу GIS Mining или воспользуйтесь навигацией сайта.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-404");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД ХЛЕБНЫХ КРОШЕК СО СТАНДАРТНЫМ ШАБЛОНОМ -----
// Хлебные крошки теперь формируются автоматически в header
?>

    <main class="err404-page">
        <div class="err404-page__wrapper container">
            <h1 class="err404-page__main-title section-title highlighted-color visually-hidden">Страница не найдена</h1>
            <div class="err404-page__grid">
                <!-- Row 1 -->
                <span>1</span><span>0</span><span>1</span><span>0</span><span>1</span><span>1</span><span>0</span><span>1</span><span>0</span>
                <!-- Row 2 -->
                <span>1</span><span class="char-blue">4</span><span class="char-blue">0</span><span
                        class="char-blue">4</span><span>1</span><span>1</span><span>0</span><span>1</span><span>1</span>
                <!-- Row 3 -->
                <span>0</span><span>0</span><span>0</span><span>1</span><span>1</span><span>0</span><span>1</span><span>0</span><span>0</span>
                <!-- Row 4 -->
                <span class="char-black">с</span><span class="char-black">т</span><span class="char-black">р</span><span
                        class="char-black">а</span><span class="char-black">н</span><span class="char-black">и</span><span
                        class="char-black">ц</span><span class="char-black">а</span><span>1</span>
                <!-- Row 5 -->
                <span>1</span><span>0</span><span>0</span><span>1</span><span>1</span><span>0</span><span>0</span><span>0</span><span>0</span>
                <!-- Row 6 -->
                <span>1</span><span>1</span><span>1</span><span>0</span><span class="char-black">н</span><span
                        class="char-black">е</span><span>1</span><span>0</span><span>1</span>
                <!-- Row 7 -->
                <span>0</span><span class="char-black">н</span><span class="char-black">а</span><span
                        class="char-black">й</span><span class="char-black">д</span><span class="char-black">е</span><span
                        class="char-black">н</span><span class="char-black">а</span><span>0</span>
                <!-- Row 8 -->
                <span>1</span><span>1</span><span>0</span><span>0</span><span>1</span><span>1</span><span>0</span><span>0</span><span>1</span>
            </div>
            <div class="err404-page__note-wrapper">
                <p class="err404-page__message">Похоже, запрашиваемая вами страница не найдена</p>
                <a href="/" class="err404-page__link">
                    На главную
                    <svg class="safety-section__icon" width="36.000000" height="36.000000" viewBox="0 0 36 36"
                         fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect id="Btn1" rx="0.000000" width="35.250000" height="35.250000"
                              transform="translate(0.375000 0.375000)" fill="#FFFFFF" fill-opacity="0"></rect>
                        <g clip-path="url(#clip5129_113345)">
                            <circle id="Ellipse2" cx="18.000000" cy="18.000000" r="17.625000" stroke="#131315"
                                    stroke-opacity="1.000000" stroke-width="0.750000"></circle>
                            <path id="Vector8" d="M18.75 13.87L22.12 13.87L22.12 17.25M22.12 13.87L13.87 22.12"
                                  stroke="#131315" stroke-opacity="1.000000" stroke-width="0.750000"
                                  stroke-linejoin="round" stroke-linecap="round"></path>
                        </g>
                    </svg>
                </a>
            </div>
        </div>
    </main>


<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>