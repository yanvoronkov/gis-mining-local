document.addEventListener('DOMContentLoaded', () => {
	const header = document.querySelector('.header__container');
	const right = document.querySelector('.site-header__right-side');
	const changeColorSection = document.querySelector('.change-color-section'); // Ваша секция
	const mobileMenuButton = document.querySelector('.header__mobile-menu-toggle');
	const mobilePanel = document.querySelector('.mobile-menu-panel');
	const megamenuTriggers = document.querySelectorAll('.desktop-menu__item--has-megamenu > .desktop-menu__link--button');
	const megamenuCloseButtons = document.querySelectorAll('.megamenu__close-button');
	const mobileSubmenuToggles = document.querySelectorAll('.mobile-menu-panel__nav button[aria-controls]');

	//Реализация смены стиля header при начале скрола и при достижения первой светлой секции
	let partnershipSectionOffsetTop = Infinity; // Инициализируем большим значением

	function calculateOffsets() {
		if (changeColorSection && header) {
			// partnershipSectionOffsetTop - это точка, когда ВЕРХ partnershipSection
			// достигнет ВЕРХА вьюпорта. Нам нужно, чтобы хедер изменился,
			// когда НИЗ хедера коснется ВЕРХА partnershipSection.
			// Поэтому, точка изменения будет = partnershipSection.offsetTop - header.offsetHeight
			changeColorSectionOffsetTop = changeColorSection.offsetTop - header.offsetHeight;
		} else if (changeColorSection) {
			// Если хедера нет (маловероятно, но для подстраховки)
			changeColorSectionOffsetTop = changeColorSection.offsetTop;
		}
	}

	// Вычисляем смещения при загрузке и изменении размера окна
	calculateOffsets();
	window.addEventListener('resize', calculateOffsets);

	window.addEventListener('scroll', () => {
		const scrollPosition = window.scrollY;

		// --- Этап 1: Хедер становится "прилипшим" и, возможно, немного меняет стиль ---
		if (scrollPosition > 0) { // Например, 50px от верха страницы
			header.classList.add('is-sticky');
			// Если 'is-sticky' уже подразумевает основной "рабочий" вид прилипшего хедера
			// (например, синий фон как на вашем скриншоте "header-primary"),
			// то отдельный класс 'header--primary' при достижении partnership-section может быть не нужен,
			// или 'header--primary' будет добавлять лишь небольшие изменения поверх 'is-sticky'.
		} else {
			header.classList.remove('is-sticky');
			// Если 'is-sticky' убирается, хедер возвращается к своему самому начальному виду (до скролла)
		}

		// --- Этап 2: Хедер дополнительно меняет стиль (если это другой стиль, чем is-sticky)
		//     при достижении partnership-section ---
		if (changeColorSection && scrollPosition >= changeColorSectionOffsetTop) {
			// Этот класс должен применяться ПОСЛЕ того, как хедер уже прилип,
			// если 'header--primary' - это дальнейшая модификация 'is-sticky'
			right.classList.add('site-header__right-side-primary');
			header.classList.add('header--primary');
		} else {
			// Убираем 'header--primary', но 'is-sticky' может остаться, если мы все еще ниже 50px скролла
			right.classList.remove('site-header__right-side-primary');
			header.classList.remove('header--primary');
		}
	});

	// Мобильное меню
	if (mobileMenuButton && mobilePanel) {
		mobileMenuButton.addEventListener('click', () => {
			mobileMenuButton.classList.toggle('is-active');
			mobilePanel.classList.toggle('is-open');
			document.body.classList.toggle('no-scroll');
		});

		const mobileMenuCloseButton = mobilePanel.querySelector('.mobile-menu-panel__close-button');
		if (mobileMenuCloseButton) {
			mobileMenuCloseButton.addEventListener('click', () => {
				mobileMenuButton.classList.remove('is-active');
				mobilePanel.classList.remove('is-open');
				document.body.classList.remove('no-scroll');
			});
		}
	}

	mobileSubmenuToggles.forEach(clickedToggle => {
		clickedToggle.addEventListener('click', () => {
			const targetSubmenuId = clickedToggle.getAttribute('aria-controls');
			const targetSubmenu = document.getElementById(targetSubmenuId);
			// Определяем, было ли текущее подменю открыто ДО того, как мы обработаем клик
			const wasCurrentlyExpanded = clickedToggle.getAttribute('aria-expanded') === 'true';

			// Сначала закрываем ВСЕ подменю и сбрасываем aria-expanded у их кнопок
			mobileSubmenuToggles.forEach(otherToggle => {
				// Закрываем только если это НЕ та кнопка, по которой кликнули СЕЙЧАС
				// ИЛИ если мы хотим, чтобы повторный клик по той же кнопке закрывал ее
				// (в этом случае условие otherToggle !== clickedToggle не нужно на этом шаге)
				// Для простоты аккордеона, где клик на новый пункт закрывает старый:
				if (otherToggle !== clickedToggle) {
					otherToggle.setAttribute('aria-expanded', 'false');
					const otherSubmenuId = otherToggle.getAttribute('aria-controls');
					const otherSubmenu = document.getElementById(otherSubmenuId);
					if (otherSubmenu) {
						otherSubmenu.hidden = true;
					}
				}
			});

			// Затем, открываем/закрываем ЦЕЛЕВОЕ подменю
			if (targetSubmenu) {
				if (wasCurrentlyExpanded) {
					// Если кликнули на уже открытый элемент, закрываем его
					clickedToggle.setAttribute('aria-expanded', 'false');
					targetSubmenu.hidden = true;
				} else {
					// Если кликнули на закрытый элемент (или на другой, который теперь закрыт), открываем его
					clickedToggle.setAttribute('aria-expanded', 'true');
					targetSubmenu.hidden = false;
				}
			}
		});
	});



	// Мегаменю
	megamenuTriggers.forEach(trigger => {
		trigger.addEventListener('click', (event) => {
			event.preventDefault();
			const parentItem = trigger.closest('.desktop-menu__item--has-megamenu');
			const megamenuId = trigger.getAttribute('aria-controls');
			const megamenu = document.getElementById(megamenuId);

			document.querySelectorAll('.megamenu.is-open').forEach(openMenu => {
				if (openMenu !== megamenu) {
					openMenu.classList.remove('is-open');
					const otherTrigger = openMenu.closest('.desktop-menu__item--has-megamenu').querySelector('.desktop-menu__link--button');
					if (otherTrigger) {
						otherTrigger.setAttribute('aria-expanded', 'false');
						otherTrigger.closest('.desktop-menu__item--has-megamenu').classList.remove('is-open');
					}
				}
			});

			if (megamenu) {
				const isOpen = megamenu.classList.toggle('is-open');
				trigger.setAttribute('aria-expanded', isOpen.toString());
				parentItem.classList.toggle('is-open', isOpen);
			}
		});
	});

	megamenuCloseButtons.forEach(button => {
		button.addEventListener('click', () => {
			const megamenu = button.closest('.megamenu');
			const triggerId = megamenu.id;
			const triggerButton = document.querySelector(`[aria-controls="${triggerId}"]`);

			megamenu.classList.remove('is-open');
			if (triggerButton) {
				triggerButton.setAttribute('aria-expanded', 'false');
				triggerButton.closest('.desktop-menu__item--has-megamenu').classList.remove('is-open');
			}
		});
	});

	document.addEventListener('click', (event) => {
		if (!event.target.closest('.desktop-menu__item--has-megamenu') && !event.target.closest('.megamenu')) {
			document.querySelectorAll('.megamenu.is-open').forEach(openMenu => {
				openMenu.classList.remove('is-open');
				const triggerId = openMenu.id;
				const triggerButton = document.querySelector(`[aria-controls="${triggerId}"]`);
				if (triggerButton) {
					triggerButton.setAttribute('aria-expanded', 'false');
					triggerButton.closest('.desktop-menu__item--has-megamenu').classList.remove('is-open');
				}
			});
		}
	});

	// 4. Проигрывание видео в области видимости экрана
	const videoElement = document.getElementById('myFeatureVideo');

	if (videoElement) {
		// Функция, которая будет вызываться, когда видимость видео изменится
		const handleIntersection = (entries, observer) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					// Элемент стал видимым
					// Пытаемся запустить видео
					const playPromise = videoElement.play();

					if (playPromise !== undefined) {
						playPromise.then(_ => {
							// Автоплей начался успешно
							console.log("Видео начало воспроизводиться");
						}).catch(error => {
							// Автоплей был заблокирован браузером
							// Это часто случается, если видео не `muted`
							// или если пользователь не взаимодействовал со страницей.
							console.error("Автоплей видео заблокирован:", error);
							// Здесь можно показать кастомную кнопку Play,
							// чтобы пользователь сам запустил видео.
							// videoElement.controls = true; // Показать стандартные контролы
						});
					}
					// Опционально: перестать наблюдать после первого запуска, если нужно одноразовое срабатывание
					// observer.unobserve(videoElement);
				} else {
					// Элемент стал невидимым
					// Опционально: поставить видео на паузу, когда оно уходит из вида
					if (!videoElement.paused) {
						videoElement.pause();
						console.log("Видео поставлено на паузу (ушло из видимости)");
					}
				}
			});
		};

		// Настройки для Intersection Observer
		// threshold: 1.0 означает, что callback сработает, когда 100% элемента будет видно.
		// threshold: 0.5 означает, что callback сработает, когда 50% элемента будет видно.
		// Можно передать массив [0, 0.25, 0.5, 0.75, 1] для более гранулированного отслеживания.
		const observerOptions = {
			root: null, // Отслеживать относительно вьюпорта браузера
			rootMargin: '0px', // Никаких дополнительных отступов от вьюпорта
			threshold: 0.5  // Сработает, когда 50% видео будет видно
		};

		// Создаем новый экземпляр Intersection Observer
		const observer = new IntersectionObserver(handleIntersection, observerOptions);

		// Начинаем наблюдение за элементом видео
		observer.observe(videoElement);
	}

});


// Универсальная логика кнопки "Загрузить еще"
function initializeLoadMore(button) {
	const targetSelector = button.dataset.loadMoreTarget;
	const countToShow = parseInt(button.dataset.loadMoreCount, 10) || Infinity; // По умолчанию показать все

	if (!targetSelector) {
		console.warn('Кнопка "Загрузить еще" не имеет атрибута data-load-more-target.', button);
		button.classList.add('is-hidden'); // Скрываем кнопку, если нет цели
		return;
	}

	// Находим ВСЕ элементы, которые потенциально могут быть показаны.
	// Этот список будет уменьшаться по мере показа элементов.
	// Важно, чтобы is-hidden-initially был таким, чтобы его можно было удалить,
	// или чтобы он не мешал показу после изменения style.display.
	// Лучше ориентироваться на элементы, которые имеют display: none через этот класс.
	let hiddenItems = Array.from(document.querySelectorAll(targetSelector));

	// Сразу скрываем кнопку, если изначально нет скрытых элементов
	if (hiddenItems.length === 0) {
		button.classList.add('is-hidden');
		return;
	}

	button.addEventListener('click', () => {
		const itemsToShowThisClick = hiddenItems.slice(0, countToShow);

		itemsToShowThisClick.forEach(item => {
			item.style.display = ''; // Сбрасываем инлайновый display: none
			// или можно задать конкретное значение, если оно известно
			// item.style.display = 'block'; // или 'flex', 'grid' и т.д.
			item.classList.remove('is-hidden-initially'); // Удаляем класс, который скрывал элемент
			// Можно добавить класс для анимации появления, если нужно
			// item.classList.add('is-visible-animated');
		});

		// Обновляем список оставшихся скрытых элементов
		hiddenItems = hiddenItems.slice(countToShow);

		if (hiddenItems.length === 0) {
			button.classList.add('is-hidden'); // Скрываем кнопку, если больше нечего показывать
		}
	});
}

// Инициализация для всех кнопок "Загрузить еще" на странице
const loadMoreButtons = document.querySelectorAll('[data-load-more-target]');
loadMoreButtons.forEach(initializeLoadMore);


// --- Логика аккордеона (один открытый вопрос) ---
// Эту логику можно также сделать более универсальной, если нужно,
// например, если у вас несколько разных аккордеонов на странице.
// Пока оставим её как есть, так как она специфична для <details> и FAQ.
function initializeAccordion(accordionContainerSelector, itemSelector, summarySelector) {
	const accordionContainer = document.querySelector(accordionContainerSelector);
	if (!accordionContainer) return;

	const items = accordionContainer.querySelectorAll(itemSelector);

	items.forEach(item => {
		const summary = item.querySelector(summarySelector);
		if (summary) {
			summary.addEventListener('click', (event) => {
				event.preventDefault(); // Для <summary> это предотвратит стандартное поведение <details>

				// Если используется элемент <details>
				if (item.tagName === 'DETAILS') {
					const isCurrentlyOpen = item.hasAttribute('open');
					// Закрыть все <details> элементы внутри этого аккордеона
					items.forEach(otherItem => {
						if (otherItem.tagName === 'DETAILS') {
							otherItem.removeAttribute('open');
						}
					});
					if (!isCurrentlyOpen) {
						item.setAttribute('open', '');
					}
				} else {
					// Логика для кастомного аккордеона (не <details>)
					const content = item.querySelector('.accordion-content'); // Пример селектора
					const isActive = item.classList.contains('active');

					items.forEach(otherItem => {
						otherItem.classList.remove('active');
						const otherContent = otherItem.querySelector('.accordion-content');
						if (otherContent) otherContent.style.display = 'none'; // или maxHeight = 0
					});

					if (!isActive) {
						item.classList.add('active');
						if (content) content.style.display = 'block'; // или maxHeight
					}
				}
			});
		}
	});



	// Анимация бегущей строки с лого СМИ
	const marqueeContainer = document.querySelector('.marquee-container-js');
	if (!marqueeContainer) return;

	const marqueeContent = marqueeContainer.querySelector('.marquee-content-js');
	if (!marqueeContent || marqueeContent.children.length === 0) return;

	const originalItems = Array.from(marqueeContent.children);
	const itemWidthIncludingMargin = (() => {
		if (originalItems.length === 0) return 0;
		const itemStyle = window.getComputedStyle(originalItems[0]);
		return originalItems[0].offsetWidth + parseFloat(itemStyle.marginRight);
	})();

	if (itemWidthIncludingMargin === 0 && originalItems.length > 0) {
		console.warn("Marquee item width is 0, animation might not work correctly. Ensure items are visible and have dimensions.");
		// Попробуем получить ширину чуть позже, если стили еще не применились
		// Это не идеальное решение, лучше убедиться, что элементы имеют размеры при DOMContentLoaded
		setTimeout(() => {
			// Повторный расчет itemWidthIncludingMargin можно добавить здесь, если это частая проблема
		}, 100);
	}


	// Клонируем элементы, чтобы заполнить видимую область + запас
	// Нам нужно достаточно элементов, чтобы при уходе одного слева, справа уже был готов следующий
	// Обычно (количество видимых элементов + 2-3 запасных) с каждой стороны достаточно,
	// но для простоты можно клонировать весь набор несколько раз.
	const containerWidth = marqueeContainer.offsetWidth;
	let clonesNeeded = 0;
	if (itemWidthIncludingMargin > 0) {
		clonesNeeded = Math.ceil(containerWidth / itemWidthIncludingMargin) + originalItems.length; // Запас в один полный набор
	} else if (originalItems.length > 0) {
		clonesNeeded = originalItems.length * 2; // Просто удваиваем, если ширину не удалось определить
	}


	// Очищаем и заполняем клонами
	marqueeContent.innerHTML = ''; // Очищаем оригиналы, так как мы их сохранили
	for (let i = 0; i < clonesNeeded + originalItems.length; i++) {
		const originalItem = originalItems[i % originalItems.length]; // Циклически берем из оригиналов
		const clone = originalItem.cloneNode(true);
		marqueeContent.appendChild(clone);
	}

	let currentPosition = 0;
	const speed = 0.5; // Скорость движения (пикселей за кадр)
	let animationFrameId;
	let isPaused = false;

	// Общая ширина всего прокручиваемого контента (теперь это все клоны)
	// Мы не будем сбрасывать currentPosition до 0, а будем его постоянно уменьшать
	// и перемещать элементы.

	function animateMarquee() {
		if (!isPaused) {
			currentPosition -= speed;
			marqueeContent.style.transform = `translateX(${currentPosition}px)`;

			// Проверяем, ушел ли первый видимый элемент полностью за левый край
			const firstChild = marqueeContent.firstChild;
			if (firstChild) {
				// Получаем позицию первого элемента относительно контейнера marqueeContent
				const firstChildRect = firstChild.getBoundingClientRect();
				const marqueeContentRect = marqueeContent.getBoundingClientRect();

				// Если правый край первого элемента ушел левее левого края marqueeContent
				// (с небольшим запасом, чтобы не было видно перестроения)
				// Это условие нужно тщательно подобрать.
				// Проще: когда currentPosition сдвинул первый оригинальный набор элементов
				// (или эквивалентное количество клонов) за пределы видимости.
				// Ширина одного элемента itemWidthIncludingMargin
				if (currentPosition < -itemWidthIncludingMargin) { // Если первый элемент ушел
					marqueeContent.appendChild(firstChild); // Перемещаем его в конец
					currentPosition += itemWidthIncludingMargin; // Корректируем позицию, чтобы не было скачка
					marqueeContent.style.transform = `translateX(${currentPosition}px)`; // Мгновенно обновляем transform
				}
			}
		}
		animationFrameId = requestAnimationFrame(animateMarquee);
	}

	// Запуск анимации
	if (itemWidthIncludingMargin > 0 || originalItems.length > 0) { // Запускаем только если есть элементы и их ширина известна
		animationFrameId = requestAnimationFrame(animateMarquee);
	}


	marqueeContainer.addEventListener('mouseenter', () => {
		isPaused = true;
	});

	marqueeContainer.addEventListener('mouseleave', () => {
		isPaused = false;
		// Анимация возобновится в следующем кадре animateMarquee
	});

	// Опционально: перезапуск при изменении размера окна,
	// так как количество необходимых клонов может измениться.
	// Но это усложнит логику, так как нужно будет перестраивать DOM.
	// Для простоты пока опустим, но для идеальной адаптивности это нужно.
	// window.addEventListener('resize', () => { ... });
}

// Инициализация аккордеона для FAQ
initializeAccordion('.faq-section', '.faq-item', '.faq-item__question');
// Если у вас будет другой аккордеон:
initializeAccordion('.section-company-history', '.text-item');


//Вставка видео или картинки в hero в зависимости от устройства

// document.addEventListener('DOMContentLoaded', function () {
// 	const mediaContainer = document.getElementById('adaptiveMedia');
// 	const videoSrcWebM = '../img/hero-section_main_video.webm'; // Путь к вашему WebM видео
// 	const imageSrcForIOS = '../img/home/hero_section_image.png'; // Путь к вашей картинке для iOS
// 	const imageAltText = 'Анимация оборудования GIS Mining'; // Alt текст для картинки

// 	// Функция для определения iOS
// 	function isIOS() {
// 		return [
// 			'iPad Simulator',
// 			'iPhone Simulator',
// 			'iPod Simulator',
// 			'iPad',
// 			'iPhone',
// 			'iPod'
// 		].includes(navigator.platform)
// 			// Дополнительная проверка на iPad на iOS 13+
// 			|| (navigator.userAgent.includes("Mac") && "ontouchend" in document);
// 	}

// 	if (isIOS()) {
// 		// Показываем картинку для iOS
// 		const imgElement = document.createElement('img');
// 		imgElement.src = imageSrcForIOS;
// 		imgElement.alt = imageAltText;
// 		mediaContainer.appendChild(imgElement);
// 		console.log("iOS detected. Showing image.");
// 	} else {
// 		// Показываем видео для других устройств
// 		const videoElement = document.createElement('video');
// 		videoElement.autoplay = true;
// 		videoElement.loop = true;
// 		videoElement.muted = true; // Важно для автоплея
// 		videoElement.playsinline = true; // Важно для iOS (хотя здесь мы его не используем для видео) и хорошая практика

// 		const sourceWebM = document.createElement('source');
// 		sourceWebM.src = videoSrcWebM;
// 		sourceWebM.type = 'video/webm';
// 		videoElement.appendChild(sourceWebM);

// 		// Можно добавить запасной текст, если видео не поддерживается
// 		const fallbackText = document.createTextNode('Ваш браузер не поддерживает тег video или формат WebM.');
// 		videoElement.appendChild(fallbackText);

// 		mediaContainer.appendChild(videoElement);
// 		console.log("Non-iOS detected. Showing video.");
// 	}
// });

// Вставка видео или картинки с приоритетами форматов и настройкой через HTML

// document.addEventListener('DOMContentLoaded', function () {
// 	const mediaContainer = document.getElementById('adaptiveMedia');

// 	// Проверяем, существует ли контейнер на странице
// 	if (!mediaContainer) {
// 		console.error('Контейнер с id="adaptiveMedia" не найден.');
// 		return; // Прерываем выполнение скрипта
// 	}

// 	// Получаем пути к файлам из data-атрибутов
// 	const videoSrcHEVC = mediaContainer.dataset.videoHevcMp4;
// 	const videoSrcWebM = mediaContainer.dataset.videoWebm;
// 	const imageSrcFallback = mediaContainer.dataset.fallbackImage;
// 	const imageAltText = mediaContainer.dataset.altText || 'Адаптивное медиа'; // Текст по умолчанию

// 	// Функция для создания и вставки картинки-фолбэка
// 	const showFallbackImage = () => {
// 		// Очищаем контейнер от возможных остатков видео, если была ошибка
// 		mediaContainer.innerHTML = '';
// 		const imgElement = document.createElement('img');
// 		imgElement.src = imageSrcFallback;
// 		imgElement.alt = imageAltText;
// 		mediaContainer.appendChild(imgElement);
// 		console.log("Видео не поддерживается или не загрузилось. Показываем картинку-фолбэк.");
// 	};

// 	// Создаем элемент <video> с общими атрибутами
// 	const videoElement = document.createElement('video');
// 	videoElement.autoplay = true;
// 	videoElement.loop = true;
// 	videoElement.muted = true;      // Обязательно для автоплея в большинстве браузеров
// 	videoElement.playsinline = true; // Обязательно для корректного отображения на мобильных, особенно iOS

// 	// Добавляем обработчик ошибки. Он сработает, если браузер не сможет воспроизвести ни один из источников.
// 	videoElement.addEventListener('error', (e) => {
// 		// Иногда ошибка может быть ложной, проверяем, есть ли реальная проблема с источниками
// 		if (videoElement.networkState === videoElement.NETWORK_NO_SOURCE) {
// 			showFallbackImage();
// 		}
// 	});

// 	// Добавляем источники <source> в порядке приоритета.
// 	// Браузер сам выберет первый, который сможет воспроизвести.

// 	// 1. HEVC (h.265) в MP4 для Safari (iOS, macOS) с альфа-каналом
// 	if (videoSrcHEVC) {
// 		const sourceHEVC = document.createElement('source');
// 		sourceHEVC.src = videoSrcHEVC;
// 		// Используем MIME-тип с кодеком hvc1, который предпочитает Safari для HEVC
// 		sourceHEVC.type = 'video/mp4; codecs="hvc1"';
// 		videoElement.appendChild(sourceHEVC);
// 	}

// 	// 2. WebM для Chrome, Firefox и других современных браузеров
// 	if (videoSrcWebM) {
// 		const sourceWebM = document.createElement('source');
// 		sourceWebM.src = videoSrcWebM;
// 		sourceWebM.type = 'video/webm';
// 		videoElement.appendChild(sourceWebM);
// 	}

// 	// Проверяем, был ли добавлен хотя бы один источник видео
// 	if (videoElement.children.length > 0) {
// 		// Если есть источники, добавляем видео в контейнер
// 		mediaContainer.appendChild(videoElement);
// 		console.log("Видео с доступными форматами добавлено на страницу.");
// 	} else if (imageSrcFallback) {
// 		// Если не указан ни один источник видео, сразу показываем картинку
// 		showFallbackImage();
// 		console.log("Источники видео не указаны. Показываем картинку-фолбэк.");
// 	} else {
// 		// Если не указано вообще ничего
// 		console.error("Не указаны источники ни для видео, ни для картинки-фолбэка в data-атрибутах.");
// 	}
// });

// document.addEventListener('DOMContentLoaded', function () {
// 	const videoElement = document.querySelector('.adaptive-media-container video');

// 	if (videoElement) {
// 		// Пытаемся запустить видео программно, это может помочь в некоторых случаях
// 		const playPromise = videoElement.play();

// 		if (playPromise !== undefined) {
// 			playPromise.catch(error => {
// 				// Автоплей был заблокирован браузером. Это нормально.
// 				// Пользователь увидит poster-картинку.
// 				console.warn("Video autoplay was prevented:", error);
// 			});
// 		}

// 		// Обработчик на случай, если видео загрузилось, но не может быть воспроизведено
// 		videoElement.addEventListener('error', (e) => {
// 			console.error("Ошибка воспроизведения видео:", e);

// 			// Получаем родительский контейнер
// 			const container = videoElement.parentElement;

// 			// Получаем картинку, которая находится внутри тега <video>
// 			const fallbackImage = videoElement.querySelector('img');

// 			if (container && fallbackImage) {
// 				// Прячем видео и показываем только картинку
// 				videoElement.style.display = 'none';
// 				// Достаем картинку из видео и вставляем в контейнер
// 				container.appendChild(fallbackImage);
// 			}
// 		});
// 	}
// });






