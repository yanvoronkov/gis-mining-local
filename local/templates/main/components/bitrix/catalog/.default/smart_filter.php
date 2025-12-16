<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - tech_catalog
 * Страница фильтрации (smart_filter.php)
 * 
 * Вызывается когда URL содержит /filter/.../apply/
 * Просто включает section.php — вся логика фильтрации там.
 * 
 * @canonical Bitrix approach
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

// Подключаем section.php для отображения отфильтрованного списка
include(__DIR__ . '/section.php');
