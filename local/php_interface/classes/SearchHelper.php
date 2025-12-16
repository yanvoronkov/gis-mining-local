<?php
/**
 * Хелпер для работы с поиском товаров
 * Единая точка для обогащения данных товаров (цены, картинки, свойства)
 */

use Bitrix\Main\Loader;

class SearchHelper
{
    /**
     * Конфигурация отображаемых свойств для каждого инфоблока
     */
    private static $displayProperties = [
        1 => ['MANUFACTURER', 'CRYPTO', 'ALGORITHM', 'HASHRATE', 'POWER'], // ASIC-майнеры
        3 => ['DEVICES_COUNT', 'YEARLY_PROFIT', 'PAYBACK_PERIOD'], // Готовый бизнес
        4 => ['INVESTMENT_TYPE', 'MIN_INVESTMENT'], // Инвестиции
        5 => ['MANUFACTURER', 'POWER'], // GPU
        6 => ['CONTAINER_SIZE', 'CAPACITY'], // Контейнеры
        11 => ['MANUFACTURER', 'CRYPTO', 'HASHRATE'], // Видеокарты
    ];

    /**
     * Названия инфоблоков (кеш)
     */
    private static $iblockNames = [];

    /**
     * Обогащает массив товаров данными (цена, картинка, свойства)
     * 
     * @param array $productIds Массив ID товаров
     * @return array Массив обогащенных данных
     */
    public static function enrichProducts(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        // Подключаем модули
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        $result = [];

        // Получаем базовые данные элементов
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_PAGE_URL',
        ];

        $rsElements = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            ['ID' => $productIds, 'ACTIVE' => 'Y'],
            false,
            false,
            $arSelect
        );

        while ($arElement = $rsElements->GetNext()) {
            $productId = $arElement['ID'];
            $iblockId = $arElement['IBLOCK_ID'];

            // Пропускаем товары без кода (они не могут иметь URL)
            if (empty($arElement['CODE'])) {
                continue;
            }

            $result[$productId] = [
                'ID' => $productId,
                'NAME' => $arElement['NAME'],
                'CODE' => $arElement['CODE'],
                'PREVIEW_TEXT' => $arElement['PREVIEW_TEXT'] ?? '',
                'DETAIL_PAGE_URL' => self::getProductUrl($arElement['CODE'], $iblockId),
                'IMAGE' => self::getProductImage($arElement),
                'PRICE' => self::getProductPrice($productId),
                'PROPERTIES' => self::getDisplayProperties($productId, $iblockId),
                'IBLOCK_ID' => $iblockId,
                'IBLOCK_NAME' => self::getIblockName($iblockId),
            ];
        }

        return $result;
    }

    /**
     * Получает изображение товара (оригинальный размер, как в каталоге)
     * 
     * @param array $arElement Массив элемента
     * @return string URL изображения
     */
    private static function getProductImage($arElement)
    {
        $imageId = null;

        // Приоритет: PREVIEW_PICTURE -> DETAIL_PICTURE
        if (!empty($arElement['PREVIEW_PICTURE'])) {
            $imageId = $arElement['PREVIEW_PICTURE'];
        } elseif (!empty($arElement['DETAIL_PICTURE'])) {
            $imageId = $arElement['DETAIL_PICTURE'];
        }

        if ($imageId) {
            try {
                // Получаем оригинальное изображение, без ресайза (как в каталоге)
                $imagePath = CFile::GetPath($imageId);
                if ($imagePath) {
                    return $imagePath;
                }
            } catch (Exception $e) {
                // В случае ошибки возвращаем пустую строку
            }
        }

        // Заглушка, если нет изображения
        return '';
    }

    /**
     * Получает цену товара
     * 
     * @param int $productId ID товара
     * @return string|null Форматированная цена или null
     */
    private static function getProductPrice($productId)
    {
        try {
            $arPrice = CCatalogProduct::GetOptimalPrice($productId, 1);

            if ($arPrice && isset($arPrice['RESULT_PRICE']['DISCOUNT_PRICE'])) {
                $price = $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
                if ($price > 0) {
                    return number_format($price, 0, '', ' ') . ' ₽';
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибки получения цены
        }

        return null;
    }

    /**
     * Получает только нужные свойства для конкретного инфоблока
     * 
     * @param int $productId ID товара
     * @param int $iblockId ID инфоблока
     * @return array Массив свойств
     */
    private static function getDisplayProperties($productId, $iblockId)
    {
        if (!isset(self::$displayProperties[$iblockId])) {
            return [];
        }

        $result = [];

        try {
            $rsProps = CIBlockElement::GetProperty($iblockId, $productId, [], []);

            while ($arProp = $rsProps->GetNext()) {
                if (
                    !empty($arProp['VALUE']) &&
                    !empty($arProp['CODE']) &&
                    in_array($arProp['CODE'], self::$displayProperties[$iblockId])
                ) {

                    // Для свойств-списков берем текстовое значение
                    $displayValue = $arProp['VALUE'];

                    if ($arProp['PROPERTY_TYPE'] == 'L' && !empty($arProp['VALUE_ENUM'])) {
                        // Свойство типа "Список" - берем текстовое значение
                        $displayValue = $arProp['VALUE_ENUM'];
                    } elseif ($arProp['PROPERTY_TYPE'] == 'S' && !empty($arProp['VALUE'])) {
                        // Строковое свойство
                        $displayValue = $arProp['VALUE'];
                    } elseif ($arProp['PROPERTY_TYPE'] == 'N' && !empty($arProp['VALUE'])) {
                        // Числовое свойство
                        $displayValue = $arProp['VALUE'];
                    }

                    $result[$arProp['CODE']] = [
                        'CODE' => $arProp['CODE'],
                        'NAME' => $arProp['NAME'],
                        'VALUE' => $displayValue,
                    ];
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибки получения свойств
        }

        return $result;
    }

    /**
     * Получает название инфоблока
     * 
     * @param int $iblockId ID инфоблока
     * @return string Название инфоблока
     */
    public static function getIblockName($iblockId)
    {
        if (!isset(self::$iblockNames[$iblockId])) {
            $arIblock = CIBlock::GetByID($iblockId)->Fetch();
            self::$iblockNames[$iblockId] = $arIblock['NAME'] ?? '';
        }

        return self::$iblockNames[$iblockId];
    }

    /**
     * Формирует URL товара в зависимости от инфоблока
     * 
     * @param string $code Символьный код товара
     * @param int $iblockId ID инфоблока
     * @return string URL товара
     */
    private static function getProductUrl($code, $iblockId)
    {
        $urlMap = [
            1 => '/catalog/asics/' . $code . '/',      // ASIC-майнеры
            5 => '/catalog/product/' . $code . '/',    // GPU
            11 => '/catalog/videocard/' . $code . '/', // Видеокарты
            3 => '/catalog/product/' . $code . '/',    // Готовый бизнес
            4 => '/catalog/product/' . $code . '/',    // Инвестиции
            6 => '/catalog/product/' . $code . '/',    // Контейнеры
        ];

        return $urlMap[$iblockId] ?? '/catalog/product/' . $code . '/';
    }
    /**
     * Выполняет поиск товаров
     * 
     * @param string $query Поисковый запрос
     * @param array $params Параметры поиска (IBLOCK_IDS, MIN_LENGTH, MAX_RESULTS, OFFSET)
     * @return array Результат поиска ['items' => [], 'total' => 0]
     */
    public static function searchProducts($query, $params = [])
    {
        $query = trim($query);
        $iblockIds = $params['IBLOCK_IDS'] ?? IBLOCK_IDS_ALL_CATALOG;
        $minLength = $params['MIN_LENGTH'] ?? SearchConfig::get('MIN_QUERY_LENGTH');
        $maxResults = $params['MAX_RESULTS'] ?? 10;
        $offset = $params['OFFSET'] ?? 0;

        if (mb_strlen($query) < $minLength) {
            return ['items' => [], 'total' => 0];
        }

        // Кеширование результатов поиска
        $queryLower = mb_strtolower($query);
        $cacheId = md5(serialize(['query' => $queryLower, 'params' => $params]));
        $cacheDir = '/search_results';
        $cacheTime = SearchConfig::get('CACHE_TIME');

        $cache = \Bitrix\Main\Data\Cache::createInstance();

        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            // Возвращаем из кеша
            return $cache->getVars();
        }

        $cache->startDataCache();

        // Подключаем модули
        if (!Loader::includeModule('iblock')) {
            $cache->abortDataCache();
            return ['items' => [], 'total' => 0];
        }

        $productIds = [];
        $relevanceMap = [];

        // Получаем все свойства с флагом "участвует в поиске" для указанных инфоблоков
        $searchableProperties = [];
        foreach ($iblockIds as $iblockId) {
            $rsProps = CIBlockProperty::GetList(
                [],
                [
                    'IBLOCK_ID' => $iblockId,
                    'SEARCHABLE' => 'Y',
                    'ACTIVE' => 'Y'
                ]
            );
            while ($arProp = $rsProps->Fetch()) {
                if (!empty($arProp['CODE'])) {
                    $searchableProperties[] = 'PROPERTY_' . $arProp['CODE'];
                }
            }
        }

        // Формируем фильтр для поиска по базовым полям и свойствам
        $arFilterConditions = [
            ['%NAME' => $query],
            ['%PREVIEW_TEXT' => $query],
        ];

        // Добавляем поиск по всем свойствам с SEARCHABLE=Y
        foreach ($searchableProperties as $propCode) {
            $arFilterConditions[] = ['%' . $propCode => $query];
        }

        $arFilter = [
            'IBLOCK_ID' => $iblockIds,
            'ACTIVE' => 'Y',
            '!CODE' => false, // Исключаем элементы без символьного кода (они не пройдут enrichProducts)
            [
                'LOGIC' => 'OR',
                ...$arFilterConditions
            ]
        ];

        // Получаем элементы (без лимита, чтобы правильно отсортировать по релевантности)
        // В идеале для больших баз нужно использовать Sphinx/Elastic, но для текущей задачи так
        $rsElements = CIBlockElement::GetList(
            ['NAME' => 'ASC'],
            $arFilter,
            false,
            false, // Берем все подходящие для правильной сортировки и пагинации
            ['ID', 'NAME', 'PREVIEW_TEXT', 'IBLOCK_ID']
        );

        // Собираем ID и определяем релевантность
        while ($arElement = $rsElements->GetNext()) {
            $itemId = $arElement['ID'];
            $relevance = 0;
            $nameLower = mb_strtolower($arElement['NAME'] ?? '');
            $previewTextLower = mb_strtolower($arElement['PREVIEW_TEXT'] ?? '');

            // Определяем релевантность
            if (mb_strpos($nameLower, $queryLower) === 0) {
                $relevance = 100; // Начало названия
            } elseif (mb_strpos($nameLower, $queryLower) !== false) {
                $relevance = 80; // Название содержит
            } elseif (!empty($previewTextLower) && mb_strpos($previewTextLower, $queryLower) !== false) {
                $relevance = 40; // В анонсе
            } else {
                // Проверяем совпадение в свойствах с SEARCHABLE=Y
                $props = CIBlockElement::GetProperty(
                    $arElement['IBLOCK_ID'],
                    $itemId,
                    [],
                    ['SEARCHABLE' => 'Y']
                );
                while ($prop = $props->Fetch()) {
                    if (!empty($prop['VALUE'])) {
                        $propValue = is_array($prop['VALUE']) ? implode(' ', $prop['VALUE']) : $prop['VALUE'];
                        $propValueLower = mb_strtolower($propValue);
                        if (mb_strpos($propValueLower, $queryLower) !== false) {
                            $relevance = 20; // В свойствах
                            break;
                        }
                    }
                }
            }

            // Добавляем товар только если найдено совпадение
            if ($relevance > 0) {
                $relevanceMap[$itemId] = $relevance;
            }
        }

        // Сортируем ID по релевантности
        arsort($relevanceMap);

        $allFoundIds = array_keys($relevanceMap);
        $totalCount = count($allFoundIds);

        // Применяем пагинацию
        $pageIds = array_slice($allFoundIds, $offset, $maxResults);

        // Обогащаем данные
        $items = self::enrichProducts($pageIds);

        // Восстанавливаем порядок сортировки (так как enrichProducts может вернуть в другом порядке)
        $sortedItems = [];
        foreach ($pageIds as $id) {
            if (isset($items[$id])) {
                // Добавляем релевантность для отладки или использования
                $items[$id]['RELEVANCE'] = $relevanceMap[$id];
                $sortedItems[] = $items[$id];
            }
        }

        $result = [
            'items' => $sortedItems,
            'total' => $totalCount
        ];

        // Сохраняем результат в кеш
        $cache->endDataCache($result);

        return $result;
    }
}

