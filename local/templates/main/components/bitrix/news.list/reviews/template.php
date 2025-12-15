<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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

// Подключаем CSS стили шаблона
$this->addExternalCss($templateFolder . "/style.css");

// Подключаем модуль информационных блоков
if (!CModule::IncludeModule("iblock")) {
    ShowError("Модуль информационных блоков не установлен");
    return;
}

// Получаем список разделов для вкладок
$reviewsIblockId = $arParams["IBLOCK_ID"];
$arSections = array();

$rsSections = CIBlockSection::GetList(
    array("SORT" => "ASC", "NAME" => "ASC"),
    array(
        "IBLOCK_ID" => $reviewsIblockId,
        "ACTIVE" => "Y",
        "GLOBAL_ACTIVE" => "Y"
    ),
    false,
    array("ID", "NAME", "CODE", "PICTURE", "SORT")
);

while ($arSection = $rsSections->GetNext()) {
    $arSections[] = $arSection;
}

// Карта сортировки разделов по ID
$sectionSortById = array();
foreach ($arSections as $sec) {
    $sectionSortById[(int)$sec['ID']] = (int)$sec['SORT'];
}

// Определяем текущий раздел
$currentSectionCode = !empty($_GET["SECTION_CODE"]) ? $_GET["SECTION_CODE"] : "";
?>

<div class="container">
    <!-- Заголовок страницы -->
    <div class="page-reviews__header section-title">
        <h1 class="page-reviews__title section-title">
            <span class="highlighted-color">Отзывы</span> о компании
        </h1>
    </div>

    <div class="page-reviews__grid">

        <?php if (!empty($arSections)): ?>
            <!-- Вкладки источников отзывов -->
            <aside class="page-reviews__filter">
                <div class="page-reviews__container">
                    <div class="page-reviews__filter-buttons">
                        <?php foreach ($arSections as $section):
                            $isActive = ($currentSectionCode === $section["CODE"]);
                            $sectionUrl = "/reviews/?SECTION_CODE=" . $section["CODE"];
                            ?>
                            <a href="<?= $sectionUrl ?>"
                               class="page-reviews__filter-btn <?= $isActive ? 'active' : '' ?>">
                                <div class="page-reviews__logo-wrap">
                                    <?php if (!empty($section["PICTURE"])):
                                        $pictureSrc = CFile::GetPath($section["PICTURE"]);
                                        ?>
                                        <img src="<?= $pictureSrc ?>" alt="<?= $section["NAME"] ?>"
                                             class="page-reviews__filter-icon">
                                    <?php endif; ?>
                                </div>
                                <?= $section["NAME"] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        <?php endif; ?>

        <?php if (!empty($arResult["ITEMS"])): ?>
            <!-- Список отзывов -->
            <div class="page-reviews__list">
                <?php
                // Сортируем элементы: сперва по SORT раздела, затем по SORT элемента
                $itemsSorted = $arResult["ITEMS"];
                usort($itemsSorted, function ($a, $b) use ($sectionSortById) {
                    $aSectionSort = $sectionSortById[(int)($a['IBLOCK_SECTION_ID'] ?? 0)] ?? PHP_INT_MAX;
                    $bSectionSort = $sectionSortById[(int)($b['IBLOCK_SECTION_ID'] ?? 0)] ?? PHP_INT_MAX;
                    if ($aSectionSort === $bSectionSort) {
                        $aSort = (int)($a['SORT'] ?? 500);
                        $bSort = (int)($b['SORT'] ?? 500);
                        if ($aSort === $bSort) {
                            return (int)($a['ID'] ?? 0) <=> (int)($b['ID'] ?? 0);
                        }
                        return $aSort <=> $bSort;
                    }
                    return $aSectionSort <=> $bSectionSort;
                });

                foreach ($itemsSorted as $item):
                    // Получаем дату отзыва
                    $reviewDate = "";
                    if (!empty($item["PROPERTIES"]["REVIEW_DATE"]["VALUE"])) {
                        $reviewDate = FormatDate("d.m.Y", MakeTimeStamp($item["PROPERTIES"]["REVIEW_DATE"]["VALUE"]));
                    }

                    // URL источника
                    $sourceUrl = !empty($item["PROPERTIES"]["SOURCE_URL"]["VALUE"]) ? $item["PROPERTIES"]["SOURCE_URL"]["VALUE"] : "";

                    // Получаем название раздела
                    $sectionName = "";
                    if (!empty($item["IBLOCK_SECTION_ID"])) {
                        $resSection = CIBlockSection::GetByID($item["IBLOCK_SECTION_ID"]);
                        if ($arSection = $resSection->GetNext()) {
                            $sectionName = $arSection["NAME"];
                        }
                    }
                    ?>
                    <div class="page-reviews__item review-card">
                        <div class="review-card__image-block">
                            <?php if (!empty($item["PREVIEW_PICTURE"]["SRC"])): ?>

                                <img src="<?= $item["PREVIEW_PICTURE"]["SRC"] ?>"
                                     alt="Скриншот отзыва <?= htmlspecialchars($item["NAME"]) ?>"
                                     class="review-card__image">

                            <?php endif; ?>
                        </div>

                        <div class="review-card__content-block">
                            <div class="review-card__header">
                                <div class="review-card__author"><?= $item["NAME"] ?></div>
                                <?php if (!empty($reviewDate)): ?>
                                    <span class="review-card__date"><?= $reviewDate ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($item["PREVIEW_TEXT"])): ?>
                                <div class="review-card__text">
                                    <?= $item["PREVIEW_TEXT"] ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($sourceUrl)): ?>
                                <a href="<?= htmlspecialcharsbx($sourceUrl) ?>"
                                   target="_blank"
                                   rel="nofollow ugc noopener noreferrer"
                                   class="review-card__link">
                                    Открыть отзыв на <?= !empty($sectionName) ? $sectionName : "источнике" ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
                                <g clip-path="url(#clip0_news_whatsapp)">
                                    <rect x="-3.125" y="-3.70117" width="32" height="32" fill="#18A53A"></rect>
                                    <path d="M15.7974 13.2084C15.7737 13.197 14.8853 12.7595 14.7274 12.7027C14.6629 12.6795 14.5939 12.6569 14.5204 12.6569C14.4005 12.6569 14.2997 12.7167 14.2212 12.8342C14.1324 12.9661 13.8638 13.2801 13.7808 13.3739C13.7699 13.3863 13.7551 13.4011 13.7463 13.4011C13.7383 13.4011 13.6008 13.3445 13.5592 13.3264C12.6061 12.9124 11.8827 11.9169 11.7835 11.749C11.7694 11.7249 11.7688 11.7139 11.7686 11.7139C11.7721 11.7011 11.8042 11.669 11.8207 11.6524C11.8691 11.6045 11.9215 11.5414 11.9723 11.4804C11.9963 11.4514 12.0203 11.4225 12.044 11.3952C12.1175 11.3096 12.1503 11.2431 12.1883 11.1661L12.2082 11.1261C12.3009 10.9418 12.2217 10.7863 12.1961 10.7361C12.1751 10.6941 11.8 9.7887 11.7601 9.69358C11.6642 9.46405 11.5374 9.35718 11.3613 9.35718C11.345 9.35718 11.3613 9.35718 11.2928 9.36007C11.2094 9.36359 10.7549 9.42341 10.554 9.55007C10.3409 9.6844 9.98047 10.1126 9.98047 10.8656C9.98047 11.5434 10.4106 12.1833 10.5952 12.4267C10.5998 12.4328 10.6082 12.4453 10.6205 12.4632C11.3277 13.496 12.2093 14.2614 13.103 14.6184C13.9634 14.9621 14.3708 15.0018 14.6024 15.0018H14.6025C14.6998 15.0018 14.7777 14.9942 14.8464 14.9874L14.89 14.9832C15.1873 14.9569 15.8404 14.6184 15.989 14.2055C16.1061 13.8803 16.1369 13.525 16.0591 13.3961C16.0057 13.3084 15.9138 13.2643 15.7974 13.2084Z"
                                          fill="white"></path>
                                    <path d="M12.9828 6.20557C9.68225 6.20557 6.99704 8.87059 6.99704 12.1463C6.99704 13.2058 7.28058 14.2429 7.81771 15.1506L6.78963 18.1833C6.77048 18.2398 6.78472 18.3023 6.82655 18.3449C6.85674 18.3757 6.89769 18.3923 6.93951 18.3923C6.95554 18.3923 6.97168 18.3899 6.98743 18.3849L10.1497 17.38C11.015 17.8424 11.993 18.0864 12.9828 18.0864C16.2831 18.0865 18.968 15.4217 18.968 12.1463C18.968 8.87059 16.2831 6.20557 12.9828 6.20557ZM12.9828 16.849C12.0514 16.849 11.1493 16.58 10.3739 16.0712C10.3478 16.0541 10.3175 16.0453 10.2871 16.0453C10.271 16.0453 10.2548 16.0477 10.2391 16.0527L8.65504 16.5563L9.16641 15.0476C9.18295 14.9988 9.17468 14.9449 9.14421 14.9033C8.5537 14.0964 8.24156 13.1431 8.24156 12.1463C8.24156 9.55297 10.3685 7.44308 12.9828 7.44308C15.5968 7.44308 17.7234 9.55297 17.7234 12.1463C17.7235 14.7394 15.5968 16.849 12.9828 16.849Z"
                                          fill="white"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_news_whatsapp">
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
                                <path d="M16.0224 7.38711C16.0224 7.38711 16.9474 6.95426 16.8703 8.00547C16.8446 8.43833 16.6134 9.95331 16.4335 11.592L15.8168 16.4461C15.8168 16.4461 15.7654 17.1572 15.3029 17.2809C14.8403 17.4046 14.1466 16.8481 14.0181 16.7244C13.9153 16.6316 12.0909 15.2403 11.4484 14.5601C11.2686 14.3746 11.063 14.0036 11.4741 13.5707L14.1722 10.4789C14.4806 10.1079 14.7889 9.24221 13.5041 10.2934L9.90666 13.2307C9.90666 13.2307 9.49552 13.5398 8.72465 13.2616L7.05438 12.6432C7.05438 12.6432 6.43767 12.1794 7.49122 11.7156C10.0609 10.2625 13.2215 8.77841 16.0224 7.38711Z"
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

        <?php else: ?>
            <div class="no-reviews">
                <p>Отзывов пока нет</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- Пагинация -->
    <?php if ($arResult["NAV_RESULT"]):
        $navResult = $arResult["NAV_RESULT"];
        $currentPage = $navResult->NavPageNomer;
        $totalPages = $navResult->NavPageCount;

        if ($totalPages > 1):
            // Сохраняем все текущие GET-параметры, меняем только PAGEN_1
            $queryParams = $_GET;
            unset($queryParams['PAGEN_1']);
            $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
            ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++):
                    $queryParams['PAGEN_1'] = $i;
                    $url = $baseUrl . '?' . http_build_query($queryParams);
                    if ($i == $currentPage): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($url) ?>"><?= $i ?></a>
                    <?php endif;
                endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

