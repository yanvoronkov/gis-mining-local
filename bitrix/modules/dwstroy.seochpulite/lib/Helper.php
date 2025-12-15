<?

namespace Dwstroy\SeoChpuLite;

use Bitrix\Iblock\IblockSiteTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\SitemapIblock;
use Bitrix\Seo\SitemapRuntime;
use Bitrix\Seo\SitemapRuntimeTable;

Loc::loadMessages(__FILE__);
require_once(__DIR__.'/../define.php');

class Helper{

    private static $_instance           = null;
    public static $data = [];
    public $curIblock = [];
    const UF_IBLOCK = 'UF_IBLOCK';

    private function __construct(){
    }

    protected function __clone(){
    }

    static public function getInstance(){
        if( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function getChain($itemId, $iblockId,  &$chain){
        if( Loader::includeModule( 'iblock' ) ){
            $resElem = \CIBlockElement::GetList(
                [
                    'SORT' => 'ASC',
                    'ID'   => 'ASC'
                ], [
                'IBLOCK_ID'        => $iblockId,
                'ACTIVE'           => 'Y',
                'DATE_ACTIVE'           => 'Y',
                'GLOBAL_ACTIVE'           => 'Y',
                'ID' => $itemId,
                '!PROPERTY_SEO_H1' => false,
                '!PROPERTY_ADD_TO_CHAIN' => false,
                '!PROPERTY_NEW_URL' => false
            ], false, [
                'nTopCount' => 1
            ],
                [
                    'ID',
                    'PROPERTY_SEO_H1',
                    'PROPERTY_PARENT_BREADCRUMB_ITEM',
                    'PROPERTY_NEW_URL',
                ]
            );

            if( $data = $resElem->Fetch() ){
                $text = $data['PROPERTY_SEO_H1_VALUE']['TEXT'];
                if( empty($text) ){
                    $text = $data['PROPERTY_SEO_H1_VALUE']['HTML'];
                }
                array_unshift($chain, [
                    'NAME' => $text,
                    'URL' => $data['PROPERTY_NEW_URL_VALUE']
                ]);
                if( $data['PROPERTY_PARENT_BREADCRUMB_ITEM_VALUE'] ){
                    self::getChain($data['PROPERTY_PARENT_BREADCRUMB_ITEM_VALUE'], $iblockId, $chain);
                }
            }
        }
    }


    public static function returnNewUrl( $arParams, &$arResult ){
        $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_SET_FILTER_URL' ] = str_replace( '/filter/clear/apply/', '/', $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_SET_FILTER_URL' ] );
        $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_DEL_FILTER_URL' ] = str_replace( '/filter/clear/apply/', '/', $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_DEL_FILTER_URL' ] );
        $arResult[ 'FILTER_URL' ] = str_replace( '/filter/clear/apply/', '/', $arResult[ 'FILTER_URL' ] );
        $arResult[ 'FILTER_AJAX_URL' ] = str_replace( '/filter/clear/apply/', '/', $arResult[ 'FILTER_AJAX_URL' ] );
        $arResult[ 'SEF_SET_FILTER_URL' ] = str_replace( '/filter/clear/apply/', '/', $arResult[ 'SEF_SET_FILTER_URL' ] );
        $arResult[ 'SEF_DEL_FILTER_URL' ] = str_replace( '/filter/clear/apply/', '/', $arResult[ 'SEF_DEL_FILTER_URL' ] );


        $arResult[ "JS_FILTER_PARAMS" ][ "FOLDER" ] = $arParams[ "FOLDER" ];
        $arResult[ "JS_FILTER_PARAMS" ][ "FILTER_NAME" ] = $arParams[ "FILTER_NAME" ];

        $backUrls = [];
        $urlsMap = [];
        $urlsMap[ $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_SET_FILTER_URL' ] ][] = [
            'JS_FILTER_PARAMS',
            'SEF_SET_FILTER_URL'
        ];
        $urlsMap[ $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_DEL_FILTER_URL' ] ][] = [
            'JS_FILTER_PARAMS',
            'SEF_DEL_FILTER_URL'
        ];
        $urlsMap[ $arResult[ 'FILTER_URL' ] ][] = [ 'FILTER_URL' ];
        $urlsMap[ $arResult[ 'FILTER_AJAX_URL' ] ][] = [ 'FILTER_AJAX_URL' ];
        $urlsMap[ $arResult[ 'SEF_SET_FILTER_URL' ] ][] = [ 'SEF_SET_FILTER_URL' ];
        $urlsMap[ $arResult[ 'SEF_DEL_FILTER_URL' ] ][] = [ 'SEF_DEL_FILTER_URL' ];
        $urlsMap[ $arResult[ 'FORM_ACTION' ] ][] = [ 'FORM_ACTION' ];

        $backUrls[ $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_SET_FILTER_URL' ] ] = $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_SET_FILTER_URL' ];
        $backUrls[ $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_DEL_FILTER_URL' ] ] = $arResult[ 'JS_FILTER_PARAMS' ][ 'SEF_DEL_FILTER_URL' ];
        $backUrls[ $arResult[ 'FILTER_URL' ] ] = $arResult[ 'FILTER_URL' ];
        $backUrls[ $arResult[ 'FILTER_AJAX_URL' ] ] = $arResult[ 'FILTER_AJAX_URL' ];
        $backUrls[ $arResult[ 'SEF_SET_FILTER_URL' ] ] = $arResult[ 'SEF_SET_FILTER_URL' ];
        $backUrls[ $arResult[ 'SEF_DEL_FILTER_URL' ] ] = $arResult[ 'SEF_DEL_FILTER_URL' ];
        $backUrls[ $arResult[ 'FORM_ACTION' ] ] = $arResult[ 'FORM_ACTION' ];

        foreach( $arResult[ 'SELECTED' ] as $key => $value ){
            $backUrls[ $value[ 'URL' ] ] = $value[ 'URL' ];
            $urlsMap[ $value[ 'URL' ] ][] = [
                'SELECTED',
                $key,
                'URL'
            ];
        }


        if( !empty( $backUrls ) ){

            if( Loader::includeModule(SEO_CHPU_LITE) ){
                $iblocks_id = Helper::getInstance()->getChpuIblocks();
                if( $iblocks_id && Loader::includeModule( 'iblock' ) ){
                    foreach($iblocks_id as $iblock_id){
                        $resElem = \CIBlockElement::GetList(
                            [
                                'SORT' => 'ASC',
                                'ID'   => 'ASC'
                            ], [
                                'IBLOCK_ID'         => $iblock_id,
                                'ACTIVE'            => 'Y',
                                'DATE_ACTIVE'           => 'Y',
                                'GLOBAL_ACTIVE'           => 'Y',
                                [
                                    'LOGIC' => "OR",
                                    'PROPERTY_OLD_URL'  => $backUrls,
                                    'PROPERTY_OTHER_URLS'  => $backUrls,
                                ],
                                '!PROPERTY_NEW_URL' => false
                            ]
                        );

                        if( $ob = $resElem->GetNextElement() ){
                            $dataElem = $ob->GetFields();

                            $dataElem[ 'PROPERTIES' ] = $ob->GetProperties();

                            $oldUrl = $dataElem[ 'PROPERTIES' ][ 'OLD_URL' ][ 'VALUE' ];
                            foreach( $urlsMap[ $oldUrl ] as $keys ){
                                $arResult = self::insert_using_keys($arResult, $keys, $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ]);
                            }

                            foreach( $dataElem[ 'PROPERTIES' ][ 'OTHER_URLS' ][ 'VALUE' ] as $oldUrl ){
                                foreach( $urlsMap[ $oldUrl ] as $keys ){
                                    $arResult = self::insert_using_keys($arResult, $keys, $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ]);
                                }
                            }
                            break;
                        }


                    }

                }
            }
        }

        self::returnUrl();
    }

    public static function returnUrl(){
        if( !empty( self::$data ) ){
            self::reInitUrl( self::$data[ 'NEW_URL' ], 'new' );
        }
    }


    public static function getArr_variants($items = array(), $limit = 5, $repeatItems = false, $arrayOrDelimiter = false) {
        static $excludeItems = array();
        $R = array();
        if (!is_array($items)) return array();
        if (!$repeatItems && count($items) < $limit) $limit = count($items);
        ///
        foreach ($items as $item) {
            if (isset($excludeItems[$item])) continue;
            if (!$repeatItems) $excludeItems[$item] = '';
            $R[] = $arrayOrDelimiter === true ? array($item) : $item;
            if ($limit > 1) {
                foreach (self::getArr_variants($items, $limit - 1, $repeatItems, $arrayOrDelimiter) as $subItem) {
                    $R[] = $arrayOrDelimiter === true ? array_merge(array($item), $subItem) : $item.$arrayOrDelimiter.$subItem;
                }
            }
            unset($excludeItems[$item]);
        }
        ///
        return $R;
    }

    public static  function pc_permute($items, $perms = array()) {
        if (empty($items)) {
            return implode('/', $perms) ;
        } else {
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                self::pc_permute($newitems, $newperms);
            }
        }
    }


    public static function insert_using_keys( $arr, $keys, $value ){
        // we're modifying a copy of $arr, but here
        // we obtain a reference to it. we move the
        // reference in order to set the values.
        $a = &$arr;

        while( count( $keys ) > 0 ){
            // get next first key
            $k = array_shift( $keys );

            // if $a isn't an array already, make it one
            if( !is_array( $a ) ){
                $a = array();
            }

            // move the reference deeper
            $a = &$a[ $k ];
        }
        $a = $value;

        // return a copy of $arr with the value set
        return $arr;
    }

    public static function addCodeToCatalogResultModifier($folder){
        $cont = "if( \Bitrix\Main\Loader::includeModule('".SEO_CHPU_LITE."') ){\n".SEO_CHPU_LITE_NAMESPACE_PREFIX."Helper::returnUrl();\n}";
        self::addContToFile($cont, $folder);
    }
    public static function addCodeToSmartFilterResultModifier($folder){
        $cont = "if( \Bitrix\Main\Loader::includeModule('".SEO_CHPU_LITE."') ){\n".SEO_CHPU_LITE_NAMESPACE_PREFIX."Helper::returnNewUrl(\$arParams, \$arResult);\n}";
        self::addContToFile($cont, $folder);
    }

    public static function addContToFile($cont, $folder){
        $file = $_SERVER['DOCUMENT_ROOT'].$folder.'/result_modifier.php';
        if( !file_exists($file) ){
            file_put_contents($file, "<?if(!defined(\"B_PROLOG_INCLUDED\") || B_PROLOG_INCLUDED!==true)die();\n".$cont);
        }elseif( mb_strpos(file_get_contents($file), $cont) === false){
            file_put_contents($file, file_get_contents($file). "\n".$cont);
        }
    }

    public static function clearGet(){
        return;
        foreach($_SERVER['OLD_GET'] as $key => $v){
            unset($_GET[$key]);
            unset($_REQUEST[$key]);
        }
    }

    public static function makeOldUrl(){
        $url = Helper::getInstance()->getUrl2();
        if( Loader::includeModule(SEO_CHPU_LITE) ){
            $iblock_ids = Helper::getInstance()->getChpuIblocks();
            if( $iblock_ids && Loader::includeModule( 'iblock' ) ){

                foreach($iblock_ids as $iblock_id){
                    $resElem = \CIBlockElement::GetList(
                        [
                            'SORT' => 'ASC',
                            'ID'   => 'ASC'
                        ], [
                        'IBLOCK_ID'            => $iblock_id,
                        'ACTIVE'               => 'Y',
                        'GLOBAL_ACTIVE'        => 'Y',
                        'DATE_ACTIVE'        => 'Y',
                        'PROPERTY_NEW_URL'     => $url,
                        '!PROPERTY_OLD_URL' => false,
                    ], false
                    );
                    while( $ob = $resElem->GetNextElement() ){
                        $dataElem = $ob->GetFields();
                        $dataElem[ 'PROPERTIES' ] = $ob->GetProperties();
                        if( $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ] === $url ){
                            self::$data[ 'OLD_URL' ] = Helper::getInstance()->myUrlDecode($dataElem[ 'PROPERTIES' ][ 'OLD_URL' ][ 'VALUE' ]);
                            self::$data[ 'NEW_URL' ] = $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ];
                            self::reInitUrl( self::$data[ 'OLD_URL' ], 'old' );
                            break;
                        }
                    }
                    if( !empty(self::$data) ){
                        break;
                    }
                }

            }

        }
    }

    public static function reInitUrl( $url, $type ){
        global $APPLICATION;
        $context = Context::getCurrent();
        $server = $context->getServer();
        $server_array = $server->toArray();

        $_SERVER[ "REQUEST_URI_NEW" ] = $_SERVER[ "REQUEST_URI" ];
        $_SERVER[ "OLD_GET" ] = $_GET;
        $foundQMark = mb_strpos( $url, "?" );
        $requestUriWithoutParams = ($foundQMark !== false ? mb_substr(
            $url, 0, $foundQMark
        ) : $url);
        $requestParams = ($foundQMark !== false ? mb_substr( $url, $foundQMark ) : "");

        $_SERVER[ 'REQUEST_URI' ] = $url /*. ($foundQMark ? $requestParams : '')*/;
        $_SERVER[ 'QUERY_STRING' ] = ($foundQMark ? mb_substr( $requestParams, 1 ) : '');
        $server_array[ 'REQUEST_URI' ] = $_SERVER[ 'REQUEST_URI' ];
        $server_array[ 'REQUEST_URI_NEW' ] = $_SERVER[ "REQUEST_URI_NEW" ];
        $server_array[ 'OLD_GET' ] = $_SERVER[ "OLD_GET" ];
        $server->set( $server_array );

        if( !empty($_SERVER[ 'QUERY_STRING' ]) ){
            $get = explode('&', $_SERVER[ 'QUERY_STRING' ]);
            foreach($get as $g){
                $g = explode('=', $g);
                $_GET[$g[0]] = $g[1];
                $_REQUEST[$g[0]] = $g[1];
            }
        }
        $context->initialize( new HttpRequest( $server, $_GET, $_POST, $_FILES, $_COOKIE ), $context->getResponse(), $server );
        // $APPLICATION->reinitPath();
        $APPLICATION->SetCurPage( $url );
    }


    public static function isSkip( $urls ){
        $request = Context::getCurrent()->getRequest();

        foreach($urls as $url){
            if( (mb_strpos( $url, '/filter/' ) !== false && $_GET[ 'ajax' ] == 'y') || $request->isPost()){
                return true;
            }
        }

        return false;
    }


    function myUrlEncode($string) {
        return rawurldecode($string);
    }
/*    function myUrlEncode($string) {
        $entities = array(' ');
        $replacements = array('%20');
        return str_replace($entities, $replacements, html_entity_decode($string));
    }*/
    function myUrlDecode($string) {
        $entities = array('%2520');
        $replacements = array('%20');

        $foundQMark = mb_strpos( $string, "?" );
        $requestParams = ($foundQMark !== false ? mb_substr( $string, $foundQMark ) : "");

        $requestUriWithoutParams = ($foundQMark !== false ? mb_substr(
            $string, 0, $foundQMark
        ) : $string);

        $url = explode('/', $requestUriWithoutParams);
        foreach($url as $ui => $u){
            //$url[$ui] = urlencode(html_entity_decode($u));
            $url[$ui] = rawurlencode($u);
        }
        $string = implode('/', $url).$requestParams;
        return $string;
        return str_replace($entities, $replacements, $string);
    }

    function getUrl(){
        global $APPLICATION;
        $url = $APPLICATION->GetCurPage();
        $query = str_replace($url, '', $APPLICATION->GetCurPageParam());
        $url = explode('/', $url);
        $urls = [];
        foreach($url as $ui => $u){
            $url[$ui] = $this->myUrlEncode($u);
        }
        //$url = implode('/', $url);
        $url = implode('/', $url)/*.$query*/;
        $urls[] = $url;
        $urls[] = $url.$query;
        /*if( !empty($query) ){
            $query = mb_substr($query, 1);
            $queryPath = explode('&', $query);
            $variants = Helper::getArr_variants($queryPath, 100, false, '&');

            foreach($variants as $v){
                $urls[] = $url.'?'.$v;
            }
        }*/
        return $urls;
    }

    function getUrl2(){
        global $APPLICATION;
        $url = $APPLICATION->GetCurPage();
        $url = explode('/', $url);
        foreach($url as $ui => $u){
            $url[$ui] = $this->myUrlEncode($u);
        }

        $url = implode('/', $url);
        return $url;
    }

    public function redirect(){
        $urls = $this->getUrl();
        if( self::isSkip( $urls ) ){
            return;
        }

        $foundQMark = mb_strpos( $_SERVER[ "REQUEST_URI" ], "?" );
        $requestParams = ($foundQMark !== false ? mb_substr( $_SERVER[ "REQUEST_URI" ], $foundQMark ) : "");

        if( Loader::includeModule(SEO_CHPU_LITE) ){
            $iblock_ids = Helper::getInstance()->getChpuIblocks();
            if( $iblock_ids && Loader::includeModule( 'iblock' ) ){
                foreach($iblock_ids as $iblock_id){
                    $resElem = \CIBlockElement::GetList(
                        [
                            'SORT' => 'ASC',
                            'ID'   => 'ASC'
                        ], [
                        'IBLOCK_ID'          => $iblock_id,
                        'ACTIVE'             => 'Y',
                        'DATE_ACTIVE'           => 'Y',
                        'GLOBAL_ACTIVE'           => 'Y',
                        [
                            'LOGIC' => "OR",
                            '=PROPERTY_OLD_URL'  => $urls,
                            '=PROPERTY_OTHER_URLS'  => $urls,
                        ],
                        '!PROPERTY_NEW_URL'  => false,
                        '!PROPERTY_REDIRECT' => false,
                    ], false, [
                            'nTopCount' => 1
                        ]
                    );
                    if( $ob = $resElem->GetNextElement() ){
                        $dataElem = $ob->GetFields();
                        $dataElem[ 'PROPERTIES' ] = $ob->GetProperties();

                        $foundQMarkNew = mb_strpos( $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ], "?" );
                        $requestParamsNew = ($foundQMarkNew !== false ? mb_substr( $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ], $foundQMarkNew ) : "");

                        $foundQMarkOld = mb_strpos( $dataElem[ 'PROPERTIES' ][ 'OLD_URL' ][ 'VALUE' ], "?" );
                        $requestParamsOld = ($foundQMarkOld !== false ? mb_substr( $dataElem[ 'PROPERTIES' ][ 'OLD_URL' ][ 'VALUE' ], $foundQMarkOld ) : "");

                        if( $foundQMark ){
                            $requestParams3 = [];
                            $requestParams2 = (!empty($requestParams)?mb_substr( $requestParams, 1 ):"");
                            $requestParamsNew2 = (!empty($requestParamsNew)?mb_substr( $requestParamsNew, 1 ):"");
                            $requestParamsOld2 = (!empty($requestParamsOld)?mb_substr( $requestParamsOld, 1 ):"");
                            $requestParams2Path = (!empty($requestParams2)?explode('&', $requestParams2):[]);
                            $requestParamsNew2Path = (!empty($requestParamsNew2)?explode('&', $requestParamsNew2):[]);
                            $requestParamsOld2Path = (!empty($requestParamsOld2)?explode('&', $requestParamsOld2):[]);

                            foreach($requestParams2Path as  $v){
                                if( in_array($v, $requestParamsNew2Path)  ){
                                    $requestParams3[] = $v;
                                }else if(!in_array($v, $requestParamsOld2Path)){
                                    $requestParams3[] = $v;
                                }
                            }
                        }

                        LocalRedirect( $dataElem[ 'PROPERTIES' ][ 'NEW_URL' ][ 'VALUE' ].(!empty($requestParams3)?'?'.implode('&', $requestParams3):''), true, $dataElem[ 'PROPERTIES' ][ 'REDIRECT' ][ 'VALUE' ] );

                        break;
                    }
                }
            }
        }
    }

    public function createIblock($date = false){
        if( !Loader::includeModule('iblock') ){
            return false;
        }
        $siteIds = [];
        $resSites = SiteTable::getList();
        while($dataSite = $resSites->fetch()){
            $siteIds[] = $dataSite['LID'];
        }

        $d = new DateTime();

        $iblock = new \CIBlock();
        $iblockId = $iblock->Add(
            [
                "ACTIVE"           => 'Y',
                "NAME"             => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_NAME' ).($date?Loc::getMessage( SEO_CHPU_LITE_PREFIX.'CREATED_AT' ).$d->format('d.m.Y H:i:s'):''),
                "CODE"             => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_CODE' ).($date?'_'.$d->format('d_m_Y_H_i_s'):''),
                "SORT"    => '10',
                "LIST_PAGE_URL"    => '',
                "DETAIL_PAGE_URL"  => '#ELEMENT_CODE#',
                "INDEX_ELEMENT"  => 'Y',
                "INDEX_SECTION"  => 'N',
                "WORKFLOW"  => 'N',
                "BIZPROC"  => 'N',
                "LIST_MODE"  => 'C',
                "IBLOCK_TYPE_ID"   => SEO_CHPU_LITE_NO_DOT,
                "SITE_ID"          => $siteIds,
                "FIELDS"         => [
                    'CODE' => [
                        'DEFAULT_VALUE' => [
                            'TRANSLITERATION' => 'Y'
                        ]
                    ],
                    'SECTION_CODE' => [
                        'DEFAULT_VALUE' => [
                            'TRANSLITERATION' => 'Y'
                        ]
                    ]
                ],
                "GROUP_ID"         => array(
                    "2" => "R",
                )
            ]
        );

        if( $iblockId ){

            $arFields = [
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."OLD_URL"),
                    "ACTIVE" => "Y",
                    "IS_REQUIRED" => "Y",
                    "SORT" => "10",
                    "CODE" => "OLD_URL",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockId,
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."NEW_URL"),
                    "ACTIVE" => "Y",
                    "IS_REQUIRED" => "Y",
                    "SORT" => "20",
                    "CODE" => "NEW_URL",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockId,
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."REDIRECT"),
                    "ACTIVE" => "Y",
                    "SORT" => "22",
                    "CODE" => "REDIRECT",
                    "PROPERTY_TYPE" => "L",
                    "IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => "301 Moved permanently",
                            "DEF" => "Y",
                            "SORT" => "10",
                            "XML_ID" => "301"
                        ],
                        [
                            "VALUE" => "302",
                            "DEF" => "N",
                            "SORT" => "20",
                            "XML_ID" => "302"
                        ]
                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ROBOTS"),
                    "ACTIVE" => "Y",
                    "SORT" => "23",
                    "CODE" => "ROBOTS",
                    "PROPERTY_TYPE" => "L",
                    "LIST_TYPE" => "L",
                    "IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ROBOTS_INDEX_FOLLOW"),
                            "DEF" => "Y",
                            "SORT" => "10",
                            "XML_ID" => "index_follow"
                        ],
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ROBOTS_NOINDEX_FOLLOW"),
                            "DEF" => "N",
                            "SORT" => "20",
                            "XML_ID" => "noindex_follow"
                        ],
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ROBOTS_NOINDEX_NOFOLLOW"),
                            "DEF" => "N",
                            "SORT" => "30",
                            "XML_ID" => "noindex_nofollow"
                        ],
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ROBOTS_INDEX"),
                            "DEF" => "N",
                            "SORT" => "40",
                            "XML_ID" => "index"
                        ],
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ROBOTS_FOLLOW"),
                            "DEF" => "N",
                            "SORT" => "50",
                            "XML_ID" => "follow"
                        ]

                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."GENERATE_VARIANTS"),
                    "ACTIVE" => "Y",
                    "SORT" => "25",
                    "CODE" => "GENERATE_VARIANTS",
                    "PROPERTY_TYPE" => "L",
                    "LIST_TYPE" => "C",
                    "IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."Y"),
                            "DEF" => "Y",
                            "SORT" => "10",
                            "XML_ID" => "Y"
                        ]
                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."OTHER_URLS"),
                    "ACTIVE" => "Y",
                    "MULTIPLE" => "Y",
                    "SORT" => "27",
                    "CODE" => "OTHER_URLS",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockId,
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ADD_TO_SITEMAP"),
                    "ACTIVE" => "Y",
                    "SORT" => "30",
                    "CODE" => "ADD_TO_SITEMAP",
                    "PROPERTY_TYPE" => "L",
                    "LIST_TYPE" => "C",
                    "IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."Y"),
                            "DEF" => "Y",
                            "SORT" => "10",
                            "XML_ID" => "Y"
                        ]
                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."SEARCH_INDEX"),
                    "ACTIVE" => "Y",
                    "SORT" => "33",
                    "CODE" => "SEARCH_INDEX",
                    "PROPERTY_TYPE" => "L",
                    "LIST_TYPE" => "C",
                    "IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."Y"),
                            "DEF" => "Y",
                            "SORT" => "10",
                            "XML_ID" => "Y"
                        ]
                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."ADD_TO_CHAIN"),
                    "ACTIVE" => "Y",
                    "SORT" => "35",
                    "CODE" => "ADD_TO_CHAIN",
                    "PROPERTY_TYPE" => "L",
                    "LIST_TYPE" => "C",
                    "IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."Y"),
                            "DEF" => "Y",
                            "SORT" => "10",
                            "XML_ID" => "Y"
                        ]
                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."PARENT_BREADCRUMB_ITEM"),
                    "ACTIVE" => "Y",
                    "SORT" => "40",
                    "CODE" => "PARENT_BREADCRUMB_ITEM",
                    "PROPERTY_TYPE" => "E",
                    "USER_TYPE" => "EAutocomplete",
                    "IBLOCK_ID" => $iblockId,
                    "LINK_IBLOCK_ID" => $iblockId,
                    "VALUES" => [
                        [
                            "VALUE" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."Y"),
                            "DEF" => "N",
                            "SORT" => "10",
                            "XML_ID" => "Y"
                        ]
                    ]
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."SEO_H1"),
                    "ACTIVE" => "Y",
                    "SORT" => "70",
                    "CODE" => "SEO_H1",
                    "PROPERTY_TYPE" => "S",
                    "USER_TYPE" => "HTML",
                    "IBLOCK_ID" => $iblockId
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."SEO_TITLE"),
                    "ACTIVE" => "Y",
                    "SORT" => "80",
                    "CODE" => "SEO_TITLE",
                    "PROPERTY_TYPE" => "S",
                    "USER_TYPE" => "HTML",
                    "IBLOCK_ID" => $iblockId
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."SEO_KEYWORDS"),
                    "ACTIVE" => "Y",
                    "SORT" => "90",
                    "CODE" => "SEO_KEYWORDS",
                    "PROPERTY_TYPE" => "S",
                    "USER_TYPE" => "HTML",
                    "IBLOCK_ID" => $iblockId
                ],
                [
                    "NAME" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."SEO_DESCRIPTION"),
                    "ACTIVE" => "Y",
                    "SORT" => "100",
                    "CODE" => "SEO_DESCRIPTION",
                    "PROPERTY_TYPE" => "S",
                    "USER_TYPE" => "HTML",
                    "IBLOCK_ID" => $iblockId
                ]
            ];

            $ibp = new \CIBlockProperty();

            $props = [];
            $cnt = count($arFields);
            foreach($arFields as $key => $field){
                $PropID = $ibp->Add($field);
                if( $PropID ){
                    $props[] = "--PROPERTY_".$PropID."--#--".$field['NAME'].($key == ($cnt - 1)?'':"--");
                }
            }

            \CUserOptions::DeleteOption('form', 'form_element_'.$iblockId.'_disabled', false);
            $arOptions = [
                [
                    'c' => 'form',
                    'n' => 'form_element_'.$iblockId,
                    'd' => 'Y',
                    'v' => [
                        'tabs' => "edit1--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."ELEMENT")."--,--ID--#--ID--,--DATE_CREATE--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."DATE_CREATE")."--,--TIMESTAMP_X--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."TIMESTAMP_X")."--,--ACTIVE--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."ACTIVE")."--,--ACTIVE_FROM--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."ACTIVE_FROM")."--,--ACTIVE_TO--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."ACTIVE_TO")."--,--NAME--#--*".Loc::getMessage(SEO_CHPU_LITE_PREFIX."NAME")."--,--CODE--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."CODE")."--,--SORT--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."SORT")."--,--IBLOCK_ELEMENT_PROP_VALUE--#----".Loc::getMessage(SEO_CHPU_LITE_PREFIX."IBLOCK_ELEMENT_PROP_VALUE")."--,".implode(',', $props)."--,--PREVIEW_TEXT--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."PREVIEW_TEXT")."--,--DETAIL_TEXT--#--".Loc::getMessage(SEO_CHPU_LITE_PREFIX."DETAIL_TEXT")
                    ]
                ]
            ];
            \CUserOptions::SetOptionsFromArray($arOptions);
        }

        return $iblockId;
    }

    public function getChpuIblocks($site_id = false){
        if( !empty($this->curIblock) ){
            return  $this->curIblock;
        }
        if( Loader::includeModule('iblock') ){
            $res = \CIBlock::GetList( array('SORT' => 'ASC'), array( 'TYPE'       => SEO_CHPU_LITE_NO_DOT,
                'SITE_ID'    => ($site_id == false?SITE_ID:$site_id),
                'ACTIVE'     => 'Y',
            ));

            while( $data = $res->Fetch() ){
                $this->curIblock[] = $data['ID'];
            }
        }
        return  $this->curIblock;
    }

    public static function ckechIfEnd( $result ){
        return preg_match( "/BX\.finishSitemap\(\)/usmi", $result, $matches );
    }

    public static function generateChpuForSitemap( $dataSitemap ){
        if( !is_array( $dataSitemap[ 'SETTINGS' ] ) ){
            $dataSitemap[ 'SETTINGS' ] = unserialize( $dataSitemap[ 'SETTINGS' ] );
        }
        $dataSitemap[ 'SETTINGS' ][ 'FILENAME_CHPU' ] = 'sitemap-iblock-seochpu-lite-#IBLOCK_ID#.xml';
        $v = intval( $_REQUEST[ 'value' ] );
        $ID = intval( $_REQUEST[ 'ID' ] );
        $NS = isset( $_REQUEST[ 'NS' ] ) && is_array( $_REQUEST[ 'NS' ] ) ? $_REQUEST[ 'NS' ] : array();
        $PID = $ID;

        $arValueSteps = array(
            'iblock_seo_chpu_lite_index' => 61,
            'iblock_seo_chpu_lite'       => 64,
            'index'                   => 69,
        );

        $arSitemapSettings = array(
            'SITE_ID'  => $dataSitemap[ 'SITE_ID' ],
            'PROTOCOL' => $dataSitemap[ 'SETTINGS' ][ 'PROTO' ] == 1 ? 'https' : 'http',
            'DOMAIN'   => $dataSitemap[ 'SETTINGS' ][ 'DOMAIN' ],
        );



        if( $v < $arValueSteps[ 'iblock_seo_chpu_lite_index' ] ){
            $NS[ 'time_start' ] = microtime( true );

            $arIBlockList = array();
            if( Loader::includeModule( 'iblock' ) ){
                $arIBlockList = unserialize(Option::get(SEO_CHPU_LITE, 'SITEMAP_'.$dataSitemap['ID'], serialize([])));
                if( count( $arIBlockList ) > 0 ){
                    $arIBlocks = array();
                    $dbIBlock = \CIBlock::GetList( array(), array( 'ID' => $arIBlockList ) );
                    while( $arIBlock = $dbIBlock->Fetch() ){
                        $arIBlocks[ $arIBlock[ 'ID' ] ] = $arIBlock;
                    }

                    foreach( $arIBlockList as $iblockId ){
                        if(  !array_key_exists( $iblockId, $arIBlocks ) ){
                            unset( $arIBlockList[ $iblockId ] );
                        }else{
                            $res = SitemapRuntimeTable::getList(
                                [
                                    'filter' => [
                                        'PID'       => $PID,
                                        'ITEM_ID'   => $iblockId,
                                        'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_IBLOCK,
                                    ]
                                ]
                            );
                            if( $data = $res->fetch() ){
                                SitemapRuntimeTable::update($data['ID'], ['PROCESSED' => SitemapRuntimeTable::UNPROCESSED]);
                            }else{
                                $res = SitemapRuntimeTable::add(
                                    array(
                                        'PID'       => $PID,
                                        'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
                                        'ITEM_ID'   => $iblockId,
                                        'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_IBLOCK,
                                    )
                                );
                            }

                            $fileName = str_replace(
                                array(
                                    '#IBLOCK_ID#',
                                    '#IBLOCK_CODE#',
                                    '#IBLOCK_XML_ID#'
                                ), array(
                                $iblockId,
                                $arIBlocks[ $iblockId ][ 'CODE' ],
                                $arIBlocks[ $iblockId ][ 'XML_ID' ]
                            ), $dataSitemap[ 'SETTINGS' ][ 'FILENAME_CHPU' ]
                            );

                            $sitemapFile = new SitemapRuntime( $PID, $fileName, $arSitemapSettings );
                            if( $sitemapFile->isExists() ){
                                $sitemapFile->delete();
                            }
                        }
                    }
                }
            }

            $NS[ 'LEFT_MARGIN' ] = 0;
            $NS[ 'IBLOCK_LASTMOD' ] = 0;

            $NS[ 'IBLOCK' ] = array();
            $NS[ 'СРЗГ_IBLOCK_MAP' ] = array();

            if( count( $arIBlockList ) <= 0 ){
                $v = $arValueSteps[ 'iblock_seo_chpu_lite' ];
                $msg = Loc::getMessage( 'SITEMAP_RUN_IBLOCK_EMPTY' );
            }else{
                $v = $arValueSteps[ 'iblock_seo_chpu_lite_index' ];
                $msg = Loc::getMessage( 'SITEMAP_RUN_IBLOCK' );
            }
            $NS[ 'self_step' ] = $v;
        }else if( $v < $arValueSteps[ 'iblock_seo_chpu_lite' ] ){
            $stepDuration = 10;
            $ts_finish = microtime(true) + $stepDuration * 0.95;

            $bFinished = false;
            $bCheckFinished = false;

            $currentIblock = false;
            $iblockId = 0;

            $dbOldIblockResult = null;
            $dbIblockResult = null;

            if(isset($_SESSION["SEO_SITEMAP_".$PID]))
            {
                $NS['СРЗГ_IBLOCK_MAP'] = $_SESSION["SEO_SITEMAP_".$PID];
                unset($_SESSION["SEO_SITEMAP_".$PID]);
            }


            while(!$bFinished && microtime(true) <= $ts_finish)
            {
                if(!$currentIblock)
                {
                    $arCurrentIBlock = false;
                    $dbRes = SitemapRuntimeTable::getList(array(
                        'order' => array('ID' => 'ASC'),
                        'filter' => array(
                            'PID' => $PID,
                            'ITEM_TYPE' => SitemapRuntimeTable::ITEM_TYPE_IBLOCK,
                            'PROCESSED' => SitemapRuntimeTable::UNPROCESSED,
                        ),
                        'limit' => 1
                    ));

                    $currentIblock = $dbRes->fetch();

                    if($currentIblock)
                    {
                        $iblockId = intval($currentIblock['ITEM_ID']);

                        $dbIBlock = \CIBlock::GetByID($iblockId);
                        $arCurrentIBlock = $dbIBlock->Fetch();

                        if(!$arCurrentIBlock)
                        {
                            SitemapRuntimeTable::update($currentIblock['ID'], array(
                                'PROCESSED' => SitemapRuntimeTable::PROCESSED
                            ));

                            $NS['IBLOCK_LASTMOD'] = 0;
                            $NS['LAST_ELEMENT_ID'] = 0;
                            unset($NS['CURRENT_SECTION']);
                        }
                        else
                        {
                            if($arCurrentIBlock['SECTION_PAGE_URL'] == '')
                                $dataSitemap['SETTINGS']['IBLOCK_SECTION'][$iblockId] = 'N';
                            if($arCurrentIBlock['DETAIL_PAGE_URL'] == '')
                                $dataSitemap['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] = 'N';

                            $NS['IBLOCK_LASTMOD'] = max($NS['IBLOCK_LASTMOD'], MakeTimeStamp($arCurrentIBlock['TIMESTAMP_X']));

                            if($NS['LEFT_MARGIN'] <= 0 && $dataSitemap['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] != 'N')
                            {
                                $NS['CURRENT_SECTION'] = 0;
                            }

                            $fileName = str_replace(
                                array('#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'),
                                array($iblockId, $arCurrentIBlock['CODE'], $arCurrentIBlock['XML_ID']),
                                $dataSitemap['SETTINGS']['FILENAME_CHPU']
                            );
                            $sitemapFile = new SitemapRuntime($PID, $fileName, $arSitemapSettings);
                        }
                    }
                }

                if(!$currentIblock)
                {
                    $bFinished = true;
                }
                elseif(is_array($arCurrentIBlock))
                {
                    if($dbIblockResult == null)
                    {
                        $dbIblockResult = \CIBlockElement::GetList(
                            array( 'ID' => 'ASC' ), array(
                            'IBLOCK_ID' => $iblockId,
                            'ACTIVE' => 'Y',
                            '>ID' => intval( $NS[ 'LAST_ELEMENT_ID' ] ),
                            'SITE_ID' => $dataSitemap[ 'SITE_ID' ],
                            "ACTIVE_DATE" => "Y",
                            "!PROPERTY_NEW_URL" => false,
                            "!PROPERTY_ADD_TO_SITEMAP" => false
                        ), false, array( 'nTopCount' => 1000 ), array(
                                'ID',
                                'IBLOCK_ID',
                                'TIMESTAMP_X',
                                'PROPERTY_NEW_URL',
                            )
                        );
                    }

                    if(true)
                    {
                        $arElement = $dbIblockResult->fetch();
                        if($arElement)
                        {
                            if(!is_array($NS['СРЗГ_IBLOCK_MAP'][$iblockId]))
                            {
                                $NS['СРЗГ_IBLOCK_MAP'][$iblockId] = array();
                            }

                            if(!array_key_exists($arElement['ID'], $NS['СРЗГ_IBLOCK_MAP'][$iblockId]))
                            {
                                $arElement['LANG_DIR'] = $dataSitemap['SITE']['DIR'];

                                $bCheckFinished = false;
                                $elementLastmod = MakeTimeStamp($arElement['TIMESTAMP_X']);
                                $NS['IBLOCK_LASTMOD'] = max($NS['IBLOCK_LASTMOD'], $elementLastmod);
                                $NS['LAST_ELEMENT_ID'] = $arElement['ID'];

                                $NS['IBLOCK'][$iblockId]['E']++;
                                $NS['СРЗГ_IBLOCK_MAP'][$iblockId][$arElement["ID"]] = 1;

                                //							remove or replace SERVER_NAME
                                //$url = SitemapIblock::prepareUrlToReplace($arElement['DETAIL_PAGE_URL'], $dataSitemap['SITE_ID']);
                                //$url = \CIBlock::ReplaceDetailUrl($url, $arElement, false, "E");
                                $url = $arElement['PROPERTY_NEW_URL_VALUE'];

                                $sitemapFile->addIBlockEntry($url, $elementLastmod);
                            }
                        }
                        elseif(!$bCheckFinished)
                        {
                            $bCheckFinished = true;
                            $dbIblockResult = null;
                        }
                        else
                        {
                            $bCheckFinished = false;
                            // we have finished current iblock

                            SitemapRuntimeTable::update($currentIblock['ID'], array(
                                'PROCESSED' => SitemapRuntimeTable::PROCESSED,
                            ));


                            if($sitemapFile->isNotEmpty())
                            {
                                if($sitemapFile->isCurrentPartNotEmpty())
                                {
                                    $sitemapFile->finish();
                                }
                                else
                                {
                                    $sitemapFile->delete();
                                }

                                if(!is_array($NS['XML_FILES']))
                                    $NS['XML_FILES'] = array();

                                $xmlFiles = $sitemapFile->getNameList();
                                $directory = $sitemapFile->getPathDirectory();
                                foreach($xmlFiles as &$xmlFile)
                                    $xmlFile = $directory.$xmlFile;


                                $NS['XML_FILES'] = array_unique(array_merge($NS['XML_FILES'], $xmlFiles));
                            }
                            else
                            {
                                $sitemapFile->delete();
                            }

                            $currentIblock = false;
                        }
                    }
                }
            }
            if($v < $arValueSteps['iblock_seo_chpu_lite']-1)
            {
                $msg = Loc::getMessage('SITEMAP_RUN_IBLOCK_NAME', array('#IBLOCK_NAME#' => $arCurrentIBlock['NAME']));
                $v++;
            }

            if($bFinished)
            {
                $v = $arValueSteps['index'];
                $msg = Loc::getMessage('SITEMAP_RUN_FINALIZE');
            }
            $NS[ 'self_step' ] = $v;
        }

        return [
            'ID'  => $ID,
            'v'   => $v,
            'PID' => $PID,
            'NS'  => $NS
        ];
    }

    public static function makeText($data, $text){
        if( !empty($data) ){
            $replace = [];
            $search = [];

            foreach($data as $fieldCode => $value){
                if( $fieldCode == 'PROPERTIES' ){
                    continue;
                }
                if( $fieldCode == 'CLEARED_PROPERTIES' ){
                    foreach ($data[$fieldCode] as $propertyCode => $propertyValue){
                        if (mb_strpos($propertyCode, '~') === 0){
                            continue;
                        }

                        if (is_array($propertyValue) && $propertyValue['TEXT']){
                            $propertyValue = $propertyValue['TEXT'];
                        }elseif (is_array($propertyValue) && $propertyValue['HTML']){
                            $propertyValue = $propertyValue['HTML'];
                        }

                        $replace["#".SEOCHPU_LITE_PROPERTY_REPLACE_PREFIX.$propertyCode."#"] = html_entity_decode((string)$propertyValue);
                        $replace["#~".SEOCHPU_LITE_PROPERTY_REPLACE_PREFIX.$propertyCode."#"] = $propertyValue;
                        $search[] = "#".SEOCHPU_LITE_PROPERTY_REPLACE_PREFIX.$propertyCode."#";
                        $search[] = "#~".SEOCHPU_LITE_PROPERTY_REPLACE_PREFIX.$propertyCode."#";
                    }
                    continue;
                }else{
                    $replace["#".SEOCHPU_LITE_FIELD_REPLACE_PREFIX.$fieldCode."#"] = html_entity_decode($value);
                    $replace["#~".SEOCHPU_LITE_FIELD_REPLACE_PREFIX.$fieldCode."#"] = $value;
                    $search[] = "#".SEOCHPU_LITE_FIELD_REPLACE_PREFIX.$fieldCode."#";
                    $search[] = "#~".SEOCHPU_LITE_FIELD_REPLACE_PREFIX.$fieldCode."#";
                }
            }

            $text = str_replace($search, $replace, $text);

        }
        $text = preg_replace("/#".SEOCHPU_LITE_FIELD_REPLACE_PREFIX."[^#]+#/", '', $text);
        return $text;
    }

    public static function getCurPage(){
        global $APPLICATION;
        return $APPLICATION->GetCurPage(false);
    }

    public static function isAdminPage(){
        $check = false;
        if( defined("ADMIN_SECTION") && ADMIN_SECTION === true ){
            $check = true;
        }elseif( mb_strpos(self::getCurPage(), '/bitrix/') !== false ){
            $check = true;
        }
        return $check;
    }

    public static function getRequest(){
        return Application::getInstance()->getContext()->getRequest();
    }
    public static function isAjax(){
        $check = false;
        if( isset($_GET['bxajaxid']) && !empty($_GET['bxajaxid']) ){
            $check = true;
        }else if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && \mb_strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            $check = true;
        }else{
            $request = self::getRequest();
            if( !empty($request->getHeader('Bx-ajax')) ){
                $check = true;
            }else if( !empty($request->getHeader('BX-Ajax')) ){
                $check = true;
            }
        }
        return $check;
    }

    static function getLocalPath($path, $baseFolder = "/bitrix")
    {
        $root = rtrim($_SERVER["DOCUMENT_ROOT"], "\\/");

        if (file_exists($root . $baseFolder . "/" . $path))
        {
            return $baseFolder . "/" . $path;
        }

        // cli repository mode
        if (empty($_SERVER["DOCUMENT_ROOT"]) || defined('REPOSITORY_ROOT'))
        {
            $root = realpath(__DIR__ . '/../../');
            $localPath = $root . '/' . $path;

            if (file_exists($localPath))
            {
                return $localPath;
            }
        }

        return false;
    }

    static function GetRealTemplateLists($name = '', $siteTemplate = ''){
        $arTemplates = self::GetTemplatesList($name, $siteTemplate);

        $result = array(
            'templates' => array()
        );

        $arSiteTemplates = array(".default" => Loc::getMessage(SEO_CHPU_LITE_PREFIX."PAR_MAN_DEFAULT"));
        if(!empty($siteTemplate))
        {
            $dbst = \CSiteTemplate::GetList(array(), array("ID" => $siteTemplate), array());
            while($siteTempl = $dbst->Fetch())
                $arSiteTemplates[$siteTempl['ID']] = $siteTempl['NAME'];
        }

        foreach($arTemplates as $k => $templ)
        {
            $showTemplateName = ($templ["TEMPLATE"] !== '' && $arSiteTemplates[$templ["TEMPLATE"]] <> '') ? $arSiteTemplates[$templ["TEMPLATE"]] : Loc::getMessage(SEO_CHPU_LITE_PREFIX."PAR_MAN_DEF_TEMPLATE");
            $arTemplates[$k]['DISPLAY_NAME'] = $templ['NAME'].' ('.$showTemplateName.')';
        }

        if (is_array($arTemplates))
        {
            foreach ($arTemplates as $arTemplate)
            {
                $result['templates'][] = $arTemplate;
            }
        }
        return $result['templates'];
    }


    public static function GetTemplatesList($componentName, $currentTemplate = false)
    {
        $arTemplatesList = array();

        $componentName = trim($componentName);
        if ($componentName == '')
            return $arTemplatesList;

        $path2Comp = \CComponentEngine::MakeComponentPath($componentName);
        if ($path2Comp == '')
            return $arTemplatesList;

        $componentPath = self::getLocalPath("components".$path2Comp);

        if (!\CComponentUtil::isComponent($componentPath))
        {
            return $arTemplatesList;
        }

        $templateFolders = array();
        $arExists = array();
        $folders = array(
            "/local/templates",
            BX_PERSONAL_ROOT."/templates",
        );

        foreach($folders as $folder)
        {
            if(file_exists($_SERVER["DOCUMENT_ROOT"].$folder))
            {
                if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$folder))
                {
                    while (($file = readdir($handle)) !== false)
                    {
                        if ($file == "." || $file == "..")
                            continue;

                        if ($currentTemplate !== false && $currentTemplate != $file || $file == ".default")
                            continue;

                        if (file_exists($_SERVER["DOCUMENT_ROOT"].$folder."/".$file."/components".$path2Comp))
                        {
                            $templateFolders[] = array(
                                "path" => $folder."/".$file."/components".$path2Comp,
                                "template" => $file,
                            );
                        }
                    }
                    closedir($handle);

                    if (file_exists($_SERVER["DOCUMENT_ROOT"].$folder."/.default/components".$path2Comp))
                    {
                        $templateFolders[] = array(
                            "path" => $folder."/.default/components".$path2Comp,
                            "template" => ".default",
                        );
                    }
                }
            }
        }

        $templateFolders[] = array(
            "path" => $componentPath."/templates",
            "template" => "",
        );

        foreach($templateFolders as $templateFolder)
        {
            $templateFolderPath = $templateFolder["path"];
            if ($handle1 = @opendir($_SERVER["DOCUMENT_ROOT"].$templateFolderPath))
            {
                while (($file1 = readdir($handle1)) !== false)
                {
                    if ($file1 == "." || $file1 == "..")
                        continue;

                    if (in_array($file1, $arExists))
                        continue;

                    $arTemplate = array(
                        "NAME" => $file1,
                        "TEMPLATE" => $templateFolder["template"],
                    );

                    if (file_exists($_SERVER["DOCUMENT_ROOT"].$templateFolderPath."/".$file1."/.description.php"))
                    {
                        \CComponentUtil::__IncludeLang($templateFolderPath."/".$file1, ".description.php");

                        $arTemplateDescription = array();
                        include($_SERVER["DOCUMENT_ROOT"].$templateFolderPath."/".$file1."/.description.php");

                        $arTemplate["TITLE"] = $arTemplateDescription["NAME"];
                        $arTemplate["DESCRIPTION"] = $arTemplateDescription["DESCRIPTION"];
                    }

                    $arTemplatesList[] = $arTemplate;
                    $arExists[] = $arTemplate["NAME"];
                }
                @closedir($handle1);
            }
        }

        return $arTemplatesList;
    }

}
