<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\EventManager,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\SiteTable,
    Bitrix\Main\Loader;

Loc::loadMessages( __FILE__ );

require_once(__DIR__.'/../define.php');

class dwstroy_seochpulite extends CModule{
    const MODULE_ID = "dwstroy.seochpulite";
    var $MODULE_ID = "dwstroy.seochpulite";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    var $errors;

    function __construct(){
        $arModuleVersion = array();
        include(dirname( __FILE__ ) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion[ "VERSION" ];
        $this->MODULE_VERSION_DATE = $arModuleVersion[ "VERSION_DATE" ];
        $this->MODULE_NAME = Loc::getMessage( SEO_CHPU_LITE_PREFIX."MODULE_NAME" );
        $this->MODULE_DESCRIPTION = Loc::getMessage( SEO_CHPU_LITE_PREFIX."MODULE_DESC" );

        $this->PARTNER_NAME = 'ООО "ДАЛЬВЕБСТРОЙ"';
        $this->PARTNER_URI = "https://dwstroy.ru";
    }

    function InstallEvents(){
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler( 'main', 'OnPageStart', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnPageStart', 1000 );
        $eventManager->registerEventHandler( 'main', 'OnBeforeProlog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnBeforeProlog', 1000 );
        $eventManager->registerEventHandler( 'main', 'OnProlog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'redirect', 1000 );
        $eventManager->registerEventHandler( 'main', 'OnEpilog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnEpilog', 1000 );
        $eventManager->registerEventHandler( 'main', 'OnEndBufferContent', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnEndBufferContent', 1000 );
        $eventManager->registerEventHandler( 'iblock', 'OnAfterIblockElementAdd', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnAfterIblockElementAddUpdate', 1000 );
        $eventManager->registerEventHandler( 'iblock', 'OnAfterIblockElementUpdate', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnAfterIblockElementAddUpdate', 1000 );

        $eventManager->registerEventHandler( 'main', 'OnAdminTabControlBegin', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnAdminTabControlBegin', 1000 );
        $eventManager->registerEventHandler( 'main', 'OnEndBufferContent', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnEndBufferContentSitemap', 1000 );
        $eventManager->registerEventHandler( 'main', 'OnProlog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'SaveOptions', 1000 );
        $eventManager->registerEventHandler("main", "OnBuildGlobalMenu", self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX."Events", "OnBuildGlobalMenu", 1000);

        $eventManager->registerEventHandler( 'search', 'OnSearchGetURL', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnSearchGetURL', 1000 );
        $eventManager->registerEventHandler( 'search', 'BeforeIndex', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'BeforeIndex', 1000 );

        return true;
    }

    function UnInstallEvents(){
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler( 'main', 'OnPageStart', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnPageStart' );
        $eventManager->unRegisterEventHandler( 'main', 'OnBeforeProlog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnBeforeProlog' );
        $eventManager->unRegisterEventHandler( 'main', 'OnEpilog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnEpilog' );
        $eventManager->unRegisterEventHandler( 'main', 'OnEndBufferContent', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnEndBufferContent' );
        $eventManager->unRegisterEventHandler( 'iblock', 'OnAfterIblockElementAdd', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnAfterIblockElementAddUpdate' );
        $eventManager->unRegisterEventHandler( 'iblock', 'OnAfterIblockElementUpdate', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnAfterIblockElementAddUpdate' );

        $eventManager->unRegisterEventHandler( 'main', 'OnAdminTabControlBegin', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnAdminTabControlBegin', 1000 );
        $eventManager->unRegisterEventHandler( 'main', 'OnEndBufferContent', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnEndBufferContentSitemap', 1000 );
        $eventManager->unRegisterEventHandler( 'main', 'OnProlog', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'SaveOptions', 1000 );
        $eventManager->unRegisterEventHandler("main", "OnBuildGlobalMenu", self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX."Events", "OnBuildGlobalMenu", 1000);

        $eventManager->unRegisterEventHandler( 'search', 'OnSearchGetURL', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'OnSearchGetURL', 1000 );
        $eventManager->unRegisterEventHandler( 'search', 'BeforeIndex', self::MODULE_ID, SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events', 'BeforeIndex', 1000 );

        return true;
    }

    function InstallDB( $arParams = array() ){
        if( Loader::includeModule( $this->MODULE_ID ) ){

            if( Loader::includeModule('iblock') ){

                $resIblockType = \Bitrix\Iblock\TypeTable::getList(
                    [
                        'filter' => [
                            'ID' => SEO_CHPU_LITE_NO_DOT
                        ]
                    ]
                );
                $haveIblockType = false;
                if( !$resIblockType->getSelectedRowsCount() ){
                    $iblockType = new CIBlockType();
                    $resAddIblockType = $iblockType->Add(
                        array(
                            'ID'       => SEO_CHPU_LITE_NO_DOT,
                            'SECTIONS' => 'Y',
                            'IN_RSS'   => 'N',
                            'SORT'     => 100,
                            'LANG'     => array(
                                'ru' => array(
                                    'NAME'         => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_TYPE_NAME' ),
                                    'SECTION_NAME' => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_TYPE_SECTION_NAME' ),
                                    'ELEMENT_NAME' => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_TYPE_ELEMENT_NAME' )
                                )
                            )
                        )
                    );
                    if( $resAddIblockType ){
                        $haveIblockType = true;
                    }
                }else{
                    $haveIblockType = true;
                }

                if( $haveIblockType ){
                    $resIblock = \Bitrix\Iblock\IblockTable::getList(
                        [
                            'filter' => [
                                "IBLOCK_TYPE_ID" => SEO_CHPU_LITE_NO_DOT
                            ]
                        ]
                    );
                    if( !$resIblock->getSelectedRowsCount() ){
                        \Dwstroy\SeoChpuLite\Helper::getInstance()->createIblock();
                    }
                }
            }
        }

        return true;
    }

    function UnInstallDB( $arParams = array() ){

        if( !array_key_exists( "savedata", $arParams ) || ($arParams[ "savedata" ] != "Y") ){
            if( Loader::includeModule( $this->MODULE_ID ) ){
                if( Loader::includeModule('iblock') ){
                    $resIblockType = \Bitrix\Iblock\TypeTable::getList(
                        [
                            'filter' => [
                                'ID' => SEO_CHPU_LITE_NO_DOT
                            ]
                        ]
                    );
                    if( $resIblockType->getSelectedRowsCount() ){
                        $resIblock = \Bitrix\Iblock\IblockTable::getList(
                            [
                                'filter' => [
                                    "IBLOCK_TYPE_ID" => SEO_CHPU_LITE_NO_DOT
                                ]
                            ]
                        );
                        while( $dataIblock =  $resIblock->fetch() ){
                            \CIBlock::Delete($dataIblock['ID']);
                        }
                        \CIBlockType::Delete(SEO_CHPU_LITE_NO_DOT);
                    }
                }
            }
        }

        return true;
    }

    function InstallFiles( $arParams = array() ){
        if( is_dir( $p = $_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/modules/' . self::MODULE_ID . '/admin' ) ){
            if( $dir = opendir( $p ) ){
                while( false !== $item = readdir( $dir ) ){
                    if( $item == '..' || $item == '.' || $item == 'menu.php' ){
                        continue;
                    }
                    file_put_contents(
                        $file = $_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/admin/' . self::MODULE_ID . '_' . $item, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . self::MODULE_ID . '/admin/' . $item . '");?' . '>'
                    );
                }
                closedir( $dir );
            }
        }
        if( is_dir( $p = $_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/modules/' . self::MODULE_ID . '/install/components' ) ){
            if( $dir = opendir( $p ) ){
                while( false !== $item = readdir( $dir ) ){
                    if( $item == '..' || $item == '.' ){
                        continue;
                    }
                    CopyDirFiles( $p . '/' . $item, $_SERVER[ 'DOCUMENT_ROOT' ] . '/local/components/' . $item, $ReWrite = true, $Recursive = true );
                }
                closedir( $dir );
            }
        }

        CopyDirFiles($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/" . self::MODULE_ID . "/install/js/", $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/js/", true, true);
        CopyDirFiles($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/" . self::MODULE_ID . "/install/themes/", $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/themes/", true, true);
        return true;
    }

    function UnInstallFiles(){
        if( is_dir( $p = $_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/modules/' . self::MODULE_ID . '/admin' ) ){
            if( $dir = opendir( $p ) ){
                while( false !== $item = readdir( $dir ) ){
                    if( $item == '..' || $item == '.' || $item == 'menu.php'){
                        continue;
                    }
                    unlink( $_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/admin/' . self::MODULE_ID . '_' . $item );
                }
                closedir( $dir );
            }
        }
        if( is_dir( $p = $_SERVER[ 'DOCUMENT_ROOT' ] . '/bitrix/modules/' . self::MODULE_ID . '/install/components' ) ){
            if( $dir = opendir( $p ) ){
                while( false !== $item = readdir( $dir ) ){
                    if( $item == '..' || $item == '.' || !is_dir( $p0 = $p . '/' . $item ) ){
                        continue;
                    }

                    $dir0 = opendir( $p0 );
                    while( false !== $item0 = readdir( $dir0 ) ){
                        if( $item0 == '..' || $item0 == '.' ){
                            continue;
                        }
                        DeleteDirFilesEx( '/local/components/' . $item . '/' . $item0 );
                    }
                    closedir( $dir0 );
                }
                closedir( $dir );
            }
        }

        DeleteDirFilesEx ( '/bitrix/js/'.self::MODULE_ID );
        DeleteDirFilesEx ( '/bitrix/themes/.default/'.self::MODULE_ID.'.css' );
        DeleteDirFilesEx ( '/bitrix/themes/.default/icons/'.self::MODULE_ID );
        return true;
    }


    function DoInstall(){
        ModuleManager::registerModule( self::MODULE_ID );
        $this->InstallFiles();
        $this->InstallEvents();
        $this->InstallDB();

        global $APPLICATION, $step;
        $step = intval( $step );
        if( $step < 2 ){
            $APPLICATION->IncludeAdminFile( Loc::getMessage( SEO_CHPU_LITE_PREFIX.'INST_TITLE' ), $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/".self::MODULE_ID."/install/step1.php" );
        }

        return true;
    }

    function DoUninstall(){
        global $APPLICATION, $step;
        $step = intval( $step );
        if( $step < 2 ){
            $APPLICATION->IncludeAdminFile( Loc::getMessage( SEO_CHPU_LITE_PREFIX.'UNST_TITLE' ), $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/".self::MODULE_ID."/install/unstep1.php" );
        }elseif( $step == 2 ){
            $this->UnInstallDB(
                array(
                    "savedata" => $_REQUEST[ "savedata" ],
                )
            );
            $this->UnInstallEvents();
            $this->UnInstallFiles();
            ModuleManager::unRegisterModule(self::MODULE_ID);
            $GLOBALS[ "errors" ] = $this->errors;
            $APPLICATION->IncludeAdminFile( Loc::getMessage( SEO_CHPU_LITE_PREFIX.'UNST_TITLE' ), $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/".self::MODULE_ID."/install/unstep2.php" );
        }
    }
}
?>