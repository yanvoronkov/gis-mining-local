<?php
/**
 * Шаблон карточки товара для ASIC-майнеров (IBLOCK_ID = 1)
 * Включает калькулятор доходности и специфичные характеристики
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

// Получаем данные товара из $arResult
$product = $arResult;

// Формируем ALT-атрибуты для изображений
$mainImageAlt = !empty($product['DETAIL_PICTURE']['ALT']) ? $product['DETAIL_PICTURE']['ALT'] : $product['NAME'];
$mainImageTitle = !empty($product['DETAIL_PICTURE']['TITLE']) ? $product['DETAIL_PICTURE']['TITLE'] : $product['NAME'];

?>

<?php
// --- Сохраняем просмотренные товары в сессию ---
if (!isset($_SESSION['VIEWED_PRODUCTS'])) {
    $_SESSION['VIEWED_PRODUCTS'] = [];
}

// Убираем текущий товар, если он уже есть в массиве
$_SESSION['VIEWED_PRODUCTS'] = array_diff($_SESSION['VIEWED_PRODUCTS'], [$arResult['ID']]);

// Добавляем текущий товар в конец (он будет последним просмотренным)
$_SESSION['VIEWED_PRODUCTS'][] = $arResult['ID'];

// Храним не больше 20 последних ID
$_SESSION['VIEWED_PRODUCTS'] = array_slice($_SESSION['VIEWED_PRODUCTS'], -20, 20, true);

// Разворачиваем массив, чтобы последний просмотренный был первым
$viewedProductIds = array_reverse($_SESSION['VIEWED_PRODUCTS']);

// Ограничиваем вывод максимум 4 товарами
$viewedProductIds = array_slice($viewedProductIds, 0, 4);
?>
<!-- Хлебные крошки теперь выводятся глобально в header.php -->

<div class="product-desktop-grid container" id="app-root" data-iblock-id="1">
    <section class="product section-padding">
        <div class="product__color-block"></div>
        <!-- Компонент живого поиска по каталогу -->
        <?php
        $APPLICATION->IncludeComponent(
            "custom:catalog.search",
            ".default",
            array(
                "IBLOCK_IDS" => array(1, 3, 4, 5, 6, 11),
                "MIN_QUERY_LENGTH" => 2,
                "MAX_RESULTS" => 10,
                "SHOW_PRICE" => "Y",
                "PRICE_CODE" => "BASE",
                "CACHE_TIME" => 3600,
            )
        );
        ?>
        <h1 class="product__name section-title"><?= htmlspecialchars($product['NAME']) ?></h1>
        <div class="product__card-info card-info">
            <?php
            // --- Получаем отзывы для текущего товара ---
            if (!\Bitrix\Main\Loader::includeModule('highloadblock')) {
                return;
            }

            $productId = (int) $arResult["ID"];
            $hlbl = 2; // ID HL-блока "Отзывы"
            
            $reviewsCount = 0;
            $averageRating = 0;

            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
            if ($hlblock) {
                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
                $entityClass = $entity->getDataClass();

                $ratings = [];
                $res = $entityClass::getList([
                    'select' => ['UF_RATING'],
                    'filter' => ['UF_PRODUCT_ID' => $productId],
                ]);
                while ($row = $res->fetch()) {
                    $ratings[] = (int) $row['UF_RATING'];
                }

                $reviewsCount = count($ratings);
                $averageRating = $reviewsCount > 0 ? round(array_sum($ratings) / $reviewsCount, 1) : 0;
            }

            // --- Функция склонения "отзывов" ---
            function pluralForm($n, $forms)
            {
                $n = abs($n) % 100;
                $n1 = $n % 10;
                if ($n > 10 && $n < 20)
                    return $forms[2];
                if ($n1 > 1 && $n1 < 5)
                    return $forms[1];
                if ($n1 == 1)
                    return $forms[0];
                return $forms[2];
            }

            ?>

            <div class="card-info__rating">
                <div class="card-info__icon card-info__icon--star">
                    <svg width="23" height="21" viewBox="0 0 23 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10.6033 0.735933C10.9701 -0.00731289 12.0299 -0.007315 12.3967 0.735931L14.941 5.89115C15.0866 6.18629 15.3682 6.39086 15.6939 6.43819L21.383 7.26487C22.2033 7.38405 22.5308 8.39202 21.9373 8.97056L17.8206 12.9833C17.5849 13.2131 17.4773 13.5441 17.533 13.8685L18.5048 19.5346C18.6449 20.3515 17.7875 20.9745 17.0538 20.5888L11.9653 17.9136C11.674 17.7604 11.326 17.7604 11.0347 17.9136L5.94616 20.5888C5.21253 20.9745 4.3551 20.3515 4.49521 19.5346L5.46702 13.8685C5.52266 13.5441 5.41511 13.2131 5.17943 12.9833L1.06274 8.97056C0.469227 8.39202 0.796736 7.38405 1.61696 7.26487L7.30607 6.43819C7.63178 6.39086 7.91335 6.18629 8.05901 5.89115L10.6033 0.735933Z"
                            fill="#FFC700" />
                    </svg>
                </div>
                <span class="card-info__text"><b><?= $averageRating ?></b></span>
                <?php if ($reviewsCount > 0): ?>
                    <a href="#reviews" class="card-info__reviews">
                        <?= $reviewsCount ?>     <?= pluralForm($reviewsCount, ["отзыв", "отзыва", "отзывов"]) ?>
                    </a>
                <?php else: ?>
                    <a href="#reviews" class="card-info__reviews">Нет отзывов</a>
                <?php endif; ?>
            </div>

            <!-- <div class="card-info__actions action-chart">
                        <div class="card-info__icon card-info__icon-chart">
                          <svg width="25" height="26" viewBox="0 0 25 26" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                 <path d="M14.2852 21.2314V5.60645H11.8813V21.2314H14.2852Z" fill="#5B61FF" />
                                 <path d="M20.3105 21.2314V10.8148H17.9069V21.2314H20.3105Z" fill="#5B61FF" />
                                 <path d="M5.72852 21.2315V16.0231H8.13242V21.2315H5.72852Z" fill="#5B61FF" />
                           </svg>
                         </div>
                        <span>Сравнить</span>
                   </div> -->
            <div class="card-info__actions action-arrow js-open-popup-form" data-form-type="share">
                <div class="card-info__icon card-info__icon-arrow">
                    <svg width="25" height="26" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5.20768 20.1898V16.544C5.20768 15.6091 5.53824 14.8111 6.19935 14.1502C6.86029 13.4891 7.6582 13.1585 8.5931 13.1585H18.3608L14.3504 17.169L15.4642 18.2666L21.3535 12.3773L15.4642 6.48796L14.3504 7.58562L18.3608 11.596H8.5931C7.22557 11.596 6.05907 12.0788 5.09362 13.0445C4.128 14.0099 3.64518 15.1764 3.64518 16.544V20.1898H5.20768Z"
                            fill="#6F7682" />

                    </svg>
                </div>
                <span>Поделиться</span>
            </div>

            <!-- Попап Поделиться статьей -->
            <div class="share-popup form-popup popup-form-wrapper" id="sharePopupFormWrapper" role="dialog"
                aria-modal="true" aria-labelledby="share-popup-title" hidden>

                <!-- Полупрозрачная подложка -->
                <div class="share-popup__overlay"></div>

                <!-- Контент -->
                <div class="share-popup__content">

                    <!-- Кнопка "Закрыть" -->
                    <button class="share-popup__close btn" id="closeSharePopupFormBtn" type="button"
                        aria-label="Закрыть">
                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.1094 6L6.10938 18" stroke="#6F7682" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M6.10938 6L18.1094 18" stroke="#6F7682" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Заголовок для скринридеров -->
                    <h2 class="share-popup__title visually-hidden" id="share-popup-title">Поделиться ссылкой</h2>

                    <!-- Соцсети -->
                    <ul class="share-popup__social-list">
                        <li class="share-popup__social-item">
                            <a href="https://t.me/share/url?url=<?= urlencode('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($arResult["NAME"]) ?>"
                                target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                                <div class="share-popup__icon-wrapper">
                                    <!-- Место для иконки Telegram -->
                                    <span class="share-popup__icon icon-telegram" aria-hidden="true">
                                        <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3286_53479)">
                                                <path
                                                    d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                                    fill="url(#paint0_linear_3286_53479)" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M14.5922 31.6619C23.9208 27.5975 30.1414 24.918 33.2538 23.6235C42.1406 19.9273 43.9872 19.2851 45.1907 19.2639C45.4554 19.2593 46.0473 19.3249 46.4307 19.636C46.9994 20.0975 47.0027 21.0993 46.9396 21.7623C46.4579 26.8223 44.3742 39.1014 43.3141 44.7686C42.8654 47.1666 41.9822 47.9708 41.1272 48.0494C39.2689 48.2204 37.8578 46.8213 36.0579 45.6415C33.2415 43.7953 31.6504 42.646 28.9166 40.8445C25.7572 38.7625 27.8053 37.6183 29.6058 35.7481C30.0771 35.2588 38.2647 27.8114 38.4232 27.1358C38.4431 27.0513 38.4614 26.7363 38.2743 26.57C38.0872 26.4038 37.811 26.4605 37.6118 26.5058C37.3293 26.5699 32.83 29.5438 24.114 35.4273C22.8369 36.3043 21.6802 36.7315 20.6438 36.7091C19.5012 36.6845 17.3034 36.0631 15.6696 35.532C13.6656 34.8806 12.0728 34.5361 12.2116 33.4299C12.2838 32.8535 13.0773 32.2643 14.5922 31.6619Z"
                                                    fill="white" />
                                            </g>
                                            <defs>
                                                <linearGradient id="paint0_linear_3286_53479" x1="32.1094" y1="0"
                                                    x2="32.1094" y2="63.5" gradientUnits="userSpaceOnUse">
                                                    <stop offset="0" stop-color="#2AABEE" />
                                                </linearGradient>
                                                <clipPath id="clip0_3286_53479">
                                                    <rect width="64" height="64" fill="white"
                                                        transform="translate(0.109375)" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </span>
                                </div>
                                <span class="share-popup__label">Telegram</span>
                            </a>
                        </li>
                        <li class="share-popup__social-item">
                            <a href="https://wa.me/?text=<?= urlencode($arResult["NAME"] . ' - https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>"
                                target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                                <div class="share-popup__icon-wrapper">
                                    <!-- Место для иконки WhatsApp -->
                                    <span class="share-popup__icon icon-whatsapp" aria-hidden="true">
                                        <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3286_53485)">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M32.6094 0C50.2831 0 64.6094 14.3263 64.6094 32C64.6094 49.6738 50.2831 64 32.6094 64C14.9356 64 0.609375 49.6738 0.609375 32C0.609375 14.3263 14.9356 0 32.6094 0Z"
                                                    fill="url(#paint0_linear_3286_53485)" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M32.6101 46.7278C30.0038 46.7278 27.4388 46.034 25.1951 44.7228C24.8338 44.5115 24.3988 44.4553 23.9951 44.5653L19.1163 45.904L20.8163 42.1603C21.0476 41.6515 20.9876 41.0578 20.6613 40.604C18.8426 38.0853 17.8826 35.109 17.8826 32.0003C17.8826 23.879 24.4888 17.2728 32.6101 17.2728C40.7313 17.2728 47.3376 23.879 47.3376 32.0003C47.3376 40.1215 40.7313 46.7278 32.6101 46.7278ZM32.6101 14.1553C22.7701 14.1553 14.7663 22.1603 14.7663 32.0003C14.7663 35.4615 15.7476 38.7853 17.6163 41.6703L14.9051 47.6415C14.6551 48.1928 14.7463 48.8378 15.1376 49.2978C15.4388 49.649 15.8751 49.8453 16.3238 49.8453C17.3288 49.8453 22.8101 48.1228 24.2051 47.739C26.7838 49.119 29.6751 49.8453 32.6101 49.8453C42.4488 49.8453 50.4551 41.839 50.4551 32.0003C50.4551 22.1603 42.4488 14.1565 32.6101 14.1553Z"
                                                    fill="white" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M36.8758 34.0869C36.2008 34.3631 35.7696 35.4206 35.3321 35.9606C35.1071 36.2369 34.8396 36.2806 34.4946 36.1419C31.9596 35.1319 30.0158 33.4394 28.6171 31.1069C28.3796 30.7456 28.4221 30.4594 28.7083 30.1231C29.1308 29.6256 29.6621 29.0606 29.7758 28.3894C30.0296 26.9069 28.0908 22.3056 25.5296 24.3906C18.1608 30.3956 37.8221 46.3219 41.3708 37.7081C42.3746 35.2656 37.9958 33.6281 36.8758 34.0869Z"
                                                    fill="white" />
                                            </g>
                                            <defs>
                                                <linearGradient id="paint0_linear_3286_53485" x1="32.6094" y1="64"
                                                    x2="35.6094" y2="4.46104e-07" gradientUnits="userSpaceOnUse">
                                                    <stop offset="1" stop-color="#60D66A" />
                                                </linearGradient>
                                                <clipPath id="clip0_3286_53485">
                                                    <rect width="64" height="64" fill="white"
                                                        transform="translate(0.609375)" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </span>
                                </div>
                                <span class="share-popup__label">WhatsApp</span>
                            </a>
                        </li>
                        <li class="share-popup__social-item">
                            <a href="https://vk.com/share.php?url=<?= urlencode('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($arResult["NAME"]) ?>"
                                target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                                <div class="share-popup__icon-wrapper">
                                    <!-- Место для иконки VK -->
                                    <span class="share-popup__icon icon-vk" aria-hidden="true">
                                        <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                                fill="url(#paint0_linear_3286_53495)" />
                                            <path
                                                d="M50.551 44.5234C49.6201 44.6543 45.0674 44.5234 44.831 44.5234C43.6541 44.5384 42.5187 44.089 41.671 43.2725C40.711 42.3488 39.8528 41.3306 38.9147 40.3634C38.6372 40.0697 38.3369 39.7986 38.0165 39.5525C37.2674 38.9743 36.5256 39.1052 36.1728 39.9888C35.8973 40.9437 35.705 41.9207 35.5983 42.9088C35.5365 43.8034 34.9692 44.3634 33.9656 44.4252C33.3438 44.4579 32.7219 44.4688 32.1074 44.4543C29.8677 44.44 27.6633 43.8954 25.6747 42.8652C23.5229 41.6662 21.6482 40.0269 20.1728 38.0543C17.7474 34.9561 15.8092 31.5525 14.0783 28.0434C13.9874 27.8688 12.2128 24.0943 12.1692 23.9161C12.0092 23.3197 12.1692 22.7488 12.6601 22.5525C12.9728 22.4325 18.8201 22.5525 18.9183 22.5525C19.353 22.5487 19.7776 22.6834 20.1307 22.9371C20.4837 23.1907 20.7468 23.5502 20.8819 23.9634C21.9629 26.7311 23.4428 29.3258 25.2747 31.6652C25.531 31.978 25.8311 32.2523 26.1656 32.4797C26.6419 32.8034 27.0928 32.6906 27.2892 32.1416C27.5219 31.3797 27.6587 30.5918 27.6965 29.7961C27.7292 28.2179 27.6965 27.1779 27.6092 25.6034C27.551 24.5925 27.1947 23.7052 25.7038 23.4216C25.2456 23.3416 25.2056 22.9634 25.5001 22.5852C26.1147 21.7997 26.9547 21.6761 27.8965 21.6288C29.3001 21.5488 30.7038 21.6034 32.1074 21.6288H32.4128C33.0237 21.6266 33.6332 21.6888 34.231 21.8143C34.5844 21.8858 34.9053 22.0695 35.1458 22.3382C35.3863 22.6069 35.5335 22.946 35.5656 23.3052C35.6374 23.7034 35.6679 24.108 35.6565 24.5125C35.6201 26.2361 35.5365 27.9561 35.5183 29.6797C35.5015 30.3622 35.5638 31.0443 35.7038 31.7125C35.9038 32.6288 36.5328 32.8579 37.1583 32.1961C37.9682 31.3405 38.711 30.4239 39.3801 29.4543C40.5633 27.6872 41.5394 25.7898 42.2892 23.7997C42.6965 22.7779 43.0165 22.5525 44.1074 22.5525H50.311C50.6799 22.546 51.0476 22.595 51.4019 22.6979C51.5273 22.7309 51.6446 22.7894 51.7463 22.8697C51.8481 22.9501 51.9321 23.0506 51.9932 23.165C52.0543 23.2793 52.0912 23.4051 52.1014 23.5343C52.1117 23.6636 52.0951 23.7935 52.0528 23.9161C51.7474 25.2761 51.0128 26.4361 50.2347 27.5525C48.9728 29.3416 47.6419 31.0834 46.351 32.8434C46.1878 33.0768 46.0384 33.3196 45.9038 33.5706C45.4165 34.447 45.4528 34.9379 46.1583 35.6688C47.2856 36.8252 48.4892 37.9125 49.5765 39.1052C50.3748 39.9723 51.0952 40.908 51.7292 41.9016C52.5256 43.1343 52.0347 44.3125 50.551 44.5234Z"
                                                fill="white" />
                                            <defs>
                                                <linearGradient id="paint0_linear_3286_53495" x1="32.1094" y1="2.29091"
                                                    x2="32.1094" y2="57.7491" gradientUnits="userSpaceOnUse">
                                                    <stop offset="0" stop-color="#4B7AFF" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </span>
                                </div>
                                <span class="share-popup__label">VK</span>
                            </a>
                        </li>
                        <li class="share-popup__social-item">
                            <a href="https://connect.ok.ru/offer?url=<?= urlencode('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($arResult["NAME"]) ?>"
                                target="_blank" rel="noopener noreferrer nofollow" class="share-popup__social-button">
                                <div class="share-popup__icon-wrapper">
                                    <!-- Место для иконки OK -->
                                    <span class="share-popup__icon icon-ok" aria-hidden="true">
                                        <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                                fill="url(#paint0_linear_3286_53502)" />
                                            <path
                                                d="M32.1062 15.1582C30.4257 15.1582 28.7829 15.6565 27.3856 16.5902C25.9883 17.5238 24.8993 18.8509 24.2562 20.4034C23.6131 21.956 23.4448 23.6645 23.7726 25.3127C24.1005 26.9609 24.9097 28.4749 26.098 29.6632C27.2864 30.8515 28.8003 31.6608 30.4486 31.9886C32.0968 32.3165 33.8052 32.1482 35.3578 31.5051C36.9104 30.862 38.2374 29.7729 39.1711 28.3756C40.1047 26.9783 40.6031 25.3356 40.6031 23.655C40.6036 22.5391 40.3842 21.4339 39.9574 20.4028C39.5306 19.3717 38.9047 18.4348 38.1156 17.6456C37.3265 16.8565 36.3896 16.2307 35.3585 15.8039C34.3273 15.3771 33.2222 15.1577 32.1062 15.1582ZM32.1062 27.8908C31.2685 27.8908 30.4495 27.6424 29.7529 27.177C29.0564 26.7115 28.5135 26.05 28.1929 25.276C27.8723 24.502 27.7884 23.6503 27.9518 22.8287C28.1153 22.007 28.5187 21.2523 29.1111 20.6599C29.7035 20.0675 30.4582 19.6641 31.2799 19.5006C32.1015 19.3372 32.9532 19.4211 33.7272 19.7417C34.5012 20.0623 35.1627 20.6052 35.6282 21.3018C36.0936 21.9983 36.342 22.8173 36.342 23.655C36.3426 24.2115 36.2334 24.7625 36.0207 25.2767C35.808 25.7908 35.496 26.258 35.1026 26.6514C34.7092 27.0449 34.242 27.3569 33.7278 27.5695C33.2137 27.7822 32.6626 27.8914 32.1062 27.8908Z"
                                                fill="white" />
                                            <path
                                                d="M44.6271 33.4532C44.4421 33.1229 44.1937 32.8323 43.8963 32.598C43.5989 32.3637 43.2582 32.1904 42.8937 32.0879C42.5292 31.9855 42.1481 31.9559 41.7722 32.0009C41.3963 32.0458 41.0329 32.1645 40.7029 32.35C34.9176 35.5922 29.2923 35.5922 23.5071 32.35C23.1753 32.1648 22.8103 32.0467 22.4329 32.0026C22.0555 31.9584 21.6731 31.989 21.3076 32.0926C20.942 32.1962 20.6004 32.3708 20.3023 32.6064C20.0042 32.842 19.7555 33.1341 19.5702 33.4658C19.385 33.7976 19.2669 34.1626 19.2227 34.54C19.1786 34.9174 19.2092 35.2998 19.3128 35.6653C19.522 36.4036 20.016 37.0286 20.686 37.4027C22.5839 38.4798 24.6115 39.3104 26.7197 39.8743L22.6271 43.9669C22.0894 44.5046 21.7874 45.2339 21.7874 45.9943C21.7874 46.7546 22.0894 47.4839 22.6271 48.0216C23.1648 48.5593 23.894 48.8613 24.6544 48.8613C25.4148 48.8613 26.1441 48.5593 26.6818 48.0216L32.105 42.5774L37.5281 48.0006C38.0658 48.5382 38.7951 48.8402 39.5555 48.8402C40.3159 48.8402 41.0451 48.5382 41.5829 48.0006C42.1205 47.4628 42.4225 46.7336 42.4225 45.9732C42.4225 45.2128 42.1205 44.4836 41.5829 43.9458L37.4902 39.8532C39.5984 39.2893 41.626 38.4588 43.5239 37.3816C43.8548 37.1966 44.1459 36.9481 44.3805 36.6504C44.6152 36.3526 44.7887 36.0115 44.8912 35.6465C44.9937 35.2815 45.0231 34.8999 44.9778 34.5235C44.9325 34.1471 44.8133 33.7834 44.6271 33.4532Z"
                                                fill="white" />
                                            <defs>
                                                <linearGradient id="paint0_linear_3286_53502" x1="32.1094" y1="0"
                                                    x2="32.1094" y2="64" gradientUnits="userSpaceOnUse">
                                                    <stop offset="0" stop-color="#FF9800" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </span>
                                </div>
                                <span class="share-popup__label">OK</span>
                            </a>
                        </li>
                        <li class="share-popup__social-item">
                            <a href="mailto:?subject=<?= urlencode($arResult["NAME"]) ?>&body=<?= urlencode('Поделился статьей: ' . $arResult["NAME"] . ' - https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) ?>"
                                class="share-popup__social-button">
                                <div class="share-popup__icon-wrapper">
                                    <!-- Место для иконки E-mail -->
                                    <span class="share-popup__icon icon-email" aria-hidden="true">
                                        <svg width="65" height="64" viewBox="0 0 65 64" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M32.1094 64C49.7825 64 64.1094 49.6731 64.1094 32C64.1094 14.3269 49.7825 0 32.1094 0C14.4363 0 0.109375 14.3269 0.109375 32C0.109375 49.6731 14.4363 64 32.1094 64Z"
                                                fill="#2196F3" />
                                            <path
                                                d="M32.1019 33.5803L15.2344 25.1653V41.8853C15.2367 42.7329 15.5744 43.5452 16.1738 44.1446C16.7732 44.744 17.5855 45.0817 18.4331 45.084H45.7856C46.6333 45.0817 47.4456 44.744 48.0449 44.1446C48.6443 43.5452 48.9821 42.7329 48.9844 41.8853V25.3415L32.1019 33.5803Z"
                                                fill="white" />
                                            <path
                                                d="M32.1169 30.42L48.9844 22.1875V22.115C48.9821 21.2674 48.6443 20.4551 48.0449 19.8557C47.4456 19.2563 46.6333 18.9186 45.7856 18.9163H18.4331C17.605 18.9179 16.8095 19.2398 16.2133 19.8146C15.6171 20.3894 15.2663 21.1725 15.2344 22L32.1169 30.42Z"
                                                fill="white" />
                                        </svg>

                                    </span>
                                </div>
                                <span class="share-popup__label">E-mail</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Копирование ссылки -->
                    <div class="share-popup__copy-section">
                        <div class="share-popup__copy-input-wrapper">
                            <input class="share-popup__copy-input" type="text"
                                value="https://<?= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ?>" readonly
                                aria-label="Ссылка для копирования">
                        </div>
                        <button class="share-popup__copy-button btn-primary btn" type="button">
                            <span class="share-popup__copy-button-text">Скопировать ссылку</span>
                        </button>
                        <div class="toast-notification" role="status" aria-live="polite">
                            <div class="toast-notification__icon-wrapper">
                                <span class="toast-notification__icon icon-success" aria-hidden="true"></span>
                            </div>
                            <p class="toast-notification__text">Ссылка скопирована</p>
                            <button class="toast-notification__close btn" type="button"
                                aria-label="Закрыть уведомление"></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-info__line">
                <div class="card-info__icon card-info__icon-checkmark">
                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M8.04232 13.4333L15.0454 6.43014L13.9478 5.33248L8.04232 11.238L5.07357 8.2692L3.97591 9.36686L8.04232 13.4333ZM2.52956 18.2731C2.00334 18.2731 1.55794 18.0908 1.19336 17.7262C0.828776 17.3616 0.646484 16.9163 0.646484 16.39V2.44785C0.646484 1.92163 0.828776 1.47623 1.19336 1.11165C1.55794 0.747065 2.00334 0.564774 2.52956 0.564774H16.4717C16.998 0.564774 17.4434 0.747065 17.8079 1.11165C18.1725 1.47623 18.3548 1.92163 18.3548 2.44785V16.39C18.3548 16.9163 18.1725 17.3616 17.8079 17.7262C17.4434 18.0908 16.998 18.2731 16.4717 18.2731H2.52956Z"
                            fill="#18A53A" />
                    </svg>
                </div>
                <a href="/razmeschenie/" target="_blank"><span class="card-info__text">Доступно размещение на хостинге
                        GIS Mining</span></a>
            </div>
        </div>
        <div class="product__gallery-column js-product-gallery">
            <?php
            // --- Собираем все изображения в один массив для удобства ---
            $allImages = [];
            // Детальная картинка всегда будет первой
            if (!empty($product['DETAIL_PICTURE']['SRC'])) {
                $allImages[] = array(
                    'SRC' => $product['DETAIL_PICTURE']['SRC'],
                    'ALT' => $mainImageAlt,
                    'TITLE' => $mainImageTitle
                );
            }
            // Добавляем остальные картинки из галереи
            if (!empty($product['PROPERTIES']['MORE_PHOTO']['VALUE'])) {
                foreach ($product['PROPERTIES']['MORE_PHOTO']['VALUE'] as $photoId) {
                    $photo = CFile::GetFileArray($photoId);
                    if ($photo) {
                        $allImages[] = array(
                            'SRC' => $photo['SRC'],
                            'ALT' => !empty($photo['ALT']) ? $photo['ALT'] : $product['NAME'],
                            'TITLE' => !empty($photo['TITLE']) ? $photo['TITLE'] : $product['NAME']
                        );
                    }
                }
            }
            ?>

            <div class="product__image">
                <div class="product__tags">
                    <?php if (!empty($product['PROPERTIES']['FEATURED']['VALUE'])): ?>
                        <span class="product__tag tag--hit">Хит</span>
                    <?php endif; ?>
                    <?php if (!empty($product['PROPERTIES']['IN_STOCK']['VALUE'])): ?>
                        <span class="product__tag tag--in-stock">В наличии</span>
                    <?php endif; ?>
                </div>

                <div class="product__image-wrapper js-native-slider js-gallery-wrapper" data-native-slider-options='{
                        "pagination": {
                            "el": ".js-gallery-dots",
                            "clickable": true,
                            "bulletClass": "product__dot",
                            "bulletActiveClass": "product__dot--active"
                        },
                        "navigation": {
                            "prevEl": ".js-gallery-prev",
                            "nextEl": ".js-gallery-next"
                        },
                        "loop": false
                    }'>
                    <!-- 2. Обертка для скролла. -->
                    <div class="native-slider__wrapper">
                        <?php // 3. Слайды. ?>
                        <?php foreach ($allImages as $index => $img): ?>
                            <div class="native-slider__slide js-gallery-item">
                                <img src="<?= htmlspecialchars($img['SRC']) ?>" alt="<?= htmlspecialchars($img['ALT']) ?>"
                                    title="<?= htmlspecialchars($img['TITLE']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Стрелки управления. JS найдет их по классам, указанным в опциях. -->
                    <button type="button" class="gallery-arrow gallery-arrow--prev js-gallery-prev">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                        </svg>
                    </button>
                    <button type="button" class="gallery-arrow gallery-arrow--next js-gallery-next">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <!-- Контейнер для пагинации. Он должен быть ПУСТЫМ. JS сам его наполнит. -->
                    <div class="product__dots js-gallery-dots"></div>
                </div>
            </div>

            <div class="product__carousel js-gallery-thumbnails">
                <?php // Превью-картинки внизу ?>
                <?php foreach ($allImages as $index => $img): ?>
                    <div class="product__carousel-item js-gallery-thumb" data-index="<?= $index ?>">
                        <img src="<?= htmlspecialchars($img['SRC']) ?>" alt="<?= htmlspecialchars($img['ALT']) ?>"
                            title="<?= htmlspecialchars($img['TITLE']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="product__properties">
            <?php
            // Получаем значение свойства "MODEL" текущего товара
            $rsProp = CIBlockElement::GetProperty(
                $arParams["IBLOCK_ID"],
                $arResult["ID"],
                "sort",
                "asc",
                array("CODE" => "MODEL")
            );
            $arModel = $rsProp->Fetch();

            if ($arModel && !empty($arModel["VALUE"])) {
                $modelValue = $arModel["VALUE"];

                // Получаем все товары с таким же MODEL
                $res = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                        "PROPERTY_MODEL" => $modelValue
                    ),
                    false,
                    false,
                    array("ID", "NAME", "CODE", "PROPERTY_HASHRATE_TH", "PROPERTY_HASHRATE_MH", "PROPERTY_HASHRATE_KSOL")
                );

                $arSameModelProducts = array();
                while ($arItem = $res->GetNext()) {
                    // Определяем, какое свойство хешрейта заполнено
                    $hashrateValue = 0;
                    $hashrateUnit = '';

                    if (!empty($arItem["PROPERTY_HASHRATE_TH_VALUE"])) {
                        $hashrateValue = $arItem["PROPERTY_HASHRATE_TH_VALUE"];
                        $hashrateUnit = 'TH/s';
                    } elseif (!empty($arItem["PROPERTY_HASHRATE_MH_VALUE"])) {
                        $hashrateValue = $arItem["PROPERTY_HASHRATE_MH_VALUE"];
                        $hashrateUnit = 'MH/s';
                    } elseif (!empty($arItem["PROPERTY_HASHRATE_KSOL_VALUE"])) {
                        $hashrateValue = $arItem["PROPERTY_HASHRATE_KSOL_VALUE"];
                        $hashrateUnit = 'KSol/s';
                    }

                    // Генерируем правильный URL на основе шаблона текущего раздела
                    $itemUrl = str_replace('#ELEMENT_CODE#', $arItem["CODE"], $arParams["DETAIL_URL"]);

                    if ($hashrateValue > 0) {
                        $arSameModelProducts[] = array(
                            "ID" => $arItem["ID"],
                            "NAME" => $arItem["NAME"],
                            "DETAIL_PAGE_URL" => $itemUrl,
                            "HASHRATE_VALUE" => $hashrateValue,
                            "HASHRATE_UNIT" => $hashrateUnit,
                            "DISPLAY_HASHRATE" => $hashrateValue . ' ' . $hashrateUnit
                        );
                    }
                }

                // Сортируем по значению хешрейта
                usort($arSameModelProducts, function ($a, $b) {
                    return $a["HASHRATE_VALUE"] - $b["HASHRATE_VALUE"];
                });

                $arResult["SAME_MODEL_PRODUCTS"] = $arSameModelProducts;
            }
            ?>

            <?php if (!empty($arResult["SAME_MODEL_PRODUCTS"])): ?>
                <div class="hashrate-selector">
                    <div class="hashrate-options">
                        <?php foreach ($arResult["SAME_MODEL_PRODUCTS"] as $item): ?>
                            <a href="<?= $item["DETAIL_PAGE_URL"] ?>"
                                class="hashrate-option <?= ($item["ID"] == $arResult["ID"]) ? 'active' : '' ?>">
                                <?= $item["DISPLAY_HASHRATE"] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>


            <div class="product__properties-container">
                <?php // Специфичные свойства для ASIC-майнеров ?>
                <?php if ($prop = $product['PROPERTIES']['PROFITABILITY']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Доходность, в %</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['SROK']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Срок окупаемости, в мес</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['MANUFACTURER']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Производитель</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>

                <?php
                $prop = $product['PROPERTIES']['CRYPTO']['VALUE'];
                if (!empty($prop)): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Криптовалюта</p>
                        <p class="product__property-item-value">
                            <?php
                            if (is_array($prop)) {
                                // Для множественного свойства: выводим через запятую
                                echo htmlspecialchars(implode(', ', $prop));
                            } else {
                                // Для одиночного
                                echo htmlspecialchars($prop);
                            }
                            ?>
                        </p>
                    </div>
                <?php endif; ?>


                <?php if ($prop = $product['PROPERTIES']['ALGORITHM']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Алгоритм</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['POWER']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Энергопотребление</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?> Вт</p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['GUARANTEE']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Гарантия</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['HASHRATE_TH']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Хешрейт</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?> TH/s</p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['HASHRATE_MH']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Хешрейт</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?> MH/s</p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['HASHRATE_KSOL']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Хешрейт</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?> kSol/s</p>
                    </div>
                <?php endif; ?>

                <?php if ($prop = $product['PROPERTIES']['EFFICIENCY']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Эффективность</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>




                <?php if ($prop = $product['PROPERTIES']['BRAND']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Производитель</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($prop = $product['PROPERTIES']['ARHITECTURE']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Архитектура</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($prop = $product['PROPERTIES']['STORAGE']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Память (тип/объём)</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($prop = $product['PROPERTIES']['CAPACITY']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Пропускная способность (GB/s)</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($prop = $product['PROPERTIES']['PROIZVOD']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Производительность FP16 / FP8 (TFLOPS)</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($prop = $product['PROPERTIES']['INTERFACE']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Интерфейс</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($prop = $product['PROPERTIES']['APPLICATION']['VALUE']): ?>
                    <div class="product__property-item">
                        <p class="product__property-item-title">Основное применение</p>
                        <p class="product__property-item-value"><?= htmlspecialchars($prop) ?></p>
                    </div>
                <?php endif; ?>


                <div class="product__more-property-item">
                    <p class="product__property-item-text js-more-specs-btn" style="cursor: pointer;">Больше
                        характеристик</p>
                </div>
            </div>

            <?php
            $priceValue = $product['ITEM_PRICES'][0]['PRICE'] ?? 0;
            $priceFormatted = ($priceValue > 0)
                ? number_format($priceValue, 0, '.', ' ') . ' ₽'
                : 'По запросу';
            ?>

            <div class="product__price">
                <?php if ($priceValue > 0): ?>
                    <!-- <span class="ot">от </span> -->
                    <span class="price-value"><?= htmlspecialchars($priceFormatted) ?></span>
                    <span class="price-nds">с&nbsp;НДС</span>
                <?php else: ?>
                    <?= htmlspecialchars($priceFormatted) ?>
                <?php endif; ?>

                <div class="product__found-cheaper js-open-popup-form" data-form-type="cheaper"
                    data-metric-goal="found-cheaper-click">
                    Нашли дешевле?
                </div>
            </div>





            <div class="product__actions product-card" data-product-id="<?= $product['ID'] ?>"
                data-name="<?= htmlspecialchars($product['NAME']) ?>"
                data-price="<?= htmlspecialchars($priceFormatted) ?>" data-price-raw="<?= (float) $priceValue ?>"
                data-photo="<?= htmlspecialchars($product['DETAIL_PICTURE']['SRC'] ?? '') ?>">
                <div class="quantity-selector">
                    <button type="button" class="quantity-selector__btn quantity-selector__btn--minus js-quantity-minus"
                        aria-label="Уменьшить количество">
                        <svg width="25" height="25" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="3.5" y1="8" x2="12.5" y2="8" stroke="black" stroke-linecap="round" />
                        </svg>
                    </button>
                    <input type="number" class="quantity-selector__input js-quantity-value"
                        id="quantity-<?= $product['ID'] ?>" value="1" min="1" max="999" aria-label="Количество товара">
                    <button type="button" class="quantity-selector__btn quantity-selector__btn--plus js-quantity-plus"
                        aria-label="Увеличить количество">
                        <svg width="25" height="25" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 3.5V13.5" stroke="black" stroke-linecap="round" />
                            <path d="M3 8.5H13" stroke="black" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
                <button class="btn btn-primary product__order-btn js-add-to-cart js-open-popup-form"
                    data-form-type="cart" data-metric-goal="zakaz_click">Заказать
                </button>
                <!-- <button class="btn product__rassrochka-btn js-open-popup-form" data-form-type="rassrochka" data-metric-goal="rassrochka_click">В рассрочку</button>-->
                <!-- <a href="/catalog/" class="btn product__back-btn">В каталог</a> -->
            </div>

        </div>
    </section>

    <!-- Калькулятор доходности для ASIC-майнеров -->
    <?php
    require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/mining-calculator/includes/functions.php';
    $profitCalculator = [
        'inCalculator' => $product['PROPERTIES']['IN_CALCULATOR']['VALUE'] == 'Да',
        'coin' => (!empty($product['PROPERTIES']['CRYPTO']['VALUE']) && !empty($product['PROPERTIES']['CRYPTO']['VALUE'][0])) ? $product['PROPERTIES']['CRYPTO']['VALUE'][0] : '',
        'hashrateTh' => trim($product['PROPERTIES']['HASHRATE_TH']['VALUE']),
        'hashrateMh' => trim($product['PROPERTIES']['HASHRATE_MH']['VALUE']),
        'power' => $product['PROPERTIES']['POWER']['VALUE'] ?: 0,
        'price' => (float) ($product['ITEM_PRICES'][0]['PRICE'] ?? 0),
        'cryptoUsdRates' => getCryptoUsdRates(),
        'usdtRubRate' => getUsdtRubRate(),
        'cryptoFfps' => getCryptoFfps(),
    ];
    ?>
    <?php if ($profitCalculator['inCalculator'] && ($profitCalculator['coin'] == 'BTC' || $profitCalculator['coin'] == 'LTC+DOGE')): ?>
        <section class="profit-calculator section-padding <?php
        if ($profitCalculator['coin'] == 'BTC') {
            echo 'profit-calculator--btc';
        } elseif ($profitCalculator['coin'] == 'LTC+DOGE') {
            echo 'profit-calculator--ltcdoge';
        }
        ?>">
            <div class="profit-calculator__wrapper">
                <div class="profit-calculator__container" id="profit-calculator">
                    <input type="hidden" id="pc-pool-fee" value="2">
                    <input type="hidden" id="pc-uptime" value="97">
                    <input type="hidden" id="pc-device-coin" value="<?= $profitCalculator['coin'] ?: '' ?>">
                    <input type="hidden" id="pc-device-price" value="<?= $profitCalculator['price'] ?: 0 ?>">
                    <input type="hidden" id="pc-device-hashrate-th" value="<?= $profitCalculator['hashrateTh'] ?: 0 ?>">
                    <input type="hidden" id="pc-device-hashrate-mh" value="<?= $profitCalculator['hashrateMh'] ?: 0 ?>">
                    <input type="hidden" id="pc-device-power" value="<?= $profitCalculator['power'] ?: 0 ?>">
                    <input type="hidden" id="pc-device-price" value="<?= $profitCalculator['price'] ?: 0 ?>">
                    <input type="hidden" id="pc-btcusd-rate"
                        value="<?= isset($profitCalculator['cryptoUsdRates']['btc']) ? round($profitCalculator['cryptoUsdRates']['btc'], 2) : 0 ?>">
                    <input type="hidden" id="pc-ltcusd-rate"
                        value="<?= isset($profitCalculator['cryptoUsdRates']['ltc']) ? round($profitCalculator['cryptoUsdRates']['ltc'], 2) : 0 ?>">
                    <input type="hidden" id="pc-dogeusd-rate"
                        value="<?= isset($profitCalculator['cryptoUsdRates']['doge']) ? round($profitCalculator['cryptoUsdRates']['doge'], 2) : 0 ?>">
                    <input type="hidden" id="pc-usdtrub-rate"
                        value="<?= isset($profitCalculator['usdtRubRate']['current']) ? round($profitCalculator['usdtRubRate']['current'], 2) : 0 ?>">
                    <input type="hidden" id="pc-btc-ffps"
                        value="<?= isset($profitCalculator['cryptoFfps']['btc']) ? $profitCalculator['cryptoFfps']['btc'] : 4.1e-7 ?>">
                    <input type="hidden" id="pc-ltc-ffps"
                        value="<?= isset($profitCalculator['cryptoFfps']['ltc']) ? $profitCalculator['cryptoFfps']['ltc'] : 3.7975443 ?>">
                    <input type="hidden" id="pc-doge-ffps"
                        value="<?= isset($profitCalculator['cryptoFfps']['doge']) ? $profitCalculator['cryptoFfps']['doge'] : 0.00102614 ?>">

                    <h2 class="profit-calculator__title">Калькулятор доходности</h2>

                    <form class="profit-calculator__form" name="profitCalculator">
                        <div class="profit-calculator__fields">
                            <div class="profit-calculator__field profit-calculator__field--quantity">
                                <div class="profit-calculator__field-label">Количество</div>
                                <div class="profit-calculator__field-input-wrapper">
                                    <input id="pc-quantity" class="profit-calculator__field-input" type="number"
                                        inputmode="numeric" value="1" min="1" step="1" required>
                                    <span class="profit-calculator__field-unit">шт</span>
                                </div>
                            </div>
                            <div class="profit-calculator__field profit-calculator__field--power-price">
                                <div class="profit-calculator__field-label">Цена электроэнергии</div>
                                <div class="profit-calculator__field-input-wrapper">
                                    <input id="pc-tariff" class="profit-calculator__field-input" type="number"
                                        inputmode="numeric" value="5.3" min="0" step="0.1" required>
                                    <span class="profit-calculator__field-unit">₽/кВт·ч</span>
                                </div>
                            </div>
                        </div>

                        <div class="profit-calculator__main-profit"
                            style="display:none;visibility:hidden;opacity:0;height:0;">
                            <div class="profit-calculator__main-label-value-wrap">
                                <div class="profit-calculator__main-label">
                                    Чистая прибыль в месяц <br><span>после вычета затрат на электричество</span>
                                </div>
                                <div class="profit-calculator__main-value highlighted-color preloader-block">
                                    <span id="pc-net-month" class="preloader-text">—</span> <span
                                        class="unit highlighted-color">₽</span>
                                </div>
                            </div>

                            <div class="profit-calculator__secondary-profit">
                                <div class="profit-calculator__secondary-item preloader-block">
                                    <div class="profit-calculator__secondary-label">Доход в месяц</div>
                                    <div class="profit-calculator__secondary-value"><span id="pc-income-month"
                                            class="preloader-text">—</span> <span class="unit">₽</span></div>
                                </div>
                                <div class="profit-calculator__secondary-item preloader-block">
                                    <div class="profit-calculator__secondary-label">Расход в месяц</div>
                                    <div class="profit-calculator__secondary-value"><span id="pc-consumption-month"
                                            class="preloader-text">—</span> <span class="unit">₽</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="profit-calculator__metrics" style="display:none;">
                            <div class="profit-calculator__metric-card preloader-block">
                                <div class="profit-calculator__metric-card-label">Доходность</div>
                                <div class="profit-calculator__metric-card-value highlighted-color">
                                    <span id="pc-profitability" class="preloader-text">—</span> <span
                                        class="unit">%/год</span>
                                </div>
                            </div>
                            <?php /*<div class="profit-calculator__metric-card preloader-block">
    <div class="profit-calculator__metric-card-label">Окупаемость</div>
    <div class="profit-calculator__metric-card-value highlighted-color">
      <span id="pc-payback" class="preloader-text">—</span>
    </div>
  </div>*/ ?>
                        </div>

                        <div class="profit-calculator__fields">
                            <?php if ($profitCalculator['coin'] == 'BTC') { ?>
                                <div class="profit-calculator__field profit-calculator__field--exchange-rate-btc-used">
                                    <div class="profit-calculator__field-label">BTC/USD</div>
                                    <div class="profit-calculator__field-input-wrapper">
                                        <input name="exchangeRateBtcUsed" type="text" inputmode="numeric"
                                            class="profit-calculator__field-input" />
                                        <div
                                            class="profit-calculator__field-input-btn-icon profit-calculator-exchange-rate-btc-used-reset">
                                            <svg width="24" height="24" viewBox="0 0 24 24">
                                                <path
                                                    d="M2 12C2 16.97 6.03 21 11 21C13.39 21 15.68 20.06 17.4 18.4L15.9 16.9C14.63 18.25 12.86 19 11 19C4.76 19 1.64 11.46 6.05 7.05C10.46 2.64 18 5.77 18 12H15L19 16H19.1L23 12H20C20 7.03 15.97 3 11 3C6.03 3 2 7.03 2 12Z"
                                                    fill="currentColor"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="profit-calculator__field-slider-wrapper profit-calculator__field-slider-wrapper--exchange-rate-btc-used"
                                        style="visibility:hidden;">
                                        <input name="exchangeRateBtcUsedDiffSlider" type="text"
                                            class="profit-calculator__field-slider">
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($profitCalculator['coin'] == 'LTC+DOGE') { ?>
                                <div class="profit-calculator__field profit-calculator__field--exchange-rate-ltc-used">
                                    <div class="profit-calculator__field-label">LTC/USD</div>
                                    <div class="profit-calculator__field-input-wrapper">
                                        <input name="exchangeRateLtcUsed" type="text" inputmode="numeric"
                                            class="profit-calculator__field-input" />
                                        <div
                                            class="profit-calculator__field-input-btn-icon profit-calculator-exchange-rate-ltc-used-reset">
                                            <svg width="24" height="24" viewBox="0 0 24 24">
                                                <path
                                                    d="M2 12C2 16.97 6.03 21 11 21C13.39 21 15.68 20.06 17.4 18.4L15.9 16.9C14.63 18.25 12.86 19 11 19C4.76 19 1.64 11.46 6.05 7.05C10.46 2.64 18 5.77 18 12H15L19 16H19.1L23 12H20C20 7.03 15.97 3 11 3C6.03 3 2 7.03 2 12Z"
                                                    fill="currentColor"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="profit-calculator__field-slider-wrapper profit-calculator__field-slider-wrapper--exchange-rate-ltc-used"
                                        style="visibility:hidden;">
                                        <input name="exchangeRateLtcUsedDiffSlider" type="text"
                                            class="profit-calculator__field-slider">
                                    </div>
                                </div>
                                <div class="profit-calculator__field profit-calculator__field--exchange-rate-doge-used">
                                    <div class="profit-calculator__field-label">DOGE/USD</div>
                                    <div class="profit-calculator__field-input-wrapper">
                                        <input name="exchangeRateDogeUsed" type="text" inputmode="numeric"
                                            class="profit-calculator__field-input" />
                                        <div
                                            class="profit-calculator__field-input-btn-icon profit-calculator-exchange-rate-doge-used-reset">
                                            <svg width="24" height="24" viewBox="0 0 24 24">
                                                <path
                                                    d="M2 12C2 16.97 6.03 21 11 21C13.39 21 15.68 20.06 17.4 18.4L15.9 16.9C14.63 18.25 12.86 19 11 19C4.76 19 1.64 11.46 6.05 7.05C10.46 2.64 18 5.77 18 12H15L19 16H19.1L23 12H20C20 7.03 15.97 3 11 3C6.03 3 2 7.03 2 12Z"
                                                    fill="currentColor"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="profit-calculator__field-slider-wrapper profit-calculator__field-slider-wrapper--exchange-rate-doge-used"
                                        style="visibility:hidden;">
                                        <input name="exchangeRateDogeUsedDiffSlider" type="text"
                                            class="profit-calculator__field-slider">
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="profit-calculator__field profit-calculator__field--exchange-rate-usdt-used">
                                <div class="profit-calculator__field-label">USDT/RUB</div>
                                <div class="profit-calculator__field-input-wrapper">
                                    <input name="exchangeRateUsdtUsed" type="text" inputmode="numeric"
                                        class="profit-calculator__field-input" />
                                    <div
                                        class="profit-calculator__field-input-btn-icon profit-calculator-exchange-rate-usdt-used-reset">
                                        <svg width="24" height="24" viewBox="0 0 24 24">
                                            <path
                                                d="M2 12C2 16.97 6.03 21 11 21C13.39 21 15.68 20.06 17.4 18.4L15.9 16.9C14.63 18.25 12.86 19 11 19C4.76 19 1.64 11.46 6.05 7.05C10.46 2.64 18 5.77 18 12H15L19 16H19.1L23 12H20C20 7.03 15.97 3 11 3C6.03 3 2 7.03 2 12Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="profit-calculator__field-slider-wrapper profit-calculator__field-slider-wrapper--exchange-rate-usdt-used"
                                    style="visibility:hidden;">
                                    <input name="exchangeRateUsdtUsedDiffSlider" type="text"
                                        class="profit-calculator__field-slider">
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary profit-calculator__btn-calculate" type="submit">Рассчитать</button>
                    </form>

                    <p class="profit-calculator__footer-note preloader-block">Актуальный расчет на <span id="pc-date"
                            class="preloader-text">—</span></p>
                    <p class="profit-calculator__footer-note preloader-block">Расчёт носит предварительный характер и
                        зависит от курса валют,<br>сложности сети и других рыночных факторов.</p>
                </div>
            </div>
        </section>
        <link rel="stylesheet" href="/mining-calculator/assets/product/product-calc.css">
        <script src="/mining-calculator/assets/product/rangeslider.min.js"></script>
        <script src="/mining-calculator/assets/product/product-calc.js"></script>
    <?php endif; ?>

</div>

<section class="offerings section-padding container">
    <h2 class="offerings__title section-title">Мы предлагаем</h2>
    <div class="offerings__cards">
        <div class="offerings__card">
            <div class="offerings__card-icon">

                <svg width="49" height="48" viewBox="0 0 56 57" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_3230_59723)">
                        <rect y="0.583984" width="56" height="56" rx="17" fill="#5B61FF"></rect>
                        <path
                            d="M20.5 28.584C24.64 28.584 28 25.224 28 21.084C28 16.944 24.64 13.584 20.5 13.584C16.36 13.584 13 16.944 13 21.084C13 25.224 16.36 28.584 20.5 28.584ZM19.5 18.084C19.5 17.534 19.95 17.084 20.5 17.084C21.05 17.084 21.5 17.534 21.5 18.084V20.674L23.21 22.384C23.6 22.774 23.6 23.404 23.21 23.794C23.01 23.994 22.76 24.084 22.5 24.084C22.24 24.084 21.99 23.984 21.79 23.794L19.79 21.794C19.6 21.604 19.5 21.354 19.5 21.084V18.084Z"
                            fill="white"></path>
                        <path
                            d="M42.71 24.0243C42.47 23.7843 42.11 23.6843 41.78 23.7543L32.38 25.9243C32.03 26.0043 31.74 26.2743 31.64 26.6243C31.54 26.9743 31.64 27.3543 31.89 27.6043L33.35 29.0643L27.29 35.1243L26.36 32.9443C25.97 32.0443 25.17 31.3943 24.21 31.1943C23.25 30.9943 22.26 31.2743 21.54 31.9443L13.31 39.7043C13.12 39.8843 13 40.1343 13 40.4043C13 40.6743 13.09 40.9243 13.27 41.1243L14.7 42.6443C15.29 43.2743 16.09 43.5843 16.88 43.5843C17.62 43.5843 18.36 43.3143 18.94 42.7743L22.5 39.4243L23.49 41.7343C23.88 42.6543 24.7 43.3143 25.68 43.4943C26.66 43.6843 27.66 43.3743 28.37 42.6643L37.66 33.3743L39.12 34.8343C39.38 35.0943 39.75 35.1943 40.1 35.0843C40.45 34.9843 40.72 34.7043 40.8 34.3443L42.97 24.9443C43.05 24.6043 42.95 24.2543 42.7 24.0143L42.71 24.0243Z"
                            fill="white"></path>
                    </g>
                </svg>
            </div>
            <h3 class="offerings__card-title">Up-time от 97% по договору</h3>
        </div>
        <div class="offerings__card">
            <div class="offerings__card-icon">
                <svg width="49" height="48" viewBox="0 0 49 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_5262_67341)">
                        <rect x="0.730469" width="48" height="48" rx="15" fill="#5B61FF" />
                        <g clip-path="url(#clip1_5262_67341)">
                            <path
                                d="M21.0234 19.6277C19.5307 19.6277 18.3164 20.842 18.3164 22.3347C18.3164 23.8278 19.5307 25.0421 21.0234 25.0421C22.5161 25.0421 23.7308 23.8278 23.7308 22.3347C23.7308 20.842 22.5161 19.6277 21.0234 19.6277Z"
                                fill="white" />
                            <path
                                d="M35.8691 13.6499H13.5914C13.1159 13.6499 12.7305 14.0354 12.7305 14.5109V28.3105C12.7305 28.786 13.1159 29.1714 13.5914 29.1714H14L17.8473 25.3242C17.9432 25.2282 17.9527 25.074 17.8656 24.9699C17.24 24.2219 16.8746 23.2493 16.9105 22.1923C16.9842 20.0193 18.7667 18.2641 20.9406 18.2225C23.2765 18.1777 25.1852 20.0906 25.1334 22.4276C25.0851 24.6033 23.3235 26.3801 21.1483 26.4461C20.4959 26.4659 19.8765 26.3328 19.3222 26.0804C19.2222 26.0348 19.1046 26.0554 19.0269 26.1331L15.9881 29.1714H35.8691C36.3443 29.1714 36.7305 28.7853 36.7305 28.3105V14.5113C36.7305 14.0366 36.3443 13.6499 35.8691 13.6499ZM27.6469 25.8556C27.6469 26.2441 27.3325 26.5586 26.944 26.5586C26.5559 26.5586 26.241 26.2441 26.241 25.8556V23.1426C26.241 22.7546 26.5559 22.4396 26.944 22.4396C27.3325 22.4396 27.6469 22.7546 27.6469 23.1426V25.8556ZM29.8496 25.8556C29.8496 26.2441 29.5352 26.5586 29.1467 26.5586C28.7586 26.5586 28.4437 26.2441 28.4437 25.8556V18.456C28.4437 18.068 28.7586 17.753 29.1467 17.753C29.5352 17.753 29.8496 18.068 29.8496 18.456V25.8556ZM32.0889 25.8556C32.0889 26.2441 31.7739 26.5586 31.3859 26.5586C30.9974 26.5586 30.6829 26.2441 30.6829 25.8556V20.3306C30.6829 19.9426 30.9974 19.6276 31.3859 19.6276C31.7739 19.6276 32.0889 19.9426 32.0889 20.3306V25.8556ZM34.3295 25.8556C34.3295 26.2441 34.0146 26.5586 33.6265 26.5586C33.238 26.5586 32.9236 26.2441 32.9236 25.8556V22.2052C32.9236 21.8172 33.238 21.5023 33.6265 21.5023C34.0146 21.5023 34.3295 21.8172 34.3295 22.2052V25.8556Z"
                                fill="white" />
                            <path
                                d="M30.359 32.9995L30.3543 32.9975C29.2613 32.5289 28.1289 32.2023 26.9695 32.019V30.6848C26.9695 30.5396 26.8517 30.4219 26.7065 30.4219H26.2408H25.5635H22.7545C22.6093 30.4219 22.4916 30.5396 22.4916 30.6848V32.0207C21.3385 32.2039 20.2123 32.5287 19.1252 32.9941C18.7767 33.1432 18.5934 33.5414 18.7278 33.8958C18.8706 34.2725 19.2968 34.4507 19.663 34.2932C21.2733 33.6014 22.9786 33.2503 24.7313 33.2496H24.7459C26.4837 33.2496 28.2032 33.6034 29.7999 34.2895L29.8087 34.2933C29.8992 34.3322 29.9932 34.3506 30.0859 34.3506C30.4107 34.3505 30.7169 34.124 30.781 33.76C30.837 33.4411 30.6565 33.1271 30.359 32.9995Z"
                                fill="white" />
                            <path
                                d="M14.0073 29.1719L12.9521 30.2292C12.6774 30.5038 12.6774 30.9486 12.9521 31.2232C13.0894 31.3605 13.2694 31.429 13.4493 31.429C13.6293 31.429 13.8093 31.3605 13.9461 31.2232L15.9953 29.1719H14.0073Z"
                                fill="white" />
                        </g>
                    </g>
                </svg>
            </div>
            <h3 class="offerings__card-title">Личный кабинет мониторинга</h3>
        </div>
        <div class="offerings__card">
            <div class="offerings__card-icon">
                <svg width="49" height="49" viewBox="0 0 49 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_5262_67358)">
                        <rect x="0.730469" y="0.5" width="48" height="48" rx="15" fill="#5B61FF" />
                        <path
                            d="M24.6643 22.3684C24.6643 22.162 24.4956 21.9932 24.2893 21.9932H18.3936C18.1874 21.9932 18.0186 22.162 18.0186 22.3684C18.0186 22.7398 18.0111 23.115 17.9961 23.4939H24.6868C24.6718 23.115 24.6643 22.7398 24.6643 22.3684Z"
                            fill="white" />
                        <path
                            d="M26.5348 32.6748C26.2047 32.0557 25.9519 31.375 25.7697 30.6787C25.2034 28.4951 24.8546 26.3415 24.7234 24.2441H17.9576C17.8264 26.3415 17.4776 28.4951 16.9113 30.6787C16.57 32.0032 16.0112 33.1175 15.2461 33.9992H27.4424C27.0974 33.6053 26.7936 33.1625 26.5348 32.6748ZM22.7056 29.2905L21.6743 31.3541C21.455 31.7908 20.7866 31.4576 21.0067 31.0164C21.0067 31.0164 21.7643 29.4969 21.7643 29.4969H20.3091C20.1779 29.4969 20.0578 29.4294 19.9903 29.3205C19.9228 29.208 19.9153 29.0692 19.9754 28.9528L21.0067 26.8893C21.2229 26.4565 21.8954 26.7832 21.6743 27.227C21.6743 27.227 20.9167 28.7465 20.9167 28.7465H22.3719C22.6451 28.7382 22.8352 29.0533 22.7056 29.2905Z"
                            fill="white" />
                        <path
                            d="M35.2106 35.0164C35.1644 34.8622 35.0205 34.7475 34.8543 34.75H14.6021C14.5132 34.7507 14.4326 34.7772 14.3696 34.8326C14.1573 34.9915 14.1924 35.361 14.4446 35.4629C14.4924 35.4861 14.5469 35.4994 14.6021 35.5004H34.8543C35.0908 35.5076 35.2964 35.2458 35.2106 35.0164Z"
                            fill="white" />
                        <path
                            d="M26.9324 25.1818C26.9324 25.5533 26.9248 25.9285 26.9023 26.3074H32.3067C32.2842 25.9285 32.2767 25.5533 32.2767 25.1818C32.2767 24.9755 32.1079 24.8066 31.9016 24.8066H27.3074C27.0974 24.8066 26.9324 24.9755 26.9324 25.1818Z"
                            fill="white" />
                        <path
                            d="M26.4102 30.1571C26.7267 31.5875 27.399 33.0439 28.4879 33.9991C26.8565 27.058 26.8565 27.058 26.8565 27.058C26.774 28.0748 26.6239 29.1103 26.4102 30.1571Z"
                            fill="white" />
                        <path
                            d="M20.1528 20.7809C20.2203 20.8372 20.3066 20.8672 20.3928 20.8672H22.4593C22.5455 20.8672 22.6318 20.8372 22.6993 20.7809C22.9881 20.5408 23.1981 20.2293 23.3144 19.8767C23.9657 19.9321 24.6018 19.6393 24.9983 19.1488C25.7994 19.8605 27.1573 19.7427 27.8186 18.9011C28.2049 19.1525 28.6549 19.2876 29.12 19.2876C30.4439 19.2876 31.5203 18.2108 31.5203 16.8901C31.5164 14.4035 28.152 13.578 27.001 15.7646C26.2212 15.4517 25.2557 15.7166 24.7433 16.3648C24.387 16.0647 23.9332 15.8958 23.4531 15.8958C22.5868 15.8958 21.833 16.4586 21.5704 17.2616C19.6668 17.1013 18.6771 19.578 20.1528 20.7809Z"
                            fill="white" />
                    </g>
                </svg>
            </div>
            <h3 class="offerings__card-title">Дата-центр по стандартам атомной отрасли</h3>
        </div>
        <div class="offerings__card">
            <div class="offerings__card-icon">
                <svg width="49" height="48" viewBox="0 0 49 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_5262_67369)">
                        <rect x="0.730469" width="48" height="48" rx="15" fill="#5B61FF" />
                        <g clip-path="url(#clip1_5262_67369)">
                            <path
                                d="M28.9961 20.5312C24.7314 20.5312 21.2617 24.0009 21.2617 28.2656C21.2617 32.5304 24.7314 36 28.9961 36C33.2608 36 36.7305 32.5304 36.7305 28.2656C36.7305 24.0009 33.2608 20.5312 28.9961 20.5312ZM28.9961 27.5625C30.1593 27.5625 31.1055 28.5087 31.1055 29.6719C31.1055 30.5873 30.5159 31.3605 29.6992 31.6517V33.1875H28.293V31.6517C27.4763 31.3605 26.8867 30.5873 26.8867 29.6719H28.293C28.293 30.0598 28.6082 30.375 28.9961 30.375C29.384 30.375 29.6992 30.0598 29.6992 29.6719C29.6992 29.2839 29.384 28.9688 28.9961 28.9688C27.8329 28.9688 26.8867 28.0225 26.8867 26.8594C26.8867 25.9439 27.4763 25.1708 28.293 24.8795V23.3438H29.6992V24.8795C30.5159 25.1708 31.1055 25.9439 31.1055 26.8594H29.6992C29.6992 26.4714 29.384 26.1562 28.9961 26.1562C28.6082 26.1562 28.293 26.4714 28.293 26.8594C28.293 27.2473 28.6082 27.5625 28.9961 27.5625Z"
                                fill="white" />
                            <path
                                d="M20.5586 20.5312C24.8955 20.5312 28.293 18.678 28.293 16.3125C28.293 13.947 24.8955 12 20.5586 12C16.2217 12 12.7305 13.947 12.7305 16.3125C12.7305 18.678 16.2217 20.5312 20.5586 20.5312Z"
                                fill="white" />
                            <path
                                d="M12.7305 27.8231V28.9688C12.7305 31.3343 16.2217 33.1875 20.5586 33.1875C20.8046 33.1875 21.043 33.1649 21.2852 33.153C20.7458 32.305 20.3436 31.3641 20.1067 30.3581C16.9705 30.2715 14.2474 29.3078 12.7305 27.8231Z"
                                fill="white" />
                            <path
                                d="M19.8893 28.933C19.8731 28.7118 19.8555 28.4909 19.8555 28.2657C19.8555 27.5327 19.9515 26.8233 20.1153 26.1397C16.9752 26.0547 14.2488 25.0904 12.7305 23.6044V24.75C12.7305 26.9901 15.8834 28.7504 19.8893 28.933Z"
                                fill="white" />
                            <path
                                d="M20.5586 24.75C20.5593 24.75 20.56 24.7499 20.5608 24.7499C21.0245 23.6416 21.6986 22.6425 22.5357 21.8054C21.9007 21.8857 21.2445 21.9375 20.5586 21.9375C17.2203 21.9375 14.3207 20.9421 12.7305 19.3856V20.5313C12.7305 22.8968 16.2217 24.75 20.5586 24.75Z"
                                fill="white" />
                        </g>
                    </g>
                </svg>
            </div>
            <h3 class="offerings__card-title">Персональную финансовую модель</h3>
        </div>
    </div>
</section>

<!-- Описание товара -->
<section class="catalog-about section-padding container">
    <h2 class="visually-hidden">Описание товара</h2>
    <div class="about__btn-block">
        <button class="btn about__button is-active js-about-tab" data-tab="overview">Обзор</button>
        <button class="btn about__button js-about-tab" data-tab="specifications">Характеристики</button>
        <button class="btn about__button js-about-tab" data-tab="reviews">Отзывы</button>
        <button class="btn about__button js-about-tab" data-tab="guarantee">Гарантия</button>
        <button class="btn about__button js-about-tab" data-tab="payment">Оплата и доставка</button>
    </div>
    <hr class="about__separator">
    <div class="about__content">
        <h2 class="about__title">Обзор</h2>
        <div class="about__tab-content js-tab-content is-active" data-tab="overview">
            <?= $product['PREVIEW_TEXT'] ?: '<p>Описание для этого товара еще не добавлено.</p>' ?>
        </div>
        <div class="about__tab-content js-tab-content tab-content-specifications" data-tab="specifications">
            <div class="about__content-specifications">
                <?
                // Массив кодов свойств, которые нужно исключить
                $excludeProperties = ['IN_STOCK', "SORT_BY_PRICE", "FEATURED", "PRIORITET", "MORE_PHOTO", "IN_CALCULATOR"]; // Добавьте сюда нужные коды
                
                $hasProperties = false;
                if (!empty($product['PROPERTIES'])):
                    echo '<div class="product-properties">';
                    foreach ($product['PROPERTIES'] as $propCode => $property):
                        // Пропускаем указанные свойства и пустые значения
                        if (in_array($propCode, $excludeProperties) || empty($property['VALUE'])) {
                            continue;
                        }

                        $value = is_array($property['VALUE']) ? implode(', ', $property['VALUE']) : $property['VALUE'];
                        if (!empty($value)):
                            $hasProperties = true;
                            $name = !empty($property['NAME']) ? $property['NAME'] : $propCode;
                            echo '<div class="property-item">';
                            echo '<span class="property-name">' . htmlspecialchars($name) . '</span>';
                            echo '<span class="property-separator"></span>';
                            echo '<span class="property-value">' . htmlspecialchars($value) . '</span>';
                            echo '</div>';
                        endif;
                    endforeach;
                    echo '</div>';

                    if (!$hasProperties):
                        echo '<p>Характеристики для этого товара еще не добавлены.</p>';
                    endif;
                else:
                    echo '<p>Характеристики для этого товара еще не добавлены.</p>';
                endif;
                ?>
            </div>
        </div>
        <div class="about__tab-content js-tab-content tab-content-reviews" data-tab="reviews">
            <div class="reviews-section">
                <div class="reviews-container">

                    <?php
                    // динамическая область отзывов
                    $frame = new \Bitrix\Main\Page\FrameHelper("reviews_block_" . $arResult['ID']);
                    $frame->begin();
                    ?>

                    <? $APPLICATION->IncludeFile(
                        "/local/include/reviews.php",
                        ["PRODUCT_ID" => $arResult["ID"]],
                        ["MODE" => "php", "SHOW_BORDER" => false]
                    ); ?>

                    <?php
                    $frame->end();
                    ?>


                </div>
            </div>
        </div>


        <div class="about__tab-content js-tab-content tab-content-guarantee" data-tab="guarantee">
            <div class="about__content-guarantee">
                <div class="content-section">
                    <h3 class="content-section__title">Официальная гарантия</h3>
                    <p class="content-section__text">
                        Мы предоставляем официальную гарантию на всё майнинговое оборудование сроком от 6 до
                        12 месяцев в
                        зависимости от производителя.
                    </p>
                    <ul class="content-section__features-list">
                        <li class="content-section__features-list-item">Гарантия бесплатная — оплачивается
                            только
                            доставка
                            оборудования в сервисный
                            центр и обратно.
                        </li>
                        <li class="content-section__features-list-item">Срок гарантии отсчитывается от даты
                            производства
                            устройства, а не от момента
                            покупки.
                        </li>
                        <li class="content-section__features-list-item">
                            Проверить актуальный срок гарантии можно самостоятельно на сайте производителя:
                            <ul class="content-section__vendor-list">
                                <li class="content-section__vendor-list-item">Bitmain: <a
                                        href="https://service.bitmain.com/support/warranty"
                                        class="content-section__link">https://service.bitmain.com/support/warranty</a>
                                </li>
                                <li class="content-section__vendor-list-item">Whatsminer: <a
                                        href="https://www.whatsminer.com/src/views/support.html"
                                        class="content-section__link">https://www.whatsminer.com/src/views/support.html</a>
                                </li>
                                <li class="content-section__vendor-list-item">ELPHAPEX: <a
                                        href="https://elphapex.com/warranty"
                                        class="content-section__link">https://elphapex.com/warranty</a></li>
                            </ul>
                        </li>
                    </ul>
                    <p class="content-section__text">
                        <span class="bold">Мы берем на себя всю организацию гарантийного ремонта:</span> от
                        выявления момента
                        поломки
                        устройства, до
                        коммуникации и создания заявки в гарантийный СЦ и дальнейшего ведения со статусами.
                        В случае отказа
                        производителя в бесплатном ремонте вы заранее будете знать все причины и сможете
                        принять решение о
                        дальнейшем ремонте.
                    </p>
                </div>
                <div class="content-section">
                    <h3 class="content-section__title">Партнерский сервис — альтернатива без лишних
                        ожиданий</h3>
                    <p class="content-section__text">
                        Официальное гарантийное обслуживание может занимать <span class="bold">несколько
                            месяцев.</span> Всё
                        это время
                        оборудование
                        простаивает и <span class="bold">не приносит прибыль.</span> Чтобы минимизировать риски,
                        мы
                        предлагаем быстрый
                        ремонт через наш
                        партнерский сервис.
                    </p>
                    <ul class="content-section__features-list">
                        <li class="content-section__features-list-item"><span class="bold">Сроки ремонта в разы
                                короче,
                                чем в
                                официальных
                                сервисах.</span></li>
                        <li class="content-section__features-list-item">Вся коммуникация и логистика — на нас.
                        </li>
                        <li class="content-section__features-list-item">Вы продолжаете зарабатывать на
                            оборудовании без
                            долгих простоев.
                        </li>
                    </ul>
                    <p class="content-section__text">
                        Так же партнерский сервис дает гарантию 2 недели, уже после того, как оборудование
                        вернется в работу.
                    </p>
                    <p class="content-section__text">
                        Этот вариант выбирают клиенты, для которых время работы оборудования важнее, чем
                        ожидание бесплатного
                        ремонта.
                    </p>
                </div>
                <div class="content-section">
                    <h3 class="content-section__title">FAQ</h3>
                    <div class="faq-list">
                        <details class="faq-item" open>
                            <summary class="faq-item__question">Что происходит, если оборудование выходит из строя на
                                вашем хостинге?
                            </summary>
                            <div class="faq-item__answer">
                                <p>На объекте ежедневно дежурит бригада инженеров в режиме 24/7, средняя
                                    скорость реагирования на поломку до 7 минут.
                                    Мелкие поломки (замена кулера, блока питания, термопасты) мы устраняем на
                                    месте, без отправки в сервис по согласованию с
                                    клиентом. Благодаря этому время простоя оборудования сокращается в разы.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary class="faq-item__question">Есть ли расширенная гарантия?</summary>
                            <div class="faq-item__answer">
                                <p>Официальная гарантия ограничена условиями производителя. Дополнительно можно
                                    воспользоваться нашим партнерским сервисом
                                    — это платная услуга, но она позволяет быстро вернуть оборудование в строй и
                                    избежать убытков от простоя.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary class="faq-item__question">Проверяете ли вы оборудование перед отправкой клиенту
                                после ремонта?
                            </summary>
                            <div class="faq-item__answer">
                                <p>Да, мы проводим проверку после ремонта оборудования. Она возможна как через
                                    официальный сервис, так и через наш
                                    партнерский. Клиент получает устройство, прошедшее диагностику и готовое к
                                    работе.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary class="faq-item__question">Если поломка серьезная, куда отправляется оборудование?
                            </summary>
                            <div class="faq-item__answer">
                                <p>В таком случае оно отправляется в сервисный центр производителя. Логистику
                                    оплачивает клиент.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary class="faq-item__question">Что делать, если ремонт занимает слишком много времени?
                            </summary>
                            <div class="faq-item__answer">
                                <p>Если гарантийный ремонт затягивается из – за большой загрузки, у клиента
                                    всегда есть альтернатива в пользу партнерского
                                    СЦ. Это избавит вас от лишних хлопот и сократит простой.</p>
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary class="faq-item__question">Возможен ли возврат оборудования?</summary>
                            <div class="faq-item__answer">
                                <p>Если оборудование находится на гарантии, обмен или возврат осуществляется
                                    через производителя.
                                    В случае, если вы хотите продать уже купленное оборудование (в том числе
                                    неисправное), мы можем оказать полную
                                    поддержку.</p>
                            </div>
                        </details>
                    </div>
                </div>
                <div class="content-section">
                    <h3 class="content-section__title">Таким образом, вы получаете две гарантии надежности:</h3>
                    <ol class="content-section__items">
                        <li class="content-section__item"><span class="bold">1. Официальную гарантию от
                                производителя.</span>
                        </li>
                        <li class="content-section__item"><span class="bold">2. Поддержку нашей компании и
                                партнерского сервиса,
                                если
                                важно быстро вернуть
                                оборудование в строй.</span></li>
                    </ol>
                    <p class="content-section__text">
                        Мы понимаем, что для майнеров каждый день простоя — это прямые убытки. Именно
                        поэтому мы предлагаем гибкие
                        варианты, чтобы ваши инвестиции были максимально защищены.
                    </p>
                </div>
            </div>
        </div>
        <div class="about__tab-content js-tab-content tab-content-payment" data-tab="payment">
            <div class="payment-methods__grid">

                <!-- Карточка 1: Безналичный расчет -->
                <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для физических лиц</span>
                        <span class="payment-card__tag">для юридических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Безналичный расчет</h3>
                        <div class="payment-card__content-group">
                            <h4 class="payment-card__subtitle">Кредитная или дебетовая карта</h4>
                            <p class="payment-card__description">Картами Visa, MasterCard, Мир. Без комиссии</p>
                            <p class="payment-card__description">Мы подготавливаем договор и согласовываем основные
                                условия</p>
                        </div>
                        <div class="payment-card__content-group">
                            <h4 class="payment-card__subtitle">Банковский перевод</h4>
                            <p class="payment-card__description">Вы осуществляете перевод денежных средств на
                                расчетный
                                счет нашей организации</p>
                        </div>
                    </div>
                </article>

                <!-- Карточка 2: Наличный расчет -->
                <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для физических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Наличный расчет</h3>
                        <p class="payment-card__description">Вы можете оплатить заказ в центральном офисе компании
                            по
                            адресу: Москва, Варшавское шоссе, 1, стр. 1-2 W-Plaza</p>
                        <div class="payment-card__map-links links-map">
                            <a href="https://yandex.by/maps/213/moscow/house/varshavskoye_shosse_1s1_2/Z04YcAVlQEUOQFtvfXtxcnVqZg==/?ll=37.625909%2C55.703992&z=17"
                                class="payment-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="payment-card__map-icon"
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo1.svg"
                                    alt="Иконка Yandex карты" loading="lazy" width=22 height=32>
                                Яндекс.Карты
                            </a>
                            <a href="https://maps.app.goo.gl/nNaHLjx3ZrHkxrh87" class="payment-card__map-link"
                                target="_blank" rel="noopener noreferrer nofollow">
                                <img class="payment-card__map-icon"
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo2.svg"
                                    alt="Иконка Google карты" loading="lazy" width=22 height="32">
                                Google Карты
                            </a>
                            <a href="https://maps.apple.com/?address=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5,%20%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0,%20%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F,%20117216&ll=55.561882,37.593305&q=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5&_ext=EiYpfQh0k1jHS0AxMO0WJu3KQkA5+92Z737IS0BBCMImqvXMQkBQBA%3D%3D&t=m"
                                class="payment-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="payment-card__map-icon"
                                    src="<?= SITE_TEMPLATE_PATH ?>/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo3.svg"
                                    alt="Иконка Apple карты" loading="lazy" width="32" height="32">
                                Apple Карты
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Карточка 3: Лизинг -->
                <!-- <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для ИП</span>
                        <span class="payment-card__tag">для юридических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Лизинг</h3>
                        <p class="payment-card__description">Лизинг позволяет получить современное оборудование уже сегодня, оплачивая его стоимость постепенно, удобными ежемесячными платежами.
                        <ul class="payment-card__features-list">
                            <li class="payment-card__feature-item"><b>20%</b> возмещение НДС</li>
                            <li class="payment-card__feature-item"><b>20%</b> оптимизация расходов на размещение и
                                электроэнергию</li>
                            <li class="payment-card__feature-item"><b>20%</b> экономия по налогу на прибыль</li>
                        </ul>
                    </div>
                    <footer class="payment-card__footer">
                        <a href="#" class="payment-card__more-link">
                            Подробнее
                            <svg class="cta-banner-section__icon" width="36" height="36" viewBox="0 0 36 36"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_7003_2908)">
                                    <circle cx="18" cy="18" r="17.625" stroke="#131315" stroke-width="0.75">
                                    </circle>
                                    <path
                                        d="M18.7496 13.875H22.125M22.125 13.875L13.875 22.1235M22.125 13.875L22.1246 17.25"
                                        stroke="#131315" stroke-width="0.75" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_7003_2908">
                                        <rect width="36" height="36" fill="white"></rect>
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    </footer>
                </article> -->

                <!-- Карточка 4: Рассрочка -->
                <!-- <article class="payment-methods__card payment-card">
                    <div class="payment-card__header">
                        <span class="payment-card__tag">для физических лиц</span>
                    </div>
                    <div class="payment-card__body">
                        <h3 class="payment-card__title">Рассрочка</h3>
                        <p class="payment-card__description">Оплата заказа частями</p>
                    </div>
                    <footer class="payment-card__footer">
                        <a href="#" class="payment-card__more-link">
                            Подробнее
                            <svg class="cta-banner-section__icon" width="36" height="36" viewBox="0 0 36 36"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_7003_2908)">
                                    <circle cx="18" cy="18" r="17.625" stroke="#131315" stroke-width="0.75">
                                    </circle>
                                    <path
                                        d="M18.7496 13.875H22.125M22.125 13.875L13.875 22.1235M22.125 13.875L22.1246 17.25"
                                        stroke="#131315" stroke-width="0.75" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </g>
                                <defs>
                                    <clipPath id="clip0_7003_2908">
                                        <rect width="36" height="36" fill="white"></rect>
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    </footer>
                </article> -->

            </div>
            <div class="delivery-methods__grid">

                <!-- Карточка 1: Самовывоз -->
                <article class="delivery-methods__card delivery-card">

                    <div class="delivery-card__body">
                        <h3 class="delivery-card__title">Самовывоз</h3>
                        <p class="delivery-card__description">Чтобы получить заказ от GIS Mining самостоятельно, вы
                            можете выбрать вариант самовывоза. После того, как наш менеджер
                            свяжется в вами и подтвердит наличие товара, вы сможете приехать и забрать заказ.</p>
                        <p class="delivery-card__description">Центральный офис компании GIS Mining находится в
                            Москве, по адресу: Варшавское шоссе, 1, стр. 1-2 W-Plaza</p>
                        <div class="delivery-card__map-links links-map">
                            <a href="https://yandex.by/maps/213/moscow/house/varshavskoye_shosse_1s1_2/Z04YcAVlQEUOQFtvfXtxcnVqZg==/?ll=37.625909%2C55.703992&amp;z=17"
                                class="delivery-card__map-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img class="delivery-card__map-icon"
                                    src="/local/templates/main/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo1.svg"
                                    alt="Иконка Yandex карты" loading="lazy" width="22" height="32">
                                Яндекс.Карты
                            </a>
                            <a href="https://maps.app.goo.gl/nNaHLjx3ZrHkxrh87" class="delivery-card__map-link"
                                target="_blank" rel="noopener noreferrer nofollow">
                                <img class="delivery-card__map-icon"
                                    src="/local/templates/main/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo2.svg"
                                    alt="Иконка Google карты" loading="lazy" width="22" height="32">
                                Google Карты
                            </a>
                            <a href="https://maps.apple.com/?address=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5,%20%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0,%20%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F,%20117216&amp;ll=55.561882,37.593305&amp;q=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5&amp;_ext=EiYpfQh0k1jHS0AxMO0WJu3KQkA5+92Z737IS0BBCMImqvXMQkBQBA%3D%3D&amp;t=m"
                                target="_blank" rel="noopener noreferrer nofollow" class="delivery-card__map-link">
                                <img class="delivery-card__map-icon"
                                    src="/local/templates/main/assets/img/oplata-i-dostavka/oplata-i-dostavka_payment-method_logo3.svg"
                                    alt="Иконка Apple карты" loading="lazy" width="32" height="32">
                                Apple Карты
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Карточка 2: Транспортные компании -->
                <article class="delivery-methods__card delivery-card">
                    <div class="delivery-card__body">
                        <h3 class="delivery-card__title">Транспортные компании</h3>
                        <p class="delivery-card__description">Организуем доставку в любой регион РФ и страны СНГ
                            через транспортные компании (СДЭК, DHL, Деловые линии и др.)</p>
                        <div class="delivery-card__carriers">
                            <div class="delivery-card__carrier-logo-wrap">
                                <img class="delivery-card__carrier-logo"
                                    src="/local/templates/main/assets/img/oplata-i-dostavka/oplata-i-dostavka_delivery-method_logo1.webp"
                                    alt="Логотип CDEK" loading="lazy" width="142" height="32">
                            </div>
                            <div class="delivery-card__carrier-logo-wrap">
                                <img class="delivery-card__carrier-logo"
                                    src="/local/templates/main/assets/img/oplata-i-dostavka/oplata-i-dostavka_delivery-method_logo2.webp"
                                    alt="Логотип DHL" loading="lazy" width="55" height="32">
                            </div>
                            <div class="delivery-card__carrier-logo-wrap">
                                <img class="delivery-card__carrier-logo"
                                    src="/local/templates/main/assets/img/oplata-i-dostavka/oplata-i-dostavka_delivery-method_logo3.webp"
                                    alt="Логотип Деловые линии" loading="lazy" width="210" height="32">
                            </div>

                        </div>
                        <div class="delivery-card__content-group">
                            <h4 class="delivery-card__subtitle">Сроки доставки</h4>
                            <p class="delivery-card__description">Стандартная доставка от заводов-производителей в
                                РФ занимает до 21 дня (точный срок зависит от объема партии). Далее мы
                                доставляем оборудование до заказчика или отправляем асики на наш склад, откуда вы
                                можете забрать аппаратуру
                                самостоятельно.</p>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </div>
</section>




<!-- <section class="viewed section-padding container">
    <h2 class="viewed__title section-title">Вы смотрели</h2>

    <?php
    /*
    echo "<pre>";
print_r($viewedProductIds);
echo "</pre>";

    global $arrFilterViewed;
    $arrFilterViewed = [
        "ID" => $viewedProductIds,
        "!ID" => $arResult["ID"], // исключаем текущий товар
    ];

    if (!empty($viewedProductIds)) {

        $APPLICATION->IncludeComponent(
            "bitrix:catalog.section",
            "catalog_section_slider",
            array(
                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],

                // Наш фильтр
                "FILTER_NAME" => "arrFilterViewed",

                // КОЛ-ВО
                "PAGE_ELEMENT_COUNT" => 4,

                // Кеш
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "3600",
                "CACHE_GROUPS" => "Y",

                // Убираем влияние раздела
                "SHOW_ALL_WO_SECTION" => "Y",
                "INCLUDE_SUBSECTIONS" => "N",
                "BY_LINK" => "N",

                // Сортировка, чтобы ID шли по порядку просмотра
                "ELEMENT_SORT_FIELD" => "ID",
                "ELEMENT_SORT_ORDER" => "DESC",
                "ELEMENT_SORT_FIELD2" => "ID",
                "ELEMENT_SORT_ORDER2" => "DESC",

                "HIDE_NOT_AVAILABLE" => "Y",

                "PRICE_CODE" => $arParams["PRICE_CODE"],

                // Отключаем SEO
                "SET_TITLE" => "N",
                "SET_BROWSER_TITLE" => "N",
                "SET_META_DESCRIPTION" => "N",
                "SET_META_KEYWORDS" => "N",
                "ADD_SECTIONS_CHAIN" => "N",

                // Сравнение
                "DISPLAY_COMPARE" => "N",

                // Передаём url
                "DETAIL_URL" => $arParams["DETAIL_URL"],
            ),
            $component
        );

    } else {
        echo "<p>Вы пока не смотрели товары.</p>";
    }
        */
    ?>
</section> -->




<?php
// JSON-LD Product schema теперь генерируется в component_epilog.php через универсальную систему
?>



<!-- Полноэкранный слайдер галереи -->
<div class="fullscreen-gallery" id="fullscreen-gallery">
    <div class="fullscreen-gallery__overlay"></div>
    <div class="fullscreen-gallery__container">
        <div class="fullscreen-gallery__content">
            <div class="fullscreen-gallery__image-container">
                <img class="fullscreen-gallery__image"
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt=""
                    style="display: none;" />
                <button class="fullscreen-gallery__close" type="button" aria-label="Закрыть галерею">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>

                <button class="fullscreen-gallery__nav fullscreen-gallery__nav--prev" type="button"
                    aria-label="Предыдущее изображение">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>

                <button class="fullscreen-gallery__nav fullscreen-gallery__nav--next" type="button"
                    aria-label="Следующее изображение">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="fullscreen-gallery__counter">
                <span class="fullscreen-gallery__current">1</span> / <span class="fullscreen-gallery__total">1</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Данные галереи для полноэкранного просмотра
    window.productGalleryData = <?= json_encode($allImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Попап форма "Нашли дешевле?" -->
<div class="form-popup popup-form-wrapper" id="cheaperPopupFormWrapper" style="display: none;">
    <div class="form-popup__items">
        <button type="button" class="form-popup__close-btn popup-form__close-btn menu-close"
            id="closeCheaperPopupFormBtn" aria-label="Закрыть">
            <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.9844 10L10.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10.9844 10L22.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <form class="form-popup__popup-form js-ajax-form" id="cheaperForm" data-metric-goal="cheaper-form-submit">
            <h2 class="form-popup__title">Заполните форму, и мы предложим вам более выгодные условия</h2>

            <input type="text" name="client_name" class="form-input" placeholder="Имя*" required>
            <input type="tel" name="client_phone" class="form-input js-phone-mask" placeholder="Телефон*" required>
            <input type="text" name="cheaper_link" class="form-input" placeholder="Ссылка на более дешевый товар*"
                required>

            <div class="form-group form-check">
                <input type="checkbox" id="cheaper-privacy-policy" name="privacy-policy" class="form-check-input"
                    required>
                <label for="privacy-policy-popup" class="form-check-label">Согласен(а) с <a
                        href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a> и с <a
                        href="/soglasie-s-obrabotkoy/" target="_blank"><u>обработкой персональных данных</u></a></label>
            </div>

            <!-- Скрытые поля -->
            <input type="hidden" name="form_name" value="Нашли дешевле - <?= htmlspecialchars($product['NAME']) ?>">
            <input type="hidden" name="utm_source">
            <input type="hidden" name="utm_medium">
            <input type="hidden" name="utm_campaign">
            <input type="hidden" name="utm_term">
            <input type="hidden" name="utm_content">
            <input type="hidden" name="yc_id">
            <input type="hidden" name="page_url" value="">

            <button type="submit" class="btn btn-primary form-popup__submit-btn">Отправить заявку</button>

            <div class="form-error-message" style="display: none;"></div>
        </form>
    </div>
</div>

<!-- Модальное окно успеха для формы "Нашли дешевле?" -->
<div class="modal-overlay" id="cheaperSuccessModalOverlay" style="display: none;">
    <div class="modal" id="cheaperSuccessModal">
        <button class="modal__close-btn" id="closeCheaperSuccessModalBtn" aria-label="Закрыть">
            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.875 22.2988L22.875 2.71741" stroke="#131315" stroke-linecap="round" />
                <path d="M2.875 2.29883L22.875 21.8802" stroke="#131315" stroke-linecap="round" />
            </svg>
        </button>
        <div class="modal__icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">
                <path fill="#10B981"
                    d="M25,2C12.318,2,2,12.318,2,25s10.318,23,23,23s23-10.318,23-23S37.682,2,25,2z M37.12,20.432 l-13.068,13.068c-0.196,0.196-0.454,0.294-0.712,0.294s-0.516-0.098-0.712-0.294l-6.918-6.918c-0.392-0.392-0.392-1.028,0-1.42s1.028-0.392,1.42,0l6.206,6.206l12.356-12.356c0.392-0.392,1.028-0.392,1.42,0S37.512,20.04,37.12,20.432z" />
            </svg>
        </div>
        <h3 class="modal__title">Заявка принята!</h3>
        <p class="modal__text">Мы рассмотрим ваше предложение и свяжемся с вами в ближайшее время.</p>
    </div>
</div>