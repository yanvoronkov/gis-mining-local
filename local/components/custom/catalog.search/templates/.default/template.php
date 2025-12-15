<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(false);

$componentId = $arResult["COMPONENT_ID"];
?>


<section class="catalog-search-section">
    <div class="catalog-search-container">
        <!-- Всплывающая подсказка -->
        <div class="catalog-search-tooltip" id="catalog-search-tooltip-<?= $componentId ?>">
            <div class="catalog-search-tooltip__content">
                <p class="catalog-search-tooltip__text">Воспользуйтесь поиском, чтобы быстрее найти нужное оборудование</p>
            </div>
            <span class="catalog-search-tooltip__close" aria-label="Закрыть подсказку">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M18 6L6 18" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6 6L18 18" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
            </span>
        </div>

        <!-- Форма поиска для отправки на страницу результатов -->
        <form action="/search/" method="GET" class="catalog-search-form">
            <div class="catalog-search-wrapper" id="<?= $componentId ?>">
                <div class="catalog-search-input-wrapper">
            <span class="catalog-search-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </span>
                    <input
                            type="text"
                            name="q"
                            class="catalog-search-input"
                            placeholder="Поиск по каталогу..."
                            autocomplete="off"
                            value="<?= $arResult["PARAMS"]["INITIAL_QUERY"] ?>"
                            data-component-id="<?= $componentId ?>"
                            data-ajax-path="<?= $arResult["AJAX_PATH"] ?>"
                            data-iblock-ids='<?= json_encode($arResult["PARAMS"]["IBLOCK_IDS"]) ?>'
                            data-min-length="<?= $arResult["PARAMS"]["MIN_QUERY_LENGTH"] ?>"
                            data-max-results="<?= $arResult["PARAMS"]["MAX_RESULTS"] ?>"
                            data-show-price="<?= $arResult["PARAMS"]["SHOW_PRICE"] ?>"
                    >
                    <span class="catalog-search-loader" style="display:none;">
                <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" fill="none"
                            stroke-dasharray="40" stroke-dashoffset="10">
                        <animateTransform attributeName="transform" type="rotate" from="0 10 10" to="360 10 10" dur="1s"
                                          repeatCount="indefinite"/>
                    </circle>
                </svg>
            </span>
                    <button class="catalog-search-clear" style="display:none;" type="button" aria-label="Очистить">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="catalog-search-results" style="display:none;">
                    <!-- Результаты поиска будут вставлены динамически через JavaScript -->
                </div>
            </div>
        </form>
    </div>
</section>

