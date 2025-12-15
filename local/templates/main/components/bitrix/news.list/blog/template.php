<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
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

// Подключаем модуль информационных блоков
if (!CModule::IncludeModule("iblock")) {
    ShowError("Модуль информационных блоков не установлен");
    return;
}
?>

<div class="container">
    <!-- Заголовок блога -->
    <div class="page-blog__header section-title">
        <h1 class="page-blog__title section-title highlighted-color">
            Блог
        </h1>
    </div>

    <!-- Статьи -->
    <div class="page-blog__articles">
        <!-- Вкладки для переключения страниц -->
        <?php $APPLICATION->IncludeComponent("custom:blog.sidebar", "", [], false); ?>

        <?php if (!empty($arResult["ITEMS"])): ?>
            <!-- Сетка статей -->
            <div class="page-blog__grid">
                <?php foreach ($arResult["ITEMS"] as $item): ?>
                    <a href="/our-blog/<?= $item["CODE"] ?>/" class="page-blog__card-link">
                        <div class="page-blog__card card-blog">
                            <div class="card-blog__image-wrap">
                                <?php if (!empty($item["PREVIEW_PICTURE"]["SRC"])): ?>
                                    <img src="<?= $item["PREVIEW_PICTURE"]["SRC"] ?>" alt="<?= $item["NAME"] ?>"
                                        class="card-blog__image">
                                <?php endif; ?>
                            </div>

                            <div class="card-blog__content">
                                <div class="card-blog__tag-date-wrap">
                                    <?php if (!empty($item["PROPERTIES"]["TAGS"]["VALUE"])): ?>
                                        <div class="card-blog__tag">
                                            #<?= is_array($item["PROPERTIES"]["TAGS"]["VALUE"]) ? $item["PROPERTIES"]["TAGS"]["VALUE"][0] : $item["PROPERTIES"]["TAGS"]["VALUE"] ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-blog__date">
                                        <?= FormatDate("d.m.Y", MakeTimeStamp($item["ACTIVE_FROM"])) ?>
                                    </div>
                                </div>
                                <h2 class="card-blog__title"><?= $item["NAME"] ?></h2>
                                <?php if (!empty($item["PREVIEW_TEXT"])): ?>
                                    <div class="card-blog__excerpt">
                                        <?= $item["PREVIEW_TEXT"] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="btn offer-section__btn btn-primary card-blog__btn">Читать статью</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-articles">
                <p>Статьи не найдены</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($arResult["ITEMS"])): ?>
        <!-- Пагинация -->
        <?php if ($arResult["NAV_RESULT"]): ?>
            <div class="page-blog__pagination">
                <?php
                $APPLICATION->IncludeComponent(
                    "bitrix:system.pagenavigation",
                    "catalog_new",
                    array(
                        "NAV_RESULT" => $arResult["NAV_RESULT"],
                        "SHOW_ALWAYS" => "Y",
                        "NAV_TITLE" => "Статьи",
                        "BASE_LINK" => "",
                    ),
                    false
                );
                ?>
            </div>
        <?php else: ?>
            <div class="page-blog__btn btn btn--primary">Показать еще</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    // Новый компонент просмотренных статей через LocalStorage
    $APPLICATION->IncludeComponent(
        "custom:viewed.articles",
        "",
        array(
            "IBLOCK_ID" => $blogIblockId,
            "CURRENT_ARTICLE_ID" => null, // На главной странице нет текущей статьи
            "CURRENT_ARTICLE_DATA" => null
        ),
        false
    );
    ?>

</div>