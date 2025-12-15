<?php

$app1760940257Config = [
  // Настройки калькулятора
  'calculator' => [
    // Локальная валюта
    'RUB' => [
      'symbol' => '₽',
    ],

    // Валюта котировки
    'USDT' => [
      'symbol' => '$',
      'site' => 'coingecko.com',
      'bases' => [
        'BTC' => null,
        'LTC' => null,
        'DOGE' => null,
        'RUB' => null,
      ],
    ],

    // Алгоритмы
    'algorithmItems' => [
      [
        'code' => 'BTC',
      ],
      [
        'code' => 'LTC+DOGE',
      ],
    ],

    // Монеты
    'coins' => [
      'BTC' => [
        'code' => 'BTC',
        'name' => 'Bitcoin',
        'site' => 'coingecko.com',
        'symbol' => '₿',
        'ffps' => null,
        'ffpsUnit' => 'TH',
        'ffpsSite' => 'viabtc.com',
      ],
      'LTC' => [
        'code' => 'LTC',
        'name' => 'Litecoin',
        'site' => 'coingecko.com',
        'symbol' => 'Ł',
        'ffps' => null,
        'ffpsUnit' => 'GH',
        'ffpsSite' => 'viabtc.com',
      ],
      'DOGE' => [
        'code' => 'DOGE',
        'name' => 'Dogecoin',
        'site' => 'coingecko.com',
        'symbol' => 'D',
        'ffps' => null,
        'ffpsUnit' => 'GH',
        'ffpsSite' => 'viabtc.com',
      ],
    ],

    // Устройства для майнинга
    'deviceItems' => [
      [
        'id' => 0,
        'brand' => 'Свое устройство',
        'name' => 'Свое устройство',
        'description' => 'Введите параметры вручную',
        'hashrateTh' => null,
        'power' => null,
        'price' => null,
        'image' => '/mining-calculator/assets/img/self-device.png',
      ],
      // ... другие устройства загружаются из Битрикс
    ],

    // Единицы измерения цены устройств
    'devicePriceUnitItems' => [
      [
        'value' => 'RUB',
        'code' => 'RUB',
        'name' => 'руб.',
      ],
      [
        'value' => 'USD',
        'code' => 'USD',
        'name' => '$',
      ],
    ],

    // Единицы измерения мощности устройств
    'deviceHashrateUnitItems' => [
      [
        'value' => 'th',
        'name' => 'TH/s',
      ],
      [
        'value' => 'mh',
        'name' => 'MH/s',
      ],
    ],

    // Цена за кВт/ч
    'powerPrice' => 5.3,

    // Ссылка на политику конфиденциальности
    'policyUrl' => 'https://gis-mining.ru/policy-confidenciales/',
    'policyUrl2' => 'https://gis-mining.ru/soglasie-s-obrabotkoy/',

    // Локализация текстов
    'localization' => [
      'coinLabel' => 'Добываемая криптовалюта',
      // 'brandLabel' => 'Бренд',
      // 'brandTooltip' => 'Выберите бренд устройства для майнинга из списка или выберите "Свое устройство", чтобы ввести параметры вручную.',
      'deviceSelectorLabel' => 'Модель',
      'deviceSelectorTooltip' => 'Выберите модель устройства для майнинга из списка.',
      // 'brandSelectorPlaceholder' => 'Выберите бренд',
      'deviceSelectorPlaceholder' => 'Выберите модель',
      'deviceSelectorEmptyMessage' => 'Модель не найдена',
      'devicePriceLabel' => 'Стоимость устройства',
      'devicePriceTooltip' => 'Стоимость одного устройства.',
      'enterPricePlaceholder' => 'Введите стоимость',
      'enterHashratePlaceholder' => 'Введите хэшрейт',
      'enterPowerPlaceholder' => 'Введите мощность',
      'powerLabel' => 'Мощность (Вт)',
      'powerTooltip' => 'Потребляемая мощность устройства для майнинга.',
      'hashrateLabel' => 'Хэшрейт',
      'hashrateTooltip' => 'Общая вычислительная мощность устройства для майнинга.',
      'quantityLabel' => 'Количество',
      'quantityTooltip' => 'Количество устройств для майнинга.',
      'totalLabel' => 'Итого',
      'totalTooltip' => 'Общая стоимость всех устройств для майнинга.',
      'powerPriceLabel' => 'Цена электроэнергии',
      'powerPriceTooltip' => 'Цена за кВт/ч',
      'powerUnit' => 'кВт/ч',
      'basicPlacement' => 'Базовое размещение',
      'customPriceLabel' => 'Задать свою стоимость',
      'exchangeRateLabel' => 'Рассчитать по своему курсу',
      'exchangeRateTooltip' => 'Использовать пользовательский курс для расчета доходности.',
      'calculateButton' => 'Рассчитать доходность',
      'resetButton' => 'Сбросить данные',
      'resultsTitle' => 'Результат расчета',
      'resultProfitTitle' => 'Чистая прибыль',
      'resultProfitTooltip' => 'Прибыль за вычетом затрат на электроэнергию.',
      'resultProfitDescription' => 'после вычета затрат на электричество',
      'resultIncomeTitle' => 'Доходность',
      'resultIncomeTooltip' => 'Общий доход до вычета затрат на электроэнергию.',
      'resultIncomePercentTitle' => 'Доход годовых, %',
      'resultIncomePercentTooltip' => 'Процент доходности за год.',
      'resultPaybackTitle' => 'Окупаемость',
      'resultPaybackTooltip' => 'Примерный срок окупаемости оборудования при текущих условиях.',
      'resultEquipmentTitle' => 'Стоимость оборудования',
      'resultEquipmentTooltip' => 'Стоимость приобретенного оборудования.',
      'resultPowerTitle' => 'Расходы на электроэнергию',
      'resultPowerTooltip' => 'Стоимость электроэнергии за выбранный период.',
      'resultPerLabel' => 'в',
      'resultDayLabel' => ['день', 'дня', 'дней'],
      'resultMonthLabel' => ['месяц', 'месяца', 'месяцев'],
      'resultYearLabel' => ['год', 'года', 'лет'],
      'resultDayLabelShort' => 'день',
      'resultMonthLabelShort' => 'мес',
      'resultYearLabelShort' => 'год',
      'getOfferButton' => 'Получить КП',
      'getCalculateLinkButton' => 'Ссылка на расчет',
      'getCalculateLinkButtonSuccess' => 'Ссылка скопирована',
      'warningText' => 'Расчёт носит предварительный характер и зависит от курса валют, сложности сети и других рыночных факторов.',
      'dialogOfferTitle' => 'Получить коммерческое предложение',
      'formNamePlaceholder' => 'Имя',
      'formPhonePlaceholder' => 'Телефон',
      'formCommentPlaceholder' => 'Комментарий',
      'policyTextPrefix' => 'Согласен(а) с',
      'policyLinkText' => 'политикой конфиденциальности',
      'policyTextPrefix2' => 'и с',
      'policyLinkText2' => 'обработкой персональных данных',
      'submitButton' => 'Отправить заявку',
      'closeButton' => 'Закрыть',
      'deviceHashrateLabel' => 'Хэшрейт',
      'devicePowerLabel' => 'Потребление',
      'unitWH' => 'Вт/ч',
      'callbackDescription' => 'Хотите получить професионнальную инвестиционную стратегию от нашего экперта по майнингу?',
      'callbackDescription2' => 'Оставьте контакты',
      'priceValidate' => 'Для расчета окупаемости введите стоимость оборудования',
      'powerValidate' => 'Для расчета окупаемости введите потребляемую мощность оборудования',
      'hashrateValidate' => 'Для расчета окупаемости введите хэшрейт оборудования',
      'usdtTooltip' => 'Курс USDT',
      'successMessage' => 'Спасибо! Ваша заявка отправлена.',
      'errorMessage' => 'Произошла ошибка. Попробуйте позже.',
    ],
  ],
];
