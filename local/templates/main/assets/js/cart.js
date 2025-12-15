// =================================================================================
// GIS MINING - CART SCRIPT (cart.js)
// Финальная самодостаточная версия.
// Управляет данными, кнопками, своим модальным окном и его видимостью.
// =================================================================================

class Cart {
    constructor() {
        this.storageKey = 'gisMiningCart';
        this.items = this.load();

        // Свойства для хранения ссылок на HTML-элементы
        this.modal = null;
        this.badge = null;
        this.cartIcons = null; // Теперь это коллекция
        this.closeButton = null;
        this.itemsContainer = null;
        this.summaryContainer = null;
        this.form = null;
        this.hiddenItemsInput = null;

        // Инициализация после загрузки DOM
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    /**
     * Инициализация: находит все нужные элементы и навешивает обработчики.
     */
    init() {
        this.badge = document.querySelector('.js-cart-badge');
        this.cartIcons = document.querySelectorAll('.js-open-cart');
        this.modal = document.querySelector('.js-cart-modal');

        if (this.modal) {
            this.itemsContainer = this.modal.querySelector('.js-cart-items-container');
            this.summaryContainer = this.modal.querySelector('.js-cart-summary');
            this.form = this.modal.querySelector('form');
            this.closeButton = this.modal.querySelector('.js-close-cart-modal, .popup-form__close-btn');
            this.hiddenItemsInput = this.modal.querySelector('input[name="cart_items"]');
        }

        this.setupEventListeners();
        this.trackUtmTags();

        this.updateBadgeAndIconVisibility();

        // Обновляем состояние кнопок только для страниц с AJAX-функциональностью (ASICS)
        const appRoot = document.getElementById('app-root');
        if (appRoot && appRoot.dataset.iblockId === '1') {
            this.updateCardButtonsState();
        }

        console.log('Модуль корзины (cart.js) успешно инициализирован.');
    }

    /**
     * Устанавливает все обработчики событий.
     */
    setupEventListeners() {
        // --- Обработчики для модального окна ---
        this.cartIcons.forEach(btn => btn.addEventListener('click', e => { e.preventDefault(); this.open(); }));
        this.closeButton?.addEventListener('click', () => this.close());
        this.modal?.addEventListener('click', e => { if (e.target === this.modal) this.close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && this.modal && this.modal.classList.contains('is-open')) this.close(); });

        // Обработчик для кнопки "Очистить корзину"
        document.addEventListener('click', (event) => {
            if (event.target.closest('.js-clear-cart')) {
                event.preventDefault();
                this.clearCart();
            }
        });

        // Удаляю обработчик submit формы корзины из cart.js
        // this.form?.addEventListener('submit', e => this.submitOrder(e));

        // --- Главный обработчик для карточек товаров и кликов в корзине ---
        document.body.addEventListener('click', (event) => {
            // Логика для кнопок ВНУТРИ модального окна
            if (event.target.closest('.js-cart-modal')) {
                const cartItem = event.target.closest('.cart-item');
                if (!cartItem) return;
                const productId = cartItem.dataset.productId;
                if (event.target.closest('.js-cart-item-increase')) this.updateQuantity(productId, 1);
                if (event.target.closest('.js-cart-item-decrease')) this.updateQuantity(productId, -1);
                if (event.target.closest('.js-cart-item-remove')) this.removeFromCart(productId);
                return;
            }

            // Логика для карточек товаров НА СТРАНИЦЕ
            const card = event.target.closest('.product-card');
            if (!card) return;

            const productId = card.dataset.productId;
            if (!productId) return;

            // Предотвращаем переход по ссылке при клике на интерактивные элементы
            if (event.target.closest('.js-quantity-plus') ||
                event.target.closest('.js-quantity-minus') ||
                event.target.closest('.js-quantity-value') ||
                event.target.closest('.js-add-to-cart')) {
                event.preventDefault();
                event.stopPropagation();
            }

            const quantityValueEl = card.querySelector('.js-quantity-value');
            if (!quantityValueEl) return;

            let currentQuantity = parseInt(quantityValueEl.value || quantityValueEl.textContent, 10);
            const isInCart = this.items.some(item => item.id == productId);

            if (event.target.closest('.js-quantity-plus')) {
                currentQuantity++;
                if (quantityValueEl.tagName === 'INPUT') {
                    quantityValueEl.value = currentQuantity;
                } else {
                    quantityValueEl.textContent = currentQuantity;
                }
                if (isInCart) this.updateQuantityInCartOnly(productId, currentQuantity);
            } else if (event.target.closest('.js-quantity-minus')) {
                if (currentQuantity > 1) {
                    currentQuantity--;
                    if (quantityValueEl.tagName === 'INPUT') {
                        quantityValueEl.value = currentQuantity;
                    } else {
                        quantityValueEl.textContent = currentQuantity;
                    }
                    if (isInCart) this.updateQuantityInCartOnly(productId, currentQuantity);
                }
            } else if (event.target.closest('.js-add-to-cart')) {
                const orderButton = event.target.closest('.js-add-to-cart');
                if (orderButton.classList.contains('is-added')) {
                    this.open();
                } else {
                    const productData = {
                        id: card.dataset.productId,
                        name: card.dataset.name,
                        price: card.dataset.price,
                        priceRaw: parseFloat(card.dataset.priceRaw) || 0,
                        photo: card.dataset.photo,
                        quantity: currentQuantity
                    };
                    this.addToCart(productData);
                    this.setButtonStateAdded(orderButton);
                    this.open();
                }
            }
        });

        // Обработчики для ручного ввода количества товара
        document.body.addEventListener('input', (event) => {
            const quantityInput = event.target.closest('.js-quantity-value');
            if (!quantityInput || quantityInput.tagName !== 'INPUT') return;

            const card = quantityInput.closest('.product-card');
            if (!card) return;

            const productId = card.dataset.productId;
            if (!productId) return;

            let newQuantity = parseInt(quantityInput.value, 10);

            // Валидация введенного значения
            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
            } else if (newQuantity > 999) {
                newQuantity = 999;
            }

            quantityInput.value = newQuantity;

            // Обновляем корзину, если товар уже в ней
            const isInCart = this.items.some(item => item.id == productId);
            if (isInCart) {
                this.updateQuantityInCartOnly(productId, newQuantity);
            }
        });

        // Обработчик для события blur (потеря фокуса) - финальная валидация
        document.body.addEventListener('blur', (event) => {
            const quantityInput = event.target.closest('.js-quantity-value');
            if (!quantityInput || quantityInput.tagName !== 'INPUT') return;

            let newQuantity = parseInt(quantityInput.value, 10);

            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
                quantityInput.value = newQuantity;
            }
        }, true);

        // Удаляю дублирующий обработчик submit формы корзины
        // if (this.form) {
        //     this.form.addEventListener('submit', (e) => {
        //         e.preventDefault(); // Отменяем стандартное поведение
        //         this.submitOrder(); // Вызываем наш собственный метод отправки
        //     });
        // }
    }

    open() {
        if (!this.modal) return;
        this.render();
        this.modal.style.display = 'flex';
        setTimeout(() => this.modal.classList.add('is-visible'), 10);
        // Убираем блокировку скролла, так как теперь это управляется через form-actions.js
        // document.body.classList.add('no-scroll');
    }

    close() {
        if (!this.modal) return;
        this.modal.classList.remove('is-visible');
        setTimeout(() => { this.modal.style.display = 'none'; }, 300);
        // Убираем восстановление скролла, так как теперь это управляется через form-actions.js
        // document.body.classList.remove('no-scroll');
    }

    addToCart(product) {
        const existingItem = this.items.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity = product.quantity;
        } else {
            this.items.push(product);
        }
        this.save();
        this.updateBadgeAndIconVisibility();
    }

    updateQuantity(productId, amount) {
        const item = this.items.find(item => item.id == productId);
        if (item) {
            const newQuantity = item.quantity + amount;
            if (newQuantity > 0) {
                item.quantity = newQuantity;
            } else {
                this.removeFromCart(productId);
                return;
            }
        }
        this.save();
        this.render();
        this.updateBadgeAndIconVisibility();
    }

    removeFromCart(productId) {
        this.items = this.items.filter(item => item.id != productId);
        this.save();
        this.render();
        this.updateBadgeAndIconVisibility();

        // Обновляем состояние кнопок только для страниц с AJAX-функциональностью (ASICS)
        const appRoot = document.getElementById('app-root');
        if (appRoot && appRoot.dataset.iblockId === '1') {
            this.updateCardButtonsState();
        }
    }

    render() {
        if (!this.itemsContainer || !this.summaryContainer) return;
        const form = this.modal.querySelector('form');
        const submitButton = form.querySelector('button[type="submit"]');

        if (this.items.length > 0) {
            // Проверяем, нужно ли создавать новые элементы или обновлять существующие
            const existingItems = this.itemsContainer.querySelectorAll('.cart-item');
            const needsFullRender = existingItems.length !== this.items.length;

            if (needsFullRender) {
                // Полная перерисовка только при изменении количества товаров
                this.renderFullItems();
            } else {
                // Обновляем только цены и количества существующих товаров
                this.updateExistingItems();
            }

            // Обновляем итоговую строку
            this.updateSummary();

            if (submitButton) submitButton.disabled = false;
        } else {
            this.itemsContainer.innerHTML = '<p>Ваша корзина пуста.</p>';
            this.summaryContainer.innerHTML = '';
            if (submitButton) submitButton.disabled = true;
        }

        if (this.hiddenItemsInput) {
            const cartItemsForCRM = this.items.map(item => ({ name: item.name, quantity: item.quantity, price: item.price }));
            this.hiddenItemsInput.value = JSON.stringify(cartItemsForCRM);
        }
    }

    renderFullItems() {
        const itemsHTML = this.items.map(item => {
            const hasPrice = item.priceRaw && parseFloat(item.priceRaw) > 0;
            const itemTotalPrice = hasPrice ? parseFloat(item.priceRaw) * item.quantity : 0;
            const itemTotalPriceFormatted = hasPrice ? new Intl.NumberFormat('ru-RU').format(itemTotalPrice) : '';

            return `
                <div class="cart-item" data-product-id="${item.id}">
                    <img src="${item.photo}" alt="${item.name}" class="cart-item__image">
                    <div class="cart-item__wrap">
                        <div class="cart-item__details">
                            <p class="cart-item__name">${item.name}</p>
                            <strong class="cart-item__price">
                                ${hasPrice ? `${itemTotalPriceFormatted} ₽` : (item.price || 'Цена по запросу')}
                            </strong>
                        </div>
                        <div class="cart-item__quantity">
                        <button type="button" class="cart-item__btn js-cart-item-decrease">
                            <svg width="10" height="2"><line x1="0.5" y1="1" x2="9.5" y2="1" stroke="black" stroke-linecap="round"></line></svg>
                        </button>
                        <span class="cart-item__quantity-value">${item.quantity}</span>
                        <button type="button" class="cart-item__btn js-cart-item-increase">
                            <svg width="12" height="11"><path d="M6 0.5V10.5" stroke="black" stroke-linecap="round"></path><path d="M1 5.5L11 5.5" stroke="black" stroke-linecap="round"></path></svg> 
                        </button>
                    </div>
                    </div>

                    <button type="button" class="cart-item__remove js-cart-item-remove" aria-label="Удалить товар">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.0781 1L1.07812 13" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.07812 1L13.0781 13" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            `;
        }).join('');

        this.itemsContainer.innerHTML = itemsHTML;
    }

    updateExistingItems() {
        // Обновляем только цены и количества существующих товаров
        this.items.forEach(item => {
            const cartItem = this.itemsContainer.querySelector(`[data-product-id="${item.id}"]`);
            if (cartItem) {
                const hasPrice = item.priceRaw && parseFloat(item.priceRaw) > 0;
                const itemTotalPrice = hasPrice ? parseFloat(item.priceRaw) * item.quantity : 0;
                const itemTotalPriceFormatted = hasPrice ? new Intl.NumberFormat('ru-RU').format(itemTotalPrice) : '';

                // Обновляем цену
                const priceElement = cartItem.querySelector('.cart-item__price');
                if (priceElement) {
                    priceElement.innerHTML = hasPrice ? `${itemTotalPriceFormatted} ₽` : (item.price || 'Цена по запросу');
                }

                // Обновляем количество
                const quantityElement = cartItem.querySelector('.cart-item__quantity-value');
                if (quantityElement) {
                    quantityElement.textContent = item.quantity;
                }
            }
        });
    }

    updateSummary() {
        // Разделяем товары на две категории: с ценой и без цены
        const itemsWithPrice = this.items.filter(item => item.priceRaw && parseFloat(item.priceRaw) > 0);
        const itemsWithoutPrice = this.items.filter(item => !item.priceRaw || parseFloat(item.priceRaw) <= 0);

        // Формируем итоговую строку
        let summaryHTML = '';
        const totalQuantityWithPrice = itemsWithPrice.reduce((sum, item) => sum + item.quantity, 0);
        const totalQuantityWithoutPrice = itemsWithoutPrice.reduce((sum, item) => sum + item.quantity, 0);
        const totalSumWithPrice = itemsWithPrice.reduce((sum, item) => sum + (parseFloat(item.priceRaw) * item.quantity), 0);

        // Формируем итоговую строку в зависимости от наличия товаров с ценой и без цены
        if (itemsWithPrice.length > 0 && itemsWithoutPrice.length === 0) {
            // Только товары с ценой
            summaryHTML = `<div class="cart-summary__row"><strong>Итого: ${totalQuantityWithPrice} шт. на ${new Intl.NumberFormat('ru-RU').format(totalSumWithPrice)} ₽</strong></div>`;
        } else if (itemsWithPrice.length === 0 && itemsWithoutPrice.length > 0) {
            // Только товары без цены
            summaryHTML = `<div class="cart-summary__row"><strong>Итого: ${totalQuantityWithoutPrice} шт. под заказ</strong></div>`;
        } else if (itemsWithPrice.length > 0 && itemsWithoutPrice.length > 0) {
            // И товары с ценой, и без цены
            summaryHTML = `<div class="cart-summary__row"><strong>Итого: ${totalQuantityWithPrice} шт. на ${new Intl.NumberFormat('ru-RU').format(totalSumWithPrice)} ₽ и ${totalQuantityWithoutPrice} шт. под заказ</strong></div>`;
        }

        this.summaryContainer.innerHTML = summaryHTML;
    }

    updateBadgeAndIconVisibility() {
        const totalQuantity = this.items.reduce((sum, item) => sum + item.quantity, 0);

        this.cartIcons.forEach(icon => {
            if (totalQuantity > 0) {
                icon.classList.add('show');
            } else {
                icon.classList.remove('show');
            }
        });

        if (this.badge) {
            if (totalQuantity > 0) {
                this.badge.textContent = totalQuantity;
                this.badge.classList.add('show');
            } else {
                this.badge.classList.remove('show');
            }
        }
    }

    updateQuantityInCartOnly(productId, newQuantity) {
        const item = this.items.find(item => item.id == productId);
        if (item && newQuantity > 0) {
            item.quantity = newQuantity;
            this.save();
            this.updateBadgeAndIconVisibility();
        }
    }

    updateCardButtonsState() {
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            const productId = card.dataset.productId;
            const orderButton = card.querySelector('.js-add-to-cart');
            if (orderButton) {
                const itemInCart = this.items.find(item => item.id == productId);
                if (itemInCart) {
                    this.setButtonStateAdded(orderButton);
                    const quantityEl = card.querySelector('.js-quantity-value');
                    if (quantityEl) {
                        if (quantityEl.tagName === 'INPUT') {
                            quantityEl.value = itemInCart.quantity;
                        } else {
                            quantityEl.textContent = itemInCart.quantity;
                        }
                    }
                } else {
                    this.setButtonStateDefault(orderButton);
                }
            }
        });
    }

    setButtonStateAdded(button) {
        button.textContent = 'В корзине';
        button.classList.add('is-added');
        button.disabled = false;
    }

    setButtonStateDefault(button) {
        button.textContent = 'Заказать';
        button.classList.remove('is-added');
        button.disabled = false;
    }

    trackUtmTags() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const utmFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
            utmFields.forEach(field => {
                if (urlParams.has(field)) sessionStorage.setItem(field, urlParams.get(field));
            });
        } catch (e) { console.error('Ошибка при отслеживании UTM в cart.js:', e); }
    }

    // Удаляю метод submitOrder, чтобы не было ошибки в консоли

    save() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
    }

    load() {
        try {
            const items = localStorage.getItem(this.storageKey);
            return items ? JSON.parse(items) : [];
        } catch (e) {
            console.error("Не удалось загрузить корзину:", e);
            return [];
        }
    }

    clear() {
        this.items = [];
        this.save();
        this.updateBadgeAndIconVisibility();
        this.render();

        // Обновляем состояние кнопок только для страниц с AJAX-функциональностью (ASICS)
        const appRoot = document.getElementById('app-root');
        if (appRoot && appRoot.dataset.iblockId === '1') {
            document.querySelectorAll('.js-add-to-cart.is-added').forEach(btn => {
                this.setButtonStateDefault(btn);
            });
        }
    }

    clearCart() {
        this.clear();
        this.close();
    }
}

// Создаем единственный глобальный экземпляр корзины.
window.appCart = new Cart();

// Отдельно вешаем обработчики для модалки "Спасибо"
document.addEventListener('DOMContentLoaded', () => {
    const successModalOverlay = document.getElementById('successModalOverlay');
    const closeSuccessBtn = document.getElementById('closeSuccessModalBtn');
    const closeSuccessModal = () => successModalOverlay?.classList.remove('is-visible');
    successModalOverlay?.addEventListener('click', e => { if (e.target === successModalOverlay) closeSuccessModal(); });
    closeSuccessBtn?.addEventListener('click', closeSuccessModal);
});