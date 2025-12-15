<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$rsProducts = CIBlockElement::GetList(
    array(),
    array("IBLOCK_ID" => 1),
    false,
    false,
    array("ID")
);

$updated = 0;
while($arProduct = $rsProducts->GetNext()) {
    $arPrice = CCatalogProduct::GetOptimalPrice($arProduct["ID"], 1, array(), "N");
    $sortValue = 999999999; // Большое значение для товаров без цены (они будут в конце)
    
    if($arPrice && isset($arPrice["PRICE"]["PRICE"]) && $arPrice["PRICE"]["PRICE"] > 0) {
        $sortValue = $arPrice["PRICE"]["PRICE"]; // Для товаров с ценой - реальная цена (будут первыми)
    }
    
    CIBlockElement::SetPropertyValuesEx(
        $arProduct["ID"], 
        1, 
        array("SORT_BY_PRICE" => $sortValue)
    );
    
    $updated++;
    if($updated % 100 == 0) {
        echo "Обновлено: $updated товаров<br>";
        flush();
    }
}

echo "Всего обновлено: $updated товаров";

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>