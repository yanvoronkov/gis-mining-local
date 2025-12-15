<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
if (empty($arResult)) {
    return;
}

// =================================================================================
// ШАГ 1: ПОДГОТОВКА ДАННЫХ ДЛЯ МЕГАМЕНЮ
// Мы заранее подгружаем пункты из нашего второго меню '.megamenu.menu.php'
// =================================================================================
$megamenuSubItems = []; // Переменная для хранения подпунктов

// CMenu - это класс Битрикса для работы с файлами меню.
$obMenu = new CMenu('megamenu'); // Указываем тип меню, который хотим загрузить
if ($obMenu->Init(SITE_DIR, true)) { // Инициализируем его для текущего раздела
    $megamenuSubItems = $obMenu->arMenu;
}
?>

<nav class="header__desktop-menu desktop-menu" aria-label="Основная навигация">
    <ul class="desktop-menu__list">

        <?php
        // =================================================================================
        // ШАГ 2: ОТРИСОВКА ОСНОВНОГО МЕНЮ
        // Проходим по основному меню (из .top.menu.php) и рисуем его.
        // =================================================================================
        foreach ($arResult as $arItem):
            ?>
            <?php
            // Ищем наш флаг-триггер
            $isMegamenuParent = isset($arItem['PARAMS']['IS_MEGAMENU']) && $arItem['PARAMS']['IS_MEGAMENU'] === 'Y';
            ?>

            <?php if ($isMegamenuParent): ?>
            <?php // --- РИСУЕМ ПУНКТ, КОТОРЫЙ АКТИВИРУЕТ МЕГАМЕНЮ --- ?>
            <li class="desktop-menu__item desktop-menu__item--has-megamenu">
                <button type="button" class="desktop-menu__link desktop-menu__link--button btn" aria-expanded="false" aria-controls="megamenu-services">
                    <?= htmlspecialchars($arItem["TEXT"]) ?>
                    <svg class="desktop-menu__arrow" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div class="megamenu container" id="megamenu-services" role="region" aria-label="Дополнительные услуги">
                    <div class="megamenu__header">
                        <div class="megamenu__title"><?= htmlspecialchars($arItem["TEXT"]) ?></div>
                        <button type="button" class="megamenu__close-button btn" aria-label="Закрыть меню <?= htmlspecialchars($arItem["TEXT"]) ?>">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="megamenu__grid">
                        <?php // Теперь рисуем все дочерние пункты из нашего заранее загруженного массива ?>
                        <?php if (!empty($megamenuSubItems)): ?>
                            <?php foreach ($megamenuSubItems as $subItem): ?>
                                <a href="<?= htmlspecialchars($subItem[1]) // Ссылка у CMenu в [1] ?>" class="megamenu__card">
                                    <div class="megamenu__card-title"><?= htmlspecialchars($subItem[0]) // Текст у CMenu в [0] ?></div>
                                    <span class="megamenu__card-arrow" aria-hidden="true">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 19L19 12L12 5M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
        <?php else: ?>
            <?php // --- РИСУЕМ ОБЫЧНЫЙ ПУНКТ МЕНЮ --- ?>
            <li class="desktop-menu__item">
                <a href="<?= htmlspecialchars($arItem["LINK"]) ?>" class="desktop-menu__link"><?= htmlspecialchars($arItem["TEXT"]) ?></a>
            </li>
        <?php endif; ?>

        <?php endforeach; ?>

    </ul>
</nav>