<?php
namespace Dwstroy\SeoChpuLite;

use Bitrix\Main as BMain;

class Main extends \CMain{
    private static $__classes_map = array();
    public $component = null;
    private $__componentStack = array();

    private static $forkActions = array();

    private function __getClassForPath($componentPath)
    {
        if (!isset(self::$__classes_map[$componentPath]))
        {
            $fname = $_SERVER["DOCUMENT_ROOT"].$componentPath."/class.php";
            if (file_exists($fname) && is_file($fname))
            {
                $beforeClasses = get_declared_classes();
                $beforeClassesCount = count($beforeClasses);
                include_once($fname);
                $afterClasses = get_declared_classes();
                $afterClassesCount = count($afterClasses);
                for ($i = $beforeClassesCount; $i < $afterClassesCount; $i++)
                {
                    if (\is_subclass_of($afterClasses[$i], "cbitrixcomponent"))
                        self::$__classes_map[$componentPath] = $afterClasses[$i];
                }
            }
            else
            {
                self::$__classes_map[$componentPath] = "";
            }
        }
        return self::$__classes_map[$componentPath];
    }

    public function IncludeComponent($componentName, $componentTemplate, $arParams = array(), $parentComponent = null, $arFunctionParams = array(), $returnResult = false)
    {
        /** @global CMain $APPLICATION */
        global $APPLICATION, $USER;

        if(is_array($this->arComponentMatch))
        {
            $skipComponent = true;
            foreach($this->arComponentMatch as $cValue)
            {
                if(mb_strpos($componentName, $cValue) !== false)
                {
                    $skipComponent = false;
                    break;
                }
            }
            if($skipComponent)
                return false;
        }

        $componentRelativePath = \CComponentEngine::MakeComponentPath($componentName);
        if ($componentRelativePath == '')
			return False;

        $debug = null;
		$bShowDebug = BMain\Application::getInstance()->getKernelSession()["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y"
			&& (
				$USER->CanDoOperation('edit_php')
				|| BMain\Application::getInstance()->getKernelSession()["SHOW_SQL_STAT"]=="Y"
			)
			&& !defined("PUBLIC_AJAX_MODE")
		;
        if($bShowDebug || $APPLICATION->ShowIncludeStat)
		{
			$debug = new \CDebugInfo();
			$debug->Start($componentName);
		}

        if (is_object($parentComponent))
		{
			if (!($parentComponent instanceof cbitrixcomponent))
				$parentComponent = null;
		}

        $bDrawIcons = ((!isset($arFunctionParams["HIDE_ICONS"]) || $arFunctionParams["HIDE_ICONS"] <> "Y") && $APPLICATION->GetShowIncludeAreas());

        if($bDrawIcons)
            echo $this->IncludeStringBefore();

        $result = null;
        $bComponentEnabled = (!isset($arFunctionParams["ACTIVE_COMPONENT"]) || $arFunctionParams["ACTIVE_COMPONENT"] <> "N");

        $this->component = new \CBitrixComponent();
        if($this->component->InitComponent($componentName))
        {

            $this->component->__path = str_replace('/local/', '/bitrix/', $this->component->__path);
            //$this->component->classOfComponent = self::__getClassForPath($this->component->__path);
            $reflectionClass = new \ReflectionClass('CBitrixComponent');

            $reflectionProperty = $reflectionClass->getProperty('classOfComponent');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($this->component, $this->__getClassForPath($this->component->__path));
            $obAjax = null;
            if($bComponentEnabled)
            {
                if($arParams['AJAX_MODE'] == 'Y')
                    $obAjax = new \CComponentAjax($componentName, $componentTemplate, $arParams, $parentComponent, $returnResult);

                $this->__componentStack[] = $this->component;
                $result = $this->component->IncludeComponent($componentTemplate, $arParams, $parentComponent);

                array_pop($this->__componentStack);
            }

            if($bDrawIcons)
            {
                $panel = new \CComponentPanel($this->component, $componentName, $componentTemplate, $parentComponent, $bComponentEnabled);
                $arIcons = $panel->GetIcons();

                echo $s = $this->IncludeStringAfter($arIcons["icons"], $arIcons["parameters"]);
            }

            if($bComponentEnabled && $obAjax)
            {
                $obAjax->Process();
            }
        }

        if($bShowDebug)
            echo $debug->Output($componentName, "/bitrix/components".$componentRelativePath."/component.php", $arParams["CACHE_TYPE"].$arParams["MENU_CACHE_TYPE"]);
        elseif(isset($debug))
            $debug->Stop($componentName, "/bitrix/components".$componentRelativePath."/component.php", $arParams["CACHE_TYPE"].$arParams["MENU_CACHE_TYPE"]);


        return $result;
    }

}
