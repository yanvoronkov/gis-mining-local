// Файл: /local/templates/main/components/bitrix/menu/header_desktop/script.js

// Мы используем такую конструкцию, чтобы наш код не конфликтовал с другими скриптами
(function() {
    // Ждем, пока вся страница (DOM) будет готова
    document.addEventListener('DOMContentLoaded', function() {

        // Ищем наш родительский элемент для мегаменю
        const megamenuContainer = document.querySelector('.desktop-menu__item--has-megamenu');

        // Если на странице нет такого меню, ничего не делаем
        if (!megamenuContainer) {
            return;
        }

        // Находим кнопки управления внутри него
        const openButton = megamenuContainer.querySelector('.desktop-menu__link--button');
        const closeButton = megamenuContainer.querySelector('.megamenu__close-button');
        const megamenuPanel = megamenuContainer.querySelector('.megamenu');

        // Функция для открытия меню
        const openMegamenu = () => {
            megamenuContainer.classList.add('is-open');
            openButton.setAttribute('aria-expanded', 'true');
        };

        // Функция для закрытия меню
        const closeMegamenu = () => {
            megamenuContainer.classList.remove('is-open');
            openButton.setAttribute('aria-expanded', 'false');
        };

        // Вешаем событие на кнопку "Другие услуги"
        if (openButton) {
            openButton.addEventListener('click', (event) => {
                event.stopPropagation(); // Предотвращаем "всплытие" клика до document
                // Если меню уже открыто - закрываем, если закрыто - открываем
                if (megamenuContainer.classList.contains('is-open')) {
                    closeMegamenu();
                } else {
                    openMegamenu();
                }
            });
        }

        // Вешаем событие на кнопку "Закрыть" (крестик)
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                closeMegamenu();
            });
        }

        // Вешаем событие на весь документ, чтобы закрывать меню при клике вне его
        document.addEventListener('click', (event) => {
            // Проверяем, открыто ли меню и был ли клик НЕ внутри контейнера меню
            if (megamenuContainer.classList.contains('is-open') && !megamenuContainer.contains(event.target)) {
                closeMegamenu();
            }
        });

        // Закрываем меню по нажатию на клавишу Escape
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && megamenuContainer.classList.contains('is-open')) {
                closeMegamenu();
            }
        });
    });
})();