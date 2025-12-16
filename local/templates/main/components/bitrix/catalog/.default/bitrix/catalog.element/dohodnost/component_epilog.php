<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arResult */
/** @global CMain $APPLICATION */
global $APPLICATION;

// Получаем данные из глобальной переменной
global $arResult;
if (empty($arResult)) {
    // Попробуем получить данные из компонента, если они доступны
    if (isset($this) && !empty($this->arResult)) {
        $arResult = $this->arResult;
    }
}

// Если по какой-то причине $arResult пуст, ничего не делаем
if (empty($arResult) || !isset($arResult['NAME'])) {
    return;
}

// ВАЖНО: Очищаем дополнительную цепочку хлебных крошек,
// т.к. стандартный компонент мог добавить некорректные элементы
$APPLICATION->arAdditionalChain = [];

// --- Специфичные настройки для страницы доходности ---
// ПРИМЕЧАНИЕ: Title и Description уже установлены в template.php
// Здесь мы используем их только для формирования OG и Twitter тегов
$productNamePrefix = "Доходность ";
$pageTitle = $productNamePrefix . $arResult['NAME'];
$pageDescription = "Калькулятор прибыльности асика " . $arResult['NAME'] . " - GIS-MINING 2025";

// Определяем раздел каталога на основе IBLOCK_ID товара
$catalogSection = '';
$catalogUrl = '';

switch ($arResult['IBLOCK_ID']) {
    case 1: // ASIC-майнеры
        $catalogSection = 'ASIC-майнеры';
        $catalogUrl = '/catalog/asics/';
        break;
    case 3: // GPU-майнеры
        $catalogSection = 'GPU-майнеры';
        $catalogUrl = '/catalog/gpu/';
        break;
    case 11: // Видеокарты
        $catalogSection = 'Видеокарты';
        $catalogUrl = '/catalog/videocard/';
        break;
    case 5: // Контейнеры
        $catalogSection = 'Контейнеры';
        $catalogUrl = '/catalog/conteynery/';
        break;
    case 6: // Готовый бизнес
        $catalogSection = 'Готовый бизнес';
        $catalogUrl = '/catalog/gotovyy-biznes/';
        break;
    case 7: // Инвестиции
        $catalogSection = 'Инвестиции';
        $catalogUrl = '/catalog/investicii/';
        break;
    default:
        // По умолчанию используем "Каталог"
        $catalogSection = 'Каталог';
        $catalogUrl = '/catalog/';
}

// Проверяем URL страницы, чтобы понять, нужно ли добавлять раздел каталога
$currentUrl = $APPLICATION->GetCurDir();
$needToAddSection = true;

// Если URL содержит путь к разделу каталога, значит раздел уже добавлен автоматически
switch ($arResult['IBLOCK_ID']) {
    case 1: // ASIC-майнеры
        if (strpos($currentUrl, '/catalog/asics/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 3: // GPU-майнеры
        if (strpos($currentUrl, '/catalog/gpu/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 11: // Видеокарты
        if (strpos($currentUrl, '/catalog/videocard/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 5: // Контейнеры
        if (strpos($currentUrl, '/catalog/conteynery/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 6: // Готовый бизнес
        if (strpos($currentUrl, '/catalog/gotovyy-biznes/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 7: // Инвестиции
        if (strpos($currentUrl, '/catalog/investicii/') !== false) {
            $needToAddSection = false;
        }
        break;
}

// Добавляем раздел каталога в хлебные крошки только если он еще не добавлен автоматически
if (!empty($catalogSection) && $needToAddSection) {
    $APPLICATION->AddChainItem($catalogSection, $catalogUrl);
}

// Добавляем товар в хлебные крошки (со ссылкой на обычную страницу товара, без /calculator-dohodnosti/)
// Формируем правильную ссылку, убирая /calculator-dohodnosti/ из текущего URL
$currentDetailUrl = $arResult['DETAIL_PAGE_URL'] ?? '';

// Пробуем разные варианты получения CODE
$elementCode = '';
if (!empty($arResult['CODE'])) {
    $elementCode = $arResult['CODE'];
} elseif (!empty($_REQUEST['ELEMENT_CODE'])) {
    $elementCode = $_REQUEST['ELEMENT_CODE'];
} elseif (preg_match('#/([^/]+)/calculator-dohodnosti/?$#', $currentDetailUrl, $matches)) {
    $elementCode = $matches[1];
} elseif (preg_match('#/([^/]+)/?$#', $currentDetailUrl, $matches)) {
    // Если в URL нет calculator-dohodnosti, просто берем последний сегмент
    $elementCode = $matches[1];
}

// Определяем базовый путь в зависимости от IBLOCK_ID
$basePath = '/catalog/';
switch ($arResult['IBLOCK_ID']) {
    case 1: // ASIC-майнеры
        $basePath = '/catalog/asics/';
        break;
    case 3: // GPU-майнеры
        $basePath = '/catalog/gpu/';
        break;
    case 11: // Видеокарты
        $basePath = '/catalog/videocard/';
        break;
    case 5: // Контейнеры
        $basePath = '/catalog/conteynery/';
        break;
    case 6: // Готовый бизнес
        $basePath = '/catalog/gotovyy-biznes/';
        break;
    case 7: // Инвестиции
        $basePath = '/catalog/investicii/';
        break;
}

// Формируем ссылку на обычную страницу товара
if ($elementCode) {
    $baseDetailUrl = $basePath . $elementCode . '/';
} else {
    // Если не удалось получить CODE, просто убираем /calculator-dohodnosti/ из URL
    $baseDetailUrl = preg_replace('#/calculator-dohodnosti/?$#', '/', $currentDetailUrl);
    // Если все еще пусто, используем базовый путь каталога
    if (empty($baseDetailUrl) || $baseDetailUrl === '/') {
        $baseDetailUrl = $basePath;
    }
}

// Убеждаемся, что ссылка не пустая
if (!empty($baseDetailUrl)) {
    $APPLICATION->AddChainItem($arResult['NAME'], $baseDetailUrl);
} else {
    // Если ссылка все еще пустая, добавляем товар без ссылки
    $APPLICATION->AddChainItem($arResult['NAME']);
}

// Добавляем "Калькулятор доходности" в конец цепочки (без URL, так как это текущая страница)
$APPLICATION->AddChainItem("Калькулятор доходности");

// Устанавливаем Open Graph теги на основе данных товара
$siteName = COption::GetOptionString("main", "site_name", "GIS Mining");

// Определяем полный URL страницы (с /calculator-dohodnosti/)
$protocol = CMain::IsHTTPS() ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$cleanDomain = preg_replace('/:\d+$/', '', $domain);
$detailPageUrl = rtrim($arResult['DETAIL_PAGE_URL'], '/') . '/calculator-dohodnosti/';
$fullPageUrl = $protocol . '://' . $cleanDomain . $detailPageUrl;

// Формируем URL изображения
$imageUrl = !empty($arResult['DETAIL_PICTURE']['SRC'])
    ? $protocol . '://' . $cleanDomain . $arResult['DETAIL_PICTURE']['SRC']
    : '';

// Open Graph теги
$APPLICATION->SetPageProperty('og:title', $pageTitle);
$APPLICATION->SetPageProperty('og:description', $pageDescription);
$APPLICATION->SetPageProperty('og:type', 'product');
$APPLICATION->SetPageProperty('og:url', $fullPageUrl);
if ($imageUrl) {
    $APPLICATION->SetPageProperty('og:image', $imageUrl);
}
$APPLICATION->SetPageProperty('og:site_name', $siteName);

// Twitter Card теги
$APPLICATION->SetPageProperty('twitter:card', 'summary_large_image');
$APPLICATION->SetPageProperty('twitter:title', $pageTitle);
$APPLICATION->SetPageProperty('twitter:description', $pageDescription);
if ($imageUrl) {
    $APPLICATION->SetPageProperty('twitter:image', $imageUrl);
}

// Product schema теперь генерируется в result_modifier.php, где доступны полные данные $arResult

