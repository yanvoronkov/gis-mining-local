<?php
/**
 * Централизованная конфигурация для системы поиска
 * Единая точка управления всеми параметрами поиска на сайте
 */

class SearchConfig
{
    /**
     * Минимальная длина поискового запроса (в символах)
     * Можно изменить здесь для применения во всем сайте
     */
    const MIN_QUERY_LENGTH = 2;

    /**
     * Максимальное количество результатов на странице
     */
    const MAX_RESULTS = 10;

    /**
     * Время кеширования результатов поиска (в секундах)
     */
    const CACHE_TIME = 3600; // 1 час

    /**
     * Время задержки debounce при вводе (в миллисекундах)
     */
    const DEBOUNCE_TIME = 400;

    /**
     * ID инфоблоков для поиска по умолчанию
     */
    const DEFAULT_IBLOCK_IDS = IBLOCK_IDS_ALL_CATALOG;

    /**
     * Получить значение конфигурации
     * 
     * @param string $key Ключ параметра
     * @param mixed $default Значение по умолчанию, если ключ не найден
     * @return mixed Значение параметра
     */
    public static function get($key, $default = null)
    {
        $config = [
            'MIN_QUERY_LENGTH' => self::MIN_QUERY_LENGTH,
            'MAX_RESULTS' => self::MAX_RESULTS,
            'CACHE_TIME' => self::CACHE_TIME,
            'DEBOUNCE_TIME' => self::DEBOUNCE_TIME,
            'IBLOCK_IDS' => self::DEFAULT_IBLOCK_IDS,
        ];

        return $config[$key] ?? $default;
    }

    /**
     * Получить все настройки поиска
     * 
     * @return array Массив всех настроек
     */
    public static function getAll()
    {
        return [
            'MIN_QUERY_LENGTH' => self::MIN_QUERY_LENGTH,
            'MAX_RESULTS' => self::MAX_RESULTS,
            'CACHE_TIME' => self::CACHE_TIME,
            'DEBOUNCE_TIME' => self::DEBOUNCE_TIME,
            'IBLOCK_IDS' => self::DEFAULT_IBLOCK_IDS,
        ];
    }
}
