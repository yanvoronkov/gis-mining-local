# Шаблон "Калькулятор доходности" (catalog_element_dohodnost)

## Описание
Специальный шаблон для отображения страницы калькулятора доходности товаров (ASIC-майнеров).

## URL структура
- **Обычная страница товара**: `/catalog/[section]/[product-code]/`
- **Страница доходности**: `/catalog/[section]/[product-code]/calculator-dohodnosti/`

## Отличия от основного шаблона

### 1. H1 заголовок
Перед названием товара добавляется слово "Доходность":
- Было: `Antminer S19 Pro`
- Стало: `Доходность Antminer S19 Pro`

### 2. Title страницы
Формат: `Доходность {название товара}`

### 3. Meta Description
Формат: `Калькулятор прибыльности асика {название товара} - GIS-MINING 2025`

### 4. Хлебные крошки
Добавлен элемент "Калькулятор доходности" в конец цепочки:
```
Главная > Каталог > Секция > Товар > Калькулятор доходности
```

### 5. LD-разметка (Schema.org)
- `name`: "Доходность {название товара}"
- `description`: "Калькулятор прибыльности асика {название товара} - GIS-MINING 2025"
- `url`: URL с `/calculator-dohodnosti/`

## Файлы

### Шаблон компонента
`/local/templates/main/components/bitrix/catalog.element/catalog_element_dohodnost/`
- `template.php` - основной шаблон
- `component.php` - логика компонента
- `component_epilog.php` - код после вывода компонента
- `component_simple.php` - упрощенная версия
- `init.php` - инициализация

### Обработчик страницы
`/catalog/detail_dohodnost.php` - файл для обработки запросов к странице доходности

### Правило ЧПУ
В файле `/urlrewrite.php` добавлено правило с ID=26 и SORT=1:
```php
'CONDITION' => '#^/catalog/([^/]+)/([a-z0-9_\\-]+)/calculator-dohodnosti/?(?:\\?(.*))?$#i',
'RULE' => 'ELEMENT_CODE=$2&$3',
'ID' => 'bitrix:catalog.element',
'PATH' => '/catalog/detail_dohodnost.php',
```

## Использование

Чтобы открыть страницу калькулятора доходности для любого товара, добавьте `/calculator-dohodnosti/` к URL товара:

Пример:
- Обычная страница: `https://example.com/catalog/asics/antminer-s19-pro/`
- Калькулятор доходности: `https://example.com/catalog/asics/antminer-s19-pro/calculator-dohodnosti/`

## Технические детали

### Приоритет обработки URL
Правило имеет SORT=1, что означает наивысший приоритет обработки URL. Это гарантирует, что URL с `/calculator-dohodnosti/` будут обрабатываться раньше остальных правил каталога.

### SEO настройки
- `SET_TITLE` = "N" - Title устанавливается в самом шаблоне
- `SET_META_DESCRIPTION` = "N" - Description устанавливается в шаблоне
- `SET_CANONICAL_URL` = "Y" - Canonical URL генерируется автоматически
- Canonical URL будет указывать на страницу с `/calculator-dohodnosti/`

## Дальнейшие модификации

В дальнейшем планируется изменить структуру шаблона для фокусировки на калькуляторе доходности.

---
Создано: 2025-11-14

