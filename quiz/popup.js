document.addEventListener("DOMContentLoaded", () => {
  const openBtn  = document.getElementById("openCallbackBtn");
  const overlay  = document.getElementById("gmOverlay");
  const modal    = document.getElementById("gmModal");
  const closeBtn = document.getElementById("gmClose");
  const form     = document.getElementById("callbackForm");
  const errEl    = document.getElementById("popupError");
  const okEl     = document.getElementById("popupSuccess");

  // скрыть попап при загрузке
  if (overlay) { overlay.hidden = true; overlay.classList.remove("active"); }
  if (modal)   { modal.hidden   = true; modal.classList.remove("active"); }

  const open = (e) => {
    e?.preventDefault();
    overlay.hidden = false;
    modal.hidden   = false;
    requestAnimationFrame(() => {
      overlay.classList.add("active");
      modal.classList.add("active");
    });
  };

  const close = () => {
    overlay.classList.remove("active");
    modal.classList.remove("active");
    setTimeout(() => { overlay.hidden = true; modal.hidden = true; }, 250);
  };

  openBtn  && openBtn.addEventListener("click", open);
  closeBtn && closeBtn.addEventListener("click", close);
  overlay  && overlay.addEventListener("click", close);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") close(); });

  if (form) {
    // заполнение page_url
    const pageUrl = form.querySelector('input[name="page_url"]');
    if (pageUrl) pageUrl.value = window.location.href;

    // UTM скрытые поля
    const utmFields = ["utm_source","utm_medium","utm_campaign","utm_content","utm_term"];
    utmFields.forEach(name => {
      if (!form.querySelector(`input[name="${name}"]`)) {
        const input = document.createElement("input");
        input.type  = "hidden";
        input.name  = name;
        form.appendChild(input);
      }
    });
    const params = new URLSearchParams(window.location.search);
    utmFields.forEach(name => {
      const field = form.querySelector(`input[name="${name}"]`);
      if (field) field.value = params.get(name) || "";
    });

    // Маска телефона
    const phoneInput = form.querySelector('input[name="client_phone"]');
    let phoneMask;
    if (phoneInput && window.IMask) {
      phoneMask = IMask(phoneInput, { mask: '+{7} (000) 000-00-00' });
    }

    // Создаем прелоадер
    const loader = document.createElement("div");
    loader.className = "gm-loader";
    loader.style.display = "none";
    form.appendChild(loader);

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      errEl.style.display = "none";
      okEl.style.display  = "none";

      // проверка телефона на полное заполнение
      if (!phoneInput.value || phoneInput.value.includes("_") || phoneInput.value.length < 18) {
        errEl.textContent = "Введите корректный номер телефона.";
        errEl.style.display = "block";
        errEl.classList.add("show");
        return;
      }

      const fd = new FormData(form);

      // блокируем форму и показываем прелоадер
      Array.from(form.elements).forEach(el => el.disabled = true);
      loader.style.display = "block";

      try {
        const res  = await fetch("/send_lead.php", { method: "POST", body: fd });
        const json = await res.json();

        if (json && json.success) {
          okEl.textContent = "Заявка успешно отправлена!";
          okEl.style.display = "block";
          okEl.classList.add("show");
          form.reset();
        } else {
          errEl.textContent = (json && json.error) ? json.error : "Ошибка отправки. Попробуйте позже.";
          errEl.style.display = "block";
          errEl.classList.add("show");
        }
      } catch {
        errEl.textContent = "Ошибка соединения с сервером.";
        errEl.style.display = "block";
        errEl.classList.add("show");
      }

      // разблокируем форму
      Array.from(form.elements).forEach(el => el.disabled = false);
      loader.style.display = "none";
    });
  }
});


document.addEventListener("DOMContentLoaded", () => {
  const banner = document.getElementById("cookieBanner");
  const close  = document.getElementById("cookieClose");

  if (!banner) return;

  // Показывать баннер только если пользователь ещё не согласился
  if (localStorage.getItem("cookieAccepted")) {
    banner.style.display = "none";
  }

  close.addEventListener("click", () => {
    banner.style.display = "none";
    localStorage.setItem("cookieAccepted", "true");
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const heroVideo   = document.getElementById("heroVideo");
  const overlay     = document.getElementById("videoOverlay");
  const modal       = document.getElementById("videoModal");
  const closeBtn    = document.getElementById("closeVideoPopup");
  const popupVideo  = document.getElementById("popupVideo");

  if (!heroVideo || !overlay || !modal || !closeBtn || !popupVideo) return;

  const open = () => {
    overlay.classList.add("active");
    modal.classList.add("active");
    popupVideo.currentTime = 0;
    popupVideo.play();
  };

  const close = () => {
    overlay.classList.remove("active");
    modal.classList.remove("active");
    popupVideo.pause();
  };

  heroVideo.addEventListener("click", open);
  closeBtn.addEventListener("click", close);
  overlay.addEventListener("click", close);
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });
});
