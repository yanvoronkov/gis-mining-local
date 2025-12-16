<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'POPUP_TITLE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Заголовок попапа',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Получить КП',
        ],
        'POPUP_CTA' => [
            'PARENT' => 'BASE',
            'NAME' => 'Текст призыва к действию',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Заполните форму, чтобы оставить заявку на КП. Мы перезвоним вам в ближайшее время',
        ],
        'FORM_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID формы (уникальный)',
            'TYPE' => 'STRING',
            'DEFAULT' => 'catalogPopupForm',
        ],
        'SOURCE_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID источника для CRM',
            'TYPE' => 'STRING',
            'DEFAULT' => '40',
        ],
        'FORM_NAME' => [
            'PARENT' => 'BASE',
            'NAME' => 'Название формы для CRM',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Заказ из каталога',
        ],
        'METRIC_GOAL' => [
            'PARENT' => 'BASE',
            'NAME' => 'Цель метрики (data-metric-goal)',
            'TYPE' => 'STRING',
            'DEFAULT' => 'send-catalog-lead',
        ],
        'BUTTON_TEXT' => [
            'PARENT' => 'BASE',
            'NAME' => 'Текст кнопки',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Получить КП',
        ],
        'CACHE_TIME' => [
            'PARENT' => 'CACHE_SETTINGS',
            'NAME' => 'Время кеширования (сек)',
            'TYPE' => 'STRING',
            'DEFAULT' => '36000000',
        ],
    ],
];
