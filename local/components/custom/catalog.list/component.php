<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

if (!Loader::includeModule('iblock')) {
    ShowError('Модуль iblock не установлен');
    return;
}

$arResult['ITEMS'] = [];

// Используем константу IBLOCK_IDS_ALL_CATALOG для фильтрации
$targetIblockIds = defined('IBLOCK_IDS_ALL_CATALOG') ? IBLOCK_IDS_ALL_CATALOG : [];

if (!empty($targetIblockIds)) {
    // Кеширование
    $cacheId = 'custom_catalog_list_' . SITE_ID . '_' . md5(serialize($targetIblockIds));
    $cacheTime = $arParams['CACHE_TIME'] ? $arParams['CACHE_TIME'] : 3600;
    $cacheDir = '/custom/catalog_list';

    $obCache = new CPHPCache;
    if ($obCache->InitCache($cacheTime, $cacheId, $cacheDir)) {
        $arResult['ITEMS'] = $obCache->GetVars();
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

            // Особый случай: инфоблок "Размещение"
            if ($iblock['ID'] == 2 || $iblock['CODE'] == 'razmeschenie') {
                $url = '/razmeschenie/';
            }

            $arResult['ITEMS'][] = [
                'ID' => $iblock['ID'],
                'NAME' => $iblock['NAME'],
                'CODE' => $iblock['CODE'],
                'PICTURE' => $iblock['PICTURE'] ? CFile::GetPath($iblock['PICTURE']) : '/local/templates/main/assets/images/no-photo.jpg',
                'URL' => $url,
            ];
        }

        $obCache->EndDataCache($arResult['ITEMS']);
    }
}

$this->IncludeComponentTemplate();
