<?php
// ======================================================================
// Файл: /local/scripts/update_prices_from_google.php
// Финальная версия. Содержит ТОЛЬКО функцию для агента.
// ======================================================================

/**
 * Главная функция-агент для обновления цен из Google Sheets.
 * Эта функция будет вызываться агентом Битрикса.
 * @return string - Имя функции для следующего запуска агента.
 */
function UpdatePricesAgent() {

    // Подключаем нужные модули прямо внутри функции для надежности
    if (!\Bitrix\Main\Loader::includeModule('iblock') || !\Bitrix\Main\Loader::includeModule('catalog')) {
        error_log("Google Sheets Price Update (Agent): Modules 'iblock' or 'catalog' not loaded.");
        return "UpdatePricesAgent();"; // Повторить через час
    }

    // --- Настройки ---
    $googleSheetUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vS3ZNwvIHIrnqfxlMgNFQJltIHLfYnAaWIQVS0eaGdvAROsmuak4x4tAZefiW4lh7gx6J99AaplivIZ/pub?gid=991566321&single=true&output=csv';
    $iblockIdsToSearch = [1, 6]; // !!! УКАЖИТЕ ID ВСЕХ ВАШИХ ИНФОБЛОКОВ С ТОВАРАМИ !!!
    $priceTypeId = 1; // !!! УКАЖИТЕ ID ВАШЕГО ТИПА ЦЕНЫ ("Базовая" или "Розничная") !!!
    // --- Конец настроек ---

    $logDetails = ["--- Запуск агента " . date("d.m.Y H:i:s") . " ---"];

    // 1. Скачиваем CSV-файл
    $csvData = file_get_contents($googleSheetUrl);
    if ($csvData === false) {
        $logDetails[] = "КРИТИЧЕСКАЯ ОШИБКА: Не удалось скачать CSV по ссылке.";
        \Bitrix\Main\Diag\Debug::writeToFile(implode("\n", $logDetails), "", "update_prices.log");
        return "UpdatePricesAgent();";
    }

    // 2. Надежный парсинг CSV с обработкой многострочных записей
    $csvDataNormalized = str_replace(["\r\n", "\r"], "\n", $csvData);
    
    // Используем более надежный метод парсинга CSV
    $tempFile = tempnam(sys_get_temp_dir(), 'csv_parse_');
    file_put_contents($tempFile, $csvDataNormalized);
    
    $csvHandle = fopen($tempFile, 'r');
    if (!$csvHandle) {
        $logDetails[] = "ОШИБКА: Не удалось открыть временный файл для парсинга CSV.";
        \Bitrix\Main\Diag\Debug::writeToFile(implode("\n", $logDetails), "", "update_prices.log");
        return "UpdatePricesAgent();";
    }
    
    // Читаем заголовки
    $headers = fgetcsv($csvHandle, 0, ',', '"', '\\');
    if (!$headers || count($headers) < 3) {
        fclose($csvHandle);
        unlink($tempFile);
        $logDetails[] = "ОШИБКА: CSV-файл не содержит корректных заголовков.";
        \Bitrix\Main\Diag\Debug::writeToFile(implode("\n", $logDetails), "", "update_prices.log");
        return "UpdatePricesAgent();";
    }
    
    $headers = array_map('trim', $headers);
    $headers = array_map('strtolower', $headers);

    $logDetails[] = "Получены заголовки из CSV: " . implode(', ', $headers);

    $updatedCount = 0;
    $skippedCount = 0;
    $notFoundCount = 0;
    $deletedCount = 0;
    $lineNumber = 1; // Начинаем с 1, так как заголовки уже прочитаны

    // Читаем данные построчно
    while (($rowData = fgetcsv($csvHandle, 0, ',', '"', '\\')) !== false) {
        $lineNumber++;
        
        // Проверяем количество колонок
        if (count($headers) !== count($rowData)) {
            $logDetails[] = "ОШИБКА в строке " . $lineNumber . ": Количество колонок не совпадает с заголовками (ожидалось " . count($headers) . ", получено " . count($rowData) . "). Строка: " . substr(implode(',', $rowData), 0, 100) . "...";
            $skippedCount++;
            continue;
        }
        
        $row = array_combine($headers, $rowData);

        $productID = (int)($row['id'] ?? 0);
        $price = (float)preg_replace('/[^\d.]/', '', str_replace(',', '.', $row['price'] ?? '0'));
        $shouldUpdate = strtoupper(trim($row['statusdownload'] ?? 'FALSE'));

        $logDetails[] = "--- Обработка строки " . $lineNumber . ": id={$productID}, price={$price}, statusdownload={$shouldUpdate} ---";

        // Проверка на корректность ID
        if ($productID <= 0) {
            $skippedCount++;
            $logDetails[] = "Пропущено: некорректный ID товара.";
            continue;
        }

        // 3. Ищем товар в Битриксе сразу в нескольких инфоблоках
        $dbItem = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockIdsToSearch, 'ID' => $productID],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID']
        );

        if ($item = $dbItem->Fetch()) {
            // Если статус FALSE - удаляем цену товара
            if ($shouldUpdate === 'FALSE') {
                $dbPrice = CPrice::GetList([], ["PRODUCT_ID" => $productID, "CATALOG_GROUP_ID" => $priceTypeId]);
                if ($arPrice = $dbPrice->Fetch()) {
                    CPrice::Delete($arPrice["ID"]);
                    $deletedCount++;
                    $logDetails[] = "УДАЛЕНО: Цена для товара ID={$productID} (из инфоблока {$item['IBLOCK_ID']}) была удалена (statusdownload=FALSE).";
                } else {
                    $logDetails[] = "Товар ID={$productID} со статусом FALSE не имел цены, удалять нечего.";
                }
                continue;
            }

            // Если статус TRUE и цена корректна - обновляем цену товара
            if ($shouldUpdate === 'TRUE' && $price > 0) {
                $arFields = ["PRODUCT_ID" => $productID, "CATALOG_GROUP_ID" => $priceTypeId, "PRICE" => $price, "CURRENCY" => "RUB"];
                $dbPrice = CPrice::GetList([], ["PRODUCT_ID" => $productID, "CATALOG_GROUP_ID" => $priceTypeId]);
                if ($arPrice = $dbPrice->Fetch()) {
                    CPrice::Update($arPrice["ID"], $arFields);
                } else {
                    CPrice::Add($arFields);
                }
                $updatedCount++;
                $logDetails[] = "УСПЕХ: Цена для товара ID={$productID} (из инфоблока {$item['IBLOCK_ID']}) обновлена на {$price}.";
            } else {
                // Статус не TRUE и не FALSE, или цена некорректна
                $skippedCount++;
                $logDetails[] = "Пропущено: статус не TRUE/FALSE или некорректная цена.";
            }
        } else {
            $notFoundCount++;
            $logDetails[] = "ПРЕДУПРЕЖДЕНИЕ: Товар с ID={$productID} не найден ни в одном из инфоблоков: " . implode(', ', $iblockIdsToSearch);
        }
    }
    
    // Закрываем файл и удаляем временный файл
    fclose($csvHandle);
    unlink($tempFile);

    // 5. Записываем финальный отчет в лог
    $logMessage = "=== ОБНОВЛЕНИЕ ЦЕН ИЗ GOOGLE SHEETS ===\n";
    $logMessage .= "Дата запуска: " . date("d.m.Y H:i:s") . "\n";
    $logMessage .= "Инфоблоки для поиска: " . implode(', ', $iblockIdsToSearch) . "\n";
    $logMessage .= "Тип цены: $priceTypeId\n\n";
    $logMessage .= "РЕЗУЛЬТАТЫ:\n";
    $logMessage .= " ✓ Обновлено: $updatedCount\n";
    $logMessage .= " 🗑 Удалено (statusdownload=FALSE): $deletedCount\n";
    $logMessage .= " ⚠ Пропущено (некорректные данные): $skippedCount\n";
    $logMessage .= " ✗ Не найдено в базе: $notFoundCount\n";
    $logMessage .= "\n" . str_repeat("=", 60) . "\nДЕТАЛЬНЫЙ ЛОГ:\n" . str_repeat("=", 60) . "\n" . implode("\n", $logDetails);

    \Bitrix\Main\Diag\Debug::writeToFile($logMessage, "", "update_prices.log");

    // Обязательно возвращаем имя функции для следующего запуска агента
    return "UpdatePricesAgent();";
}
?>