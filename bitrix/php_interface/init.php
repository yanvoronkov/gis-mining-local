<?php
// Автоматическое обновление свойства сортировки при изменении цены
AddEventHandler("catalog", "OnPriceUpdate", "UpdateSortPropertyOnPriceChange");
AddEventHandler("catalog", "OnPriceAdd", "UpdateSortPropertyOnPriceChange");

function UpdateSortPropertyOnPriceChange($ID) {
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");
    
    // Получаем информацию о цене
    $rsPrice = CPrice::GetList(array(), array("ID" => $ID));
    if($arPrice = $rsPrice->Fetch()) {
        $productId = $arPrice["PRODUCT_ID"];
        updateProductSortProperty($productId);
    }
}

// Также отслеживаем изменения элементов инфоблока
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "UpdateSortPropertyOnElementChange");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "UpdateSortPropertyOnElementChange");

function UpdateSortPropertyOnElementChange($arFields) {
    if(isset($arFields["IBLOCK_ID"]) && $arFields["IBLOCK_ID"] == 1) { // Ваш ID инфоблока
        CModule::IncludeModule("catalog");
        CModule::IncludeModule("iblock");
        $productId = isset($arFields["ID"]) ? $arFields["ID"] : 0;
        if($productId > 0) {
            updateProductSortProperty($productId);
        }
    }
}

function updateProductSortProperty($productId) {
    if(!$productId) return;
    
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");
    
    // Получаем цену товара
    $arPrice = CCatalogProduct::GetOptimalPrice($productId, 1, array(), "N");
    $sortValue = 999999999; // Большое значение для товаров без цены
    
    if($arPrice && isset($arPrice["PRICE"]["PRICE"]) && $arPrice["PRICE"]["PRICE"] > 0) {
        $sortValue = (float)$arPrice["PRICE"]["PRICE"]; // Для товаров с ценой - реальная цена
    }
    
    // Обновляем свойство сортировки
    CIBlockElement::SetPropertyValuesEx(
        $productId,
        1,
        array("SORT_BY_PRICE" => $sortValue)
    );
}
?>