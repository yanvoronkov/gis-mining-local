<?php

require_once __DIR__.'/../bitrix/modules/main/include/prolog_before.php';
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/config.php';

try {
  $cryptoUsdRates = getCryptoUsdRates();
  $usdRubRate = getUsdtRubRate();
  $products = getProducts();

  $app1760940257Config['calculator']['USDT']['bases']['RUB'] = $usdRubRate;
  $app1760940257Config['calculator']['USDT']['bases']['BTC'] = $cryptoUsdRates['btc'];
  $app1760940257Config['calculator']['USDT']['bases']['LTC'] = $cryptoUsdRates['ltc'];
  $app1760940257Config['calculator']['USDT']['bases']['DOGE'] = round($cryptoUsdRates['doge'], 2);
  $app1760940257Config['calculator']['deviceItems'] = array_values(array_merge($app1760940257Config['calculator']['deviceItems'], $products));

  $cryptoFfps = getCryptoFfps($cryptoUsdRates['btc'], $usdRubRate);
  $app1760940257Config['calculator']['coins']['BTC']['ffps'] = isset($cryptoFfps['btc']) ? $cryptoFfps['btc'] : 4.1e-7;
  $app1760940257Config['calculator']['coins']['LTC']['ffps'] = isset($cryptoFfps['ltc']) ? $cryptoFfps['ltc'] : 3.7975443;
  $app1760940257Config['calculator']['coins']['DOGE']['ffps'] = isset($cryptoFfps['doge']) ? $cryptoFfps['doge'] : 0.00102614;

  unset($coin);

  echo json_encode([
    'success' => true,
    'data' => $app1760940257Config['calculator'],
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $err) {
  die(json_encode([
    'success' => false,
    'error' => $err->getMessage(),
  ]));
}
