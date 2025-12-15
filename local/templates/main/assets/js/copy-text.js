$(document).ready(function () {
    // Функция для показа временного глобального уведомления
    function showGlobalCopyFeedback(message, isError = false, duration = 2000) {
        const $feedbackPopup = $('.copy-feedback-popup');
        if ($feedbackPopup.length) {
            $feedbackPopup.text(message);
            if (isError) {
                $feedbackPopup.css('background-color', '#f44336'); // Красный для ошибки
            } else {
                $feedbackPopup.css('background-color', '#4CAF50'); // Зеленый по умолчанию
            }
            $feedbackPopup.fadeIn();
            setTimeout(function () {
                $feedbackPopup.fadeOut();
            }, duration);
        } else {
            alert(message); // Фолбэк, если элемента .copy-feedback-popup нет
        }
    }

    // Обработчик клика на иконку копирования
    $(document).on('click', '.js-copy-icon', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const $icon = $(this);
        let textToCopy = '';

        // Ищем элемент для копирования.
        // Предпочтение отдается предыдущему сиблингу с классом .copyable-text.
        // Это работает для адреса, email и отдельных телефонов.
        let $copyTarget = $icon.prev('.copyable-text');

        if (!$copyTarget.length) {
            // Если не нашли как prev(), возможно, иконка и текст обернуты в общий div,
            // а .copyable-text является сиблингом этого div'а или самой иконки внутри него.
            // Для структуры с телефонами (каждый в своем div):
            $copyTarget = $icon.siblings('a.copyable-text');
            if (!$copyTarget.length) {
                // Для координат, где <a> является .copyable-text и содержит <span>
                const $parentContent = $icon.closest('.section-contacts__content');
                $copyTarget = $parentContent.find('a.copyable-text');
            }
        }


        if ($copyTarget.length) {
            if ($copyTarget.is('a')) {
                const href = $copyTarget.attr('href');
                // Для ссылок tel: и mailto: копируем значение без префикса
                if (href && href.startsWith('tel:')) {
                    textToCopy = href.substring(4); // Убираем "tel:"
                } else if (href && href.startsWith('mailto:')) {
                    textToCopy = href.substring(7); // Убираем "mailto:"
                } else if ($copyTarget.find('span').length > 0 && $copyTarget.closest('.section-contacts__content').find('.section-contacts__item-title p').text().includes('Координаты')) {
                    // Специальная логика для координат (если есть span внутри и это блок координат)
                    let coordText = "";
                    $copyTarget.find('span').each(function (index) {
                        if (index > 0 && coordText.length > 0) coordText += "\n"; // Перенос строки между спанами
                        coordText += $(this).text().trim();
                    });
                    textToCopy = coordText.trim();
                }
                else {
                    textToCopy = $copyTarget.text().trim(); // Иначе копируем видимый текст ссылки
                }
            } else { // Если это не <p> (например, адрес)
                textToCopy = $copyTarget.text().trim();
            }
        }

        if (textToCopy) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy)
                    .then(function () {
                        console.log('Скопировано (navigator.clipboard): ' + textToCopy);
                        showGlobalCopyFeedback('Скопировано!');
                    })
                    .catch(function (err) {
                        console.error('Не удалось скопировать через navigator.clipboard: ', err);
                        // Попытка скопировать через устаревший document.execCommand
                        fallbackCopyTextToClipboard(textToCopy);
                    });
            } else {
                // Совсем старый фолбэк, если navigator.clipboard не поддерживается
                console.warn('navigator.clipboard не поддерживается, используется fallback.');
                fallbackCopyTextToClipboard(textToCopy);
            }
        } else {
            console.warn('Не найден текст для копирования рядом с иконкой:', $icon[0]);
            showGlobalCopyFeedback('Не удалось определить текст для копирования', true, 3000);
        }
    });

    // Фолбэк-функция для копирования текста
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;

        // Стили, чтобы сделать textarea невидимым и не влияющим на макет
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.width = '2em';
        textArea.style.height = '2em';
        textArea.style.padding = '0';
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';
        textArea.style.background = 'transparent';

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            const msg = successful ? 'Скопировано!' : 'Ошибка копирования (execCommand failed)';
            console.log('Результат копирования (fallback): ' + msg + ' Текст: ' + text);
            showGlobalCopyFeedback(msg, !successful);
        } catch (err) {
            console.error('Ошибка при копировании через execCommand: ', err);
            showGlobalCopyFeedback('Ошибка копирования (execCommand exception)', true, 3000);
        }
        document.body.removeChild(textArea);
    }
});