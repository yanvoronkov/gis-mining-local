<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

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

if (!$arResult["NavShowAlways"]) {
    if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
        return;
}

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"] . "&" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?" . $arResult["NavQueryString"] : "");

$sUrlPath = $arResult["sUrlPath"];
$navQueryString = $arResult["NavQueryString"];
$navNum = $arResult["NavNum"];

// Проверяем, что navNum установлен правильно
if (empty($navNum)) {
    $navNum = 1; // Значение по умолчанию
}

$currentPage = $arResult["NavPageNomer"];
$totalPages = $arResult["NavPageCount"];


if ($totalPages <= 1) {
    return;
}
?>

<div class="pagination">
    <ul class="pagination__list">
        <?php
        // --- Стрелка "В САМОЕ НАЧАЛО" ---
        if ($currentPage > 1): ?>
            <li class="pagination__item pagination__item--prev">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=1" class="pagination__link"
                    aria-label="Первая страница">
                    <svg width="14" height="11" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 10.5L1 5.5L6 0.5" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M13 10.5L8 5.5L13 0.5" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </li>
        <?php endif; ?>

        <?php
        // --- Стрелка "НАЗАД" ---
        if ($currentPage > 1): ?>
            <li class="pagination__item pagination__item--prev">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=<?= ($currentPage - 1) ?>"
                    class="pagination__link" aria-label="Предыдущая страница">
                    <svg width="8" height="13" viewBox="0 0 8 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.25 12.5L1.25 6.5L7.25 0.5" stroke="#6F7682" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </a>
            </li>
        <?php endif; ?>

        <?php
        // --- Цифровые кнопки (с логикой многоточия) ---
        $pagesToShow = 5; // Сколько кнопок с цифрами показывать (нечетное число)
        $sideButtons = floor($pagesToShow / 2);

        $startPage = max(1, $currentPage - $sideButtons);
        $endPage = min($totalPages, $currentPage + $sideButtons);

        // Корректируем диапазон, если мы у края
        if ($currentPage - $startPage < $sideButtons) {
            $endPage = min($totalPages, $endPage + ($sideButtons - ($currentPage - $startPage)));
        }
        if ($endPage - $currentPage < $sideButtons) {
            $startPage = max(1, $startPage - ($sideButtons - ($endPage - $currentPage)));
        }

        // Многоточие в начале
        if ($startPage > 1): ?>
            <li class="pagination__item">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=1" class="pagination__link">1</a>
            </li>
            <?php if ($startPage > 2): ?>
                <li class="pagination__item pagination__item--dots"><span>...</span></li>
            <?php endif;
        endif;

        // Основной цикл по цифрам
        for ($i = $startPage; $i <= $endPage; $i++):
            $activeClass = ($i === $currentPage) ? 'pagination__item--active' : '';
            ?>
            <li class="pagination__item <?= $activeClass ?>">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=<?= $i ?>"
                    class="pagination__link"><?= $i ?></a>
            </li>
        <?php endfor;

        // Многоточие в конце
        if ($endPage < $totalPages):
            if ($endPage < $totalPages - 1): ?>
                <li class="pagination__item pagination__item--dots"><span>...</span></li>
            <?php endif; ?>
            <li class="pagination__item">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=<?= $totalPages ?>"
                    class="pagination__link"><?= $totalPages ?></a>
            </li>
        <?php endif; ?>

        <?php
        // --- Стрелка "ВПЕРЕД" ---
        if ($currentPage < $totalPages): ?>
            <li class="pagination__item pagination__item--next">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=<?= ($currentPage + 1) ?>"
                    class="pagination__link" aria-label="Следующая страница">
                    <svg width="8" height="13" viewBox="0 0 8 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.75 12.5L6.75 6.5L0.75 0.5" stroke="#131315" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </a>
            </li>
        <?php endif; ?>

        <?php
        // --- Стрелка "В САМЫЙ КОНЕЦ" ---
        if ($currentPage < $totalPages): ?>
            <li class="pagination__item pagination__item--next">
                <a href="<?= $sUrlPath ?>?<?= $strNavQueryString ?>PAGEN_<?= $navNum ?>=<?= $totalPages ?>" class="pagination__link"
                    aria-label="Последняя страница">
                    <svg width="14" height="11" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 10.5L13 5.5L8 0.5" stroke="#131315" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M1 10.5L6 5.5L1 0.5" stroke="#131315" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>