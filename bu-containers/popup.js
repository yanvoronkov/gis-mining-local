document.addEventListener("DOMContentLoaded", () => {

  /* ============================
     POPUP CALLBACK
  ============================ */

  const openBtns = document.querySelectorAll(".js-open-callback");
  const overlay  = document.getElementById("gmOverlay");
  const modal    = document.getElementById("gmModal");
  const closeBtn = document.getElementById("gmClose");
  const form     = document.getElementById("callbackForm");
  const errEl    = document.getElementById("popupError");
  const okEl     = document.getElementById("popupSuccess");

  // Скрываем попап при загрузке
  if (overlay) overlay.hidden = true;
  if (modal)   modal.hidden   = true;

  const openPopup = (e) => {
    e.preventDefault();
    overlay.hidden = false;
    modal.hidden   = false;

    requestAnimationFrame(() => {
      overlay.classList.add("active");
      modal.classList.add("active");
    });
  };

  const closePopup = () => {
    overlay.classList.remove("active");
    modal.classList.remove("active");
    setTimeout(() => {
      overlay.hidden = true;
      modal.hidden   = true;
    }, 250);
  };

  // Вешаем обработчики на ВСЕ кнопки открытия
  openBtns.forEach(btn => {
    btn.addEventListener("click", openPopup);
  });

  if (closeBtn) closeBtn.addEventListener("click", closePopup);
  if (overlay)  overlay.addEventListener("click", closePopup);

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closePopup();
  });

  /* ==== ОБРАБОТКА ФОРМЫ ==== */

  if (form) {
    const pageUrl = form.querySelector('input[name="page_url"]');
    if (pageUrl) pageUrl.value = window.location.href;

    const utmFields = ["utm_source","utm_medium","utm_campaign","utm_content","utm_term"];
    const params = new URLSearchParams(window.location.search);

    utmFields.forEach(name => {
      let field = form.querySelector(`input[name="${name}"]`);
      if (!field) {
        field = document.createElement("input");
        field.type = "hidden";
        field.name = name;
        form.appendChild(field);
      }
      field.value = params.get(name) || "";
    });

    const phoneInput = form.querySelector('input[name="client_phone"]');
    let phoneMask;
    if (phoneInput && window.IMask) {
      phoneMask = IMask(phoneInput, { mask: '+{7} (000) 000-00-00' });
    }

    const loader = document.createElement("div");
    loader.className = "gm-loader";
    loader.style.display = "none";
    form.appendChild(loader);

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      errEl.style.display = "none";
      okEl.style.display  = "none";

      if (!phoneInput.value || phoneInput.value.includes("_")) {
        errEl.textContent = "Введите корректный номер телефона.";
        errEl.style.display = "block";
        errEl.classList.add("show");
        return;
      }

      const fd = new FormData(form);

      Array.from(form.elements).forEach(el => el.disabled = true);
      loader.style.display = "block";

      try {
        const res  = await fetch("/send_lead.php", { method: "POST", body: fd });
        const json = await res.json();

        if (json.success) {
          okEl.textContent = "Заявка успешно отправлена!";
          okEl.style.display = "block";
          okEl.classList.add("show");
          form.reset();
        } else {
          errEl.textContent = json.error || "Ошибка отправки.";
          errEl.style.display = "block";
          errEl.classList.add("show");
        }
      } catch {
        errEl.textContent = "Ошибка соединения с сервером.";
        errEl.style.display = "block";
        errEl.classList.add("show");
      }

      Array.from(form.elements).forEach(el => el.disabled = false);
      loader.style.display = "none";
    });
  }

  /* ============================
     COOKIE BANNER
  ============================ */

  const banner = document.getElementById("cookieBanner");
  const closeCookie = document.getElementById("cookieClose");

  if (banner && closeCookie) {
    if (localStorage.getItem("cookieAccepted")) {
      banner.style.display = "none";
    }
    closeCookie.addEventListener("click", () => {
      banner.style.display = "none";
      localStorage.setItem("cookieAccepted", "true");
    });
  }

  /* ============================
     HERO VIDEO POPUP
  ============================ */

  const heroVideo   = document.getElementById("heroVideo");
  const videoOverlay = document.getElementById("videoOverlay");
  const videoModal   = document.getElementById("videoModal");
  const videoClose   = document.getElementById("closeVideoPopup");
  const popupVideo   = document.getElementById("popupVideo");

  if (heroVideo && videoOverlay && videoModal && videoClose && popupVideo) {

    const openVideo = () => {
      videoOverlay.classList.add("active");
      videoModal.classList.add("active");
      popupVideo.currentTime = 0;
      popupVideo.play();
    };

    const closeVideo = () => {
      videoOverlay.classList.remove("active");
      videoModal.classList.remove("active");
      popupVideo.pause();
    };

    heroVideo.addEventListener("click", openVideo);
    videoClose.addEventListener("click", closeVideo);
    videoOverlay.addEventListener("click", closeVideo);

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeVideo();
    });
  }

});
