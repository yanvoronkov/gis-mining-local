<?php
/**
 * Шаблон компонента catalog.filter для нового каталога
 * Соответствует структуре, которая раньше рендерилась через JavaScript
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

<form class="catalog-filter js-filter-form" method="GET" action="">
    <?php if (!empty($arResult["ITEMS"])): ?>
        <?php foreach ($arResult["ITEMS"] as $arItem): ?>
            <?php if (strpos($arItem["INPUT_NAME"], "PROPERTY_") === 0): ?>
                <!-- Фильтр по свойствам -->
                <div class="catalog-filter__group">
                    <button type="button" class="catalog-filter__group-toggle js-filter-group-toggle is-active">
                        <span><?= $arItem["NAME"] ?></span>
                        <svg class="icon-arrow" width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 0.5L5 4.5L1 0.5" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="catalog-filter__group-content">
                        <ul class="filter-options">
                            <?php if ($arItem["TYPE"] == "CHECKBOX"): ?>
                                <?php 
                                $initialVisibleCount = 4; // Сколько пунктов показывать по умолчанию
                                $itemCount = 0;
                                ?>
                                <?php foreach ($arItem["LIST"] as $value => $label): ?>
                                    <?php if ($value !== ""): ?>
                                        <?php 
                                        $itemCount++;
                                        $hiddenClass = $itemCount > $initialVisibleCount ? 'is-hidden' : '';
                                        ?>
                                        <li class="filter-options__item <?= $hiddenClass ?>">
                                            <label class="checkbox-custom">
                                                <input type="checkbox" 
                                                       class="checkbox-custom__input" 
                                                       name="<?= $arItem["INPUT_NAME"] ?>" 
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       <?= (is_array($arItem["~INPUT_VALUE"]) && in_array($value, $arItem["~INPUT_VALUE"])) ? 'checked' : '' ?>>
                                                <span class="checkbox-custom__box"></span>
                                                <span class="checkbox-custom__text"><?= htmlspecialchars($label) ?></span>
                                            </label>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <?php if ($itemCount > $initialVisibleCount): ?>
                                    <li class="filter-options__item">
                                        <a href="#" class="filter-options__show-more js-show-more">
                                            Показать еще <?= $itemCount - $initialVisibleCount ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                            <?php elseif ($arItem["TYPE"] == "RADIO"): ?>
                                <?php foreach ($arItem["LIST"] as $value => $label): ?>
                                    <?php if ($value !== ""): ?>
                                        <li class="filter-options__item">
                                            <label class="checkbox-custom">
                                                <input type="radio" 
                                                       class="checkbox-custom__input" 
                                                       name="<?= $arItem["INPUT_NAME"] ?>" 
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       <?= ($arItem["~INPUT_VALUE"] == $value) ? 'checked' : '' ?>>
                                                <span class="checkbox-custom__box"></span>
                                                <span class="checkbox-custom__text"><?= htmlspecialchars($label) ?></span>
                                            </label>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Скрытые поля для отправки формы -->
    <input type="hidden" name="set_filter" value="Y">
    
    <!-- Скрытые поля для сохранения параметров пагинации -->
    <?php foreach ($arResult["ITEMS"] as $arItem): ?>
        <?php if (isset($arItem["HIDDEN"]) && $arItem["HIDDEN"]): ?>
            <?= $arItem["INPUT"] ?>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <!-- Кнопки управления фильтром -->
    <div class="catalog-filter__actions">
        <button type="submit" class="btn btn-primary catalog-filter__apply-btn">Применить</button>
        <button type="button" class="btn btn-secondary catalog-filter__reset-btn">Сбросить все фильтры</button>
    </div>
</form>

