<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/**
 * Компонент catalog.popup
 * Универсальная попап-форма для заявок из каталога
 */

// Значения по умолчанию
$arParams['POPUP_TITLE'] = $arParams['POPUP_TITLE'] ?: 'Получить КП';
$arParams['POPUP_CTA'] = $arParams['POPUP_CTA'] ?: 'Заполните форму, чтобы оставить заявку на КП. Мы перезвоним вам в ближайшее время';
$arParams['FORM_ID'] = $arParams['FORM_ID'] ?: 'catalogPopupForm';
$arParams['SOURCE_ID'] = $arParams['SOURCE_ID'] ?: '40';
$arParams['FORM_NAME'] = $arParams['FORM_NAME'] ?: 'Заказ из каталога';
$arParams['METRIC_GOAL'] = $arParams['METRIC_GOAL'] ?: 'send-catalog-lead';
$arParams['BUTTON_TEXT'] = $arParams['BUTTON_TEXT'] ?: 'Получить КП';

// Передаем параметры в шаблон
$arResult['POPUP_TITLE'] = htmlspecialchars($arParams['POPUP_TITLE']);
$arResult['POPUP_CTA'] = htmlspecialchars($arParams['POPUP_CTA']);
$arResult['FORM_ID'] = htmlspecialchars($arParams['FORM_ID']);
$arResult['SOURCE_ID'] = htmlspecialchars($arParams['SOURCE_ID']);
$arResult['FORM_NAME'] = htmlspecialchars($arParams['FORM_NAME']);
$arResult['METRIC_GOAL'] = htmlspecialchars($arParams['METRIC_GOAL']);
$arResult['BUTTON_TEXT'] = htmlspecialchars($arParams['BUTTON_TEXT']);

// Генерируем уникальные ID для полей формы (чтобы не было конфликтов при нескольких попапах на странице)
$arResult['FIELD_IDS'] = [
    'name' => $arParams['FORM_ID'] . '_client_name',
    'phone' => $arParams['FORM_ID'] . '_client_phone',
    'email' => $arParams['FORM_ID'] . '_client_email',
    'privacy' => $arParams['FORM_ID'] . '_privacy_policy',
];

// Подключаем шаблон
$this->IncludeComponentTemplate();
