document.addEventListener('DOMContentLoaded', () => {

    //Вешаем обработчик ссылки на карточку слайдера
    const clickableCards = document.querySelectorAll('.package-deal-card[data-href]');

    clickableCards.forEach(card => {
        card.style.cursor = 'pointer'; // Показываем, что элемент кликабельный
        card.addEventListener('click', function (event) {
            //Убедимся, что клик не был по другой ссылке или кнопке внутри карточки(если они есть)
            if (event.target.closest('a, button')) {
                return;
            }
            const link = this.dataset.href;
            if (link) {
                // window.location.href = link; // Обычный переход
                window.open(link, '_blank'); // Открыть в новой вкладке (если нужно)
            }
        });
    });


    const swiperInstances = new Map(); // Хранилище для экземпляров Swiper {element: instance}

    function initOrDestroySwiper(sliderElement) {
        if (!sliderElement) return;

        // --- Получение настроек из data-атрибутов ---
        let swiperOptions = {};
        const optionsString = sliderElement.dataset.swiperOptions;
        if (optionsString) {
            try {
                swiperOptions = JSON.parse(optionsString);
            } catch (e) {
                console.error('Ошибка парсинга JSON из data-swiper-options для элемента:', sliderElement, e);
                return;
            }
        }

        const destroyBreakpoint = sliderElement.dataset.swiperDestroyBreakpoint
            ? parseInt(sliderElement.dataset.swiperDestroyBreakpoint, 10)
            : Infinity;

        const initBreakpoint = sliderElement.dataset.swiperInitBreakpoint // Если будете использовать
            ? parseInt(sliderElement.dataset.swiperInitBreakpoint, 10)
            : 0;


        let currentSwiperInstance = swiperInstances.get(sliderElement);
        const currentWindowWidth = window.innerWidth;

        const shouldBeActive = currentWindowWidth >= initBreakpoint && currentWindowWidth < destroyBreakpoint;

        if (shouldBeActive) {
            if (!currentSwiperInstance || currentSwiperInstance.destroyed) {
                try {
                    currentSwiperInstance = new Swiper(sliderElement, swiperOptions);
                    swiperInstances.set(sliderElement, currentSwiperInstance);
                    // console.log('Swiper Initialized/Re-initialized for:', sliderElement.classList[1] || sliderElement.id);
                } catch (e) {
                    console.error('Ошибка инициализации Swiper для элемента:', sliderElement, e, 'Опции:', swiperOptions);
                }
            }
        } else {
            if (currentSwiperInstance && !currentSwiperInstance.destroyed) {
                if (currentSwiperInstance.autoplay && typeof currentSwiperInstance.autoplay.stop === 'function') { // Добавил проверку на функцию
                    currentSwiperInstance.autoplay.stop();
                }
                currentSwiperInstance.destroy(true, true);
                swiperInstances.delete(sliderElement);
                // console.log('Swiper Destroyed for:', sliderElement.classList[1] || sliderElement.id);
            }
        }
    }

    function handleAllSliders() {
        const sliderElements = document.querySelectorAll('.js-swiper-slider');
        sliderElements.forEach(sliderEl => {
            initOrDestroySwiper(sliderEl);
        });
    }

    handleAllSliders();

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            handleAllSliders();
        }, 250);
    });
});
