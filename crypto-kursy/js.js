document.addEventListener("DOMContentLoaded", () => {

    const container = document.getElementById("cryptoList");
    const updatedEl = document.getElementById("cryptoUpdated");

    // POPUP криптовалюты
    const popup = document.getElementById("cryptoPopup");
    const popupClose = document.getElementById("cryptoPopupClose");
    const popupLogo = document.getElementById("cryptoPopupLogo");
    const popupTitle = document.getElementById("cryptoPopupTitle");
    const popupContent = document.getElementById("cryptoPopupContent");
    const popupChart = document.getElementById("cryptoPopupChart");

    function openCryptoPopup() {
        popup.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function closeCryptoPopup() {
        popup.style.display = "none";
        document.body.style.overflow = "";
    }

    popupClose.addEventListener("click", closeCryptoPopup);
    popup.addEventListener("click", e => {
        if (e.target.classList.contains("crypto-popup__overlay")) closeCryptoPopup();
    });

    // POPUP embed
    const embedPopup = document.getElementById("embedPopup");
    const embedPopupClose = document.getElementById("embedPopupClose");
    const openEmbedPopupBtn = document.getElementById("openEmbedPopup");
    const embedCodeArea = document.getElementById("embedCodeArea");
    const copyEmbedCodeBtn = document.getElementById("copyEmbedCode");

    function openEmbedPopup() {
    const widgetCode = `<script src="https://gis-mining.ru/crypto-widget/widget.js"></script>
<div id="gis-crypto"></div>
<script>
  new GisCryptoWidget({
    selector: '#gis-crypto',
    lang: 'ru'
  });
<\/script>`;

    embedCodeArea.value = widgetCode;

    embedPopup.style.display = "block";
    document.body.style.overflow = "hidden";
}


    function closeEmbedPopup() {
        embedPopup.style.display = "none";
        document.body.style.overflow = "";
    }

    openEmbedPopupBtn.addEventListener("click", openEmbedPopup);
    embedPopupClose.addEventListener("click", closeEmbedPopup);
    embedPopup.addEventListener("click", e => {
        if (e.target.classList.contains("crypto-popup__overlay")) closeEmbedPopup();
    });

    copyEmbedCodeBtn.addEventListener("click", () => {
        embedCodeArea.select();
        navigator.clipboard.writeText(embedCodeArea.value).then(() => {
            copyEmbedCodeBtn.textContent = "Скопировано!";
            setTimeout(() => copyEmbedCodeBtn.textContent = "Скопировать код", 1500);
        }).catch(() => {});
    });

    // Список монет
    const coins = [
        "BTCUSDT","ETHUSDT","ETCUSDT","LTCUSDT","BCHUSDT",
        "ZECUSDT","DASHUSDT","RVNUSDT","ERGUSDT","XMRUSDT",
        "TONUSDT","CFXUSDT","KASUSDT","FLUXUSDT","NEOUSDT","ZENUSDT"
    ];

    let cryptoData = [];

    const SKELETON_COUNT = 8;

    // ---------- Skeleton ----------
    function showSkeleton() {
        const items = [];
        for (let i = 0; i < SKELETON_COUNT; i++) {
            items.push(`
                <div class="crypto-card crypto-card--skeleton">
                    <div class="crypto-card__header">
                        <div class="crypto-skel crypto-skel-avatar"></div>
                        <div style="flex:1;">
                            <div class="crypto-skel crypto-skel-line crypto-skel-line-lg"></div>
                            <div class="crypto-skel crypto-skel-line crypto-skel-line-sm"></div>
                        </div>
                    </div>
                    <div class="crypto-skel crypto-skel-line crypto-skel-line-xl" style="margin-top:18px;"></div>
                    <div class="crypto-skel crypto-skel-line crypto-skel-line-md" style="margin-top:10px;"></div>
                </div>
            `);
        }
        container.innerHTML = items.join("");
    }

    // формат даты обновления: 14:27, 11 января 2025
    function formatUpdatedDate(date) {
        const months = [
            "января", "февраля", "марта", "апреля", "мая", "июня",
            "июля", "августа", "сентября", "октября", "ноября", "декабря"
        ];
        const h = String(date.getHours()).padStart(2, "0");
        const m = String(date.getMinutes()).padStart(2, "0");
        const d = date.getDate();
        const monthName = months[date.getMonth()];
        const y = date.getFullYear();
        return `${h}:${m}, ${d} ${monthName} ${y}`;
    }

    function updateTime() {
        updatedEl.textContent = "Обновлено: " + formatUpdatedDate(new Date());
    }

    // ---------- Загрузка с Binance ----------
    async function loadCrypto() {
        try {
            showSkeleton();
            const resp = await fetch("https://api.binance.com/api/v3/ticker/24hr");
            const data = await resp.json();
            cryptoData = data.filter(c => coins.includes(c.symbol));
            renderCards(cryptoData);
            updateTime();
        } catch (e) {
            console.error("Ошибка Binance:", e);
            container.innerHTML = "<p>Ошибка загрузки данных Binance</p>";
        }
    }

    // ---------- Рендер карточек ----------
    function renderCards(list) {
        container.innerHTML = list.map(coin => {
            const symbol = coin.symbol.replace("USDT", "");
            const change = Number(coin.priceChangePercent).toFixed(2);
            const icon = `https://raw.githubusercontent.com/spothq/cryptocurrency-icons/master/128/color/${symbol.toLowerCase()}.png`;

            return `
                <div class="crypto-card"
                     data-symbol="${coin.symbol}"
                     data-icon="${icon}">
                    <div class="crypto-card__header">
                        <img class="crypto-card__icon" src="${icon}" onerror="this.style.display='none'">
                        <div>
                            <div class="crypto-card__name">${symbol}</div>
                            <div class="crypto-card__symbol">/ ${coin.symbol}</div>
                        </div>
                    </div>

                    <div class="crypto-card__price">$${Number(coin.lastPrice).toFixed(2)}</div>

                    <div class="crypto-card__change ${change >= 0 ? "crypto-card__change--up" : "crypto-card__change--down"}">
                        ${change}%
                    </div>
                </div>
            `;
        }).join("");
    }

    // ---------- Клик по карточке ----------
    document.addEventListener("click", async (e) => {
        const card = e.target.closest(".crypto-card");
        if (!card || card.classList.contains("crypto-card--skeleton")) return;

        const symbolFull = card.dataset.symbol;
        const icon = card.dataset.icon;
        const coin = cryptoData.find(c => c.symbol === symbolFull);
        if (!coin) return;

        fillCryptoPopup(symbolFull, coin, icon);
        await drawChart(symbolFull);
        openCryptoPopup();
    });

    function fillCryptoPopup(symbolFull, coin, icon) {
        const symbol = symbolFull.replace("USDT", "");
        const price = Number(coin.lastPrice).toFixed(2);
        const change = Number(coin.priceChangePercent).toFixed(2);
        const isUp = change >= 0;

        popupLogo.src = icon;
        popupTitle.textContent = symbol;

        popupContent.innerHTML = `
            <div class="crypto-price-main">
                $${price}
                <div class="crypto-price-change-badge ${isUp ? "crypto-up" : "crypto-down"}">
                    ${isUp ? "+" : ""}${change}%
                </div>
            </div>

            <div class="crypto-info-grid">
                <div><span>High 24ч:</span> $${Number(coin.highPrice).toFixed(2)}</div>
                <div><span>Low 24ч:</span> $${Number(coin.lowPrice).toFixed(2)}</div>
                <div><span>Объём:</span> ${Number(coin.volume).toLocaleString()}</div>
                <div><span>Quote Vol:</span> ${Number(coin.quoteVolume).toLocaleString()}</div>
                <div><span>Цена открытия:</span> $${Number(coin.openPrice).toFixed(2)}</div>
                <div><span>Средняя цена 24ч:</span> $${Number(coin.weightedAvgPrice).toFixed(2)}</div>
            </div>
        `;
    }

    // ---------- График с осями ----------
    async function drawChart(symbolFull) {
        popupChart.innerHTML = "";

        try {
            const url = `https://api.binance.com/api/v3/uiKlines?symbol=${symbolFull}&interval=1h`;
            const resp = await fetch(url);
            const data = await resp.json();

            const prices = data.map(c => Number(c[4]));
            const times = data.map(c => Number(c[0])); // timestamp
            if (!prices.length) return;

            const width = 1000;
            const height = 300;

            const max = Math.max(...prices);
            const min = Math.min(...prices);
            const len = prices.length;

            // линии сетки по Y и подписи
            let gridLines = "";
            let yLabels = "";
            const ySteps = 4;
            for (let i = 0; i <= ySteps; i++) {
                const y = (height / ySteps) * i;
                const val = max - ((max - min) * i) / ySteps;
                gridLines += `<line x1="0" y1="${y}" x2="${width}" y2="${y}" class="crypto-chart-grid-line" />`;
                yLabels += `<text x="0" y="${y - 4}" class="crypto-chart-text">${val.toFixed(2)}</text>`;
            }

            // точки графика
            const pointsArr = prices.map((p, i) => {
                const x = (i / (len - 1 || 1)) * width;
                const y = (max === min)
                    ? height / 2
                    : height - ((p - min) / (max - min)) * height;
                return { x, y };
            });

            const points = pointsArr.map(p => `${p.x},${p.y}`).join(" ");

            // area под графиком
            const areaPoints = `0,${height} ${points} ${width},${height}`;

            // ось X — 4 отметки времени
            let xLabels = "";
            const xTicksIdx = [
                0,
                Math.floor(len / 3),
                Math.floor((2 * len) / 3),
                len - 1
            ];
            const used = new Set();
            xTicksIdx.forEach(idx => {
                if (idx < 0 || idx >= len || used.has(idx)) return;
                used.add(idx);
                const x = pointsArr[idx].x;
                const date = new Date(times[idx]);
                const hh = String(date.getHours()).padStart(2, "0");
                const mm = String(date.getMinutes()).padStart(2, "0");
                const label = `${hh}:${mm}`;
                xLabels += `<text x="${x}" y="${height - 5}" text-anchor="middle" class="crypto-chart-text">${label}</text>`;
            });

            const first = pointsArr[0];
            const last = pointsArr[pointsArr.length - 1];

            popupChart.innerHTML = `
                <svg width="100%" height="100%" viewBox="0 0 ${width} ${height}">
                    ${gridLines}
                    ${yLabels}
                    <polyline class="crypto-chart-area" points="${areaPoints}" />
                    <polyline class="crypto-chart-line" points="${points}" />
                    <circle class="crypto-chart-point" cx="${first.x}" cy="${first.y}"></circle>
                    <circle class="crypto-chart-point" cx="${last.x}" cy="${last.y}"></circle>
                    ${xLabels}
                </svg>
            `;
        } catch (e) {
            console.error("Ошибка графика:", e);
        }
    }

    // ---------- Фильтры с skeleton-анимацией ----------
    document.querySelectorAll(".crypto-filter-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const filter = btn.dataset.filter;
            document.querySelectorAll(".crypto-filter-btn")
                .forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            showSkeleton();

            setTimeout(() => {
                if (filter === "all") renderCards(cryptoData);
                if (filter === "up") renderCards(cryptoData.filter(c => c.priceChangePercent > 0));
                if (filter === "down") renderCards(cryptoData.filter(c => c.priceChangePercent < 0));
            }, 300);
        });
    });

    // ---------- Старт и автообновление (5 минут) ----------
    loadCrypto();
    setInterval(loadCrypto, 300000); // 5 минут
});
