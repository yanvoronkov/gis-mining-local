document.addEventListener("DOMContentLoaded", () => {
  const table = document.querySelector("#priceTable");
  const loader = document.querySelector("#tableLoader");
  const search = document.querySelector("#searchInput");
  const spinner = document.querySelector("#searchSpinner");
  const rows = Array.from(table.querySelectorAll("tbody tr"));
  const priceCol = document.querySelector("[data-sort='price']");
  let sortMode = 0;
  const originalOrder = rows.map((r, i) => ({ row: r, index: i }));

  // Анимация появления строк
  rows.forEach((row, i) => row.style.setProperty("--i", i));

  // Поиск с анимацией и skeleton
  search.addEventListener("input", () => {
    spinner.style.display = "inline-block";
    loader.style.display = "flex";
    rows.forEach(r => r.classList.add("skeleton-row"));

    setTimeout(() => {
      const val = search.value.trim().toLowerCase();
      rows.forEach(row => {
        const model = row.cells[0].innerText.toLowerCase();
        row.style.display = model.includes(val) ? "" : "none";
        row.classList.remove("skeleton-row");
      });
      spinner.style.display = "none";
      loader.style.display = "none";
    }, 500);
  });

  // Сортировка по цене
  priceCol.addEventListener("click", () => {
    sortMode = (sortMode + 1) % 3;
    const tbody = table.querySelector("tbody");
    const sorted = [...rows].filter(r => r.style.display !== "none");

    if (sortMode === 1) {
      sorted.sort((a, b) => a.cells[1].dataset.value - b.cells[1].dataset.value);
      priceCol.querySelector("i").className = "fa-solid fa-sort-up";
    } else if (sortMode === 2) {
      sorted.sort((a, b) => b.cells[1].dataset.value - a.cells[1].dataset.value);
      priceCol.querySelector("i").className = "fa-solid fa-sort-down";
    } else {
      sorted.sort((a, b) => {
        const ai = originalOrder.find(x => x.row === a)?.index ?? 0;
        const bi = originalOrder.find(x => x.row === b)?.index ?? 0;
        return ai - bi;
      });
      priceCol.querySelector("i").className = "fa-solid fa-sort";
    }

    tbody.innerHTML = "";
    sorted.forEach((r, i) => {
      r.style.setProperty("--i", i);
      tbody.appendChild(r);
    });
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const popup = document.querySelector("#mainPopupFormWrapper");
  const popupTitle = popup?.querySelector(".form-popup__title");
  const popupImage = popup?.querySelector(".form-popup__img");
  const popupFormName = popup?.querySelector('input[name="form_name"]');
  const popupProductName = popup?.querySelector("#popup_product_name"); // 👈 новое поле

  document.querySelectorAll(".btn-order").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();

      const name = btn.dataset.name || "Оборудование";
      const imgSrc = btn.dataset.img || "/local/templates/main/assets/img/components/popup_form_image.png";

      if (popupTitle) popupTitle.textContent = name;
      if (popupImage) popupImage.src = imgSrc;
      if (popupFormName) popupFormName.value = "Заказ оборудования: " + name;
    });
  });
});






