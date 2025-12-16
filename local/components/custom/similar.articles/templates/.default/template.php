<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<?php if (!empty($arResult["SIMILAR_ARTICLES"])): ?>
<!-- Читайте также (Похожие статьи) -->
<div class="page-blog-detail__similar-articles" id="similarArticlesContainer">
    <h3 class="page-blog-detail__similar-title">Читайте также</h3>
    <div class="page-blog-detail__similar-grid native-slider js-native-slider similar-slider slider--centered slider--partial-view"
        data-native-slider-options='{
            "pagination": { "el": ".similar-slider__pagination", "clickable": false },
            "navigation": {"nextEl": ".slider-button-next", "prevEl": ".slider-button-prev"}
        }'>

        <div class="native-slider__wrapper" id="similarArticlesList">
            <?php foreach ($arResult["SIMILAR_ARTICLES"] as $article): ?>
                <a href="<?= $arResult["BASE_URL"] ?><?= $article["CODE"] ?>/" class="page-blog-detail__similar-card-link native-slider__slide">
                    <div class="page-blog__card card-blog">
                        <div class="card-blog__image-wrap">
                            <?php if (!empty($article["PREVIEW_PICTURE"])): ?>
                                <img src="<?= CFile::GetPath($article["PREVIEW_PICTURE"]) ?>"
                                     alt="<?= $article["NAME"] ?>"
                                     class="card-blog__image">
                            <?php endif; ?>
                        </div>

                        <div class="card-blog__content">
                            <div class="card-blog__tag-date-wrap">
                                <?php if (!empty($article["PROPERTY_TAGS_VALUE"])): ?>
                                    <div class="card-blog__tag">
                                        #<?= is_array($article["PROPERTY_TAGS_VALUE"]) ? $article["PROPERTY_TAGS_VALUE"][0] : $article["PROPERTY_TAGS_VALUE"] ?>
                                    </div>
                                <?php endif; ?>
                                <div class="card-blog__date">
                                    <?= FormatDate("d.m.Y", MakeTimeStamp($article["ACTIVE_FROM"])) ?>
                                </div>
                            </div>
                            <h3 class="card-blog__title">
                                <?= $article["NAME"] ?>
                            </h3>
                            <?php if (!empty($article["PREVIEW_TEXT"])): ?>
                                <div class="card-blog__excerpt">
                                    <?= $article["PREVIEW_TEXT"] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="native-slider__pagination similar-slider__pagination">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div class="native-slider__nav slider-button-prev">PREV</div>
        <div class="native-slider__nav slider-button-next">NEXT</div>
    </div>
</div>
<?php endif; ?>

