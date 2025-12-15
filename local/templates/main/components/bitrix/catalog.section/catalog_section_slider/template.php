<?php
/**
 * Шаблон компонента catalog.section для нового каталога
 * Адаптирован под дизайн сайта
 * КОД ИСПРАВЛЕН С ИСПОЛЬЗОВАНИЕМ КЛАССИЧЕСКОГО ПОДХОДА К ВЫВОДУ СВОЙСТВ
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

<?php if (!empty($arResult["ITEMS"])): ?>
    <!-- Обертка для native-slider -->
    <div class="js-native-slider products-slider" data-native-slider-options='{
        "navigation": {
            "prevEl": ".js-products-prev",
            "nextEl": ".js-products-next"
        },
        "loop": false,
        "enableFullscreen": false
    }'>
        <div class="product-grid asic-miners__product-grid native-slider__wrapper">
            <?php foreach ($arResult["ITEMS"] as $arItem): ?>
                <?php
                // Области для редактирования и удаления элемента в режиме правки
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

                // --- ПОДГОТОВКА ДАННЫХ ДЛЯ КАРТОЧКИ ---

                // Получаем цену. Используем оператор объединения с null для краткости и надежности.
                $priceValue = $arItem['ITEM_PRICES'][0]['PRICE'] ?? 0;
                $priceFormatted = ($priceValue > 0) ? number_format($priceValue, 0, '.', ' ') . ' ₽' : 'Под заказ';

                // Ссылка на детальную страницу
                $detailUrl = $arItem["DETAIL_PAGE_URL"];

                // Изображение
                $imageSrc = !empty($arItem["PREVIEW_PICTURE"]["SRC"]) ? $arItem["PREVIEW_PICTURE"]["SRC"] : '/local/templates/main/assets/img/no-photo.jpg';
                ?>

                <!-- Обертка слайда -->
                <div class="native-slider__slide">
                    <div class="product-card asic-product-card" id="<?= $this->GetEditAreaId($arItem['ID']); ?>"
                         data-product-id="<?= $arItem['ID'] ?>"
                         data-name="<?= htmlspecialchars($arItem['NAME']) ?>"
                         data-price="<?= htmlspecialchars($priceFormatted) ?>"
                         data-price-raw="<?= (float)$priceValue ?>"
                         data-photo="<?= htmlspecialchars($imageSrc) ?>">
                        <a href="<?= $detailUrl ?>" class="product-card__link js-product-link">
                            <div class="product-card__header">
                                <div class="product-card__tags">
                                    <?php // Проверка свойства "Хит". Для свойств типа "Список" значение лежит в VALUE.
                                    if (!empty($arItem["PROPERTIES"]["FEATURED"]["VALUE"])): ?>
                                        <span class="tag tag--hit">Хит</span>
                                    <?php endif; ?>

                                    <?php // Проверка наличия цены для определения статуса товара
                                    if (!empty($arItem["PROPERTIES"]["IN_STOCK"]["VALUE"])): ?>
                                        <span class="tag tag--in-stock">В наличии</span>
                                    <?php else: ?>
                                        <!-- <span class="tag tag-in-stock">Предзаказ</span> -->
                                    <?php endif; ?>
                                </div>
                                <div class="product-card__image-wrapper">
                                    <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($arItem["NAME"]) ?>"
                                         class="product-card__image">
                                </div>
                                <div class="product-card__dots">
                                    <span class="product-card__dot product-card__dot--active"></span><span
                                            class="product-card__dot"></span><span
                                            class="product-card__dot"></span><span class="product-card__dot"></span>
                                </div>
                                <div class="product-card__info">
                                    <span class="product-card__name"><?= htmlspecialchars($arItem["NAME"]) ?></span>
                                    <?php if ($prop = $arItem['PROPERTIES']['HASHRATE_TH']['VALUE']): ?>
                                        <p class="product-card__hashrate">Хешрейт: <?= htmlspecialchars($prop) ?>
                                            TH/s</p>
                                    <?php endif; ?>
                                    <?php if ($prop = $arItem['PROPERTIES']['HASHRATE_MH']['VALUE']): ?>
                                        <p class="product-card__hashrate">Хешрейт: <?= htmlspecialchars($prop) ?>
                                            MH/s</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-card__footer">
                                <p class="product-card__price">
                                    <?php // Проверка наличия цены
                                    if ($priceValue > 0): ?>
                                        <span class="ot_listing">от</span>
                                    <?php endif; ?>
                                    <?= $priceFormatted ?>
                                </p>
                            </div>
                        </a>
                        <div class="product-card__actions">
                            <div class="quantity-selector">
                                <button type="button"
                                        class="quantity-selector__btn quantity-selector__btn--minus js-quantity-minus"
                                        aria-label="Уменьшить количество">
                                    <svg width="25" height="25" viewBox="0 0 16 17" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <line x1="3.5" y1="8" x2="12.5" y2="8" stroke="black" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <input type="number" class="quantity-selector__input js-quantity-value"
                                       id="quantity-<?= $arItem['ID'] ?>" value="1" min="1" max="999"
                                       aria-label="Количество товара">
                                <button type="button"
                                        class="quantity-selector__btn quantity-selector__btn--plus js-quantity-plus"
                                        aria-label="Увеличить количество">
                                    <svg width="25" height="25" viewBox="0 0 16 17" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 3.5V13.5" stroke="black" stroke-linecap="round"/>
                                        <path d="M3 8.5H13" stroke="black" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form"
                                    data-form-type="cart" data-metric-goal="listing_zakaz_click">Заказать
                            </button>
                        </div>
                    </div>
                </div><!-- /.native-slider__slide -->
            <?php endforeach; ?>
        </div><!-- /.native-slider__wrapper -->

        <!-- Стрелки навигации -->
        <button type="button" class="products-slider__arrow products-slider__arrow--prev js-products-prev"
                aria-label="Предыдущий товар">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
        </button>
        <button type="button" class="products-slider__arrow products-slider__arrow--next js-products-next"
                aria-label="Следующий товар">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
        </button>
    </div><!-- /.products-slider -->

    <?php


    // ВЫВОД ПАГИНАЦИИ - КРИТИЧЕСКИ ВАЖНО!
//    if (isset($arResult["NAV_STRING"]) && !empty($arResult["NAV_STRING"])) {
//        echo $arResult["NAV_STRING"];
//    } else {
//        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
//            echo '<div style="background: #ffcccc; padding: 10px; margin: 10px; border: 1px solid #ff0000;">';
//            echo '<h4>ОШИБКА: Пагинация не выводится!</h4>';
//            echo '<p>NAV_STRING не найден или пустой</p>';
//            echo '</div>';
//        }
//    }
//    ?>


<?php else: ?>
    <div class="product-grid">
        <p>Товары не найдены</p>
    </div>
<?php endif; ?>