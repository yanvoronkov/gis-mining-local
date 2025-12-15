<?php
/**
 * Шаблон компонента catalog.section.list для сайдбара с инфоблоками
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

<div class="catalog-accordion">
    <button type="button" class="catalog-accordion__toggle btn btn-primary">
        <svg width="16" height="11" viewBox="0 0 16 11" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M15.2 9.07143C15.6418 9.07143 16 9.39123 16 9.78571C16 10.1802 15.6418 10.5 15.2 10.5H0.8C0.358172 10.5 0 10.1802 0 9.78571C0 9.39123 0.358172 9.07143 0.8 9.07143H15.2ZM15.2 4.78571C15.6418 4.78571 16 5.10551 16 5.5C16 5.89449 15.6418 6.21429 15.2 6.21429H0.8C0.358172 6.21429 0 5.89449 0 5.5C0 5.10551 0.358172 4.78571 0.8 4.78571H15.2ZM15.2 0.5C15.6418 0.5 16 0.819797 16 1.21429C16 1.60877 15.6418 1.92857 15.2 1.92857H0.8C0.358172 1.92857 0 1.60877 0 1.21429C0 0.819797 0.358172 0.5 0.8 0.5H15.2Z"
                  fill="white" />
        </svg>
        <span>Категории</span>
        <svg class="icon-arrow" width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 0.5L5 4.5L1 0.5" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div class="catalog-accordion__content">
        <nav class="filter-categories">
            <ul class="filter-categories__list">
                <?php if (!empty($iblocks)): ?>
                    <?php foreach($iblocks as $iblock): ?>
                        <?php $isActive = ($APPLICATION->GetCurDir() == $iblock['URL']); ?>
                        <li class="filter-categories__item <?= $isActive ? 'filter-categories__item--active' : '' ?>">
                            <a href="<?= $iblock['URL'] ?>" class="category-button">
                                <div class="filter-categories__img-wrapper">
                                    <img class="filter-categories__image" 
                                         src="<?= $iblock['PICTURE'] ?>" 
                                         alt="<?= $iblock['NAME'] ?>">
                                </div>
                                <p><?= $iblock['NAME'] ?></p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Если инфоблоков нет, показываем сообщение -->
                    <li class="filter-categories__item">
                        <p>Категории не найдены</p>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>
