<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - tech_catalog
 * Страница списка разделов (sections.php)
 * 
 * Для каталогов ASIC/Videocard показываем все товары сразу
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

// Просто подключаем section.php для отображения всех товаров
include(__DIR__ . '/section.php');