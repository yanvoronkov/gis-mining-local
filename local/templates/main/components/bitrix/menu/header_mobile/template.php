<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
if (empty($arResult)) {
    return;
}

// =================================================================================
// ШАГ 1: ПОДГОТОВКА ДАННЫХ ДЛЯ МЕГАМЕНЮ (так же, как в десктопе)
// Мы заранее подгружаем пункты из нашего второго меню '.megamenu.menu.php'
// =================================================================================

$megamenuSubItems = []; // Переменная для хранения подпунктов

// Используем класс CMenu для загрузки пунктов из файла .megamenu.menu.php
$obMenu = new CMenu('megamenu');
if ($obMenu->Init(SITE_DIR, true)) {
    $megamenuSubItems = $obMenu->arMenu;
}
?>

<nav class="mobile-menu-panel__nav" aria-label="Мобильная навигация">
    <ul>

        <?php
        // =================================================================================
        // ШАГ 2: ОТРИСОВКА ОСНОВНОГО МЕНЮ
        // Проходим по основному меню (из .top.menu.php) и рисуем его.
        // =================================================================================
        foreach ($arResult as $arItem):
            ?>
            <?php
            // Ищем наш флаг-триггер, который мы задали в /.top.menu.php
            $isMegamenuParent = isset($arItem['PARAMS']['IS_MEGAMENU']) && $arItem['PARAMS']['IS_MEGAMENU'] === 'Y';
            ?>

            <?php if ($isMegamenuParent): ?>
            <?php // --- РИСУЕМ ПУНКТ, КОТОРЫЙ АКТИВИРУЕТ ВЫПАДАЮЩЕЕ ПОДМЕНЮ --- ?>
            <li>
                <button type="button" aria-expanded="false" aria-controls="mobile-submenu-services">
                    <?= htmlspecialchars($arItem["TEXT"]) ?>
                    <svg class="mobile-menu-panel__arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"
                         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round" />
                    </svg>
                </button>
                <ul id="mobile-submenu-services" hidden>
                    <?php // Теперь рисуем все дочерние пункты из нашего заранее загруженного массива ?>
                    <?php if (!empty($megamenuSubItems)): ?>
                        <?php foreach ($megamenuSubItems as $subItem): ?>
                            <li>
                                <a href="<?= htmlspecialchars($subItem[1]) // Ссылка у CMenu в [1] ?>">
                                    <?= htmlspecialchars($subItem[0]) // Текст у CMenu в [0] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </li>
        <?php else: ?>
            <?php // --- РИСУЕМ ОБЫЧНЫЙ ПУНКТ МЕНЮ (1-го уровня) --- ?>
            <li><a href="<?= htmlspecialchars($arItem["LINK"]) ?>"><?= htmlspecialchars($arItem["TEXT"]) ?></a></li>
        <?php endif; ?>

        <?php endforeach; ?>

    </ul>
</nav>