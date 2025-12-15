<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $component */

// Формируем полный URL текущей статьи
$currentArticleUrl = '';
if (!empty($arParams["CURRENT_ARTICLE_DATA"]["CODE"])) {
    $baseUrl = $arParams["BASE_URL"] ?? "/our-blog/";
    $currentArticleUrl = $baseUrl . $arParams["CURRENT_ARTICLE_DATA"]["CODE"] . "/";
}

// Передаем параметры в шаблон
$arResult = [
    "IBLOCK_ID" => $arParams["IBLOCK_ID"] ?? 1,
    "CURRENT_ARTICLE_ID" => $arParams["CURRENT_ARTICLE_ID"] ?? null,
    "CURRENT_ARTICLE_DATA" => $arParams["CURRENT_ARTICLE_DATA"] ?? null,
    "SECTION_TYPE" => $arParams["SECTION_TYPE"] ?? "blog",
    "BASE_URL" => $arParams["BASE_URL"] ?? "/our-blog/",
    "CURRENT_ARTICLE_URL" => $currentArticleUrl,
];

$this->includeComponentTemplate();
?>
