document.addEventListener('DOMContentLoaded', function () {

    // --- Функция для отправки цели в Яндекс.Метрику ---
    function reachMetricGoal(goalId, params = {}) {
        // Проверяем существование метрики
        if (typeof ym !== 'undefined' && typeof window.YM_ID !== 'undefined') {
            try {
                ym(window.YM_ID, 'reachGoal', goalId, params);
                console.log('Цель Яндекс.Метрики отправлена:', goalId, params);
            } catch (error) {
                console.warn('Ошибка отправки цели Яндекс.Метрики:', error);
            }
        } else {
            console.warn('Яндекс.Метрика не инициализирована или не найден YM_ID');
        }
    }

    // --- Инициализация маски для телефонов ---
    const phoneInputs = document.querySelectorAll('.js-phone-mask');
    if (phoneInputs.length > 0) {
        const phoneMaskOptions = {
            mask: '+{7} (000) 000-00-00',
            lazy: false,
        };
        phoneInputs.forEach(phoneInput => {
            IMask(phoneInput, phoneMaskOptions);
            // Дополнительная логика focus/blur при необходимости
        });
    }

    // --- Элементы модального окна успеха (поддерживаем несколько) ---
    const successModals = {
        main: {
            overlay: document.getElementById('mainSuccessModalOverlay'),
            closeBtn: document.getElementById('closeMainSuccessModalBtn')
        },
        cart: {
            overlay: document.getElementById('cartSuccessModalOverlay'),
            closeBtn: document.getElementById('closeCartSuccessModalBtn')
        },
        cheaper: {
            overlay: document.getElementById('cheaperSuccessModalOverlay'),
            closeBtn: document.getElementById('closeCheaperSuccessModalBtn')
        }
    };

    // --- Элементы управления всплывающими формами ---
    const popupForms = {
        main: {
            wrapper: document.getElementById('mainPopupFormWrapper'),
            closeBtn: document.getElementById('closeMainPopupFormBtn')
        },
        rassrochka: {
            wrapper: document.getElementById('rassrochkaPopupFormWrapper'),
            closeBtn: document.getElementById('closeRassrochkaPopupFormBtn')
        },
        cart: {
            wrapper: document.getElementById('cartPopupFormWrapper'),
            closeBtn: document.getElementById('closeCartPopupFormBtn')
        },
        share: {
            wrapper: document.getElementById('sharePopupFormWrapper'),
            closeBtn: document.getElementById('closeSharePopupFormBtn')
        },
        cheaper: {
            wrapper: document.getElementById('cheaperPopupFormWrapper'),
            closeBtn: document.getElementById('closeCheaperPopupFormBtn')
        }
    };

    const openPopupFormBtns = document.querySelectorAll('.js-open-popup-form');

    // Проверка наличия обязательных элементов для модального окна успеха
    const hasSuccessModals = Object.values(successModals).some(modal => modal.overlay && modal.closeBtn);
    if (!hasSuccessModals) {
        console.warn('Предупреждение: Не найдены элементы модального окна успеха.');
    }

    // --- Вспомогательные функции ---
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Функция для получения cookie
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    function showError(message, errorElement, invalidInput = null) {
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        if (invalidInput) {
            invalidInput.classList.add('is-invalid');
            invalidInput.addEventListener('input', () => {
                invalidInput.classList.remove('is-invalid');
            }, { once: true });
        }
    }

    function hideError(errorElement, form = null) {
        if (errorElement) {
            errorElement.style.display = 'none';
            errorElement.textContent = '';
        }
        if (form) {
            const invalidFields = form.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => {
                field.classList.remove('is-invalid');
            });
        }
    }

    function showPopupForm(formType = 'main') {
        const popup = popupForms[formType];
        if (popup && popup.wrapper) {
            const formInPopup = popup.wrapper.querySelector('form.js-ajax-form');
            if (formInPopup) {
                formInPopup.reset();
                const errorMsg = formInPopup.querySelector('.form-error-message');
                if (errorMsg) hideError(errorMsg, formInPopup);
                fillHiddenFields(formInPopup);
            }
            popup.wrapper.style.display = 'flex';
            setTimeout(() => {
                popup.wrapper.classList.add('is-visible');
            }, 10);
            if (formType !== 'cart') {
                document.body.classList.add('no-scroll');
            }
        }
        
    }

    function hidePopupForm(formType = 'main') {
        const popup = popupForms[formType];
        if (popup && popup.wrapper) {
            popup.wrapper.classList.remove('is-visible');
            setTimeout(() => {
                popup.wrapper.style.display = 'none';
                if (formType !== 'cart') {
                    document.body.classList.remove('no-scroll');
                }
            }, 300);
        }
    }

    function fillHiddenFields(form) {
        const utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        utmParams.forEach(param => {
            const value = getQueryParam(param);
            if (value) {
                const inputElement = form.querySelector(`input[name="${param}"]`);
                if (inputElement) {
                    inputElement.value = value;
                }
            }
        });
        const ycIdValue = getCookie('_ym_uid');
        if (ycIdValue) {
            const ycIdInput = form.querySelector('input[name="yc_id"]');
            if (ycIdInput) {
                ycIdInput.value = ycIdValue;
            }
        }
    }

    const allForms = document.querySelectorAll('.js-ajax-form');
    allForms.forEach(form => {
        fillHiddenFields(form);
    });

    // --- Универсальный popup (успех / ошибка) ---
    function showGlobalPopup(type, message = '') {
        const overlay = document.getElementById('globalPopupOverlay');
        const title = document.getElementById('globalPopupTitle');
        const text = document.getElementById('globalPopupText');
        const icon = document.getElementById('globalPopupIcon');

        if (!overlay || !title || !text || !icon) return;

        if (type === 'success') {
            title.textContent = 'Заявка принята!';
            title.style.display = 'block';
            text.textContent = message || 'Благодарим! Мы свяжемся с вами в ближайшее время.';
            icon.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
                  <path fill="#10B981" d="M25,2C12.3,2,2,12.3,2,25s10.3,23,23,23s23-10.3,23-23S37.7,2,25,2z
                    M37.1,20.4L24,33.5c-0.2,0.2-0.5,0.3-0.7,0.3s-0.5-0.1-0.7-0.3l-6.9-6.9
                    c-0.4-0.4-0.4-1,0-1.4s1-0.4,1.4,0l6.2,6.2l12.4-12.4c0.4-0.4,1-0.4,1.4,0
                    C37.5,19.4,37.5,20,37.1,20.4z"/>
                </svg>`;
        } else if (type === 'error') {
            title.textContent = 'Ошибка!';
            title.style.display = 'block';
            text.textContent = message || 'Форма заполнена некорректно. Проверьте данные и попробуйте снова.';
            icon.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
                  <circle cx="25" cy="25" r="23" fill="#EF4444"/>
                  <line x1="16" y1="16" x2="34" y2="34" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                  <line x1="34" y1="16" x2="16" y2="34" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                </svg>`;
        }

        overlay.style.display = 'flex';
        setTimeout(() => overlay.classList.add('is-visible'), 10);
    }

    const globalOverlay = document.getElementById('globalPopupOverlay');
    const globalCloseBtn = document.getElementById('closeGlobalPopupBtn');
    if (globalCloseBtn) {
        globalCloseBtn.addEventListener('click', () => {
            globalOverlay.classList.remove('is-visible');
            setTimeout(() => {
                globalOverlay.style.display = 'none';
                const title = document.getElementById('globalPopupTitle');
                if (title) title.style.display = 'none';
            }, 300);
        });
    }
    if (globalOverlay) {
        globalOverlay.addEventListener('click', (e) => {
            if (e.target.id === 'globalPopupOverlay') {
                globalOverlay.classList.remove('is-visible');
                setTimeout(() => {
                    globalOverlay.style.display = 'none';
                    const title = document.getElementById('globalPopupTitle');
                    if (title) title.style.display = 'none';
                }, 300);
            }
        });
    }

    // --- Обработчики открытия/закрытия всплывающих форм ---
    if (openPopupFormBtns.length > 0) {
        openPopupFormBtns.forEach(btn => {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                const formType = this.dataset.formType || 'main';
                const goalId = this.getAttribute('data-metric-goal');
                if (goalId) {
                    reachMetricGoal(goalId, { form_type: formType });
                }
                showPopupForm(formType);
            });
        });
    }

    Object.entries(popupForms).forEach(([type, popup]) => {
        if (popup.closeBtn) {
            popup.closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                hidePopupForm(type);
            });
        }
        if (popup.wrapper) {
            popup.wrapper.addEventListener('click', function (event) {
                if (event.target === popup.wrapper) {
                    hidePopupForm(type);
                }
            });
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            Object.entries(popupForms).forEach(([type, popup]) => {
                if (popup.wrapper && (popup.wrapper.classList.contains('is-visible') || popup.wrapper.style.display !== 'none')) {
                    hidePopupForm(type);
                }
            });
        }
    });

    // --- Обработчик форм ---
    const ajaxForms = document.querySelectorAll('.js-ajax-form');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            const errorElement = form.querySelector('.form-error-message');
            let formType = 'main';
            let successCallback = null;
            if (form.closest('#mainPopupFormWrapper')) {
                formType = 'main';
                successCallback = () => hidePopupForm('main');
            } else if (form.closest('#cartPopupFormWrapper')) {
                formType = 'cart';
                successCallback = () => hidePopupForm('cart');
            }
            handleFormSubmit(form, submitButton, errorElement, successCallback, formType);
        });
    });

    function handleFormSubmit(form, button, errorElement, successUiCallback, formType = 'main') {
        hideError(errorElement, form);
        const phoneInput = form.querySelector('input[name="client_phone"]');

        if (!phoneInput || !phoneInput.value.trim()) {
            showError('Пожалуйста, заполните телефон.', errorElement, phoneInput);
            showGlobalPopup('error', 'Пожалуйста, заполните телефон.');
            return;
        }
        if (phoneInput && phoneInput.imask) {
            const phoneMaskInstance = phoneInput.imask;
            if (phoneMaskInstance.unmaskedValue.length > 0 && phoneMaskInstance.unmaskedValue.length < 11) {
                showError('Пожалуйста, введите корректный номер телефона (11 цифр).', errorElement, phoneInput);
                showGlobalPopup('error', 'Пожалуйста, введите корректный номер телефона (11 цифр).');
                return;
            }
        } else {
            const rawPhone = phoneInput.value.replace(/\D/g, '');
            if (rawPhone.length < 11) {
                showError('Пожалуйста, введите корректный номер телефона (11 цифр).', errorElement, phoneInput);
                showGlobalPopup('error', 'Пожалуйста, введите корректный номер телефона (11 цифр).');
                return;
            }
        }

        const originalButtonText = button.textContent;
        button.disabled = true;
        button.textContent = 'Отправка...';

        const formData = new FormData(form);

        fetch('/send_lead.php', { method: 'POST', body: formData })
            .then(response => {
                const statusCode = response.status;
                return response.json().then(data => ({
                    ok: response.ok,
                    status: statusCode,
                    data: data
                })).catch(() => {
                    return {
                        ok: false,
                        status: statusCode || 500,
                        data: { error: `Ошибка сервера (${statusCode}).` }
                    };
                });
            })
            .then(result => {
                if (result.ok && result.data.success) {
                    const formGoal = form.getAttribute('data-metric-goal');
                    if (formGoal) {
                        reachMetricGoal(formGoal, {
                            form_type: formType,
                            phone: phoneInput ? phoneInput.value : ''
                        });
                    }
                    form.reset();
                    if (form.id === 'cartForm' && window.appCart) {
                        window.appCart.clear();
                    }
                    if (successUiCallback) successUiCallback();
                    showGlobalPopup('success');
                } else {
                    const serverError = result.data?.error || `Ошибка ${result.status}. Попробуйте позже.`;
                    showError(serverError, errorElement);
                    showGlobalPopup('error', serverError);
                }
            })
            .catch(() => {
                showError('Не удалось отправить заявку. Проверьте подключение.', errorElement);
                showGlobalPopup('error', 'Не удалось отправить заявку. Проверьте подключение.');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalButtonText;
            });
    }

    // --- Копирование ссылки ---
    const copyLinkBtn = document.querySelector('.share-popup__copy-button');
    const copyInput = document.querySelector('.share-popup__copy-input');
    const toastNotification = document.querySelector('.toast-notification');
    if (copyLinkBtn && copyInput) {
        copyLinkBtn.addEventListener('click', function () {
            const currentUrl = window.location.href;
            copyInput.value = currentUrl;
            copyInput.select();
            copyInput.setSelectionRange(0, 99999);
            try {
                document.execCommand('copy');
                if (toastNotification) {
                    toastNotification.style.display = 'flex';
                    setTimeout(() => { toastNotification.style.display = 'none'; }, 2000);
                }
                const originalText = this.querySelector('.share-popup__copy-button-text');
                if (originalText) {
                    const originalTextContent = originalText.textContent;
                    originalText.textContent = 'Ссылка скопирована';
                    setTimeout(() => { originalText.textContent = originalTextContent; }, 2000);
                }
                this.classList.add('copied');
                setTimeout(() => { this.classList.remove('copied'); }, 2000);
            } catch (err) {
                console.error('Ошибка при копировании:', err);
            }
        });
    }
    const toastCloseBtn = document.querySelector('.toast-notification__close');
    if (toastCloseBtn && toastNotification) {
        toastCloseBtn.addEventListener('click', function () {
            toastNotification.style.display = 'none';
        });
    }
});
