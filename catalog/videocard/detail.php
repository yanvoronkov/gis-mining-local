<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("main_class", "product-page");
?>

    <div class="catalog-page catalog-new">
        <?php
        $APPLICATION->IncludeComponent(
            "bitrix:catalog.element",
            "catalog_element",
            [
                "IBLOCK_TYPE" => "catalog",
                "IBLOCK_ID" => 11,
                "ELEMENT_CODE" => $_REQUEST["ELEMENT_CODE"],

                "SEF_MODE" => "Y",
                "SEF_FOLDER" => "/catalog/videocard/",
                "DETAIL_URL" => "/catalog/videocard/#ELEMENT_CODE#/",
                "ELEMENT_URL" => "/catalog/videocard/#ELEMENT_CODE#/",

                "SET_TITLE" => "Y",
                "SET_BROWSER_TITLE" => "Y",
                "SET_META_KEYWORDS" => "Y",
                "SET_META_DESCRIPTION" => "Y",
                "SET_CANONICAL_URL" => "Y",

                "PROPERTY_CODE" => [
                    "MANUFACTURER",
                    "CRYPTO",
                    "ALGORITHM",
                    "HASHRATE",
                    "EFFICIENCY",
                    "POWER",
                    "GUARANTEE",
                    "HIT",
                    "MORE_PHOTO"
                ],
                "PRICE_CODE" => ["BASE"],

                "SET_STATUS_404" => "Y",
                "SHOW_404" => "Y",
                "FILE_404" => "/404.php",
            ],
            false
        );
        ?>
    </div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>