// main.js
document.addEventListener('DOMContentLoaded', function () {
	// Плавная прокрутка к разделам при клике по навигации
	document.querySelectorAll('a[href^="#"]').forEach(anchor => {
		anchor.addEventListener('click', function (e) {
			e.preventDefault();

			const targetId = this.getAttribute('href');
			const targetElement = document.querySelector(targetId);

			if (targetElement) {
				window.scrollTo({
					top: targetElement.offsetTop - 70,
					behavior: 'smooth'
				});
			}
		});
	});
});
