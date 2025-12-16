<?php
/**
 * Константы для ID инфоблоков каталога
 * Использовать вместо магических чисел во всем коде
 */

// Товарные инфоблоки (с функционалом eCommerce)
define('IBLOCK_CATALOG_ASICS', 1);
define('IBLOCK_CATALOG_VIDEOCARD', 11);

// Нетоварные инфоблоки (контентные)
define('IBLOCK_CONTENT_GPU', 5);
define('IBLOCK_CONTENT_INVESTICII', 4);
define('IBLOCK_CONTENT_CONTAINERS', 6);
define('IBLOCK_CONTENT_BUSINESS', 3);
define('IBLOCK_CONTENT_HOSTING', 2); // Размещение

// Группы инфоблоков для удобной проверки
define('IBLOCK_IDS_PRODUCT', [IBLOCK_CATALOG_ASICS, IBLOCK_CATALOG_VIDEOCARD]);
define('IBLOCK_IDS_CONTENT', [
    IBLOCK_CONTENT_GPU,
    IBLOCK_CONTENT_INVESTICII,
    IBLOCK_CONTENT_CONTAINERS,
    IBLOCK_CONTENT_BUSINESS,
    IBLOCK_CONTENT_HOSTING
]);
define('IBLOCK_IDS_ALL_CATALOG', array_merge(IBLOCK_IDS_PRODUCT, IBLOCK_IDS_CONTENT));

// ====================================================================
// ШАБЛОНЫ КОМПОНЕНТОВ
// ====================================================================
// Шаблон пагинации для всего сайта (каталог, блог, новости, кейсы)
define('PAGER_TEMPLATE', '.default');
