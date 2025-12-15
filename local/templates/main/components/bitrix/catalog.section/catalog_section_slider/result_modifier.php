<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $sortedIds;

if (!empty($sortedIds) && !empty($arResult['ITEMS'])) {
    $positions = array_flip($sortedIds);

    usort($arResult['ITEMS'], function($a, $b) use ($positions) {
        $posA = $positions[$a['ID']] ?? PHP_INT_MAX;
        $posB = $positions[$b['ID']] ?? PHP_INT_MAX;
        return $posA <=> $posB;
    });
}
