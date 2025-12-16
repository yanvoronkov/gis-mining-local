<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - invest_catalog
 * Страница списка разделов (sections.php)
 * 
 * Для контентных инфоблоков показываем все элементы сразу
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

// Просто подключаем section.php для отображения всех элементов
include(__DIR__ . '/section.php');