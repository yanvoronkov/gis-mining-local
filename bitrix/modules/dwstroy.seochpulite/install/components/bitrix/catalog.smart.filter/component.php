<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Loader;
use Dwstroy\SeoChpuLite\Helper;
use Dwstroy\SeoChpuLite\Main;
if(  Loader::includeModule('dwstroy.seochpulite') ){
    $this->initComponentTemplate('', $this->getSiteTemplateId(), '');
    Helper::addCodeToSmartFilterResultModifier($this->__template->__folder);
    Helper::makeOldUrl();
    error_reporting(0);
    $apl = new Main();
    $apl->IncludeComponent(
        "bitrix:catalog.smart.filter", $this->__templateName, $arParams, $this->__parent
    );
    Helper::clearGet();
    error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) & ~E_STRICT & ~E_DEPRECATED);
}else{
    // include($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/catalog.smart.filter/class.php');
    include($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/catalog.smart.filter/component.php');
}
