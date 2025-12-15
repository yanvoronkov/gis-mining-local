<?php
// ======================================================================
// Файл: send_lead.php - Финальная версия с HttpClient Битрикса (СДЕЛКИ)
// ======================================================================

// Подключаем prolog_before для доступа к API Битрикса
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключаем необходимые классы
use Bitrix\Main\Web\HttpClient;

// --- Конфигурация ---
define('BITRIX_WEBHOOK_BASE', 'https://gis-mining.bitrix24.ru/rest/3516/klajzellh9cspi44/');
define('BITRIX_WEBHOOK_URL', BITRIX_WEBHOOK_BASE . 'crm.deal.add.json');

// --- Функциональная часть ---
header('Content-Type: application/json');

function send_json_response($success, $data = []) {
    echo json_encode(array_merge(['success' => $success], $data));
    exit;
}

// Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, ['error' => 'Метод не разрешен. Ожидается POST.']);
}

// --- Сбор данных ---
$client_phone   = isset($_POST['client_phone']) ? htmlspecialchars(trim($_POST['client_phone'])) : '';
$client_name    = isset($_POST['client_name']) ? htmlspecialchars(trim($_POST['client_name'])) : '';
$form_name      = isset($_POST['form_name']) ? htmlspecialchars(trim($_POST['form_name'])) : 'Неизвестная форма';
$client_email   = isset($_POST['client_email']) ? htmlspecialchars(trim($_POST['client_email'])) : '';
$client_comment = isset($_POST['client_comment']) ? trim($_POST['client_comment']) : '';
$client_colvo   = isset($_POST['client_colvo']) ? htmlspecialchars(trim($_POST['client_colvo'])) : '';
$cheaper_link   = isset($_POST['cheaper_link']) ? htmlspecialchars(trim($_POST['cheaper_link'])) : '';
$source_id      = isset($_POST['source_id']) ? htmlspecialchars(trim($_POST['source_id'])) : 'WEB';

// URL страницы
$page_url = '';
if (!empty($_POST['page_url'])) {
    $page_url = htmlspecialchars(trim($_POST['page_url']));
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $page_url = $_SERVER['HTTP_REFERER'];
} else {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $page_url = $protocol . '://' . $host . $uri;
}

// UTM-метки
$utm_source   = $_POST['utm_source']   ?? $_COOKIE['utm_source']   ?? '';
$utm_medium   = $_POST['utm_medium']   ?? $_COOKIE['utm_medium']   ?? '';
$utm_campaign = $_POST['utm_campaign'] ?? $_COOKIE['utm_campaign'] ?? '';
$utm_content  = $_POST['utm_content']  ?? $_COOKIE['utm_content']  ?? '';
$utm_term     = $_POST['utm_term']     ?? $_COOKIE['utm_term']     ?? '';

// Yandex Metrica
$metrica_client_id = $_COOKIE['_ym_uid'] ?? '';

// Состав заказа
$orderDetails = '';
if (!empty($_POST['cart_items'])) {
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

// Проверка телефона
if (empty($client_phone)) {
    send_json_response(false, ['error' => 'Номер телефона является обязательным полем.']);
}

// Комментарий
$fullComment = "Форма: $form_name\nСтраница: $page_url\n";
if (!empty($client_comment)) {
    // Преобразуем переводы строк в <br>, чтобы CRM отобразила форматирование
    $commentFormatted = nl2br($client_comment);
    $fullComment .= "Комментарий клиента:<br>" . $commentFormatted . "<br>";
}

if (!empty($client_colvo)) $fullComment .= "Кол-во размещаемых устройств: $client_colvo\n";
if (!empty($cheaper_link)) $fullComment .= "Ссылка на более дешевый товар: $cheaper_link\n";
$fullComment .= $orderDetails;

// Сумма
$totalAmount = 0.0;
if (!empty($cartItems)) {
    foreach ($cartItems as $item) {
        $priceRaw = $item['priceRaw'] ?? $item['price'] ?? '0';
        $priceRaw = preg_replace('/[^\d.,]/', '', $priceRaw);
        $priceRaw = str_replace(',', '.', $priceRaw);
        $priceRaw = floatval($priceRaw);
        $quantity = intval($item['quantity'] ?? 1);
        $totalAmount += $priceRaw * $quantity;
    }
}
$totalAmount = round($totalAmount, 2);

// --- Инициализация клиента ---
$httpClient = new HttpClient();

// --- Поиск или создание контакта ---
$contactId = null;

// 1. Поиск по телефону
$contactSearchResponse = $httpClient->post(BITRIX_WEBHOOK_BASE . 'crm.contact.list.json', [
    'filter' => ['PHONE' => $client_phone],
    'select' => ['ID']
]);
$contactSearch = json_decode($contactSearchResponse, true);

if (!empty($contactSearch['result'][0]['ID'])) {
    $contactId = $contactSearch['result'][0]['ID'];
} else {
    // 2. Создаём контакт
    $contactCreateResponse = $httpClient->post(BITRIX_WEBHOOK_BASE . 'crm.contact.add.json', [
        'fields' => [
            'NAME' => $client_name ?: 'Без имени',
            'PHONE' => [['VALUE' => $client_phone, 'VALUE_TYPE' => 'WORK']],
            'EMAIL' => !empty($client_email) ? [['VALUE' => $client_email, 'VALUE_TYPE' => 'WORK']] : []
        ]
    ]);
    $contactCreate = json_decode($contactCreateResponse, true);
    if (!empty($contactCreate['result'])) {
        $contactId = $contactCreate['result'];
    }
}

// --- Подготовка данных для сделки ---
$deal_data = [
    'fields' => [
        'TITLE'        => "Сделка с формы: " . $form_name . (!empty($client_name) ? " ($client_name)" : ""),
        'SOURCE_ID'    => $source_id,
        'COMMENTS'     => $fullComment,
        'OPPORTUNITY'  => $totalAmount,
        'CURRENCY_ID'  => 'RUB',
        'CATEGORY_ID'  => 56,           // ID нужной воронки
        'STAGE_ID'     => 'C56:NEW',    // Код нужной стадии
    ],
    'params' => ['REGISTER_SONET_EVENT' => 'Y']
];

// Привязываем контакт, если есть
if (!empty($contactId)) {
    $deal_data['fields']['CONTACT_ID'] = $contactId;
}

// Добавляем UTM-метки
if (!empty($utm_source))   $deal_data['fields']['UTM_SOURCE'] = $utm_source;
if (!empty($utm_medium))   $deal_data['fields']['UTM_MEDIUM'] = $utm_medium;
if (!empty($utm_campaign)) $deal_data['fields']['UTM_CAMPAIGN'] = $utm_campaign;
if (!empty($utm_content))  $deal_data['fields']['UTM_CONTENT'] = $utm_content;
if (!empty($utm_term))     $deal_data['fields']['UTM_TERM'] = $utm_term;

// Добавляем метрику
if (!empty($metrica_client_id)) $deal_data['fields']['UF_CRM_1756887338233'] = $metrica_client_id;

// --- Отправка сделки ---
try {
    $response = $httpClient->post(BITRIX_WEBHOOK_URL, $deal_data);

    if ($response === false || $httpClient->getStatus() >= 400) {
        throw new Exception('Сетевая ошибка или неверный HTTP-статус от CRM: ' . $httpClient->getStatus());
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ошибка JSON: ' . json_last_error_msg());
    }
    if (isset($result['error'])) {
        throw new Exception($result['error_description'] ?? 'Ошибка API Bitrix24.');
    }
    if (isset($result['result']) && $result['result'] > 0) {
        send_json_response(true, ['message' => 'Сделка успешно создана!', 'deal_id' => $result['result']]);
    } else {
        throw new Exception('CRM вернул неожиданный ответ, сделка не создана.');
    }

} catch (Exception $e) {
    error_log("B24 Deal Error: " . $e->getMessage());
    send_json_response(false, ['error' => 'Ошибка при отправке данных в CRM. Попробуйте позже.']);
}
?>
