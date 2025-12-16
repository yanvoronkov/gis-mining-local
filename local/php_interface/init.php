<?php
// Главный файл для кастомизации Битрикс. Подключается на всех страницах и во всех режимах.

// Предотвращаем прямой доступ к файлу
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * ======================================================================
 * Подключение констант
 * ======================================================================
 */
require_once __DIR__ . '/constants.php';
// ======================================================================
// Подключение классов-хелперов
// ======================================================================

// Подключаем централизованный SEO менеджер
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/SeoManager.php");

// Инициализируем SEO (Canonical + Meta)
\Local\Seo\SeoManager::init();

// Подключаем хелпер для работы с поиском
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/SearchHelper.php");

// Подключаем централизованную конфигурацию поиска
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/SearchConfig.php");

// ... (Rest of the file remains, but remove redundant functions if any)


/**
 * ======================================================================
 * Автоматическое добавление номера страницы к мета-тегам (SEO)
 * ======================================================================
 */

/**
 * Автоматически добавляет "— страница №" к title и description для страниц пагинации.
 * Работает для всех страниц, где в URL присутствует параметр PAGEN_1 > 1.
 *
 * Это помогает поисковым роботам:
 * 1. Понять, что страницы пагинации не являются дублями
 * 2. Правильно индексировать последовательные страницы каталога
 * 3. Улучшить SEO-показатели сайта
 *
 * Номер страницы добавляется ТОЛЬКО для:
 * - META title (<title>)
 * - META description
 *
 * НЕ добавляется для:
 * - H1 заголовка страницы
 * - Open Graph тегов (для соц. сетей)
 * - Twitter Card тегов (для соц. сетей)
 *
 * @return void
 */
function addPaginationToMetaTags()
{
    global $APPLICATION;

    // Проверяем наличие параметра пагинации
    $pageNum = isset($_GET['PAGEN_1']) ? intval($_GET['PAGEN_1']) : 0;

    // Если это первая страница или параметра нет, ничего не делаем
    if ($pageNum <= 1) {
        return;
    }

    // Формируем суффикс с номером страницы
    $pageSuffix = " — страница {$pageNum}";

    // --- ОБНОВЛЕНИЕ META TITLE (для поисковиков) ---
    $currentTitle = $APPLICATION->GetPageProperty('title');
    if (!empty($currentTitle) && strpos($currentTitle, '— страница') === false) {
        $APPLICATION->SetPageProperty('title', $currentTitle . $pageSuffix);
    }

    // --- ОБНОВЛЕНИЕ META DESCRIPTION (для поисковиков) ---
    $currentDescription = $APPLICATION->GetPageProperty('description');
    if (!empty($currentDescription) && strpos($currentDescription, '— страница') === false) {
        $APPLICATION->SetPageProperty('description', $currentDescription . $pageSuffix);
    }

    // Примечание: H1, Open Graph и Twitter Card теги НЕ изменяются,
    // чтобы пользователи видели оригинальные заголовки при шаринге в соц. сетях
}

/**
 * Регистрируем обработчик события OnEpilog.
 * Это событие срабатывает после формирования контента страницы, но до вывода в браузер.
 * Это идеальный момент для модификации мета-тегов, которые уже были установлены компонентами.
 */
AddEventHandler('main', 'OnEpilog', 'addPaginationToMetaTags');

/**
 * ======================================================================
 * Настройка поисковой индексации - исключаем DETAIL_TEXT
 * ======================================================================
 */

use Bitrix\Main\EventManager;

/**
 * Обработчик события BeforeIndex для исключения DETAIL_TEXT из поисковой индексации.
 * Пересобирает BODY только из нужных полей (NAME, PREVIEW_TEXT, свойства),
 * полностью исключая DETAIL_TEXT из индекса.
 * 
 * @param array $arFields Массив полей для индексации
 * @return array Модифицированный массив полей
 */
function excludeDetailTextFromSearch($arFields)
{
    // Проверяем, что это элемент инфоблока
    if ($arFields['MODULE_ID'] == 'iblock' && isset($arFields['ITEM_ID'])) {

        // Получаем ID инфоблока из PARAM2
        $iblockId = intval($arFields['PARAM2']);

        // Применяем только для инфоблоков каталога
        $catalogIblocks = IBLOCK_IDS_ALL_CATALOG;

        if (in_array($iblockId, $catalogIblocks) && intval($arFields['ITEM_ID']) > 0) {
            // Подключаем модули
            if (CModule::IncludeModule('iblock') && CModule::IncludeModule('search')) {
                // Получаем элемент с полными данными
                $dbElement = CIBlockElement::GetByID($arFields['ITEM_ID']);
                if ($arElement = $dbElement->Fetch()) {
                    // Пересобираем BODY только из нужных полей, исключая DETAIL_TEXT
                    $bodyParts = [];

                    // 1. Добавляем название (NAME)
                    if (!empty($arElement['NAME'])) {
                        $bodyParts[] = $arElement['NAME'];
                    }

                    // 2. Добавляем анонс/описание (PREVIEW_TEXT)
                    if (!empty($arElement['PREVIEW_TEXT'])) {
                        $bodyParts[] = CSearch::KillTags($arElement['PREVIEW_TEXT']);
                    }

                    // 3. Добавляем свойства с SEARCHABLE=Y
                    $dbProps = CIBlockElement::GetProperty(
                        $iblockId,
                        $arFields['ITEM_ID'],
                        [],
                        ['SEARCHABLE' => 'Y', 'ACTIVE' => 'Y']
                    );

                    while ($arProp = $dbProps->Fetch()) {
                        if (!empty($arProp['VALUE'])) {
                            // Обрабатываем множественные значения
                            if (is_array($arProp['VALUE'])) {
                                foreach ($arProp['VALUE'] as $value) {
                                    if (!empty($value)) {
                                        $bodyParts[] = is_array($value) ? implode(' ', $value) : $value;
                                    }
                                }
                            } else {
                                $bodyParts[] = $arProp['VALUE'];
                            }
                        }
                    }

                    // 4. Объединяем все части в новое BODY (без DETAIL_TEXT)
                    $arFields['BODY'] = implode(' ', $bodyParts);

                    // 5. Очищаем от лишних пробелов
                    $arFields['BODY'] = preg_replace('/\s+/', ' ', $arFields['BODY']);
                    $arFields['BODY'] = trim($arFields['BODY']);
                }
            }
        }
    }

    return $arFields;
}

// Регистрируем обработчик события индексации
$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('search', 'BeforeIndex', 'excludeDetailTextFromSearch');

/**
 * ======================================================================
 * Глобальная сортировка каталога (SORT_PRIORITY)
 * ======================================================================
 */

use Bitrix\Main\Loader;

/**
 * Обновляет свойство SORT_PRIORITY для товара на основе его характеристик.
 * Логика:
 * 1. Хит (FEATURED) -> 300
 * 2. Есть цена (>0) -> 200
 * 3. Нет цены (0)   -> 100
 *
 * @param int $elementId ID элемента
 * @param int $iblockId ID инфоблока
 * @return void
 */
function updateProductSortPriority($elementId, $iblockId)
{
    // Проверяем, что работаем только с товарными инфоблоками
    if (!defined('IBLOCK_IDS_PRODUCT') || !in_array($iblockId, IBLOCK_IDS_PRODUCT)) {
        return;
    }

    if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
        return;
    }

    $priority = 100; // Дефолт (нет цены)

    // 1. Получаем свойства (FEATURED)
    // Используем GetList, чтобы получить актуальные данные из БД
    $dbProps = CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => 'FEATURED']);
    $isFeatured = false;
    if ($arProp = $dbProps->Fetch()) {
        $val = $arProp['VALUE_ENUM'] ?? $arProp['VALUE']; // Может быть списком или строкой
        if ($val == 'Y' || $val == 'Да') {
            $isFeatured = true;
        }
    }

    // 2. Получаем цену (Базовую / Оптимальную)
    // CPrice::GetBasePrice возвращает базовую цену
    $arPrice = CPrice::GetBasePrice($elementId);
    $price = 0;
    if ($arPrice) {
        $price = (float) $arPrice['PRICE'];
    }

    // 3. Рассчитываем приоритет (4 уровня)
    // 1. Хит с ценой       -> 400
    // 2. Хит без цены      -> 300
    // 3. Не хит с ценой    -> 200
    // 4. Не хит без цены   -> 100

    if ($isFeatured) {
        if ($price > 0) {
            $priority = 400;
        } else {
            $priority = 300;
        }
    } else {
        if ($price > 0) {
            $priority = 200;
        } else {
            $priority = 100;
        }
    }

    // 4. Обновляем свойство SORT_PRIORITY
    // Используем SetPropertyValuesEx, чтобы не вызывать пересохранение всего элемента (и не зациклить события)
    CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, ['SORT_PRIORITY' => $priority]);
}

/**
 * Обработчики событий для авто-обновления SORT_PRIORITY
 */

// При обновлении/добавлении элемента инфоблока
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "handlerOnAfterIBlockElementUpdate");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "handlerOnAfterIBlockElementUpdate");

function handlerOnAfterIBlockElementUpdate($arFields)
{
    if ($arFields['ID'] > 0 && $arFields['IBLOCK_ID'] > 0) {
        // Важно: чтобы избежать рекурсии вызовем функцию напрямую
        updateProductSortPriority($arFields['ID'], $arFields['IBLOCK_ID']);
    }
}

// При изменении цены (Bitrix Catalog events)
// Старые события (catalog module)
AddEventHandler("catalog", "OnPriceAdd", "handlerOnPriceUpdate");
AddEventHandler("catalog", "OnPriceUpdate", "handlerOnPriceUpdate");

function handlerOnPriceUpdate($id, $arFields)
{
    if (isset($arFields['PRODUCT_ID']) && $arFields['PRODUCT_ID'] > 0) {
        // Нам нужен IBLOCK_ID. Получим его по элементу.
        $elementId = $arFields['PRODUCT_ID'];
        $res = CIBlockElement::GetByID($elementId);
        if ($ob = $res->GetNext()) {
            updateProductSortPriority($elementId, $ob['IBLOCK_ID']);
        }
    }
}

/**
 * ======================================================================
 * SEO фильтр: Обработка custom URLs
 * ======================================================================
 * Обработка кастомных URL для модуля SEO умного фильтра Lite
 * реализована через кастомный компонент local/components/bitrix/catalog
 * (см. template.php и smart_filter.php в шаблоне .default)
 * ======================================================================
 */
