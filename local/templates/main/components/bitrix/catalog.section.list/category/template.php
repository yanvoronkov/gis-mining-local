<?php
/**
 * Шаблон компонента catalog.section.list для сайдбара с инфоблоками
 * Адаптирован под дизайн сайта - сетка 4 в ряд
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

// Получаем список активных инфоблоков типа catalog
$iblocks = [];
if (CModule::IncludeModule('iblock')) {
    $dbIblocks = CIBlock::GetList(
        ['SORT' => 'ASC', 'NAME' => 'ASC'],
        [
            'IBLOCK_TYPE_ID' => 'catalog',
            'ACTIVE' => 'Y',
            'SITE_ID' => SITE_ID,
        ]
    );
    
    while ($iblock = $dbIblocks->Fetch()) {
    // Массивы исключений
    $excludeIds = [8, 9, 10, 12, 13, 14];       // исключаемые ID инфоблоков
    $excludeCodes = ['blog'];       // исключаемые коды инфоблоков

    // Пропускаем инфоблоки из списков исключений
    if (in_array($iblock['ID'], $excludeIds) || in_array($iblock['CODE'], $excludeCodes)) {
        continue;
    }

    // Формируем URL для каждого инфоблока
    $url = '/catalog/' . ($iblock['CODE'] ?: $iblock['ID']) . '/';

    // Особый случай: инфоблок "Размещение"
    if ($iblock['ID'] == 2 || $iblock['CODE'] == 'razmeschenie') {
        $url = '/razmeschenie/';
    }

    $iblocks[] = [
        'ID'      => $iblock['ID'],
        'NAME'    => $iblock['NAME'],
        'CODE'    => $iblock['CODE'],
        'PICTURE' => $iblock['PICTURE'] ? CFile::GetPath($iblock['PICTURE']) : '/local/templates/main/assets/images/no-photo.jpg',
        'URL'     => $url,
    ];
}

}
?>

<div class="categories-grid-container">
    <ul class="categories-grid">
        <?php if (!empty($iblocks)): ?>
            <?php foreach($iblocks as $iblock): ?>
                <?php $isActive = ($APPLICATION->GetCurDir() == $iblock['URL']); ?>
                <li class="category-grid-item">
                    <a href="<?= $iblock['URL'] ?>" class="category-grid-card <?= $isActive ? 'active' : '' ?>">
                        <div class="category-grid-image-wrapper">
                            <img class="category-grid-image" 
                                 src="<?= $iblock['PICTURE'] ?>" 
                                 alt="<?= $iblock['NAME'] ?>">
                        </div>
                        <h2 class="category-grid-title"><?= $iblock['NAME'] ?></h2>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="category-grid-item">
                <div class="category-grid-card">
                    <div class="category-grid-image-wrapper">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="category-grid-title">Категории не найдены</h3>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</div>