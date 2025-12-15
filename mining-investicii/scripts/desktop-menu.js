// desktop-menu.js
document.addEventListener('DOMContentLoaded', function () {
	// Прокрутка для desktop-menu
	const desktopMenu = document.querySelector('.desktop-menu');
	const heroSection = document.querySelector('.hero-section');

	if (desktopMenu && heroSection && window.innerWidth >= 768) {
		// Функция обработки прокрутки
		function handleScroll() {
			// Добавляем класс scrolled при прокрутке
			if (window.scrollY > 0) {
				desktopMenu.classList.add('scrolled');
			} else {
				desktopMenu.classList.remove('scrolled');
			}

			// Проверяем, вышли ли из hero-section
			const heroRect = heroSection.getBoundingClientRect();
			const heroBottom = heroRect.bottom;
			const menuHeight = desktopMenu.offsetHeight;

			if (heroBottom <= menuHeight) {
				desktopMenu.classList.add('out-of-hero');
			} else {
				desktopMenu.classList.remove('out-of-hero');
			}
		}

		// Вызываем функцию при загрузке и при прокрутке
		handleScroll();
		window.addEventListener('scroll', handleScroll);
	}
});
