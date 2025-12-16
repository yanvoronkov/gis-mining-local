<?php
/**
 * Шаблон компонента catalog.section для раздела "Готовый бизнес"
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
    <div class="product-grid gotovyy-biznes__product-grid">
        <?php foreach ($arResult["ITEMS"] as $arItem): ?>
            <?php
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            
            // --- ПОДГОТОВКА ДАННЫХ ДЛЯ КАРТОЧКИ ---
            
            // Получаем цену. Используем оператор объединения с null для краткости и надежности.
            $priceValue = $arItem['ITEM_PRICES'][0]['PRICE'] ?? 0;
            $priceFormatted = ($priceValue > 0) ? number_format($priceValue, 0, '.', ' ') . ' ₽' : 'Цена по запросу';
            
            // Изображение
            $imageSrc = !empty($arItem["PREVIEW_PICTURE"]["SRC"]) ? $arItem["PREVIEW_PICTURE"]["SRC"] : '/local/templates/main/assets/img/no-photo.jpg';
            ?>
            <div class="product-card business-product-card" 
                 data-product-id="<?= $arItem['ID'] ?>" 
                 data-name="<?= htmlspecialchars($arItem['NAME']) ?>" 
                 data-price="<?= htmlspecialchars($priceFormatted) ?>" 
                 data-price-raw="<?= (float)$priceValue ?>"
                 data-photo="<?= htmlspecialchars($imageSrc) ?>"> 
                 <div class="product-card__header">
                    <div class="product-card__tags">
                        <?php if (!empty($arItem["PROPERTIES"]["CRYPTO"]["VALUE"])): ?>
                            <?php 
                            $cryptoValues = is_array($arItem["PROPERTIES"]["CRYPTO"]["VALUE"]) 
                                ? $arItem["PROPERTIES"]["CRYPTO"]["VALUE"] 
                                : [$arItem["PROPERTIES"]["CRYPTO"]["VALUE"]];
                            foreach ($cryptoValues as $crypto): ?>
                                <span class="tag tag--white"><?= htmlspecialchars($crypto) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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
                    <p class="product-card__costfrom">от <?= $priceFormatted ?></p>
                    <?php if (!empty($arItem["PROPERTIES"]["DEVICES_COUNT"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Количество устройств</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["DEVICES_COUNT"]["VALUE"]) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["YEARLY_PROFIT"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Прибыль в год</p>
                        <p class="product-card__numofdev-value">от <?= htmlspecialchars($arItem["PROPERTIES"]["YEARLY_PROFIT"]["VALUE"]) ?> ₽</p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["MONTHLY_INCOME"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Прибыль в мес</p>
                        <p class="product-card__numofdev-value">от <?= htmlspecialchars($arItem["PROPERTIES"]["MONTHLY_INCOME"]["VALUE"]) ?> ₽</p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arItem["PROPERTIES"]["PAYBACK_PERIOD"]["VALUE"])): ?>
                    <div class="product-card__property-item">
                        <p class="product-card__numofdev-name">Окупаемость</p>
                        <p class="product-card__numofdev-value"><?= htmlspecialchars($arItem["PROPERTIES"]["PAYBACK_PERIOD"]["VALUE"]) ?>дн</p>
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
