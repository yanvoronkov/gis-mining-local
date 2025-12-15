<?php
/**
 * Генерация нового фида с исправлением ссылок и добавлением коллекций.
 * Рабочий вариант: исправляет /catalog/videocard/ → /catalog/product/
 * и сохраняет оригинальную структуру.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sourceUrl  = 'https://89.111.154.117/bitrix/catalog_export/videocards.php';
$outputFile = __DIR__ . '/videocards_collections.xml';

// Загружаем исходный XML
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => ['timeout' => 15]
]);
$xmlContent = file_get_contents($sourceUrl, false, $context);
if (!$xmlContent) {
    header('Content-Type: text/plain; charset=UTF-8');
    die("Ошибка: не удалось загрузить исходный XML по адресу $sourceUrl");
}

// Убираем BOM и нормализуем кодировку
$xmlContent = preg_replace("/^\xEF\xBB\xBF/", '', $xmlContent);
$encoding = mb_detect_encoding($xmlContent, ['UTF-8', 'Windows-1251', 'ISO-8859-1'], true);
if ($encoding && $encoding !== 'UTF-8') {
    $xmlContent = iconv($encoding, 'UTF-8//IGNORE', $xmlContent);
}
if (!str_contains($xmlContent, 'encoding="UTF-8"')) {
    $xmlContent = preg_replace(
        '/<\?xml([^>]*)\?>/i',
        '<?xml version="1.0" encoding="UTF-8"?>',
        $xmlContent,
        1
    );
}

// Парсим XML
libxml_use_internal_errors(true);
$xml = simplexml_load_string($xmlContent);
if (!$xml || !isset($xml->shop)) {
    header('Content-Type: text/plain; charset=UTF-8');
    die("Ошибка: не удалось разобрать XML — возможно, повреждён файл.");
}

// === Основная логика ===
if (isset($xml->shop->offers->offer)) {
    foreach ($xml->shop->offers->offer as $offer) {

        // 1. Исправляем ссылки
        if (isset($offer->url)) {
            $oldUrl = (string)$offer->url;
            // заменяем только часть пути
            $newUrl = str_replace('/catalog/videocard/', '/catalog/product/', $oldUrl);
            $offer->url = $newUrl;
        }

        // 2. Добавляем <collectionId> если его нет
        if (!isset($offer->collectionId)) {
            $offer->addChild('collectionId', 'all');
        }
    }
}

// 3. Добавляем блок <collections>, если нет
if (!isset($xml->shop->collections)) {
    $collections = $xml->shop->addChild('collections');
    $collection  = $collections->addChild('collection');
    $collection->addAttribute('id', 'all');
    $collection->addChild('url', 'https://gis-mining.ru/catalog/videocard/');
    $collection->addChild('name', 'Купить видеокарты для ИИ, майнинга и 3D — NVIDIA, AMD, DGX');
    $collection->addChild('description', 'Каталог видеокарт от Gis Mining: NVIDIA RTX 4090, H100, H200, AMD Instinct, DGX-серии. Подходит для ИИ, машинного обучения, 3D-рендеринга и майнинга. Помощь в подборе, доставка по России.');
}

// === Сохраняем и выводим ===
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = false;
$dom->loadXML($xml->asXML(), LIBXML_NOBLANKS);

// Пытаемся сохранить в файл (не критично, если нет прав)
@file_put_contents($outputFile, $dom->saveXML());

// Отдаём в браузер
header('Content-Type: application/xml; charset=UTF-8');
echo $dom->saveXML();
exit;
?>
