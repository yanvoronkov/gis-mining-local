<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// Обновляем навигационную цепочку если нужно
if (isset($arResult["NAV_RESULT"])) {
    $arResult["NAV_RESULT"]->NavRecordCount = count($arResult["ITEMS"]);
}
?>