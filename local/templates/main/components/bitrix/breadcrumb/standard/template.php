<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/**
 * Стандартный шаблон хлебных крошек Битрикса
 * Использует правильную логику с файлами .section.php и AddChainItem()
 */

global $APPLICATION;

// Не выводим ничего, если хлебных крошек нет
if (empty($arResult)) {
    return "";
}

// Проверяем исключенные страницы
$excludePages = array_map('trim', explode(',', $arParams["EXCLUDE_PAGES"] ?: '404.php'));
$currentPageFile = basename($APPLICATION->GetCurPage(false));

if (in_array($currentPageFile, $excludePages)) {
    return "";
}

// Проверяем 404 страницы
if (
    http_response_code() === 404 ||
    (defined('ERROR_404') && ERROR_404 === true) ||
    (isset($APPLICATION->arResult['ERROR_404']) && $APPLICATION->arResult['ERROR_404'] === true)
) {
    return "";
}

// --- ГЕНЕРАЦИЯ JSON-LD МИКРОРАЗМЕТКИ ---
$itemListElement = [];
$itemCount = count($arResult);

for ($index = 0; $index < $itemCount; ++$index) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);
    $link = $arResult[$index]["LINK"];

    // Формируем элемент списка
    $listItem = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title
    ];

    // Формируем полный URL
    $protocol = CMain::IsHTTPS() ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $cleanDomain = preg_replace('/:\d+$/', '', $domain);

    // Если есть ссылка - используем её, иначе текущий URL страницы
    if ($link <> "") {
        $listItem['item'] = $protocol . '://' . $cleanDomain . $link;
    } else {
        // Для последнего элемента (текущей страницы) используем полный URL
        $currentUri = $_SERVER['REQUEST_URI'];
        $listItem['item'] = $protocol . '://' . $cleanDomain . $currentUri;
    }

    $itemListElement[] = $listItem;
}

// --- ВЫВОД СКРИПТА С МИКРОРАЗМЕТКОЙ ---
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $itemListElement
];

// Вывод схемы через ViewContent (будет показано в footer.php)
$APPLICATION->AddViewContent('json_ld_schemas', '<script type="application/ld+json">' . "\n" .
    json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
    "\n" . '</script>' . "\n");


// --- ВЫВОД ВИЗУАЛЬНЫХ ХЛЕБНЫХ КРОШЕК ---
$strReturn = '<nav class="breadcrumbs container" aria-label="Хлебные крошки">';

for ($index = 0; $index < $itemCount; ++$index) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);
    $link = $arResult[$index]["LINK"];
    $isLast = ($index === $itemCount - 1);

    // Выводим элемент (со ссылкой или без)
    if ($link <> "" && !$isLast) {
        // Элемент со ссылкой (не последний)
        $strReturn .= '
            <a href="' . $link . '" class="breadcrumbs__item">
                <span>' . $title . '</span>
            </a>';
    } else {
        // Элемент без ссылки (последний или без ссылки)
        $class = $isLast ? 'breadcrumbs__item breadcrumbs__item--current' : 'breadcrumbs__item';
        $strReturn .= '
            <span class="' . $class . '">
                <span>' . $title . '</span>
            </span>';
    }

    // Разделитель добавляется после всех элементов, кроме последнего
    if (!$isLast && $arParams["SHOW_SEPARATOR"] !== "N") {
        $separator = $arParams["SEPARATOR"] ?: '/';
        $strReturn .= '<span class="breadcrumbs__separator" aria-hidden="true">' . htmlspecialcharsex($separator) . '</span>';
    }
}

$strReturn .= '</nav>';

// Возвращаем HTML крошек для отображения на странице
return $strReturn;
?>