const API_BASE = "https://gis-mining.ru/local/api/mining";
const DAYS_IN_MONTH = 30.5;
const DAYS_IN_YEAR = 365;

// ---------- DOM ----------

// ASIC селектор
const asicSelected    = document.getElementById("asicSelected");
const asicDropdown    = document.getElementById("asicDropdown");
const asicSearch      = document.getElementById("asicSearch");
const asicList        = document.getElementById("asicList");

// Краткая инфа по асикам
const briefBlock        = document.getElementById("asicBrief");
const briefManufacturer = document.getElementById("briefManufacturer");
const briefHashrate     = document.getElementById("briefHashrate");
const briefPower        = document.getElementById("briefPower");
const briefPrice        = document.getElementById("briefPrice");

// Параметры
const countInput   = document.getElementById("countInput");
const tariffInput  = document.getElementById("tariffInput");
const btnCountMinus = document.getElementById("btnCountMinus");
const btnCountPlus  = document.getElementById("btnCountPlus");

// Результаты
const btcIncomeEl   = document.getElementById("btcIncome");    // BTC-доход
const ltcIncomeEl   = document.getElementById("ltcIncome");    // Scrypt: LTC
const dogeIncomeEl  = document.getElementById("dogeIncome");   // Scrypt: DOGE
const mainIncomeEl  = document.getElementById("mainIncome");
const powerCostEl   = document.getElementById("powerCost");
const netProfitEl   = document.getElementById("netProfit");
const powerKwhEl    = document.getElementById("powerKwh");
const paybackEl     = document.getElementById("payback");
const roiYearEl     = document.getElementById("roiYear");
const periodLabelText = document.getElementById("periodLabelText");

// Ряды для BTC / Scrypt
const btcOnlyRows    = document.querySelectorAll(".btc-only");
const scryptOnlyRows = document.querySelectorAll(".scrypt-only");

// Пользовательские курсы
const customRateCheckbox = document.getElementById("customRateCheckbox");
const customRateBlock    = document.getElementById("customRateBlock");

// BTC-блок курсов
const rateBlockBtc     = document.getElementById("rateBlockBtc");
const inputBtcUsdt     = document.getElementById("inputBtcUsdt");
const inputUsdtRub     = document.getElementById("inputUsdtRub");
const sliderBtcUsdt    = document.getElementById("sliderBtcUsdt");
const sliderUsdtRub    = document.getElementById("sliderUsdtRub");
const btcUsdtPercent   = document.getElementById("btcUsdtPercent");
const usdtRubPercent   = document.getElementById("usdtRubPercent");
const resetBtcUsdt     = document.getElementById("resetBtcUsdt");
const resetUsdtRub     = document.getElementById("resetUsdtRub");

// Scrypt-блок курсов
const rateBlockScrypt   = document.getElementById("rateBlockScrypt");
const inputLtcUsdt      = document.getElementById("inputLtcUsdt");
const inputDogeUsdt     = document.getElementById("inputDogeUsdt");
const inputUsdtRub2     = document.getElementById("inputUsdtRub2");
const sliderLtcUsdt     = document.getElementById("sliderLtcUsdt");
const sliderDogeUsdt    = document.getElementById("sliderDogeUsdt");
const sliderUsdtRub2    = document.getElementById("sliderUsdtRub2");
const ltcUsdtPercent    = document.getElementById("ltcUsdtPercent");
const dogeUsdtPercent   = document.getElementById("dogeUsdtPercent");
const usdtRubPercent2   = document.getElementById("usdtRubPercent2");
const resetLtcUsdt      = document.getElementById("resetLtcUsdt");
const resetDogeUsdt     = document.getElementById("resetDogeUsdt");
const resetUsdtRub2     = document.getElementById("resetUsdtRub2");

// Период
const btnDay   = document.getElementById("btnPeriodDay");
const btnMonth = document.getElementById("btnPeriodMonth");
const btnYear  = document.getElementById("btnPeriodYear");

// Валюта
const btnCurRub  = document.getElementById("btnCurRub");
const btnCurUsd  = document.getElementById("btnCurUsd");
const btnCurBtc  = document.getElementById("btnCurBtc");
const btnCurLtc  = document.getElementById("btnCurLtc");
const btnCurDoge = document.getElementById("btnCurDoge");

// Фильтр крипты
const cryptoButtons = document.querySelectorAll(".crypto-btn");

// ---------- STATE ----------

let ASICS = [];
let MARKET = null;

let selectedAsicId = null;
let period   = "month";    // day | month | year
let currency = "rub";      // rub | usd | btc | ltc | doge

let cryptoFilter = "all";  // all | BTC | LTC+DOGE

// Кастомные курсы
let customRates   = false;
let userBtcUsdt   = null;
let userUsdtRub   = null;

let userLtcUsdt   = null;
let userDogeUsdt  = null;
let userUsdtRub2  = null;

let calcTimeout   = null;

// ---------- Telegram ----------

if (window.Telegram && window.Telegram.WebApp) {
  window.Telegram.WebApp.expand();
}

// ---------- HELPERS ----------

function formatNum(value, decimals = 0) {
  const n = Number(value) || 0;
  return n.toLocaleString("ru-RU", {
    maximumFractionDigits: decimals,
    minimumFractionDigits: decimals,
  });
}

function getPeriodMultiplier() {
  if (period === "day") return 1;
  if (period === "month") return DAYS_IN_MONTH;
  if (period === "year") return DAYS_IN_YEAR;
  return 1;
}

function getPeriodLabel() {
  if (period === "day") return "за день";
  if (period === "month") return "за месяц";
  if (period === "year") return "за год";
  return "";
}

function isScryptAsic(asic) {
  if (!asic) return false;
  if (asic.crypto === "LTC+DOGE") return true;
  if (asic.algorithm && typeof asic.algorithm === "string") {
    return asic.algorithm.toLowerCase().includes("scrypt");
  }
  return false;
}

// Скелетон
function showSkeleton() {
  document.querySelectorAll(".result-row strong").forEach((el) => {
    el.classList.add("loading-value");

    if (!el.nextElementSibling || !el.nextElementSibling.classList.contains("skeleton")) {
      const sk = document.createElement("div");
      sk.className = "skeleton";
      el.after(sk);
    }
  });
}

function hideSkeleton() {
  document.querySelectorAll(".result-row strong").forEach((el) => {
    el.classList.remove("loading-value");
    const next = el.nextElementSibling;
    if (next && next.classList.contains("skeleton")) next.remove();
  });
}

// Обновление активных кнопок валют
function updateCurrencyButtons() {
  btnCurRub.classList.toggle("segment-btn--active", currency === "rub");
  btnCurUsd.classList.toggle("segment-btn--active", currency === "usd");
  btnCurBtc.classList.toggle("segment-btn--active", currency === "btc");
  btnCurLtc.classList.toggle("segment-btn--active", currency === "ltc");
  btnCurDoge.classList.toggle("segment-btn--active", currency === "doge");
}

// ---------- LOAD DATA ----------

async function loadAsics() {
  const res = await fetch(`${API_BASE}/get_asics.php`);
  const data = await res.json();
  ASICS = data.filter((a) => a.IN_CALCULATOR_TG === "Да");
}

async function loadMarket() {
  const res = await fetch(`${API_BASE}/get_market_data.php`);
  MARKET = await res.json() || {};

  // Сохраняем базовые значения
  MARKET._btc_usd_base          = MARKET.btc_usd  || 0;
  MARKET._usdt_rub_base         = MARKET.usdt_rub || 0;
  MARKET._ltc_usdt_base         = MARKET.ltc_usd  || 0;
  MARKET._doge_usdt_base        = MARKET.doge_usd || 0;

  MARKET._fpps_btc_per_th_day_base   = MARKET.fpps_btc_per_th_day   || 0;
  MARKET._fpps_ltc_per_mh_day_base   = MARKET.fpps_ltc_per_mh_day   || 0;
  MARKET._fpps_doge_per_mh_day_base  = MARKET.fpps_doge_per_mh_day  || 0;
}

// ---------- CRYPTO FILTER ----------

function applyCryptoFilter() {
  let filtered = ASICS;
  if (cryptoFilter === "BTC") {
    filtered = ASICS.filter((a) => a.crypto === "BTC");
  } else if (cryptoFilter === "LTC+DOGE") {
    filtered = ASICS.filter((a) => a.crypto === "LTC+DOGE");
  }
  return filtered;
}

function updateCryptoButtons() {
  cryptoButtons.forEach((btn) => {
    btn.classList.toggle("active", btn.dataset.crypto === cryptoFilter);
  });
}

// ---------- ASIC LIST RENDER ----------

function renderAsicList(filterText = "") {
  asicList.innerHTML = "";
  const lower = filterText.toLowerCase();
  const list = applyCryptoFilter();

  list
    .filter((a) => a.name.toLowerCase().includes(lower))
    .forEach((a) => {
      const item = document.createElement("div");
      item.className = "asic-item";

      item.innerHTML = `
        <img src="${a.image || ""}" alt="">
        <div>
            <div class="asic-info-title">${a.name}</div>
            <div class="asic-info-sub">Потребление: ${formatNum(a.power_kw || 0)} Вт</div>
        </div>
      `;

      item.onclick = () => {
        selectedAsicId = a.id;
        asicSelected.textContent = a.name;
        asicDropdown.classList.add("hidden");

        autoSetCryptoFilter(a.crypto);
        updateBrief();
        calculate();
      };

      asicList.appendChild(item);
    });
}

function autoSetCryptoFilter(crypto) {
  if (!crypto) return;
  if (crypto === "BTC") cryptoFilter = "BTC";
  else if (crypto === "LTC+DOGE") cryptoFilter = "LTC+DOGE";
  else cryptoFilter = "all";

  updateCryptoButtons();
  renderAsicList();
}

// ---------- BRIEF INFO ----------

function updateBrief() {
  const asic = ASICS.find((a) => a.id === selectedAsicId);
  if (!asic) {
    briefBlock.style.display = "none";
    return;
  }

  briefBlock.style.display = "flex";

  briefManufacturer.textContent = asic.manufacturer || "—";

  const scrypt = isScryptAsic(asic);
  const hashrateTh = asic.hashrate_th || 0;
  const hashrateMh = asic.hashrate_mh || 0;

  if (scrypt) {
    // MH/s → GH/s для отображения
    const gh = hashrateMh / 1000;
    briefHashrate.textContent = hashrateMh
      ? `${formatNum(gh, 2)} GH/s`
      : "—";
  } else {
    briefHashrate.textContent = hashrateTh
      ? `${formatNum(hashrateTh, 0)} TH/s`
      : "—";
  }

  briefPower.textContent = `${formatNum(asic.power_kw || 0)} Вт`;
  briefPrice.textContent = asic.price ? `${formatNum(asic.price)} ₽` : "—";
}

// ---------- ALGO-DEPENDENT UI ----------

function updateAlgoUI(asic) {
  const scrypt = isScryptAsic(asic);

  // BTC / Scrypt строки результатов
  btcOnlyRows.forEach((row) => row.classList.toggle("hidden", scrypt));
  scryptOnlyRows.forEach((row) => row.classList.toggle("hidden", !scrypt));

  if (scrypt) {
    // --- Scrypt режим ---
    // Разрешаем только RUB и USD
    btnCurRub.classList.remove("hidden");
    btnCurUsd.classList.remove("hidden");

    btnCurLtc.classList.add("hidden");
    btnCurDoge.classList.add("hidden");
    btnCurBtc.classList.add("hidden");

    // Если была выбрана LTC или DOGE — принудительно вернуть RUB
    if (currency === "ltc" || currency === "doge" || currency === "btc") {
      currency = "rub";
    }
  } else {
    // --- BTC ASIC ---
    btnCurRub.classList.remove("hidden");
    btnCurUsd.classList.remove("hidden");
    btnCurBtc.classList.remove("hidden");

    // LTC/DOGE для BTC ASIC скрыты
    btnCurLtc.classList.add("hidden");
    btnCurDoge.classList.add("hidden");

    if (currency === "ltc" || currency === "doge") {
      currency = "rub";
    }
  }

  updateCurrencyButtons();

  // Блоки курсов
  if (customRates) {
    customRateBlock.classList.remove("hidden");
    rateBlockBtc.classList.toggle("hidden", scrypt);
    rateBlockScrypt.classList.toggle("hidden", !scrypt);
  } else {
    customRateBlock.classList.add("hidden");
  }
}


// ---------- EFFECTIVE RATES ----------

function getEffectiveRates(isScrypt) {
  const rates = {
    btcUsd:  MARKET?._btc_usd_base  || 0,
    usdtRub: MARKET?._usdt_rub_base || 0,
    ltcUsdt: MARKET?._ltc_usdt_base || 0,
    dogeUsdt: MARKET?._doge_usdt_base || 0,
  };

  if (customRates) {
    if (isScrypt) {
      if (!isNaN(userLtcUsdt)   && userLtcUsdt   > 0) rates.ltcUsdt  = userLtcUsdt;
      if (!isNaN(userDogeUsdt)  && userDogeUsdt  > 0) rates.dogeUsdt = userDogeUsdt;
      if (!isNaN(userUsdtRub2)  && userUsdtRub2  > 0) rates.usdtRub  = userUsdtRub2;
    } else {
      if (!isNaN(userBtcUsdt)   && userBtcUsdt   > 0) rates.btcUsd   = userBtcUsdt;
      if (!isNaN(userUsdtRub)   && userUsdtRub   > 0) rates.usdtRub  = userUsdtRub;
    }
  }

  return rates;
}

// ---------- MAIN CALCULATION ----------

function calculate() {
  showSkeleton();

  clearTimeout(calcTimeout);
  calcTimeout = setTimeout(() => {
    if (!MARKET || !selectedAsicId) {
      setEmptyResult();
      hideSkeleton();
      return;
    }

    const asic = ASICS.find((a) => a.id === selectedAsicId);
    if (!asic) {
      setEmptyResult();
      hideSkeleton();
      return;
    }

    const scrypt = isScryptAsic(asic);
    updateAlgoUI(asic);

    // Проверка FPPS в зависимости от алгоритма
    if (scrypt) {
      if (!MARKET._fpps_ltc_per_mh_day_base || !MARKET._fpps_doge_per_mh_day_base) {
        setEmptyResult();
        hideSkeleton();
        return;
      }
    } else {
      if (!MARKET._fpps_btc_per_th_day_base) {
        setEmptyResult();
        hideSkeleton();
        return;
      }
    }

    // Кол-во и тариф
    let count = Math.max(1, parseInt(countInput.value, 10) || 1);
    countInput.value = count;

    let tariff = parseFloat(String(tariffInput.value).replace(",", ".")) || 5.3;
    if (tariff < 0) tariff = 0;
    tariffInput.value = tariff;

    const powerW  = asic.power_kw || 0;   // в БД Вт
    const powerKW = powerW / 1000;        // кВт
    const devicePriceRub = asic.price || 0;

    const multiplier   = getPeriodMultiplier();
    const periodLabel  = getPeriodLabel();
    periodLabelText.textContent = periodLabel;

    const rates = getEffectiveRates(scrypt);
    const usdtRub = rates.usdtRub || 0;

    // Общие значения расхода электроэнергии
    const powerKwhDay     = powerKW * count * 24;
    const powerKwhPeriod  = powerKwhDay * multiplier;
    const powerCostPeriod = powerKwhPeriod * tariff;

    let incomeRubPeriod = 0;
    let incomeUsdPeriod = 0;
    let netRubPeriod    = 0;

    // --- BTC (SHA-256) ---
    if (!scrypt) {
      const hashrateTH = asic.hashrate_th || 0;
      const fpps       = MARKET._fpps_btc_per_th_day_base;
      const btcUsd     = rates.btcUsd || 0;
      const btcRub     = btcUsd * usdtRub;

      const incomeBtcDay    = hashrateTH * count * fpps;
      const incomeBtcPeriod = incomeBtcDay * multiplier;

      incomeUsdPeriod = btcUsd * incomeBtcPeriod;
      incomeRubPeriod = incomeBtcPeriod * btcRub;

      netRubPeriod = incomeRubPeriod - powerCostPeriod;

      // Вывод BTC-дохода
      btcIncomeEl.textContent = `${formatNum(incomeBtcPeriod, 6)} BTC`;

      // Основной блок дохода / затрат / прибыли
      let incomeDisplay, netDisplay, powerCostDisplay;

      if (currency === "rub") {
        incomeDisplay     = `${formatNum(incomeRubPeriod)} ₽`;
        netDisplay        = `${formatNum(netRubPeriod)} ₽`;
        powerCostDisplay  = `${formatNum(powerCostPeriod)} ₽`;
      } else if (currency === "usd") {
        const incomeUsd   = usdtRub ? incomeRubPeriod / usdtRub : 0;
        const netUsd      = usdtRub ? netRubPeriod / usdtRub : 0;
        const powerUsd    = usdtRub ? powerCostPeriod / usdtRub : 0;

        incomeDisplay     = `${formatNum(incomeUsd)} $`;
        netDisplay        = `${formatNum(netUsd)} $`;
        powerCostDisplay  = `${formatNum(powerUsd)} $`;
      } else {
        // BTC
        incomeDisplay     = `${formatNum(incomeBtcPeriod, 6)} BTC`;
        const netBtc      = btcRub ? netRubPeriod / btcRub : 0;
        const powerBtc    = btcRub ? powerCostPeriod / btcRub : 0;

        netDisplay        = `${formatNum(netBtc, 6)} BTC`;
        powerCostDisplay  = `${formatNum(powerBtc, 6)} BTC`;
      }

      mainIncomeEl.textContent = incomeDisplay;
      powerCostEl.textContent  = `${powerCostDisplay} • ${formatNum(powerKwhPeriod)} кВт⋅ч`;
      netProfitEl.textContent  = netDisplay;
      powerKwhEl.textContent   = `${formatNum(powerKwhPeriod)} кВт⋅ч`;

    } else {
      // --- SCRYPT: LTC + DOGE ---
      const hashrateMh       = asic.hashrate_mh || 0;   // MH/s из БД
      const mhTotal          = hashrateMh * count;

      const fppsLtc          = MARKET._fpps_ltc_per_mh_day_base;   // LTC за 1 MH/s в сутки
      const fppsDoge         = MARKET._fpps_doge_per_mh_day_base;  // DOGE за 1 MH/s в сутки

      const ltcUsdt          = rates.ltcUsdt || 0;   // LTC/USDT (≈ LTC/USD)
      const dogeUsdt         = rates.dogeUsdt || 0;  // DOGE/USDT

      const incomeLtcDay     = mhTotal * fppsLtc;
      const incomeDogeDay    = mhTotal * fppsDoge;

      const incomeLtcPeriod  = incomeLtcDay * multiplier;
      const incomeDogePeriod = incomeDogeDay * multiplier;

      const incomeLtcUsd     = incomeLtcPeriod * ltcUsdt;
      const incomeDogeUsd    = incomeDogePeriod * dogeUsdt;

      incomeUsdPeriod        = incomeLtcUsd + incomeDogeUsd;
      incomeRubPeriod        = incomeUsdPeriod * usdtRub;

      netRubPeriod           = incomeRubPeriod - powerCostPeriod;

      // Вывод LTC / DOGE
      ltcIncomeEl.textContent  = `${formatNum(incomeLtcPeriod, 6)} LTC`;
      dogeIncomeEl.textContent = `${formatNum(incomeDogePeriod, 2)} DOGE`;

      // Основной доход
      let incomeDisplay, netDisplay, powerCostDisplay;

      if (currency === "rub") {
        incomeDisplay     = `${formatNum(incomeRubPeriod)} ₽`;
        netDisplay        = `${formatNum(netRubPeriod)} ₽`;
        powerCostDisplay  = `${formatNum(powerCostPeriod)} ₽`;
      } else if (currency === "usd") {
        const netUsd      = usdtRub ? netRubPeriod / usdtRub : 0;
        const powerUsd    = usdtRub ? powerCostPeriod / usdtRub : 0;

        incomeDisplay     = `${formatNum(incomeUsdPeriod)} $`;
        netDisplay        = `${formatNum(netUsd)} $`;
        powerCostDisplay  = `${formatNum(powerUsd)} $`;
      } else if (currency === "ltc") {
        const totalLtc    = ltcUsdt ? incomeUsdPeriod / ltcUsdt : 0;
        const netLtc      = (ltcUsdt && usdtRub) ? (netRubPeriod / usdtRub) / ltcUsdt : 0;
        const powerLtc    = (ltcUsdt && usdtRub) ? (powerCostPeriod / usdtRub) / ltcUsdt : 0;

        incomeDisplay     = `${formatNum(totalLtc, 6)} LTC`;
        netDisplay        = `${formatNum(netLtc, 6)} LTC`;
        powerCostDisplay  = `${formatNum(powerLtc, 6)} LTC`;
      } else {
        // DOGE
        const totalDoge   = dogeUsdt ? incomeUsdPeriod / dogeUsdt : 0;
        const netDoge     = (dogeUsdt && usdtRub) ? (netRubPeriod / usdtRub) / dogeUsdt : 0;
        const powerDoge   = (dogeUsdt && usdtRub) ? (powerCostPeriod / usdtRub) / dogeUsdt : 0;

        incomeDisplay     = `${formatNum(totalDoge, 2)} DOGE`;
        netDisplay        = `${formatNum(netDoge, 2)} DOGE`;
        powerCostDisplay  = `${formatNum(powerDoge, 2)} DOGE`;
      }

      mainIncomeEl.textContent = incomeDisplay;
      powerCostEl.textContent  = `${powerCostDisplay} • ${formatNum(powerKwhPeriod)} кВт⋅ч`;
      netProfitEl.textContent  = netDisplay;
      powerKwhEl.textContent   = `${formatNum(powerKwhPeriod)} кВт⋅ч`;
    }

    // Окупаемость и ROI
    let paybackMonthsText = "–";
    let roiYearText       = "–";

    if (devicePriceRub > 0 && netRubPeriod > 0) {
      const netRubPerMonth =
        period === "month"
          ? netRubPeriod
          : period === "day"
          ? netRubPeriod * DAYS_IN_MONTH
          : netRubPeriod / 12;

      const paybackMonths = (devicePriceRub * count) / netRubPerMonth;
      const paybackYears  = paybackMonths / 12;

      paybackMonthsText =
        `${paybackMonths.toFixed(1)} мес • ${paybackYears.toFixed(2)} лет`;

      const netRubYear = netRubPerMonth * 12;
      const roiYear    = (netRubYear / (devicePriceRub * count)) * 100;
      roiYearText      = `${roiYear.toFixed(1)} %`;
    }

    paybackEl.textContent = paybackMonthsText;
    roiYearEl.textContent = roiYearText;

    hideSkeleton();
  }, 200);
}

function setEmptyResult() {
  btcIncomeEl && (btcIncomeEl.textContent = "–");
  ltcIncomeEl && (ltcIncomeEl.textContent = "–");
  dogeIncomeEl && (dogeIncomeEl.textContent = "–");
  mainIncomeEl.textContent  = "–";
  powerCostEl.textContent   = "–";
  netProfitEl.textContent   = "–";
  powerKwhEl.textContent    = "–";
  paybackEl.textContent     = "–";
  roiYearEl.textContent     = "–";
}

// ---------- INIT ----------

async function init() {
  try {
    asicSelected.textContent = "Загрузка...";
    await Promise.all([loadAsics(), loadMarket()]);

    // Инициализация инпутов курсов
    if (MARKET) {
      if (inputBtcUsdt)  inputBtcUsdt.value  = MARKET._btc_usd_base;
      if (inputUsdtRub)  inputUsdtRub.value  = MARKET._usdt_rub_base;

      if (inputLtcUsdt)  inputLtcUsdt.value  = MARKET._ltc_usdt_base;
      if (inputDogeUsdt) inputDogeUsdt.value = MARKET._doge_usdt_base;
      if (inputUsdtRub2) inputUsdtRub2.value = MARKET._usdt_rub_base;
    }

    if (!ASICS.length) {
      asicSelected.textContent = "Модели не найдены";
      setEmptyResult();
      return;
    }

    selectedAsicId = ASICS[0].id;
    asicSelected.textContent = ASICS[0].name;

    renderAsicList();
    updateBrief();
    setPeriod("month");
    calculate();
  } catch (e) {
    console.error(e);
    asicSelected.textContent = "Ошибка загрузки данных";
    setEmptyResult();
  }
}

// ---------- EVENTS ----------

// Открытие / закрытие дропдауна
asicSelected.addEventListener("click", () => {
  asicDropdown.classList.toggle("hidden");
});

// Поиск
asicSearch.addEventListener("input", () => {
  renderAsicList(asicSearch.value);
});

// Закрытие по клику вне
document.addEventListener("click", (e) => {
  if (!asicDropdown.contains(e.target) && !asicSelected.contains(e.target)) {
    asicDropdown.classList.add("hidden");
  }
});

// Изменение количества и тарифа
countInput.addEventListener("input", calculate);
tariffInput.addEventListener("input", calculate);

btnCountMinus.addEventListener("click", () => {
  countInput.value = Math.max(1, (parseInt(countInput.value, 10) || 1) - 1);
  calculate();
});

btnCountPlus.addEventListener("click", () => {
  countInput.value = (parseInt(countInput.value, 10) || 1) + 1;
  calculate();
});

// Период
function setPeriod(p) {
  period = p;
  btnDay.classList.toggle("segment-btn--active", p === "day");
  btnMonth.classList.toggle("segment-btn--active", p === "month");
  btnYear.classList.toggle("segment-btn--active", p === "year");
  calculate();
}

btnDay.addEventListener("click", () => setPeriod("day"));
btnMonth.addEventListener("click", () => setPeriod("month"));
btnYear.addEventListener("click", () => setPeriod("year"));

// Валюта
function setCurrency(cur) {
  currency = cur;
  updateCurrencyButtons();
  calculate();
}

btnCurRub.addEventListener("click", () => setCurrency("rub"));
btnCurUsd.addEventListener("click", () => setCurrency("usd"));
btnCurBtc.addEventListener("click", () => setCurrency("btc"));
btnCurLtc.addEventListener("click", () => setCurrency("ltc"));
btnCurDoge.addEventListener("click", () => setCurrency("doge"));

// Фильтр криптовалют
cryptoButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    cryptoFilter = btn.dataset.crypto;
    updateCryptoButtons();
    renderAsicList();
  });
});

// Пользовательские курсы — включение / выключение
customRateCheckbox.addEventListener("change", () => {
  customRates = customRateCheckbox.checked;

  const asic = ASICS.find((a) => a.id === selectedAsicId);
  const scrypt = isScryptAsic(asic);

  if (customRates) {
    customRateBlock.classList.remove("hidden");
    rateBlockBtc.classList.toggle("hidden", scrypt);
    rateBlockScrypt.classList.toggle("hidden", !scrypt);

    userBtcUsdt  = parseFloat(inputBtcUsdt.value);
    userUsdtRub  = parseFloat(inputUsdtRub.value);

    userLtcUsdt  = inputLtcUsdt  ? parseFloat(inputLtcUsdt.value)  : null;
    userDogeUsdt = inputDogeUsdt ? parseFloat(inputDogeUsdt.value) : null;
    userUsdtRub2 = inputUsdtRub2 ? parseFloat(inputUsdtRub2.value) : null;
  } else {
    customRateBlock.classList.add("hidden");

    // Сброс слайдеров и процентов
    sliderBtcUsdt.value   = 0;
    sliderUsdtRub.value   = 0;
    btcUsdtPercent.textContent = "0%";
    usdtRubPercent.textContent = "0%";

    if (sliderLtcUsdt)  sliderLtcUsdt.value  = 0;
    if (sliderDogeUsdt) sliderDogeUsdt.value = 0;
    if (sliderUsdtRub2) sliderUsdtRub2.value = 0;
    if (ltcUsdtPercent)   ltcUsdtPercent.textContent   = "0%";
    if (dogeUsdtPercent)  dogeUsdtPercent.textContent  = "0%";
    if (usdtRubPercent2)  usdtRubPercent2.textContent  = "0%";

    // Сброс пользовательских значений к базовым
    if (MARKET) {
      inputBtcUsdt.value = MARKET._btc_usd_base;
      inputUsdtRub.value = MARKET._usdt_rub_base;

      if (inputLtcUsdt)  inputLtcUsdt.value  = MARKET._ltc_usdt_base;
      if (inputDogeUsdt) inputDogeUsdt.value = MARKET._doge_usdt_base;
      if (inputUsdtRub2) inputUsdtRub2.value = MARKET._usdt_rub_base;
    }

    userBtcUsdt   = null;
    userUsdtRub   = null;
    userLtcUsdt   = null;
    userDogeUsdt  = null;
    userUsdtRub2  = null;
  }

  calculate();
});

// универсальная логика пересчёта слайдера
function recalcSlider(slider, base, input, percentEl) {
  if (!MARKET) return;
  const mul = 1 + slider.value / 100;
  input.value = (base * mul).toFixed(4);
  percentEl.textContent = `${slider.value}%`;
}

// BTC/USDT
sliderBtcUsdt.addEventListener("input", () => {
  if (!MARKET) return;
  recalcSlider(sliderBtcUsdt, MARKET._btc_usd_base, inputBtcUsdt, btcUsdtPercent);
  userBtcUsdt = parseFloat(inputBtcUsdt.value);
  calculate();
});

// USDT/RUB (BTC)
sliderUsdtRub.addEventListener("input", () => {
  if (!MARKET) return;
  recalcSlider(sliderUsdtRub, MARKET._usdt_rub_base, inputUsdtRub, usdtRubPercent);
  userUsdtRub = parseFloat(inputUsdtRub.value);
  calculate();
});

// LTC/USDT
if (sliderLtcUsdt) {
  sliderLtcUsdt.addEventListener("input", () => {
    if (!MARKET) return;
    recalcSlider(sliderLtcUsdt, MARKET._ltc_usdt_base, inputLtcUsdt, ltcUsdtPercent);
    userLtcUsdt = parseFloat(inputLtcUsdt.value);
    calculate();
  });
}

// DOGE/USDT
if (sliderDogeUsdt) {
  sliderDogeUsdt.addEventListener("input", () => {
    if (!MARKET) return;
    recalcSlider(sliderDogeUsdt, MARKET._doge_usdt_base, inputDogeUsdt, dogeUsdtPercent);
    userDogeUsdt = parseFloat(inputDogeUsdt.value);
    calculate();
  });
}

// USDT/RUB (Scrypt)
if (sliderUsdtRub2) {
  sliderUsdtRub2.addEventListener("input", () => {
    if (!MARKET) return;
    recalcSlider(sliderUsdtRub2, MARKET._usdt_rub_base, inputUsdtRub2, usdtRubPercent2);
    userUsdtRub2 = parseFloat(inputUsdtRub2.value);
    calculate();
  });
}

// reset buttons BTC
resetBtcUsdt.addEventListener("click", () => {
  if (!MARKET) return;
  sliderBtcUsdt.value      = 0;
  btcUsdtPercent.textContent = "0%";
  inputBtcUsdt.value       = MARKET._btc_usd_base;
  userBtcUsdt              = MARKET._btc_usd_base;
  calculate();
});

resetUsdtRub.addEventListener("click", () => {
  if (!MARKET) return;
  sliderUsdtRub.value      = 0;
  usdtRubPercent.textContent = "0%";
  inputUsdtRub.value       = MARKET._usdt_rub_base;
  userUsdtRub              = MARKET._usdt_rub_base;
  calculate();
});

// reset buttons Scrypt
if (resetLtcUsdt) {
  resetLtcUsdt.addEventListener("click", () => {
    if (!MARKET) return;
    sliderLtcUsdt.value    = 0;
    ltcUsdtPercent.textContent = "0%";
    inputLtcUsdt.value     = MARKET._ltc_usdt_base;
    userLtcUsdt            = MARKET._ltc_usdt_base;
    calculate();
  });
}

if (resetDogeUsdt) {
  resetDogeUsdt.addEventListener("click", () => {
    if (!MARKET) return;
    sliderDogeUsdt.value   = 0;
    dogeUsdtPercent.textContent = "0%";
    inputDogeUsdt.value    = MARKET._doge_usdt_base;
    userDogeUsdt           = MARKET._doge_usdt_base;
    calculate();
  });
}

if (resetUsdtRub2) {
  resetUsdtRub2.addEventListener("click", () => {
    if (!MARKET) return;
    sliderUsdtRub2.value   = 0;
    usdtRubPercent2.textContent = "0%";
    inputUsdtRub2.value    = MARKET._usdt_rub_base;
    userUsdtRub2           = MARKET._usdt_rub_base;
    calculate();
  });
}

// manual input BTC
inputBtcUsdt.addEventListener("input", () => {
  userBtcUsdt = parseFloat(inputBtcUsdt.value);
  calculate();
});

inputUsdtRub.addEventListener("input", () => {
  userUsdtRub = parseFloat(inputUsdtRub.value);
  calculate();
});

// manual input Scrypt
if (inputLtcUsdt) {
  inputLtcUsdt.addEventListener("input", () => {
    userLtcUsdt = parseFloat(inputLtcUsdt.value);
    calculate();
  });
}

if (inputDogeUsdt) {
  inputDogeUsdt.addEventListener("input", () => {
    userDogeUsdt = parseFloat(inputDogeUsdt.value);
    calculate();
  });
}

if (inputUsdtRub2) {
  inputUsdtRub2.addEventListener("input", () => {
    userUsdtRub2 = parseFloat(inputUsdtRub2.value);
    calculate();
  });
}

// START
init();
