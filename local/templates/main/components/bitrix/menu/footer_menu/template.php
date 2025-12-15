<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?// Проверяем, что в массиве меню есть хотя бы один пункт?>
<?if (!empty($arResult)):?>
    <nav class="footer__navigation" aria-label="Дополнительная навигация сайта">
        <ul class="footer__nav-list">

        <?
        // В цикле проходим по каждому пункту меню из файла .bottom.menu.php
        foreach($arResult as $arItem):
            // Для футера нам не нужен специальный класс для активного пункта,
            // поэтому просто выводим все пункты одинаково.
        ?>
            <li class="footer__nav-item"><a href="<?=$arItem["LINK"]?>" class="footer__nav-link"><?=$arItem["TEXT"]?></a></li>
        <?endforeach?>

        </ul>
    </nav>
<?endif?>