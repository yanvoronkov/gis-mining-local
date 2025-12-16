<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);
?>

<!-- PopUp форма каталога -->
<div class="page-business__popup-form popup-form-wrapper js-cart-modal" id="mainPopupFormWrapper"
    style="display: none;">

    <form class="popup-form js-ajax-form" id="<?= $arResult['FORM_ID'] ?>"
        data-metric-goal="<?= $arResult['METRIC_GOAL'] ?>">

        <button type="button" class="popup-form__close-btn menu-close" id="closeMainPopupFormBtn" aria-label="Закрыть">
            <span>
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
            </span>
        </button>

        <p class="popup-form__text"><?= $arResult['POPUP_TITLE'] ?></p>

        <p class="popup-form__cta"><?= $arResult['POPUP_CTA'] ?></p>

        <label for="<?= $arResult['FIELD_IDS']['name'] ?>">Ваше имя*:</label>
        <input type="text" name="client_name" id="<?= $arResult['FIELD_IDS']['name'] ?>" placeholder="Имя"
            class="contact-form__input form-input" required aria-label="Имя">

        <label for="<?= $arResult['FIELD_IDS']['phone'] ?>">Телефон*:</label>
        <input type="tel" name="client_phone" id="<?= $arResult['FIELD_IDS']['phone'] ?>" placeholder="Телефон*"
            class="contact-form__input form-input js-phone-mask" required aria-label="Номер телефона">

        <label for="<?= $arResult['FIELD_IDS']['email'] ?>">Email:</label>
        <input type="email" name="client_email" id="<?= $arResult['FIELD_IDS']['email'] ?>"
            placeholder="your@email.com (необязательно)" class="contact-form__input form-input"
            aria-label="Электронная почта">

        <input type="hidden" name="source_id" value="<?= $arResult['SOURCE_ID'] ?>">
        <input type="hidden" name="utm_source">
        <input type="hidden" name="utm_medium">
        <input type="hidden" name="utm_campaign">
        <input type="hidden" name="utm_content">
        <input type="hidden" name="utm_term">
        <!-- Название формы для CRM -->
        <input type="hidden" name="form_name" value="<?= $arResult['FORM_NAME'] ?>">
        <input type="hidden" name="page_url" value="">

        <button type="submit" class="btn btn-primary"><?= $arResult['BUTTON_TEXT'] ?></button>

        <div class="form-group form-check mb-3">
            <input type="checkbox" id="<?= $arResult['FIELD_IDS']['privacy'] ?>" name="privacy-policy"
                class="form-check-input" required>
            <label for="<?= $arResult['FIELD_IDS']['privacy'] ?>" class="form-check-label">Согласен(а) с <a
                    href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a></label>
        </div>
        <p class="form-error-message" style="color: red; display: none;"></p>
    </form>
</div>