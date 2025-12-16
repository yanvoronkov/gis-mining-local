<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arComponentDescription = [
    'NAME' => 'Попап-форма каталога',
    'DESCRIPTION' => 'Универсальная попап-форма для заявок из каталога (GPU, Инвестиции, Контейнеры, Готовый бизнес)',
    'ICON' => '/images/icon.gif',
    'SORT' => 50,
    'PATH' => [
        'ID' => 'custom',
        'NAME' => 'Кастомные компоненты',
        'SORT' => 10,
        'CHILD' => [
            'ID' => 'catalog',
            'NAME' => 'Каталог',
        ],
    ],
    'CACHE_PATH' => 'Y',
];
