<?php
// === НАСТРОЙКА ===
$token = '8412497931:AAGeIliZMrt-L76DsE6cvNLstqW9ffss4jI'; // <-- вставь сюда

// URL мини-приложения
$webAppUrl = 'https://gis-mining.ru/tg-app/';

// === ЧИТАЕМ ВХОДЯЩЕЕ СООБЩЕНИЕ ОТ TELEGRAM ===
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Можно залогировать для проверки (по желанию)
// file_put_contents(__DIR__ . '/bot_log.txt', $input . PHP_EOL, FILE_APPEND);

if (!isset($update['message'])) {
    exit;
}

$message = $update['message'];
$chat_id = $message['chat']['id'];
$text = $message['text'] ?? '';

// === ГОТОВИМ КНОПКУ С MINI APP ===
$replyMarkup = [
    'inline_keyboard' => [
        [
            [
                'text' => 'Открыть калькулятор 🧮',
                'web_app' => [
                    'url' => $webAppUrl
                ]
            ]
        ]
    ]
];

$data = [
    'chat_id' => $chat_id,
    'text' => "Запускаю калькулятор доходности 👇",
    'reply_markup' => json_encode($replyMarkup, JSON_UNESCAPED_UNICODE)
];

// Можно отвечать только на /start, если хочешь:
if ($text === '/start' || $text === '/menu') {
    file_get_contents(
        "https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query($data)
    );
}
