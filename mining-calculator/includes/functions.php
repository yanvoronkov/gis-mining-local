<?php

/**
 * Get crypto to USD exchange rates from CoinGecko public API
 * @return array{btc: float|null, ltc: float|null, doge: float|null}|null
 */
function getCryptoUsdRates() {
  $cacheDir = __DIR__ . '/../runtime/cache';
  $cacheFile = $cacheDir . '/crypto_usd_rates.json';
  $currentCacheLifetime = 3600;

  if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
  }

  $result = [
    'btc' => null,
    'ltc' => null,
    'doge' => null,
  ];

  $needUpdate = true;
  $cacheData = null;

  if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData) {
      if (isset($cacheData['timestamp']) &&
          (time() - $cacheData['timestamp']) < $currentCacheLifetime) {
        $result = [
          'btc' => $cacheData['btc'] ?? null,
          'ltc' => $cacheData['ltc'] ?? null,
          'doge' => $cacheData['doge'] ?? null,
        ];
        $needUpdate = false;
      }
    }
  }

  // Get prices from CoinGecko (bitcoin, litecoin, dogecoin -> usd)
  if ($needUpdate) {
    try {
      $url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,litecoin,dogecoin&vs_currencies=usd';
      $opts = [
        'http' => [
          'method'  => 'GET',
          'header'  => "User-Agent: mining-calculator/1.0\r\nAccept: application/json\r\n",
          'timeout' => 10,
        ]
      ];
      $context = stream_context_create($opts);
      $json = @file_get_contents($url, false, $context);
      if ($json === false) {
        throw new Exception('Failed to retrieve rates from CoinGecko');
      }

      $data = json_decode($json, true);
      if ($data === null) {
        throw new Exception('Invalid response from CoinGecko');
      }

      $result['btc'] = isset($data['bitcoin']['usd']) ? (float)$data['bitcoin']['usd'] : null;
      $result['ltc'] = isset($data['litecoin']['usd']) ? (float)$data['litecoin']['usd'] : null;
      $result['doge'] = isset($data['dogecoin']['usd']) ? (float)$data['dogecoin']['usd'] : null;

    } catch (Exception $e) {
      // Use cached data if available
      if ($cacheData) {
        $result['btc'] = $cacheData['btc'] ?? null;
        $result['ltc'] = $cacheData['ltc'] ?? null;
        $result['doge'] = $cacheData['doge'] ?? null;
      }
    }
  }

  $cacheToSave = [
    'btc' => $result['btc'],
    'ltc' => $result['ltc'],
    'doge' => $result['doge'],
    'timestamp' => $needUpdate ? time() : ($cacheData['timestamp'] ?? time()),
  ];

  @file_put_contents($cacheFile, json_encode($cacheToSave));

  return $result;
}

/**
 * Get USDT to RUB exchange rate from CoinGecko public API
 * @return array{current_timestamp: int, current: float}|null
 */
function getUsdtRubRate() {
  $cacheDir = __DIR__ . '/../runtime/cache';
  $cacheFile = $cacheDir . '/usdtrub_rate.json';
  $currentCacheLifetime = 3600;

  if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
  }

  $result = [
    'current' => null,
    'current_timestamp' => 0,
  ];

  $needUpdateCurrent = true;
  $cacheData = null;

  if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData) {
      if (isset($cacheData['current']) &&
          isset($cacheData['current_timestamp']) &&
          (time() - $cacheData['current_timestamp']) < $currentCacheLifetime) {
        $result = $cacheData;
        $needUpdateCurrent = false;
      }
    }
  }

  // Current price from CoinGecko (tether -> rub)
  if ($needUpdateCurrent) {
    try {
      $url = 'https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=rub';
      $opts = [
        'http' => [
          'method'  => 'GET',
          'header'  => "User-Agent: mining-calculator/1.0\r\nAccept: application/json\r\n",
          'timeout' => 10,
        ]
      ];
      $context = stream_context_create($opts);
      $json = @file_get_contents($url, false, $context);
      if ($json === false) {
        throw new Exception('Failed to retrieve current rate from CoinGecko');
      }

      $data = json_decode($json, true);
      if ($data === null || !isset($data['tether']['rub'])) {
        throw new Exception('Invalid response from CoinGecko for tether->rub');
      }

      $result['current'] = (float)$data['tether']['rub'];
      $result['current_timestamp'] = time();
    } catch (Exception $e) {
      if (isset($cacheData['current'])) {
        $result['current'] = $cacheData['current'];
        $result['current_timestamp'] = $cacheData['current_timestamp'] ?? time();
      } else {
        $result['current_timestamp'] = time();
      }
    }
  }

  $cacheToSave = [
    'current' => $result['current'],
    'current_timestamp' => $result['current_timestamp'],
  ];

  @file_put_contents($cacheFile, json_encode($cacheToSave));

  return $result;
}

/**
 * Get mining FFPS data from ViaBTC public API
 * @return array{timestamp: int, btc: float|null, ltc: float|null, doge: float|null}|null
 */
function getCryptoFfps() {
  $cacheDir = __DIR__ . '/../runtime/cache';
  $cacheFile = $cacheDir . '/crypto_ffps.json';
  $cacheLifetime = 86400; // 24 часа

  if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
  }

  // Проверяем кеш
  if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData && isset($cacheData['timestamp']) && (time() - $cacheData['timestamp']) < $cacheLifetime) {
      return $cacheData;
    }
  }

  $opts = [
    'http' => [
      'method'  => 'GET',
      'header'  => "User-Agent: mining-calculator/1.0\r\nAccept: application/json\r\n",
      'timeout' => 10,
    ]
  ];

  try {
    $context = stream_context_create($opts);
    $response = @file_get_contents('https://www.viabtc.com/res/tools/calculator', false, $context);

    if ($response === false) {
      throw new Exception('Failed to retrieve data from ViaBTC');
    }

    $json = json_decode($response, true);
    if ($json === null || !isset($json['data'])) {
      throw new Exception('Invalid JSON from ViaBTC');
    }

    $result = [
      'timestamp' => time(),
      'btc' => null,
      'ltc' => null,
      'doge' => null
    ];

    // Парсим данные для каждой криптовалюты
    foreach ($json['data'] as $item) {
      $coin = strtolower($item['coin']);

      if ($coin === 'btc' && isset($item['profit']['BTC'])) {
        $result['btc'] = (float)$item['profit']['BTC'];
      } elseif ($coin === 'ltc' && isset($item['profit']['LTC'])) {
        $result['ltc'] = (float)$item['profit']['LTC'];

        // Для LTC также извлекаем DOGE из gifts_profit
        if (isset($item['gifts_profit']['DOGE']['DOGE'])) {
          $result['doge'] = (float)$item['gifts_profit']['DOGE']['DOGE'];
        }
      }
    }

    // Сохраняем в кеш
    file_put_contents($cacheFile, json_encode($result));
    return $result;

  } catch (Exception $e) {
    // В случае ошибки пытаемся использовать устаревший кеш
    if (file_exists($cacheFile)) {
      $cacheData = json_decode(file_get_contents($cacheFile), true);
      if ($cacheData) {
        return $cacheData;
      }
    }
    return null;
  }
}

/**
 * Get the list of products
 * @return array<int, array<string, mixed>>
 */
function getProducts() {
  define('NO_KEEP_STATISTIC', true);
  define('NOT_CHECK_PERMISSIONS', true);

  \Bitrix\Main\Loader::includeModule('iblock');
  \Bitrix\Main\Loader::includeModule('catalog');

  $iblockId = 1; // ID инфоблока "ASIC-майнеры"

  $arFilter = [
    'IBLOCK_ID' => $iblockId,
    'ACTIVE' => 'Y',
    // 'PROPERTY_IN_STOCK_VALUE' => 'Да',
    'PROPERTY_IN_CALCULATOR_VALUE' => 'Да',
    // 'PROPERTY_CRYPTO_VALUE' => 'BTC',
    '!PROPERTY_IN_CALCULATOR_VALUE' => false,
    // '!PROPERTY_HASHRATE_TH' => false,
    '!PROPERTY_POWER' => false,
  ];

  $arSelect = [
    'ID',
    'NAME',
    'DETAIL_PICTURE',
    'PROPERTY_MANUFACTURER',
    'PROPERTY_SORT_BY_PRICE',
    'PROPERTY_CRYPTO',
    'PROPERTY_HASHRATE_TH',
    'PROPERTY_HASHRATE_MH',
    'PROPERTY_POWER',
    'DETAIL_PAGE_URL',
  ];

  $rsElements = CIBlockElement::GetList(['SORT' => 'ASC'], $arFilter, false, false, $arSelect);

  $products = [];
  while ($obElement = $rsElements->GetNextElement()) {
    $arFields = $obElement->GetFields();

    $imageUrl = '';
    if ($arFields['DETAIL_PICTURE']) {
      $imageFile = CFile::GetFileArray($arFields['DETAIL_PICTURE']);
      if ($imageFile) {
        $imageUrl = $imageFile['SRC'];
      }
    }

    $price = 0;
    $arPrice = CCatalogProduct::GetOptimalPrice($arFields['ID'], 1, [], 'N');
    if (!empty($arPrice['RESULT_PRICE']['BASE_PRICE'])) {
      $price = (int)$arPrice['RESULT_PRICE']['BASE_PRICE'];
    }

    $products[] = [
      'id' => (int)$arFields['ID'],
      'brand' => (string)$arFields['PROPERTY_MANUFACTURER_VALUE'],
      'name' => (string)$arFields['NAME'],
      'image' => (string)$imageUrl,
      'price' => $price,
      'coin' => $arFields['PROPERTY_CRYPTO_VALUE'],
      'hashrateTh' => (int)$arFields['PROPERTY_HASHRATE_TH_VALUE'],
      'hashrateMh' => (int)$arFields['PROPERTY_HASHRATE_MH_VALUE'],
      'power' => (int)$arFields['PROPERTY_POWER_VALUE'],
      'url' => (string)($arFields['DETAIL_PAGE_URL'] ?? ''),
    ];
  }

  return $products;
}
