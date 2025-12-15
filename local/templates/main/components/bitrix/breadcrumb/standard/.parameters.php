<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "GROUPS" => array(
        "SEO" => array(
            "NAME" => "SEO настройки",
            "SORT" => 100,
        ),
        "VISUAL" => array(
            "NAME" => "Визуальные настройки",
            "SORT" => 200,
        ),
    ),
    "PARAMETERS" => array(
        "SHOW_HOME" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Показывать ссылку на главную",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "HOME_TEXT" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Текст ссылки на главную",
            "TYPE" => "STRING",
            "DEFAULT" => "Главная",
        ),
        "SHOW_SEPARATOR" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Показывать разделители",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SEPARATOR" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Разделитель",
            "TYPE" => "STRING",
            "DEFAULT" => "/",
        ),
        "EXCLUDE_PAGES" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Исключить страницы (через запятую)",
            "TYPE" => "STRING",
            "DEFAULT" => "404.php",
            "DESCRIPTION" => "Укажите страницы, на которых не показывать хлебные крошки",
        ),
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
            "DEFAULT" => "3600",
        ),
    ),
);
?>
