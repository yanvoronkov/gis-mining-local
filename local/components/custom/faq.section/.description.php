<?php
/**
 * Описание компонента FAQ секции
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arComponentDescription = [
    'NAME' => 'FAQ секция',
    'DESCRIPTION' => 'Выводит блок часто задаваемых вопросов с аккордеоном',
    'ICON' => '/images/ico_comp_list.gif',
    'CACHE_PATH' => 'Y',
    'SORT' => 10,
    'PATH' => [
        'ID' => 'custom',
        'NAME' => 'Кастомные компоненты',
        'CHILD' => [
            'ID' => 'faq',
            'NAME' => 'FAQ',
        ],
    ],
];
?>