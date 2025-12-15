<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arComponentDescription = array(
    "NAME" => "Кастомная страница поиска",
    "DESCRIPTION" => "Страница поиска, использующая единый алгоритм с динамическим поиском",
    "ICON" => "/images/search.gif",
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "custom",
        "NAME" => "Custom Components",
    ),
);
