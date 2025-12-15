// =================================================================================
// GIS MINING - CATALOG SCRIPT (catalog.js)
// Отвечает ТОЛЬКО за фильтрацию и поиск товаров
// =================================================================================

const catalogState = {
    filters: {},
    products: [],
    currentIblockId: null,
    currentFilters: {},
    isInitialized: false, // Флаг для отслеживания первой инициализации
};

// --- ОСНОВНАЯ ТОЧКА ВХОДА ---
document.addEventListener('DOMContentLoaded', async () => {
    const appRoot = document.getElementById('app-root');

    // Инициализируем переключение вкладок на всех страницах
    initializeProductTabs();

    // Скрипт будет работать только на страницах, где есть #app-root (т.е. в каталоге)
    if (!appRoot) return;

    catalogState.currentIblockId = appRoot.dataset.iblockId;
    if (!catalogState.currentIblockId) {
        console.error('Ошибка каталога: Не найден ID инфоблока!');
        return;
    }
    await initCatalog(appRoot);
});

async function initCatalog(rootElement) {
    // Инициализация аккордеонов при загрузке страницы
    initializeAccordions();

    // Инициализация вкладок описания товара/раздела
    initializeProductTabs();

    // Проверяем, что это страница ASICS (инфоблок 1)
    if (catalogState.currentIblockId === '1') {
        // Только для ASICS: настраиваем обработчики событий для стандартного фильтра Битрикса
        setupCatalogEventListeners(rootElement);
    }

    // Отмечаем, что инициализация завершена
    catalogState.isInitialized = true;
}

function initializeAccordions() {
    const accordionToggles = document.querySelectorAll('.catalog-accordion__toggle');

    accordionToggles.forEach((toggle) => {
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const accordion = this.closest('.catalog-accordion');
            const content = accordion ? accordion.querySelector('.catalog-accordion__content') : null;

            if (content) {
                const wasVisible = content.classList.contains('is-visible');
                content.classList.toggle('is-visible');
            }

            // Переключаем активное состояние кнопки
            this.classList.toggle('is-active');
        });
    });
}

// --- БЛОК 1: ИНИЦИАЛИЗАЦИЯ ---

/**
 * Инициализация переключения вкладок описания товара
 */
function initializeProductTabs() {
    // Проверяем, что мы на странице товара (есть секция about или catalog-about с вкладками)
    const aboutSection = document.querySelector('.about, .catalog-about');
    if (!aboutSection) return;

    const tabButtons = document.querySelectorAll('.js-about-tab');
    const tabContents = document.querySelectorAll('.js-tab-content');

    if (!tabButtons.length || !tabContents.length) return;

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetTab = this.dataset.tab;

            // Убираем активный класс со всех кнопок
            tabButtons.forEach(btn => btn.classList.remove('is-active'));
            // Добавляем активный класс к нажатой кнопке
            this.classList.add('is-active');

            // Скрываем все контенты
            tabContents.forEach(content => content.classList.remove('is-active'));
            // Показываем нужный контент
            const targetContent = document.querySelector(`.js-tab-content[data-tab="${targetTab}"]`);
            if (targetContent) {
                targetContent.classList.add('is-active');
            }
        });
    });
}

// --- БЛОК 3: ОБРАБОТЧИКИ СОБЫТИЙ ---
function setupCatalogEventListeners(rootElement) {
    const filterForm = rootElement.querySelector('.js-filter-form');
    const searchInput = rootElement.querySelector('.js-catalog-search-input');
    const sidebar = rootElement.querySelector('.catalog-page__sidebar');

    // --- ОБРАБОТЧИК 1: Аккордеоны и кнопки "Показать еще" ---
    rootElement.addEventListener('click', (event) => {
        // Логика для кнопок "Показать еще" в фильтрах
        const showMoreButton = event.target.closest('.js-show-more');
        if (showMoreButton) {
            event.preventDefault();
            const optionsList = showMoreButton.closest('.filter-options');
            const hiddenItems = optionsList.querySelectorAll('.filter-options__item.is-hidden');

            hiddenItems.forEach(item => {
                item.classList.remove('is-hidden');
            });

            showMoreButton.style.display = 'none';
        }

        // Логика для переключения аккордеонов фильтров
        const filterToggle = event.target.closest('.js-filter-group-toggle');
        if (filterToggle) {
            event.preventDefault();
            const contentWrapper = filterToggle.nextElementSibling;
            const arrow = filterToggle.querySelector('.icon-arrow');

            filterToggle.classList.toggle('is-active');

            if (contentWrapper) {
                if (contentWrapper.style.maxHeight && contentWrapper.style.maxHeight !== '0px') {
                    contentWrapper.style.maxHeight = '0';
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                } else {
                    contentWrapper.style.maxHeight = contentWrapper.scrollHeight + "px";
                    if (arrow) arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    });

    // --- ОБРАБОТЧИК 2: Поиск по пунктам фильтра ---
    // Вешаем один обработчик на весь сайдбар
    if (sidebar) {
        sidebar.addEventListener('input', (event) => {
            // Срабатывает только если ввод идет в поле с классом .js-filter-search
            if (event.target.classList.contains('js-filter-search')) {
                const searchQuery = event.target.value.toLowerCase();
                const optionsList = event.target.closest('.catalog-filter__group-content').querySelector('.filter-options');
                if (!optionsList) return;

                let visibleItems = 0;
                optionsList.querySelectorAll('.filter-options__item').forEach(item => {
                    const showMoreButton = item.querySelector('.js-show-more');
                    if (showMoreButton) return; // Пропускаем саму кнопку "показать еще"

                    const label = item.querySelector('.checkbox-custom__text');
                    if (label) {
                        const isVisible = item.classList.contains('is-hidden') === false;
                        const match = label.textContent.toLowerCase().includes(searchQuery);

                        if (isVisible && match) {
                            item.style.display = '';
                            visibleItems++;
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            }
        });
    }
}

/**
 * Рендерит стандартную карточку товара
 */
// function renderDefaultProductCard(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     const tagsHTML = `
//         ${product.PROPERTIES.HIT && product.PROPERTIES.HIT.VALUE ? '<span class="tag tag-hit">Хит</span>' : ''}
//         ${product.IS_AVAILABLE ? '<span class="tag tag-in-stock">В наличии</span>' : ''}
//     `;
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

/**
 * Рендерит карточку товара для готового бизнеса
 */
// function renderBusinessProductCard(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     // Убираем ссылку на страницу товара для готового бизнеса
//     // const detailUrl = `/catalog/product/${product.ID}/`;
//
//     // Формируем теги на основе свойств товара
//     const tags = [];
//     if (product.PROPERTIES.CRYPTO && product.PROPERTIES.CRYPTO.VALUE) {
//         const cryptoValues = Array.isArray(product.PROPERTIES.CRYPTO.VALUE)
//             ? product.PROPERTIES.CRYPTO.VALUE
//             : [product.PROPERTIES.CRYPTO.VALUE];
//         cryptoValues.forEach(crypto => {
//             tags.push(`<span class="tag tag--white">${crypto}</span>`);
//         });
//     }
//     const tagsHTML = tags.join('');
//
//     return `
//         <div class="product-card business-product-card"
//              data-product-id="${product.ID}"
//              data-name="${product.NAME}"
//              data-price="${product.PRINT_PRICE}"
//              data-price-raw="${product.PRICE_RAW}"
//              data-photo="${imageSrc}">
//              <div class="product-card__header">
//                 <div class="product-card__tags">${tagsHTML}</div>
//                 <div class="product-card__image-wrapper">
//                     <img class="product-card__image" src="${imageSrc}" alt="${product.NAME}" loading="lazy">
//                 </div>
//                 <div class="product-card__dots">
//                     <span class="product-card__dot product-card__dot--active"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                 </div>
//             </div>
//             <div class="product-card__info">
//                 <div class="product-card__name">${product.NAME}</div>
//                 <p class="product-card__costfrom">от ${product.PRINT_PRICE}</p>
//                 ${product.PROPERTIES.DEVICES_COUNT ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Количество устройств</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.DEVICES_COUNT.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.YEARLY_PROFIT ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Прибыль в год</p>
//                     <p class="product-card__numofdev-value">от ${product.PROPERTIES.YEARLY_PROFIT.VALUE} ₽</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.MONTHLY_INCOME ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Прибыль в мес</p>
//                     <p class="product-card__numofdev-value">от ${product.PROPERTIES.MONTHLY_INCOME.VALUE} ₽</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.PAYBACK_PERIOD ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Окупаемость</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.PAYBACK_PERIOD.VALUE}дн</p>
//                 </div>` : ''}
//             </div>
//             <div class="product-card__action">
//                 <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить КП</button>
//             </div>
//         </div>
//     `;
// }

/**
 * Рендерит карточку товара для размещения
 */
// function renderRazmeschenieProductCard(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     // Убираем ссылку на страницу товара для размещения
//     // const detailUrl = `/catalog/product/${product.ID}/`;
//
//     return `
//         <div class="product-card razmeschenie-product-card"
//              data-product-id="${product.ID}"
//              data-name="${product.NAME}"
//              data-price="${product.PRINT_PRICE}"
//              data-price-raw="${product.PRICE_RAW}"
//              data-photo="${imageSrc}">
//              <div class="product-card__header">
//                 <div class="product-card__tags">
//                     <span class="tag tag--white">Размещение</span>
//                 </div>
//                 <div class="product-card__image-wrapper">
//                     <img class="product-card__image" src="${imageSrc}" alt="${product.NAME}" loading="lazy">
//                 </div>
//                 <div class="product-card__dots">
//                     <span class="product-card__dot product-card__dot--active"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                 </div>
//             </div>
//             <div class="product-card__info">
//                 <div class="product-card__name">${product.NAME}</div>
//                 <p class="product-card__costfrom">от ${product.PRINT_PRICE}</p>
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Услуга размещения</p>
//                     <p class="product-card__numofdev-value">Детальная информация будет добавлена позже</p>
//                 </div>
//             </div>
//             <div class="product-card__action">
//                 <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить КП</button>
//             </div>
//         </div>
//     `;
// }

/**
 * Рендерит карточку товара для инвестиций
 */
// function renderInvesticiiProductCard(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     // Убираем ссылку на страницу товара для инвестиций
//     // const detailUrl = `/catalog/product/${product.ID}/`;
//
//     return `
//         <div class="product-card investicii-product-card"
//              data-product-id="${product.ID}"
//              data-name="${product.NAME}"
//              data-price="${product.PRINT_PRICE}"
//              data-price-raw="${product.PRICE_RAW}"
//              data-photo="${imageSrc}">
//              <div class="product-card__header">
//
//                 <div class="product-card__image-wrapper">
//                     <img class="product-card__image" src="${imageSrc}" alt="${product.NAME}" loading="lazy">
//                 </div>
//                 <div class="product-card__dots">
//                     <span class="product-card__dot product-card__dot--active"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                 </div>
//             </div>
//             <div class="product-card__info">
//                 <div class="product-card__name">${product.NAME}</div>
//                 <p class="product-card__costfrom">от ${product.PRINT_PRICE}</p>
//                 ${product.PROPERTIES.INVEST_COMPLETION_DATE ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Срок сдачи объекта</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.INVEST_COMPLETION_DATE.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.INVEST_TYPE ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Тип</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.INVEST_TYPE.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.INVEST_NUM_OF_DEVICES ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Количество устройств</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.INVEST_NUM_OF_DEVICES.VALUE}</p>
//                 </div>` : ''}
//             </div>
//             <div class="product-card__action">
//                 <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить консультацию</button>
//             </div>
//         </div>
//     `;
// }

/**
 * Рендерит карточку товара для GPU
 */
// function renderGpuProductCard(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     // Убираем ссылку на страницу товара для GPU
//     // const detailUrl = `/catalog/product/${product.ID}/`;
//
//     return `
//         <div class="product-card gpu-product-card"
//              data-product-id="${product.ID}"
//              data-name="${product.NAME}"
//              data-price="${product.PRINT_PRICE}"
//              data-price-raw="${product.PRICE_RAW}"
//              data-photo="${imageSrc}">
//              <div class="product-card__header">
//
//                 <div class="product-card__image-wrapper">
//                     <img class="product-card__image" src="${imageSrc}" alt="${product.NAME}" loading="lazy">
//                 </div>
//                 <div class="product-card__dots">
//                     <span class="product-card__dot product-card__dot--active"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                 </div>
//             </div>
//             <div class="product-card__info">
//                 <div class="product-card__name">${product.NAME}</div>
//                 <p class="product-card__costfrom">от ${product.PRINT_PRICE}</p>
//                 ${product.PROPERTIES.GPU_POWER ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Мощность</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.GPU_POWER.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.GPU_ENGINE ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Двигатель</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.GPU_ENGINE.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.GPU_COUNTRY_OF_ORIGIN ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Страна производства</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.GPU_COUNTRY_OF_ORIGIN.VALUE}</p>
//                 </div>` : ''}
//             </div>
//             <div class="product-card__action">
//                 <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить КП</button>
//             </div>
//         </div>
//     `;
// }

/**
 * Рендерит карточку товара для контейнеров
 */
// function renderConteyneryProductCard(product) {
//     const imageSrc = product.PREVIEW_PICTURE_SRC;
//     // Убираем ссылку на страницу товара для контейнеров
//     // const detailUrl = `/catalog/product/${product.ID}/`;
//
//     return `
//         <div class="product-card conteynery-product-card"
//              data-product-id="${product.ID}"
//              data-name="${product.NAME}"
//              data-price="${product.PRINT_PRICE}"
//              data-price-raw="${product.PRICE_RAW}"
//              data-photo="${imageSrc}">
//              <div class="product-card__header">
//
//                 <div class="product-card__image-wrapper">
//                     <img class="product-card__image" src="${imageSrc}" alt="${product.NAME}" loading="lazy">
//                 </div>
//                 <div class="product-card__dots">
//                     <span class="product-card__dot product-card__dot--active"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                     <span class="product-card__dot"></span>
//                 </div>
//             </div>
//             <div class="product-card__info">
//                 <div class="product-card__name">${product.NAME}</div>
//                 <p class="product-card__costfrom">от ${product.PRINT_PRICE}</p>
//                 ${product.PROPERTIES.CONTAINERS_NUMBER_OF_PLACES ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Количество мест</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.CONTAINERS_NUMBER_OF_PLACES.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.CONTAINERS_POWER ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Общая мощность, кВт</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.CONTAINERS_POWER.VALUE}</p>
//                 </div>` : ''}
//                 ${product.PROPERTIES.CONTAINERS_COOLING_SYSTEM ? `
//                 <div class="product-card__property-item">
//                     <p class="product-card__numofdev-name">Система охлаждения</p>
//                     <p class="product-card__numofdev-value">${product.PROPERTIES.CONTAINERS_COOLING_SYSTEM.VALUE}</p>
//                 </div>` : ''}
//             </div>
//             <div class="product-card__action">
//                 <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить КП</button>
//             </div>
//         </div>
//     `;
// }

