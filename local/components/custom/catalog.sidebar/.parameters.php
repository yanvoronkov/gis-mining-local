<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID инфоблока',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'SHOW_FILTER' => [
            'PARENT' => 'BASE',
            'NAME' => 'Показывать умный фильтр',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ],
        'FILTER_NAME' => [
            'PARENT' => 'BASE',
            'NAME' => 'Имя переменной фильтра',
            'TYPE' => 'STRING',
            'DEFAULT' => 'arrFilter',
        ],
        'SEF_RULE' => [
            'PARENT' => 'BASE',
            'NAME' => 'SEF правило для фильтра',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'SECTION_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID раздела',
            'TYPE' => 'STRING',
            'DEFAULT' => '0',
        ],
    ],
];
