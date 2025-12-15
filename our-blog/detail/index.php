<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Подключаем модуль информационных блоков
if (!CModule::IncludeModule("iblock")) {
    ShowError("Модуль информационных блоков не установлен");
    return;
}



// Получаем символьный код статьи из URL
$elementCode = $_REQUEST["ELEMENT_CODE"] ?? '';
if (empty($elementCode)) {
    // Если ELEMENT_CODE не передан, пробуем извлечь из URL
    $elementCode = $APPLICATION->GetCurPage(false);
    $elementCode = str_replace('/our-blog/', '', $elementCode);
    $elementCode = str_replace('/', '', $elementCode);
}



// Получаем ID инфоблока "blog"
$blogIblockId = 8; // Используем ваш ID напрямую

// Получаем инфоблок
$iblock = CIBlock::GetList(
    array("SORT" => "ASC"),
    array("ID" => $blogIblockId)
)->GetNext();

if ($iblock) {
    $blogIblockId = $iblock["ID"]; // Убеждаемся, что ID правильный
}

// Получаем данные статьи
$article = null;
if ($blogIblockId > 0 && $elementCode) {
    $article = CIBlockElement::GetList(
        array("SORT" => "ASC"),
        array("IBLOCK_ID" => $blogIblockId, "CODE" => $elementCode, "ACTIVE" => "Y"),
        false,
        false,
        array("ID", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "DETAIL_PICTURE", "ACTIVE_FROM", "PROPERTY_TAGS", "PROPERTY_AUTHOR", "PROPERTY_SOURCE")
    )->GetNext();
}



if ($article) {
    // --- SEO НАСТРОЙКИ ДЛЯ СТАТЬИ ---
    
    // Определяем протокол
    $protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";

    // Получаем имя сервера из настроек сайта. Это самый надежный способ.
    // Константа SITE_SERVER_NAME определяется на основе поля "URL сервера", которое мы настроили.
    $serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];

    // Получаем чистый URL страницы без GET-параметров
    $pageUrl = $APPLICATION->GetCurPage(false);

    // Собираем полный канонический URL
    $fullPageUrl = $protocol . '://' . $serverName . $pageUrl;
    
    // Формируем URL для OG-картинки статьи
    $ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.webp';
    if (!empty($article["PREVIEW_PICTURE"])) {
        $ogImageUrl = $protocol . '://' . $serverName . CFile::GetPath($article["PREVIEW_PICTURE"]);
    }
    
    // Title
    $APPLICATION->SetTitle($article["NAME"]);
    
    // Description
    $description = !empty($article["PREVIEW_TEXT"]) ? $article["PREVIEW_TEXT"] : $article["NAME"];
    $description = strip_tags($description);
    $description = mb_substr($description, 0, 160);
    $APPLICATION->SetPageProperty("description", $description);
    
    // Keywords
    $keywords = "майнинг, криптовалюта, ASIC, оборудование";
    if (!empty($article["PROPERTY_TAGS_VALUE"])) {
        $keywords .= ", " . $article["PROPERTY_TAGS_VALUE"];
    }
    $APPLICATION->SetPageProperty("keywords", $keywords);
    
    // Robots
    $APPLICATION->SetPageProperty("robots", "index, follow");
    
    // Open Graph
    $APPLICATION->SetPageProperty("OG:TITLE", $article["NAME"]);
    $APPLICATION->SetPageProperty("OG:DESCRIPTION", $description);
    $APPLICATION->SetPageProperty("OG:TYPE", "article");
    $APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
    $APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
    $APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
    $APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);
    
    // Twitter Card
    $APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
    $APPLICATION->SetPageProperty("TWITTER:TITLE", $article["NAME"]);
    $APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", $description);
    $APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);


    // --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
    $APPLICATION->SetPageProperty("main_class", "page-blog-detail");
    $APPLICATION->SetPageProperty("header_right_class", "color-block");
    
    // Хлебные крошки теперь формируются автоматически в header
    
} else {
    $APPLICATION->SetTitle("Статья не найдена");
    $APPLICATION->SetPageProperty("robots", "noindex, nofollow");
}
?>

<?php if ($article): ?>
    <?php
    // Компонент детальной страницы статьи
    $APPLICATION->IncludeComponent(
	"bitrix:news.detail", 
	"blog_detail", 
	[
		"IBLOCK_TYPE" => "content",
		"IBLOCK_ID" => $blogIblockId,
		"ELEMENT_ID" => $article["ID"],
		"ELEMENT_CODE" => $elementCode,
		"CHECK_DATES" => "Y",
		"FIELD_CODE" => [
			0 => "ID",
			1 => "NAME",
			2 => "PREVIEW_TEXT",
			3 => "PREVIEW_PICTURE",
			4 => "DETAIL_TEXT",
			5 => "DETAIL_PICTURE",
			6 => "ACTIVE_FROM",
			7 => "",
		],
		"PROPERTY_CODE" => [
			0 => "",
			1 => "TAGS",
			2 => "AUTHOR",
			3 => "SOURCE",
			4 => "",
		],
		"IBLOCK_URL" => "/our-blog/",
		"USE_PERMISSIONS" => "N",
		"GROUP_PERMISSIONS" => "",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"PAGER_TEMPLATE" => "",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_BASE_LINK" => "",
		"PAGER_PARAMS_NAME" => "PAGEN_",
		"SHOW_SIMILAR_ARTICLES" => "Y",
		"SIMILAR_ARTICLES_COUNT" => "3",
		"COMPONENT_TEMPLATE" => "blog_detail",
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"SET_TITLE" => "Y",
		"SET_CANONICAL_URL" => "N",
		"SET_BROWSER_TITLE" => "Y",
		"BROWSER_TITLE" => "-",
		"SET_META_KEYWORDS" => "Y",
		"META_KEYWORDS" => "-",
		"SET_META_DESCRIPTION" => "Y",
		"META_DESCRIPTION" => "-",
		"SET_LAST_MODIFIED" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"ADD_ELEMENT_CHAIN" => "Y",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"STRICT_SECTION_CHECK" => "N",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"PAGER_TITLE" => "Страница",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => ""
	],
	false
);
    ?>
<?php else: ?>
    <div class="article-not-found">
        <h1>Статья не найдена</h1>
        <p>Запрашиваемая статья не существует или была удалена.</p>
        <a href="/our-blog/" class="back-to-blog">← Вернуться к блогу</a>
    </div>
    
    <style>
    .article-not-found {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .article-not-found h1 {
        color: #333;
        margin-bottom: 20px;
    }
    
    .back-to-blog {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.3s ease;
    }
    
    .back-to-blog:hover {
        background: #0056b3;
        color: white;
    }
    </style>
<?php endif; ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
