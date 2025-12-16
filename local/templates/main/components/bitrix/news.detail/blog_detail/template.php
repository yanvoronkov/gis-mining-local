<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

// Подключаем модуль информационных блоков
if (!CModule::IncludeModule("iblock")) {
    ShowError("Модуль информационных блоков не установлен");
    return;
}

// ==================== МИКРОРАЗМЕТКА ARTICLE ====================
// Готовим данные для микроразметки, выведем её в конце страницы перед </body>
$description = $arResult['PREVIEW_TEXT'] ?: strip_tags($arResult['DETAIL_TEXT']);

$articleData = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $arResult['NAME'],
    'image' => !empty($arResult['DETAIL_PICTURE']['SRC'])
        ? 'https://' . $_SERVER['SERVER_NAME'] . $arResult['DETAIL_PICTURE']['SRC']
        : null,
    'description' => $description,
    'author' => [
        '@type' => 'Person',
        'name' => $arResult['PROPERTIES']['AUTHOR']['VALUE'] ?: 'Редакция сайта',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $_SERVER['SERVER_NAME'],
        'url' => 'https://' . $_SERVER['SERVER_NAME'],
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://' . $_SERVER['SERVER_NAME'] . '/upload/logo.png'
        ]
    ],
    'datePublished' => !empty($arResult['ACTIVE_FROM'])
        ? date('c', MakeTimeStamp($arResult['ACTIVE_FROM']))
        : null,
    'dateModified' => !empty($arResult['TIMESTAMP_X'])
        ? date('c', MakeTimeStamp($arResult['TIMESTAMP_X']))
        : null,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => 'https://' . $_SERVER['SERVER_NAME'] . $APPLICATION->GetCurPage()
    ],
];


$articleData = array_filter($articleData);

// Регистрируем схему Article в универсальной системе JSON-LD
$schemas = $APPLICATION->GetPageProperty('json_ld_schemas') ?: [];
$schemas['Article'] = $articleData;
$APPLICATION->SetPageProperty('json_ld_schemas', $schemas);
// ===============================================================



// Получаем заголовки для навигации (h2, h3, h4)
$content = $arResult["DETAIL_TEXT"];
$headings = array();
if (!empty($content)) {
    preg_match_all('/<h([2-4])([^>]*)>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER);
    if (!empty($matches)) {
        foreach ($matches as $index => $match) {
            $level = $match[1];
            $attributes = $match[2];
            $text = strip_tags($match[3]);
            if (strpos($attributes, 'id=') === false) {
                $headings[] = array(
                    'level' => $level,
                    'text' => $text,
                    'id' => 'heading-' . ($index + 1),
                    'attributes' => $attributes,
                    'full_tag' => '<h' . $level . $attributes . '>'
                );
            }
        }
    }
}
?>

<div class="container">

    <!-- Заголовок блога -->
    <!--    <div class="page-blog-detail__header">-->
    <!--        <h2 class="page-blog-detail__title section-title highlighted-color">-->
    <!--            Блог-->
    <!--        </h2>-->
    <!--    </div>-->

    <div class="page-blog-detail__block">
        <div class="page-blog-detail__article">
            <!-- Шапка статьи -->
            <div class="page-blog-detail__article-header article-header">
                <h1 class="article-header__title"><?= $arResult["NAME"] ?></h1>
                <div class="article-header__tag-date-wrap">
                    <?php if (!empty($arResult["PROPERTIES"]["TAGS"]["VALUE"])): ?>
                        <div class="article-header__tag">
                            #<?= is_array($arResult["PROPERTIES"]["TAGS"]["VALUE"]) ? $arResult["PROPERTIES"]["TAGS"]["VALUE"][0] : $arResult["PROPERTIES"]["TAGS"]["VALUE"] ?>
                        </div>
                    <?php endif; ?>
                    <div class="article-header__date">
                        <?= FormatDate("d.m.Y", MakeTimeStamp($arResult["ACTIVE_FROM"])) ?>
                    </div>
                </div>
                <?php if (!empty($arResult["DETAIL_PICTURE"]["SRC"])): ?>
                    <div class="article-header__image-block main-image">
                        <img src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>" alt="<?= $arResult["NAME"] ?>"
                            class="article-header__image">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Навигация по статье -->
            <?php if (!empty($headings)): ?>
                <div class="page-blog-detail__navigation-items">
                    <div class="page-blog-detail__navigation-block">
                        <h2 class="page-blog-detail__navigation-title">Навигация по статье</h2>
                        <ul class="page-blog-detail__navigation-list">
                            <?php foreach ($headings as $index => $heading): ?>
                                <li>
                                    <a href="#<?= $heading['id'] ?>" class="page-blog-detail__navigation-link">
                                        <?= ($index + 1) ?>. <?= $heading['text'] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <a href="" class="page-blog-detail__share-btn btn btn-primary js-open-popup-form" data-form-type="share"
                data-metric-goal="share_article">
                Поделиться статьей
                <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_7485_14504)">
                        <path
                            d="M14.763 12.7802C13.8526 12.7802 13.0325 13.1734 12.4632 13.7987L7.34315 10.6276C7.47985 10.2777 7.55566 9.8976 7.55566 9.49998C7.55566 9.10221 7.47985 8.72213 7.34315 8.37235L12.4632 5.2011C13.0325 5.82645 13.8526 6.21972 14.763 6.21972C16.4778 6.21972 17.8729 4.82464 17.8729 3.10979C17.8729 1.39493 16.4778 0 14.763 0C13.0481 0 11.653 1.39508 11.653 3.10993C11.653 3.50755 11.729 3.88763 11.8656 4.23756L6.74563 7.40867C6.17638 6.78332 5.35621 6.39005 4.44587 6.39005C2.73101 6.39005 1.33594 7.78527 1.33594 9.49998C1.33594 11.2148 2.73101 12.6099 4.44587 12.6099C5.35621 12.6099 6.17638 12.2168 6.74563 11.5913L11.8656 14.7624C11.729 15.1123 11.653 15.4924 11.653 15.8902C11.653 17.6049 13.0481 19 14.763 19C16.4778 19 17.8729 17.6049 17.8729 15.8902C17.8729 14.1753 16.4778 12.7802 14.763 12.7802ZM12.7871 3.10993C12.7871 2.02043 13.6735 1.13401 14.763 1.13401C15.8525 1.13401 16.7389 2.02043 16.7389 3.10993C16.7389 4.19944 15.8525 5.08586 14.763 5.08586C13.6735 5.08586 12.7871 4.19944 12.7871 3.10993ZM4.44587 11.4759C3.35622 11.4759 2.4698 10.5895 2.4698 9.49998C2.4698 8.41047 3.35622 7.52405 4.44587 7.52405C5.53538 7.52405 6.42165 8.41047 6.42165 9.49998C6.42165 10.5895 5.53538 11.4759 4.44587 11.4759ZM12.7871 15.89C12.7871 14.8005 13.6735 13.9141 14.763 13.9141C15.8525 13.9141 16.7389 14.8005 16.7389 15.89C16.7389 16.9795 15.8525 17.8659 14.763 17.8659C13.6735 17.8659 12.7871 16.9795 12.7871 15.89Z"
                            fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_7485_14504">
                            <rect width="19" height="19" fill="white" transform="translate(0.109375)" />
                        </clipPath>
                    </defs>
                </svg>
            </a>

            <!-- Контент статьи из записи инфоблока -->
            <div class="page-blog-detail__content article-content">
                <div class="article-content__section">
                    <?php
                    if (!empty($headings)) {
                        $content = $arResult["DETAIL_TEXT"];
                        foreach ($headings as $heading) {
                            $searchTag = $heading['full_tag'];
                            $replaceTag = '<h' . $heading['level'] . $heading['attributes'] . ' id="' . $heading['id'] . '">';
                            $pos = strpos($content, $searchTag);
                            if ($pos !== false) {
                                $content = substr_replace($content, $replaceTag, $pos, strlen($searchTag));
                            }
                        }
                        echo $content;
                    } else {
                        echo $arResult["DETAIL_TEXT"];
                    }
                    ?>
                </div>
            </div>

            <!-- Контактная форма -->
            <div class="contact-form-section">
                <h2 class="contact-form-section__title">Свяжитесь с нами</h2>
                <form class="contact-form js-ajax-form" data-metric-goal="blog-contact-form">
                    <div class="contact-form__field">
                        <input type="text" name="client_name" placeholder="Имя" class="form-input" required>
                    </div>
                    <div class="contact-form__field">
                        <input type="tel" name="client_phone" placeholder="Телефон" class="form-input js-phone-mask"
                            required>
                    </div>
                    <div class="contact-form__field">
                        <textarea name="client_comment" placeholder="Комментарий (необязательно)" class="form__textarea"
                            rows="4"></textarea>
                    </div>

                    <!-- Скрытые поля -->
                    <input type="hidden" name="form_name" value="Контактная форма блога">
                    <input type="hidden" name="utm_source">
                    <input type="hidden" name="utm_medium">
                    <input type="hidden" name="utm_campaign">
                    <input type="hidden" name="utm_content">
                    <input type="hidden" name="utm_term">
                    <input type="hidden" name="source_id" value="25">
                    <input type="hidden" name="page_url" value="">

                    <button type="submit" class="btn btn-primary contact-form__submit">Отправить</button>
                    <div class="form-group form-check mb-3">
                        <input type="checkbox" id="privacy-policy-feedback" name="privacy-policy"
                            class="form-check-input" required="">
                        <label for="privacy-policy-feedback" class="form-check-label">Согласен(а) с <a
                                href="/policy-confidenciales/" target="_blank"><u>политикой
                                    конфиденциальности</u></a></label>
                    </div>
                    <!-- Сообщения об ошибке -->
                    <p class="form-error-message"></p>
                </form>

                <!-- Блок социальных сетей -->
                <div class="contact-options">
                    <h2 class="contact-options__title">
                        МОЖНО СВЯЗАТЬСЯ С НАМИ В УДОБНОМ<br>
                        МЕССЕНДЖЕРЕ, ОТВЕТИМ СРАЗУ
                    </h2>

                    <div class="contact-options__buttons">
                        <a id="wa-link"
                            href="https://api.whatsapp.com/send/?phone=%2B79311114298&amp;text=%D0%97%D0%B4%D1%80%D0%B0%D0%B2%D1%81%D1%82%D0%B2%D1%83%D0%B9%D1%82%D0%B5!%20%D0%9C%D0%BE%D0%B9%20%D0%BF%D1%80%D0%BE%D0%BC%D0%BE%D0%BA%D0%BE%D0%B4%3A%20174412120042717233%0A%D0%A5%D0%BE%D1%87%D1%83%20%D0%B7%D0%B0%D0%BA%D1%80%D0%B5%D0%BF%D0%B8%D1%82%D1%8C%20%D1%82%D0%B0%D1%80%D0%B8%D1%84%20%D0%B4%D0%BE%20%D0%BA%D0%BE%D0%BD%D1%86%D0%B0%20%D0%B3%D0%BE%D0%B4%D0%B0%20%D0%BD%D0%B0%20%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%89%D0%B5%D0%BD%D0%B8%D0%B5%20%D0%B0%D1%81%D0%B8%D0%BA%D0%BE%D0%B2.%0A%D0%9A%D0%B0%D0%BA%D0%B8%D0%B5%20%D1%83%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D1%8F%3F"
                            target="_blank" rel="nofollow" class="contact-btn whatsapp">
                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_blog_whatsapp)">
                                    <rect x="-3.125" y="-3.70117" width="32" height="32" fill="#18A53A"></rect>
                                    <path
                                        d="M15.7974 13.2084C15.7737 13.197 14.8853 12.7595 14.7274 12.7027C14.6629 12.6795 14.5939 12.6569 14.5204 12.6569C14.4005 12.6569 14.2997 12.7167 14.2212 12.8342C14.1324 12.9661 13.8638 13.2801 13.7808 13.3739C13.7699 13.3863 13.7551 13.4011 13.7463 13.4011C13.7383 13.4011 13.6008 13.3445 13.5592 13.3264C12.6061 12.9124 11.8827 11.9169 11.7835 11.749C11.7694 11.7249 11.7688 11.7139 11.7686 11.7139C11.7721 11.7011 11.8042 11.669 11.8207 11.6524C11.8691 11.6045 11.9215 11.5414 11.9723 11.4804C11.9963 11.4514 12.0203 11.4225 12.044 11.3952C12.1175 11.3096 12.1503 11.2431 12.1883 11.1661L12.2082 11.1261C12.3009 10.9418 12.2217 10.7863 12.1961 10.7361C12.1751 10.6941 11.8 9.7887 11.7601 9.69358C11.6642 9.46405 11.5374 9.35718 11.3613 9.35718C11.345 9.35718 11.3613 9.35718 11.2928 9.36007C11.2094 9.36359 10.7549 9.42341 10.554 9.55007C10.3409 9.6844 9.98047 10.1126 9.98047 10.8656C9.98047 11.5434 10.4106 12.1833 10.5952 12.4267C10.5998 12.4328 10.6082 12.4453 10.6205 12.4632C11.3277 13.496 12.2093 14.2614 13.103 14.6184C13.9634 14.9621 14.3708 15.0018 14.6024 15.0018H14.6025C14.6998 15.0018 14.7777 14.9942 14.8464 14.9874L14.89 14.9832C15.1873 14.9569 15.8404 14.6184 15.989 14.2055C16.1061 13.8803 16.1369 13.525 16.0591 13.3961C16.0057 13.3084 15.9138 13.2643 15.7974 13.2084Z"
                                        fill="white"></path>
                                    <path
                                        d="M12.9828 6.20557C9.68225 6.20557 6.99704 8.87059 6.99704 12.1463C6.99704 13.2058 7.28058 14.2429 7.81771 15.1506L6.78963 18.1833C6.77048 18.2398 6.78472 18.3023 6.82655 18.3449C6.85674 18.3757 6.89769 18.3923 6.93951 18.3923C6.95554 18.3923 6.97168 18.3899 6.98743 18.3849L10.1497 17.38C11.015 17.8424 11.993 18.0864 12.9828 18.0864C16.2831 18.0865 18.968 15.4217 18.968 12.1463C18.968 8.87059 16.2831 6.20557 12.9828 6.20557ZM12.9828 16.849C12.0514 16.849 11.1493 16.58 10.3739 16.0712C10.3478 16.0541 10.3175 16.0453 10.2871 16.0453C10.271 16.0453 10.2548 16.0477 10.2391 16.0527L8.65504 16.5563L9.16641 15.0476C9.18295 14.9988 9.17468 14.9449 9.14421 14.9033C8.5537 14.0964 8.24156 13.1431 8.24156 12.1463C8.24156 9.55297 10.3685 7.44308 12.9828 7.44308C15.5968 7.44308 17.7234 9.55297 17.7234 12.1463C17.7235 14.7394 15.5968 16.849 12.9828 16.849Z"
                                        fill="white"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_blog_whatsapp">
                                        <rect width="24" height="24" rx="7" transform="matrix(1 0 0 -1 0.875 24.2988)"
                                            fill="white"></rect>
                                    </clipPath>
                                </defs>
                            </svg>
                            Написать в WhatsApp
                        </a>
                        <a id="tg-link"
                            href="https://t.me/gismining_official?text=%D0%97%D0%B4%D1%80%D0%B0%D0%B2%D1%81%D1%82%D0%B2%D1%83%D0%B9%D1%82%D0%B5!%20%D0%9C%D0%BE%D0%B9%20%D0%BF%D1%80%D0%BE%D0%BC%D0%BE%D0%BA%D0%BE%D0%B4%3A%20174412120042717233%0A%D0%A5%D0%BE%D1%87%D1%83%20%D0%B7%D0%B0%D0%BA%D1%80%D0%B5%D0%BF%D0%B8%D1%82%D1%8C%20%D1%82%D0%B0%D1%80%D0%B8%D1%84%20%D0%B4%D0%BE%20%D0%BA%D0%BE%D0%BD%D1%86%D0%B0%20%D0%B3%D0%BE%D0%B4%D0%B0%20%D0%BD%D0%B0%20%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%89%D0%B5%D0%BD%D0%B8%D0%B5%20%D0%B0%D1%81%D0%B8%D0%BA%D0%BE%D0%B2.%0A%D0%9A%D0%B0%D0%BA%D0%B8%D0%B5%20%D1%83%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D1%8F%3F"
                            target="_blank" rel="nofollow" class="contact-btn telegram">
                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect x="0.875" y="0.298828" width="24" height="24" rx="7" fill="#0093DE"></rect>
                                <ellipse cx="12.8749" cy="12.2988" rx="11.9999" ry="12" fill="#0093DE"></ellipse>
                                <path
                                    d="M16.0224 7.38711C16.0224 7.38711 16.9474 6.95426 16.8703 8.00547C16.8446 8.43833 16.6134 9.95331 16.4335 11.592L15.8168 16.4461C15.8168 16.4461 15.7654 17.1572 15.3029 17.2809C14.8403 17.4046 14.1466 16.8481 14.0181 16.7244C13.9153 16.6316 12.0909 15.2403 11.4484 14.5601C11.2686 14.3746 11.063 14.0036 11.4741 13.5707L14.1722 10.4789C14.4806 10.1079 14.7889 9.24221 13.5041 10.2934L9.90666 13.2307C9.90666 13.2307 9.49552 13.5398 8.72465 13.2616L7.05438 12.6432C7.05438 12.6432 6.43767 12.1794 7.49122 11.7156C10.0609 10.2625 13.2215 8.77841 16.0224 7.38711Z"
                                    fill="white"></path>
                            </svg>
                            Написать в Telegram
                        </a>
                    </div>

                    <div class="contact-options__phones">
                        <p>Или позвоните по номеру:</p>
                        <a href="tel:+74958434743">+7 (495) 843-47-43</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Похожие статьи - СТАРЫЙ КОД (ЗАКОММЕНТИРОВАН) -->
        <?php
        /*
        $similarArticles = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array(
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "ACTIVE" => "Y",
                "!ID" => $arResult["ID"]
            ),
            false,
            array("nTopCount" => 4),
            array("ID", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "ACTIVE_FROM", "CODE", "PROPERTY_TAGS")
        );

        if ($similarArticles->SelectedRowsCount() > 0):
            ?>
            <div class="page-blog-detail__similar-articles">
                <h3 class="page-blog-detail__similar-title">Читайте также</h3>
                <div class="page-blog-detail__similar-grid">
                    <?php while ($similar = $similarArticles->GetNext()): ?>
                        <a href="/our-blog/<?= $similar["CODE"] ?>/" class="page-blog-detail__similar-card-link">
                            <div class="page-blog__card card-blog">
                                <div class="card-blog__image-wrap">
                                    <?php if (!empty($similar["PREVIEW_PICTURE"])): ?>
                                        <img src="<?= CFile::GetPath($similar["PREVIEW_PICTURE"]) ?>"
                                             alt="<?= $similar["NAME"] ?>"
                                             class="card-blog__image">
                                    <?php endif; ?>
                                </div>

                                <div class="card-blog__content">
                                    <div class="card-blog__tag-date-wrap">
                                        <?php if (!empty($similar["PROPERTY_TAGS_VALUE"])): ?>
                                            <div class="card-blog__tag">
                                                #<?= is_array($similar["PROPERTY_TAGS_VALUE"]) ? $similar["PROPERTY_TAGS_VALUE"][0] : $similar["PROPERTY_TAGS_VALUE"] ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-blog__date">
                                            <?= FormatDate("d.m.Y", MakeTimeStamp($similar["ACTIVE_FROM"])) ?>
                                        </div>
                                    </div>
                                    <h3 class="card-blog__title">
                                        <?= $similar["NAME"] ?>
                                    </h3>
                                    <?php if (!empty($similar["PREVIEW_TEXT"])): ?>
                                        <div class="card-blog__excerpt">
                                            <?= $similar["PREVIEW_TEXT"] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif;
        */
        ?>
    </div>

    <!-- Просмотренные статьи - ЗАКОММЕНТИРОВАН -->
    <?php
    /*
    $APPLICATION->IncludeComponent(
        "custom:viewed.articles",
        "",
        array(
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "CURRENT_ARTICLE_ID" => $arResult["ID"],
            "SECTION_TYPE" => "blog",
            "BASE_URL" => "/our-blog/",
            "CURRENT_ARTICLE_DATA" => array(
                "ID" => $arResult["ID"],
                "NAME" => $arResult["NAME"],
                "CODE" => $arResult["CODE"],
                "PREVIEW_TEXT" => $arResult["PREVIEW_TEXT"],
                "PREVIEW_PICTURE" => $arResult["PREVIEW_PICTURE"],
                "ACTIVE_FROM" => $arResult["ACTIVE_FROM"],
                "PROPERTY_TAGS_VALUE" => $arResult["PROPERTIES"]["TAGS"]["VALUE"] ?? array()
            )
        ),
        false
    );
    */
    ?>

    <!-- Читайте также - НОВЫЙ КОМПОНЕНТ -->
    <?php
    $APPLICATION->IncludeComponent(
        "custom:similar.articles",
        "",
        array(
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "CURRENT_ARTICLE_ID" => $arResult["ID"],
            "BASE_URL" => "/our-blog/",
            "LIMIT" => 5
        ),
        false
    );
    ?>

    <!-- Фильтр по разделам -->
    <div class="page-blog-detail__filter">
        <h2 class="page-blog-detail__filter-title">Читайте также</h2>
        <div class="page-blog-detail__container">
            <div class="page-blog-detail__filter-buttons">
                <?php
                $sections = CIBlockSection::GetList(
                    array("SORT" => "ASC"),
                    array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y"),
                    false,
                    array("ID", "NAME", "CODE")
                );
                if ($sections) {
                    while ($section = $sections->GetNext()):
                        ?>
                        <a href="/our-blog/?SECTION_ID=<?= $section["ID"] ?>" class="page-blog-detail__filter-btn">
                            <?= $section["NAME"] ?>
                        </a>
                        <?php
                    endwhile;
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Попап Поделиться статьей -->
    <div class="share-popup form-popup popup-form-wrapper" id="sharePopupFormWrapper" role="dialog" aria-modal="true"
        aria-labelledby="share-popup-title" hidden>

        <!-- Полупрозрачная подложка -->
        <div class="share-popup__overlay"></div>

        <!-- Контент -->
        <div class="share-popup__content">

            <!-- Кнопка "Закрыть" -->
            <button class="share-popup__close btn" id="closeSharePopupFormBtn" type="button" aria-label="Закрыть">
                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.1094 6L6.10938 18" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M6.10938 6L18.1094 18" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <!-- Заголовок для скринридеров -->
            <h2 class="share-popup__title visually-hidden" id="share-popup-title">Поделиться ссылкой</h2>

            <!-- Соцсети -->
            <ul class="share-popup__social-list">
                <li class="share-popup__social-item">
                    <a href="https://t.me/share/url?url=<?= urlencode('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($arResult["NAME"]) ?>"
                        target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                        <div class="share-popup__icon-wrapper">
                            <!-- Место для иконки Telegram -->
                            <span class="share-popup__icon icon-telegram" aria-hidden="true">
                                <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_3286_53479)">
                                        <path
                                            d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                            fill="url(#paint0_linear_3286_53479)" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M14.5922 31.6619C23.9208 27.5975 30.1414 24.918 33.2538 23.6235C42.1406 19.9273 43.9872 19.2851 45.1907 19.2639C45.4554 19.2593 46.0473 19.3249 46.4307 19.636C46.9994 20.0975 47.0027 21.0993 46.9396 21.7623C46.4579 26.8223 44.3742 39.1014 43.3141 44.7686C42.8654 47.1666 41.9822 47.9708 41.1272 48.0494C39.2689 48.2204 37.8578 46.8213 36.0579 45.6415C33.2415 43.7953 31.6504 42.646 28.9166 40.8445C25.7572 38.7625 27.8053 37.6183 29.6058 35.7481C30.0771 35.2588 38.2647 27.8114 38.4232 27.1358C38.4431 27.0513 38.4614 26.7363 38.2743 26.57C38.0872 26.4038 37.811 26.4605 37.6118 26.5058C37.3293 26.5699 32.83 29.5438 24.114 35.4273C22.8369 36.3043 21.6802 36.7315 20.6438 36.7091C19.5012 36.6845 17.3034 36.0631 15.6696 35.532C13.6656 34.8806 12.0728 34.5361 12.2116 33.4299C12.2838 32.8535 13.0773 32.2643 14.5922 31.6619Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <linearGradient id="paint0_linear_3286_53479" x1="32.1094" y1="0" x2="32.1094"
                                            y2="63.5" gradientUnits="userSpaceOnUse">
                                            <stop offset="0" stop-color="#2AABEE" />
                                        </linearGradient>
                                        <clipPath id="clip0_3286_53479">
                                            <rect width="64" height="64" fill="white" transform="translate(0.109375)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                        </div>
                        <span class="share-popup__label">Telegram</span>
                    </a>
                </li>
                <li class="share-popup__social-item">
                    <a href="https://wa.me/?text=<?= urlencode($arResult["NAME"] . ' - https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>"
                        target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                        <div class="share-popup__icon-wrapper">
                            <!-- Место для иконки WhatsApp -->
                            <span class="share-popup__icon icon-whatsapp" aria-hidden="true">
                                <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_3286_53485)">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M32.6094 0C50.2831 0 64.6094 14.3263 64.6094 32C64.6094 49.6738 50.2831 64 32.6094 64C14.9356 64 0.609375 49.6738 0.609375 32C0.609375 14.3263 14.9356 0 32.6094 0Z"
                                            fill="url(#paint0_linear_3286_53485)" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M32.6101 46.7278C30.0038 46.7278 27.4388 46.034 25.1951 44.7228C24.8338 44.5115 24.3988 44.4553 23.9951 44.5653L19.1163 45.904L20.8163 42.1603C21.0476 41.6515 20.9876 41.0578 20.6613 40.604C18.8426 38.0853 17.8826 35.109 17.8826 32.0003C17.8826 23.879 24.4888 17.2728 32.6101 17.2728C40.7313 17.2728 47.3376 23.879 47.3376 32.0003C47.3376 40.1215 40.7313 46.7278 32.6101 46.7278ZM32.6101 14.1553C22.7701 14.1553 14.7663 22.1603 14.7663 32.0003C14.7663 35.4615 15.7476 38.7853 17.6163 41.6703L14.9051 47.6415C14.6551 48.1928 14.7463 48.8378 15.1376 49.2978C15.4388 49.649 15.8751 49.8453 16.3238 49.8453C17.3288 49.8453 22.8101 48.1228 24.2051 47.739C26.7838 49.119 29.6751 49.8453 32.6101 49.8453C42.4488 49.8453 50.4551 41.839 50.4551 32.0003C50.4551 22.1603 42.4488 14.1565 32.6101 14.1553Z"
                                            fill="white" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M36.8758 34.0869C36.2008 34.3631 35.7696 35.4206 35.3321 35.9606C35.1071 36.2369 34.8396 36.2806 34.4946 36.1419C31.9596 35.1319 30.0158 33.4394 28.6171 31.1069C28.3796 30.7456 28.4221 30.4594 28.7083 30.1231C29.1308 29.6256 29.6621 29.0606 29.7758 28.3894C30.0296 26.9069 28.0908 22.3056 25.5296 24.3906C18.1608 30.3956 37.8221 46.3219 41.3708 37.7081C42.3746 35.2656 37.9958 33.6281 36.8758 34.0869Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <linearGradient id="paint0_linear_3286_53485" x1="32.6094" y1="64" x2="35.6094"
                                            y2="4.46104e-07" gradientUnits="userSpaceOnUse">
                                            <stop offset="1" stop-color="#60D66A" />
                                        </linearGradient>
                                        <clipPath id="clip0_3286_53485">
                                            <rect width="64" height="64" fill="white" transform="translate(0.609375)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                        </div>
                        <span class="share-popup__label">WhatsApp</span>
                    </a>
                </li>
                <li class="share-popup__social-item">
                    <a href="https://vk.com/share.php?url=<?= urlencode('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($arResult["NAME"]) ?>"
                        target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                        <div class="share-popup__icon-wrapper">
                            <!-- Место для иконки VK -->
                            <span class="share-popup__icon icon-vk" aria-hidden="true">
                                <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                        fill="url(#paint0_linear_3286_53495)" />
                                    <path
                                        d="M50.551 44.5234C49.6201 44.6543 45.0674 44.5234 44.831 44.5234C43.6541 44.5384 42.5187 44.089 41.671 43.2725C40.711 42.3488 39.8528 41.3306 38.9147 40.3634C38.6372 40.0697 38.3369 39.7986 38.0165 39.5525C37.2674 38.9743 36.5256 39.1052 36.1728 39.9888C35.8973 40.9437 35.705 41.9207 35.5983 42.9088C35.5365 43.8034 34.9692 44.3634 33.9656 44.4252C33.3438 44.4579 32.7219 44.4688 32.1074 44.4543C29.8677 44.44 27.6633 43.8954 25.6747 42.8652C23.5229 41.6662 21.6482 40.0269 20.1728 38.0543C17.7474 34.9561 15.8092 31.5525 14.0783 28.0434C13.9874 27.8688 12.2128 24.0943 12.1692 23.9161C12.0092 23.3197 12.1692 22.7488 12.6601 22.5525C12.9728 22.4325 18.8201 22.5525 18.9183 22.5525C19.353 22.5487 19.7776 22.6834 20.1307 22.9371C20.4837 23.1907 20.7468 23.5502 20.8819 23.9634C21.9629 26.7311 23.4428 29.3258 25.2747 31.6652C25.531 31.978 25.8311 32.2523 26.1656 32.4797C26.6419 32.8034 27.0928 32.6906 27.2892 32.1416C27.5219 31.3797 27.6587 30.5918 27.6965 29.7961C27.7292 28.2179 27.6965 27.1779 27.6092 25.6034C27.551 24.5925 27.1947 23.7052 25.7038 23.4216C25.2456 23.3416 25.2056 22.9634 25.5001 22.5852C26.1147 21.7997 26.9547 21.6761 27.8965 21.6288C29.3001 21.5488 30.7038 21.6034 32.1074 21.6288H32.4128C33.0237 21.6266 33.6332 21.6888 34.231 21.8143C34.5844 21.8858 34.9053 22.0695 35.1458 22.3382C35.3863 22.6069 35.5335 22.946 35.5656 23.3052C35.6374 23.7034 35.6679 24.108 35.6565 24.5125C35.6201 26.2361 35.5365 27.9561 35.5183 29.6797C35.5015 30.3622 35.5638 31.0443 35.7038 31.7125C35.9038 32.6288 36.5328 32.8579 37.1583 32.1961C37.9682 31.3405 38.711 30.4239 39.3801 29.4543C40.5633 27.6872 41.5394 25.7898 42.2892 23.7997C42.6965 22.7779 43.0165 22.5525 44.1074 22.5525H50.311C50.6799 22.546 51.0476 22.595 51.4019 22.6979C51.5273 22.7309 51.6446 22.7894 51.7463 22.8697C51.8481 22.9501 51.9321 23.0506 51.9932 23.165C52.0543 23.2793 52.0912 23.4051 52.1014 23.5343C52.1117 23.6636 52.0951 23.7935 52.0528 23.9161C51.7474 25.2761 51.0128 26.4361 50.2347 27.5525C48.9728 29.3416 47.6419 31.0834 46.351 32.8434C46.1878 33.0768 46.0384 33.3196 45.9038 33.5706C45.4165 34.447 45.4528 34.9379 46.1583 35.6688C47.2856 36.8252 48.4892 37.9125 49.5765 39.1052C50.3748 39.9723 51.0952 40.908 51.7292 41.9016C52.5256 43.1343 52.0347 44.3125 50.551 44.5234Z"
                                        fill="white" />
                                    <defs>
                                        <linearGradient id="paint0_linear_3286_53495" x1="32.1094" y1="2.29091"
                                            x2="32.1094" y2="57.7491" gradientUnits="userSpaceOnUse">
                                            <stop offset="0" stop-color="#4B7AFF" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </span>
                        </div>
                        <span class="share-popup__label">VK</span>
                    </a>
                </li>
                <li class="share-popup__social-item">
                    <a href="https://connect.ok.ru/offer?url=<?= urlencode('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($arResult["NAME"]) ?>"
                        target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                        <div class="share-popup__icon-wrapper">
                            <!-- Место для иконки OK -->
                            <span class="share-popup__icon icon-ok" aria-hidden="true">
                                <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                        fill="url(#paint0_linear_3286_53502)" />
                                    <path
                                        d="M32.1062 15.1582C30.4257 15.1582 28.7829 15.6565 27.3856 16.5902C25.9883 17.5238 24.8993 18.8509 24.2562 20.4034C23.6131 21.956 23.4448 23.6645 23.7726 25.3127C24.1005 26.9609 24.9097 28.4749 26.098 29.6632C27.2864 30.8515 28.8003 31.6608 30.4486 31.9886C32.0968 32.3165 33.8052 32.1482 35.3578 31.5051C36.9104 30.862 38.2374 29.7729 39.1711 28.3756C40.1047 26.9783 40.6031 25.3356 40.6031 23.655C40.6036 22.5391 40.3842 21.4339 39.9574 20.4028C39.5306 19.3717 38.9047 18.4348 38.1156 17.6456C37.3265 16.8565 36.3896 16.2307 35.3585 15.8039C34.3273 15.3771 33.2222 15.1577 32.1062 15.1582ZM32.1062 27.8908C31.2685 27.8908 30.4495 27.6424 29.7529 27.177C29.0564 26.7115 28.5135 26.05 28.1929 25.276C27.8723 24.502 27.7884 23.6503 27.9518 22.8287C28.1153 22.007 28.5187 21.2523 29.1111 20.6599C29.7035 20.0675 30.4582 19.6641 31.2799 19.5006C32.1015 19.3372 32.9532 19.4211 33.7272 19.7417C34.5012 20.0623 35.1627 20.6052 35.6282 21.3018C36.0936 21.9983 36.342 22.8173 36.342 23.655C36.3426 24.2115 36.2334 24.7625 36.0207 25.2767C35.808 25.7908 35.496 26.258 35.1026 26.6514C34.7092 27.0449 34.242 27.3569 33.7278 27.5695C33.2137 27.7822 32.6626 27.8914 32.1062 27.8908Z"
                                        fill="white" />
                                    <path
                                        d="M44.6271 33.4532C44.4421 33.1229 44.1937 32.8323 43.8963 32.598C43.5989 32.3637 43.2582 32.1904 42.8937 32.0879C42.5292 31.9855 42.1481 31.9559 41.7722 32.0009C41.3963 32.0458 41.0329 32.1645 40.7029 32.35C34.9176 35.5922 29.2923 35.5922 23.5071 32.35C23.1753 32.1648 22.8103 32.0467 22.4329 32.0026C22.0555 31.9584 21.6731 31.989 21.3076 32.0926C20.942 32.1962 20.6004 32.3708 20.3023 32.6064C20.0042 32.842 19.7555 33.1341 19.5702 33.4658C19.385 33.7976 19.2669 34.1626 19.2227 34.54C19.1786 34.9174 19.2092 35.2998 19.3128 35.6653C19.522 36.4036 20.016 37.0286 20.686 37.4027C22.5839 38.4798 24.6115 39.3104 26.7197 39.8743L22.6271 43.9669C22.0894 44.5046 21.7874 45.2339 21.7874 45.9943C21.7874 46.7546 22.0894 47.4839 22.6271 48.0216C23.1648 48.5593 23.894 48.8613 24.6544 48.8613C25.4148 48.8613 26.1441 48.5593 26.6818 48.0216L32.105 42.5774L37.5281 48.0006C38.0658 48.5382 38.7951 48.8402 39.5555 48.8402C40.3159 48.8402 41.0451 48.5382 41.5829 48.0006C42.1205 47.4628 42.4225 46.7336 42.4225 45.9732C42.4225 45.2128 42.1205 44.4836 41.5829 43.9458L37.4902 39.8532C39.5984 39.2893 41.626 38.4588 43.5239 37.3816C43.8548 37.1966 44.1459 36.9481 44.3805 36.6504C44.6152 36.3526 44.7887 36.0115 44.8912 35.6465C44.9937 35.2815 45.0231 34.8999 44.9778 34.5235C44.9325 34.1471 44.8133 33.7834 44.6271 33.4532Z"
                                        fill="white" />
                                    <defs>
                                        <linearGradient id="paint0_linear_3286_53502" x1="32.1094" y1="0" x2="32.1094"
                                            y2="64" gradientUnits="userSpaceOnUse">
                                            <stop offset="0" stop-color="#FF9800" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </span>
                        </div>
                        <span class="share-popup__label">OK</span>
                    </a>
                </li>
                <li class="share-popup__social-item">
                    <a href="mailto:?subject=<?= urlencode($arResult["NAME"]) ?>&body=<?= urlencode('Поделился статьей: ' . $arResult["NAME"] . ' - https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>"
                        class="share-popup__social-button">
                        <div class="share-popup__icon-wrapper">
                            <!-- Место для иконки E-mail -->
                            <span class="share-popup__icon icon-email" aria-hidden="true">
                                <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                        fill="#2196F3" />
                                    <path
                                        d="M32.1019 33.5803L15.2344 25.1653V41.8853C15.2367 42.7329 15.5744 43.5452 16.1738 44.1446C16.7732 44.744 17.5855 45.0817 18.4331 45.084H45.7856C46.6333 45.0817 47.4456 44.744 48.0449 44.1446C48.6443 43.5452 48.9821 42.7329 48.9844 41.8853V25.3415L32.1019 33.5803Z"
                                        fill="white" />
                                    <path
                                        d="M32.1169 30.42L48.9844 22.1875V22.115C48.9821 21.2674 48.6443 20.4551 48.0449 19.8557C47.4456 19.2563 46.6333 18.9186 45.7856 18.9163H18.4331C17.605 18.9179 16.8095 19.2398 16.2133 19.8146C15.6171 20.3894 15.2663 21.1725 15.2344 22L32.1169 30.42Z"
                                        fill="white" />
                                </svg>

                            </span>
                        </div>
                        <span class="share-popup__label">E-mail</span>
                    </a>
                </li>
            </ul>

            <!-- Копирование ссылки -->
            <div class="share-popup__copy-section">
                <div class="share-popup__copy-input-wrapper">
                    <input class="share-popup__copy-input" type="text"
                        value="https://<?= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ?>" readonly
                        aria-label="Ссылка для копирования">
                </div>
                <button class="share-popup__copy-button btn-primary btn" type="button">
                    <span class="share-popup__copy-button-text">Скопировать ссылку</span>
                </button>
                <div class="toast-notification" role="status" aria-live="polite">
                    <div class="toast-notification__icon-wrapper">
                        <span class="toast-notification__icon icon-success" aria-hidden="true"></span>
                    </div>
                    <p class="toast-notification__text">Ссылка скопирована</p>
                    <button class="toast-notification__close btn" type="button"
                        aria-label="Закрыть уведомление"></button>
                </div>
            </div>
        </div>
    </div>

</div>