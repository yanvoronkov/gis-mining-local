// sliders.js
document.addEventListener('DOMContentLoaded', function () {
	// Инициализация всех слайдеров на странице, кроме stats
	function initializeSliders() {
		const sliders = document.querySelectorAll('.slider:not(.stats__slider)');

		sliders.forEach(slider => {
			const sliderContainer = slider.querySelector('.slider__container');
			const sliderDots = slider.querySelectorAll('.slider__dot');

			if (sliderContainer && sliderDots.length > 0) {
				const sliderItems = sliderContainer.querySelectorAll('.slider__item');
				let currentSlide = 0;
				let slideInterval;

				// Установка активного слайда
				function setActiveSlide(index) {
					// Обновляем положение слайдера
					const slideWidth = sliderItems[0].offsetWidth;
					sliderContainer.scrollTo({
						left: slideWidth * index,
						behavior: 'smooth'
					});

					// Обновляем активную точку
					sliderDots.forEach((dot, i) => {
						dot.classList.toggle('active', i === index);
					});

					currentSlide = index;
				}

				// Обработчик клика по точкам
				sliderDots.forEach((dot, index) => {
					dot.addEventListener('click', () => {
						setActiveSlide(index);
					});
				});

				// Обработчик прокрутки слайдера
				sliderContainer.addEventListener('scroll', () => {
					const slideWidth = sliderItems[0].offsetWidth;
					const scrollPosition = sliderContainer.scrollLeft;
					const newSlideIndex = Math.round(scrollPosition / slideWidth);

					if (newSlideIndex !== currentSlide && newSlideIndex < sliderItems.length) {
						sliderDots.forEach((dot, i) => {
							dot.classList.toggle('active', i === newSlideIndex);
						});
						currentSlide = newSlideIndex;
					}
				});

				// Автоматическая прокрутка слайдера
				function startAutoSlide() {
					slideInterval = setInterval(() => {
						const nextSlide = (currentSlide + 1) % sliderItems.length;
						setActiveSlide(nextSlide);
					}, 5000); // Смена слайда каждые 5 секунд
				}

				function stopAutoSlide() {
					clearInterval(slideInterval);
				}

				// Запускаем автоматическую прокрутку
				startAutoSlide();

				// Останавливаем при взаимодействии пользователя
				sliderContainer.addEventListener('mouseenter', stopAutoSlide);
				sliderContainer.addEventListener('touchstart', stopAutoSlide);
				sliderContainer.addEventListener('mousedown', stopAutoSlide);

				// Возобновляем после прекращения взаимодействия
				sliderContainer.addEventListener('mouseleave', startAutoSlide);
				sliderContainer.addEventListener('touchend', stopAutoSlide);
				sliderContainer.addEventListener('mouseup', startAutoSlide);
			}
		});
	}

	// Вызываем функцию инициализации слайдеров
	initializeSliders();
});
