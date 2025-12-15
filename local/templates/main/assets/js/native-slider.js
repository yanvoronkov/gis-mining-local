document.addEventListener('DOMContentLoaded', () => {

    class NativeSlider {
        constructor(sliderElement) {
            if (!sliderElement || sliderElement.dataset.nativeSliderInit === 'true') return;
            sliderElement.dataset.nativeSliderInit = 'true';

            sliderElement.sliderInstance = this;

            this.sliderEl = sliderElement;
            this.wrapper = this.sliderEl.querySelector('.native-slider__wrapper');
            this.slides = this.sliderEl.querySelectorAll('.native-slider__slide');

            if (!this.wrapper || this.slides.length === 0) return;

            // Парсинг ПОВЕДЕНЧЕСКИХ опций
            try {
                this.options = JSON.parse(this.sliderEl.dataset.nativeSliderOptions || '{}');
            } catch (e) {
                this.options = {};
            }

            this.prevButton = this.options.navigation?.prevEl ? this.sliderEl.querySelector(this.options.navigation.prevEl) : null;
            this.nextButton = this.options.navigation?.nextEl ? this.sliderEl.querySelector(this.options.navigation.nextEl) : null;
            this.paginationContainer = this.options.pagination?.el ? this.sliderEl.querySelector(this.options.pagination.el) : null;

            // Состояние
            this.currentIndex = 0;
            this.autoplayInterval = null;
            this.interactionTimeout = null;

            // Управление свайпом: по умолчанию перелистываем ровно на один слайд за жест
            // Можно отключить, передав { oneSlidePerSwipe: false } в data-native-slider-options
            this.oneSlidePerSwipe = this.options.oneSlidePerSwipe !== false;
            this.touchStartX = null;
            this.touchStartY = null;
            this.touchStartIndex = 0;
            this.isTouching = false;
            this.touchStartScrollLeft = 0;
            this.horizontalSwipe = false;
            this.verticalSwipe = false;

            this.init();
        }

        init() {
            this.setupEventListeners();
            this.setupPagination();
            this.startAutoplay();
            // Установим корректную видимость стрелок при загрузке
            this.updateArrowsVisibility();
        }

        startAutoplay() {
            this.stopAutoplay();
            // Автоплей запускается только если есть опция и больше одного слайда
            if (!this.options.autoplay?.delay || this.slides.length <= 1) return;

            this.autoplayInterval = setInterval(() => {
                this.move(1);
            }, this.options.autoplay.delay);
        }

        stopAutoplay() {
            clearInterval(this.autoplayInterval);
        }

        handleInteraction() {
            this.stopAutoplay();
            clearTimeout(this.interactionTimeout);
            if (!this.options.autoplay?.delay) return;

            const restartDelay = this.options.autoplay.delay + 2000;
            this.interactionTimeout = setTimeout(() => {
                this.startAutoplay();
            }, restartDelay);
        }

        setupEventListeners() {
            const handleAndMove = (direction) => { this.handleInteraction(); this.move(direction); };
            if (this.prevButton) this.prevButton.onclick = () => handleAndMove(-1);
            if (this.nextButton) this.nextButton.onclick = () => handleAndMove(1);

            if (this.options.autoplay?.pauseOnMouseEnter) {
                this.sliderEl.addEventListener('mouseenter', () => this.stopAutoplay());
                this.sliderEl.addEventListener('mouseleave', () => this.handleInteraction());
            }

            // Этот обработчик нужен для обновления пагинации при ручном скролле
            this.wrapper.addEventListener('scroll', () => this.updateCurrentIndexOnScroll(), { passive: true });

            // Обработчик для кликов по превью-изображениям (карусели)
            this.setupThumbnailNavigation();

            // Обработчик для кликов по изображениям для открытия полноэкранного режима
            this.setupFullscreenTriggers();

            // Свайпы на мобильных: ограничиваем перемещение одним слайдом
            if (this.oneSlidePerSwipe) {
                this.wrapper.addEventListener('touchstart', (e) => {
                    if (!e.touches || e.touches.length === 0) return;
                    this.handleInteraction();
                    this.isTouching = true;
                    this.touchStartX = e.touches[0].clientX;
                    this.touchStartY = e.touches[0].clientY;
                    this.touchStartIndex = this.currentIndex;
                    this.touchStartScrollLeft = this.wrapper.scrollLeft;
                    this.horizontalSwipe = false;
                    this.verticalSwipe = false;
                }, { passive: true });

                // Блокируем нативный горизонтальный скролл, чтобы не было промежуточных состояний
                this.wrapper.addEventListener('touchmove', (e) => {
                    if (!this.isTouching) return;
                    if (!(e.touches && e.touches.length)) return;

                    const currentX = e.touches[0].clientX;
                    const currentY = e.touches[0].clientY;
                    const dx = currentX - (this.touchStartX ?? currentX);
                    const dy = currentY - (this.touchStartY ?? currentY);

                    // Определяем намерение жеста один раз по порогу
                    const intentThreshold = 10; // px
                    if (!this.horizontalSwipe && !this.verticalSwipe) {
                        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > intentThreshold) {
                            this.horizontalSwipe = true;
                        } else if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > intentThreshold) {
                            this.verticalSwipe = true;
                        }
                    }

                    if (this.verticalSwipe) {
                        // Разрешаем вертикальную прокрутку страницы, не мешаем событию
                        this.isTouching = false; // чтобы touchend не переключал слайд
                        return;
                    }

                    if (this.horizontalSwipe) {
                        // Блокируем горизонтальный дрейф слайдов до фиксации в touchend
                        e.preventDefault();
                        this.wrapper.scrollLeft = this.touchStartScrollLeft;
                    }
                }, { passive: false });

                this.wrapper.addEventListener('touchend', (e) => {
                    if (!this.isTouching) return;
                    this.isTouching = false;
                    const touch = e.changedTouches && e.changedTouches[0];
                    if (!touch || this.touchStartX === null) return;

                    const deltaX = touch.clientX - this.touchStartX;
                    const threshold = 20; // минимальная амплитуда в px для смены слайда

                    if (this.verticalSwipe) {
                        // Жест был вертикальным — ничего не делаем
                        this.touchStartX = null;
                        this.touchStartY = null;
                        this.touchStartScrollLeft = 0;
                        this.horizontalSwipe = false;
                        this.verticalSwipe = false;
                        return;
                    }

                    // Вычисляем направление и целевой индекс на основе стартового индекса, а не инерции скролла
                    let targetIndex = this.touchStartIndex;
                    if (Math.abs(deltaX) > threshold) {
                        targetIndex = this.touchStartIndex + (deltaX < 0 ? 1 : -1);
                    }

                    if (this.options.loop) {
                        if (targetIndex >= this.slides.length) targetIndex = 0;
                        if (targetIndex < 0) targetIndex = this.slides.length - 1;
                    } else {
                        targetIndex = Math.max(0, Math.min(targetIndex, this.slides.length - 1));
                    }

                    // Жестко останавливаем инерцию и позиционируем на нужном слайде
                    // Временно отключаем snap-поведение, чтобы не было промежуточных прыжков
                    const prevSnap = this.wrapper.style.scrollSnapType;
                    this.wrapper.style.scrollSnapType = 'none';
                    this.goTo(targetIndex, 'auto');
                    // Возвращаем snap чуть позже, когда скролл применится
                    setTimeout(() => {
                        this.wrapper.style.scrollSnapType = prevSnap || '';
                    }, 0);

                    // Сбрасываем стартовые значения
                    this.touchStartX = null;
                    this.touchStartY = null;
                    this.touchStartScrollLeft = 0;
                    this.horizontalSwipe = false;
                    this.verticalSwipe = false;
                }, { passive: true });
            }
        }

        updateCurrentIndexOnScroll() {
            let minDistance = Infinity;
            let newCurrentIndex = 0;
            const wrapperRect = this.wrapper.getBoundingClientRect();

            this.slides.forEach((slide, i) => {
                const slideRect = slide.getBoundingClientRect();
                const distance = Math.abs(slideRect.left - wrapperRect.left);
                if (distance < minDistance) {
                    minDistance = distance;
                    newCurrentIndex = i;
                }
            });

            if (this.currentIndex !== newCurrentIndex) {
                this.currentIndex = newCurrentIndex;
                this.updatePagination();
            }
        }

        setupPagination() {
            // Если контейнер для пагинации не указан в опциях, ничего не делаем
            if (!this.paginationContainer) return;

            // Очищаем контейнер от старых точек, если они там были
            this.paginationContainer.innerHTML = '';

            // Если слайдов меньше или равно одному, пагинация не нужна
            if (this.slides.length <= 1) return;

            // Получаем класс для активной точки из опций, или используем 'is-active' по умолчанию
            const activeClass = this.options.pagination?.bulletActiveClass || 'is-active';

            // Получаем класс для обычной точки из опций, или используем 'dot' по умолчанию
            const bulletClass = this.options.pagination?.bulletClass || 'dot';

            // Создаем точки для каждого слайда
            this.slides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.classList.add(bulletClass);
                // Добавляем data-атрибут для легкого связывания
                dot.dataset.slideTo = index;

                // Добавляем ARIA-атрибуты для доступности
                dot.setAttribute('role', 'tab');
                dot.setAttribute('aria-label', `Перейти к слайду ${index + 1}`);

                this.paginationContainer.appendChild(dot);
            });

            // Если пагинация кликабельна, вешаем один обработчик на весь контейнер
            if (this.options.pagination?.clickable === true) {
                this.paginationContainer.classList.add('is-clickable');
                this.paginationContainer.addEventListener('click', (event) => {
                    // Используем делегирование событий
                    const clickedDot = event.target.closest(`.${bulletClass}`);
                    if (clickedDot && clickedDot.dataset.slideTo) {
                        this.handleInteraction();
                        const index = parseInt(clickedDot.dataset.slideTo, 10);
                        this.goTo(index);
                    }
                });
            }

            // Устанавливаем активную точку для первого слайда
            this.updatePagination();
        }

        updatePagination() {
            // Обновляем точки пагинации, если они есть
            if (this.paginationContainer) {
                const activeClass = this.options.pagination?.bulletActiveClass || 'is-active';
                const dots = this.paginationContainer.querySelectorAll(`.${this.options.pagination?.bulletClass || 'dot'}`);

                dots.forEach((dot, index) => {
                    dot.classList.toggle(activeClass, index === this.currentIndex);
                });
            }

            // Обновляем состояние превью-изображений
            this.updateThumbnailStates();

            // Обновляем видимость стрелок в зависимости от позиции
            this.updateArrowsVisibility();
        }

        setupThumbnailNavigation() {
            // Ищем карусель превью-изображений
            const thumbnailContainer = document.querySelector('.js-gallery-thumbnails');
            if (!thumbnailContainer) return;

            // Добавляем обработчик кликов на карусель
            thumbnailContainer.addEventListener('click', (event) => {
                const thumbnail = event.target.closest('.js-gallery-thumb');
                if (thumbnail && thumbnail.dataset.index) {
                    this.handleInteraction();
                    const index = parseInt(thumbnail.dataset.index, 10);
                    this.goTo(index);
                }
            });
        }

        // Метод для открытия полноэкранного режима
        openFullscreen(index) {
            if (window.fullscreenGallery) {
                window.fullscreenGallery.openGallery(index);
            }
        }

        // Настройка обработчиков для полноэкранного режима
        setupFullscreenTriggers() {
            // Проверяем, включен ли fullscreen (по умолчанию true)
            const enableFullscreen = this.options.enableFullscreen !== false;

            if (!enableFullscreen) {
                return; // Не добавляем обработчики, если fullscreen отключен
            }

            this.slides.forEach((slide, index) => {
                const image = slide.querySelector('img');
                if (image) {
                    image.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.openFullscreen(index);
                    });
                }
            });
        }

        updateThumbnailStates() {
            // Ищем карусель превью-изображений
            const thumbnailContainer = document.querySelector('.js-gallery-thumbnails');
            if (!thumbnailContainer) return;

            // Обновляем активное состояние превью-изображений
            const thumbnails = thumbnailContainer.querySelectorAll('.js-gallery-thumb');
            thumbnails.forEach((thumbnail, index) => {
                thumbnail.classList.toggle('is-active', index === this.currentIndex);
            });
        }

        move(direction) {
            let newIndex = this.currentIndex + direction;
            if (this.options.loop) {
                if (newIndex >= this.slides.length) newIndex = 0;
                if (newIndex < 0) newIndex = this.slides.length - 1;
            } else {
                newIndex = Math.max(0, Math.min(newIndex, this.slides.length - 1));
            }
            this.goTo(newIndex);
        }

        goTo(index, behavior = 'smooth') {
            if (!this.slides[index]) return;

            // 1. СНАЧАЛА обновляем состояние и UI
            this.currentIndex = index;
            this.updatePagination();

            // 2. ПОТОМ даем команду на скролл
            const wrapperWidth = this.wrapper.offsetWidth;
            const slideWidth = this.slides[index].offsetWidth;
            const slideLeft = this.slides[index].offsetLeft;

            let scrollPosition = slideLeft;
            // Проверяем, есть ли опция centeredSlides в JSON
            if (this.options.centeredSlides) {
                scrollPosition = slideLeft - (wrapperWidth / 2) + (slideWidth / 2);
            }

            this.wrapper.scrollTo({ left: scrollPosition, behavior: behavior });
        }

        // Скрывать стрелки на крайних слайдах (если loop выключен)
        updateArrowsVisibility() {
            if (!this.prevButton && !this.nextButton) return;
            const isLoop = !!this.options.loop;

            if (isLoop) {
                if (this.prevButton) this.prevButton.style.display = 'flex';
                if (this.nextButton) this.nextButton.style.display = 'flex';
                return;
            }

            const atStart = this.currentIndex === 0;
            const atEnd = this.currentIndex === this.slides.length - 1;

            if (this.prevButton) {
                this.prevButton.style.display = atStart ? 'none' : 'flex';
            }
            if (this.nextButton) {
                this.nextButton.style.display = atEnd ? 'none' : 'flex';
            }
        }
    }

    // Инициализируем все слайдеры на странице
    document.querySelectorAll('.js-native-slider').forEach(sliderElement => {
        new NativeSlider(sliderElement);
    });

});