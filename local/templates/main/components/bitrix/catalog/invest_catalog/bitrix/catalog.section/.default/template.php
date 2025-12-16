<?php
/**
 * Универсальный шаблон компонента catalog.section для контентных разделов
 * Выводит свойства динамически на основе LIST_PROPERTY_CODE
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);

// Маппинг кодов свойств на человекочитаемые названия
// Используется для отображения в карточке товара
$propertyLabels = [
    // GPU
    'MANUFACTURER' => 'Производитель',
    'GPU_POWER' => 'Мощность',
    'GPU_ENGINE' => 'Двигатель',
    'GPU_COUNTRY_OF_ORIGIN' => 'Страна производства',
    'HASHRATE' => 'Хешрейт',
    'ALGORITHM' => 'Алгоритм',
    'EFFICIENCY' => 'Эффективность',
    'POWER' => 'Потребление',

    // Инвестиции / Бизнес
    'DEVICES_COUNT' => 'Количество устройств',
    'YEARLY_PROFIT' => 'Прибыль в год',
    'MONTHLY_INCOME' => 'Прибыль в мес',
    'PAYBACK_PERIOD' => 'Окупаемость',

    // Контейнеры
    'CONTAINER_SIZE' => 'Размер контейнера',
    'CONTAINER_TYPE' => 'Тип контейнера',
    'COOLING_TYPE' => 'Тип охлаждения',
    'MAX_DEVICES' => 'Макс. устройств',

    // Крипто
    'CRYPTO' => 'Криптовалюта',
];

// Суффиксы для значений свойств (единицы измерения)
$propertySuffixes = [
    'GPU_POWER' => ' кВт',
    'POWER' => ' Вт',
    'YEARLY_PROFIT' => ' ₽',
    'MONTHLY_INCOME' => ' ₽',
    'PAYBACK_PERIOD' => ' дн',
    'HASHRATE' => '',
];
?>

<?php if (!empty($arResult["ITEMS"])): ?>
    <div class="product-grid invest-catalog__product-grid">
        <?php foreach ($arResult["ITEMS"] as $arItem): ?>
            <?php
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

            // --- ПОДГОТОВКА ДАННЫХ ДЛЯ КАРТОЧКИ ---
    
            // Получаем цену
            $priceValue = $arItem['ITEM_PRICES'][0]['PRICE'] ?? 0;
            $priceFormatted = ($priceValue > 0) ? number_format($priceValue, 0, '.', ' ') . ' ₽' : 'Под заказ';

            // Изображение
            $imageSrc = !empty($arItem["PREVIEW_PICTURE"]["SRC"]) ? $arItem["PREVIEW_PICTURE"]["SRC"] : '/local/templates/main/assets/img/no-photo.jpg';
            ?>
            <div class="product-card invest-product-card" data-product-id="<?= $arItem['ID'] ?>"
                data-name="<?= htmlspecialchars($arItem['NAME']) ?>" data-price="<?= htmlspecialchars($priceFormatted) ?>"
                data-price-raw="<?= (float) $priceValue ?>" data-photo="<?= htmlspecialchars($imageSrc) ?>">
                <div class="product-card__header">
                    <?php
                    // Теги криптовалюты (если есть)
                    if (!empty($arItem["PROPERTIES"]["CRYPTO"]["VALUE"])):
                        $cryptoValues = is_array($arItem["PROPERTIES"]["CRYPTO"]["VALUE"])
                            ? $arItem["PROPERTIES"]["CRYPTO"]["VALUE"]
                            : [$arItem["PROPERTIES"]["CRYPTO"]["VALUE"]];
                        ?>
                        <div class="product-card__tags">
                            <?php foreach ($cryptoValues as $crypto): ?>
                                <span class="tag tag--white"><?= htmlspecialchars($crypto) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="product-card__image-wrapper">
                        <img class="product-card__image" src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($arItem["NAME"]) ?>"
                            loading="lazy">
                    </div>
                    <div class="product-card__dots">
                        <span class="product-card__dot product-card__dot--active"></span>
                        <span class="product-card__dot"></span>
                        <span class="product-card__dot"></span>
                        <span class="product-card__dot"></span>
                    </div>
                </div>
                <div class="product-card__info">
                    <div class="product-card__name"><?= htmlspecialchars($arItem["NAME"]) ?></div>
                    <p class="product-card__costfrom"><?= $priceFormatted ?></p>

                    <?php
                    // УНИВЕРСАЛЬНЫЙ ВЫВОД СВОЙСТВ
                    // Выводим все свойства из PROPERTIES, которые:
                    // 1. Имеют непустое значение
                    // 2. Не являются служебными (CRYPTO уже выведен как теги)
            
                    $excludeProps = ['CRYPTO', 'MORE_PHOTO', 'CML2_MANUFACTURER']; // Свойства-исключения
            
                    foreach ($arItem["PROPERTIES"] as $propCode => $propData):
                        // Пропускаем исключённые свойства
                        if (in_array($propCode, $excludeProps))
                            continue;

                        // Пропускаем пустые значения
                        $propValue = $propData["VALUE"] ?? null;
                        if (empty($propValue))
                            continue;

                        // Для множественных свойств объединяем значения
                        if (is_array($propValue)) {
                            $propValue = implode(', ', $propValue);
                        }

                        // Получаем человекочитаемое название свойства
                        $propLabel = $propertyLabels[$propCode] ?? $propData["NAME"] ?? $propCode;

                        // Получаем суффикс (единицу измерения)
                        $propSuffix = $propertySuffixes[$propCode] ?? '';
                        ?>
                        <div class="product-card__property-item">
                            <p class="product-card__numofdev-name"><?= htmlspecialchars($propLabel) ?></p>
                            <p class="product-card__numofdev-value"><?= htmlspecialchars($propValue) ?><?= $propSuffix ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="product-card__action">
                    <button class="btn btn-primary product-card__order-btn js-add-to-cart js-open-popup-form">Получить
                        КП</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-products">
        <p>Товары не найдены</p>
    </div>
<?php endif; ?>