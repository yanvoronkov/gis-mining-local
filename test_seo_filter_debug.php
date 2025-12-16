<?php
/**
 * Тестовый скрипт для диагностики модуля SEO фильтра
 * Открыть в браузере: http://ваш-сайт/test_seo_filter_debug.php
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Loader;

$APPLICATION->SetTitle("Диагностика SEO фильтра");

?>
<style>
    .debug-section {
        background: #f5f5f5;
        padding: 15px;
        margin: 10px 0;
        border-left: 4px solid #007bff;
    }

    .debug-ok {
        color: #28a745;
        font-weight: bold;
    }

    .debug-error {
        color: #dc3545;
        font-weight: bold;
    }

    .code-block {
        background: #fff;
        padding: 10px;
        border: 1px solid #ddd;
        font-family: monospace;
    }
</style>

<h1>Диагностика модуля "SEO для умного фильтра Lite"</h1>

<div class="debug-section">
    <h2>1. Проверка установки модуля</h2>
    <?php
    if (Loader::includeModule('dwstroy.seochpulite')) {
        echo '<p class="debug-ok">✓ Модуль dwstroy.seochpulite успешно подключен</p>';

        // Получаем ID инфоблоков SEO фильтра
        $helper = \Dwstroy\SeoChpuLite\Helper::getInstance();
        $iblockIds = $helper->getChpuIblocks();

        if (!empty($iblockIds)) {
            echo '<p class="debug-ok">✓ Найдены инфоблоки SEO фильтра: ' . implode(', ', $iblockIds) . '</p>';
        } else {
            echo '<p class="debug-error">✗ Инфоблоки SEO фильтра не найдены!</p>';
        }
    } else {
        echo '<p class="debug-error">✗ Модуль dwstroy.seochpulite НЕ установлен или не активен</p>';
    }
    ?>
</div>

<div class="debug-section">
    <h2>2. Проверка инфоблока с ID 13</h2>
    <?php
    if (Loader::includeModule('iblock')) {
        $iblock = CIBlock::GetByID(13)->Fetch();

        if ($iblock) {
            echo '<p class="debug-ok">✓ Инфоблок ID 13 найден</p>';
            echo '<div class="code-block">';
            echo '<strong>Название:</strong> ' . $iblock['NAME'] . '<br>';
            echo '<strong>Код:</strong> ' . $iblock['CODE'] . '<br>';
            echo '<strong>Тип:</strong> ' . $iblock['IBLOCK_TYPE_ID'] . '<br>';
            echo '<strong>Активен:</strong> ' . ($iblock['ACTIVE'] == 'Y' ? 'Да' : 'Нет');
            echo '</div>';

            // Получаем элементы
            $rsElements = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => 13, 'ACTIVE' => 'Y'],
                false,
                false,
                ['ID', 'NAME', 'ACTIVE', 'PROPERTY_OLD_URL', 'PROPERTY_NEW_URL']
            );

            $elementsCount = 0;
            echo '<h3 style="margin-top: 20px;">Элементы инфоблока:</h3>';
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>ID</th><th>Название</th><th>OLD_URL</th><th>NEW_URL</th></tr>';

            while ($element = $rsElements->Fetch()) {
                $elementsCount++;

                // Получаем свойства элемента
                $oldUrl = '';
                $newUrl = '';

                $rsProps = CIBlockElement::GetProperty(13, $element['ID'], ['SORT' => 'ASC'], ['CODE' => 'OLD_URL']);
                if ($prop = $rsProps->Fetch()) {
                    $oldUrl = $prop['VALUE'];
                }

                $rsProps = CIBlockElement::GetProperty(13, $element['ID'], ['SORT' => 'ASC'], ['CODE' => 'NEW_URL']);
                if ($prop = $rsProps->Fetch()) {
                    $newUrl = $prop['VALUE'];
                }

                echo '<tr>';
                echo '<td>' . $element['ID'] . '</td>';
                echo '<td>' . htmlspecialchars($element['NAME']) . '</td>';
                echo '<td><code>' . htmlspecialchars($oldUrl) . '</code></td>';
                echo '<td><code>' . htmlspecialchars($newUrl) . '</code></td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($elementsCount > 0) {
                echo '<p class="debug-ok">✓ Найдено элементов: ' . $elementsCount . '</p>';
            } else {
                echo '<p class="debug-error">✗ Элементы в инфоблоке ID 13 НЕ НАЙДЕНЫ! Создайте хотя бы одну запись.</p>';
            }

        } else {
            echo '<p class="debug-error">✗ Инфоблок с ID 13 НЕ НАЙДЕН!</p>';
            echo '<p>Возможно, модуль создал инфоблок с другим ID. Проверьте в админке: Контент → Инфоблоки → Тип "dwstroy_seochpulite"</p>';
        }
    }
    ?>
</div>

<div class="debug-section">
    <h2>3. Проверка событий модуля</h2>
    <?php
    use Bitrix\Main\EventManager;

    $eventManager = EventManager::getInstance();
    $events = $eventManager->findEventHandlers('main', 'OnEpilog');

    $found = false;
    foreach ($events as $event) {
        if ($event['TO_MODULE_ID'] == 'dwstroy.seochpulite') {
            $found = true;
            break;
        }
    }

    if ($found) {
        echo '<p class="debug-ok">✓ Событие OnEpilog зарегистрировано для модуля dwstroy.seochpulite</p>';
    } else {
        echo '<p class="debug-error">✗ Событие OnEpilog НЕ зарегистрировано!</p>';
        echo '<p>Попробуйте переустановить модуль в админке: Marketplace → Установленные решения</p>';
    }
    ?>
</div>

<div class="debug-section">
    <h2>4. Тестовый URL для проверки</h2>
    <p>Если у вас создана запись в инфоблоке ID 13:</p>
    <ul>
        <li>OLD_URL: <code>/catalog/asics/filter/crypto-is-zec/apply/</code></li>
        <li>NEW_URL: <code>/catalog/asics/filter-zec-crypto/</code></li>
    </ul>
    <p>Попробуйте открыть оба URL и проверьте:</p>
    <ol>
        <li><strong>Редирект:</strong> Должен ли OLD_URL редиректить на NEW_URL</li>
        <li><strong>SEO-теги:</strong> Установлены ли H1, title, description из записи инфоблока</li>
        <li><strong>Фильтрация:</strong> Применяется ли фильтр по криптовалюте ZEC</li>
    </ol>
</div>

<div class="debug-section">
    <h2>5. Текущий URL и параметры</h2>
    <div class="code-block">
        <?php
        echo '<strong>REQUEST_URI:</strong> ' . htmlspecialchars($_SERVER['REQUEST_URI']) . '<br>';
        echo '<strong>SMART_FILTER_PATH:</strong> ' . htmlspecialchars($_REQUEST['SMART_FILTER_PATH'] ?? 'не установлен') . '<br>';
        echo '<strong>GetCurPage:</strong> ' . $APPLICATION->GetCurPage() . '<br>';
        echo '<strong>GetCurPageParam:</strong> ' . htmlspecialchars($APPLICATION->GetCurPageParam()) . '<br>';
        ?>
    </div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
