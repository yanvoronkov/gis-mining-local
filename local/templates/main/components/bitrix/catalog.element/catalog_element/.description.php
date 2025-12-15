<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => "Детальная страница товара",
    "DESCRIPTION" => "Компонент для отображения детальной страницы товара с метатегами и SEO",
    "ICON" => "/images/icon.gif",
    "CACHE_PATH" => "Y",
    "SORT" => 10,
    "PATH" => array(
        "ID" => "catalog",
        "NAME" => "Каталог",
        "CHILD" => array(
            "ID" => "catalog_element",
            "NAME" => "Элемент каталога",
            "SORT" => 10,
        ),
    ),
);
?>
