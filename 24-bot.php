<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === НАСТРОЙКИ ===
define('CLIENT_ID',     'local.68dce7d3e215c1.63012925');
define('CLIENT_SECRET', 'r1OXlKMSBCEcO1yELTXO7CTlLgy5YgPLa6RksE18HvtANbq10v');
define('REDIRECT_URI',  'https://gis-mining.ru/24-bot.php'); // тот же адрес

$STATE_FILE = __DIR__ . '/b24_state.json';

// === ВСПОМОГАТЕЛЬНЫЕ ===
function saveState($data) {
    global $STATE_FILE;
    file_put_contents($STATE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function loadState() {
    global $STATE_FILE;
    if (file_exists($STATE_FILE)) {
        return json_decode(file_get_contents($STATE_FILE), true);
    }
    return [];
}
function b24($method, $params = []) {
    $state = loadState();
    if (empty($state['access_token']) || empty($state['client_endpoint'])) {
        return ['error'=>'NO_TOKEN'];
    }
    $url = $state['client_endpoint'] . $method;
    $params['auth'] = $state['access_token'];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// === УСТАНОВКА ПРИЛОЖЕНИЯ ===
if (isset($_REQUEST['code'])) {
    $query = http_build_query([
        'grant_type' => 'authorization_code',
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'redirect_uri' => REDIRECT_URI,
        'code' => $_REQUEST['code'],
    ]);
    $res = json_decode(file_get_contents('https://oauth.bitrix.info/oauth/token/?' . $query), true);

    if (!empty($res['access_token'])) {
        saveState($res);

        // Регистрируем бота
        $bot = b24('imbot.register', [
            'CODE' => 'crm_deal_bot',
            'TYPE' => 'B',
            'EVENT_MESSAGE_ADD' => REDIRECT_URI . '?event=ONIMBOTMESSAGEADD',
            'EVENT_BOT_DELETE'  => REDIRECT_URI . '?event=ONIMBOTDELETE',
            'PROPERTIES' => [
                'NAME' => 'CRM Bot',
                'COLOR' => 'GREEN',
                'WORK_POSITION' => 'Автоматический бот для сделок',
            ]
        ]);

        if (!empty($bot['result'])) {
            $state = loadState();
            $state['BOT_ID'] = $bot['result'];
            saveState($state);
            echo "✅ Приложение установлено и бот зарегистрирован! BOT_ID=" . $bot['result'];
        } else {
            echo "Приложение установлено, но ошибка при регистрации бота: ";
            print_r($bot);
        }
    } else {
        echo "Ошибка получения токена: ";
        print_r($res);
    }
    exit;
}

// === СОБЫТИЯ ОТ БОТА ===
$event = $_REQUEST['event'] ?? '';
if ($event === 'ONIMBOTMESSAGEADD') {
    $data = json_decode(file_get_contents('php://input'), true);
    $dialogId = $data['data']['PARAMS']['DIALOG_ID'] ?? null;
    $botId = loadState()['BOT_ID'] ?? null;

    if ($dialogId && $botId) {
        b24('imbot.message.add', [
            'BOT_ID' => $botId,
            'DIALOG_ID' => $dialogId,
            'MESSAGE' => "Привет! Я CRM Bot 🚀",
        ]);
    }
    echo 'OK';
    exit;
}
if ($event === 'ONIMBOTDELETE') {
    saveState([]); // очистим state
    echo 'OK';
    exit;
}

echo "Бот работает. Для установки откройте приложение в Битрикс24.";
