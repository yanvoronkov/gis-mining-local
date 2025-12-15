<?php

// --- Начало Настроек ---
$bitrixWebhookUrl = 'https://gis-mining.bitrix24.ru/rest/114/gvt745oo0cej55ql/crm.lead.add.json';
$defaultLeadTitle = 'Заявка с сайта gis-mining.ru';
// --- ВАЖНО: Добавьте ваш СЕКРЕТНЫЙ ключ Яндекс.Капчи ---
// $yandexCaptchaSecretKey = 'ysc2_aGAiniCBTLvmOKw3y7OmTPlnIfQKKhzyT4xLROhu82abbd6e';
// --- Конец Настроек ---

// Опционально: включить отображение ошибок PHP ТОЛЬКО для отладки
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

// Устанавливаем заголовок для JSON ответа СРАЗУ
header('Content-Type: application/json');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// Получаем данные из формы
$name = trim($_POST['client_name'] ?? '');
$phone = trim($_POST['client_phone'] ?? '');
$formName = trim($_POST['form_name'] ?? '');
// --- Получаем токен капчи ---
// $captchaToken = $_POST['smart-token'] ?? '';

// Получаем UTM-метки
$utm_source = $_POST['utm_source'] ?? '';
$utm_medium = $_POST['utm_medium'] ?? '';
$utm_campaign = $_POST['utm_campaign'] ?? '';
$utm_content = $_POST['utm_content'] ?? '';
$utm_term = $_POST['utm_term'] ?? '';

// --- Валидация базовых полей ---
if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Имя и Телефон обязательны для заполнения.']);
    exit;
}

// --- НОВАЯ СЕРВЕРНАЯ ВАЛИДАЦИЯ КАПЧИ ---
// if (empty($captchaToken)) {
//     http_response_code(400);
//     echo json_encode(['success' => false, 'error' => 'Не получен токен капчи.']);
//     exit;
// }

// if (empty($yandexCaptchaSecretKey) || strpos($yandexCaptchaSecretKey, 'ВАШ_СЕКРЕТНЫЙ_КЛЮЧ') !== false) {
//      error_log("Yandex SmartCaptcha Secret Key is not configured!");
//      http_response_code(500);
//      echo json_encode(['success' => false, 'error' => 'Ошибка конфигурации сервера (капча).']);
//      exit;
// }

// // URL для проверки токена Яндекс.Капчи
// $validationUrl = 'https://smartcaptcha.yandexcloud.net/validate';

// // Данные для отправки на сервер Яндекса
// $validationData = [
//     'secret' => $yandexCaptchaSecretKey,
//     'token' => $captchaToken,
//     'ip' => $_SERVER['REMOTE_ADDR'] // IP пользователя (опционально, но рекомендуется)
// ];

// // Отправляем запрос на сервер Яндекса для валидации
// $ch_captcha = curl_init();
// curl_setopt($ch_captcha, CURLOPT_URL, $validationUrl);
// curl_setopt($ch_captcha, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch_captcha, CURLOPT_POST, true);
// curl_setopt($ch_captcha, CURLOPT_POSTFIELDS, http_build_query($validationData));
// curl_setopt($ch_captcha, CURLOPT_CONNECTTIMEOUT, 3); // Короткий таймаут для капчи
// curl_setopt($ch_captcha, CURLOPT_TIMEOUT, 5);
// $validationResponse = curl_exec($ch_captcha);
// $captchaCurlError = curl_error($ch_captcha);
// curl_close($ch_captcha);

// // Обрабатываем результат валидации капчи
// if ($captchaCurlError) {
//     error_log("Yandex Captcha cURL Error: " . $captchaCurlError);
//     http_response_code(500);
//     echo json_encode(['success' => false, 'error' => 'Ошибка сети при проверке капчи.']);
//     exit;
// }

// $validationResult = json_decode($validationResponse, true);

// // Проверяем статус от Яндекса
// if (!isset($validationResult['status']) || $validationResult['status'] !== 'ok') {
//     $errorMessage = $validationResult['message'] ?? 'Неизвестная ошибка капчи';
//     error_log("Yandex Captcha Validation Failed: " . $errorMessage . " | Response: " . $validationResponse);
//     http_response_code(400); // Ошибка пользователя (капча не пройдена)
//     echo json_encode(['success' => false, 'error' => 'Проверка капчи не пройдена.']);
//     exit;
// }
// --- КОНЕЦ СЕРВЕРНОЙ ВАЛИДАЦИИ КАПЧИ ---

// --- Если капча прошла успешно, продолжаем с отправкой в Битрикс ---

// --- Формирование данных для Битрикс ---
$leadTitle = !empty($formName) ? "Заявка: " . $formName : $defaultLeadTitle;
$comments = "Источник: Форма на сайте ({$formName})\n";
$comments .= "--- UTM ---\n";
$comments .= "Source: {$utm_source}\nMedium: {$utm_medium}\nCampaign: {$utm_campaign}\nContent: {$utm_content}\nTerm: {$utm_term}\n";

$bitrixData = [
    // ... (данные для Битрикс остаются как раньше) ...
    'fields' => [
        'TITLE' => $leadTitle,
        'NAME' => $name,
        'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']],
        'COMMENTS' => $comments,
        'SOURCE_ID' => 'WEB',
        'UTM_SOURCE' => $utm_source,
        'UTM_MEDIUM' => $utm_medium,
        'UTM_CAMPAIGN' => $utm_campaign,
        'UTM_CONTENT' => $utm_content,
        'UTM_TERM' => $utm_term,
    ],
    'params' => ["REGISTER_SONET_EVENT" => "Y"]
];

// --- Отправка запроса в Битрикс через cURL ---
$ch_bitrix = curl_init();
// ... (настройки cURL для Битрикс как раньше) ...
curl_setopt($ch_bitrix, CURLOPT_URL, $bitrixWebhookUrl);
curl_setopt($ch_bitrix, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_bitrix, CURLOPT_POST, true);
curl_setopt($ch_bitrix, CURLOPT_POSTFIELDS, http_build_query($bitrixData));
curl_setopt($ch_bitrix, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch_bitrix, CURLOPT_TIMEOUT, 10);

$bitrixResponse = curl_exec($ch_bitrix);
$bitrixCurlError = curl_error($ch_bitrix);
$bitrixHttpCode = curl_getinfo($ch_bitrix, CURLINFO_HTTP_CODE);
curl_close($ch_bitrix);

// --- Обработка результата отправки в Битрикс (как раньше, но JSON уже установлен) ---
if ($bitrixCurlError) {
    error_log("Bitrix Webhook cURL Error: " . $bitrixCurlError);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка сети при связи с CRM.']);
    exit;
}

if ($bitrixHttpCode != 200) {
    error_log("Bitrix Webhook HTTP Error: Code {$bitrixHttpCode}, Response: {$bitrixResponse}");
    http_response_code($bitrixHttpCode);
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера CRM.', 'details' => 'HTTP Code: ' . $bitrixHttpCode]);
    exit;
}

$bitrixResult = json_decode($bitrixResponse, true);

if ($bitrixResult === null && json_last_error() !== JSON_ERROR_NONE) {
    error_log("Bitrix Webhook JSON Decode Error: " . json_last_error_msg() . " | Response: " . $bitrixResponse);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не удалось обработать ответ от CRM.']);
    exit;
}

if (isset($bitrixResult['result'])) {
    echo json_encode(['success' => true]);
    exit;
} else {
    $bitrixError = $bitrixResult['error_description'] ?? 'Неизвестная ошибка от CRM';
    error_log("Bitrix Webhook API Error: " . $bitrixError . " | Response: " . $bitrixResponse);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ошибка при обработке данных в CRM.', 'details' => $bitrixError]);
    exit;
}

?>