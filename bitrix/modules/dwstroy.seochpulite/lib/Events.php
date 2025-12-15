<?

namespace Dwstroy\SeoChpuLite;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Context;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class Events{

    public static $data = [];

    public static function OnPageStart(){

    }

    public static function OnBeforeProlog(){

    }

    public static function redirect(){
        if( Helper::isAdminPage() ){
            return;
        }
        Helper::getInstance()->redirect();
    }

    public static function OnEpilog(){
        if( Helper::isAdminPage() ){
            return;
        }
        Helper::returnUrl();
        global $APPLICATION;
        $urls = Helper::getInstance()->getUrl();

        if( Loader::includeModule(SEO_CHPU_LITE) ){
            $iblocks_id = Helper::getInstance()->getChpuIblocks();
            if( $iblocks_id ){

                if( Loader::includeModule( 'iblock' ) ){
                    foreach($iblocks_id as $iblock_id){
                        $resElem = \CIBlockElement::GetList(
                            [
                                'SORT' => 'ASC',
                                'ID'   => 'ASC'
                            ], [
                            'IBLOCK_ID'          => $iblock_id,
                            'ACTIVE'             => 'Y',
                            'GLOBAL_ACTIVE'             => 'Y',
                            'DATE_ACTIVE'             => 'Y',
                            'PROPERTY_NEW_URL'  => $urls,
                        ], false
                        );
                        while( $ob = $resElem->GetNextElement() ){
                            $dataElem = $ob->GetFields();
                            $dataElem[ 'PROPERTIES' ] = $ob->GetProperties();
                            if( !in_array($dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ], $urls) ){
                                continue;
                            }
                            $dataElem[ 'CLEARED_PROPERTIES' ] = [];

                            foreach($dataElem['PROPERTIES'] as $propertyCode => $property){
                                if (isset($property["VALUE"]["TEXT"])){
                                    $dataElem[ 'CLEARED_PROPERTIES' ][$propertyCode] = $property["VALUE"]["TEXT"];
                                    $dataElem[ 'CLEARED_PROPERTIES' ]['~'.$propertyCode] = $property["~VALUE"]["TEXT"];
                                }elseif ( !empty($property["VALUE"]) ){
                                    $dataElem[ 'CLEARED_PROPERTIES' ][$propertyCode] = $property["VALUE"];
                                    $dataElem[ 'CLEARED_PROPERTIES' ]['~'.$propertyCode] = $property["~VALUE"];
                                }
                            }

                            self::$data = $dataElem;

                            $title = self::getValue($dataElem[ 'PROPERTIES' ], 'SEO_TITLE');

                            if( !empty($title) ){
                                $APPLICATION->SetPageProperty('title', $title);
                            }

                            $h1 = self::getValue($dataElem[ 'PROPERTIES' ], 'SEO_H1');

                            $chain = [];
                            if( $dataElem[ 'PROPERTIES' ]['PARENT_BREADCRUMB_ITEM']['VALUE'] ){
                                Helper::getChain($dataElem[ 'PROPERTIES' ]['PARENT_BREADCRUMB_ITEM']['VALUE'], $iblocks_id, $chain);
                            }
                            if( !empty($h1) ){
                                if( !empty($dataElem[ 'PROPERTIES' ]['ADD_TO_CHAIN']['VALUE']) ){
                                    $chain[] = [
                                        'NAME' => $h1,
                                        'URL' => $dataElem['PROPERTIES']['NEW_URL']['VALUE']
                                    ];

                                }
                                $APPLICATION->SetTitle($h1, false);

                            }

                            foreach($chain as $c){
                                $APPLICATION->AddChainItem($c['NAME'], $c['URL']);
                            }


                            $keywords = self::getValue($dataElem[ 'PROPERTIES' ], 'SEO_KEYWORDS');
                            if( !empty($keywords) ){
                                $APPLICATION->SetPageProperty('keywords', $keywords);
                            }

                            $description = self::getValue($dataElem[ 'PROPERTIES' ], 'SEO_DESCRIPTION');
                            if( !empty($description) ){
                                $APPLICATION->SetPageProperty('description', $description);
                            }

                            $isFirstPage = false;

                            if( $APPLICATION->GetPageProperty('page') == 1 ){
                                $isFirstPage = true;
                            }elseif( !isset($_SERVER[ "OLD_GET" ]['PAGEN_1']) || $_SERVER[ "OLD_GET" ]['PAGEN_1'] == 1 ){
                                $isFirstPage = true;
                            }


                            if( !empty($dataElem['PREVIEW_TEXT']) && $isFirstPage ){
                                $APPLICATION->__view['SEO_DESCRIPTION_TOP'] = [];
                                $APPLICATION->__view['top_desc'] = [];
                                $APPLICATION->AddViewContent('SEO_DESCRIPTION_TOP', $dataElem['PREVIEW_TEXT']);
                                $APPLICATION->AddViewContent('top_desc', $dataElem['PREVIEW_TEXT']);
                            }else{
                                $APPLICATION->__view['SEO_DESCRIPTION_TOP'] = [];
                                $APPLICATION->__view['top_desc'] = [];
                                $APPLICATION->AddViewContent('SEO_DESCRIPTION_TOP', '');
                                $APPLICATION->AddViewContent('top_desc', '');
                            }
                            if( !empty($dataElem['DETAIL_TEXT']) && $isFirstPage ){
                                $APPLICATION->__view['SEO_DESCRIPTION_BOTTOM'] = [];
                                $APPLICATION->__view['bottom_desc'] = [];
                                $APPLICATION->AddViewContent('SEO_DESCRIPTION_BOTTOM', $dataElem['DETAIL_TEXT']);
                                $APPLICATION->AddViewContent('bottom_desc', $dataElem['DETAIL_TEXT']);
                            }else{
                                $APPLICATION->__view['SEO_DESCRIPTION_BOTTOM'] = [];
                                $APPLICATION->__view['bottom_desc'] = [];
                                $APPLICATION->AddViewContent('SEO_DESCRIPTION_BOTTOM', '');
                                $APPLICATION->AddViewContent('bottom_desc', '');
                            }
                            if( !empty($dataElem[ 'PROPERTIES' ]['ROBOTS']['VALUE']) ){
                                $APPLICATION->SetPageProperty('robots', str_replace('_', ', ', $dataElem[ 'PROPERTIES' ]['ROBOTS']['VALUE_XML_ID']));
                            }
                            break;
                        }
                    }
                }
            }
        }
    }

    public static function getValue($properties, $key){
        $value = null;
        if( !empty($properties[$key]['VALUE']) ){
            if( isset($properties[$key]['VALUE']['TEXT']) && !empty($properties[$key]['VALUE']['TEXT']) ){
                $value = $properties[$key]['VALUE']['TEXT'];
                if( $properties[$key]['VALUE']['TYPE'] == 'HTML' ){
                    $value = html_entity_decode($value);
                }
            }elseif(isset($properties[$key]['VALUE']['HTML']) && !empty($properties[$key]['VALUE']['HTML'])){
                $value = $properties[$key]['VALUE']['HTML'];
                if( $properties[$key]['VALUE']['TYPE'] == 'HTML' ){
                    $value = html_entity_decode($value);
                }

            }
        }
        return $value;
    }


    public static function OnEndBufferContent(&$content){
        if( Helper::isAdminPage() ){
            $curPage = Helper::getCurPage();
            if( array_key_exists("component_name", $_GET) && $curPage === '/bitrix/admin/component_props.php' ){
                $componentName = $_GET["component_name"];
                if(in_array($componentName, ['bitrix:catalog', 'bitrix:catalog.smart.filter', 'bitrix:news', 'dresscode:catalog', 'dresscode:catalog.smart.filter', 'dresscode:news']) && preg_match("/new\sCompDialogManager\((.*?)\)\;/usmi", $content, $matches) ){
                    $json_data = json_decode($matches[1], true);
                    $templateId = $_GET["template_id"];
                    $json_data['data']['templates'] = Helper::GetRealTemplateLists($componentName, $templateId);
                    $content = str_replace($matches[1], Json::encode($json_data), $content);
                }
            }
            return;
        }
        $content = Helper::makeText( self::$data, $content );
    }

    public static function OnAfterIblockElementAddUpdate($arFields){
        if( $arFields['ID'] ){
            $res = IblockTable::getList(
                [
                    'filter' => [
                        "IBLOCK_TYPE_ID"   => SEO_CHPU_LITE_NO_DOT,
                        'ID' => $arFields['IBLOCK_ID']
                    ]
                ]
            );
            if( $data = $res->fetch() ){
                $resElem = \CIBlockElement::GetList(
                    [
                        'ID' => 'ASC'
                    ],
                    [
                        'ID' => $arFields['ID'],
                        'IBLOCK_ID' => $arFields['IBLOCK_ID']
                    ]
                );
                if( $ob = $resElem->GetNextElement() ){
                    $arData = $ob->GetFields();
                    $arData['PROPERTIES'] = $ob->GetProperties();

                    if( !empty($arData['PROPERTIES']['OLD_URL']['VALUE']) ){
                        $decodedOldUrl = urldecode($arData['PROPERTIES']['OLD_URL']['VALUE']);
                        if( strcmp($arData['PROPERTIES']['OLD_URL']['VALUE'], $decodedOldUrl) != 0){
                            \CIBlockElement::SetPropertyValuesEx($arData['ID'], $arData['IBLOCK_ID'], ['OLD_URL' => $decodedOldUrl]);
                        }

                        if( $arData['CODE'] !== $arData['PROPERTIES']['NEW_URL']['VALUE'] ){
                            $element = new \CIBlockElement();
                            $element->Update($arData['ID'], ['CODE' => $arData['PROPERTIES']['NEW_URL']['VALUE']]);
                        }

                        if( preg_match("/(.*?\/)filter\/(.*?)(\/apply\/.*)/usmi", urldecode($arData['PROPERTIES']['OLD_URL']['VALUE']), $matches) ){
                            $sId = 0;
                            $arPath = explode('/', $matches[1]);
                            $sectionCode = $arPath[count($arPath) - 2];
                            $sections = [];
                            if (is_numeric($sectionCode)) {
                                $res = \CIBlockSection::GetList(
                                    [
                                        'ID' => 'ASC'
                                    ],
                                    [
                                        'ID' => $sectionCode,
                                    ]
                                );
                                if (!$res->SelectedRowsCount()) {
                                    $res = \CIBlockSection::GetList(
                                        [
                                            'ID' => 'ASC'
                                        ],
                                        [
                                            'CODE' => $sectionCode,
                                        ]
                                    );
                                }

                                while ($ob = $res->GetNextElement()) {
                                    $sections[] = $ob->GetFields();
                                }

                            } else {
                                $res = \CIBlockSection::GetList(
                                    [
                                        'ID' => 'ASC'
                                    ],
                                    [
                                        'CODE' => $sectionCode,
                                    ]
                                );
                                while ($ob = $res->GetNextElement()) {
                                    $sections[] = $ob->GetFields();
                                }
                            }
                            if (!empty($sections)) {
                                foreach ($sections as $section) {
                                    if ($section['SECTION_PAGE_URL'] == $matches[1]) {
                                        $sId = $section['ID'];
                                        break;
                                    }
                                }
                            }
                        }

                        if( $sId ){
                            \CIBlockElement::SetPropertyValuesEx($arData['ID'], $arData['IBLOCK_ID'], ['SECTION_ID' => $sId]);
                        }
                    }



                    if(false &&  !empty($arData['PROPERTIES']['GENERATE_VARIANTS']['VALUE']) && !empty($arData['PROPERTIES']['OLD_URL']['VALUE']) ){
                        if( preg_match("/(.*?\/filter\/)(.*?)(\/apply\/.*)/usmi", $arData['PROPERTIES']['OLD_URL']['VALUE'], $matches) ){
                            $otherUrls = (!empty($arData['PROPERTIES']['OTHER_URLS']['VALUE'])?$arData['PROPERTIES']['OTHER_URLS']['VALUE']:[]);
                            $tmp = explode('/', $matches[2]);
                            $tmp_back = $tmp;
                            foreach($tmp as $ti => $t){
                                if( mb_strpos($t, '-or-') !== false ){
                                    $tt = explode('-is-', $t);
                                    $ttt = explode('-or-', $tt[1]);
                                    $tttt = Helper::getArr_variants($ttt, 100, false, '-or-');
                                    $cntt = count($ttt);
                                    unset($tmp_back[$ti]);
                                    foreach($tttt as $t_){
                                        if( count(explode('-or-', $t_)) === $cntt ){
                                            $tmp_back[] = $tt[0].'-is-'.$t_;
                                        }
                                    }
                                }
                            }
                            $cnt = count($tmp);
                            $new = Helper::getArr_variants($tmp_back, 100, false, '/');
                            foreach($new as $n){
                                $tmpn = explode('/', $n);
                                $cnt_ = [];
                                foreach($tmpn as $tmpn__){
                                    $tmpnn = explode('-is-', $tmpn__);
                                    $cnt_[$tmpnn[0]] =  $tmpnn[0];
                                }

                                if( count($tmpn) === $cnt && count($cnt_) === $cnt ){
                                    $otherUrls[] = $matches[1].$n.$matches[3];
                                }
                            }
                            foreach($tmp_back as $ti => $t){
                                $tmp_back[$ti] = str_replace('&quot;', '"', urldecode($t));
                            }

                            $new = Helper::getArr_variants($tmp_back, 100, false, '/');
                            foreach($new as $n){
                                $tmpn = explode('/', $n);
                                $cnt_ = [];
                                foreach($tmpn as $tmpn__){
                                    $tmpnn = explode('-is-', $tmpn__);
                                    $cnt_[$tmpnn[0]] =  $tmpnn[0];
                                }

                                if( count($tmpn) === $cnt && count($cnt_) === $cnt ){
                                    $otherUrls[] = $matches[1].$n.$matches[3];
                                }
                            }
                            $value = [];
                            if( !empty($otherUrls) ){
                                $otherUrls = array_unique($otherUrls);
                                foreach($otherUrls as $oldUrl){
                                    if( $oldUrl === $arData['PROPERTIES']['OLD_URL']['VALUE'] ){
                                        continue;
                                    }
                                    $value[] = ['VALUE' => $oldUrl];
                                }
                            }

                            if( empty($value) ){
                                $value = false;
                            }
                            \CIBlockElement::SetPropertyValuesEx($arData['ID'], $arData['IBLOCK_ID'], ['OTHER_URLS' => $value]);
                        }
                    }
                }
            }
        }
    }



    public static function OnEndBufferContentSitemap( &$content ){
        return;
        //old variant
        global $APPLICATION;
        $request = Context::getCurrent()->getRequest();
        if( ($APPLICATION->GetCurPage() == '/bitrix/admin/seo_sitemap_run.php' ) && $request->isPost() && $request->getPost( 'action' ) == 'sitemap_run' && (int) $request->getPost( 'ID' ) ){

            $id = (int) $request->getPost( 'ID' );

            if( $id ){
                $resSitemaps = \Bitrix\Seo\SitemapTable::getList(
                    [
                        'order'  => [
                            'ID' => 'ASC'
                        ],
                        'filter' => [
                            'ID' => $id
                        ],
                        'select' => [
                            '*',
                        ]
                    ]
                );
                if( $dataSitemap = $resSitemaps->fetch() ){
                    $checkEnd = Helper::ckechIfEnd( $content );
                    if( !$checkEnd ){
                        if( preg_match("/BX\.runSitemap\((.*?)\)/usmi", $content, $matches) ){
                            $json = \CUtil::JsObjectToPhp('['.$matches[1].']', true);

                            if( $json[1] >= 60 && $json[1] <=80 && (!isset($json[3]['self_step']) || $json[3]['self_step'] < 69)){
                                if( isset($json[3]['self_step']) ){
                                    $_REQUEST['value'] = $json[3]['self_step'];
                                }
                                $_REQUEST[ 'NS' ] = $json[3];
                                $returnData = Helper::generateChpuForSitemap( $dataSitemap );
                                $PID = intval( $_REQUEST[ 'ID' ] );
                                if( $returnData[ 'v' ] < 70 ){
                                    if( isset( $NS[ 'IBLOCK_MAP' ] ) ){
                                        $_SESSION[ "SEO_PAGE_SITEMAP_" . $returnData[ 'PID' ] ] = $returnData[ 'NS' ][ 'IBLOCK_MAP' ];
                                        unset( $returnData[ 'NS' ][ 'IBLOCK_MAP' ] );
                                    }

                                    $_SESSION[ "SEO_PAGE_SITEMAP_" . $PID.'_VALUE' ] = $returnData[ 'v' ];
                                    $content = preg_replace( "/BX\.runSitemap\((.*?)\)/usmi", 'top.BX.runSitemap(' . $returnData[ 'ID' ] . ', ' . $returnData[ 'v' ] . ', \'' . $returnData[ 'PID' ] . '\', ' . \CUtil::PhpToJsObject( $returnData[ 'NS' ] ) . ');', $content );
                                }else{
                                    unset($_SESSION[ "SEO_PAGE_SITEMAP_" . $PID.'_VALUE' ]);
                                }
                            }
                        }
                    }
                }
            }
        }elseif( mb_strpos($APPLICATION->GetCurPage(), '/bitrix/') === false ){

        }
    }


    public static function SaveOptions(){
        global $APPLICATION;
        $request = Context::getCurrent()->getRequest();
        if( $request->isPost() && ($APPLICATION->GetCurPage() == '/bitrix/admin/seo_sitemap.php' || $APPLICATION->GetCurPage() == '/bitrix/admin/seo_sitemap_edit.php') ){
            $ID = $request->get( 'ID' );
            if( !isset($_POST[SEO_CHPU_LITE_PREFIX.'SITEMAP_IBLOCKS']) ){
                $_POST[SEO_CHPU_LITE_PREFIX.'SITEMAP_IBLOCKS'] = [];
            }
            if( $ID ){
                Option::set(SEO_CHPU_LITE, 'SITEMAP_'.$ID, serialize($_POST[SEO_CHPU_LITE_PREFIX.'SITEMAP_IBLOCKS']));
            }
        }
    }

    public static function OnAdminTabControlBegin( &$form ){
        if ($GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/iblock_edit.php' &&
            array_key_exists('ID', $_REQUEST) && intval($_REQUEST['ID'])>0
        ) {
            global $USER_FIELD_MANAGER, $APPLICATION;
            $ID = intval($_REQUEST['ID']);
            $PROPERTY_ID = Helper::UF_IBLOCK;
            $bVarsFromForm = $_SERVER['REQUEST_METHOD']=='POST';
            if ($USER_FIELD_MANAGER->GetRights($PROPERTY_ID) >= 'W') {
                ob_start();
                if(method_exists($USER_FIELD_MANAGER, 'showscript')) {
                    echo $USER_FIELD_MANAGER->ShowScript();
                }
                ?>
                <tr>
                    <td colspan="2" align="left">
                        <a href="/bitrix/admin/userfield_edit.php?lang=<?= LANGUAGE_ID?><?
                        ?>&amp;ENTITY_ID=<?= urlencode($PROPERTY_ID)?>&amp;back_url=<?= urlencode($APPLICATION->GetCurPageParam().'&tabControl_active_tab=user_fields_tab')?><?
                        ?>">Добавить пользовательское свойство</a>
                    </td>
                </tr>
                <?
                $arUserFields = $USER_FIELD_MANAGER->GetUserFields($PROPERTY_ID, $ID, LANGUAGE_ID);
                foreach($arUserFields as $FIELD_NAME => $arUserField) {

                    $arUserField['VALUE_ID'] = $ID;
                    if (isset($_REQUEST['def_'.$FIELD_NAME])) {
                        $arUserField['SETTINGS']['DEFAULT_VALUE'] = $_REQUEST['def_'.$FIELD_NAME];
                    }

                    echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField);
                }
                $strContent = ob_get_contents();
                ob_end_clean();

                $arTab = $GLOBALS['USER_FIELD_MANAGER']->EditFormTab($PROPERTY_ID);
                $arTab['CONTENT'] = $strContent;
                $form->tabs[] = $arTab;
            }
        }

        return;
        //old variant
        if( $GLOBALS[ 'APPLICATION' ]->GetCurPage( true ) == '/bitrix/admin/seo_sitemap_edit.php' ){
            require_once($_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/modules/iblock/prolog.php');
            $ID = $_REQUEST[ 'ID' ];
            $cont = '';
            if( !$ID ){
                $cont = '<tr>
                        <td  colspan="2" style="text-align: center">';
                $cont .= BeginNote();
                $cont .= Loc::getMessage( SEO_CHPU_LITE_PREFIX.'SITEMAP_NOTE' );
                $cont .= EndNote();
                $cont .= '</td>
                    </tr>';
            }else{

                $resIblock = \Bitrix\Iblock\IblockTable::getList(
                    [
                        'filter' => [
                            "IBLOCK_TYPE_ID" => SEO_CHPU_LITE_NO_DOT
                        ]
                    ]
                );
                if( $resIblock->getSelectedRowsCount() ){
                    $cont = '<tr class="heading">
                        <td  colspan="2">' . Loc::getMessage( SEO_CHPU_LITE_PREFIX.'SITEMAP_MODULE_SETTINGS' ) . '</td>
                    </tr>';
                    $selectedIds = unserialize(Option::get(SEO_CHPU_LITE, 'SITEMAP_'.$ID, serialize([])));
                    while( $dataIblock = $resIblock->fetch() ){

                        $cont .= '<tr>
                        <td width="40%"><label for="'.SEO_CHPU_LITE_PREFIX.'SITEMAP_IBLOCKS_'.$dataIblock['ID'].'">[<a href="u/bitrix/admin/iblock_edit.php?lang=ru&ID='.$dataIblock['ID'].'&type='.$dataIblock['IBLOCK_TYPE_ID'].'&admin=Y">'.$dataIblock['ID'].']</a> ' . $dataIblock['NAME'] . '</label></td>
                        <td width="60%">
                            <input id="'.SEO_CHPU_LITE_PREFIX.'SITEMAP_IBLOCKS_'.$dataIblock['ID'].'" type="checkbox" name="'.SEO_CHPU_LITE_PREFIX.'SITEMAP_IBLOCKS[]" '.(in_array($dataIblock['ID'], $selectedIds)?'checked':'').' value="'.$dataIblock['ID'].'" />
                        </td>
                    </tr>';
                    }
                }
            }

            $form->tabs[] = array(
                'DIV'     => str_replace('.', '_', SEO_CHPU_LITE),
                'TAB'     => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'SITEMAP_TAB_TITLE' ),
                'TITLE'   => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'SITEMAP_TAB_TITLE' ),
                'CONTENT' => $cont
            );
        }
    }

    static function BeforeIndex($arFields)
    {
        if($arFields["MODULE_ID"] == "iblock"){
            $iblock_ids = Helper::getInstance()->getChpuIblocks($arFields['SITE_ID']);

            foreach($iblock_ids as $iblock_id){
                if( $arFields["PARAM2"] == $iblock_id && Loader::includeModule('iblock') ){
                    $resElem = \CIBlockElement::GetList(
                        [
                            'SORT' => 'ASC'
                        ],
                        [
                            'IBLOCK_ID' => $iblock_id,
                            'ACTIVE' => 'Y',
                            'ID' => $arFields["ITEM_ID"],
                            '!PROPERTY_SEARCH_INDEX' => false,
                            '!PROPERTY_SEO_H1' => false,
                            '!PROPERTY_NEW_URL' => false
                        ],
                        false,
                        false,
                        [
                            'ID',
                            'IBLOCK_ID',
                            'PROPERTY_SEO_H1',
                            'PROPERTY_NEW_URL',
                        ]
                    );
                    if( $dataElem = $resElem->Fetch() ){
                        $arFields['URL'] = $dataElem['PROPERTY_NEW_URL_VALUE'];
                        $arFields['TITLE'] = $dataElem['PROPERTY_SEO_H1_VALUE']['TEXT'];
                        $arFields['BODY'] = $dataElem['PROPERTY_SEO_H1_VALUE']['TEXT'];
                    }else{
                        unset($arFields['TITLE']);
                        unset($arFields['BODY']);
                    }
                }
            }

        }
        return $arFields;
    }


    static function OnSearchGetURL($arFields)
    {
        if($arFields["MODULE_ID"] == "iblock"){
            $iblock_ids = Helper::getInstance()->getChpuIblocks($arFields['SITE_ID']);

            foreach($iblock_ids as $iblock_id){
                if( $arFields["PARAM2"] == $iblock_id && Loader::includeModule('iblock') ){
                    return $arFields["URL"];
                }
            }
        }
        return $arFields["URL"];
    }

    static function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
    {
        foreach($aModuleMenu as $key=>$moduleMenu){

            if( $moduleMenu['parent_menu'] == 'global_menu_content' && $moduleMenu['items_id'] == 'menu_iblock_/dwstroy_seochpulite'){
                $aModuleMenu[$key]['icon'] = 'dwstroy_seochpulite_menu_icon';
                $aModuleMenu[$key]['page_icon'] = 'dwstroy_seochpulite_page_icon';
            }
        }
    }
}