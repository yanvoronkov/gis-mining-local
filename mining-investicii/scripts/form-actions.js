document.addEventListener('DOMContentLoaded', function () {

	// --- Получение ссылок на основные элементы ---
	const contactForm = document.getElementById('contactForm'); // Основная форма
	const submitButton = document.getElementById('submitContactBtn'); // Кнопка основной формы
	const errorMessageElement = document.getElementById('contactFormError'); // Элемент ошибок основной формы
	const successModalOverlay = document.getElementById('successModalOverlay'); // Модальное окно успеха
	const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn'); // Кнопка закрытия модального окна успеха

	// --- Элементы всплывающей формы ---
	const popupFormWrapper = document.getElementById('popupFormWrapper'); // Обертка всплывающей формы
	const popupContactForm = document.getElementById('contactFormPopup'); // Всплывающая форма
	const popupSubmitButton = document.getElementById('submitContactBtnPopup'); // Кнопка всплывающей формы
	const popupErrorMessageElement = document.getElementById('contactFormErrorPopup'); // Элемент ошибок всплывающей формы
	const closePopupFormBtn = document.getElementById('closePopupFormBtn'); // Кнопка закрытия всплывающей формы
	const openPopupFormBtns = document.querySelectorAll('#openPopupFormBtn'); // Кнопки открытия всплывающей формы

	// Выходим, если не найдены критически важные элементы основной формы
	if (contactForm && (!submitButton || !successModalOverlay || !closeSuccessModalBtn)) {
		console.error('Ошибка: Не найдены форма, кнопка или элементы модального окна основной формы.');
		return;
	}
	// Выходим, если не найдены критически важные элементы всплывающей формы
	if (popupContactForm && (!popupSubmitButton || !popupFormWrapper || !closePopupFormBtn)) {
		console.error('Ошибка: Не найдены форма, кнопка или элементы модального окна всплывающей формы.');
		return;
	}

	// --- Вспомогательные функции ---

	/**
	 * Получает значение GET-параметра из URL.
	 * @param {string} param Имя параметра.
	 * @returns {string|null} Значение параметра или null.
	 */
	function getQueryParam(param) {
		const urlParams = new URLSearchParams(window.location.search);
		return urlParams.get(param);
	}

	/**
	 * Показывает сообщение об ошибке под формой.
	 * @param {string} message Текст ошибки.
	 * @param {HTMLElement} errorElement Элемент, куда выводить ошибку.
	 */
	function showError(message, errorElement) {
		if (errorElement) {
			errorElement.textContent = message;
			errorElement.style.display = 'block';
			console.log('showError called:', message);
		} else {
			console.error('Element for error message not found!');
		}
	}

	/**
	 * Скрывает сообщение об ошибке.
	 * @param {HTMLElement} errorElement Элемент, который нужно скрыть.
	 */
	function hideError(errorElement) {
		if (errorElement) {
			errorElement.style.display = 'none';
			errorElement.textContent = '';
			console.log('hideError called');
		}
	}

	/**
	 * Показывает модальное окно успеха.
	 */
	function showSuccessModal() {
		if (successModalOverlay) {
			successModalOverlay.style.display = 'flex';
			setTimeout(() => {
				successModalOverlay.classList.add('is-visible');
			}, 10);
			console.log('showSuccessModal called');
		} else {
			console.error('Success modal overlay element not found!');
		}
	}

	/**
	 * Скрывает модальное окно успеха.
	 */
	function hideSuccessModal() {
		if (successModalOverlay) {
			successModalOverlay.classList.remove('is-visible');
			setTimeout(() => {
				successModalOverlay.style.display = 'none';
			}, 300); // Должно совпадать с transition-duration в CSS
			console.log('hideSuccessModal called');
		}
	}

	/**
	 * Скрывает всплывающую форму.
	 */
	function hidePopupForm() {
		if (popupFormWrapper) {
			popupFormWrapper.style.display = 'none';
			console.log('hidePopupForm called');
		}
	}
	/**
	 * Показывает всплывающую форму.
	 */
	function showPopupForm() {
		if (popupFormWrapper) {
			popupFormWrapper.style.display = 'flex';
			console.log('showPopupForm called');
		}
	}

	// --- Заполнение скрытых полей UTM-метками ---
	const utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
	utmParams.forEach(function (param) {
		const value = getQueryParam(param);
		if (value) {
			// Ищем поле внутри конкретной формы
			if (contactForm) {
				const inputElement = contactForm.querySelector('input[name="' + param + '"]');
				if (inputElement) {
					inputElement.value = value;
					console.log(`UTM param ${param} set to: ${value}`);
				}
			}
			if (popupContactForm) {
				const popupInputElement = popupContactForm.querySelector('input[name="' + param + '"]');
				if (popupInputElement) {
					popupInputElement.value = value;
					console.log(`Popup UTM param ${param} set to: ${value}`);
				}
			}
		}
	});

	// --- Обработчики событий модального окна ---
	closeSuccessModalBtn.addEventListener('click', hideSuccessModal);
	successModalOverlay.addEventListener('click', function (event) {
		if (event.target === successModalOverlay) {
			hideSuccessModal();
		}
	});

	// --- Обработчик закрытия всплывающей формы ---
	if (closePopupFormBtn) {
		closePopupFormBtn.addEventListener('click', hidePopupForm);
	}

	// --- Обработчики открытия всплывающей формы ---
	openPopupFormBtns.forEach(function (btn) {
		btn.addEventListener('click', function (event) {
			event.preventDefault(); // Предотвращаем переход по ссылке, если это ссылка
			showPopupForm();
		});
	});

	// --- Обработчик отправки основной формы ---
	if (contactForm) {
		contactForm.addEventListener('submit', function (event) {
			event.preventDefault(); // Отменяем стандартную отправку
			handleFormSubmit(contactForm, submitButton, errorMessageElement);
		});
	}

	// --- Обработчик отправки всплывающей формы ---
	if (popupContactForm) {
		popupContactForm.addEventListener('submit', function (event) {
			event.preventDefault(); // Отменяем стандартную отправку
			handleFormSubmit(popupContactForm, popupSubmitButton, popupErrorMessageElement, hidePopupForm);
		});
	}

	/**
	 * Обрабатывает отправку формы.
	 * @param {HTMLFormElement} form Элемент формы.
	 * @param {HTMLButtonElement} button Кнопка отправки.
	 * @param {HTMLElement} errorElement Элемент для вывода ошибок.
	 * @param {function} [successCallback] Функция, вызываемая при успешной отправке.
	 */
	function handleFormSubmit(form, button, errorElement, successCallback) {
		// --- Валидация обязательных полей ---
		const nameInput = form.querySelector('input[name="client_name"]');
		const phoneInput = form.querySelector('input[name="client_phone"]');
		if (!nameInput || !phoneInput || !nameInput.value.trim() || !phoneInput.value.trim()) {
			showError('Пожалуйста, заполните имя и телефон.', errorElement);
			return; // Останавливаем отправку
		}

		// --- Клиентская проверка капчи ---
		console.log('Checking captcha...');
		const captchaInput = form.querySelector('[name="smart-token"]');
		const tokenValue = captchaInput ? captchaInput.value : null;
		console.log('Captcha input element:', captchaInput);
		console.log('Captcha token value:', tokenValue);

		if (!captchaInput || !tokenValue) { // Проверяем наличие элемента И непустое значение
			console.log('Captcha check failed in JS.');
			showError('Пожалуйста, подтвердите, что вы не робот.', errorElement);
			return; // Останавливаем отправку!
		}
		console.log('Captcha check passed in JS.');
		// --- Конец проверки капчи ---

		// --- Подготовка к отправке ---
		const originalButtonText = button.textContent; // Сохраняем исходный текст кнопки
		button.disabled = true;
		button.textContent = 'Отправка...';
		hideError(errorElement); // Скрываем предыдущие ошибки

		const formData = new FormData(form); // Собираем все данные формы

		// --- Отправка данных на сервер ---
		fetch('send_lead.php', { // Путь к вашему PHP обработчику
			method: 'POST',
			body: formData
		})
			.then(response => {
				// Обрабатываем ответ, пытаемся получить JSON в любом случае
				const statusCode = response.status;
				// response.json() возвращает промис, поэтому используем .then() для получения данных
				return response.json().then(data => ({
					ok: response.ok, // true если статус 200-299
					status: statusCode,
					data: data
				}))
					.catch(jsonError => {
						// Если ответ сервера не был JSON (например, HTML с ошибкой PHP 500)
						console.error('Failed to parse JSON response:', jsonError);
						return { // Возвращаем структуру ошибки, чтобы .catch ниже сработал
							ok: false,
							status: statusCode || 500, // Используем статус или 500 по умолчанию
							data: { error: 'Не удалось обработать ответ сервера.' }
						};
					});
			})
			.then(result => {
				// Анализируем результат (успех или ошибка от сервера)
				if (result.ok && result.data.success) {
					// УСПЕХ: Статус 2xx и PHP вернул success: true
					form.reset(); // Очищаем форму
					// Опционально: сброс виджета капчи, если API позволяет
					if (typeof smartCaptcha !== 'undefined' && smartCaptcha.getResponse() !== '') {
						smartCaptcha.reset(); // Попробуйте так, если используете глобальный объект smartCaptcha
					}
					if (successCallback) {
						successCallback(); // Вызываем функцию обратного вызова, если она передана
					}
					showSuccessModal(); // Показываем модальное окно
				} else {
					// ОШИБКА: Статус не 2xx ИЛИ PHP вернул success: false
					const serverError = result.data?.error || `Ошибка ${result.status}. Попробуйте позже.`;
					showError(serverError, errorElement); // Показываем ошибку от сервера
					console.error('Server returned an error:', result.data);
				}
			})
			.catch(error => {
				// КРИТИЧЕСКАЯ ОШИБКА: Сеть недоступна, CORS, DNS и т.п.
				showError('Не удалось отправить заявку. Проверьте подключение к интернету.', errorElement);
				console.error('Fetch failed:', error);
			})
			.finally(() => {
				// Выполняется всегда: и при успехе, и при ошибке
				button.disabled = false;
				button.textContent = originalButtonText; // Возвращаем исходный текст кнопки
				console.log('Fetch finished.');
			});
	}

}); // Конец DOMContentLoaded

/**
 * Пустая функция обратного вызова для Яндекс.Капчи.
 * Вызывается после успешного прохождения капчи пользователем.
 * Требуется, если указан data-callback="callback" в HTML.
 */
function callback() {
	console.log('Yandex SmartCaptcha callback triggered.');
	// Здесь можно включить кнопку отправки, если она была выключена до прохождения капчи,
	// но в текущей логике это не обязательно, так как проверка идет при submit.
}
// Убедитесь, что функция callback доступна глобально (не внутри DOMContentLoaded)
// window.callback = callback; // Можно и так, для явного указания
