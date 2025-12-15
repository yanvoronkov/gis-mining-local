<?php
// /local/api/mining/get_market_data.php

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Bitrix\Main\Data\Cache;

header('Content-Type: application/json; charset=utf-8');

// ----------------------------------------------------
// CACHE — обновляем данные раз в 10 минут
// ----------------------------------------------------
$cacheTime = 600; // 10 минут
$cacheId   = "mining_market_data_v8"; // новая версия кеша
$cacheDir  = "/mining_market_data";

$cache = Cache::createInstance();

// Если кеш актуален — сразу отдаём данные
if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
    echo json_encode($cache->getVars(), JSON_UNESCAPED_UNICODE);
    die();
}

// ----------------------------------------------------
// HTTP helper с защитой
// ----------------------------------------------------
function http_get_json($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'GisMiningBot/1.0',
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    if (!$res) return null;

    $json = json_decode($res, true);
    return $json ?: $res;
}

$data = [];

/* ====================================================
    1. CoinGecko — курсы BTC, LTC, DOGE, USDT
==================================================== */

$cg = http_get_json(
    "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,litecoin,dogecoin,tether&vs_currencies=usd,rub"
);

$data["btc_usd"]  = $cg["bitcoin"]["usd"] ?? null;
$data["btc_rub"]  = $cg["bitcoin"]["rub"] ?? null;

$data["usdt_rub"] = $cg["tether"]["rub"] ?? null;

$data["ltc_usd"]  = $cg["litecoin"]["usd"] ?? null;
$data["ltc_rub"]  = $cg["litecoin"]["rub"] ?? null;

$data["doge_usd"] = $cg["dogecoin"]["usd"] ?? null;
$data["doge_rub"] = $cg["dogecoin"]["rub"] ?? null;

/* ====================================================
    2. BTC Difficulty — Blockchain.info
==================================================== */

$difficultyRaw = @file_get_contents("https://blockchain.info/q/getdifficulty");

$data["difficulty"] = (is_numeric($difficultyRaw))
    ? floatval($difficultyRaw)
    : null;

/* ====================================================
    3. Средняя комиссия за блок (BTC) — mempool.space
==================================================== */

$feeData = http_get_json("https://mempool.space/api/v1/mining/blocks/fees/24h");

$avgFeeBtc = 0.0;
if (!empty($feeData) && isset($feeData["averageFee"])) {
    $avgFeeBtc = floatval($feeData["averageFee"]) / 1e8; // sat → BTC
}

/* ====================================================
    4. FPPS BTC — классическая формула через difficulty
==================================================== */

$blockRewardBtc  = 3.125;
$blocksPerDayBtc = 144;
$diff            = $data["difficulty"];

if ($diff) {
    $networkHashrateTh = $diff * pow(2, 32) / 600 / 1e12;

    if ($networkHashrateTh > 0) {
        $totalRewardPerDay = ($blockRewardBtc + $avgFeeBtc) * $blocksPerDayBtc;
        $data["fpps_btc_per_th_day"] = $totalRewardPerDay / $networkHashrateTh;
    } else {
        $data["fpps_btc_per_th_day"] = null;
    }
} else {
    $data["fpps_btc_per_th_day"] = null;
}

/* ====================================================
    5. Scrypt (LTC + DOGE) — Blockchair
==================================================== */

// ---------------- Litecoin ----------------
$ltcStats = http_get_json("https://api.blockchair.com/litecoin/stats");

$ltcDifficulty   = $ltcStats["data"]["difficulty"]             ?? null;
$ltcBlockReward  = $ltcStats["data"]["block_reward"]           ?? 6.25;
$ltcBlockTimeSec = $ltcStats["data"]["average_block_time_24h"] ?? 150;

$data["ltc_difficulty"]   = $ltcDifficulty;
$data["ltc_block_reward"] = $ltcBlockReward;
$data["ltc_block_time"]   = $ltcBlockTimeSec;

if ($ltcDifficulty && $ltcBlockTimeSec > 0) {
    $ltcNetworkHashrateMh = $ltcDifficulty * pow(2, 32) / $ltcBlockTimeSec / 1e6;

    $ltcBlocksPerDay       = 86400 / $ltcBlockTimeSec;
    $ltcTotalRewardPerDay  = $ltcBlockReward * $ltcBlocksPerDay;

    $data["fpps_ltc_per_mh_day"] =
        ($ltcNetworkHashrateMh > 0)
            ? $ltcTotalRewardPerDay / $ltcNetworkHashrateMh
            : null;
} else {
    $data["fpps_ltc_per_mh_day"] = null;
}

// ---------------- Dogecoin ----------------
$dogeStats = http_get_json("https://api.blockchair.com/dogecoin/stats");

$dogeDifficulty   = $dogeStats["data"]["difficulty"]             ?? null;
$dogeBlockReward  = $dogeStats["data"]["block_reward"]           ?? 10000.0;
$dogeBlockTimeSec = $dogeStats["data"]["average_block_time_24h"] ?? 60.0;

$data["doge_difficulty"]   = $dogeDifficulty;
$data["doge_block_reward"] = $dogeBlockReward;
$data["doge_block_time"]   = $dogeBlockTimeSec;

if ($dogeDifficulty && $dogeBlockTimeSec > 0) {
    $dogeNetworkHashrateMh = $dogeDifficulty * pow(2, 32) / $dogeBlockTimeSec / 1e6;

    $dogeBlocksPerDay      = 86400 / $dogeBlockTimeSec;
    $dogeTotalRewardPerDay = $dogeBlockReward * $dogeBlocksPerDay;

    $data["fpps_doge_per_mh_day"] =
        ($dogeNetworkHashrateMh > 0)
            ? $dogeTotalRewardPerDay / $dogeNetworkHashrateMh
            : null;
} else {
    $data["fpps_doge_per_mh_day"] = null;
}

/* ====================================================
    CACHE SAVE
==================================================== */

if ($cache->startDataCache()) {
    $cache->endDataCache($data);
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
