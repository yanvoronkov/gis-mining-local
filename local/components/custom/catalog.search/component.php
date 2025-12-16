<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

// Подключаем необходимые модули
if (!Loader::includeModule('iblock')) {
    ShowError('Модуль "Информационные блоки" не установлен');
    return;
}

if (!Loader::includeModule('catalog')) {
    ShowError('Модуль "Торговый каталог" не установлен');
    return;
}

// Получаем параметры компонента
$arParams["IBLOCK_IDS"] = is_array($arParams["IBLOCK_IDS"]) && !empty($arParams["IBLOCK_IDS"])
    ? $arParams["IBLOCK_IDS"]
    : IBLOCK_IDS_ALL_CATALOG;
$arParams["MIN_QUERY_LENGTH"] = intval($arParams["MIN_QUERY_LENGTH"]) > 0 ? intval($arParams["MIN_QUERY_LENGTH"]) : SearchConfig::get('MIN_QUERY_LENGTH');
$arParams["MAX_RESULTS"] = intval($arParams["MAX_RESULTS"]) > 0 ? intval($arParams["MAX_RESULTS"]) : 10;
$arParams["SHOW_PRICE"] = $arParams["SHOW_PRICE"] !== "N" ? "Y" : "N";
$arParams["PRICE_CODE"] = !empty($arParams["PRICE_CODE"]) ? $arParams["PRICE_CODE"] : "BASE";
$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]) > 0 ? intval($arParams["CACHE_TIME"]) : 3600;
$arParams["INITIAL_QUERY"] = isset($arParams["INITIAL_QUERY"]) ? htmlspecialchars(trim($arParams["INITIAL_QUERY"])) : "";

// Передаем параметры в результат
$arResult["PARAMS"] = $arParams;

// Генерируем уникальный ID для компонента
$arResult["COMPONENT_ID"] = "catalog_search_" . randString(6);

// Путь к AJAX-обработчику
$request = Application::getInstance()->getContext()->getRequest();
$arResult["AJAX_PATH"] = $this->getPath() . "/ajax.php";

// Регистрация JS для компонента
// ПРИМЕЧАНИЕ: Стили компонента перенесены в main.css для гарантированной загрузки на всех страницах
// ПРИМЕЧАНИЕ: Скрипт компонента перенесен в components.js для гарантированной работы на всех страницах
// $templatePath = $this->getPath() . '/templates/' . $this->getTemplateName();
// Asset::getInstance()->addJs($templatePath . '/script.js');

// Подключаем шаблон
$this->IncludeComponentTemplate();

