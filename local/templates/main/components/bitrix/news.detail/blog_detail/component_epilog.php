<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Этот код добавляет название текущей статьи в хлебные крошки
 * и устанавливает его как заголовок страницы.
 */
 
// Проверяем, что у нас есть массив с данными элемента ($arResult) и в нем есть название
if (isset($arResult['NAME']) && $arResult['NAME'] != '') {
    // Добавляем название элемента в конец цепочки навигации
//    $APPLICATION->AddChainItem($arResult['NAME']);

}