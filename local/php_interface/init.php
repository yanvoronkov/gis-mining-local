<?php
// Главный файл для кастомизации Битрикс. Подключается на всех страницах и во всех режимах.

// Предотвращаем прямой доступ к файлу
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


/**
 * ======================================================================
 * Пользовательские функции для шаблона сайта
 * ======================================================================
 */

/**
 * Функция для рендеринга OpenGraph и Twitter мета-тегов.
 * Регистрируется в header.php как отложенная функция через $APPLICATION->AddBufferContent().
 * @return string Возвращает HTML-код с мета-тегами.
 */
function renderCustomMetaTags()
{
    global $APPLICATION;
    $output = ''; // Уберем отладочные комментарии

    $arPageProperties = $APPLICATION->GetPagePropertyList();

    if (!empty($arPageProperties)) {
        foreach ($arPageProperties as $propertyCode => $propertyValue) {
            // Пропускаем пустые значения
            if (empty($propertyValue)) {
                continue;
            }

            // Обрабатываем Open Graph мета-теги
            if (preg_match('/^og:/i', $propertyCode)) {
                $output .= '<meta property="' . htmlspecialchars($propertyCode) . '" content="' . htmlspecialchars($propertyValue) . '">' . "\n\t";
            }
            // Обрабатываем Twitter Card мета-теги
            elseif (preg_match('/^twitter:/i', $propertyCode)) {
                $output .= '<meta name="' . htmlspecialchars($propertyCode) . '" content="' . htmlspecialchars($propertyValue) . '">' . "\n\t";
            }
            // Обрабатываем каноническую ссылку
            elseif (strtolower($propertyCode) === 'canonical') {
                $output .= '<link rel="canonical" href="' . htmlspecialchars($propertyValue) . '">' . "\n\t";
            }
        }
    }

    return $output;
}

/**
 * ======================================================================
 * Подключение агентов и прочих скриптов
 * ======================================================================
 */

// Ваш существующий код для подключения агента:
// Просто подключаем файл с функцией агента, чтобы Битрикс мог ее найти.
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/scripts/update_prices_from_google.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/scripts/update_prices_videocards_from_google.php");

/**
 * ======================================================================
 * Подключение классов-хелперов
 * ======================================================================
 */

// Подключаем хелпер для работы с поиском
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/SearchHelper.php");

// Подключаем централизованную конфигурацию поиска
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/SearchConfig.php");

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
        
        // Применяем только для инфоблоков каталога (1, 3, 4, 5, 6, 11)
        $catalogIblocks = [1, 3, 4, 5, 6, 11];
        
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
