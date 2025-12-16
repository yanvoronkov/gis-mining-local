<?php
/**
 * Главная страница каталога
 * Показывает список всех категорий (инфоблоков)
 */

use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// Подключаем модули
if (!Loader::includeModule('iblock')) {
    ShowError('Модуль iblock не установлен');
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

// Получаем данные инфоблока для описания (используем первый инфоблок из каталога - ASICS)
$iblockData = [];
$firstIblockId = defined('IBLOCK_CATALOG_ASICS') ? IBLOCK_CATALOG_ASICS : 1;

$iblock = CIBlock::GetByID($firstIblockId)->Fetch();
if ($iblock) {
    $iblockData = [
        'NAME' => $iblock['NAME'],
        'DESCRIPTION' => $iblock['DESCRIPTION'],
    ];
}

// SEO для главной страницы каталога (через стандартные методы)
$APPLICATION->SetTitle('Каталог оборудования для майнинга');
$APPLICATION->SetPageProperty('title', 'Купить оборудование для майнинга — Каталог продукции от «Gis Mining»');
$APPLICATION->SetPageProperty('description', 'Каталог асик-майнеров от компании «Gis Mining». Доступные цены, высокое качество, широкий ассортимент.');
$APPLICATION->SetPageProperty('robots', 'index, follow');

// Дополнительные свойства для шаблона
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title highlighted-color");
?>

<div class="catalog-page catalog-new container" id="app-root">
    <!-- Поиск по каталогу -->
    <?php
    $APPLICATION->IncludeComponent(
        "custom:catalog.search",
        ".default",
        [
            "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
            "MIN_QUERY_LENGTH" => 2,
            "MAX_RESULTS" => 10,
            "SHOW_PRICE" => "Y",
            "PRICE_CODE" => "BASE",
            "CACHE_TIME" => 3600,
        ]
    );
    ?>

    <div class="catalog-page__body">
        <aside class="catalog-page__full">
            <!-- Список категорий каталога -->
            <?php
            $APPLICATION->IncludeComponent(
                "custom:catalog.list",
                ".default",
                [
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                ],
                false
            );
            ?>

            <!-- Описание секции (редактируется в админке инфоблока ASICS) -->
            <section class="catalog-about section-padding">
                <div class="about__content">
                    <h2 class="about__title"><?= $iblockData['NAME'] ?: 'ASIC-майнеры' ?></h2>
                    <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                        <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
                    </div>
                </div>
            </section>

            <!-- Секция обратной связи -->
            <? $APPLICATION->IncludeComponent(
                "custom:feedback.section",
                ".default",
                []
            ); ?>
        </aside>
    </div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
