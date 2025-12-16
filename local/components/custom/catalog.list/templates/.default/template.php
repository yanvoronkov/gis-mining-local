<?php
/**
 * Шаблон компонента custom:catalog.list
 * Сетка категорий 4 в ряд
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arResult */
/** @global CMain $APPLICATION */
?>

<div class="categories-grid-container">
    <ul class="categories-grid">
        <?php if (!empty($arResult['ITEMS'])): ?>
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <?php $isActive = ($APPLICATION->GetCurDir() == $item['URL']); ?>
                <li class="category-grid-item">
                    <a href="<?= $item['URL'] ?>" class="category-grid-card <?= $isActive ? 'active' : '' ?>">
                        <div class="category-grid-image-wrapper">
                            <img class="category-grid-image" src="<?= $item['PICTURE'] ?>" alt="<?= $item['NAME'] ?>">
                        </div>
                        <h2 class="category-grid-title"><?= $item['NAME'] ?></h2>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="category-grid-item">
                <div class="category-grid-card">
                    <div class="category-grid-image-wrapper">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#ccc" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M2 17L12 22L22 17" stroke="#ccc" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M2 12L12 17L22 12" stroke="#ccc" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="category-grid-title">Категории не найдены</h3>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</div>