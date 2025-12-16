<?php
/**
 * Тестовая страница для проверки работы модуля SEO фильтра
 * После рефакторинга архитектуры
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Тестирование SEO фильтра после рефакторинга");

?>
<style>
    .test-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
    }

    .test-section {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 20px;
        margin: 20px 0;
    }

    .success {
        color: #28a745;
        font-weight: bold;
    }

    .error {
        color: #dc3545;
        font-weight: bold;
    }

    .info {
        color: #17a2b8;
    }

    .test-url {
        background: #fff;
        padding: 15px;
        border: 1px solid #dee2e6;
        margin: 10px 0;
    }

    .test-url a {
        color: #007bff;
        text-decoration: none;
        font-size: 16px;
    }

    .test-url a:hover {
        text-decoration: underline;
    }
</style>

<div class="test-container">
    <h1>✅ Тестирование работы модуля SEO фильтра</h1>
    <p class="info">После рефакторинга: bitrix:catalog.smart.filter теперь вызывается напрямую в section.php</p>

    <div class="test-section">
        <h2>📋 Изменения архитектуры</h2>
        <ul>
            <li>✅ <strong>/bitrix/php_interface/init.php</strong> - добавлена инициализация модуля</li>
            <li>✅ <strong>custom:catalog.sidebar</strong> - убран вызов фильтра, оставлена только навигация</li>
            <li>✅ <strong>section.php</strong> - добавлен прямой вызов bitrix:catalog.smart.filter</li>
        </ul>
    </div>

    <div class="test-section">
        <h2>🧪 Тестовые URL для проверки</h2>

        <div class="test-url">
            <h3>1. Стандартный URL фильтра (должен редиректить)</h3>
            <a href="/catalog/asics/filter/crypto-is-zec/apply/" target="_blank">
                /catalog/asics/filter/crypto-is-zec/apply/
            </a>
            <p><strong>Ожидаемое поведение:</strong> Редирект 301 на /catalog/asics/filter-zec-crypto/</p>
        </div>

        <div class="test-url">
            <h3>2. Красивый URL с SEO-тегами</h3>
            <a href="/catalog/asics/filter-zec-crypto/" target="_blank">
                /catalog/asics/filter-zec-crypto/
            </a>
            <p><strong>Ожидаемое поведение:</strong></p>
            <ul>
                <li>H1 изменен из инфоблока</li>
                <li>Title и Description установлены из инфоблока</li>
                <li>Применен фильтр по криптовалюте ZEC</li>
                <li>Отображаются только майнеры для ZEC</li>
            </ul>
        </div>

        <div class="test-url">
            <h3>3. Обычная страница каталога (без фильтра)</h3>
            <a href="/catalog/asics/" target="_blank">
                /catalog/asics/
            </a>
            <p><strong>Ожидаемое поведение:</strong> Стандартная страница, все товары, стандартные SEO-теги</p>
        </div>
    </div>

    <div class="test-section">
        <h2>🔍 Что проверить на странице фильтра</h2>
        <ol>
            <li><strong>H1 заголовок:</strong> Должен быть из инфоблока (например: "ASIC-майнеры для добычи Zcash
                (ZEC)")</li>
            <li><strong>Title страницы:</strong> Проверить в заголовке вкладки браузера</li>
            <li><strong>Фильтр применен:</strong> Криптовалюта ZEC должна быть выбрана в фильтре</li>
            <li><strong>Товары отфильтрованы:</strong> Показываются только майнеры для ZEC</li>
            <li><strong>URL в адресной строке:</strong> Должен быть красивый (/filter-zec-crypto/)</li>
        </ol>
    </div>

    <div class="test-section">
        <h2>📊 Текущее состояние модуля</h2>
        <?php
        use Bitrix\Main\Loader;

        if (Loader::includeModule('dwstroy.seochpulite')) {
            echo '<p class="success">✓ Модуль dwstroy.seochpulite загружен</p>';
        } else {
            echo '<p class="error">✗ Модуль dwstroy.seochpulite НЕ загружен</p>';
        }

        if (Loader::includeModule('iblock')) {
            // Проверяем запись в инфоблоке
            $rsElement = CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => 13, 'ID' => 99677],
                false,
                false,
                ['ID', 'NAME', 'ACTIVE']
            );

            if ($element = $rsElement->Fetch()) {
                echo '<p class="success">✓ Запись в инфоблоке найдена: ' . htmlspecialchars($element['NAME']) . '</p>';

                // Получаем свойства
                $rsProps = CIBlockElement::GetProperty(13, 99677, ['SORT' => 'ASC'], ['CODE' => 'OLD_URL']);
                if ($prop = $rsProps->Fetch()) {
                    echo '<p class="info">OLD_URL: <code>' . htmlspecialchars($prop['VALUE']) . '</code></p>';
                }

                $rsProps = CIBlockElement::GetProperty(13, 99677, ['SORT' => 'ASC'], ['CODE' => 'NEW_URL']);
                if ($prop = $rsProps->Fetch()) {
                    echo '<p class="info">NEW_URL: <code>' . htmlspecialchars($prop['VALUE']) . '</code></p>';
                }
            } else {
                echo '<p class="error">✗ Запись с ID 99677 не найдена в инфоблоке 13</p>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>⚠️ Возможные проблемы и решения</h2>
        <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
            <tr>
                <th>Проблема</th>
                <th>Возможная причина</th>
                <th>Решение</th>
            </tr>
            <tr>
                <td>Редирект не работает</td>
                <td>Модуль не перехватывает URL</td>
                <td>Очистить кеш Битрикс, проверить события в /bitrix/admin/event_type_list.php</td>
            </tr>
            <tr>
                <td>SEO-теги не меняются</td>
                <td>Событие OnEpilog не срабатывает</td>
                <td>Проверить, что init.php подключается (добавить var_dump в начало)</td>
            </tr>
            <tr>
                <td>Фильтр не применяется</td>
                <td>SMART_FILTER_PATH не передается</td>
                <td>Проверить $_REQUEST['SMART_FILTER_PATH'] на странице фильтра</td>
            </tr>
            <tr>
                <td>404 ошибка на красивом URL</td>
                <td>URL не распознается компонентом</td>
                <td>Проверить SEF_URL_TEMPLATES в index.php каталога</td>
            </tr>
        </table>
    </div>

    <div class="test-section">
        <h2>🚀 Следующие шаги</h2>
        <ol>
            <li>Протестировать все 3 URL выше</li>
            <li>Проверить работу фильтра в консоли браузера (Network tab)</li>
            <li>Если работает - создать записи для других криптовалют</li>
            <li>Настроить SEO-теги для каждой комбинации фильтров</li>
            <li>Добавить в sitemap.xml</li>
        </ol>
    </div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
