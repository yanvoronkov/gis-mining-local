// -------------------------------
// Получение client_id Яндекс.Метрики
// -------------------------------
function getYandexClientId() {
    try {
        // 1) Попытка получить через API Метрики
        if (window.ym && typeof window.ym.getClientID === "function") {
            return window.ym.getClientID();
        }

        // 2) Попытка получить через cookie _ym_uid
        const match = document.cookie.match(/_ym_uid=(\d+)/);
        if (match) return match[1];

    } catch (e) {
        console.error("Ошибка получения client_id Метрики:", e);
    }

    return null;
}


// -------------------------------
// Инициализация кнопки
// -------------------------------
function initContactManagerButton() {

    const btn = document.getElementById("contactManagerBtn");
    if (!btn) {
        console.warn("Кнопка #contactManagerBtn не найдена!");
        return;
    }

    btn.addEventListener("click", () => {

        const cid = getYandexClientId();
        const param = cid ? `cid_${cid}` : "cid_unknown";

        const url = `https://t.me/gismining_chat_bot?start=${param}`;

        // Открываем бота
        window.open(url, "_blank");
    });
}


// -------------------------------
// Старт
// -------------------------------
document.addEventListener("DOMContentLoaded", initContactManagerButton);
