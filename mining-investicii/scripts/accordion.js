// accordion.js
document.addEventListener('DOMContentLoaded', function () {
	// Аккордеон для FAQ
	const accordionItems = document.querySelectorAll('.accordion__item');

	if (accordionItems.length > 0) {
		accordionItems.forEach(item => {
			const header = item.querySelector('.accordion__header');
			const content = item.querySelector('.accordion__content');

			header.addEventListener('click', function () {
				// Проверяем, активен ли текущий элемент
				const isActive = item.classList.contains('active');

				// Закрываем все элементы
				accordionItems.forEach(accordionItem => {
					const itemContent = accordionItem.querySelector('.accordion__content');
					accordionItem.classList.remove('active');
					itemContent.style.maxHeight = '0'; // Сбрасываем maxHeight до 0
				});

				// Если элемент не был активен, открываем его
				if (!isActive) {
					item.classList.add('active');
					content.style.maxHeight = content.scrollHeight + "px";
					// Добавляем обработчик события transitionend
					content.addEventListener('transitionend', function handler() {
						// Пересчитываем maxHeight после окончания анимации
						content.style.maxHeight = content.scrollHeight + "px";
						// Удаляем обработчик, чтобы он не срабатывал каждый раз
						content.removeEventListener('transitionend', handler);
					});
				}
			});
		});
	}
});
