<?php
/**
 * Шаблон компонента catalog.search для поиска по каталогу
 * Адаптирован под дизайн сайта
 */

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

$this->setFrameMode(true);
?>

    <form action="<?= $arResult["FORM_ACTION"] ?>" method="GET" class="catalog-search-form">
        <input type="search" 
               name="q" 
               class="search-input__field js-catalog-search-input" 
               placeholder="Поиск по каталогу"
               value="<?= htmlspecialchars($arResult["REQUEST"]["QUERY"]) ?>"
               autocomplete="off">
        <button type="submit" class="search-input__button">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.18844 0.898438C12.2146 0.898438 15.4784 4.16228 15.4784 8.18844C15.4784 9.90951 14.882 11.4913 13.8846 12.7384L16.8612 15.7157C17.1775 16.032 17.1775 16.5449 16.8612 16.8612C16.5692 17.1532 16.1097 17.1756 15.792 16.9286L15.7157 16.8612L12.7384 13.8846C11.4913 14.882 9.90951 15.4784 8.18844 15.4784C4.16228 15.4784 0.898438 12.2146 0.898438 8.18844C0.898438 4.16228 4.16228 0.898438 8.18844 0.898438ZM8.18844 2.51844C5.05698 2.51844 2.51844 5.05698 2.51844 8.18844C2.51844 11.3199 5.05698 13.8584 8.18844 13.8584C11.3199 13.8584 13.8584 11.3199 13.8584 8.18844C13.8584 5.05698 11.3199 2.51844 8.18844 2.51844Z" fill="#131315"></path>
            </svg>
        </button>
    </form>

