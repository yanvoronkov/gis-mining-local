document.addEventListener('DOMContentLoaded', () => {
    // Находим все необходимые элементы
    const mediaSliderContainer = document.querySelector('.media-mentions__slider');
    const filterButtons = document.querySelectorAll('.media-filter-btn');
    const allSlides = document.querySelectorAll('.media-mentions__slider .slider-item');

    // Если на странице нет слайдера, ничего не делаем
    if (!mediaSliderContainer || filterButtons.length === 0 || allSlides.length === 0) {
        return;
    }

    // Состояние слайдера
    let activeFilter = 'rbk'; // Исправлено: соответствует активной кнопке в HTML
    let currentIndex = 0;
    let visibleSlides = [];
    let autoplayInterval;
    let autoplayTimeout; // Таймер для перезапуска автоплея

    // --- ФУНКЦИИ ---

    // Обновляет видимость слайдов в соответствии с фильтром
    function updateVisibleSlides() {
        visibleSlides = [];
        allSlides.forEach(slide => {
            if (slide.dataset.filter === activeFilter) {
                slide.classList.remove('is-hidden');
                visibleSlides.push(slide);
            } else {
                slide.classList.add('is-hidden');
            }
        });
    }

    // Переходит к слайду по индексу
    function goToSlide(index, behavior = 'smooth') {
        if (visibleSlides.length === 0 || !visibleSlides[index]) return;

        const newScrollPosition = visibleSlides[index].offsetLeft - mediaSliderContainer.offsetLeft;
        mediaSliderContainer.scrollTo({
            left: newScrollPosition,
            behavior: behavior
        });
        currentIndex = index;
    }

    // Останавливает автоплей
    function stopAutoplay() {
        clearInterval(autoplayInterval);
        clearTimeout(autoplayTimeout); // Также очищаем таймер перезапуска
    }

    // Запускает автоплей
    function startAutoplay() {
        stopAutoplay(); // Сначала всегда останавливаем предыдущий
        if (visibleSlides.length > 1) { // Запускаем только если есть что крутить
            autoplayInterval = setInterval(() => {
                // Вычисляем следующий индекс для БЕСКОНЕЧНОЙ прокрутки
                let nextIndex = (currentIndex + 1) % visibleSlides.length;
                goToSlide(nextIndex);
            }, 2000); // Интервал 3 секунды
        }
    }

    // --- ЛОГИКА И ОБРАБОТЧИКИ ---

    // Клик по кнопкам-фильтрам (улучшенная обработка для touch устройств)
    filterButtons.forEach(button => {
        // Добавляем обработчики для разных типов событий
        const handleFilterClick = (event) => {
            event.preventDefault();
            event.stopPropagation();

            console.log('Filter button clicked:', button.dataset.filter);

            if (button.classList.contains('is-active')) {
                console.log('Button already active, skipping');
                return;
            }

            filterButtons.forEach(btn => btn.classList.remove('is-active'));
            button.classList.add('is-active');

            activeFilter = button.dataset.filter;
            console.log('Switching to filter:', activeFilter);

            updateVisibleSlides();
            console.log('Updated visible slides count:', visibleSlides.length);

            goToSlide(0, 'auto'); // Мгновенный переход на первый слайд
            startAutoplay();      // Перезапускаем автоплей
        };

        // Обработчик для клика
        button.addEventListener('click', handleFilterClick);

        // Обработчик для touch событий (для мобильных устройств)
        button.addEventListener('touchend', (event) => {
            // Предотвращаем двойное срабатывание
            event.preventDefault();
            handleFilterClick(event);
        }, { passive: false });
    });

    // Обработчик ручной прокрутки (пальцем или колесиком)
    let isScrolling;
    mediaSliderContainer.addEventListener('scroll', () => {
        // Когда пользователь начинает скроллить, останавливаем автоплей
        stopAutoplay();

        // Очищаем предыдущий таймер, чтобы он не сработал во время скролла
        window.clearTimeout(isScrolling);

        // Устанавливаем новый таймер. Если пользователь не скроллит
        // в течение 5 секунд, мы считаем, что он закончил, и перезапускаем автоплей.
        isScrolling = setTimeout(() => {
            console.log("Пользователь закончил скролл, перезапускаем автоплей.");

            // Определяем, на каком слайде мы остановились
            let minDistance = Infinity;
            let newCurrentIndex = 0;
            visibleSlides.forEach((slide, index) => {
                const distance = Math.abs(slide.getBoundingClientRect().left - mediaSliderContainer.getBoundingClientRect().left);
                if (distance < minDistance) {
                    minDistance = distance;
                    newCurrentIndex = index;
                }
            });
            currentIndex = newCurrentIndex; // Обновляем текущий индекс

            startAutoplay(); // Перезапускаем автоплей
        }, 5000); // Задержка в 5 секунд перед перезапуском

    }, { passive: true });

    // Инициализация слайдера при загрузке страницы
    function initializeMediaSlider() {
        const initialActiveButton = document.querySelector('.media-filter-btn.is-active');
        if (initialActiveButton) {
            activeFilter = initialActiveButton.dataset.filter;
            console.log('Media slider initialized with filter:', activeFilter);
        }

        updateVisibleSlides();
        console.log('Visible slides count:', visibleSlides.length);

        if (visibleSlides.length > 0) {
            goToSlide(0, 'auto');
            startAutoplay();
        }
    }

    initializeMediaSlider();
});