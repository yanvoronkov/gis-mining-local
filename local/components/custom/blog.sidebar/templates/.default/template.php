<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
/** @global CMain $APPLICATION */
?>
<aside class="page-blog__filter">
    <div class="page-blog__container">
        <div class="page-blog__filter-buttons">
            <a href="/smi-o-nas/"
                class="page-blog__filter-btn <?= strpos($APPLICATION->GetCurPage(), '/smi-o-nas/') === 0 ? 'active' : '' ?>">
                СМИ о нас
            </a>
            <a href="/our-blog/"
                class="page-blog__filter-btn <?= strpos($APPLICATION->GetCurPage(), '/our-blog/') === 0 ? 'active' : '' ?>">
                Блог
            </a>
            <a href="/news/"
                class="page-blog__filter-btn <?= strpos($APPLICATION->GetCurPage(), '/news/') === 0 ? 'active' : '' ?>">
                Новости
            </a>
            <a href="/cases/"
                class="page-blog__filter-btn <?= strpos($APPLICATION->GetCurPage(), '/cases/') === 0 ? 'active' : '' ?>">
                Кейсы
            </a>
        </div>
    </div>
</aside>