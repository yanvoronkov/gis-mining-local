<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!\Bitrix\Main\Loader::includeModule('highloadblock')) {
    return;
}

$productId = (int)$arParams["PRODUCT_ID"];
$hlbl = 2; // HL-блок "Отзывы"

$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
if (!$hlblock) return;

$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$entityClass = $entity->getDataClass();

$reviews = [];
$ratings = [];

$res = $entityClass::getList([
    'select' => ['*'],
    'filter' => ['UF_PRODUCT_ID' => $productId],
    'order'  => ['UF_DATE' => 'DESC']
]);

while ($row = $res->fetch()) {
    $reviews[] = $row;
    $ratings[] = (int)$row['UF_RATING'];
}

$averageRating = count($ratings) ? round(array_sum($ratings) / count($ratings), 1) : 0;
?>

<!-- SVG-шаблон звезды -->
<svg style="display:none">
  <symbol id="star" viewBox="0 0 24 24">
    <polygon points="12,2 15,9 22,9 17,14 19,21 12,17 5,21 7,14 2,9 9,9"/>
  </symbol>
</svg>

<div class="reviews-section">
    <div class="reviews-container">
        <div class="reviews-column">
            <?php if ($reviews): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-header">
                        <div class="review-avatar">
                            <span class="avatar-text"><?= mb_substr($review['UF_AUTHOR'], 0, 1) ?></span>
                        </div>
                        <div class="review-info">
                            <div class="review-top">
                                <div class="review-author"><?= htmlspecialchars($review['UF_AUTHOR']) ?></div>
                                <div class="review-date">
                                    <?= $review['UF_DATE'] instanceof \Bitrix\Main\Type\DateTime ? $review['UF_DATE']->toString() : '' ?>
                                </div>
                                <div class="review-rating-inline">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="star-icon <?= $i <= (int)$review['UF_RATING'] ? 'filled' : '' ?>" width="16" height="16">
                                                <use xlink:href="#star"></use>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="review-text">
                                <p><?= nl2br(htmlspecialchars($review['UF_TEXT'])) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="review-separator"></div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Отзывов пока нет.</p>
            <?php endif; ?>
        </div>

        <!-- Общий рейтинг -->
        <div class="rating-summary">
            <div class="rating-card">
                <div class="rating-card-items">
                    <div class="rating-stars-large">
                        <?php
                        $fullStars = floor($averageRating);
                        $halfStar = ($averageRating - $fullStars >= 0.5);

                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= $fullStars): ?>
                                <!-- Полная звезда -->
                                <svg class="star-icon filled" width="28" height="28">
                                    <use xlink:href="#star"></use>
                                </svg>

                            <?php elseif ($halfStar && $i == $fullStars + 1): ?>
                                <!-- Половинка -->
                                <svg class="star-icon" width="28" height="28">
                                    <defs>
                                        <linearGradient id="half-gradient-<?=$productId?>" x1="0" y1="0" x2="100%" y2="0">
                                            <stop offset="50%" stop-color="#FFD700"/>
                                            <stop offset="50%" stop-color="transparent" stop-opacity="1"/>
                                        </linearGradient>
                                    </defs>
                                    <use xlink:href="#star" fill="url(#half-gradient-<?=$productId?>)" stroke="#FFD700"></use>
                                </svg>

                            <?php else: ?>
                                <!-- Пустая звезда -->
                                <svg class="star-icon" width="28" height="28">
                                    <use xlink:href="#star"></use>
                                </svg>
                            <?php endif;
                        endfor;
                        ?>
                    </div>
                    <div class="rating-score"><?= $averageRating ?>/5</div>
                </div>
                <div class="rating-description">
                    <p>Рейтинг формируется на основе опубликованных отзывов.</p>
                </div>
            </div>
        </div>
    </div>
</div>
