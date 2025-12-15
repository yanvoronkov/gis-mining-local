<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');

header('Content-Type: application/json; charset=utf-8');

$IBLOCK_ID = 1; // инфоблок асиков

$items = [];

$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y"],
    false,
    false,
    [
        "ID",
        "NAME",
        "DETAIL_PAGE_URL",     // ←←← ДОБАВЛЕНО!
        "PREVIEW_PICTURE",

        "PROPERTY_MANUFACTURER",
        "PROPERTY_HASHRATE_TH",
        "PROPERTY_HASHRATE_MH",
        "PROPERTY_ALGORITHM",
        "PROPERTY_CRYPTO",
        "PROPERTY_POWER",

        "PROPERTY_IN_CALCULATOR_TG",
        "PROPERTY_IN_INVESTOR_CALC"
    ]
);

while ($arItem = $res->GetNext()) {

    // --- КАРТИНКА ---
    $img = null;
    if ($arItem["PREVIEW_PICTURE"]) {
        $img = CFile::GetPath($arItem["PREVIEW_PICTURE"]);
    }

    // --- БАЗОВАЯ ЦЕНА ---
    $priceData = CPrice::GetBasePrice($arItem["ID"]);
    $price = $priceData["PRICE"] ?: null;

    // --- ФОРМИРУЕМ ОБЪЕКТ ---
    $items[] = [
        "id"    => (int)$arItem["ID"],
        "name"  => $arItem["NAME"],

        "manufacturer"  => $arItem["PROPERTY_MANUFACTURER_VALUE"] ?: null,
        "hashrate_th"   => floatval($arItem["PROPERTY_HASHRATE_TH_VALUE"]),
        "hashrate_mh"   => floatval($arItem["PROPERTY_HASHRATE_MH_VALUE"]),
        "algorithm"     => $arItem["PROPERTY_ALGORITHM_VALUE"] ?: null,
        "crypto"        => $arItem["PROPERTY_CRYPTO_VALUE"] ?: null,
        "power_kw"      => floatval($arItem["PROPERTY_POWER_VALUE"]),

        "price"         => $price ? floatval($price) : null,
        "image"         => $img,

        "detail_url"    => $arItem["DETAIL_PAGE_URL"] ?: null, // ←←← НОВОЕ!

        "IN_CALCULATOR_TG"  => $arItem["PROPERTY_IN_CALCULATOR_TG_VALUE"] ?: "Нет",
        "IN_INVESTOR_CALC"  => $arItem["PROPERTY_IN_INVESTOR_CALC_VALUE"] ?: "Нет"
    ];
}

echo json_encode($items, JSON_UNESCAPED_UNICODE);
