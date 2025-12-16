<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;

/**
 * Компонент catalog.sidebar
 * Универсальный сайдбар для каталога с навигацией по инфоблокам
 * ФИЛЬТР ВЫНЕСЕН В SECTION.PHP - компонент отвечает только за навигацию
 */

// Параметры компонента
$IBLOCK_ID = intval($arParams['IBLOCK_ID']);

// Проверяем наличие IBLOCK_ID
if ($IBLOCK_ID <= 0) {
    ShowError('Не указан IBLOCK_ID для компонента catalog.sidebar');
    return;
}

// Загружаем модуль инфоблоков
if (!Loader::includeModule('iblock')) {
    ShowError('Не удалось загрузить модуль iblock');
    return;
}

// Передаем параметры в шаблон
$arResult['IBLOCK_ID'] = $IBLOCK_ID;

// =========================================================================
// Получение списка разделов каталога (Инфоблоков)
// Исключаем лишние запросы из шаблона
// =========================================================================

$arResult['MENU_ITEMS'] = [];

// Используем константу IBLOCK_IDS_ALL_CATALOG для фильтрации
// Если константа не определена, используем пустой массив (безопасность)
$targetIblockIds = defined('IBLOCK_IDS_ALL_CATALOG') ? IBLOCK_IDS_ALL_CATALOG : [];

if (!empty($targetIblockIds)) {
    $cacheId = 'catalog_sidebar_menu_' . SITE_ID . '_' . md5(serialize($targetIblockIds));
    $cacheTime = 3600 * 24; // Кешируем на сутки, структура меняется редко
    $cacheDir = '/custom/catalog_sidebar';

    $obCache = new CPHPCache;
    if ($obCache->InitCache($cacheTime, $cacheId, $cacheDir)) {
        $arResult['MENU_ITEMS'] = $obCache->GetVars();
    } elseif ($obCache->StartDataCache()) {
        $dbIblocks = CIBlock::GetList(
            ['SORT' => 'ASC', 'NAME' => 'ASC'],
            [
                'IBLOCK_TYPE_ID' => 'catalog',
                'ACTIVE' => 'Y',
                'SITE_ID' => SITE_ID,
                'ID' => $targetIblockIds // Фильтруем только нужные инфоблоки
            ]
        );

        while ($iblock = $dbIblocks->Fetch()) {
            // Формируем URL
            $url = '/catalog/' . ($iblock['CODE'] ?: $iblock['ID']) . '/';

            // Особый случай: инфоблок "Размещение" (ID 2 или код razmeschenie)
            // Лучше вынести ID в константу, но пока поддерживаем легаси логику
            if ($iblock['ID'] == 2 || $iblock['CODE'] == 'razmeschenie') {
                $url = '/razmeschenie/';
            }

            $arResult['MENU_ITEMS'][] = [
                'ID' => $iblock['ID'],
                'NAME' => $iblock['NAME'],
                'CODE' => $iblock['CODE'],
                'PICTURE' => $iblock['PICTURE'] ? CFile::GetPath($iblock['PICTURE']) : '/local/templates/main/assets/images/no-photo.jpg',
                'URL' => $url,
            ];
        }

        $obCache->EndDataCache($arResult['MENU_ITEMS']);
    }
}

// Подключаем шаблон
$this->IncludeComponentTemplate();
