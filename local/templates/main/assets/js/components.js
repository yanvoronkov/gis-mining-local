document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header__container');
    const right = document.querySelector('.site-header__right-side');
    const changeColorSection = document.querySelector('.change-color-section'); // Ваша секция

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
    let hiddenItems = Array.from(document.querySelectorAll(targetSelector));

    // Сразу скрываем кнопку, если изначально нет скрытых элементов
    if (hiddenItems.length === 0) {
        button.classList.add('is-hidden');
        return;
    }

    button.addEventListener('click', () => {
        const itemsToShowThisClick = hiddenItems.slice(0, countToShow);

        itemsToShowThisClick.forEach(item => {
            // Удаляем класс, который скрывал элемент
            item.classList.remove('is-hidden-initially');
            // Устанавливаем правильный display в зависимости от типа элемента
            if (item.classList.contains('support-stages-item')) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'block';
            }
        });

        // Обновляем список оставшихся скрытых элементов
        hiddenItems = hiddenItems.slice(countToShow);

        if (hiddenItems.length === 0) {
            button.classList.add('is-hidden'); // Скрываем кнопку, если больше нечего показывать
        }
    });
}

// Универсальная логика для кнопок js-toggle-more (jQuery-based)
function initializeToggleMore() {
    // Проверяем, есть ли jQuery
    if (typeof $ === 'undefined') {
        console.warn('jQuery не загружен, механизм js-toggle-more не будет работать');
        return;
    }

    // Инициализация для всех групп контента
    const groups = new Set();
    $('.js-toggle-more, .js-toggle-less').each(function () {
        const group = $(this).data('content-group');
        if (group) {
            groups.add(group);
        }
    });

    groups.forEach(group => {
        updateButtonVisibilityAndContentState(group);
    });

    // Обработчик клика на "Развернуть/Показать еще"
    $(document).on('click', '.js-toggle-more', function (e) {
        e.preventDefault();
        const $button = $(this);
        const group = $button.data('content-group');
        if (!group) return;

        const $contentWrapper = $('.togglable-content[data-content-group="' + group + '"]');
        if (!$contentWrapper.length) return;

        const targetSelector = $button.data('toggle-target');
        if (!targetSelector) return;
        const $targets = $(targetSelector);
        if (!$targets.length) return;

        const increment = parseInt($button.data('toggle-show-increment'), 10) || 1;
        let currentlyVisible = parseInt($contentWrapper.data('currently-visible'), 10);

        if (isNaN(currentlyVisible)) {
            updateButtonVisibilityAndContentState(group);
            currentlyVisible = parseInt($contentWrapper.data('currently-visible'), 10);
        }

        const nextVisibleCount = currentlyVisible + increment;
        $targets.slice(currentlyVisible, nextVisibleCount).slideDown();
        currentlyVisible = Math.min(nextVisibleCount, $targets.length);
        $contentWrapper.data('currently-visible', currentlyVisible);

        updateButtonVisibilityAndContentState(group);
    });

    // Обработчик клика на "Свернуть"
    $(document).on('click', '.js-toggle-less', function (e) {
        e.preventDefault();
        const $button = $(this);
        const group = $button.data('content-group');
        if (!group) return;

        const $contentWrapper = $('.togglable-content[data-content-group="' + group + '"]');
        if (!$contentWrapper.length) return;

        const targetSelector = $button.data('toggle-target');
        if (!targetSelector) return;
        const $targets = $(targetSelector);
        if (!$targets.length) return;

        const initialVisible = parseInt($button.data('toggle-visible-initial'), 10) || 0;

        $targets.slice(initialVisible).slideUp();
        $contentWrapper.data('currently-visible', initialVisible);

        updateButtonVisibilityAndContentState(group);
    });
}

// Функция для обновления видимости кнопок и состояния контента (для js-toggle-more)
function updateButtonVisibilityAndContentState(group) {
    const $contentWrapper = $('.togglable-content[data-content-group="' + group + '"]');
    if (!$contentWrapper.length) {
        return;
    }

    // Берем target из любой кнопки, они должны быть одинаковыми для группы
    const $anyButtonForTarget = $('.js-toggle-more[data-content-group="' + group + '"], .js-toggle-less[data-content-group="' + group + '"]').first();
    const targetSelector = $anyButtonForTarget.data('toggle-target');

    if (!targetSelector) {
        console.error('Не найден data-toggle-target для группы ' + group);
        return;
    }

    const $targets = $(targetSelector);
    const $moreButton = $('.js-toggle-more[data-content-group="' + group + '"]');
    const $lessButton = $('.js-toggle-less[data-content-group="' + group + '"]');



    if (!$targets.length) { // Если нет элементов для скрытия/показа
        if ($moreButton.length) $moreButton.hide();
        if ($lessButton.length) $lessButton.hide();
        return;
    }

    const initialVisible = parseInt($anyButtonForTarget.data('toggle-visible-initial'), 10) || 0;
    let currentlyVisible = parseInt($contentWrapper.data('currently-visible'), 10);

    // Инициализация, если еще не было
    if (isNaN(currentlyVisible)) {
        // Элементы уже скрыты CSS стилями (класс is-hidden-initially)
        // Начинаем с 0 показанных элементов
        currentlyVisible = 0;
        $contentWrapper.data('currently-visible', currentlyVisible);
    }

    // Управление видимостью кнопок
    if (currentlyVisible >= $targets.length) {
        // Все элементы показаны
        $moreButton.hide();
        if ($lessButton.length) {
            $lessButton.show();
        }
    } else {
        // Есть что показать (или все свернуто до initial)
        $moreButton.show();
        if ($lessButton.length) {
            $lessButton.hide();
        }
    }

    // Особый случай: если initialVisible = 0, то при полном сворачивании кнопка "Развернуть" должна быть видна
    if (currentlyVisible === 0 && initialVisible === 0 && $targets.length > 0) {
        $moreButton.show();
        if ($lessButton.length) $lessButton.hide();
    }
}

// Функция для инициализации всех кнопок на странице
function initializeAllButtons() {
    // Инициализация для всех кнопок "Загрузить еще" на странице
    const loadMoreButtons = document.querySelectorAll('[data-load-more-target]');
    loadMoreButtons.forEach(button => {
        // Проверяем, не была ли кнопка уже инициализирована
        if (!button.hasAttribute('data-initialized')) {
            initializeLoadMore(button);
            button.setAttribute('data-initialized', 'true');
        }
    });

    // Инициализация для кнопок js-toggle-more (если jQuery доступен)
    if (typeof $ !== 'undefined') {
        initializeToggleMore();
    }
}

// Инициализируем кнопки после загрузки DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAllButtons);
} else {
    // DOM уже загружен
    initializeAllButtons();
}

// Повторная инициализация при динамической загрузке контента
document.addEventListener('contentLoaded', initializeAllButtons);


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

}

// Инициализация аккордеона для FAQ
initializeAccordion('.faq-section', '.faq-item', '.faq-item__question');
// Инициализация аккордеона для FAQ в блоке гарантии
initializeAccordion('.about__content-guarantee .content-section .faq-list', '.faq-item', '.faq-item__question');
// Закомментируем эту строку, так как для section-company-history используется другой механизм
// initializeAccordion('.section-company-history', '.text-item');

// Простой механизм для кнопки "Развернуть" в истории компании
function initializeSimpleToggle() {
    const toggleButton = document.querySelector('.js-simple-toggle');
    const hiddenElements = document.querySelectorAll('.section-company-history__text-item.is-hidden-initially');

    if (!toggleButton || !hiddenElements.length) return;

    let isExpanded = false;

    toggleButton.addEventListener('click', function () {
        if (isExpanded) {
            // Сворачиваем
            hiddenElements.forEach(el => el.style.display = 'none');
            toggleButton.innerHTML = 'Развернуть <svg class="read-more-button__icon" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 1.4043L6 6.4043L1 1.4043" stroke="#131315" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
            isExpanded = false;
        } else {
            // Разворачиваем
            hiddenElements.forEach(el => el.style.display = 'block');
            toggleButton.innerHTML = 'Свернуть <svg class="read-more-button__icon" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg" style="transform: rotate(180deg);"><path d="M11 1.4043L6 6.4043L1 1.4043" stroke="#131315" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
            isExpanded = true;
        }
    });

    console.log('Простой механизм переключения инициализирован');
}

// Инициализируем простой механизм
initializeSimpleToggle();

// Инициализация вкладок описания товара/раздела
function initializeProductTabs() {
    // Проверяем, что мы на странице товара (есть секция about или catalog-about с вкладками)
    const aboutSection = document.querySelector('.about, .catalog-about');
    if (!aboutSection) return;

    const tabButtons = document.querySelectorAll('.js-about-tab');
    const tabContents = document.querySelectorAll('.js-tab-content');
    const aboutTitle = document.querySelector('.about__title');

    if (!tabButtons.length || !tabContents.length) return;

    // Функция для обновления заголовка
    function updateAboutTitle(activeButton) {
        if (aboutTitle && activeButton) {
            const buttonText = activeButton.textContent.trim();
            aboutTitle.textContent = buttonText;
        }
    }

    // Инициализация: устанавливаем заголовок для активной кнопки по умолчанию
    const activeButton = document.querySelector('.js-about-tab.is-active');
    if (activeButton) {
        updateAboutTitle(activeButton);
    }

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

            // Обновляем заголовок
            updateAboutTitle(this);
        });
    });
}

// Инициализируем вкладки после загрузки DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProductTabs);
} else {
    // DOM уже загружен
    initializeProductTabs();
}


// Инициализация аккордеонов каталога для мобильных устройств
function initializeCatalogAccordions() {
    const catalogAccordions = document.querySelectorAll('.catalog-accordion');

    console.log('Найдено аккордеонов:', catalogAccordions.length);

    if (catalogAccordions.length === 0) return;

    catalogAccordions.forEach((accordion, index) => {
        const toggle = accordion.querySelector('.catalog-accordion__toggle');
        const content = accordion.querySelector('.catalog-accordion__content');
        const arrow = accordion.querySelector('.icon-arrow');

        console.log(`Аккордеон ${index + 1}:`, {
            accordion: accordion,
            toggle: toggle,
            content: content,
            arrow: arrow,
            isFilterAccordion: toggle ? toggle.classList.contains('not-mobile-visible') : false
        });

        if (!toggle || !content) return;

        // Проверяем, есть ли класс not-mobile-visible (значит это аккордеон фильтров)
        const isFilterAccordion = toggle.classList.contains('not-mobile-visible');

        // Для аккордеона категорий (без класса not-mobile-visible) - всегда открыт на десктопе
        if (!isFilterAccordion) {
            // На мобильных устройствах аккордеон категорий должен быть закрыт по умолчанию
            if (window.innerWidth < 768) {
                accordion.classList.remove('is-open');
                if (content) {
                    content.classList.remove('is-visible');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            } else {
                // На десктопе аккордеон категорий всегда открыт
                accordion.classList.add('is-open');
                if (content) {
                    content.classList.add('is-visible');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }

        // Обработчик клика для всех аккордеонов
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = accordion.classList.contains('is-open');

            console.log('Клик по аккордеону:', {
                accordion: accordion,
                isOpen: isOpen,
                content: content,
                arrow: arrow
            });

            if (isOpen) {
                // Закрываем аккордеон
                accordion.classList.remove('is-open');
                if (content) {
                    content.classList.remove('is-visible');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
                console.log('Аккордеон закрыт');
            } else {
                // Открываем аккордеон
                accordion.classList.add('is-open');
                if (content) {
                    content.classList.add('is-visible');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
                console.log('Аккордеон открыт');
            }
        });
    });
}

// Инициализируем аккордеоны каталога после загрузки DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCatalogAccordions);
} else {
    // DOM уже загружен
    initializeCatalogAccordions();
}

// Повторная инициализация при изменении размера окна
window.addEventListener('resize', initializeCatalogAccordions);


// =================================================================================
// КОМПОНЕНТ ЖИВОГО ПОИСКА ПО КАТАЛОГУ (custom:catalog.search)
// Перенесено из script.js компонента для гарантированной работы на всех страницах
// =================================================================================

(function () {
    'use strict';

    // Ждем загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCatalogSearch);
    } else {
        initCatalogSearch();
    }

    function initCatalogSearch() {
        const searchInputs = document.querySelectorAll('.catalog-search-input');

        searchInputs.forEach(function (input) {
            new CatalogSearch(input);
        });

        // Инициализация подсказок для всех экземпляров компонента
        initSearchTooltips();
    }

    function initSearchTooltips() {
        // Находим все подсказки на странице
        const tooltips = document.querySelectorAll('.catalog-search-tooltip');

        tooltips.forEach(function (tooltip) {
            initSearchTooltip(tooltip);
        });
    }

    function initSearchTooltip(tooltip) {
        if (!tooltip) return;

        const closeBtn = tooltip.querySelector('.catalog-search-tooltip__close');
        // Находим input внутри того же контейнера (подсказка и форма находятся в catalog-search-container)
        const container = tooltip.closest('.catalog-search-container');
        const searchInput = container ? container.querySelector('.catalog-search-input') : null;

        // Показываем подсказку через 1 секунду после загрузки (всегда, даже если закрывали ранее)
        setTimeout(function () {
            tooltip.classList.add('is-visible');
        }, 1000);

        // Закрытие по крестику
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                tooltip.classList.remove('is-visible');
            });
        }

        // Скрытие при фокусе на input
        if (searchInput) {
            searchInput.addEventListener('focus', function () {
                tooltip.classList.remove('is-visible');
            });
        }
    }

    function CatalogSearch(input) {
        this.input = input;
        this.wrapper = input.closest('.catalog-search-wrapper');
        this.resultsContainer = this.wrapper.querySelector('.catalog-search-results');
        this.loader = this.wrapper.querySelector('.catalog-search-loader');
        this.clearBtn = this.wrapper.querySelector('.catalog-search-clear');

        // Параметры
        this.ajaxPath = input.dataset.ajaxPath;
        this.iblockIds = JSON.parse(input.dataset.iblockIds || '[]');
        this.minLength = parseInt(input.dataset.minLength) || 2;
        this.maxResults = parseInt(input.dataset.maxResults) || 10;
        this.showPrice = input.dataset.showPrice === 'Y';

        // Состояние
        this.debounceTimer = null;
        this.currentRequest = null;
        this.activeIndex = -1;
        this.results = [];

        this.bindEvents();
    }

    CatalogSearch.prototype.bindEvents = function () {
        var self = this;

        // Ввод в поле поиска
        this.input.addEventListener('input', function (e) {
            clearTimeout(self.debounceTimer);

            var query = e.target.value.trim();

            // Показываем/скрываем кнопку очистки
            self.clearBtn.style.display = query ? 'flex' : 'none';

            if (query.length === 0) {
                self.hideResults();
                return;
            }

            if (query.length < self.minLength) {
                return;
            }

            // Debounce
            self.debounceTimer = setTimeout(function () {
                self.search(query);
            }, 400);
        });

        // Фокус на поле
        this.input.addEventListener('focus', function () {
            if (self.results.length > 0) {
                self.resultsContainer.style.display = 'block';
            }
        });

        // Навигация клавиатурой
        this.input.addEventListener('keydown', function (e) {
            if (!self.resultsContainer.style.display || self.resultsContainer.style.display === 'none') {
                return;
            }

            var items = self.resultsContainer.querySelectorAll('.catalog-search-item');

            // Стрелка вниз
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                self.activeIndex = Math.min(self.activeIndex + 1, items.length - 1);
                self.highlightItem(items);
            }

            // Стрелка вверх
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                self.activeIndex = Math.max(self.activeIndex - 1, -1);
                self.highlightItem(items);
            }

            // Enter
            if (e.key === 'Enter') {
                if (self.activeIndex >= 0 && items[self.activeIndex]) {
                    // Есть выбранный элемент - переходим на товар
                    e.preventDefault();
                    items[self.activeIndex].click();
                }
                // Иначе форма отправится сама на /search/?q=...
            }

            // Escape
            if (e.key === 'Escape') {
                self.hideResults();
                self.input.blur();
            }
        });

        // Кнопка очистки
        this.clearBtn.addEventListener('click', function () {
            self.input.value = '';
            self.clearBtn.style.display = 'none';
            self.hideResults();
            self.input.focus();
        });

        // Клик вне области поиска
        document.addEventListener('click', function (e) {
            if (!self.wrapper.contains(e.target)) {
                self.hideResults();
            }
        });
    };

    CatalogSearch.prototype.search = function (query) {
        var self = this;

        // Отменяем предыдущий запрос
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        // Показываем загрузку
        this.loader.style.display = 'flex';

        // Создаем FormData
        var formData = new FormData();
        formData.append('query', query);
        formData.append('iblock_ids', JSON.stringify(this.iblockIds));
        formData.append('min_length', this.minLength);
        formData.append('max_results', this.maxResults);
        formData.append('show_price', this.showPrice ? 'Y' : 'N');

        // AJAX-запрос
        this.currentRequest = new XMLHttpRequest();
        this.currentRequest.open('POST', this.ajaxPath, true);

        this.currentRequest.onload = function () {
            self.loader.style.display = 'none';

            if (this.status >= 200 && this.status < 400) {
                try {
                    var data = JSON.parse(this.response);

                    if (data.success) {
                        self.results = data.items;
                        self.renderResults(query);
                    } else {
                        console.error('Search error:', data.error);
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                }
            }
        };

        this.currentRequest.onerror = function () {
            self.loader.style.display = 'none';
            console.error('Request error');
        };

        this.currentRequest.send(formData);
    };

    CatalogSearch.prototype.renderResults = function (query) {
        var self = this;
        this.activeIndex = -1;

        if (this.results.length === 0) {
            this.resultsContainer.innerHTML =
                '<div class="catalog-search-empty">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' +
                '</svg>' +
                '<div>Ничего не найдено</div>' +
                '</div>';
            this.resultsContainer.style.display = 'block';
            return;
        }

        var html = '';
        this.results.forEach(function (item) {
            var imageSrc = item.image || '';
            var imageHtml = imageSrc
                ? '<img src="' + imageSrc + '" alt="' + self.escapeHtml(item.name) + '" class="catalog-search-item-image">'
                : '<div class="catalog-search-item-image-placeholder">📦</div>';

            var nameWithHighlight = self.highlightText(item.name, query);

            html +=
                '<a href="' + item.url + '" class="catalog-search-item">' +
                imageHtml +
                '<div class="catalog-search-item-info">' +
                '<div class="catalog-search-item-name">' + nameWithHighlight + '</div>' +
                '<div class="catalog-search-item-category">' + self.escapeHtml(item.iblock_name) + '</div>' +
                (item.price ? '<div class="catalog-search-item-price">' + item.price + '</div>' : '') +
                '</div>' +
                '</a>';
        });

        this.resultsContainer.innerHTML = html;
        this.resultsContainer.style.display = 'block';
    };

    CatalogSearch.prototype.highlightItem = function (items) {
        items.forEach(function (item, index) {
            item.classList.toggle('active', index === this.activeIndex);
        }, this);

        // Прокручиваем к активному элементу
        if (this.activeIndex >= 0 && items[this.activeIndex]) {
            var item = items[this.activeIndex];
            var container = this.resultsContainer;
            var itemTop = item.offsetTop;
            var itemBottom = itemTop + item.offsetHeight;
            var containerTop = container.scrollTop;
            var containerBottom = containerTop + container.clientHeight;

            if (itemTop < containerTop) {
                container.scrollTop = itemTop;
            } else if (itemBottom > containerBottom) {
                container.scrollTop = itemBottom - container.clientHeight;
            }
        }
    };

    CatalogSearch.prototype.highlightText = function (text, query) {
        var escapedText = this.escapeHtml(text);
        var escapedQuery = this.escapeHtml(query);
        var regex = new RegExp('(' + escapedQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escapedText.replace(regex, '<mark>$1</mark>');
    };

    CatalogSearch.prototype.hideResults = function () {
        this.resultsContainer.style.display = 'none';
        this.activeIndex = -1;
    };

    CatalogSearch.prototype.escapeHtml = function (text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };
})();

