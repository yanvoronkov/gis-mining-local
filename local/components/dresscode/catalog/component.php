<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use \Dwstroy\SeoChpuLite\Helper;
use \Dwstroy\SeoChpuLite\Main;
use Bitrix\Main\Loader;

if( Loader::includeModule('dwstroy.seochpulite') ){
    $this->initComponentTemplate('section', $this->getSiteTemplateId(), '');
    Helper::addCodeToCatalogResultModifier($this->__template->__folder);
    Helper::makeOldUrl();
    error_reporting(0);
    $apl = new Main();
    $apl->IncludeComponent(
        "dresscode:catalog", $this->__templateName, $arParams, $this->__parent
    );
    Helper::clearGet();
    error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) & ~E_STRICT & ~E_DEPRECATED);
}else{
    include($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/dresscode/catalog/component.php');
}
