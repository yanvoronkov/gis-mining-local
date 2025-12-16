<?php
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;

header('Content-Type: application/json; charset=utf-8');

// Логирование для отладки (можно убрать после исправления)
$debugLog = [];
$debugLog['step1'] = 'AJAX script started';

// Подключаем модули
if (!Loader::includeModule('iblock')) {
    echo json_encode(['success' => false, 'error' => 'Модуль iblock не подключен', 'debug' => $debugLog]);
    die();
}

if (!Loader::includeModule('catalog')) {
    echo json_encode(['success' => false, 'error' => 'Модуль catalog не подключен', 'debug' => $debugLog]);
    die();
}

if (!Loader::includeModule('search')) {
    echo json_encode(['success' => false, 'error' => 'Модуль search не подключен', 'debug' => $debugLog]);
    die();
}

$debugLog['step2'] = 'Modules loaded';

// Проверяем SearchHelper
if (!class_exists('SearchHelper')) {
    echo json_encode(['success' => false, 'error' => 'SearchHelper не найден', 'debug' => $debugLog]);
    die();
}

$debugLog['step3'] = 'SearchHelper exists';

$request = Application::getInstance()->getContext()->getRequest();
$query = trim($request->getPost('query'));
$iblockIds = $request->getPost('iblock_ids');
$minLength = intval($request->getPost('min_length')) ?: SearchConfig::get('MIN_QUERY_LENGTH');
$maxResults = intval($request->getPost('max_results')) ?: 10;
$showPrice = $request->getPost('show_price') === 'Y';

$debugLog['query'] = $query;
$debugLog['query_length'] = strlen($query);
$debugLog['min_length'] = $minLength;

// Валидация
if (strlen($query) < $minLength) {
    echo json_encode(['success' => false, 'error' => 'Запрос слишком короткий', 'debug' => $debugLog]);
    die();
}

if (!is_array($iblockIds) || empty($iblockIds)) {
    $iblockIds = IBLOCK_IDS_ALL_CATALOG;
}

$debugLog['iblock_ids'] = $iblockIds;

// Выполняем поиск через SearchHelper
try {
    $searchResult = SearchHelper::searchProducts($query, [
        'IBLOCK_IDS' => $iblockIds,
        'MIN_LENGTH' => $minLength,
        'MAX_RESULTS' => $maxResults,
    ]);

    $enrichedProducts = $searchResult['items'];
    $debugLog['found_products'] = $searchResult['total'];

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка SearchHelper: ' . $e->getMessage(),
        'debug' => $debugLog
    ]);
    die();
}

// Формируем результат для AJAX
$arItems = [];
foreach ($enrichedProducts as $product) {
    $arItems[] = [
        'id' => $product['ID'],
        'name' => $product['NAME'],
        'url' => $product['DETAIL_PAGE_URL'],
        'image' => $product['IMAGE'],
        'price' => $showPrice ? $product['PRICE'] : '',
        'iblock_name' => $product['IBLOCK_NAME'],
        'match_type' => 'name', // Оставляем для совместимости, хотя логика теперь внутри
        'relevance' => $product['RELEVANCE'] ?? 0,
    ];
}

echo json_encode([
    'success' => true,
    'items' => $arItems,
    'total' => count($arItems),
    'debug' => $debugLog, // Временно для отладки
], JSON_UNESCAPED_UNICODE);
