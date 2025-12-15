(function () {
  const API_BASE = "https://gis-mining.ru/local/api/mining";
  const DAYS_IN_YEAR = 365;

  const LEVEL_PRESETS = {
    novice: 5000000,
    advanced: 50000000,
    pro: 100000000,
  };

  const investorLevelGroup = document.getElementById("gic-investor-levels");
  const investInput = document.getElementById("gic-invest-amount");
  const tariffInput = document.getElementById("gic-tariff-input");
  const tariffWrapper = document.getElementById("gic-tariff-wrapper");
  const tariffNoteHosting = document.getElementById("gic-tariff-note-hosting");

  const calcBtn = document.getElementById("gic-calc-btn");
  const resetBtn = document.getElementById("gic-reset-btn");

  const resultSubtitle = document.getElementById("gic-result-subtitle");
  const marketMeta = document.getElementById("gic-market-meta");

  const mainPortfolioLabel = document.getElementById("gic-main-portfolio-label");
  const mainPortfolioTagline = document.getElementById("gic-main-portfolio-tagline");
  const mainPortfolioRoiBadge = document.getElementById("gic-main-portfolio-roi-badge");
  const mainPortfolioBody = document.getElementById("gic-main-portfolio-body");
  const altPortfoliosWrap = document.getElementById("gic-alt-portfolios");

  const portfolioTabs = document.querySelectorAll(".gic-tab-btn");

  let ASICS = [];
  let MARKET = null;
  let ASIC_METRICS = [];

  let activeLevel = "novice";
  let activeSegment = "sha";

  let portfoliosBySegment = {};

  function formatNum(value, decimals) {
    const n = Number(value) || 0;
    return n.toLocaleString("ru-RU", {
      maximumFractionDigits: decimals != null ? decimals : 0,
      minimumFractionDigits: decimals != null ? decimals : 0,
    });
  }

  function isScryptAsic(asic) {
    if (!asic) return false;
    if (asic.crypto === "LTC+DOGE") return true;
    if (asic.algorithm && typeof asic.algorithm === "string") {
      return asic.algorithm.toLowerCase().includes("scrypt");
    }
    return false;
  }

  function getTariff() {
    const mode =
      (document.querySelector('input[name="gic-tariff-mode"]:checked') || {})
        .value || "hosting";
    if (mode === "hosting") return 5.3;

    let t = parseFloat(String(tariffInput.value).replace(",", "."));
    if (isNaN(t) || t < 0) t = 5.3;
    return t;
  }

  function getPortfolioConfig(modelCount) {
    const cnt = modelCount || 0;
    if (cnt <= 1) return { maxShare: 1.0, minRoi: 0 };
    if (cnt === 2) return { maxShare: 0.9, minRoi: 0 };
    if (cnt === 3) return { maxShare: 0.8, minRoi: 0 };
    return { maxShare: 0.7, minRoi: 0 };
  }

  function parseBudget(str) {
    if (!str) return 0;
    const cleaned = str.replace(/\s+/g, "").replace(",", ".");
    return parseFloat(cleaned) || 0;
  }

  function formatBudgetInput(value) {
    const digits = value.replace(/[^\d]/g, "");
    if (!digits) return "";
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
  }

  // ---------- LEVELS ----------
  function setActiveLevel(level, options) {
    const updateInput = options?.updateInput !== false;

    if (!LEVEL_PRESETS[level]) return;

    activeLevel = level;

    investorLevelGroup.querySelectorAll(".gic-level-btn").forEach((btn) => {
      const isActive = btn.dataset.level === level;
      btn.classList.toggle("gic-level-btn--active", isActive);
    });

    if (updateInput && investInput) {
      investInput.value = formatBudgetInput(String(LEVEL_PRESETS[level]));
    }
  }

  function updateLevelByBudget(budget) {
    let newLevel = "novice";

    if (budget > LEVEL_PRESETS.advanced) newLevel = "pro";
    else if (budget > LEVEL_PRESETS.novice) newLevel = "advanced";

    if (newLevel !== activeLevel) {
      setActiveLevel(newLevel, { updateInput: false });
    }
  }

  if (investInput) {
    investInput.addEventListener("input", (e) => {
      const formatted = formatBudgetInput(e.target.value);
      e.target.value = formatted;

      e.target.setSelectionRange(formatted.length, formatted.length);

      const budget = parseBudget(formatted);
      updateLevelByBudget(budget);
    });
  }

  if (investorLevelGroup) {
    investorLevelGroup.querySelectorAll(".gic-level-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const level = btn.dataset.level;
        setActiveLevel(level, { updateInput: true });
      });
    });
  }

  // ---------- LOAD DATA ----------
  async function loadAsics() {
    const res = await fetch(API_BASE + "/get_asics.php");
    const data = await res.json();
    ASICS = (data || []).filter((a) => a.IN_INVESTOR_CALC === "Да");
  }

  async function loadMarket() {
    const res = await fetch(API_BASE + "/get_market_data.php");
    const data = await res.json();
    MARKET = data || {};
    MARKET._btc_usd_base = MARKET.btc_usd || 0;
    MARKET._usdt_rub_base = MARKET.usdt_rub || 0;
    MARKET._ltc_usdt_base = MARKET.ltc_usd || 0;
    MARKET._doge_usdt_base = MARKET.doge_usd || 0;
    MARKET._fpps_btc_per_th_day_base = MARKET.fpps_btc_per_th_day || 0;
    MARKET._fpps_ltc_per_mh_day_base = MARKET.fpps_ltc_per_mh_day || 0;
    MARKET._fpps_doge_per_mh_day_base = MARKET.fpps_doge_per_mh_day || 0;
  }

  function renderMarketMeta() {
    if (!MARKET || !marketMeta) return;
    const btc = MARKET._btc_usd_base || 0;
    const usdtRub = MARKET._usdt_rub_base || 0;

    marketMeta.innerHTML =
      `<div>BTC/USDT: <strong>${formatNum(btc, 0)}</strong></div>
       <div>USDT/RUB: <strong>${formatNum(usdtRub, 2)}</strong></div>`;
  }

  // ---------- TARIFF UI ----------
  function updateTariffModeUI() {
    const hostingSelected =
      document.querySelector('input[name="gic-tariff-mode"]:checked')?.value ===
      "hosting";

    if (hostingSelected) {
      tariffWrapper.style.opacity = "0.4";
      tariffWrapper.style.pointerEvents = "none";
      tariffNoteHosting.style.display = "block";
    } else {
      tariffWrapper.style.opacity = "1";
      tariffWrapper.style.pointerEvents = "auto";
      tariffNoteHosting.style.display = "none";
    }
  }

  // ---------- METRICS ----------
  function calcAsicAnnualMetrics(asic, tariffRub) {
    if (!MARKET) return null;

    const powerW = asic.power_kw || 0;
    const powerKW = powerW / 1000;
    const price = asic.price || 0;
    const usdtRub = MARKET._usdt_rub_base || 0;

    if (!price || !powerKW) return null;

    const powerKwhYear = powerKW * 24 * DAYS_IN_YEAR;
    const powerCostYear = powerKwhYear * tariffRub;

    let incomeRubYear = 0;
    let netRubYear = 0;

    if (!isScryptAsic(asic)) {
      const fpps = MARKET._fpps_btc_per_th_day_base;
      const hashrateTH = asic.hashrate_th || 0;
      const btcUsd = MARKET._btc_usd_base || 0;
      if (!fpps || !hashrateTH || !btcUsd || !usdtRub) return null;

      const btcRub = btcUsd * usdtRub;
      incomeRubYear = hashrateTH * fpps * DAYS_IN_YEAR * btcRub;
      netRubYear = incomeRubYear - powerCostYear;
    } else {
      const fppsLtc = MARKET._fpps_ltc_per_mh_day_base;
      const fppsDoge = MARKET._fpps_doge_per_mh_day_base;
      const hashrateMH = asic.hashrate_mh || 0;

      if (!fppsLtc || !fppsDoge || !hashrateMH) return null;

      const incomeLtcYear = hashrateMH * fppsLtc * DAYS_IN_YEAR;
      const incomeDogeYear = hashrateMH * fppsDoge * DAYS_IN_YEAR;

      const incomeUsdYear =
        incomeLtcYear * MARKET._ltc_usdt_base +
        incomeDogeYear * MARKET._doge_usdt_base;

      incomeRubYear = incomeUsdYear * usdtRub;
      netRubYear = incomeRubYear - powerCostYear;
    }

    const roiPercentYear = price ? (netRubYear / price) * 100 : 0;
    const yieldPerRub = price ? netRubYear / price : 0;
    const paybackYears =
      netRubYear > 0 ? price / netRubYear : null;

    return {
      algo: isScryptAsic(asic) ? "Scrypt" : "SHA-256",
      crypto: isScryptAsic(asic) ? "LTC+DOGE" : "BTC",
      priceRub: price,
      powerKwhYear,
      powerCostYear,
      incomeRubYear,
      netRubYear,
      roiPercentYear,
      yieldPerRub,
      paybackYears,
    };
  }

  function buildAsicMetrics(tariffRub) {
    ASIC_METRICS = ASICS.map((asic) => {
      const metrics = calcAsicAnnualMetrics(asic, tariffRub);
      return metrics ? { asic, metrics } : null;
    })
      .filter(Boolean)
      .filter((m) => m.metrics.netRubYear > 0);
  }

  // ---------- PORTFOLIO BUILD ----------
  function greedyBuild(list, budgetRub, maxShare) {
    let remaining = budgetRub;
    const items = [];

    const minPrice = Math.min(...list.map((x) => x.metrics.priceRub));

    for (const item of list) {
      const price = item.metrics.priceRub;
      if (remaining < price) continue;

      const maxBudgetForModel = budgetRub * maxShare;
      const maxByShare = Math.floor(maxBudgetForModel / price);
      const maxByRemaining = Math.floor(remaining / price);

      const count = Math.min(maxByShare, maxByRemaining);
      if (count <= 0) continue;

      items.push({
        asic: item.asic,
        metrics: item.metrics,
        count,
        spent: count * price,
      });

      remaining -= count * price;

      if (remaining < minPrice) break;
    }

    const totalSpent = items.reduce((s, i) => s + i.spent, 0);

    return { items, totalSpent, remaining };
  }

  function calcPortfolioSummary(items, totalSpent, initialBudget) {
    let income = 0;
    let power = 0;
    let net = 0;

    items.forEach((i) => {
      income += i.metrics.incomeRubYear * i.count;
      power += i.metrics.powerCostYear * i.count;
      net += i.metrics.netRubYear * i.count;
    });

    return {
      totalIncomeYear: income,
      totalPowerCostYear: power,
      totalNetYear: net,
      monthlyNet: net / 12,
      roiPercentPortfolio: initialBudget ? (net / initialBudget) * 100 : 0,
      paybackYears: net > 0 ? initialBudget / net : null,
      totalSpent,
    };
  }

  function buildPortfolioForSegment(segment, budgetRub) {
    let list = ASIC_METRICS.slice();

    if (segment === "sha")
      list = list.filter((x) => x.metrics.algo === "SHA-256");
    if (segment === "scrypt")
      list = list.filter((x) => x.metrics.algo === "Scrypt");

    if (!list.length) return null;

    const cfg = getPortfolioConfig(list.length);

    list = list
      .filter((x) => x.metrics.roiPercentYear >= cfg.minRoi)
      .sort((a, b) => b.metrics.yieldPerRub - a.metrics.yieldPerRub);

    if (!list.length) return null;

    const { items, totalSpent, remaining } = greedyBuild(
      list,
      budgetRub,
      cfg.maxShare
    );

    if (!items.length) return null;

    const summary = calcPortfolioSummary(items, totalSpent, budgetRub);

    return { segment, items, summary, remaining, budgetRub };
  }

  // ---------- RENDER ----------
  function renderMainPortfolio(p) {
  if (!p) {
    mainPortfolioBody.innerHTML =
      '<div class="gic-error">Нет данных для портфеля.</div>';
    return;
  }

  mainPortfolioLabel.textContent =
    p.segment === "sha"
      ? "Портфель BTC (SHA-256)"
      : p.segment === "scrypt"
      ? "Портфель LTC+DOGE (Scrypt)"
      : "Смешанный портфель";

  mainPortfolioTagline.textContent =
    p.segment === "sha"
      ? "Фокус на максимальную доходность в BTC."
      : p.segment === "scrypt"
      ? "Портфель под добычу LTC+DOGE."
      : "Сбалансированный набор из BTC и LTC+DOGE.";

  mainPortfolioRoiBadge.textContent =
    `${p.summary.roiPercentPortfolio.toFixed(1)} % годовых`;

  const itemsHtml = p.items
    .map((i) => {
      const url = i.asic.detail_url || "#";
      const img = i.asic.image;

      return `
        <div class="gic-portfolio-item">
          <div>
            <div style="display:flex;align-items:flex-start;gap:10px;">
              
              ${
                img
                  ? `<a href="${url}" target="_blank" rel="noopener">
                       <img src="${img}"
                            alt="${i.asic.name}"
                            style="width:64px;height:64px;border-radius:8px;object-fit:cover;">
                     </a>`
                  : ""
              }

              <div>
                <div class="gic-portfolio-item__title">
                  <a href="${url}" target="_blank" rel="noopener"
                     style="color:#111827;text-decoration:none;">
                    ${i.asic.name}
                  </a>
                </div>
                <div class="gic-portfolio-item__meta">
                  ${i.metrics.algo}, ${i.metrics.crypto} • ${formatNum(
        i.asic.power_kw,
        0
      )} Вт • Цена: ${formatNum(i.metrics.priceRub)} ₽
                </div>
              </div>

            </div>
          </div>

          <div class="gic-portfolio-item__count">
            <span class="gic-portfolio-item__count-main">${i.count} шт.</span>
            <span class="gic-portfolio-item__count-sub">
              На сумму ~${formatNum(i.spent)} ₽
            </span>
          </div>

          <div class="gic-portfolio-item__income">
            <span class="gic-portfolio-item__count-main">
              ${formatNum((i.metrics.netRubYear / 12) * i.count)} ₽/мес
            </span>
            <span class="gic-portfolio-item__count-sub">Чистая прибыль</span>
          </div>
        </div>`;
    })
    .join("");

  const s = p.summary;

  mainPortfolioBody.innerHTML = `
  <div class="gic-portfolio-items">${itemsHtml}</div>

  <div class="gic-portfolio-totals">

    <!-- 1. Стоимость оборудования -->
    <div class="gic-portfolio-tile">
      <div class="gic-portfolio-tile__label">Стоимость оборудования</div>
      <div class="gic-portfolio-tile__value">${formatNum(
        p.summary.totalSpent
      )} ₽</div>
      <div class="gic-portfolio-tile__sub">Остаток: ${formatNum(
        p.remaining
      )} ₽</div>
    </div>

    <!-- 2. Расходы на электроэнергию -->
    <div class="gic-portfolio-tile gic-portfolio-tile--danger">
      <div class="gic-portfolio-tile__label">Расходы на электроэнергию</div>
      <div class="gic-portfolio-tile__value">${formatNum(
        p.summary.totalPowerCostYear / 12
      )} ₽/мес</div>
      <div class="gic-portfolio-tile__sub">≈ ${formatNum(
        p.summary.totalPowerCostYear / getTariff() / 12
      )} кВт⋅ч/мес</div>
    </div>

    <!-- 3. Чистая прибыль в месяц -->
    <div class="gic-portfolio-tile gic-portfolio-tile--accent">
      <div class="gic-portfolio-tile__label">Чистая прибыль в месяц</div>
      <div class="gic-portfolio-tile__value">${formatNum(
        p.summary.monthlyNet
      )} ₽/мес</div>
      <div class="gic-portfolio-tile__sub">После вычета затрат на электроэнергию</div>
    </div>

    <!-- 4. Доходность портфеля -->
    <div class="gic-portfolio-tile">
      <div class="gic-portfolio-tile__label">Доходность портфеля</div>
      <div class="gic-portfolio-tile__value">${p.summary.roiPercentPortfolio.toFixed(
        1
      )} %</div>
      <div class="gic-portfolio-tile__sub">От вложенной суммы</div>
    </div>

  </div>
`;

}


  function renderAltPortfolios(active) {
    altPortfoliosWrap.innerHTML = "";

    ["sha", "scrypt", "mixed"]
      .filter((s) => s !== active)
      .forEach((seg) => {
        const p = portfoliosBySegment[seg];

        if (!p) {
          altPortfoliosWrap.insertAdjacentHTML(
            "beforeend",
            `<div class="gic-alt-portfolio">
              <div class="gic-alt-portfolio__header">
                <div class="gic-alt-portfolio__title">Недостаточно данных</div>
              </div>
            </div>`
          );
          return;
        }

        altPortfoliosWrap.insertAdjacentHTML(
          "beforeend",
          `<div class="gic-alt-portfolio" data-segment="${seg}">
          <div class="gic-alt-portfolio__header">
            <div>
              <div class="gic-alt-portfolio__title">
                ${
                  seg === "sha"
                    ? "Портфель BTC (SHA-256)"
                    : seg === "scrypt"
                    ? "Портфель LTC+DOGE (Scrypt)"
                    : "Смешанный портфель"
                }
              </div>
              <div class="gic-alt-portfolio__meta">Нажмите, чтобы раскрыть</div>
            </div>
            <div class="gic-alt-portfolio__roi">${p.summary.roiPercentPortfolio.toFixed(
              1
            )}% годовых</div>
            <div class="gic-alt-portfolio__chevron">▶</div>
          </div>
          <div class="gic-alt-portfolio__body" style="display:none;"></div>
        </div>`
        );
      });

    altPortfoliosWrap.querySelectorAll(".gic-alt-portfolio").forEach((card) => {
      const header = card.querySelector(".gic-alt-portfolio__header");
      const body = card.querySelector(".gic-alt-portfolio__body");
      const seg = card.dataset.segment;

      header.addEventListener("click", () => {
        if (card.classList.contains("gic-alt-portfolio--open")) {
          card.classList.remove("gic-alt-portfolio--open");
          body.style.display = "none";
        } else {
          card.classList.add("gic-alt-portfolio--open");
          body.style.display = "block";
          renderAltPortfolioContent(seg, body);
        }
      });
    });
  }

  function renderAltPortfolioContent(seg, container) {
  const p = portfoliosBySegment[seg];
  if (!p) return;

  container.innerHTML = `
    <div class="gic-portfolio-items">
      ${p.items
        .map((i) => {
          const url = i.asic.detail_url || "#";
          const img = i.asic.image;

          return `
          <div class="gic-portfolio-item">
            <div>
              <div style="display:flex;align-items:flex-start;gap:10px;">

                ${
                  img
                    ? `<a href="${url}" target="_blank" rel="noopener">
                         <img src="${img}"
                              alt="${i.asic.name}"
                              style="width:48px;height:48px;border-radius:6px;object-fit:cover;">
                       </a>`
                    : ""
                }

                <div>
                  <div class="gic-portfolio-item__title">
                    <a href="${url}" target="_blank" rel="noopener"
                       style="color:#111827;text-decoration:none;">
                      ${i.asic.name}
                    </a>
                  </div>
                  <div class="gic-portfolio-item__meta">
                    ${i.metrics.algo}, ${i.metrics.crypto}
                  </div>
                </div>

              </div>
            </div>

            <div class="gic-portfolio-item__count">
              ${i.count} шт.
            </div>

            <div class="gic-portfolio-item__income">
              ${formatNum((i.metrics.netRubYear / 12) * i.count)} ₽/мес
            </div>
          </div>`;
        })
        .join("")}
    </div>
  `;
}


  // ---------- MAIN CALC ----------
  function runCalculation() {
    const budget = parseBudget(investInput?.value || "");
    if (!budget || budget <= 0) {
      mainPortfolioBody.innerHTML =
        '<div class="gic-error">Введите сумму инвестиций.</div>';
      return;
    }

    const tariff = getTariff();

    buildAsicMetrics(tariff);

    portfoliosBySegment.sha = buildPortfolioForSegment("sha", budget);
    portfoliosBySegment.scrypt = buildPortfolioForSegment("scrypt", budget);
    portfoliosBySegment.mixed = buildPortfolioForSegment("mixed", budget);

    if (!portfoliosBySegment[activeSegment]) {
      activeSegment =
        portfoliosBySegment.sha
          ? "sha"
          : portfoliosBySegment.scrypt
          ? "scrypt"
          : "mixed";
    }

    renderMainPortfolio(portfoliosBySegment[activeSegment]);
    renderAltPortfolios(activeSegment);
  }

  function resetForm() {
    setActiveLevel("novice", { updateInput: true });
    if (tariffInput) tariffInput.value = "5.3";

    activeSegment = "sha";

    portfolioTabs.forEach((t) => {
      t.classList.toggle("gic-tab-btn--active", t.dataset.segment === "sha");
    });

    mainPortfolioBody.innerHTML =
      '<div class="gic-muted">Портфель ещё не рассчитан.</div>';
  }

  // ---------- EVENTS ----------
  document
    .querySelectorAll('input[name="gic-tariff-mode"]')
    .forEach((input) => {
      input.addEventListener("change", updateTariffModeUI);
    });

  if (calcBtn) calcBtn.addEventListener("click", runCalculation);
  if (resetBtn) resetBtn.addEventListener("click", resetForm);

  // вкладки результата
  portfolioTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const seg = tab.dataset.segment;
      if (!seg) return;

      portfolioTabs.forEach((t) =>
        t.classList.toggle("gic-tab-btn--active", t === tab)
      );

      activeSegment = seg;

      if (portfoliosBySegment[seg]) {
        renderMainPortfolio(portfoliosBySegment[seg]);
        renderAltPortfolios(seg);
      }
    });
  });

  // ---------- INIT ----------
  async function init() {
    try {
      setActiveLevel("novice", { updateInput: true });

      document.querySelector(
        'input[name="gic-tariff-mode"][value="hosting"]'
      ).checked = true;
      updateTariffModeUI();

      resultSubtitle.textContent = "Загружаем данные...";

      await Promise.all([loadAsics(), loadMarket()]);

      renderMarketMeta();

      resultSubtitle.textContent =
        "Укажите параметры и нажмите «Рассчитать портфель».";
    } catch (e) {
      console.error(e);
      resultSubtitle.textContent = "Ошибка загрузки данных.";
    }
  }

  init();
})();
