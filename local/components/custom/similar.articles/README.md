# Компонент "Читайте также" (Похожие статьи)

## Описание

Универсальный компонент для вывода похожих статей на детальных страницах блога, новостей и других разделов.

## Особенности

- ✅ Выводит 5 последних статей (количество настраивается)
- ✅ Автоматически исключает текущую статью
- ✅ Адаптивный дизайн: слайдер на мобильных, грид на больших экранах
- ✅ Легко интегрируется в разные разделы сайта

## Адаптивность

### Мобильные устройства (< 768px)
- Горизонтальный слайдер с прокруткой
- Swipe-навигация
- Показывает одну статью за раз с частичным видом следующей

### Планшеты (≥ 768px)
- Грид из 3 колонок
- Отключается слайдер, включается сетка

### Десктоп средний (≥ 1200px)
- Грид из 4 колонок
- Увеличенные отступы

### Десктоп большой (≥ 1440px)
- Грид из 5 колонок
- Максимальная плотность контента

## Использование

```php
<?php
$APPLICATION->IncludeComponent(
    "custom:similar.articles",
    "",
    array(
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],     // ID инфоблока
        "CURRENT_ARTICLE_ID" => $arResult["ID"],   // ID текущей статьи (исключается из выборки)
        "BASE_URL" => "/our-blog/",                // Базовый URL раздела
        "LIMIT" => 5                                // Количество статей
    ),
    false
);
?>
```

## Параметры

| Параметр | Тип | Описание | По умолчанию |
|----------|-----|----------|--------------|
| `IBLOCK_ID` | int | ID инфоблока | 1 |
| `CURRENT_ARTICLE_ID` | int | ID текущей статьи (исключается из выборки) | null |
| `BASE_URL` | string | Базовый URL для формирования ссылок на статьи | `/our-blog/` |
| `LIMIT` | int | Количество выводимых статей | 5 |

## Примеры использования

### Блог
```php
$APPLICATION->IncludeComponent(
    "custom:similar.articles",
    "",
    array(
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "CURRENT_ARTICLE_ID" => $arResult["ID"],
        "BASE_URL" => "/our-blog/",
        "LIMIT" => 5
    ),
    false
);
```

### Новости
```php
$APPLICATION->IncludeComponent(
    "custom:similar.articles",
    "",
    array(
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "CURRENT_ARTICLE_ID" => $arResult["ID"],
        "BASE_URL" => "/news/",
        "LIMIT" => 5
    ),
    false
);
```

### СМИ о нас
```php
$APPLICATION->IncludeComponent(
    "custom:similar.articles",
    "",
    array(
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "CURRENT_ARTICLE_ID" => $arResult["ID"],
        "BASE_URL" => "/smi-o-nas/",
        "LIMIT" => 5
    ),
    false
);
```

## Структура компонента

```
similar.articles/
├── component.php                    # Логика компонента
├── README.md                        # Документация
└── templates/
    └── .default/
        ├── template.php             # Шаблон вывода
        └── style.css               # Стили
```

## Логика работы

1. Компонент получает параметры (ID инфоблока, текущую статью, базовый URL)
2. Делает выборку последних N статей из инфоблока
3. Исключает текущую статью из выборки (если указана)
4. Передает данные в шаблон
5. Шаблон выводит статьи в адаптивном слайдере/гриде

## CSS-классы

- `.blog__similar-articles` - основной контейнер
- `.blog__similar-articles-title` - заголовок блока
- `.blog__similar-slider` - контейнер слайдера
- `.blog__similar-articles-list` - список статей (flex/grid)
- `.blog__similar-article-link` - ссылка на статью
- `.card-blog` - карточка статьи
- `.card-blog__image-wrap` - контейнер изображения
- `.card-blog__content` - контент карточки
- `.card-blog__title` - заголовок статьи
- `.card-blog__tag` - тег статьи
- `.card-blog__date` - дата публикации

## Интеграция

Компонент интегрирован в следующие шаблоны:

1. `/local/templates/main/components/bitrix/news.detail/blog_detail/template.php`
2. `/local/templates/main/components/bitrix/news.detail/news_detail/template.php`
3. `/local/templates/main/components/bitrix/news.detail/smi_detail/template.php`

## История изменений

### v1.0 (13.11.2024)
- Создан компонент
- Реализована адаптивная сетка/слайдер
- Добавлена интеграция в 3 шаблона детальных страниц
- Закомментирован старый код "Читайте также"
- Закомментирован компонент "Просмотренные статьи"

