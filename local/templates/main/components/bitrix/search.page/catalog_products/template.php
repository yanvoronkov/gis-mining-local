<?php
/**
 * Шаблон результатов поиска - идентичный каталогу ASIC
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);
?>

<?php if (!empty($arResult["SEARCH"])): ?>
    
    <div class="search-results">
        <div class="search-results__header" style="margin-bottom: 30px;">
            <h2>Результаты поиска по запросу: "<?= htmlspecialchars($arResult["SEARCH_QUERY"]) ?>"</h2>
            <p class="search-results__count" style="color: #666; margin-top: 10px;">
                Найдено товаров: <strong><?= $arResult["PRODUCT_COUNT"] ?></strong>
            </p>
        </div>
        
        <!-- Идентичная сетка из каталога -->
        <div class="product-grid asic-miners__product-grid">
            <?php foreach ($arResult["SEARCH"] as $arItem): ?>
                
                <?php if ($arItem["IS_PRODUCT"] && !empty($arItem["PRODUCT_DATA"])): ?>
                    <?php 
                    $product = $arItem["PRODUCT_DATA"];
                    
                    // Цена
                    $priceFormatted = $product['PRICE'] ?? 'Под заказ';
                    $priceRaw = 0;
                    if ($product['PRICE'] && preg_match('/[\d\s]+/', $product['PRICE'], $matches)) {
                        $priceRaw = (float)str_replace(' ', '', $matches[0]);
                    }
                    
                    // Ссылка на детальную страницу
                    $detailUrl = $product['DETAIL_PAGE_URL'];
                    
                    // Изображение
                    $imageSrc = !empty($product['IMAGE']) ? $product['IMAGE'] : '/local/templates/main/assets/img/no-photo.jpg';
                    ?>
                    
                    <div class="product-card asic-product-card" 
                         data-product-id="<?= $product['ID'] ?>"
                         data-name="<?= htmlspecialchars($product['NAME']) ?>"
                         data-price="<?= htmlspecialchars($priceFormatted) ?>"
                         data-price-raw="<?= $priceRaw ?>"
                         data-photo="<?= htmlspecialchars($imageSrc) ?>">
                        
                        <a href="<?= $detailUrl ?>" class="product-card__link js-product-link">
                            <div class="product-card__header">
                                <div class="product-card__tags">
                                    <!-- Теги, если нужны -->
                                </div>
                                
                                <div class="product-card__image-wrapper">
                                    <img src="<?= $imageSrc ?>" 
                                         alt="<?= htmlspecialchars($product['NAME']) ?>" 
                                         class="product-card__image"
                                         loading="lazy">
                                </div>
                                
                                <div class="product-card__dots">
                                    <span class="product-card__dot product-card__dot--active"></span>
                                    <span class="product-card__dot"></span>
                                    <span class="product-card__dot"></span>
                                    <span class="product-card__dot"></span>
                                </div>
                                
                                <div class="product-card__info">
                                    <span class="product-card__name"><?= htmlspecialchars($product['NAME']) ?></span>
                                    
                                    <?php if (!empty($product['PROPERTIES'])): ?>
                                        <?php foreach (array_slice($product['PROPERTIES'], 0, 3) as $prop): ?>
                                            <p class="product-card__hashrate">
                                                <?= htmlspecialchars($prop['NAME']) ?>: <?= htmlspecialchars($prop['VALUE']) ?>
                                            </p>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="product-card__footer">
                                <p class="product-card__price">
                                    <?php if ($priceRaw > 0): ?>
                                        <span class="ot_listing">от</span>
                                    <?php endif; ?>
                                    <?= $priceFormatted ?>
                                </p>
                            </div>
                        </a>
                        
                        <div class="product-card__actions">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-selector__btn quantity-selector__btn--minus js-quantity-minus" aria-label="Уменьшить количество">
                                    <svg width="25" height="25" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <line x1="3.5" y1="8" x2="12.5" y2="8" stroke="black" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <input type="number" class="quantity-selector__input js-quantity-value" 
                                       id="quantity-<?= $product['ID'] ?>" 
                                       value="1" min="1" max="999" 
                                       aria-label="Количество товара">
                                <button type="button" class="quantity-selector__btn quantity-selector__btn--plus js-quantity-plus" aria-label="Увеличить количество">
                                    <svg width="25" height="25" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 3.5V13.5" stroke="black" stroke-linecap="round"/>
                                        <path d="M3 8.5H13" stroke="black" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form" 
                                    data-form-type="cart" 
                                    data-metric-goal="listing_zakaz_click">
                                Заказать
                            </button>
                        </div>
                    </div>
                    
                <?php endif; ?>
                
            <?php endforeach; ?>
        </div>
        
        <!-- Ручная пагинация как в каталоге ASIC -->
        <?php if (!empty($arResult["PAGINATION"]) && $arResult["PAGINATION"]['TOTAL_PAGES'] > 1): ?>
            <div class="pagination">
                <?php
                $totalPages = $arResult["PAGINATION"]['TOTAL_PAGES'];
                $currentPage = $arResult["PAGINATION"]['CURRENT_PAGE'];
                
                // Сохраняем все текущие GET-параметры (включая запрос), меняем только PAGEN_1
                $queryParams = $_GET;
                unset($queryParams['PAGEN_1']);
                $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
                
                for ($i = 1; $i <= $totalPages; $i++):
                    $queryParams['PAGEN_1'] = $i;
                    $url = $baseUrl . '?' . http_build_query($queryParams);
                    if ($i == $currentPage): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($url) ?>"><?= $i ?></a>
                    <?php endif;
                endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    
<?php else: ?>
    <div class="search-empty" style="padding: 40px 20px; text-align: center;">
        <h3>По вашему запросу "<?= htmlspecialchars($arResult["SEARCH_QUERY"]) ?>" ничего не найдено</h3>
        <p style="color: #666; margin-top: 15px;">Попробуйте изменить запрос или воспользоваться поиском в каталоге</p>
        <a href="/catalog/" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Перейти в каталог</a>
    </div>
<?php endif; ?>
