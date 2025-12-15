// Файл: /local/templates/main/components/bitrix/menu/header_mobile/script.js
// Отвечает за всю логику мобильного меню: открытие панели и аккордеон.

(function() {
    // Ждем, пока вся страница (DOM) будет готова
    document.addEventListener('DOMContentLoaded', function() {

        // --- БЛОК 1: Логика открытия/закрытия основной панели меню ---

        const mobileMenuButton = document.querySelector('.header__mobile-menu-toggle');
        const mobilePanel = document.querySelector('.mobile-menu-panel');

        if (mobileMenuButton && mobilePanel) {
            // Вешаем событие на кнопку "бургер"
            mobileMenuButton.addEventListener('click', () => {
                mobileMenuButton.classList.toggle('is-active');
                mobilePanel.classList.toggle('is-open');
                // Блокируем прокрутку страницы, когда меню открыто
                document.body.classList.toggle('no-scroll');
            });

            // Ищем кнопку "Закрыть" внутри панели
            const mobileMenuCloseButton = mobilePanel.querySelector('.mobile-menu-panel__close-button');
            if (mobileMenuCloseButton) {
                // Вешаем событие на кнопку "крестик"
                mobileMenuCloseButton.addEventListener('click', () => {
                    mobileMenuButton.classList.remove('is-active');
                    mobilePanel.classList.remove('is-open');
                    document.body.classList.remove('no-scroll');
                });
            }
        }


        // --- БЛОК 2: Логика для вложенных меню (аккордеон) ---

        // Ищем контейнер нашего мобильного меню (внутри панели)
        const navContainer = document.querySelector('.mobile-menu-panel__nav');
        if (!navContainer) {
            return; // Если меню на странице нет, дальше не выполняем
        }

        // Находим ВСЕ кнопки, которые должны открывать подменю
        const submenuToggles = navContainer.querySelectorAll('button[aria-controls]');

        submenuToggles.forEach(function(clickedToggle) {
            // Вешаем обработчик клика на каждую кнопку
            clickedToggle.addEventListener('click', function() {

                const targetSubmenuId = this.getAttribute('aria-controls');
                const targetSubmenu = document.getElementById(targetSubmenuId);

                if (!targetSubmenu) {
                    return; // Если подменю не найдено, выходим
                }

                // Проверяем, было ли текущее подменю открыто до клика
                const isCurrentlyExpanded = this.getAttribute('aria-expanded') === 'true';

                // Закрываем все остальные подменю
                submenuToggles.forEach(function(otherToggle) {
                    if (otherToggle !== clickedToggle) {
                        otherToggle.setAttribute('aria-expanded', 'false');
                        const otherSubmenu = document.getElementById(otherToggle.getAttribute('aria-controls'));
                        if (otherSubmenu) {
                            otherSubmenu.hidden = true;
                        }
                    }
                });

                // Открываем или закрываем наше целевое подменю
                if (isCurrentlyExpanded) {
                    this.setAttribute('aria-expanded', 'false');
                    targetSubmenu.hidden = true;
                } else {
                    this.setAttribute('aria-expanded', 'true');
                    targetSubmenu.hidden = false;
                }
            });
        });
    });
})();