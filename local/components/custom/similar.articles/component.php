<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $component */

CModule::IncludeModule("iblock");

// Параметры компонента
$arResult["IBLOCK_ID"] = $arParams["IBLOCK_ID"] ?? 1;
$arResult["CURRENT_ARTICLE_ID"] = $arParams["CURRENT_ARTICLE_ID"] ?? null;
$arResult["BASE_URL"] = $arParams["BASE_URL"] ?? "/our-blog/";
$arResult["LIMIT"] = $arParams["LIMIT"] ?? 5;

// Получаем похожие статьи (5 последних, исключая текущую)
$arResult["SIMILAR_ARTICLES"] = [];

if ($arResult["IBLOCK_ID"]) {
    $filter = array(
        "IBLOCK_ID" => $arResult["IBLOCK_ID"],
        "ACTIVE" => "Y"
    );
    
    // Исключаем текущую статью, если она указана
    if ($arResult["CURRENT_ARTICLE_ID"]) {
        $filter["!ID"] = $arResult["CURRENT_ARTICLE_ID"];
    }
    
    $similarArticles = CIBlockElement::GetList(
        array("ACTIVE_FROM" => "DESC", "SORT" => "ASC"),
        $filter,
        false,
        array("nTopCount" => $arResult["LIMIT"]),
        array(
            "ID", 
            "NAME", 
            "PREVIEW_TEXT", 
            "PREVIEW_PICTURE", 
            "ACTIVE_FROM", 
            "CODE", 
            "PROPERTY_TAGS"
        )
    );
    
    while ($article = $similarArticles->GetNext()) {
        $arResult["SIMILAR_ARTICLES"][] = $article;
    }
}

$this->includeComponentTemplate();
?>

