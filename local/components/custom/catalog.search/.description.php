<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
    "NAME" => "Поиск по каталогу",
    "DESCRIPTION" => "Компонент живого поиска товаров по всему каталогу с AJAX-подгрузкой результатов",
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "custom",
        "NAME" => "Пользовательские компоненты",
        "CHILD" => array(
            "ID" => "catalog_search",
            "NAME" => "Поиск по каталогу",
        )
    ),
);

