<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */

// Применяем кастомную сортировку только для ASICS (1) и Videocard (11)
if (!in_array($arParams["IBLOCK_ID"], [1, 11])) {
    return;
}

// Проверяем, что есть товары для сортировки
if (empty($arResult['ITEMS'])) {
    return;
}

// Перегруппировка товаров по приоритетам:
// 1. Featured (Хиты)
// 2. С ценой (от дешевых к дорогим)
// 3. Без цены (Под заказ)

$featured = [];
$withPrice = [];
$withoutPrice = [];

foreach ($arResult['ITEMS'] as $item) {
    // Получаем цену товара
    $price = (float) ($item['ITEM_PRICES'][0]['PRICE'] ?? 0);

    // Проверяем, является ли товар "Хитом"
    $isFeatured = ($item['PROPERTIES']['FEATURED']['VALUE'] === 'Да' || $item['PROPERTIES']['FEATURED']['VALUE'] === 'Y');

    // Распределяем по группам
    if ($isFeatured) {
        $featured[] = $item;
    } elseif ($price > 0) {
        $withPrice[] = $item;
    } else {
        $withoutPrice[] = $item;
    }
}

// Сортируем товары с ценой по возрастанию цены
if (!empty($withPrice)) {
    usort($withPrice, function ($a, $b) {
        $priceA = (float) ($a['ITEM_PRICES'][0]['PRICE'] ?? 0);
        $priceB = (float) ($b['ITEM_PRICES'][0]['PRICE'] ?? 0);
        return $priceA <=> $priceB;
    });
}

// Объединяем все группы в правильном порядке и заменяем массив товаров
$arResult['ITEMS'] = array_merge($featured, $withPrice, $withoutPrice);
