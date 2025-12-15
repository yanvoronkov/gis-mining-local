<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "NAV_RESULT" => array(
            "NAME" => GetMessage("NAV_RESULT"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "SHOW_ALWAYS" => array(
            "NAME" => GetMessage("SHOW_ALWAYS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "NAV_TITLE" => array(
            "NAME" => GetMessage("NAV_TITLE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "BASE_LINK" => array(
            "NAME" => GetMessage("BASE_LINK"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
    ),
);
?>
