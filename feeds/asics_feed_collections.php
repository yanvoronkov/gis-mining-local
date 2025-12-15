<?php
/**
 * Генерация ASIC-фида с динамическими коллекциями по vendor.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sourceUrl  = 'https://89.111.154.117/bitrix/catalog_export/product-feed-artem.php';
$outputFile = __DIR__ . '/feed_asics.xml';

// Загрузка исходного XML
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => ['timeout' => 15]
]);

$xmlContent = file_get_contents($sourceUrl, false, $context);
if (!$xmlContent) {
    die("Ошибка загрузки XML: $sourceUrl");
}

// Убираем BOM
$xmlContent = preg_replace("/^\xEF\xBB\xBF/", "", $xmlContent);

// Парсим XML
libxml_use_internal_errors(true);
$xml = simplexml_load_string($xmlContent);

if (!$xml || !isset($xml->shop->offers)) {
    die("Ошибка XML");
}

// ===============================
// Добавление "ASIC-майнер" в начало <name>
// ===============================
foreach ($xml->shop->offers->offer as $offer) {
    if (isset($offer->name)) {
        $oldName = trim((string)$offer->name);

        // Добавляем только если ещё нет
        if (stripos($oldName, 'ASIC-майнер') !== 0) {
            $offer->name = "ASIC-майнер " . $oldName;
        }
    }
}




// ===============================
// Список коллекций (динамический)
// ===============================

$collections = [];
$hasNoVendor = false;

// ===============================
// Обработка offers
// ===============================
foreach ($xml->shop->offers->offer as $offer) {

    // Исправление URL
    if (isset($offer->url)) {
        $offer->url = str_replace('/catalog/videocard/', '/catalog/product/', (string)$offer->url);
    }

    // vendor → collectionId
    $vendor = trim((string)$offer->vendor);
    $vendorId = strtolower($vendor);

    if ($vendor === "") {
        // Без вендора
        $offer->addChild('collectionId', 'no_vendor');
        $hasNoVendor = true;
    } else {
        // Добавляем динамически коллекцию
        $collections[$vendorId] = $vendor;

        // Привязка offer к производителю
        $offer->addChild('collectionId', $vendorId);
    }

    // full_catalog всегда
    $offer->addChild('collectionId', 'full_catalog');
}

// === Обновляем дату в фиде ===
$now = date("Y-m-d H:i");
$xml['date'] = $now;

// ===============================
// Формируем блок <collections>
// ===============================

// Если старый блок есть — удаляем
if (isset($xml->shop->collections)) {
    unset($xml->shop->collections);
}

$collectionsNode = $xml->shop->addChild('collections');

// Добавляем коллекции производителей
foreach ($collections as $id => $name) {
    $col = $collectionsNode->addChild('collection');
    $col->addAttribute('id', $id);
    $col->addAttribute('name', strtoupper($name));
}

// Добавляем no_vendor (если есть товары без vendor)
if ($hasNoVendor) {
    $col = $collectionsNode->addChild('collection');
    $col->addAttribute('id', 'no_vendor');
    $col->addAttribute('name', 'Без вендора');
}

// Добавляем full_catalog
$col = $collectionsNode->addChild('collection');
$col->addAttribute('id', 'full_catalog');
$col->addAttribute('name', 'Асики');

// ===============================
// Чистый XML вывод (без двойной декларации)
// ===============================

$dom = new DOMDocument("1.0", "UTF-8");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML(), LIBXML_NOBLANKS);

// финальный xml без повторной декларации
$outputXml = $dom->saveXML($dom->documentElement);

// Удаляем возможный BOM
$outputXml = preg_replace("/^\xEF\xBB\xBF/", "", $outputXml);

// Вывод
header('Content-Type: application/xml; charset=UTF-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo $outputXml;

file_put_contents($outputFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$outputXml);
exit;
?>
