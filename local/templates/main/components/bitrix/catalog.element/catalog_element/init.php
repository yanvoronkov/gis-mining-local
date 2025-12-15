<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// Подключаем основной файл компонента
require_once(__DIR__ . '/component.php');

// Создаем экземпляр компонента
$component = new CatalogElementComponent();
$component->arParams = $arParams;
$component->arResult = $arResult;
$component->executeComponent();
?>
