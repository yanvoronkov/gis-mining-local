// // =================================================================================
// // GIS MINING - CATALOG SCRIPT (script.js)
// // Упрощенная версия для МНОГОСТРАНИЧНОГО каталога
// // =================================================================================
//
// const state = {
//     filters: {},
//     products: [],
//     currentIblockId: null, // Получим из HTML
//     currentFilters: {},
// };
//
// // --- ОСНОВНАЯ ТОЧКА ВХОДА ---
// document.addEventListener('DOMContentLoaded', async () => {
//     const appRoot = document.getElementById('app-root');
//     if (!appRoot) return;
//
//     state.currentIblockId = appRoot.dataset.iblockId;
//     if (!state.currentIblockId) {
//         console.error('Ошибка: Не найден ID инфоблока на странице!');
//         return;
//     }
//
//     await initCatalog(appRoot);
// });
//
// async function initCatalog(rootElement) {
//     if (rootElement.querySelector('.js-filter-form')) {
//         await fetchFilters();
//         renderFilters();
//     }
//     await fetchProducts();
//     setupEventListeners(rootElement);
// }
//
// // --- БЛОК 1: ВЗАИМОДЕЙСТВИЕ С API (ПОЛУЧЕНИЕ ДАННЫХ) ---
//
// /**
//  * Загружает данные для фильтров для ТЕКУЩЕГО активного инфоблока.
//  */
// async function fetchFilters() {
//     try {
//         const response = await fetch(`/api/catalog.php?action=get_filters&iblock_id=${state.currentIblockId}`);
//         const data = await response.json();
//         state.filters = data.filters || {};
//     } catch (error) {
//         console.error("Ошибка при загрузке фильтров:", error);
//         // В случае ошибки, просто оставляем фильтры пустыми
//         state.filters = {};
//     }
// }
//
// /**
//  * Загружает список товаров с учетом ВСЕГО текущего состояния
//  * (инфоблок, страница, фильтры).
//  */
// async function fetchProducts() {
//     // Собираем URL для запроса на основе глобального состояния state
//     let apiUrl = `/api/catalog.php?action=get_products&iblock_id=${state.currentIblockId}`;
//
//     // Добавляем параметры из формы фильтров, если они есть
//     const filterParams = new URLSearchParams();
//
//     // --- НАЧАЛО ИЗМЕНЕНИЙ ---
//     // Добавляем поисковый запрос, если он есть
//     const searchInput = document.querySelector('.js-catalog-search-input');
//     if (searchInput && searchInput.value) {
//         filterParams.append('q', searchInput.value);
//     }
//     // --- КОНЕЦ ИЗМЕНЕНИЙ ---
//
//
//     for (const key in state.currentFilters) {
//         if (Array.isArray(state.currentFilters[key])) {
//             state.currentFilters[key].forEach(value => filterParams.append(`${key}[]`, value));
//         } else {
//             filterParams.append(key, state.currentFilters[key]);
//         }
//     }
//     if (filterParams.toString()) {
//         apiUrl += `&${filterParams.toString()}`;
//     }
//
//     try {
//         const productGrid = document.querySelector('.product-grid');
//         if (productGrid) {
//             productGrid.innerHTML = '<p>Загрузка товаров...</p>';
//         }
//
//         const response = await fetch(apiUrl);
//         const data = await response.json();
//
//         // Обновляем состояние новыми данными
//         state.products = data.products || [];
//
//         // Перерисовываем только сетку товаров
//         renderProductGrid();
//     } catch (error) {
//         console.error("Ошибка при загрузке товаров:", error);
//         const productGrid = document.querySelector('.product-grid');
//         if (productGrid) {
//             productGrid.innerHTML = '<p>Ошибка при загрузке товаров.</p>';
//         }
//     }
// }
//
// // --- БЛОК 2: РЕНДЕРИНГ ---
//
// /**
//  * Рендерит фильтры на основе данных из API
//  */
// function renderFilters() {
//     const filterContainer = document.querySelector('.js-filter-form');
//     if (!filterContainer) return;
//
//     const cryptoFilterHTML = renderFilterGroupHTML('Криптовалюта', 'CRYPTO', state.filters.CRYPTO || []);
//     const algorithmFilterHTML = renderFilterGroupHTML('Алгоритм', 'ALGORITHM', state.filters.ALGORITHM || []);
//
//     filterContainer.innerHTML = `
//         ${cryptoFilterHTML}${algorithmFilterHTML}`;
// }
//
// function renderFilterGroupHTML(title, code, options) {
//     if (!options || options.length === 0) return '';
//
//     const initialVisibleCount = 4;
//     const optionsHTML = options.map((opt, index) => {
//         const hiddenClass = index >= initialVisibleCount ? 'is-hidden' : '';
//         return `
//             <li class="filter-options__item ${hiddenClass}">
//                 <label class="checkbox-custom">
//                     <input type="checkbox" class="checkbox-custom__input" name="${code}" value="${opt.ID}">
//                     <span class="checkbox-custom__box"></span>
//                     <span class="checkbox-custom__text">${opt.VALUE}</span>
//                 </label>
//             </li>
//         `;
//     }).join('');
//
//     const showMoreButtonHTML = options.length > initialVisibleCount
//         ? `<li class="filter-options__item"><a href="#" class="filter-options__show-more js-show-more">Показать еще ${options.length - initialVisibleCount}</a></li>`
//         : '';
//
//     return `
//         <div class="catalog-filter__group">
//             <button type="button" class="catalog-filter__group-toggle js-filter-group-toggle">
//                 <span>${title}</span>
//                 <svg class="icon-arrow" width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
//                     <path d="M9 0.5L5 4.5L1 0.5" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round"/>
//                 </svg>
//             </button>
//             <div class="catalog-filter__group-content" style="max-height: fit-content;">
//                 <ul class="filter-options">
//                     ${optionsHTML}
//                     ${showMoreButtonHTML}
//                 </ul>
//             </div>
//         </div>
//     `;
// }
//
// /**
//  * Рендерит сетку товаров
//  */
// function renderProductGrid() {
//     const productGrid = document.querySelector('.product-grid');
//     if (!productGrid) return;
//
//     if (state.products && state.products.length > 0) {
//         productGrid.innerHTML = state.products.map(renderProductCardHTML).join('');
//     } else {
//         productGrid.innerHTML = '<p>В этой категории товары не найдены.</p>';
//     }
//
//     if (window.appCart) {
//         window.appCart.updateCardButtonsState();
//     }
// }
//
// function renderProductCardHTML(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     const detailUrl = `/catalog/product/${product.ID}/`;
//     const tagsHTML = `
//         ${product.PROPERTIES.HIT && product.PROPERTIES.HIT.VALUE ? '<span class="tag tag-hit">Хит</span>' : ''}
//         ${product.IS_AVAILABLE ? '<span class="tag tag-in-stock">В наличии</span>' : '<span class="tag tag-in-stock">Предзаказ</span>'}
//     `;
//
//     return `
//         <div class="product-card" 
//              data-product-id="${product.ID}" 
//              data-name="${product.NAME}" 
//              data-price="${product.PRINT_PRICE}" 
//              data-price-raw="${product.PRICE_RAW}"
//              data-photo="${imageSrc}"> 
//              <div class="product-card__header">
//                 <div class="product-card__tags">${tagsHTML}</div>
//                 <div class="product-card__image-wrapper">
//                     <img src="${imageSrc}" alt="${product.NAME}" class="product-card__image">
//                 </div>
//                 <div class="product-card__dots">
//                     <span class="product-card__dot product-card__dot--active"></span><span class="product-card__dot"></span><span class="product-card__dot"></span><span class="product-card__dot"></span>
//                 </div>
//                 <div class="product-card__info">
//                     <div class="product-card__name">${product.NAME}</div>
//                 </div>
//             </div>
//             <div class="product-card__footer">
//                     <p class="product-card__price">${product.PRINT_PRICE}</p>
//                     <div class="product-card__actions">
//                         <div class="quantity-selector">
//                             <button type="button" class="quantity-selector__btn quantity-selector__btn--minus js-quantity-minus" aria-label="Уменьшить количество"><svg width="10" height="2"><line x1="0.5" y1="1" x2="9.5" y2="1" stroke="black" stroke-linecap="round"/></svg></button>
//                             <p class="js-quantity-value">1</p>
//                             <button type="button" class="quantity-selector__btn quantity-selector__btn--plus js-quantity-plus" aria-label="Увеличить количество"><svg width="12" height="11"><path d="M6 0.5V10.5" stroke="black" stroke-linecap="round"/><path d="M1 5.5L11 5.5" stroke="black" stroke-linecap="round"/></svg></button>
//                         </div>
//                         <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Заказать</button>
//                     </div>
//             </div>
//         </div>
//     `;
// }
//
// // --- БЛОК 3: ОБРАБОТЧИКИ СОБЫТИЙ ---
//
// function setupEventListeners(rootElement) {
//     const filterForm = rootElement.querySelector('.js-filter-form');
//     const searchInput = rootElement.querySelector('.js-catalog-search-input');
//     const sidebar = rootElement.querySelector('.catalog-page__sidebar');
//
//     // --- ОБРАБОТЧИК 1: Фильтры ---
//     if (filterForm) {
//         filterForm.addEventListener('change', async (event) => {
//             if (event.target.type === 'checkbox') {
//                 const filterName = event.target.name;
//                 const filterValue = event.target.value;
//                 const isChecked = event.target.checked;
//
//                 if (!state.currentFilters[filterName]) {
//                     state.currentFilters[filterName] = [];
//                 }
//
//                 if (isChecked) {
//                     if (!state.currentFilters[filterName].includes(filterValue)) {
//                         state.currentFilters[filterName].push(filterValue);
//                     }
//                 } else {
//                     state.currentFilters[filterName] = state.currentFilters[filterName].filter(v => v !== filterValue);
//                     if (state.currentFilters[filterName].length === 0) {
//                         delete state.currentFilters[filterName];
//                     }
//                 }
//
//                 await fetchProducts();
//             }
//         });
//     }
//
//     // --- ОБРАБОТЧИК 2: Поиск ---
//     if (searchInput) {
//         let searchTimeout;
//         searchInput.addEventListener('input', async (event) => {
//             clearTimeout(searchTimeout);
//             searchTimeout = setTimeout(async () => {
//                 await fetchProducts();
//             }, 500);
//         });
//     }
//
//     // --- ОБРАБОТЧИК 3: Аккордеоны и кнопки "Показать еще" ---
//     rootElement.addEventListener('click', (event) => {
//         // Логика для кнопок "Показать еще" в фильтрах
//         const showMoreButton = event.target.closest('.js-show-more');
//         if (showMoreButton) {
//             event.preventDefault();
//             const optionsList = showMoreButton.closest('.filter-options');
//             const hiddenItems = optionsList.querySelectorAll('.filter-options__item.is-hidden');
//             
//             hiddenItems.forEach(item => {
//                 item.classList.remove('is-hidden');
//             });
//             
//             showMoreButton.style.display = 'none';
//         }
//
//         // Логика для переключения аккордеонов фильтров
//         const filterToggle = event.target.closest('.js-filter-group-toggle');
//         if (filterToggle) {
//             event.preventDefault();
//             const contentWrapper = filterToggle.nextElementSibling;
//             const arrow = filterToggle.querySelector('.icon-arrow');
//             
//             filterToggle.classList.toggle('is-active');
//             
//             if (contentWrapper) {
//                 if (contentWrapper.style.maxHeight && contentWrapper.style.maxHeight !== '0px') {
//                     contentWrapper.style.maxHeight = '0';
//                     if (arrow) arrow.style.transform = 'rotate(0deg)';
//                 } else {
//                     contentWrapper.style.maxHeight = contentWrapper.scrollHeight + "px";
//                     if (arrow) arrow.style.transform = 'rotate(180deg)';
//                 }
//             }
//         }
//     });
//
//     // --- ОБРАБОТЧИК 4: Поиск по пунктам фильтра ---
//     if (sidebar) {
//         sidebar.addEventListener('input', (event) => {
//             if (event.target.classList.contains('js-filter-search')) {
//                 const searchQuery = event.target.value.toLowerCase();
//                 const optionsList = event.target.closest('.catalog-filter__group-content').querySelector('.filter-options');
//                 if (!optionsList) return;
//
//                 let visibleItems = 0;
//                 optionsList.querySelectorAll('.filter-options__item').forEach(item => {
//                     const showMoreButton = item.querySelector('.js-show-more');
//                     if (showMoreButton) return;
//
//                     const label = item.querySelector('.checkbox-custom__text');
//                     if (label) {
//                         const isVisible = item.classList.contains('is-hidden') === false;
//                         const match = label.textContent.toLowerCase().includes(searchQuery);
//
//                         if (isVisible && match) {
//                             item.style.display = '';
//                             visibleItems++;
//                         } else {
//                             item.style.display = 'none';
//                         }
//                     }
//                 });
//             }
//         });
//     }
// }

// =================================================================================
// НАВИГАЦИЯ ПО ЗАГОЛОВКАМ СТРАНИЦЫ
// =================================================================================

// Ждем полной загрузки страницы
window.addEventListener('load', function () {
	// Небольшая задержка для гарантии полной загрузки
	setTimeout(initPageNavigation, 100);
});

function initPageNavigation() {
	const navigationLinks = document.querySelectorAll('.page-blog-detail__navigation-link');
	const headings = document.querySelectorAll('h2[id], h3[id], h4[id]');

	if (!navigationLinks.length || !headings.length) {
		return;
	}

	// Функция для получения текущей высоты header
	function getHeaderHeight() {
		const header = document.querySelector('.header__container');
		if (header) {
			return header.offsetHeight;
		}
		// Fallback значения в зависимости от размера экрана
		if (window.innerWidth >= 1200) return 100;
		if (window.innerWidth >= 768) return 70;
		return 60;
	}

	// Добавляем обработчики для плавной прокрутки
	navigationLinks.forEach((link) => {
		link.addEventListener('click', function (e) {
			e.preventDefault();

			const targetId = this.getAttribute('href').substring(1);
			const targetElement = document.getElementById(targetId);

			if (targetElement) {
				try {
					// Плавная прокрутка к заголовку
					targetElement.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});

					// Обновляем URL без перезагрузки страницы
					history.pushState(null, null, '#' + targetId);
				} catch (error) {
					// Fallback: обычная прокрутка с учетом высоты header
					const headerHeight = getHeaderHeight();
					const targetPosition = targetElement.offsetTop - headerHeight;

					window.scrollTo({
						top: targetPosition,
						behavior: 'smooth'
					});
				}
			}
		});
	});

	// Функция для определения активного заголовка при прокрутке
	function updateActiveNavigation() {
		const headerHeight = getHeaderHeight();
		const scrollPosition = window.scrollY + headerHeight;

		let activeHeading = null;
		let minDistance = Infinity;

		headings.forEach(heading => {
			const rect = heading.getBoundingClientRect();
			const distance = Math.abs(rect.top);

			if (distance < minDistance && rect.top <= headerHeight) {
				minDistance = distance;
				activeHeading = heading;
			}
		});

		// Убираем активный класс у всех ссылок
		navigationLinks.forEach(link => {
			link.classList.remove('is-active');
		});

		// Добавляем активный класс к соответствующей ссылке
		if (activeHeading) {
			const activeId = activeHeading.id;
			const activeLink = document.querySelector(`.page-blog-detail__navigation-link[href="#${activeId}"]`);
			if (activeLink) {
				activeLink.classList.add('is-active');
			}
		}
	}

	// Обработчик прокрутки с throttling для производительности
	let ticking = false;
	function requestTick() {
		if (!ticking) {
			requestAnimationFrame(updateActiveNavigation);
			ticking = true;
		}
	}

	function handleScroll() {
		ticking = false;
		requestTick();
	}

	// Добавляем обработчик прокрутки
	window.addEventListener('scroll', handleScroll, { passive: true });

	// Инициализируем активное состояние при загрузке страницы
	updateActiveNavigation();

	// Обработчик изменения размера окна
	let resizeTimeout;
	window.addEventListener('resize', function () {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(() => {
			updateActiveNavigation();
		}, 100);
	});

}

// =================================================================================
// УНИВЕРСАЛЬНАЯ ФУНКЦИЯ ПЕРЕКЛЮЧЕНИЯ ВКЛАДОК
// =================================================================================

/**
 * Универсальная функция для переключения вкладок
 * @param {string} tabName - Название вкладки (specifications, reviews, etc.)
 * @param {boolean} scrollToSection - Нужно ли прокручивать к секции
 */
function switchToTab(tabName, scrollToSection = true) {
	console.log(`Переключение на вкладку: ${tabName}`);

	// Убираем активный класс со всех вкладок
	const allTabs = document.querySelectorAll('.js-about-tab');
	allTabs.forEach(tab => {
		tab.classList.remove('is-active');
	});

	// Убираем активный класс со всех контентов
	const allContents = document.querySelectorAll('.js-tab-content');
	allContents.forEach(content => {
		content.classList.remove('is-active');
	});

	// Активируем нужную вкладку
	const targetTab = document.querySelector(`[data-tab="${tabName}"]`);
	if (targetTab) {
		targetTab.classList.add('is-active');

		// Обновляем заголовок
		const aboutTitle = document.querySelector('.about__title');
		if (aboutTitle) {
			const buttonText = targetTab.textContent.trim();
			aboutTitle.textContent = buttonText;
		}
	}

	// Активируем соответствующий контент
	const targetContent = document.querySelector(`.js-tab-content[data-tab="${tabName}"]`);
	if (targetContent) {
		targetContent.classList.add('is-active');
	}

	// Прокручиваем к секции, если нужно
	if (scrollToSection) {
		const catalogAboutSection = document.querySelector('.catalog-about');
		if (catalogAboutSection) {
			// Получаем высоту header для корректной прокрутки
			const header = document.querySelector('.header__container');
			const headerHeight = header ? header.offsetHeight : 100;

			// Прокручиваем к блоку catalog-about с учетом высоты header
			const targetPosition = catalogAboutSection.offsetTop - headerHeight;
			window.scrollTo({
				top: targetPosition,
				behavior: 'smooth'
			});
		}
	}
}

// =================================================================================
// ОБРАБОТЧИКИ ДЛЯ КНОПОК ПЕРЕКЛЮЧЕНИЯ ВКЛАДОК
// =================================================================================

document.addEventListener('DOMContentLoaded', function () {
	console.log('DOM загружен, инициализируем обработчики вкладок...');

	// Обработчик для кнопки "Больше характеристик"
	const moreSpecsBtn = document.querySelector('.js-more-specs-btn');
	if (moreSpecsBtn) {
		console.log('Найдена кнопка "Больше характеристик"');
		moreSpecsBtn.addEventListener('click', function (e) {
			console.log('Клик по кнопке "Больше характеристик"');
			e.preventDefault();
			switchToTab('specifications', true);
		});
	}

	// Обработчик для кнопки отзывов
	const reviewLink = document.querySelector('.card-info__reviews');
	if (reviewLink) {
		console.log('Найдена кнопка отзывов');
		reviewLink.addEventListener('click', function (e) {
			console.log('Клик по кнопке отзывов');
			e.preventDefault();
			switchToTab('reviews', true);
		});
	}
});

// =================================================================================
// ПОЛНОЭКРАННЫЙ СЛАЙДЕР ГАЛЕРЕИ (интегрированный с NativeSlider)
// =================================================================================

// =================================================================================
// ПОЛНОЭКРАННЫЙ СЛАЙДЕР ГАЛЕРЕИ (Универсальный)
// =================================================================================

document.addEventListener('DOMContentLoaded', function () {
	const fullscreenGallery = document.getElementById('fullscreen-gallery');
	if (!fullscreenGallery) return;

	const closeBtn = fullscreenGallery.querySelector('.fullscreen-gallery__close');
	const prevBtn = fullscreenGallery.querySelector('.fullscreen-gallery__nav--prev');
	const nextBtn = fullscreenGallery.querySelector('.fullscreen-gallery__nav--next');
	const overlay = fullscreenGallery.querySelector('.fullscreen-gallery__overlay');
	const image = fullscreenGallery.querySelector('.fullscreen-gallery__image');
	const currentCounter = fullscreenGallery.querySelector('.fullscreen-gallery__current');
	const totalCounter = fullscreenGallery.querySelector('.fullscreen-gallery__total');

	let currentIndex = 0;
	let galleryData = [];
	let nativeSliderInstance = null;

	// Состояние свайпов для полноэкранного режима
	let fsTouchStartX = null;
	let fsIsTouching = false;
	const fsSwipeThreshold = 20; // px

	// Функция для обновления изображения
	function updateImage(index) {
		if (!image || !galleryData[index]) return;

		const imageData = galleryData[index];
		image.src = imageData.src;
		image.alt = imageData.alt || '';
		image.title = imageData.title || '';

		// Показываем изображение (убираем display: none)
		image.style.display = 'block';

		// Обновляем счетчик
		if (currentCounter) {
			currentCounter.textContent = index + 1;
		}
		if (totalCounter) {
			totalCounter.textContent = galleryData.length;
		}

		// Обновляем состояние кнопок навигации
		const atStart = index === 0;
		const atEnd = index === galleryData.length - 1;
		if (prevBtn) {
			prevBtn.disabled = atStart;
			prevBtn.style.display = atStart ? 'none' : '';
		}
		if (nextBtn) {
			nextBtn.disabled = atEnd;
			nextBtn.style.display = atEnd ? 'none' : '';
		}
	}

	// Функция для открытия галереи
	function openGallery(items, index = 0) {
		galleryData = items;
		currentIndex = index;
		updateImage(currentIndex);
		fullscreenGallery.classList.add('is-active');
		document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
	}

	// Функция для закрытия галереи
	function closeGallery() {
		fullscreenGallery.classList.remove('is-active');
		document.body.style.overflow = ''; // Восстанавливаем прокрутку страницы
		galleryData = []; // Очищаем данные
	}

	// Функция для перехода к предыдущему изображению
	function goToPrevious() {
		if (currentIndex > 0) {
			currentIndex--;
			updateImage(currentIndex);
		}
	}

	// Функция для перехода к следующему изображению
	function goToNext() {
		if (currentIndex < galleryData.length - 1) {
			currentIndex++;
			updateImage(currentIndex);
		}
	}

	// Обработчики событий для кнопок управления
	if (closeBtn) closeBtn.addEventListener('click', closeGallery);
	if (prevBtn) prevBtn.addEventListener('click', goToPrevious);
	if (nextBtn) nextBtn.addEventListener('click', goToNext);
	if (overlay) overlay.addEventListener('click', closeGallery);

	// Свайпы
	const fsTouchTarget = fullscreenGallery.querySelector('.fullscreen-gallery__content') || fullscreenGallery;
	if (fsTouchTarget) {
		fsTouchTarget.addEventListener('touchstart', (e) => {
			if (!e.touches || e.touches.length === 0) return;
			fsIsTouching = true;
			fsTouchStartX = e.touches[0].clientX;
		}, { passive: true });

		fsTouchTarget.addEventListener('touchmove', (e) => {
			if (!fsIsTouching) return;
			e.preventDefault();
		}, { passive: false });

		fsTouchTarget.addEventListener('touchend', (e) => {
			if (!fsIsTouching) return;
			fsIsTouching = false;
			const touch = e.changedTouches && e.changedTouches[0];
			if (!touch || fsTouchStartX === null) return;

			const deltaX = touch.clientX - fsTouchStartX;
			if (Math.abs(deltaX) > fsSwipeThreshold) {
				if (deltaX < 0) {
					goToNext();
				} else {
					goToPrevious();
				}
			}
			fsTouchStartX = null;
		}, { passive: true });
	}

	// Клавиатурная навигация
	document.addEventListener('keydown', function (e) {
		if (!fullscreenGallery.classList.contains('is-active')) {
			return;
		}
		switch (e.key) {
			case 'Escape': closeGallery(); break;
			case 'ArrowLeft': goToPrevious(); break;
			case 'ArrowRight': goToNext(); break;
		}
	});

	// Предотвращение закрытия при клике на само изображение
	if (image) {
		image.addEventListener('click', function (e) {
			e.stopPropagation();
		});
	}

	// ГЛОБАЛЬНЫЙ ОБРАБОТЧИК КЛИКОВ (Делегирование)
	document.addEventListener('click', function (e) {
		const galleryItem = e.target.closest('.js-gallery-item');
		if (!galleryItem) return;

		e.preventDefault();

		const wrapper = galleryItem.closest('.js-gallery-wrapper');
		if (!wrapper) {
			console.warn('Gallery item clicked, but no .js-gallery-wrapper found.');
			return;
		}

		// Собираем все элементы галереи в этом контейнере
		const items = Array.from(wrapper.querySelectorAll('.js-gallery-item'));

		// Формируем данные для галереи
		const galleryItemsData = items.map(item => {
			// Если это img, берем src. Если ссылка или другой элемент - ищем data-full-src или href
			let src = item.getAttribute('data-full-src');
			if (!src) {
				if (item.tagName.toLowerCase() === 'img') {
					src = item.src;
				} else if (item.tagName.toLowerCase() === 'a') {
					src = item.href;
				} else {
					// Пытаемся найти img внутри
					const img = item.querySelector('img');
					if (img) src = img.src;
				}
			}

			let alt = item.getAttribute('alt') || item.getAttribute('title') || '';
			if (!alt && item.tagName.toLowerCase() === 'img') alt = item.alt;
			if (!alt) {
				const img = item.querySelector('img');
				if (img) alt = img.alt;
			}

			return { src, alt };
		});

		// Определяем индекс нажатого элемента
		const clickedIndex = items.indexOf(galleryItem);

		if (galleryItemsData.length > 0) {
			openGallery(galleryItemsData, clickedIndex);
		}
	});

	// Экспорт API (на всякий случай)
	window.fullscreenGallery = {
		open: openGallery,
		close: closeGallery
	};
});

document.addEventListener("DOMContentLoaded", function () {

    const labels = document.querySelectorAll("form label");

    labels.forEach(label => {
        const input = label.nextElementSibling;

        // Проверяем структуру (label → input)
        if (!input || input.tagName !== "INPUT") return;

        // Убираем placeholder (обязательно для floating label)
        input.dataset.originalPlaceholder = input.placeholder;
        input.placeholder = " ";

        // Создаем wrapper
        const wrapper = document.createElement("div");
        wrapper.classList.add("floating-wrapper");

        // Вставляем wrapper, переносим элементы
        label.parentNode.insertBefore(wrapper, label);
        wrapper.appendChild(label);
        wrapper.appendChild(input);

        // Главная функция — определяет, поднимать ли лейбл
        const toggle = () => {
            // Забираем только цифры
            const digits = input.value.replace(/\D/g, "");

            let hasRealInput = false;

            // Если это телефон → игнорируем "+7", которое IMask ставит сам
            if (input.classList.contains("js-phone-mask")) {
                // Если только "7" → пользователь ещё ничего не вводил
                hasRealInput = digits.length > 1;
            } else {
                // Для обычных полей
                hasRealInput = digits.length > 0 || input.value.trim() !== "";
            }

            // Floating label логика
            if (document.activeElement === input || hasRealInput) {
                wrapper.classList.add("active");
                wrapper.classList.remove("empty-mask");
            } else {
                wrapper.classList.remove("active");
                wrapper.classList.add("empty-mask");
            }
        };

        // Навешиваем события
        input.addEventListener("focus", toggle);
        input.addEventListener("blur", toggle);
        input.addEventListener("input", toggle);

        // Даем IMask время применить маску, затем проверяем состояние
        setTimeout(toggle, 50);
    });

});







