<?php
/**
 * Диагностический скрипт для проверки товаров из CSV
 * Проверяет, в каких инфоблоках находятся товары
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!\Bitrix\Main\Loader::includeModule('iblock') || !\Bitrix\Main\Loader::includeModule('catalog')) {
    die("Не удалось загрузить модули iblock или catalog");
}

// ID товаров из вашего CSV, которые не были найдены
$missingIds = [
    99580, 99607, 99604, 99608, 99620, 99621, 99622, 
    99595, 99624, 99623, 99625, 99628, 99629, 99630,
    99632, 99633, 99626, 99627, 99631, 99579
];

echo "<h2>Проверка товаров из CSV</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID товара</th><th>Найден</th><th>Инфоблок ID</th><th>Название инфоблока</th><th>Название товара</th></tr>";

foreach ($missingIds as $productId) {
    // Ищем товар во ВСЕХ инфоблоках (без фильтра по IBLOCK_ID)
    $dbItem = CIBlockElement::GetList(
        [],
        ['ID' => $productId],
        false,
        ['nTopCount' => 1],
        ['ID', 'IBLOCK_ID', 'NAME']
    );
    
    if ($item = $dbItem->Fetch()) {
        // Получаем название инфоблока
        $dbIBlock = CIBlock::GetByID($item['IBLOCK_ID']);
        $iblock = $dbIBlock->Fetch();
        
        echo "<tr style='background: #d4edda;'>";
        echo "<td>{$productId}</td>";
        echo "<td>✓ ДА</td>";
        echo "<td>{$item['IBLOCK_ID']}</td>";
        echo "<td>{$iblock['NAME']}</td>";
        echo "<td>{$item['NAME']}</td>";
        echo "</tr>";
    } else {
        echo "<tr style='background: #f8d7da;'>";
        echo "<td>{$productId}</td>";
        echo "<td>✗ НЕТ</td>";
        echo "<td colspan='3'>Товар не найден в базе данных</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<hr>";
echo "<h3>Список всех инфоблоков в системе:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Название</th><th>API код</th><th>Тип</th></tr>";

$dbIBlocks = CIBlock::GetList(
    ['SORT' => 'ASC'],
    [],
    true
);

while ($iblock = $dbIBlocks->Fetch()) {
    echo "<tr>";
    echo "<td>{$iblock['ID']}</td>";
    echo "<td>{$iblock['NAME']}</td>";
    echo "<td>{$iblock['CODE']}</td>";
    echo "<td>{$iblock['IBLOCK_TYPE_ID']}</td>";
    echo "</tr>";
}

echo "</table>";

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>

