document.addEventListener('DOMContentLoaded', function() {
    // Найти все формы на странице
    var forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            // Проверяем, есть ли уже поле page_url
            var existingUrlField = form.querySelector('input[name="page_url"]');
            
            if (!existingUrlField) {
                // Создаем скрытое поле для URL страницы
                var urlField = document.createElement('input');
                urlField.type = 'hidden';
                urlField.name = 'page_url';
                urlField.value = window.location.href;
                form.appendChild(urlField);
            } else {
                // Если поле уже есть, обновляем его значение
                existingUrlField.value = window.location.href;
            }
        });
    });
});