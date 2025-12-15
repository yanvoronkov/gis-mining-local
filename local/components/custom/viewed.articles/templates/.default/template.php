<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<!-- Просмотренные статьи -->

<div class="blog__viewed-articles" id="viewedArticlesContainer">
    <h3 class="blog__viewed-articles-title">Вы читали</h3>
    <div class="blog__native-slider native-slider js-native-slider placement-gallery-slider slider--centered slider--partial-view"
        data-native-slider-options='{
            "pagination": { "el": ".placement-gallery-slider__pagination", "clickable": false },
            "navigation": {"nextEl": ".slider-button-next", "prevEl": ".slider-button-prev"}
        }'>

        <div class="blog__viewed-articles-list native-slider__wrapper" id="viewedArticlesList">
            <!-- Динамически заполняется через JavaScript -->
        </div>

        <div class="native-slider__pagination placement-gallery-slider__pagination">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div class="native-slider__nav slider-button-prev">PREV</div>
        <div class="native-slider__nav slider-button-next">NEXT</div>
    </div>
</div>

<!-- Скрытые поля для передачи данных в JavaScript -->
<input type="hidden" id="currentArticleId" value="<?= $arResult["CURRENT_ARTICLE_ID"] ?>">
<input type="hidden" id="currentArticleName" value="<?= htmlspecialchars($arResult["CURRENT_ARTICLE_DATA"]["NAME"] ?? '') ?>">
<input type="hidden" id="currentArticleCode" value="<?= htmlspecialchars($arResult["CURRENT_ARTICLE_DATA"]["CODE"] ?? '') ?>">
<input type="hidden" id="currentArticlePreviewText" value="<?= htmlspecialchars($arResult["CURRENT_ARTICLE_DATA"]["PREVIEW_TEXT"] ?? '') ?>">
<input type="hidden" id="currentArticlePreviewPicture" value="<?= htmlspecialchars($arResult["CURRENT_ARTICLE_DATA"]["PREVIEW_PICTURE"]["SRC"] ?? '') ?>">
<input type="hidden" id="currentArticleActiveFrom" value="<?= htmlspecialchars($arResult["CURRENT_ARTICLE_DATA"]["ACTIVE_FROM"] ?? '') ?>">
<input type="hidden" id="currentArticleTags" value="<?= htmlspecialchars(json_encode($arResult["CURRENT_ARTICLE_DATA"]["PROPERTY_TAGS_VALUE"] ?? [])) ?>">
<input type="hidden" id="baseUrl" value="<?= htmlspecialchars($arResult["BASE_URL"]) ?>">
<input type="hidden" id="sectionType" value="<?= htmlspecialchars($arResult["SECTION_TYPE"]) ?>">
<input type="hidden" id="currentArticleUrl" value="<?= htmlspecialchars($arResult["CURRENT_ARTICLE_URL"]) ?>">

<!-- Подключаем JavaScript логику -->
<script src="<?= $templateFolder ?>/script.js"></script>
