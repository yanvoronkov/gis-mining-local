/**
 * JavaScript для нового каталога по стандартам Битрикса
 * Использует стандартный механизм фильтра с интерфейсом старой версии
 */
document.addEventListener('DOMContentLoaded', function () {
	// Инициализация аккордеонов
	initializeAccordions();

	// Инициализация поиска по фильтрам
	initializeFilterSearch();

	// Инициализация синхронизации поиска
	initializeSearchSync();

	// Инициализация обработчиков фильтра
	initializeFilterHandlers();
});

/**
 * Инициализация аккордеонов
 */
function initializeAccordions() {
	const accordionToggles = document.querySelectorAll('.catalog-accordion__toggle');

	accordionToggles.forEach(toggle => {
		toggle.addEventListener('click', function () {
			const accordion = this.closest('.catalog-accordion');
			const content = accordion.querySelector('.catalog-accordion__content');
			const arrow = accordion.querySelector('.icon-arrow');

			// Переключаем состояние
			const isOpen = accordion.classList.contains('is-open');

			if (isOpen) {
				accordion.classList.remove('is-open');
				content.style.maxHeight = '0';
				arrow.style.transform = 'rotate(0deg)';
			} else {
				accordion.classList.add('is-open');
				content.style.maxHeight = content.scrollHeight + 'px';
				arrow.style.transform = 'rotate(180deg)';
			}
		});
	});

	// Открываем аккордеон фильтров по умолчанию
	const filterAccordion = document.querySelector('.catalog-accordion');
	if (filterAccordion) {
		const content = filterAccordion.querySelector('.catalog-accordion__content');
		const arrow = filterAccordion.querySelector('.icon-arrow');

		// Принудительно открываем аккордеон
		filterAccordion.classList.add('is-open');
		if (content) {
			content.classList.add('is-visible');
			content.style.maxHeight = content.scrollHeight + 'px';
			content.style.display = 'flex';
		}
		if (arrow) {
			arrow.style.transform = 'rotate(180deg)';
		}

		console.log('Filter accordion opened by default');
	} else {
		console.log('Filter accordion not found');
	}
}

/**
 * Инициализация поиска по фильтрам
 */
function initializeFilterSearch() {
	const filterSearchInputs = document.querySelectorAll('.js-filter-search');

	filterSearchInputs.forEach(input => {
		input.addEventListener('input', function () {
			const searchQuery = this.value.toLowerCase();
			const optionsList = this.closest('.catalog-filter__group-content').querySelector('.filter-options');

			if (!optionsList) return;

			let visibleItems = 0;
			optionsList.querySelectorAll('.filter-options__item').forEach(item => {
				const label = item.querySelector('.checkbox-custom__text');
				if (label) {
					const match = label.textContent.toLowerCase().includes(searchQuery);

					if (match) {
						item.style.display = '';
						visibleItems++;
					} else {
						item.style.display = 'none';
					}
				}
			});
		});
	});
}

/**
 * Инициализация синхронизации поиска
 */
function initializeSearchSync() {
	const searchInputs = document.querySelectorAll('.js-catalog-search-input');

	if (searchInputs.length > 0) {
		let searchTimeout;

		searchInputs.forEach(input => {
			input.addEventListener('input', function () {
				const currentSearchValue = this.value;

				// Синхронизируем все поля поиска
				searchInputs.forEach(otherInput => {
					if (otherInput !== this) {
						otherInput.value = currentSearchValue;
					}
				});

				// Очищаем предыдущий таймаут
				clearTimeout(searchTimeout);

				// Устанавливаем новый таймаут для отправки запроса
				searchTimeout = setTimeout(() => {
					// Здесь можно добавить логику для отправки AJAX запроса
					console.log('Search query:', currentSearchValue);
				}, 300);
			});
		});
	}
}

/**
 * Инициализация обработчиков фильтра
 */
function initializeFilterHandlers() {
	const filterForm = document.querySelector('.js-filter-form');
	const sidebar = document.querySelector('.catalog-page__sidebar');

	if (filterForm) {
		// Обработчик отправки формы фильтра
		filterForm.addEventListener('submit', function (event) {
			event.preventDefault();

			// Собираем данные формы
			const formData = new FormData(this);
			const filterParams = new URLSearchParams();

			// Добавляем параметры фильтра
			for (let [key, value] of formData.entries()) {
				if (key !== 'set_filter') {
					filterParams.append(key, value);
				}
			}

			// Добавляем параметр для применения фильтра
			filterParams.append('set_filter', 'Y');

			// Перенаправляем на ту же страницу с параметрами фильтра
			const currentUrl = new URL(window.location);
			currentUrl.search = filterParams.toString();
			window.location.href = currentUrl.toString();
		});

		// Обработчик сброса фильтра
		const resetButton = filterForm.querySelector('.catalog-filter__reset-btn');
		if (resetButton) {
			resetButton.addEventListener('click', function (event) {
				event.preventDefault();

				// Сбрасываем форму
				filterForm.reset();

				// Показываем все элементы фильтра
				filterForm.querySelectorAll('.filter-options__item').forEach(item => {
					item.style.display = '';
				});

				// Очищаем поля поиска в фильтрах
				filterForm.querySelectorAll('.js-filter-search').forEach(input => {
					input.value = '';
				});

				// Перенаправляем на страницу без параметров фильтра
				const currentUrl = new URL(window.location);
				currentUrl.search = '';
				window.location.href = currentUrl.toString();
			});
		}
	}

	// // Обработчик вложенных аккордеонов в фильтрах
	// if (sidebar) {
	// 	sidebar.addEventListener('click', function (event) {
	// 		const filterToggleButton = event.target.closest('.catalog-filter__group-toggle');
	//
	// 		if (filterToggleButton) {
	// 			event.preventDefault();
	//
	// 			// Переключаем активное состояние
	// 			filterToggleButton.classList.toggle('is-active');
	//
	// 			// Находим контент аккордеона
	// 			const content = filterToggleButton.nextElementSibling;
	// 			const arrow = filterToggleButton.querySelector('.icon-arrow');
	//
	// 			if (content) {
	// 				if (content.style.maxHeight && content.style.maxHeight !== '0px') {
	// 					// Закрываем аккордеон
	// 					content.style.maxHeight = '0';
	// 					if (arrow) {
	// 						arrow.style.transform = 'rotate(0deg)';
	// 					}
	// 				} else {
	// 					// Открываем аккордеон
	// 					content.style.maxHeight = content.scrollHeight + 'px';
	// 					if (arrow) {
	// 						arrow.style.transform = 'rotate(180deg)';
	// 					}
	// 				}
	// 			}
	// 		}
	// 	});
	// }
}

/**
 * Плавный скролл к контенту
 */
function scrollToContent() {
	const contentArea = document.querySelector('.catalog-page__content');
	if (contentArea) {
		const topPosition = contentArea.getBoundingClientRect().top + window.pageYOffset;
		window.scrollTo({
			top: topPosition,
			behavior: 'smooth'
		});
	}
}

