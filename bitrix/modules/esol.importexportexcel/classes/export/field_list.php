<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CKDAEEFieldList {
	static $useStoreReserved;
	
	function __construct($params = array())
	{
		$this->isSku = (bool)($params['IBLOCK_ID'] && CKDAExportUtils::GetOfferIblock($params['IBLOCK_ID']));
		$this->sectionLevels = (is_numeric($params['MAX_SECTION_LEVEL']) > 0 ? $params['MAX_SECTION_LEVEL'] : 5);
		$this->sectionLevels = max(0, $this->sectionLevels);
		$this->sectionLevels = min(100, $this->sectionLevels);
	}
	
	public static function GetIblockElementFields()
	{
		return array(
			"IE_NAME" => array(
				"name" => Loc::getMessage("KDA_EE_FI_NAME"),
				"type" => "string",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_ID" => array(
				"name" => Loc::getMessage("KDA_EE_FI_ID"),
				"type" => "id",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_XML_ID" => array(
				"name" => Loc::getMessage("KDA_EE_FI_UNIXML"),
				"type" => "string",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_CODE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CODE"),
				"type" => "string",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_PREVIEW_PICTURE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CATIMG"),
				"type" => "file",
				"filtrable" => "Y"
			),
			"IE_PREVIEW_PICTURE_DESCRIPTION" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CATIMG_DESCRIPTION"),
				"type" => "string"
			),
			"IE_PREVIEW_TEXT" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CATDESCR"),
				"type" => "text",
				"filtrable" => "Y"
			),
			/*"IE_PREVIEW_TEXT|PREVIEW_TEXT_TYPE=html" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CATDESCR").' (html)',
			),*/
			"IE_PREVIEW_TEXT_TYPE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CATDESCR_TYPE"),
			),
			"IE_DETAIL_PICTURE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DETIMG"),
				"type" => "file",
				"filtrable" => "Y"
			),
			"IE_DETAIL_PICTURE_DESCRIPTION" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DETIMG_DESCRIPTION"),
				"type" => "string"
			),
			"IE_DETAIL_TEXT" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DETDESCR"),
				"type" => "text",
				"filtrable" => "Y"
			),
			/*"IE_DETAIL_TEXT|DETAIL_TEXT_TYPE=html" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DETDESCR").' (html)',
			),*/
			"IE_DETAIL_TEXT_TYPE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DETDESCR_TYPE"),
			),
			"IE_ACTIVE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_ACTIV"),
				"type" => "boolean",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_ACTIVE_FROM" => array(
				"name" => Loc::getMessage("KDA_EE_FI_ACTIVFROM"),
				"type" => "date",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_ACTIVE_TO" => array(
				"name" => Loc::getMessage("KDA_EE_FI_ACTIVTO"),
				"type" => "date",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_SORT" => array(
				"name" => Loc::getMessage("KDA_EE_FI_SORT"),
				"type" => "number",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_TAGS" => array(
				"name" => Loc::getMessage("KDA_EE_FI_TAGS"),
				"type" => "string",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_DATE_CREATE" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DATE_CREATE"),
				"type" => "date",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_CREATED_BY" => array(
				"name" => Loc::getMessage("KDA_EE_FI_CREATED_BY"),
				"type" => "user",
				"filtrable" => "Y"
			),
			"IE_TIMESTAMP_X" => array(
				"name" => Loc::getMessage("KDA_EE_FI_TIMESTAMP_X"),
				"type" => "date",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_MODIFIED_BY" => array(
				"name" => Loc::getMessage("KDA_EE_FI_MODIFIED_BY"),
				"type" => "user",
				"filtrable" => "Y"
			),
			"IE_SHOW_COUNTER" => array(
				"name" => Loc::getMessage("KDA_EE_FI_SHOW_COUNTER"),
				"type" => "number",
				"sortable" => "Y",
				"filtrable" => "Y"
			),
			"IE_DETAIL_PAGE_URL" => array(
				"name" => Loc::getMessage("KDA_EE_FI_DETAIL_PAGE_URL"),
			),
			"IE_QR_CODE_IMAGE" => array(
				"name" => Loc::getMessage("KDA_EE_QR_CODE_IMAGE"),
			)
		);
	}
	
	public static function GetFieldTitle($field)
	{
		$title = '';
		if(strpos($field, 'IE_')===0)
		{
			$iblockElementFields = self::GetIblockElementFields();
			$title = $iblockElementFields[$field]['name'];
		}
		return $title;
	}
	
	public static function GetIblockSectionElementFields()
	{
		$arFields = array(
			'IE_SECTION_PATH' => array(
				"name" => Loc::getMessage("KDA_EE_FI_SECTION_PATH")
			)
		);
		return $arFields;
	}
	
	public function GetIblockSectionFields($i, $IBLOCK_ID = false)
	{
		$arSections = array(
			'ISECT'.$i.'_NAME' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_NAME"),
				"type" => "string",
				"sortable" => (strlen($i)==0 ? "Y" : "N"),
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_CODE' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_CODE"),
				"type" => "string",
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_ID' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ID"),
				"type" => "id",
				"sortable" => (strlen($i)==0 ? "Y" : "N"),
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_XML_ID' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_XML_ID"),
				"type" => "string",
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_ACTIVE' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ACTIVE"),
				"type" => "boolean",
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_SORT' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SORT"),
				"type" => "number",
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_PICTURE' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_PICTURE"),
				"type" => "file",
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_DETAIL_PICTURE' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_DETAIL_PICTURE"),
				"type" => "file",
				"filtrable" => 'Y'
			),
			'ISECT'.$i.'_DESCRIPTION' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_DESCRIPTION"),
				"type" => "text",
				"filtrable" => 'Y'
			) ,
			/*'ISECT'.$i.'_DESCRIPTION|DESCRIPTION_TYPE=html' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_DESCRIPTION").' (html)',
			) ,*/
			'ISECT'.$i.'_SECTION_PAGE_URL' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PAGE_URL"),
			),
			/*'ISECT'.$i.'_ELEMENT_CNT' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_CNT")
			),*/
			'ISECT'.$i.'_IPROP_TEMP_SECTION_META_TITLE' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_META_TITLE"),
			) ,
			'ISECT'.$i.'_IPROP_TEMP_SECTION_META_KEYWORDS' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_META_KEYWORDS"),
			) ,
			'ISECT'.$i.'_IPROP_TEMP_SECTION_META_DESCRIPTION' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_META_DESCRIPTION"),
			) ,
			'ISECT'.$i.'_IPROP_TEMP_SECTION_PAGE_TITLE' => array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PAGE_TITLE"),
			) , 
		);
		
		$arSeoMoreFields = array();
		if(\Bitrix\Main\Loader::includeModule('dwstroy.seo') && is_callable(array('\Dwstroy\Seo\CSeo', 'getMoreFields')))
		{
			$arSeoMoreFields = \Dwstroy\Seo\CSeo::getMoreFields($IBLOCK_ID, 'S');
			$arSeoMoreFields = (isset($arSeoMoreFields['S']) && is_array($arSeoMoreFields['S']) ? $arSeoMoreFields['S'] : array());
		}
		foreach($arSeoMoreFields as $k=>$v)
		{
			$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_'.$v['CODE']] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_SEO_FIELD").$v['NAME']
			);
		}
		
		if(strlen($i)==0)
		{
			/*$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PICTURE_FILE_NAME")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_DETAIL_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_DETAIL_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_DETAIL_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_DETAIL_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_SECTION_DETAIL_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_DETAIL_PICTURE_FILE_NAME")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_META_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_META_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_META_KEYWORDS'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_META_KEYWORDS")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_META_DESCRIPTION'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_META_DESCRIPTION")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_PAGE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_PAGE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_PREVIEW_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_PREVIEW_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_PREVIEW_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_PREVIEW_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_PREVIEW_PICTURE_FILE_NAME")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_DETAIL_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_DETAIL_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_DETAIL_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_DETAIL_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_ELEMENT_DETAIL_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_DETAIL_PICTURE_FILE_NAME")
			);*/
			
			
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_META_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_META_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_META_KEYWORDS'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_META_KEYWORDS")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_META_DESCRIPTION'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_META_DESCRIPTION")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_PAGE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_PAGE_TITLE")
			);
			foreach($arSeoMoreFields as $k=>$v)
			{
				$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_'.$v['CODE']] = array(
					"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_SEO_TEMPLATE_FIELD").$v['NAME']
				);
			}
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_PICTURE_FILE_NAME")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_DETAIL_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_DETAIL_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_DETAIL_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_DETAIL_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_SECTION_DETAIL_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_SECTION_DETAIL_PICTURE_FILE_NAME")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_META_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_META_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_META_KEYWORDS'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_META_KEYWORDS")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_META_DESCRIPTION'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_META_DESCRIPTION")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_PAGE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_PAGE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_NAME")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_ALT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_ALT")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_TITLE'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_TITLE")
			);
			$arSections['ISECT'.$i.'_IPROP_TEMP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_NAME'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_NAME")
			);
			
			
			$arSections['ISECT'.$i.'_IBLOCK_SECTION_ID'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_IBLOCK_SECTION_ID")
			);
			$arSections['ISECT'.$i.'_DEPTH_LEVEL'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_DEPTH_LEVEL"),
				"type" => "number",
				"filtrable" => 'Y'
			);
			$arSections['ISECT'.$i.'_PATH_NAMES'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PATH_NAMES")
			);
			$arSections['ISECT'.$i.'_SECTION_PROPERTIES'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PROPERTIES")
			);

		}
		
		if($IBLOCK_ID)
		{
			if(!isset($this->arSectionsProps)) $this->arSectionsProps = array();
			if(!isset($this->arSectionsProps[$IBLOCK_ID]))
			{
				$dbRes = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$IBLOCK_ID.'_SECTION', 'LANG' => LANGUAGE_ID));
				$arProps = array();
				while($arr = $dbRes->Fetch())
				{
					$type = 'string';
					if(in_array($arr['USER_TYPE_ID'], array('iblock_section', 'hlblock', 'iblock_element', 'enumeration')))
					{
						$type = 'list';
					}
					elseif(in_array($arr['USER_TYPE_ID'], array('integer', 'double')))
					{
						$type = 'number';
					}
					elseif($arr['USER_TYPE_ID']=='file')
					{
						$type = 'file';
					}
					elseif(in_array($arr['USER_TYPE_ID'], array('date', 'datetime')))
					{
						$type = 'date';
					}
					$name = ($arr['EDIT_FORM_LABEL'] ? $arr['EDIT_FORM_LABEL'].' ('.$arr['FIELD_NAME'].')' : $arr['FIELD_NAME']);
					$arProps[$arr['FIELD_NAME']] = array(
						'name' => $name,
						"type" => $type,
						"filtrable" => 'Y'
					);
				}
				$this->arSectionsProps[$IBLOCK_ID] = $arProps;
			}
			
			if(!empty($this->arSectionsProps[$IBLOCK_ID]))
			{
				foreach($this->arSectionsProps[$IBLOCK_ID] as $k=>$v)
				{
					$arSections['ISECT'.$i.'_'.$k] = $v;
				}
			}
		}
		
		return $arSections;
	}
	
	public static function GetCatalogSetFields($IBLOCK_ID)
	{
		$arSetFields = array();
		if(Loader::includeModule('catalog') && CBXFeatures::IsFeatureEnabled('CatCompleteSet'))
		{
			if($IBLOCK_ID!==true)
			{
				$dbRes = CCatalog::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
				$arCatalog = $dbRes->Fetch();
			}
			
			if($arCatalog || $IBLOCK_ID===true)
			{
				$arSetFields = array(
					array(
						"value" => "ICAT_SET_ITEM_ID",
						"name" => Loc::getMessage("KDA_EE_FI_SET_ITEM"),
					),
					array(
						"value" => "ICAT_SET_QUANTITY",
						"name" => Loc::getMessage("KDA_EE_FI_SET_QUANTITY"),
					),
					array(
						"value" => "ICAT_SET_SORT",
						"name" => Loc::getMessage("KDA_EE_FI_SET_SORT"),
					),
				);
			}
		}
		return (!empty($arSetFields) ? $arSetFields : false);
	}
	
	public static function GetCatalogSet2Fields($IBLOCK_ID)
	{
		$arSetFields = array();
		if(Loader::includeModule('catalog') && CBXFeatures::IsFeatureEnabled('CatCompleteSet'))
		{
			if($IBLOCK_ID!==true)
			{
				$dbRes = CCatalog::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
				$arCatalog = $dbRes->Fetch();
			}
			
			if($arCatalog || $IBLOCK_ID===true)
			{
				$arSetFields = array(
					array(
						"value" => "ICAT_SET2_ITEM_ID",
						"name" => Loc::getMessage("KDA_EE_FI_SET2_ITEM"),
					),
					array(
						"value" => "ICAT_SET2_QUANTITY",
						"name" => Loc::getMessage("KDA_EE_FI_SET2_QUANTITY"),
					),
					array(
						"value" => "ICAT_SET2_DISCOUNT_PERCENT",
						"name" => Loc::getMessage("KDA_EE_FI_SET2_DISCOUNT_PERCENT"),
					),
					array(
						"value" => "ICAT_SET2_SORT",
						"name" => Loc::getMessage("KDA_EE_FI_SET2_SORT"),
					),
				);
			}
		}
		return (!empty($arSetFields) ? $arSetFields : false);
	}
	
	public static function GetIblockProperties($IBLOCK_ID, $arParams=array())
	{
		$arProperties = array();
		if(Loader::includeModule('iblock'))
		{
			$arPropIds = false;
			if($arParams['SHOW_ONLY_SECTION_PROPERTY'] || $arParams['SHOW_ONLY_SECTION_PROPERTY_WO_IBLOCK'])
			{
				$arSections = $arParams['SECTIONS'];
				if(!is_array($arSections))
				{
					$arSections = array();
					if(is_array($arParams['SFILTER']))
					{
						$arSectionFilter = array('IBLOCK_ID' => $IBLOCK_ID);
						$eFilter = new CKDAEEFilter($IBLOCK_ID);
						$eFilter->SetSectionFilter($arSectionFilter, $arParams['SFILTER']);
						if(count($arSectionFilter) > 1)
						{
							$dbResSections = \CIblockSection::GetList(array(), $arSectionFilter, false, array('ID'));
							while($arr = $dbResSections->Fetch())
							{
								if(!in_array($arr['ID'], $arSections)) $arSections[] = $arr['ID'];
							}
						}
					}
					if(is_array($arParams['EFILTER']))
					{
						$arElementFilter = array('IBLOCK_ID' => $IBLOCK_ID);
						$eFilter = new CKDAEEFilter($IBLOCK_ID);
						$eFilter->SetFilter($arElementFilter, $arParams['EFILTER']);
						if(array_key_exists('SECTION_ID', $arElementFilter))
						{
							if(is_array($arElementFilter['SECTION_ID'])) $arSections = array_merge($arSections, $arElementFilter['SECTION_ID']);
							else $arSections[] = $arElementFilter['SECTION_ID'];
						}
						if(array_key_exists('INCLUDE_SUBSECTIONS', $arElementFilter))
						{
							$arParams['ISSUBSECTIONS'] = true;
						}
					}
				}
				if(!is_array($arSections)) $arSections = array();
				$arSections = array_diff($arSections, array(-1));
				if(!empty($arSections))
				{
					if($arParams['SHOW_ONLY_SECTION_PROPERTY'])
					{
						if(!in_array(0, $arSections)) $arSections[] = 0;
					}
					else
					{
						$arSections = array_diff($arSections, array(0));
					}
					/*Parents*/
					$dbRes = \Bitrix\Iblock\SectionTable::getList(array(
						"runtime" => array(
							new \Bitrix\Main\Entity\ReferenceField(
								'SECTION2',
								'\Bitrix\Iblock\SectionTable',
								array(
									'>this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
									'<this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN'
								)
							)
						),
						"select" => array("CID" => "SECTION2.ID"),
						"filter" => array(
							"=SECTION2.IBLOCK_ID" => $IBLOCK_ID,
							"=ID" => $arSections
						),
					));
					while($arr = $dbRes->Fetch())
					{
						if(!in_array($arr['CID'], $arSections)) $arSections[] = $arr['CID'];
					}
					/*/Parents*/
					/*Childs*/
					if($arParams['ISSUBSECTIONS'])
					{
						$dbRes = \Bitrix\Iblock\SectionTable::getList(array(
							"runtime" => array(
								new \Bitrix\Main\Entity\ReferenceField(
									'SECTION2',
									'\Bitrix\Iblock\SectionTable',
									array(
										'<this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
										'>this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN'
									)
								)
							),
							"select" => array("CID" => "SECTION2.ID"),
							"filter" => array(
								"=SECTION2.IBLOCK_ID" => $IBLOCK_ID,
								"=ID" => $arSections
							),
						));
						while($arr = $dbRes->Fetch())
						{
							if(!in_array($arr['CID'], $arSections)) $arSections[] = $arr['CID'];
						}
					}
					/*/Childs*/
					$arPropIds = array();
					$dbRes = \Bitrix\Iblock\SectionPropertyTable::getList(array(
						"select" => array("PROPERTY_ID"),
						"filter" => array(
							"=IBLOCK_ID" => $IBLOCK_ID,
							"=PROPERTY.IBLOCK_ID" => $IBLOCK_ID,
							"=SECTION_ID" => $arSections
						),
					));
					while($arr = $dbRes->Fetch())
					{
						$arPropIds[] = $arr['PROPERTY_ID'];
					}
				}
			}
			
			$dbRes = CIBlockProperty::GetList(array(
				"sort" => "asc",
				"name" => "asc",
			) , array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => $IBLOCK_ID,
				"CHECK_PERMISSIONS" => "N",
			));
			while($arr = $dbRes->Fetch())
			{
				if(is_array($arPropIds) && !in_array($arr["ID"], $arPropIds)) continue;
				
				$type = 'string';
				if(in_array($arr['PROPERTY_TYPE'], array('L', 'G', 'E')) ||
					($arr['PROPERTY_TYPE']=='S' && $arr['USER_TYPE']=='UserID') ||
					($arr['PROPERTY_TYPE']=='S' && $arr['USER_TYPE']=='directory'))
				{
					$type = 'list';
				}
				elseif($arr['PROPERTY_TYPE']=='F')
				{
					$type = 'file';
				}
				elseif($arr['PROPERTY_TYPE']=='S' && $arr['USER_TYPE']=='HTML')
				{
					$type = 'text';
				}
				elseif($arr['PROPERTY_TYPE']=='S' && ($arr['USER_TYPE']=='Date' || $arr['USER_TYPE']=='DateTime'))
				{
					$type = 'date';
				}
				elseif(in_array($arr['PROPERTY_TYPE'], array('S', 'N')) && strlen($arr['USER_TYPE']) > 0 && !in_array($arr['USER_TYPE'], array('HTML', 'video', 'Date', 'DateTime', 'map_yandex', 'map_google', 'UserID', 'TopicID', 'FileMan', 'ElementXmlID', 'directory')))
				{
					$uType = \CIBlockProperty::GetUserType($arr["USER_TYPE"]);
					if(is_array($uType) && array_key_exists("GetPropertyFieldHtml", $uType))
					{
						$type = 'custom';
					}
				}
				
				$bSortable = (in_array($arr['PROPERTY_TYPE'], array('S', 'N', 'L', 'E')) && $arr['MULTIPLE']=='N');
				$arProperties[] = array(
					"ID" => $arr["ID"],
					"NAME" => $arr["NAME"],
					"value" => "IP_PROP".$arr["ID"],
					"name" => $arr["NAME"].' ['.$arr["CODE"].']',
					"wdesc" => (bool)($arr["WITH_DESCRIPTION"]=='Y' || ($arr['PROPERTY_TYPE']=='E' && $arr['USER_TYPE'] && !(in_array($arr['USER_TYPE'], array('SKU', 'EList', 'EAutocomplete')))) || in_array($arr['USER_TYPE'], array('maxyss_unit'))),
					"sortable" => ($bSortable ? "Y": "N"),
					"type" => $type,
					"filtrable" => 'Y'
				);
				
				if($arr['PROPERTY_TYPE']=='S' && $arr['USER_TYPE']=='C' && function_exists('json_decode') && is_array($arr['USER_TYPE_SETTINGS']) && ($arSubFields = preg_grep('/_TITLE$/', array_keys($arr['USER_TYPE_SETTINGS']))))
				{
					foreach($arSubFields as $v)
					{
						$subId = mb_substr($v, 0, -6);
						$arProperties[] = array(
							"ID" => $arr["ID"].'_'.$subId,
							"NAME" => $arr["NAME"].' - '.$arr['USER_TYPE_SETTINGS'][$v],
							"value" => "IP_PROP".$arr["ID"].'|'.$subId,
							"name" => $arr["NAME"].' ['.$arr["CODE"].']'.' - '.$arr['USER_TYPE_SETTINGS'][$v],
							"wdesc" => false,
							"sortable" => "N"
						);
					}
				}
			}
		}
		if(!empty($arProperties) && $arParams['ONLYPROPS']!='Y')
		{
			array_unshift($arProperties, array(
				"value" => "IP_LIST_PROPS",
				"name" => Loc::getMessage("KDA_EE_FI_PROP_LIST"),
				"wdesc" => false,
				"sortable" => "N"
			));
		}
		return (!empty($arProperties) ? $arProperties : false);
	}
	
	public static function GetIblockIpropTemplates()
	{
		return array(
			"IPROP_TEMP_ELEMENT_META_TITLE" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_META_TITLE"),
			"IPROP_TEMP_ELEMENT_META_KEYWORDS" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_META_KEYWORDS"),
			"IPROP_TEMP_ELEMENT_META_DESCRIPTION" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_META_DESCRIPTION"),
			"IPROP_TEMP_ELEMENT_PAGE_TITLE" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_PAGE_TITLE"),
			"IPROP_TEMP_ELEMENT_PREVIEW_PICTURE_FILE_ALT" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_PREVIEW_PICTURE_FILE_ALT"),
			"IPROP_TEMP_ELEMENT_PREVIEW_PICTURE_FILE_TITLE" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_PREVIEW_PICTURE_FILE_TITLE"),
			"IPROP_TEMP_ELEMENT_PREVIEW_PICTURE_FILE_NAME" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_PREVIEW_PICTURE_FILE_NAME"),
			"IPROP_TEMP_ELEMENT_DETAIL_PICTURE_FILE_ALT" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_DETAIL_PICTURE_FILE_ALT"),
			"IPROP_TEMP_ELEMENT_DETAIL_PICTURE_FILE_TITLE" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_DETAIL_PICTURE_FILE_TITLE"),
			"IPROP_TEMP_ELEMENT_DETAIL_PICTURE_FILE_NAME" => Loc::getMessage("KDA_EE_IPROP_ELEMENT_DETAIL_PICTURE_FILE_NAME"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_META_TITLE" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_META_TITLE"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_META_KEYWORDS" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_META_KEYWORDS"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_META_DESCRIPTION" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_META_DESCRIPTION"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_PAGE_TITLE" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_PAGE_TITLE"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_ALT" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_ALT"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_TITLE" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_TITLE"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_NAME" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_PREVIEW_PICTURE_FILE_NAME"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_ALT" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_ALT"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_TITLE" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_TITLE"),
			"IPROP_TEMP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_NAME" => Loc::getMessage("KDA_EE_IPROP_TEMPLATE_ELEMENT_DETAIL_PICTURE_FILE_NAME"),
			"IPROP_TEMP_CH_ELEMENT_META_TITLE" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_META_TITLE"),
			"IPROP_TEMP_CH_ELEMENT_META_KEYWORDS" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_META_KEYWORDS"),
			"IPROP_TEMP_CH_ELEMENT_META_DESCRIPTION" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_META_DESCRIPTION"),
			"IPROP_TEMP_CH_ELEMENT_PAGE_TITLE" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_PAGE_TITLE"),
			"IPROP_TEMP_CH_ELEMENT_PREVIEW_PICTURE_FILE_ALT" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_PREVIEW_PICTURE_FILE_ALT"),
			"IPROP_TEMP_CH_ELEMENT_PREVIEW_PICTURE_FILE_TITLE" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_PREVIEW_PICTURE_FILE_TITLE"),
			"IPROP_TEMP_CH_ELEMENT_PREVIEW_PICTURE_FILE_NAME" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_PREVIEW_PICTURE_FILE_NAME"),
			"IPROP_TEMP_CH_ELEMENT_DETAIL_PICTURE_FILE_ALT" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_DETAIL_PICTURE_FILE_ALT"),
			"IPROP_TEMP_CH_ELEMENT_DETAIL_PICTURE_FILE_TITLE" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_DETAIL_PICTURE_FILE_TITLE"),
			"IPROP_TEMP_CH_ELEMENT_DETAIL_PICTURE_FILE_NAME" => Loc::getMessage("KDA_EE_IPROP_CH_ELEMENT_DETAIL_PICTURE_FILE_NAME"),
		);
	}
	
	public static function GetCatalogFields($IBLOCK_ID, $offers=false)
	{
		$arCatalogFields = array();
		if(Loader::includeModule('catalog'))
		{
			$dbRes = CCatalog::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
			$arCatalog = $dbRes->Fetch();
			
			if($arCatalog)
			{
				$arCatalogFields[] = array(
					"value" => "ICAT_PURCHASING_PRICE",
					"name" => Loc::getMessage("KDA_EE_FI_PURCHASING_PRICE"),
					"type" => "number",
					"filtrable" => "Y"
				);
				$arCatalogFields[] = array(
					"value" => "ICAT_PURCHASING_CURRENCY",
					"name" => Loc::getMessage("KDA_EE_FI_PRICE_CURRENCY").' "'.Loc::getMessage("KDA_EE_FI_PURCHASING_PRICE").'"',
					"type" => "list",
					"filtrable" => "Y"
				);
			
				$dbPriceType = CCatalogGroup::GetList(array("SORT" => "ASC"));
				while($arPriceType = $dbPriceType->Fetch())
				{
					$arCatalogFields[] = array(
						"value" => "ICAT_PRICE".$arPriceType["ID"]."_PRICE",
						"name" => Loc::getMessage("KDA_EE_FI_PRICE_NAME").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'"',
						"sortable" => "Y",
						"type" => "number",
						"filtrable" => "Y"
					);
					$arCatalogFields[] = array(
						"value" => "ICAT_PRICE".$arPriceType["ID"]."_PRICE_DISCOUNT",
						"name" => Loc::getMessage("KDA_EE_FI_PRICE_NAME").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'" '.Loc::getMessage("KDA_EE_FI_PRICE_WITH_DISCOUNT"),
					);
					$arCatalogFields[] = array(
						"value" => "ICAT_PRICE".$arPriceType["ID"]."_CURRENCY",
						"name" => Loc::getMessage("KDA_EE_FI_PRICE_CURRENCY").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'"',
						"type" => "list",
						"filtrable" => "Y"
					);
					$arCatalogFields[] = array(
						"value" => "ICAT_PRICE".$arPriceType["ID"]."_PRICE_EXT",
						"name" => Loc::getMessage("KDA_EE_FI_PRICE_NAME").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'" - '.Loc::getMessage("KDA_EE_FI_PRICE_EXT_MODE"),
						"sortable" => "Y"
					);
					if($arPriceType['BASE']!='Y')
					{
						$arCatalogFields[] = array(
							"value" => "ICAT_PRICE".$arPriceType["ID"]."_EXTRA",
							"name" => Loc::getMessage("KDA_EE_FI_PRICE_EXTRA").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'" ('.Loc::getMessage("KDA_EE_FI_PRICE_EXTRA_MEASURE").')',
						);
						$arCatalogFields[] = array(
							"value" => "ICAT_PRICE".$arPriceType["ID"]."_EXTRA_NAME",
							"name" => Loc::getMessage("KDA_EE_FI_PRICE_EXTRA_NAME").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'"',
						);
						$arCatalogFields[] = array(
							"value" => "ICAT_PRICE".$arPriceType["ID"]."_EXTRA_ID",
							"name" => Loc::getMessage("KDA_EE_FI_PRICE_EXTRA_ID").' "'.($arPriceType["NAME_LANG"] ? $arPriceType["NAME_LANG"] : $arPriceType["NAME"]).'"',
						);
					}
				}
				
				$arCatalogFields[] = array(
					"value" => "ICAT_AVAILABLE",
					"name" => Loc::getMessage("KDA_EE_FI_AVAILABLE"),
					"sortable" => "Y",
					"type" => "list",
					"filtrable" => "Y"
				);
				$arCatalogFields[] = array(
					"value" => "ICAT_QUANTITY",
					"name" => Loc::getMessage("KDA_EE_FI_QUANTITY"),
					"sortable" => "Y",
					"type" => "number",
					"filtrable" => "Y"
				);
				$arCatalogFields[] = array(
					"value" => "ICAT_QUANTITY_RESERVED",
					"name" => Loc::getMessage("KDA_EE_FI_QUANTITY_RESERVED"),
					"type" => "number",
					"filtrable" => "Y"
				);
				
				$arReservedFields = array();
				$dbRes = CCatalogStore::GetList(array("SORT"=>"ID"), array(), false, false, array("ID", "TITLE", "ADDRESS"));
				while($arStore = $dbRes->Fetch())
				{
					if(strlen($arStore['TITLE'])==0 && $arStore['ADDRESS']) $arStore['TITLE'] = $arStore['ADDRESS'];
					$arCatalogFields[] = array(
						"value" => "ICAT_STORE".$arStore["ID"]."_AMOUNT",
						"name" => Loc::getMessage("KDA_EE_FI_QUANTITY_STORE").' "'.$arStore["TITLE"].'"',
						"type" => "number",
						"filtrable" => "Y"
					);
					if(self::IsStoreReserved())
					{
						$arReservedFields[] = array(
							"value" => "ICAT_STORE".$arStore["ID"]."_QUANTITY_RESERVED",
							"name" => Loc::getMessage("KDA_EE_FI_QUANTITY_STORE_RESERVED").' "'.$arStore["TITLE"].'"',
							"type" => "number"
						);
					}
				}
				foreach($arReservedFields as $arField)
				{
					$arCatalogFields[] = $arField;
				}
				
				$arCatalogFields[] = array(
					"value" => "ICAT_WEIGHT",
					"name" => Loc::getMessage("KDA_EE_FI_WEIGHT"),
					"sortable" => "Y"
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_LENGTH",
					"name" => Loc::getMessage("KDA_EE_FI_LENGTH"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_WIDTH",
					"name" => Loc::getMessage("KDA_EE_FI_WIDTH"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_HEIGHT",
					"name" => Loc::getMessage("KDA_EE_FI_HEIGHT"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_MEASURE",
					"name" => Loc::getMessage("KDA_EE_FI_MEASURE"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_MEASURE_RATIO",
					"name" => Loc::getMessage("KDA_EE_FI_MEASURE_RATIO"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_VAT_INCLUDED",
					"name" => Loc::getMessage("KDA_EE_FI_VAT_INCLUDED"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_VAT_ID",
					"name" => Loc::getMessage("KDA_EE_FI_VAT_ID"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_BARCODE",
					"name" => Loc::getMessage("KDA_EE_FI_BARCODE"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_BARCODE_IMAGE",
					"name" => Loc::getMessage("KDA_EE_BARCODE_IMAGE"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_TYPE",
					"name" => Loc::getMessage("KDA_EE_PRODUCT_TYPE"),
					"type" => "list",
					"filtrable" => "Y"
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_QUANTITY_TRACE",
					"name" => Loc::getMessage("KDA_EE_FI_QUANTITY_TRACE"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_CAN_BUY_ZERO",
					"name" => Loc::getMessage("KDA_EE_FI_CAN_BUY_ZERO"),
				);
				
				/*$arCatalogFields[] = array(
					"value" => "ICAT_NEGATIVE_AMOUNT_TRACE",
					"name" => Loc::getMessage("KDA_EE_FI_NEGATIVE_AMOUNT_TRACE"),
				);*/
				
				$arCatalogFields[] = array(
					"value" => "ICAT_SUBSCRIBE",
					"name" => Loc::getMessage("KDA_EE_FI_SUBSCRIBE"),
				);
				
				if(!$offers)
				{
					$arCatalogFields[] = array(
						"value" => "ICAT_UF_PRODUCT_GROUP",
						"name" => Loc::getMessage("KDA_EE_FI_UF_PRODUCT_GROUP"),
					);
				}
			}
		}
		return (!empty($arCatalogFields) ? $arCatalogFields : false);
	}
	
	public static function IsStoreReserved()
	{
		if(!isset(static::$useStoreReserved))
		{
			$arStoreProductMap = \Bitrix\Catalog\StoreProductTable::getMap();
			static::$useStoreReserved = (bool)isset($arStoreProductMap['QUANTITY_RESERVED']);
		}
		return static::$useStoreReserved;
	}
	
	public static function GetCatalogDiscountFields($IBLOCK_ID)
	{
		$arDiscountFields = array();
		if(Loader::includeModule('catalog'))
		{
			$dbRes = CCatalog::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
			$arCatalog = $dbRes->Fetch();
			
			if($arCatalog)
			{
				$arDiscountFields = array(
					array(
						"value" => "ICAT_DISCOUNT_VALUE|VALUE_TYPE=P",
						"name" => Loc::getMessage("KDA_EE_FI_DISCOUNT_PERCENT"),
					),
					array(
						"value" => "ICAT_DISCOUNT_VALUE|VALUE_TYPE=F",
						"name" => Loc::getMessage("KDA_EE_FI_DISCOUNT_SUM"),
					),
					array(
						"value" => "ICAT_DISCOUNT_VALUE|VALUE_TYPE=S",
						"name" => Loc::getMessage("KDA_EE_FI_DISCOUNT_PRICE"),
					),
					array(
						"value" => "ICAT_DISCOUNT_ACTIVE_FROM",
						"name" => Loc::getMessage("KDA_EE_FI_DISCOUNT_ACTIVE_FROM"),
					),
					array(
						"value" => "ICAT_DISCOUNT_ACTIVE_TO",
						"name" => Loc::getMessage("KDA_EE_FI_DISCOUNT_ACTIVE_TO"),
					)
				);
			}
		}
		return (!empty($arDiscountFields) ? $arDiscountFields : false);
	}
	
	public static function GetSaleOrderFields($IBLOCK_ID)
	{
		$arOrderFields = array();
		if(Loader::includeModule('sale') && Loader::includeModule('catalog'))
		{
			$dbRes = CCatalog::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
			$arCatalog = $dbRes->Fetch();
			
			if($arCatalog)
			{
				$arOrderFields = array(
					array(
						"value" => "ICAT_ORDER_PRODUCT_QNT",
						"name" => Loc::getMessage("KDA_EE_FI_ORDER_PRODUCT_QNT"),
					)
				);
			}
		}
		return (!empty($arOrderFields) ? $arOrderFields : false);
	}
	
	public static function GetContentTableFields()
	{
		return array(
			"CT_SHEET_NUMBER" => array(
				"name" => Loc::getMessage("KDA_EE_FI_SHEET_NUMBER")
			),
			"CT_SHEET_NAME" => array(
				"name" => Loc::getMessage("KDA_EE_FI_SHEET_NAME"),
			),
			"CT_SHEET_LINK" => array(
				"name" => Loc::getMessage("KDA_EE_FI_SHEET_LINK"),
			),
			"CT_SHEET_POS_NUMBER" => array(
				"name" => Loc::getMessage("KDA_EE_FI_SHEET_POS_NUMBER"),
			),
		);
	}
	
	public function GetFields($IBLOCK_ID, $offers = false, $arParams = array())
	{
		if(!$this->aFields)
		{
			$this->aFields = array();
		}
		$ikey = $IBLOCK_ID.(empty($arParams) ? '' : md5(serialize($arParams)));
		
		if(!$this->aFields[$ikey])
		{
			$this->aFields[$ikey]['element'] = array(
				'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER") : Loc::getMessage("KDA_EE_GROUP_ELEMENT")),
				'items' => array()
			);
			foreach(self::GetIblockElementFields() as $k=>$ar)
			{
				if($offers) $k = 'OFFER_'.$k;
				$this->aFields[$ikey]['element']['items'][$k] = $ar["name"];
			}
			
			if($arPropFields = self::GetIblockProperties($IBLOCK_ID, $arParams))
			{
				$this->aFields[$ikey]['prop'] = array(
					'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_PROP").')' : Loc::getMessage("KDA_EE_GROUP_PROP")),
					'items' => array()
				);
				foreach($arPropFields as $ar)
				{
					if($offers)
					{
						//if(preg_match('/\D'.$offers.'$/', $ar["value"])) continue;
						$ar["value"] = 'OFFER_'.$ar["value"];
					} 
					$this->aFields[$ikey]['prop']['items'][$ar["value"]] = $ar["name"];
					if($ar["wdesc"])
					{
						$this->aFields[$ikey]['prop']['items'][$ar["value"].'_DESCRIPTION'] = $ar["name"].' ('.Loc::getMessage("KDA_EE_PROP_DESCRIPTION").')';
					}
				}
			}
			
			if($arIpropTempFields = self::GetIblockIpropTemplates())
			{
				$this->aFields[$ikey]['iprop_temp'] = array(
					'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_IPROP_TEMPLATES").')' : Loc::getMessage("KDA_EE_IPROP_TEMPLATES")),
					'items' => array()
				);
				foreach($arIpropTempFields as $k=>$v)
				{
					if($offers)
					{
						$k = 'OFFER_'.$k;
					} 
					$this->aFields[$ikey]['iprop_temp']['items'][$k] = $v;
				}
			}
			
			if($arCatalogFields = self::GetCatalogFields($IBLOCK_ID, $offers))
			{
				$this->aFields[$ikey]['catalog'] = array(
					'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_CATALOG").')' : Loc::getMessage("KDA_EE_GROUP_CATALOG")),
					'items' => array()
				);
				foreach($arCatalogFields as $ar)
				{
					if($offers) $ar["value"] = 'OFFER_'.$ar["value"];
					$this->aFields[$ikey]['catalog']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			if(!$offers)
			{
				foreach(self::GetIblockSectionElementFields() as $k=>$ar)
				{
					$this->aFields[$ikey]['element']['items'][$k] = $ar["name"];
				}

				$this->aFields[$ikey]['section'] = array(
					'title' => Loc::getMessage("KDA_EE_GROUP_SECTION_PARENT"),
					'items' => array()
				);
				foreach($this->GetIblockSectionFields('', $IBLOCK_ID) as $k=>$ar)
				{
					$this->aFields[$ikey]['section']['items'][$k] = $ar["name"];
				}				
				
				for($i=1; $i<$this->sectionLevels+1; $i++)
				{
					$this->aFields[$ikey]['section'.$i] = array(
						'title' => sprintf(Loc::getMessage("KDA_EE_GROUP_SECTION_LEVEL"), $i),
						'items' => array()
					);
					foreach($this->GetIblockSectionFields($i, $IBLOCK_ID) as $k=>$ar)
					{
						$this->aFields[$ikey]['section'.$i]['items'][$k] = $ar["name"];
					}
				}
			}
			
			if($arDiscountFields = self::GetCatalogDiscountFields($IBLOCK_ID))
			{
				$this->aFields[$ikey]['catalog_discount'] = array(
					'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_CATALOG_DISCOUNT").')' : Loc::getMessage("KDA_EE_GROUP_CATALOG_DISCOUNT")),
					'items' => array()
				);
				foreach($arDiscountFields as $ar)
				{
					if($offers) $ar["value"] = 'OFFER_'.$ar["value"];
					$this->aFields[$ikey]['catalog_discount']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			if(!$offers && ($arCatalogSetFields = self::GetCatalogSetFields($IBLOCK_ID)))
			{
				$this->aFields[$ikey]['catalog_set'] = array(
					'title' => Loc::getMessage("KDA_EE_GROUP_CATALOG_SET"),
					'items' => array()
				);
				foreach($arCatalogSetFields as $ar)
				{
					$this->aFields[$ikey]['catalog_set']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			if(!$offers && ($arCatalogSetFields = self::GetCatalogSet2Fields($IBLOCK_ID)))
			{
				$this->aFields[$ikey]['catalog_set2'] = array(
					'title' => Loc::getMessage("KDA_EE_GROUP_CATALOG_SET2"),
					'items' => array()
				);
				foreach($arCatalogSetFields as $ar)
				{
					$this->aFields[$ikey]['catalog_set2']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			if($arOrderFields = self::GetSaleOrderFields($IBLOCK_ID))
			{
				$this->aFields[$ikey]['sale_order'] = array(
					'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_SALE_ORDER").')' : Loc::getMessage("KDA_EE_GROUP_SALE_ORDER")),
					'items' => array()
				);
				foreach($arOrderFields as $ar)
				{
					if($offers) $ar["value"] = 'OFFER_'.$ar["value"];
					$this->aFields[$ikey]['sale_order']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			$customFields = self::GetCustomFields($IBLOCK_ID);
			if (count($customFields) > 0)
			{
				$this->aFields[$ikey]['custom'] = array(
					'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_CUSTOM").')' : Loc::getMessage("KDA_EE_GROUP_CUSTOM")),
					'items' => array()
				);

				foreach($customFields as $customField)
				{
					$code = 'CUSTOM_'.$customField['code'];
					if($offers) $code = 'OFFER_'.$code;
					$label = $customField['label'];
					$this->aFields[$ikey]['custom']['items'][$code] = $label;
				}
			}
		}
	
		return $this->aFields[$ikey];
	}
	
	public static function GetCustomFields($iblockId)
	{
		$fields = array();
		if(!class_exists('\Bitrix\Main\Event')) return $fields;

		/*Event Example
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler("kda.exportexcel", "onGetCustomFields", "onGetCustomFieldsHandler"); 
		function onGetCustomFieldsHandler($event) {
			$IBLOCK_ID = $event->getParameters()['iblockId'];
			$arFields = array(
				array(
					'label'=>'Поле 1', 
					'code'=>'field1', 
					'callback'=>'UFCallback1'
				),
			);
			return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, array('fields'=>$arFields));
		}
		function UFCallback1($ID)
		{
			return 'value';
		}
		//Event Example*/

		$event = new \Bitrix\Main\Event(
			\CKDAExportUtils::GetModuleId(),
			'onGetCustomFields',
			compact('iblockId')
		);
		$event->send();
		
		foreach($event->getResults() as $result)
		{
			if($result->getType()===\Bitrix\Main\EventResult::SUCCESS)
			{
				$eParams = $result->getParameters();
				if(isset($eParams['fields']) && is_array($eParams['fields']) && count($eParams['fields']) > 0) 
				{
					foreach($eParams['fields'] as $field)
					{
						foreach(array('label', 'code', 'callback') as $key)
						{
							if(!array_key_exists($key, $field))
							{
								continue 2;
							}
						}

						$code = $field['code'];
						$label = $field['label'];
						$callback = $field['callback'];
						if(!is_callable($callback)) continue;
						$fields[$code] = compact('label', 'code', 'callback');
					}
				}
			}
		}

		return $fields;
	}

	
	public function GetExtraFields()
	{
		$aFields = array();
		$aFields['content_table'] = array(
			'title' => Loc::getMessage("KDA_EE_GROUP_CONTENT_TABLE"),
			'items' => array()
		);
		foreach(self::GetContentTableFields() as $k=>$ar)
		{
			$aFields['content_table']['items'][$k] = $ar["name"];
		}
	
		return $aFields;
	}
	
	public function GetSortableFields($IBLOCK_ID)
	{
		if(!$this->aSortableFields) $this->aSortableFields = array();
		
		if(!$this->aSortableFields[$IBLOCK_ID])
		{
			foreach(self::GetIblockElementFields() as $k=>$ar)
			{
				if($ar["sortable"]=='Y') $this->aSortableFields[$IBLOCK_ID][] = $k;
			}
			
			if($arPropFields = self::GetIblockProperties($IBLOCK_ID))
			{
				foreach($arPropFields as $ar)
				{
					if($ar["sortable"]=='Y') $this->aSortableFields[$IBLOCK_ID][] = $ar["value"];
				}
			}

			if($arCatalogFields = self::GetCatalogFields($IBLOCK_ID))
			{
				foreach($arCatalogFields as $ar)
				{
					if($ar["sortable"]=='Y') $this->aSortableFields[$IBLOCK_ID][] = $ar["value"];
				}
			}
			
			if($arSectionFields = self::GetIblockSectionFields(""))
			{
				foreach($arSectionFields as $k=>$ar)
				{
					if($ar["sortable"]=='Y') $this->aSortableFields[$IBLOCK_ID][] = $k;
				}
			}
			
			if($this->isSku && ($OFFERS_IBLOCK_ID = CKDAExportUtils::GetOfferIblock($IBLOCK_ID)))
			{
				$arOfferFields = self::GetSortableFields($OFFERS_IBLOCK_ID);
				foreach($arOfferFields as $field)
				{
					$this->aSortableFields[$IBLOCK_ID][] = 'OFFER_'.$field;
				}
			}
		}
	
		return $this->aSortableFields[$IBLOCK_ID];
	}
	
	public function GetHlblSortableFields($HLBL_ID)
	{
		if(!$this->aHlblSortableFields) $this->aHlblSortableFields = array();
		
		if(!$this->aHlblSortableFields[$HLBL_ID])
		{
			foreach($this->GetHigloadBlockFields($HLBL_ID) as $k=>$ar)
			{
				if($ar["sortable"]=='Y') $this->aHlblSortableFields[$HLBL_ID][] = $k;
			}
		}
	
		return $this->aHlblSortableFields[$HLBL_ID];
	}
	
	public function GetFiltrableFields($IBLOCK_ID, $offers = false)
	{
		if(!$this->aFiltrableFields) $this->aFiltrableFields = array();
		
		if(!$this->aFiltrableFields[$IBLOCK_ID])
		{
			if(!$offers)
			{
				$this->aFiltrableFields[$IBLOCK_ID]['section'] = array(
					'title' => Loc::getMessage("KDA_EE_GROUP_SECTION_PARENT"),
					'items' => array(
						'IE_IBLOCK_SECTION' => array(
							"name" => Loc::getMessage("KDA_EE_ELEMENT_SECTION"),
							"type" => "section",
							"filtrable" => "Y"
						)
					)
				);
			}
			
			$this->aFiltrableFields[$IBLOCK_ID]['element'] = array(
				//'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER") : Loc::getMessage("KDA_EE_GROUP_ELEMENT")),
				'title' => Loc::getMessage("KDA_EE_GROUP_ELEMENT"),
				'items' => array()
			);
			foreach(self::GetIblockElementFields() as $k=>$ar)
			{
				if($ar["filtrable"]!='Y') continue;
				//if($offers) $k = 'OFFER_'.$k;
				$this->aFiltrableFields[$IBLOCK_ID]['element']['items'][$k] = $ar;
			}
			
			if($arPropFields = self::GetIblockProperties($IBLOCK_ID))
			{
				$this->aFiltrableFields[$IBLOCK_ID]['prop'] = array(
					//'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_PROP").')' : Loc::getMessage("KDA_EE_GROUP_PROP")),
					'title' => Loc::getMessage("KDA_EE_GROUP_PROP"),
					'items' => array()
				);
				foreach($arPropFields as $ar)
				{
					if($ar["filtrable"]!='Y') continue;
					//if($offers) $ar["value"] = 'OFFER_'.$ar["value"];
					$this->aFiltrableFields[$IBLOCK_ID]['prop']['items'][$ar["value"]] = $ar;
				}
			}
			
			if($arCatalogFields = self::GetCatalogFields($IBLOCK_ID))
			{
				$this->aFiltrableFields[$IBLOCK_ID]['catalog'] = array(
					//'title' => ($offers ? Loc::getMessage("KDA_EE_GROUP_OFFER").' ('.Loc::getMessage("KDA_EE_GROUP_CATALOG").')' : Loc::getMessage("KDA_EE_GROUP_CATALOG")),
					'title' => Loc::getMessage("KDA_EE_GROUP_CATALOG"),
					'items' => array()
				);
				foreach($arCatalogFields as $ar)
				{
					if($ar["filtrable"]!='Y') continue;
					//if($offers) $ar["value"] = 'OFFER_'.$ar["value"];
					$this->aFiltrableFields[$IBLOCK_ID]['catalog']['items'][$ar["value"]] = $ar;
				}
			}
		}
	
		return $this->aFiltrableFields[$IBLOCK_ID];
	}
	
	public function GetSectionFiltrableFields($IBLOCK_ID)
	{
		if(!$this->aSectionFiltrableFields) $this->aSectionFiltrableFields = array();
		
		if(!$this->aSectionFiltrableFields[$IBLOCK_ID])
		{
			
			$this->aSectionFiltrableFields[$IBLOCK_ID]['section'] = array(
				'title' => Loc::getMessage("KDA_EE_GROUP_SECTION"),
				'items' => array(
					'ISECT_SECTION_ID' => array(
						"name" => Loc::getMessage("KDA_EE_ISECT_FI_PARENT_SECTION"),
						"type" => "section",
						"filtrable" => "Y"
					)
				)
			);
			foreach(self::GetIblockSectionFields('', $IBLOCK_ID) as $k=>$ar)
			{
				if($ar['filtrable']!=='Y') continue;
				$this->aSectionFiltrableFields[$IBLOCK_ID]['section']['items'][$k] = $ar;
			}
			$this->aSectionFiltrableFields[$IBLOCK_ID]['section']['items']['ISECT_ELEMENT_COUNT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_COUNT"),
				"type" => "number",
				"filtrable" => "Y"
			);
			$this->aSectionFiltrableFields[$IBLOCK_ID]['section']['items']['ISECT_ELEMENT_ACTIVE_COUNT'] = array(
				"name" => Loc::getMessage("KDA_EE_ISECT_FI_ELEMENT_ACTIVE_COUNT"),
				"type" => "number",
				"filtrable" => "Y"
			);
		}
		return $this->aSectionFiltrableFields[$IBLOCK_ID];
	}
	
	public function GetHigloadFiltrableFields($HIGHLOADBLOCK_ID)
	{
		if(!$this->aHlFiltrableFields) $this->aHlFiltrableFields = array();
		
		if(!$this->aHlFiltrableFields[$HIGHLOADBLOCK_ID])
		{
			$this->aHlFiltrableFields[$HIGHLOADBLOCK_ID]['hlbl'] = array(
				'title' => Loc::getMessage("KDA_EE_GROUP_HIGHLOAD"),
				'items' => array()
			);
			foreach(self::GetHigloadBlockFields($HIGHLOADBLOCK_ID) as $k=>$ar)
			{
				if($ar['filtrable']!=='Y') continue;
				$this->aHlFiltrableFields[$HIGHLOADBLOCK_ID]['hlbl']['items'][$k] = $ar;
			}
		}
		return $this->aHlFiltrableFields[$HIGHLOADBLOCK_ID];
	}
	
	public function ShowSelectFilterFields($IBLOCK_ID, $fname, $offers = false)
	{
		$arGroups = $this->GetFiltrableFields($IBLOCK_ID, $offers);
		?><select name="<?echo $fname;?>"><option value=""><?echo Loc::getMessage("KDA_EE_CHOOSE_FIELD");?></option><option value="GROUP"><?echo Loc::getMessage("KDA_EE_FI_GROUP_COND");?></option><?
		foreach($arGroups as $k2=>$v2)
		{
			$groupOption = '';
			foreach($v2['items'] as $k=>$v)
			{
				if(!isset($v['filtrable']) || $v['filtrable']!='Y') continue;
				$groupOption .= '<option value="'.$k.'" '.($k==$value ? 'selected' : '').' data-type="'.ToUpper(htmlspecialcharsbx($v['type'])).'">'.htmlspecialcharsbx($v['name']).'</option>';
			}
			if(strlen($groupOption) > 0) echo '<optgroup label="'.$v2['title'].'">'.$groupOption.'</optgroup>';
		}
		?></select><?
	}
	
	public function GetRelatedFields($IBLOCK_ID, $value, $default='')
	{
		if(strlen($value)===0 && strlen($default) > 0) $value = $default;
		if(!$this->aRelatedFields) $this->aRelatedFields = array();
		
		if(!$this->aRelatedFields[$IBLOCK_ID])
		{
			$this->aRelatedFields[$IBLOCK_ID]['element'] = array(
				'title' => Loc::getMessage("KDA_EE_GROUP_ELEMENT"),
				'items' => array()
			);
			foreach(self::GetIblockElementFields() as $k=>$ar)
			{
				if($offers) $k = 'OFFER_'.$k;
				$this->aRelatedFields[$IBLOCK_ID]['element']['items'][$k] = $ar["name"];
			}
			
			if($arPropFields = self::GetIblockProperties($IBLOCK_ID))
			{
				$this->aRelatedFields[$IBLOCK_ID]['prop'] = array(
					'title' => Loc::getMessage("KDA_EE_GROUP_PROP"),
					'items' => array()
				);
				foreach($arPropFields as $ar)
				{
					$this->aRelatedFields[$IBLOCK_ID]['prop']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			if($arPropFields = self::GetCatalogFields($IBLOCK_ID))
			{
				$this->aRelatedFields[$IBLOCK_ID]['catalog'] = array(
					'title' => Loc::getMessage("KDA_EE_GROUP_CATALOG"),
					'items' => array()
				);
				foreach($arPropFields as $ar)
				{
					if(!preg_match('/^(ICAT_PRICE\d+_PRICE|ICAT_PURCHASING_PRICE)$/', $ar["value"])) continue;
					$this->aRelatedFields[$IBLOCK_ID]['catalog']['items'][$ar["value"]] = $ar["name"];
				}
			}
		}
		
		$options = '';
		$arGroups = $this->aRelatedFields[$IBLOCK_ID];
		foreach($arGroups as $k2=>$v2)
		{
			$options .= '<optgroup label="'.htmlspecialcharsbx($v2['title']).'">';
			foreach($v2['items'] as $k=>$v)
			{
				$options .= '<option value="'.htmlspecialcharsbx($k).'" '.($k==$value ? 'selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
			}
			$options .= '</optgroup>';
		}
	
		return $options;
	}
	
	public function GetRelatedSectionFields($IBLOCK_ID, $value)
	{
		if(!$this->aRelatedSectionFields) $this->aRelatedSectionFields = array();
		
		if(!$this->aRelatedSectionFields[$IBLOCK_ID])
		{
			$this->aRelatedSectionFields[$IBLOCK_ID] = array(
				'NAME' => Loc::getMessage("KDA_EE_ISECT_FI_NAME"),
				'ID'=> Loc::getMessage("KDA_EE_ISECT_FI_ID"),
				'CODE' => Loc::getMessage("KDA_EE_ISECT_FI_CODE"),
				'XML_ID' => Loc::getMessage("KDA_EE_ISECT_FI_XML_ID"),
				'SORT' => Loc::getMessage("KDA_EE_ISECT_FI_SORT"),
				'PICTURE' => Loc::getMessage("KDA_EE_ISECT_FI_PICTURE"),
				'DETAIL_PICTURE' => Loc::getMessage("KDA_EE_ISECT_FI_DETAIL_PICTURE"),
				'DESCRIPTION' => Loc::getMessage("KDA_EE_ISECT_FI_DESCRIPTION"),
				'SECTION_PAGE_URL' => Loc::getMessage("KDA_EE_ISECT_FI_SECTION_PAGE_URL")
			);
			
			$dbRes = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$IBLOCK_ID.'_SECTION', 'LANG' => LANGUAGE_ID));
			$arProps = array();
			while($arr = $dbRes->Fetch())
			{
				$name = ($arr['EDIT_FORM_LABEL'] ? $arr['EDIT_FORM_LABEL'].' ('.$arr['FIELD_NAME'].')' : $arr['FIELD_NAME']);
				$this->aRelatedSectionFields[$IBLOCK_ID][$arr['FIELD_NAME']] = $name;
			}
		}
		
		$options = '';
		foreach($this->aRelatedSectionFields[$IBLOCK_ID] as $k=>$v)
		{
			$options .= '<option value="'.htmlspecialcharsbx($k).'" '.($k==$value ? 'selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
		}
		
		return $options;
	}
	
	public function ShowSelectFields($IBLOCK_ID, $fname, $value="", $arParams=array())
	{
		$arGroups = $this->GetFields($IBLOCK_ID, false, $arParams);
		$arGroupsOffers = array();
		if($this->isSku)
		{
			$arOffer = CKDAExportUtils::GetOfferIblock($IBLOCK_ID, true);
			if($arOffer) $arGroupsOffers = $this->GetFields($arOffer['OFFERS_IBLOCK_ID'], $arOffer['OFFERS_PROPERTY_ID']);
		}
		$arGroupsExtra = $this->GetExtraFields();
		if($arParams['MULTIPLE']){?><select name="<?echo $fname;?>" multiple><?}
		else{?><select name="<?echo $fname;?>"><option value=""><?echo Loc::getMessage("KDA_EE_CHOOSE_FIELD");?></option><?}
		foreach($arGroups as $k2=>$v2)
		{
			?><optgroup label="<?echo $v2['title']?>"><?
			foreach($v2['items'] as $k=>$v)
			{
				?><option value="<?echo $k; ?>" <?if($k==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($v); ?></option><?
			}
			?></optgroup><?
		}
		foreach($arGroupsOffers as $k2=>$v2)
		{
			?><optgroup label="<?echo $v2['title']?>"><?
			foreach($v2['items'] as $k=>$v)
			{
				?><option value="<?echo $k; ?>" <?if($k==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($v); ?></option><?
			}
			?></optgroup><?
		}
		foreach($arGroupsExtra as $k2=>$v2)
		{
			?><optgroup label="<?echo $v2['title']?>"><?
			foreach($v2['items'] as $k=>$v)
			{
				?><option value="<?echo $k; ?>" <?if($k==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($v); ?></option><?
			}
			?></optgroup><?
		}
		?></select><?
	}

	public function GetSettingsFields($IBLOCK_ID)
	{
		$arGroups = $this->GetFields($IBLOCK_ID);
		$arGroupsOffers = array();
		if($this->isSku)
		{
			$arOffer = CKDAExportUtils::GetOfferIblock($IBLOCK_ID, true);
			if($arOffer) $arGroupsOffers = $this->GetFields($arOffer['OFFERS_IBLOCK_ID'], $arOffer['OFFERS_PROPERTY_ID']);
		}
		$arGroupsExtra = $this->GetExtraFields();
		
		$arFields = array();
		foreach($arGroups as $k2=>$v2)
		{
			$key = ToUpper($k2);
			$arFields[$key] = array(
				'TITLE' => $v2['title'],
				'FIELDS' => array()
			); 
			foreach($v2['items'] as $k=>$v)
			{
				$arFields[$key]['FIELDS'][$k] = $v;
			}
		}
		foreach($arGroupsOffers as $k2=>$v2)
		{
			$key = 'OFFERS_'.ToUpper($k2);
			$arFields[$key] = array(
				'TITLE' => $v2['title'],
				'FIELDS' => array()
			); 
			foreach($v2['items'] as $k=>$v)
			{
				$arFields[$key]['FIELDS'][$k] = $v;
			}
		}
		foreach($arGroupsExtra as $k2=>$v2)
		{
			$key = ToUpper($k2);
			$arFields[$key] = array(
				'TITLE' => $v2['title'],
				'FIELDS' => array()
			); 
			foreach($v2['items'] as $k=>$v)
			{
				$arFields[$key]['FIELDS'][$k] = $v;
			}
		}
		return $arFields;
	}
	
	public function GetHighloadBlocks()
	{
		if(!Loader::includeModule('highloadblock')) return array();
		$arHighloadBlocks = array();
		$dbRes = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("order" => array('NAME' => 'ASC')));
		while($arr = $dbRes->Fetch())
		{
			$arHighloadBlocks[] = $arr;
		}
		return $arHighloadBlocks;
	}
	
	public function ShowSelectFieldsHighload($HIGHLOADBLOCK_ID, $fname, $value="")
	{
		$arFields = $this->GetHigloadBlockFields($HIGHLOADBLOCK_ID);
		?><select name="<?echo $fname;?>"><option value=""><?echo Loc::getMessage("KDA_EE_CHOOSE_FIELD");?></option><?
		?><optgroup label="<?echo Loc::getMessage("KDA_EE_HIGHLOAD_FIELDS")?>"><?
		foreach($arFields as $k=>$v)
		{
			?><option value="<?echo $k; ?>" <?if($k==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($v['NAME_LANG']); ?></option><?
		}
		?></optgroup><?
		?></select><?
	}
	
	public function GetHigloadBlockFields($HIGHLOADBLOCK_ID)
	{
		if(!isset($this->hlblFields[$HIGHLOADBLOCK_ID]))
		{
			$dbRes = CUserTypeEntity::GetList(array('SORT'=>'ASC', 'ID'=>'ASC'), array('ENTITY_ID'=>'HLBLOCK_'.$HIGHLOADBLOCK_ID, 'LANG'=>LANGUAGE_ID));
			$arHLFields = array('ID' => array('FIELD_NAME'=>'ID', 'NAME_LANG'=>'ID', 'name'=>'ID', 'sortable'=>'Y', 'type'=>'id', 'filtrable'=>'Y'));
			while($arHLField = $dbRes->Fetch())
			{
				$type = 'string';
				if(in_array($arHLField['USER_TYPE_ID'], array('integer', 'double'))) $type = 'number';
				elseif(in_array($arHLField['USER_TYPE_ID'], array('datetime', 'date'))) $type = 'date';
				elseif(in_array($arHLField['USER_TYPE_ID'], array('hlblock', 'boolean', 'enumeration', 'iblock_section', 'iblock_element'))) $type = 'list';
					
				if($arHLField['MULTIPLE']!='Y' && in_array($arHLField['USER_TYPE_ID'], array('string', 'hlblock', 'integer', 'double', 'datetime', 'date', 'boolean', 'enumeration', 'iblock_section', 'iblock_element')))
				{
					$arHLField['UID'] = 'Y';
					$arHLField['sortable'] = 'Y';
				}
				$arHLField['type'] = $type;
				$arHLField['filtrable'] = 'Y';
				$arHLField['NAME_LANG'] = $arHLField['name'] = ($arHLField['EDIT_FORM_LABEL'] ? $arHLField['EDIT_FORM_LABEL'] : $arHLField['FIELD_NAME']);
				$arHLFields[$arHLField['FIELD_NAME']] = $arHLField;
			}
			$this->hlblFields[$HIGHLOADBLOCK_ID] = $arHLFields;
		}
		return $this->hlblFields[$HIGHLOADBLOCK_ID];
	}
	
	public function GetSettingsFieldsHighload($HLBL_ID)
	{
		$arHlblFields = $this->GetHigloadBlockFields($HLBL_ID);
		foreach($arHlblFields as $k=>$v)
		{
			$arHlblFields[$k] = $v['NAME_LANG'];
		}
		
		$arFields = array();
		$arFields['HLBL_FIELDS'] = array(
			'TITLE' => Loc::getMessage("KDA_EE_HIGHLOAD_FIELDS"),
			'FIELDS' => $arHlblFields
		);
		return $arFields;
	}
	
	public function GetIblocks()
	{
		$arIblocks = array();
		$dbRes = \CIBlock::GetList(array('NAME'=>'ASC', 'MIN_PERMISSION'=>'R'), array());
		while($arr = $dbRes->Fetch())
		{
			$arIblocks[$arr['IBLOCK_TYPE_ID']][] = array(
				'ID' => $arr['ID'],
				'NAME' => $arr['NAME']
			);
		}
		
		$dbRes = \CIBlockType::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array("LANGUAGE_ID" => LANG));
		while($arr = $dbRes->Fetch())
		{
			$arr = array(
				'ID' => $arr['ID'],
				'NAME' => $arr['NAME']
			);
			$arr['IBLOCKS'] = (isset($arIblocks[$arr['ID']]) && is_array($arIblocks[$arr['ID']]) ? $arIblocks[$arr['ID']] : array());
			if(array_key_exists('IBLOCKS', $arr['IBLOCKS'])) continue;
			$arIblocks[$arr['ID']] = $arr;
		}
		
		/*Added Types without lang*/
		$arNFTypes = array();
		foreach($arIblocks as $k=>$v)
		{
			if(!array_key_exists('ID', $v)) $arNFTypes[] = $k;
		}
		if(!empty($arNFTypes))
		{
			$dbRes = \CIBlockType::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array());
			while($arr = $dbRes->Fetch())
			{
				if(in_array($arr['ID'], $arNFTypes))
				{
					$arr = array(
						'ID' => $arr['ID'],
						'NAME' => $arr['NAME']
					);
					$arr['IBLOCKS'] = $arIblocks[$arr['ID']];
					$arIblocks[$arr['ID']] = $arr;
					$arNFTypes = array_diff($arNFTypes, array($arr['ID']));
				}
			}
		}
		/*/Added Types without lang*/
		return $arIblocks;
	}
}
?>