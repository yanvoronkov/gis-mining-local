document.addEventListener('DOMContentLoaded', function(){
  const productElement = document.getElementById('profit-calculator');
  if (!productElement) {
    return;
  }

  const dir = location.origin + '/mining-calculator';
  const isDebug = new URLSearchParams(window.location.search).get('_debug') == '1';

  const HOURS_PER_DAY = 24;
  const DAYS_PER_MONTH = 30.5;
  const DAYS_PER_YEAR = 365;
  const MONTHS_PER_YEAR = 12;
  const DEFAULT_TARIF = 5.3;

  const device = {
    coin: document.getElementById('pc-device-coin').value || null,
    hashrateTh: document.getElementById('pc-device-hashrate-th').value || 0,
    hashrateMh: document.getElementById('pc-device-hashrate-mh').value || 0,
    power: document.getElementById('pc-device-power').value || 0,
    price: document.getElementById('pc-device-price').value || 0,
  };

  const btcUsdRateOrig = document.getElementById('pc-btcusd-rate').value; // Курс BTCUSD
  const ltcUsdRateOrig = document.getElementById('pc-ltcusd-rate').value; // Курс LTCUSD
  const dogeUsdRateOrig = document.getElementById('pc-dogeusd-rate').value; // Курс DOGEUSD
  const usdtRubRateOrig = document.getElementById('pc-usdtrub-rate').value; // Курс USDTRUB

  const coins = {
    BTC: {
      'ffps': document.getElementById('pc-btc-ffps').value,
    },
    LTC: {
      'ffps': document.getElementById('pc-ltc-ffps').value,
    },
    DOGE: {
      'ffps': document.getElementById('pc-doge-ffps').value,
    },
  };

  document.profitCalculator.pcQuantity.addEventListener('input', function () {
    calculate();
  });
  document.profitCalculator.pcTariff.addEventListener('input', function () {
    calculate();
  });


  // exchangeRateBtcUsed
  if (document.querySelector('.profit-calculator__field--exchange-rate-btc-used')) {
    const exchangeRateBtcUsedDiffMin = -50;
    const exchangeRateBtcUsedDiffMax = 200;
    let sliderExchangeRateBtcUsedDiff
    document.profitCalculator.exchangeRateBtcUsed.value = btcUsdRateOrig;
    sliderExchangeRateBtcUsedDiff = ionRangeSlider(document.profitCalculator.exchangeRateBtcUsedDiffSlider, {
      grid: false,
      from: 0,
      min: exchangeRateBtcUsedDiffMin,
      max: exchangeRateBtcUsedDiffMax,
      step: 1,
      hide_min_max: false,
      hide_from_to: false,
      onChange: function (res) {
        handleExchangeRateBtcUsedChange(res.from);
        calculate();
      },
    });
    document.profitCalculator.exchangeRateBtcUsed.addEventListener('input', function () {
      const value = this.value.replace(/[^0-9,.]/gim, '').replace(/,/g, '.');
      this.value = value;
      handleExchangeRateBtcUsedDiffChange(value);
      calculate();
    });
    document.querySelector('.profit-calculator-exchange-rate-btc-used-reset').addEventListener('click', function () {
      resetExchangeRateBtcUsed();
      calculate();
    });
    document.querySelector('.profit-calculator__field-slider-wrapper--exchange-rate-btc-used').style.visibility = 'visible';
    function handleExchangeRateBtcUsedChange(pct) {
      if (pct == null) return;
      const base = btcUsdRateOrig;
      if (!base) return;
      const expected = base * (1 + pct / 100);
      if (document.profitCalculator.exchangeRateBtcUsed.value == null || document.profitCalculator.exchangeRateBtcUsed.value == '' || Math.abs(document.profitCalculator.exchangeRateBtcUsed.value - expected) > 1e-8) {
        document.profitCalculator.exchangeRateBtcUsed.value = round(expected, 2);
      }
    }
    function handleExchangeRateBtcUsedDiffChange(value) {
      if (value == null || value == '') return;
      if (!btcUsdRateOrig || btcUsdRateOrig === 0) return;
      const pct = (value / btcUsdRateOrig - 1) * 100;
      const clamped = clamp(pct, exchangeRateBtcUsedDiffMin, exchangeRateBtcUsedDiffMax);
      if (Math.abs((document.profitCalculator.exchangeRateBtcUsedDiffSlider.value ?? 0) - clamped) > 1e-8) {
        sliderExchangeRateBtcUsedDiff.update({ from: Math.round(clamped) });
      }
    }
    function resetExchangeRateBtcUsed() {
      if (btcUsdRateOrig) {
        document.profitCalculator.exchangeRateBtcUsed.value = btcUsdRateOrig;
        handleExchangeRateBtcUsedDiffChange(btcUsdRateOrig);
      }
    }
    resetExchangeRateBtcUsed();
  }

  // exchangeRateLtcUsed
  if (document.querySelector('.profit-calculator__field--exchange-rate-ltc-used')) {
    const exchangeRateLtcUsedDiffMin = -50;
    const exchangeRateLtcUsedDiffMax = 200;
    let sliderExchangeRateLtcUsedDiff
    document.profitCalculator.exchangeRateLtcUsed.value = ltcUsdRateOrig;
    sliderExchangeRateLtcUsedDiff = ionRangeSlider(document.profitCalculator.exchangeRateLtcUsedDiffSlider, {
      grid: false,
      from: 0,
      min: exchangeRateLtcUsedDiffMin,
      max: exchangeRateLtcUsedDiffMax,
      step: 1,
      hide_min_max: false,
      hide_from_to: false,
      onChange: function (res) {
        handleExchangeRateLtcUsedChange(res.from);
        calculate();
      },
    });
    document.profitCalculator.exchangeRateLtcUsed.addEventListener('input', function () {
      const value = this.value.replace(/[^0-9,.]/gim, '').replace(/,/g, '.');
      this.value = value;
      handleExchangeRateLtcUsedDiffChange(value);
      calculate();
    });
    document.querySelector('.profit-calculator-exchange-rate-ltc-used-reset').addEventListener('click', function () {
      resetExchangeRateLtcUsed();
      calculate();
    });
    document.querySelector('.profit-calculator__field-slider-wrapper--exchange-rate-ltc-used').style.visibility = 'visible';
    function handleExchangeRateLtcUsedChange(pct) {
      if (pct == null) return;
      const base = ltcUsdRateOrig;
      if (!base) return;
      const expected = base * (1 + pct / 100);
      if (document.profitCalculator.exchangeRateLtcUsed.value == null || document.profitCalculator.exchangeRateLtcUsed.value == '' || Math.abs(document.profitCalculator.exchangeRateLtcUsed.value - expected) > 1e-8) {
        document.profitCalculator.exchangeRateLtcUsed.value = round(expected, 2);
      }
    }
    function handleExchangeRateLtcUsedDiffChange(value) {
      if (value == null || value == '') return;
      if (!ltcUsdRateOrig || ltcUsdRateOrig === 0) return;
      const pct = (value / ltcUsdRateOrig - 1) * 100;
      const clamped = clamp(pct, exchangeRateLtcUsedDiffMin, exchangeRateLtcUsedDiffMax);
      if (Math.abs((document.profitCalculator.exchangeRateLtcUsedDiffSlider.value ?? 0) - clamped) > 1e-8) {
        sliderExchangeRateLtcUsedDiff.update({ from: Math.round(clamped) });
      }
    }
    function resetExchangeRateLtcUsed() {
      if (ltcUsdRateOrig) {
        document.profitCalculator.exchangeRateLtcUsed.value = ltcUsdRateOrig;
        handleExchangeRateLtcUsedDiffChange(ltcUsdRateOrig);
      }
    }
    resetExchangeRateLtcUsed();
  }

  // exchangeRateDogeUsed
  if (document.querySelector('.profit-calculator__field--exchange-rate-doge-used')) {
    const exchangeRateDogeUsedDiffMin = -50;
    const exchangeRateDogeUsedDiffMax = 200;
    let sliderExchangeRateDogeUsedDiff
    document.profitCalculator.exchangeRateDogeUsed.value = dogeUsdRateOrig;
    sliderExchangeRateDogeUsedDiff = ionRangeSlider(document.profitCalculator.exchangeRateDogeUsedDiffSlider, {
      grid: false,
      from: 0,
      min: exchangeRateDogeUsedDiffMin,
      max: exchangeRateDogeUsedDiffMax,
      step: 1,
      hide_min_max: false,
      hide_from_to: false,
      onChange: function (res) {
        handleExchangeRateDogeUsedChange(res.from);
        calculate();
      },
    });
    document.profitCalculator.exchangeRateDogeUsed.addEventListener('input', function () {
      const value = this.value.replace(/[^0-9,.]/gim, '').replace(/,/g, '.');
      this.value = value;
      handleExchangeRateDogeUsedDiffChange(value);
      calculate();
    });
    document.querySelector('.profit-calculator-exchange-rate-doge-used-reset').addEventListener('click', function () {
      resetExchangeRateDogeUsed();
      calculate();
    });
    document.querySelector('.profit-calculator__field-slider-wrapper--exchange-rate-doge-used').style.visibility = 'visible';
    function handleExchangeRateDogeUsedChange(pct) {
      if (pct == null) return;
      const base = dogeUsdRateOrig;
      if (!base) return;
      const expected = base * (1 + pct / 100);
      if (document.profitCalculator.exchangeRateDogeUsed.value == null || document.profitCalculator.exchangeRateDogeUsed.value == '' || Math.abs(document.profitCalculator.exchangeRateDogeUsed.value - expected) > 1e-8) {
        document.profitCalculator.exchangeRateDogeUsed.value = round(expected, 2);
      }
    }
    function handleExchangeRateDogeUsedDiffChange(value) {
      if (value == null || value == '') return;
      if (!dogeUsdRateOrig || dogeUsdRateOrig === 0) return;
      const pct = (value / dogeUsdRateOrig - 1) * 100;
      const clamped = clamp(pct, exchangeRateDogeUsedDiffMin, exchangeRateDogeUsedDiffMax);
      if (Math.abs((document.profitCalculator.exchangeRateDogeUsedDiffSlider.value ?? 0) - clamped) > 1e-8) {
        sliderExchangeRateDogeUsedDiff.update({ from: Math.round(clamped) });
      }
    }
    function resetExchangeRateDogeUsed() {
      if (dogeUsdRateOrig) {
        document.profitCalculator.exchangeRateDogeUsed.value = dogeUsdRateOrig;
        handleExchangeRateDogeUsedDiffChange(dogeUsdRateOrig);
      }
    }
    resetExchangeRateDogeUsed();
  }

  // exchangeRateUsdtUsed
  if (document.querySelector('.profit-calculator__field--exchange-rate-usdt-used')) {
    const exchangeRateUsdtUsedDiffMin = -50;
    const exchangeRateUsdtUsedDiffMax = 200;
    let sliderExchangeRateUsdtUsedDiff;
    document.profitCalculator.exchangeRateUsdtUsed.value = usdtRubRateOrig;
    sliderExchangeRateUsdtUsedDiff = ionRangeSlider(document.profitCalculator.exchangeRateUsdtUsedDiffSlider, {
      grid: false,
      from: 0,
      min: exchangeRateUsdtUsedDiffMin,
      max: exchangeRateUsdtUsedDiffMax,
      step: 1,
      hide_min_max: false,
      hide_from_to: false,
      onChange: function (res) {
        handleExchangeRateUsdtUsedChange(res.from);
        calculate();
      },
    });
    document.profitCalculator.exchangeRateUsdtUsed.addEventListener('input', function () {
      const value = this.value.replace(/[^0-9,.]/gim, '').replace(/,/g, '.');
      this.value = value;
      handleExchangeRateUsdtUsedDiffChange(value);
      calculate();
    });
    document.querySelector('.profit-calculator-exchange-rate-usdt-used-reset').addEventListener('click', function () {
      resetExchangeRateUsdtUsed();
      calculate();
    });
    document.querySelector('.profit-calculator__field-slider-wrapper--exchange-rate-usdt-used').style.visibility = 'visible';
    function handleExchangeRateUsdtUsedChange(pct) {
      if (pct == null) return;
      const base = usdtRubRateOrig;
      if (!base) return;
      const expected = base * (1 + pct / 100);
      if (document.profitCalculator.exchangeRateUsdtUsed.value == null || document.profitCalculator.exchangeRateUsdtUsed.value == '' || Math.abs(document.profitCalculator.exchangeRateUsdtUsed.value - expected) > 1e-8) {
        document.profitCalculator.exchangeRateUsdtUsed.value = round(expected, 2);
      }
    }
    function handleExchangeRateUsdtUsedDiffChange(value) {
      if (value == null || value == '') return;
      if (!usdtRubRateOrig || usdtRubRateOrig === 0) return;
      const pct = (value / usdtRubRateOrig - 1) * 100;
      const clamped = clamp(pct, exchangeRateUsdtUsedDiffMin, exchangeRateUsdtUsedDiffMax);
      if (Math.abs((document.profitCalculator.exchangeRateUsdtUsedDiffSlider.value ?? 0) - clamped) > 1e-8) {
        sliderExchangeRateUsdtUsedDiff.update({ from: Math.round(clamped) });
      }
    }
    function resetExchangeRateUsdtUsed() {
      if (usdtRubRateOrig) {
        document.profitCalculator.exchangeRateUsdtUsed.value = usdtRubRateOrig;
        handleExchangeRateUsdtUsedDiffChange(usdtRubRateOrig);
      }
    }
    resetExchangeRateUsdtUsed();
  }

  // submit
  // document.querySelector('.profit-calculator__form').addEventListener('submit', function(e) {
  //   e.preventDefault();
  //   calculate();
    // ... место для цели метрики ...
  //   return false;
  // });

  calculate();

  function declination(n, forms) {
    const absN = Math.abs(Math.trunc(Number(n) || 0));
    const n100 = absN % 100;
    const n1 = n100 % 10;
    if (n100 > 10 && n100 < 20) return forms[2];
    if (n1 > 1 && n1 < 5) return forms[1];
    if (n1 === 1) return forms[0];
    return forms[2];
  }

  function round(value, decimals) {
    const factor = Math.pow(10, decimals);
    return Math.round(value * factor) / factor;
  }

  function clamp(v, min, max) {
    return Math.min(max, Math.max(min, v));
  }

  function calculate() {
    if (device.coin == 'BTC') {
      if (!device.hashrateTh) {
        return;
      }
      calculateBTC();
    } else if (device.coin == 'LTC+DOGE') {
      if (!device.hashrateMh) {
        return;
      }
      calculateLTCDOGE();
    }
  }

  function calculateBTC() {
    let log = '';

    let totalPower = 0;
    let totalHashrateTh = 0;
    let ffps = 0;
    const customPowerPrice = parseFloat(document.getElementById('pc-tariff').value || DEFAULT_TARIF);

    const r = {
      consumptionMonth: 0,
      profitPerDay: 0,
      profitPerMonth: 0,
      profitPerYear: 0,
      paybackPeriodPerMonth: 0,
      paybackPeriodPerYear: 0,
      equipmentPrice: device.price,
      powerPerMonth: 0,
      powerPricePerMonth: 0,
      incomePerMonth: 0,
      incomePerMonthPercent: 0,
    };

    const btcUsdRate = document.profitCalculator.exchangeRateBtcUsed.value ?? btcUsdRateOrig;
    const usdtRubRate = document.profitCalculator.exchangeRateUsdtUsed.value ?? usdtRubRateOrig;

    // Количество
    let quantity = document.getElementById('pc-quantity').value || 1;
    log += `quantity: ${quantity}\n`;

    // Стоимость 1 ед.
    const price = parseFloat(device.price) || 0;
    log += `price: ${price}\n`;

    // Мощность Асика, TH
    const hashrateTh = device.hashrateTh;
    log += `hashrateTh: ${hashrateTh}\n`;

    // Мощность Асика, kWh
    const power = device.power / 1000;
    log += `power: ${power}\n`;

    if (!quantity || !hashrateTh || !power) {
      document.getElementById('pc-net-month').textContent = 0; // Чистая прибыль в месяц, руб
      document.getElementById('pc-income-month').textContent = 0; // Доход в месяц, руб
      document.getElementById('pc-consumption-month').textContent = 0; // Расход в месяц, руб
      document.getElementById('pc-profitability').textContent = 0; // Доходность, %/мес
      // document.getElementById('pc-payback').textContent = 0 + ' лет'; // Окупаемость, лет

      document.querySelector('.profit-calculator__main-profit').style.visibility = 'hidden';
      document.querySelector('.profit-calculator__metrics').style.display = 'none';
    } else {
      // Суммарная мощность Асика, TH
      totalHashrateTh = hashrateTh * quantity;
      log += `totalHashrateTh: ${totalHashrateTh}\n`;

      // Суммарная мощность Асика, kWh
      totalPower = power * quantity;
      log += `totalPower: ${totalPower}\n`;

      // Суммарная стоимость оборудования
      const totalCostEquipment = price ? price * quantity : 0;
      log += `totalCostEquipment: ${totalCostEquipment}\n`;

      // FPPS (Доходность с 1TH в сутки)
      ffps = coins.BTC.ffps;
      log += `ffps: ${ffps}\n`;

      // Курс покупки BTC/USDT
      const btcUsdtBuy = btcUsdRate;
      log += `btcUsdtBuy: ${btcUsdtBuy}\n`;

      // Курс покупки USDT/RUB
      const usdtRubBuy = usdtRubRate;
      log += `usdtRubBuy: ${usdtRubBuy}\n`;

      // BTC в месяц
      const btcPerMonth = hashrateTh * quantity * ffps * DAYS_PER_MONTH;
      log += `btcPerMonth: ${btcPerMonth}\n`;

      // Рублей в месяц
      const rubPerMonth = btcPerMonth * btcUsdtBuy * usdtRubBuy;
      log += `rubPerMonth: ${rubPerMonth}\n`;

      // Месяцы
      const numberOfMonths = 36;
      log += `numberOfMonths: ${numberOfMonths}\n`;

      // Амортизационное отчисление в месяц
      const depreciationChargePerMonth = totalCostEquipment ? totalCostEquipment / numberOfMonths : 0;
      log += `depreciationChargePerMonth: ${depreciationChargePerMonth}\n`;

      // Электроэнергия в месяц, кВт
      r.powerPerMonth = power * quantity * HOURS_PER_DAY * DAYS_PER_MONTH;
      log += `r.powerPerMonth: ${r.powerPerMonth}\n`;

      // Тариф
      const tarif = customPowerPrice;
      log += `tarif: ${tarif}\n`;

      // Всего электроэнергии в месяц
      r.powerPricePerMonth = tarif * DAYS_PER_MONTH * HOURS_PER_DAY * power / 0.993 * quantity;
      log += `r.powerPricePerMonth: ${r.powerPricePerMonth}\n`;

      // Доходы
      r.incomePerMonth = rubPerMonth;
      log += `r.incomePerMonth: ${r.incomePerMonth}\n`;

      // Расходы
      const expenses = r.powerPricePerMonth;
      log += `expenses: ${expenses}\n`;

      // Прибыль до вычета налогов
      const profitBeforeTax = r.incomePerMonth - expenses;
      log += `profitBeforeTax: ${profitBeforeTax}\n`;

      // Налогооблагаемая база
      const taxableBase = r.incomePerMonth - expenses - depreciationChargePerMonth;
      log += `taxableBase: ${taxableBase}\n`;

      // Курс продажи BTC
      const btcUsdtSell = btcUsdtBuy;
      log += `btcUsdtSell: ${btcUsdtSell}\n`;

      // Курс продажи USDT/RUB
      const usdtRubSell = usdtRubBuy;
      log += `usdtRubSell: ${usdtRubSell}\n`;

      // Доход от курсовой разницы
      const incomeFromExchangeRateDifference = (btcUsdtSell - btcUsdtBuy) * btcPerMonth * usdtRubSell;
      log += `incomeFromExchangeRateDifference: ${incomeFromExchangeRateDifference}\n`;

      // Чистая прибыль в мес
      r.profitPerMonth = profitBeforeTax + incomeFromExchangeRateDifference;
      log += `r.profitPerMonth: ${r.profitPerMonth}\n`;

      // Доходность, %
      r.incomePerYearPercent = totalCostEquipment ? r.profitPerMonth * MONTHS_PER_YEAR / totalCostEquipment * 100 : 0;
      r.incomePerMonthPercent = totalCostEquipment ? r.incomePerYearPercent / MONTHS_PER_YEAR : 0;

      // Расход в месяц
      r.consumptionMonth = r.incomePerMonth - r.profitPerMonth;
      log += `r.consumptionMonth: ${r.consumptionMonth}\n`;

      // Окупаемость, мес
      r.paybackPeriodPerMonth = totalCostEquipment ? totalCostEquipment / r.profitPerMonth : 0;
      log += `r.paybackPeriodPerMonth: ${r.paybackPeriodPerMonth}\n`;

      // Окупаемость, год
      r.paybackPeriodPerYear = r.paybackPeriodPerMonth / MONTHS_PER_YEAR;
      log += `r.paybackPeriodPerYear: ${r.paybackPeriodPerMonth}\n`;

      r.profitPerDay = r.profitPerMonth / DAYS_PER_MONTH;
      r.profitPerYear = r.profitPerMonth * MONTHS_PER_YEAR;

      document.getElementById('pc-net-month').textContent = Intl.NumberFormat().format(round(r.profitPerMonth, 0)); // Чистая прибыль в месяц, руб
      document.getElementById('pc-income-month').textContent = Intl.NumberFormat().format(round(r.incomePerMonth, 0)); // Доход в месяц, руб
      document.getElementById('pc-consumption-month').textContent = Intl.NumberFormat().format(round(r.consumptionMonth, 0)); // Расход в месяц, руб
      document.getElementById('pc-profitability').textContent = round(r.incomePerYearPercent, 2); // Доходность, %/год
      // document.getElementById('pc-payback').textContent = round(r.paybackPeriodPerMonth, 1) + ' ' + declination(r.paybackPeriodPerMonth, ['месяц', 'месяца', 'месяцев']); // Окупаемость, мес

      document.getElementById('pc-date').textContent = new Date().toLocaleDateString('ru-RU');

      document.querySelector('.profit-calculator__main-profit').style.display = '';
      if (document.querySelector('.profit-calculator__main-profit').style.visibility === 'hidden') {
        setTimeout(() => {
          document.querySelector('.profit-calculator__main-profit').style.opacity = 1;
          document.querySelector('.profit-calculator__main-profit').style.height = 'auto';
          document.querySelector('.profit-calculator__main-profit').style.visibility = '';
        }, 100);
      }
      if (!isNaN(price) && price > 0) document.querySelector('.profit-calculator__metrics').style.display = '';
    }

    productElement.classList.add('loaded');

    if (isDebug) {
      console.log(`\n${log}\n`);
      console.log(r);
    }
  }

  function calculateLTCDOGE() {
    let log = 'calculateLTCDOGE()\n';

    let totalPower = 0;
    let usdFfps = 0;
    let ltcFfps = 0;
    let dpgeFfps = 0;
    const customPowerPrice = parseFloat(document.getElementById('pc-tariff').value || DEFAULT_TARIF);

    const r = {
      consumptionMonth: 0,
      profitPerDay: 0,
      profitPerMonth: 0,
      profitPerYear: 0,
      paybackPeriodPerMonth: 0,
      paybackPeriodPerYear: 0,
      equipmentPrice: device.price,
      powerPerMonth: 0,
      powerPricePerDay: 0,
      powerPricePerMonth: 0,
      powerPricePerYear: 0,
      incomePerDay: 0,
      incomePerMonth: 0,
      incomePerYear: 0,
      incomePerYearPercent: 0,
    };

    const ltcUsdRate = document.profitCalculator.exchangeRateLtcUsed.value ?? ltcUsdRateOrig;
    const dogeUsdRate = document.profitCalculator.exchangeRateDogeUsed.value ?? dogeUsdRateOrig;
    const usdtRubRate = document.profitCalculator.exchangeRateUsdtUsed.value ?? usdtRubRateOrig;

    // Количество
    let quantity = document.getElementById('pc-quantity').value || 1;
    log += `quantity: ${quantity}\n`;

    // Хешрейт одного устройства, GH/s
    const hashrate = device.hashrateMh / 1000;
    log += `hashrate: ${hashrate}\n`;

    // Стоимость 1 ед.
    const price = device.price;
    log += `price: ${price}\n`;

    // Потребление одного устройства, кВт
    const power = device.power / 1000;
    log += `power: ${power}\n`;

    if (!quantity || !price || !hashrate || !power) {
      document.getElementById('pc-net-month').textContent = 0; // Чистая прибыль в месяц, руб
      document.getElementById('pc-income-month').textContent = 0; // Доход в месяц, руб
      document.getElementById('pc-consumption-month').textContent = 0; // Расход в месяц, руб
      document.getElementById('pc-profitability').textContent = 0; // Доходность, %/мес
      // document.getElementById('pc-payback').textContent = 0 + ' лет'; // Окупаемость, лет

      document.querySelector('.profit-calculator__main-profit').style.visibility = 'hidden';
      document.querySelector('.profit-calculator__metrics').style.display = 'none';
    } else {
      // Суммарный хешрейт, GH/s
      totalHashrate = hashrate * quantity;
      log += `totalHashrate: ${totalHashrate}\n`;

      // Суммарное потребление, кВт
      totalPower = power * quantity;
      log += `totalPower: ${totalPower}\n`;

      // Доходность 1 GH/s в сутки, USD (ViaBTC)
      usdFfps = 0.7276;
      log += `usdFfps: ${usdFfps}\n`;

      // Доход 1 GH/s/сутки, LTC (ViaBTC)
      ltcFfps = coins.LTC.ffps;
      log += `ltcFfps: ${ltcFfps}\n`;

      // Доход 1 GH/s/сутки, DOGE (ViaBTC)
      dpgeFfps = coins.DOGE.ffps;
      log += `dpgeFfps: ${dpgeFfps}\n`;

      // Курс покупки USD/RUB
      const usdtRubBuy = usdtRubRate;
      log += `usdtRubBuy: ${usdtRubBuy}\n`;

      // Курс покупки LTC/USDT
      const ltcUsdtBuy = ltcUsdRate;
      log += `ltcUsdtBuy: ${ltcUsdtBuy}\n`;

      // Курс покупки DOGE/USDT
      const dogeUsdtBuy = dogeUsdRate;
      log += `dogeUsdtBuy: ${dogeUsdtBuy}\n`;

      // Тариф на ЭЭ, руб/квтч
      const tarif = customPowerPrice;
      log += `tarif: ${tarif}\n`;

      // Суммарная стоимость оборудования
      const totalCostEquipment = price * quantity;
      log += `totalCostEquipment: ${totalCostEquipment}\n`;

      // Потребление, кВт·ч/мес
      r.powerPerMonth = totalPower * HOURS_PER_DAY * DAYS_PER_MONTH;
      log += `r.powerPerMonth: ${r.powerPerMonth}\n`;

      // Затраты на электроэнергию, руб/мес
      r.powerPricePerMonth = r.powerPerMonth * tarif;
      log += `r.powerPricePerMonth: ${r.powerPricePerMonth}\n`;

      // Затраты на электроэнергию, руб/день
      r.powerPricePerDay = r.powerPricePerMonth / DAYS_PER_MONTH;
      log += `r.powerPricePerDay: ${r.powerPricePerDay}\n`;

      // Затраты на электроэнергию, руб/год
      r.powerPricePerYear = r.powerPricePerMonth * MONTHS_PER_YEAR;
      log += `r.powerPricePerYear: ${r.powerPricePerYear}\n`;

      // LTC в сутки
      const ltcPerDay = ltcFfps * totalHashrate;
      log += `ltcPerDay: ${ltcPerDay}\n`;

      // DOGE в сутки
      const dogePerDay = dpgeFfps * totalHashrate;
      log += `dogePerDay: ${dogePerDay}\n`;

      // ltc/usd в сутки
      const ltcUsdPerDay = ltcUsdtBuy * ltcPerDay;
      log += `ltcUsdPerDay: ${ltcUsdPerDay}\n`;

      // doge/usd в сутки
      const dogeUsdPerDay = dogeUsdtBuy * dogePerDay;
      log += `dogeUsdPerDay: ${dogeUsdPerDay}\n`;

      // Валовой доход, USD/сутки
      const grossIncomePerDayUsd = ltcUsdPerDay + dogeUsdPerDay;
      log += `grossIncomePerDayUsd: ${grossIncomePerDayUsd}\n`;

      // Валовой доход, руб/сутки
      r.incomePerDay = grossIncomePerDayUsd * usdtRubBuy;
      log += `r.incomePerDay: ${r.incomePerDay}\n`;

      // Валовой доход, руб/месяц
      r.incomePerMonth = r.incomePerDay * DAYS_PER_MONTH;
      log += `r.incomePerMonth: ${r.incomePerMonth}\n`;

      // Валовой доход, руб/год
      r.incomePerYear = r.incomePerMonth * MONTHS_PER_YEAR;
      log += `r.incomePerYear: ${r.incomePerYear}\n`;

      // Потребление в сутки:
      const powerPerDay = totalPower * HOURS_PER_DAY;
      log += `powerPerDay: ${powerPerDay}\n`;

      // Затраты на ЭЭ/сутки
      const powerPricePerDay = powerPerDay * tarif;
      log += `powerPricePerDay: ${powerPricePerDay}\n`;

      // Прибыль в руб/сутки
      r.profitPerDay = r.incomePerDay - powerPricePerDay;
      log += `r.profitPerDay: ${r.profitPerDay}\n`;

      // Прибыль usd/сутки
      const usdPerDay = grossIncomePerDayUsd - (powerPricePerDay / usdtRubBuy);
      log += `usdPerDay: ${usdPerDay}\n`;

      // Прибыль в руб/мес
      r.profitPerMonth = r.profitPerDay * DAYS_PER_MONTH;
      log += `r.profitPerMonth: ${r.profitPerMonth}\n`;

      // Прибыль usd/мес
      const usdPerMonth = usdPerDay * DAYS_PER_MONTH;
      log += `usdPerMonth: ${usdPerMonth}\n`;

      // Окупаемость дней
      const paybackPeriodPerDays = totalCostEquipment / r.profitPerDay;
      log += `paybackPeriodPerDays: ${paybackPeriodPerDays}\n`;

      // Окупаемость месяцев
      r.paybackPeriodPerMonth = totalCostEquipment / r.profitPerMonth;
      log += `r.paybackPeriodPerMonth: ${r.paybackPeriodPerMonth}\n`;

      // Годовая прибыль
      r.profitPerYear = r.profitPerDay * DAYS_PER_YEAR;
      log += `r.profitPerYear: ${r.profitPerYear}\n`;

      // Доход годовых, %
      r.incomePerYearPercent = r.profitPerYear / totalCostEquipment * 100;
      log += `r.incomePerYearPercent: ${r.incomePerYearPercent}\n`;

      // Расход в месяц
      r.consumptionMonth = r.incomePerMonth - r.profitPerMonth;
      log += `r.consumptionMonth: ${r.consumptionMonth}\n`;

      document.getElementById('pc-net-month').textContent = Intl.NumberFormat().format(round(r.profitPerMonth, 0)); // Чистая прибыль в месяц, руб
      document.getElementById('pc-income-month').textContent = Intl.NumberFormat().format(round(r.incomePerMonth, 0)); // Доход в месяц, руб
      document.getElementById('pc-consumption-month').textContent = Intl.NumberFormat().format(round(r.consumptionMonth, 0)); // Расход в месяц, руб
      document.getElementById('pc-profitability').textContent = round(r.incomePerYearPercent, 2); // Доходность, %/год
      // document.getElementById('pc-payback').textContent = round(r.paybackPeriodPerMonth, 1) + ' ' + declination(r.paybackPeriodPerMonth, ['месяц', 'месяца', 'месяцев']); // Окупаемость, мес

      document.getElementById('pc-date').textContent = new Date().toLocaleDateString('ru-RU');

      document.querySelector('.profit-calculator__main-profit').style.display = '';
      if (document.querySelector('.profit-calculator__main-profit').style.visibility === 'hidden') {
        setTimeout(() => {
          document.querySelector('.profit-calculator__main-profit').style.opacity = 1;
          document.querySelector('.profit-calculator__main-profit').style.height = 'auto';
          document.querySelector('.profit-calculator__main-profit').style.visibility = '';
        }, 100);
      }
      if (!isNaN(price) && price > 0) document.querySelector('.profit-calculator__metrics').style.display = '';
    }

    productElement.classList.add('loaded');

    if (isDebug) {
      console.log(`\n${log}\n`);
      console.log(r);
    }
  };
});
