document.addEventListener("DOMContentLoaded", () => {
  const widget = document.getElementById("feedbackWidget");
  if (!widget) return;

  const steps = widget.querySelectorAll(".feedback-step");
  const stars = widget.querySelectorAll(".feedback-star");
  const tags = widget.querySelectorAll(".feedback-tag");
  const form = widget.querySelector("#feedbackForm");
  const ratingInput = widget.querySelector("#fb_rating");
  const tagsInput = widget.querySelector("#fb_tags");

  let currentRating = 0;

  /** Показ нужного шага */
  const showStep = (name) => {
    steps.forEach((s) =>
      s.classList.toggle("feedback-step--active", s.dataset.step === name)
    );
  };

  /** ⭐ Звёзды */
  stars.forEach((star, index) => {
    star.addEventListener("mouseenter", () => {
      stars.forEach((s, i) => s.classList.toggle("hovered", i <= index));
    });
    star.addEventListener("mouseleave", () => {
      stars.forEach((s) => s.classList.remove("hovered"));
    });
    star.addEventListener("click", () => {
      currentRating = index + 1;
      ratingInput.value = currentRating;
      stars.forEach((s, i) => s.classList.toggle("active", i < currentRating));

      // Если 5 звёзд — показываем платформы, иначе форму
      if (currentRating === 5) showStep("platforms");
      else showStep("form");
    });
  });

  /** 🎯 Теги */
  tags.forEach((tag) => {
    tag.addEventListener("click", () => {
      tag.classList.toggle("selected");
      const selectedTags = Array.from(tags)
        .filter((t) => t.classList.contains("selected"))
        .map((t) => t.dataset.tag || t.textContent.trim());
      tagsInput.value = selectedTags.join(", ");
    });
  });

  /** 🔙 Кнопка Назад */
  widget.querySelectorAll("[data-action='back']").forEach((btn) => {
    btn.addEventListener("click", () => {
      showStep("rate");
      currentRating = 0;
      stars.forEach((s) => s.classList.remove("active"));
    });
  });

  /** 📤 Отправка формы */
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const submitBtn = form.querySelector("button[type='submit']");
    if (submitBtn) submitBtn.disabled = true;

    // Собираем данные формы
    const formData = new FormData(form);

    const clientComment = (formData.get("client_comment") || "").trim();
    const selectedTags = Array.from(tags)
      .filter((t) => t.classList.contains("selected"))
      .map((t) => t.dataset.tag || t.textContent.trim());

    const rating = ratingInput.value || "—";
    const tagText = selectedTags.length ? selectedTags.join(", ") : "—";

    // Собираем итоговый текст комментария для Bitrix
    const fullComment = 
`Оценка клиента: ${rating} ★
Причины: ${tagText}

Комментарий клиента:
${clientComment || "—"}`;

    formData.set("client_comment", fullComment);

    try {
      const response = await fetch("./send_lead.php", {
        method: "POST",
        body: formData,
      });

      // Проверяем ответ, если нужно
      const result = await response.json().catch(() => null);

      if (result && result.success) {
        form.reset();
        tags.forEach((t) => t.classList.remove("selected"));
        stars.forEach((s) => s.classList.remove("active"));
        showStep("rate");
        currentRating = 0;
      }
    } catch (err) {
      // Ошибки не показываем
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
});
