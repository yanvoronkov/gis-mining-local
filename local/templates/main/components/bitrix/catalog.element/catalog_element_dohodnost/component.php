<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Компонент catalog.element для работы с SEO-полями инфоблока
 * Использует стандартные SEO-функции Битрикса
 */

class CatalogElementComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult = $this->getElementData();
            
            // Отладочная информация в логах
            error_log("DEBUG: Component executed for element: " . ($this->arParams['ELEMENT_CODE'] ?? $this->arParams['ELEMENT_ID']));
            error_log("DEBUG: arResult keys: " . implode(', ', array_keys($this->arResult)));
            
            $this->includeComponentTemplate();
            
            // ВАЖНО: Вызываем component_epilog.php после шаблона
            error_log("DEBUG: Вызываем component_epilog.php");
            
            // Устанавливаем глобальную переменную для epilog
            global $arResult;
            $arResult = $this->arResult;
            
            // Подключаем epilog
            $epilogPath = $this->GetPath() . '/component_epilog.php';
            if (file_exists($epilogPath)) {
                include($epilogPath);
                error_log("DEBUG: component_epilog.php выполнен");
            } else {
                error_log("DEBUG: component_epilog.php не найден по пути: " . $epilogPath);
            }
        }
    }

    protected function getElementData()
    {
        error_log("DEBUG: getElementData вызван");
        
        $arResult = array();
        
        // Получаем ID элемента по символьному коду
        $elementId = null;
        if (!empty($this->arParams['ELEMENT_CODE'])) {
            // Используем старый API Битрикса для совместимости
            $element = \CIBlockElement::GetList(
                array(),
                array(
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                    'CODE' => $this->arParams['ELEMENT_CODE'],
                    'ACTIVE' => 'Y'
                ),
                false,
                false,
                array('ID', 'IBLOCK_ID')
            )->Fetch();
            
            if ($element) {
                $elementId = $element['ID'];
                error_log("DEBUG: Found element ID: " . $elementId . " for code: " . $this->arParams['ELEMENT_CODE']);
            } else {
                error_log("DEBUG: Element not found for code: " . $this->arParams['ELEMENT_CODE']);
            }
        } elseif (!empty($this->arParams['ELEMENT_ID'])) {
            $elementId = $this->arParams['ELEMENT_ID'];
            error_log("DEBUG: Using element ID: " . $elementId);
        }

        if (!$elementId) {
            error_log("DEBUG: No element ID found, aborting");
            $this->abortResultCache();
            return array();
        }

        // Получаем основную информацию об элементе ВКЛЮЧАЯ SEO-ПОЛЯ
        $element = \CIBlockElement::GetList(
            array(),
            array(
                'ID' => $elementId,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array(
                'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_TEXT',
                'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DATE_CREATE', 'TIMESTAMP_X',
                // SEO-поля
                'IPROPERTY_TEMPLATES', 'IPROPERTY_VALUES'
            )
        )->Fetch();

        if (!$element) {
            error_log("DEBUG: Element data not found for ID: " . $elementId);
            $this->abortResultCache();
            return array();
        }

        error_log("DEBUG: Element data retrieved: " . $element['NAME']);

        $arResult = $element;

        // Получаем свойства элемента
        if (!empty($this->arParams['PROPERTY_CODE'])) {
            $arResult['PROPERTIES'] = array();
            
            foreach ($this->arParams['PROPERTY_CODE'] as $propertyCode) {
                $property = \CIBlockElement::GetProperty(
                    $this->arParams['IBLOCK_ID'],
                    $elementId,
                    array("sort" => "asc"),
                    array("CODE" => $propertyCode)
                );
                
                $values = array();
                while ($value = $property->Fetch()) {
                    $values[] = $value['VALUE'];
                }
                
                if (!empty($values)) {
                    $arResult['PROPERTIES'][$propertyCode] = array(
                        'VALUE' => $values,
                        'VALUE_ENUM' => $values
                    );
                }
            }
        }

        // Получаем цены
        $prices = \CCatalogProduct::GetOptimalPrice($elementId);
        if ($prices && !empty($prices['PRICE'])) {
            $arResult['PRICES'] = array(
                'BASE' => array(
                    'PRICE' => $prices['PRICE'],
                    'CURRENCY' => $prices['CURRENCY'] ?: 'RUB'
                )
            );
        }

        // Получаем детальную картинку
        if (!empty($element['DETAIL_PICTURE'])) {
            $arResult['DETAIL_PICTURE'] = \CFile::GetFileArray($element['DETAIL_PICTURE']);
        }

        // Получаем анонсную картинку
        if (!empty($element['PREVIEW_PICTURE'])) {
            $arResult['PREVIEW_PICTURE'] = \CFile::GetFileArray($element['PREVIEW_PICTURE']);
        }

        // Получаем раздел товара для формирования правильного URL
        $sectionCode = 'asics'; // По умолчанию для товаров из инфоблока ASICS
        
        // Получаем информацию о разделе товара
        $rsSection = CIBlockSection::GetList(
            array(),
            array(
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'ID' => $element['IBLOCK_SECTION_ID']
            ),
            false,
            array('CODE', 'NAME')
        );
        
        if ($section = $rsSection->GetNext()) {
            $sectionCode = $section['CODE'] ?: 'asics';
        }
        
        // Формируем URL детальной страницы с учетом раздела
        $arResult['DETAIL_PAGE_URL'] = '/catalog/' . $sectionCode . '/' . $element['CODE'] . '/';

        // Получаем информацию о количестве товара
        $product = \CCatalogProduct::GetByID($elementId);
        if ($product) {
            $arResult['CATALOG_QUANTITY'] = $product['QUANTITY'];
        }

        error_log("DEBUG: getElementData завершен, возвращаем " . count($arResult) . " полей");
        return $arResult;
    }
}
