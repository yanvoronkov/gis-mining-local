<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Этот код добавит название текущей новости в хлебные крошки.
 * Он должен находиться в файле component_epilog.php шаблона компонента
 * (например, bitrix/templates/your_template/components/bitrix/news.detail/news_detail/component_epilog.php)
 */

if (isset($arResult['NAME'])) {
    // Добавляем раздел "Новости" в хлебные крошки
//    $APPLICATION->AddChainItem("Новости", "/news/");
    
    // Добавляем название новости в хлебные крошки
    $APPLICATION->AddChainItem($arResult['NAME'], $arResult['DETAIL_PAGE_URL']);
}
