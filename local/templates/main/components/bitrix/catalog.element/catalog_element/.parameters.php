<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "GROUPS" => array(
        "SEO_SETTINGS" => array(
            "NAME" => "SEO настройки",
            "SORT" => 100,
        ),
        "PROPERTY_SETTINGS" => array(
            "NAME" => "Настройки свойств",
            "SORT" => 200,
        ),
        "PRICE_SETTINGS" => array(
            "NAME" => "Настройки цен",
            "SORT" => 300,
        ),
    ),
    "PARAMETERS" => array(
        // Основные параметры
        "IBLOCK_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => "Тип инфоблока",
            "TYPE" => "LIST",
            "VALUES" => array(),
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID инфоблока",
            "TYPE" => "STRING",
            "DEFAULT" => "1",
        ),
        "ELEMENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID элемента",
            "TYPE" => "STRING",
            "DEFAULT" => "={$_REQUEST['ELEMENT_ID']}",
        ),
        "ELEMENT_CODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Символьный код элемента",
            "TYPE" => "STRING",
            "DEFAULT" => "={$_REQUEST['ELEMENT_CODE']}",
        ),
        
        // SEO настройки
        "SET_TITLE" => array(
            "PARENT" => "SEO_SETTINGS",
            "NAME" => "Устанавливать заголовок страницы",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SET_BROWSER_TITLE" => array(
            "PARENT" => "SEO_SETTINGS",
            "NAME" => "Устанавливать заголовок окна браузера",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SET_META_KEYWORDS" => array(
            "PARENT" => "SEO_SETTINGS",
            "NAME" => "Устанавливать мета-тег keywords",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SET_META_DESCRIPTION" => array(
            "PARENT" => "SEO_SETTINGS",
            "NAME" => "Устанавливать мета-тег description",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SET_CANONICAL_URL" => array(
            "PARENT" => "SEO_SETTINGS",
            "NAME" => "Устанавливать канонический URL",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SET_LAST_MODIFIED" => array(
            "PARENT" => "SEO_SETTINGS",
            "NAME" => "Устанавливать дату последнего изменения",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        
        // Настройки свойств
        "PROPERTY_CODE" => array(
            "PARENT" => "PROPERTY_SETTINGS",
            "NAME" => "Коды свойств",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "DEFAULT" => array("MANUFACTURER", "CRYPTO", "ALGORITHM", "HASHRATE", "EFFICIENCY", "POWER", "GUARANTEE", "HIT", "MORE_PHOTO"),
        ),
        
        // Настройки цен
        "PRICE_CODE" => array(
            "PARENT" => "PRICE_SETTINGS",
            "NAME" => "Типы цен",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "DEFAULT" => array("BASE"),
        ),
        "USE_PRICE_COUNT" => array(
            "PARENT" => "PRICE_SETTINGS",
            "NAME" => "Использовать вывод цен с диапазонами",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "SHOW_PRICE_COUNT" => array(
            "PARENT" => "PRICE_SETTINGS",
            "NAME" => "Выводить цены для количества",
            "TYPE" => "STRING",
            "DEFAULT" => "1",
        ),
        "PRICE_VAT_INCLUDE" => array(
            "PARENT" => "PRICE_SETTINGS",
            "NAME" => "Включать НДС в цену",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        
        // Настройки кеширования
        "CACHE_TYPE" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Тип кеширования",
            "TYPE" => "LIST",
            "VALUES" => array(
                "A" => "Авто + Управляемое",
                "Y" => "Кешировать",
                "N" => "Не кешировать",
            ),
            "DEFAULT" => "A",
        ),
        "CACHE_TIME" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Время кеширования (сек.)",
            "TYPE" => "STRING",
            "DEFAULT" => "36000000",
        ),
        "CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Учитывать права доступа",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        
        // Настройки 404
        "SET_STATUS_404" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Устанавливать статус 404",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SHOW_404" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Показывать 404 страницу",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "FILE_404" => array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Файл 404 страницы",
            "TYPE" => "STRING",
            "DEFAULT" => "/404.php",
        ),
    ),
);
?>
