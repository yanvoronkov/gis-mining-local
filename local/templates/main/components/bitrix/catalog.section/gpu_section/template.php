<?php
/**
 * Шаблон компонента catalog.section для раздела "GPU"
 * Адаптирован под дизайн сайта
 */

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
?>

<?php if (!empty($arResult["ITEMS"])): ?>
    <div class="product-grid gpu__product-grid">
        <?php foreach ($arResult["ITEMS"] as $arItem): ?>
            <?php
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            
            // --- ПОДГОТОВКА ДАННЫХ ДЛЯ КАРТОЧКИ ---
            
            // Получаем цену. Используем оператор объединения с null для краткости и надежности.
            $priceValue = $arItem['ITEM_PRICES'][0]['PRICE'] ?? 0;
            $priceFormatted = ($priceValue > 0) ? number_format($priceValue, 0, '.', ' ') . ' ₽' : 'Под заказ';
            
            // Изображение
            $imageSrc = !empty($arItem["PREVIEW_PICTURE"]["SRC"]) ? $arItem["PREVIEW_PICTURE"]["SRC"] : '/local/templates/main/assets/img/no-photo.jpg';
            ?>
            <div class="product-card gpu-product-card" 
                 data-product-id="<?= $arItem['ID'] ?>" 
                 data-name="<?= htmlspecialchars($arItem['NAME']) ?>" 
                 data-price="<?= htmlspecialchars($priceFormatted) ?>" 
                 data-price-raw="<?= (float)$priceValue ?>"
                 data-photo="<?= htmlspecialchars($imageSrc) ?>"> 
                 <div class="product-card__header">
                    <div class="product-card__image-wrapper">
                        <img class="product-card__image" src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($arItem["NAME"]) ?>" loading="lazy">
                    </div>
                    <div class="product-card__dots">
                        <span class="product-card__dot product-card__dot--active"></span>
                        <span class="product-card__dot"></span>
                        <span class="product-card__dot"></span>
                        <span class="product-card__dot"></span>
                    </div>
                </div>
                <div class="product-card__info">
                    <div class="product-card__name"><?= htmlspecialchars($arItem["NAME"]) ?></div>
                    <p class="product-card__costfrom"><?= $priceFormatted ?></p>
                    <?php if (!empty($arItem["PROPERTIES"]["MANUFACTURER"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Производитель</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["MANUFACTURER"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["GPU_POWER"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Мощность</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["GPU_POWER"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["GPU_ENGINE"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Двигатель</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["GPU_ENGINE"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["GPU_COUNTRY_OF_ORIGIN"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Страна производства</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["GPU_COUNTRY_OF_ORIGIN"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["HASHRATE"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Хешрейт</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["HASHRATE"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["ALGORITHM"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Алгоритм</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["ALGORITHM"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["EFFICIENCY"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Эффективность</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["EFFICIENCY"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["POWER"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Потребление</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["POWER"]["VALUE"]) ?> Вт</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="product-card__action">
                    <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить КП</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-products">
        <p>Товары не найдены</p>
    </div>
<?php endif; ?>
