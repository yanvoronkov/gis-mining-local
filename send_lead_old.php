<?php
// ======================================================================
// Файл: send_lead.php - Финальная версия с HttpClient Битрикса
// ======================================================================

// ВАЖНО: Подключаем prolog_before, чтобы получить доступ к API Битрикса
// Эта строка должна быть в самом начале.
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключаем необходимые классы Битрикса для работы с HTTP-запросами
use Bitrix\Main\Web\HttpClient;

// --- Конфигурация ---
define('BITRIX_WEBHOOK_URL', 'https://gis-mining.bitrix24.ru/rest/3516/klajzellh9cspi44/crm.lead.add.json');

// --- Функциональная часть ---

// Устанавливаем заголовок ответа как JSON
header('Content-Type: application/json');

// Функция для отправки стандартизированного JSON-ответа
function send_json_response($success, $data = [])
{
    echo json_encode(array_merge(['success' => $success], $data));
    exit;
}

// Проверяем, что запрос пришел методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, ['error' => 'Метод не разрешен. Ожидается POST.']);
}

// --- Сбор и очистка данных из $_POST ---
$client_phone   = isset($_POST['client_phone']) ? htmlspecialchars(trim($_POST['client_phone'])) : '';
$client_name    = isset($_POST['client_name']) ? htmlspecialchars(trim($_POST['client_name'])) : '';
$form_name      = isset($_POST['form_name']) ? htmlspecialchars(trim($_POST['form_name'])) : 'Неизвестная форма';
$client_email   = isset($_POST['client_email']) ? htmlspecialchars(trim($_POST['client_email'])) : '';
$client_comment = isset($_POST['client_comment']) ? htmlspecialchars(trim($_POST['client_comment'])) : '';
$client_colvo   = isset($_POST['client_colvo']) ? htmlspecialchars(trim($_POST['client_colvo'])) : '';
$source_id      = isset($_POST['source_id']) ? htmlspecialchars(trim($_POST['source_id'])) : 'WEB';

// Получаем URL страницы, с которой пришел запрос
$page_url = '';
if (isset($_POST['page_url']) && !empty($_POST['page_url'])) {
    $page_url = htmlspecialchars(trim($_POST['page_url']));
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    $page_url = $_SERVER['HTTP_REFERER'];
}

// Если URL все еще пустой, формируем из данных сервера
if (empty($page_url)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $page_url = $protocol . '://' . $host . $uri;
}

// Получаем UTM-метки (сначала из POST, затем из cookies)
$utm_source     = isset($_POST['utm_source']) ? htmlspecialchars(trim($_POST['utm_source'])) : (isset($_COOKIE['utm_source']) ? htmlspecialchars(trim($_COOKIE['utm_source'])) : '');
$utm_medium     = isset($_POST['utm_medium']) ? htmlspecialchars(trim($_POST['utm_medium'])) : (isset($_COOKIE['utm_medium']) ? htmlspecialchars(trim($_COOKIE['utm_medium'])) : '');
$utm_campaign   = isset($_POST['utm_campaign']) ? htmlspecialchars(trim($_POST['utm_campaign'])) : (isset($_COOKIE['utm_campaign']) ? htmlspecialchars(trim($_COOKIE['utm_campaign'])) : '');
$utm_content    = isset($_POST['utm_content']) ? htmlspecialchars(trim($_POST['utm_content'])) : (isset($_COOKIE['utm_content']) ? htmlspecialchars(trim($_COOKIE['utm_content'])) : '');
$utm_term       = isset($_POST['utm_term']) ? htmlspecialchars(trim($_POST['utm_term'])) : (isset($_COOKIE['utm_term']) ? htmlspecialchars(trim($_COOKIE['utm_term'])) : '');

// Получаем metrica_client_id из cookies (Яндекс.Метрика использует _ym_uid)
$metrica_client_id = '';
if (isset($_COOKIE['_ym_uid'])) {
    $metrica_client_id = htmlspecialchars(trim($_COOKIE['_ym_uid']));
}

// Получаем и форматируем состав заказа, если он был передан (из корзины)
$orderDetails = '';
if (isset($_POST['cart_items']) && !empty($_POST['cart_items'])) {
    $cartItems = json_decode($_POST['cart_items'], true);
    if (is_array($cartItems)) {
        $orderDetails .= "\n\nСостав заказа:\n";
        foreach ($cartItems as $item) {
            $orderDetails .= "- " . ($item['name'] ?? 'Товар') .
                " (Кол-во: " . ($item['quantity'] ?? '0') . ")" .
                " - " . ($item['price'] ?? '0') . "\n";
        }
    }
}

// --- Валидация данных ---
if (empty($client_phone)) {
    send_json_response(false, ['error' => 'Номер телефона является обязательным полем.']);
}

// --- Подготовка данных для отправки в Bitrix24 ---

// Собираем полный комментарий для лида
$fullComment = "Форма: " . $form_name . "\n";
if (!empty($page_url)) {
    $fullComment .= "Страница: " . $page_url . "\n";
}
if (!empty($client_colvo)) {
    $fullComment .= "Кол-во размещаемых устройств: " . $client_colvo . "\n";
}
$fullComment .= $orderDetails;

// --- Расчет суммы заказа ---
$totalAmount = 0.0;

if (isset($_POST['cart_items']) && !empty($_POST['cart_items'])) {
    $cartItems = json_decode($_POST['cart_items'], true);
    if (is_array($cartItems)) {
        foreach ($cartItems as $item) {
            // Очищаем цену от пробелов, символов валюты и приводим к числу
            $priceRaw = $item['priceRaw'] ?? $item['price'] ?? '0';
            $priceRaw = preg_replace('/[^\d.,]/', '', $priceRaw); // Убираем всё, кроме цифр, точки и запятой
            $priceRaw = str_replace(',', '.', $priceRaw);         // Заменяем запятую на точку
            $priceRaw = floatval($priceRaw);

            $quantity = intval($item['quantity'] ?? 1);
            $totalAmount += $priceRaw * $quantity;
        }
    }
}

$totalAmount = round($totalAmount, 2);

$lead_data = [
    'fields' => [
        'TITLE'        => "Лид с формы: " . $form_name . (!empty($client_name) ? " (" . $client_name . ")" : ""),
        'PHONE'        => [['VALUE' => $client_phone, 'VALUE_TYPE' => 'WORK']],
        'SOURCE_ID'    => $source_id,
        'COMMENTS'     => $fullComment,
        'OPPORTUNITY'  => $totalAmount,           // <-- Сумма лида (в рублях)
        'CURRENCY_ID'  => 'RUB',                 // <-- Валюта
    ],
    'params' => ['REGISTER_SONET_EVENT' => 'Y']
];

// Добавляем остальные поля, только если они не пустые
if (!empty($client_name)) {
    $lead_data['fields']['NAME'] = $client_name;
}
if (!empty($client_email)) {
    $lead_data['fields']['EMAIL'] = [['VALUE' => $client_email, 'VALUE_TYPE' => 'WORK']];
}

// Добавляем UTM-метки
if (!empty($utm_source))   $lead_data['fields']['UTM_SOURCE'] = $utm_source;
if (!empty($utm_medium))   $lead_data['fields']['UTM_MEDIUM'] = $utm_medium;
if (!empty($utm_campaign)) $lead_data['fields']['UTM_CAMPAIGN'] = $utm_campaign;
if (!empty($utm_content))  $lead_data['fields']['UTM_CONTENT'] = $utm_content;
if (!empty($utm_term))     $lead_data['fields']['UTM_TERM'] = $utm_term;

// Добавляем metrica_client_id в пользовательское поле
if (!empty($metrica_client_id))    $lead_data['fields']['UF_CRM_1756888759'] = $metrica_client_id;

// --- Отправка данных в Bitrix24 через встроенный HttpClient ---
try {
    // Создаем экземпляр HTTP-клиента Битрикса
    $httpClient = new HttpClient();

    // Отправляем POST-запрос. HttpClient сам кодирует данные.
    $response = $httpClient->post(BITRIX_WEBHOOK_URL, $lead_data);

    // Проверяем на ошибки сети или HTTP
    if ($response === false || $httpClient->getStatus() >= 400) {
        throw new Exception('Сетевая ошибка или неверный HTTP-статус от CRM: ' . $httpClient->getStatus());
    }

    // Декодируем JSON-ответ от сервера
    $result = json_decode($response, true);

    // Проверяем, корректно ли декодировался JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Не удалось декодировать ответ от CRM: ' . json_last_error_msg());
    }

    // Проверяем, нет ли в ответе бизнес-ошибки от Bitrix24
    if (isset($result['error'])) {
        throw new Exception($result['error_description'] ?? 'Неизвестная ошибка API Bitrix24.');
    }

    // Проверяем, что лид действительно создан
    if (isset($result['result']) && $result['result'] > 0) {
        send_json_response(true, ['message' => 'Заявка успешно отправлена!', 'lead_id' => $result['result']]);
    } else {
        throw new Exception('CRM вернул неожиданный ответ, лид не создан.');
    }

} catch (Exception $e) {
    // "Ловим" любую из ошибок, которые мы сгенерировали выше
    // Логируем ошибку для себя (важно для отладки в будущем)
    error_log("B24 Lead Error: " . $e->getMessage());
    // Отдаем пользователю общее и безопасное сообщение
    send_json_response(false, ['error' => 'Ошибка при отправке данных в CRM. Попробуйте, пожалуйста, позже.']);
}
?>