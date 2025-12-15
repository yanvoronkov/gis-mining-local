<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global string $DBType */

/** @global CDatabase $DB */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once(__DIR__.'/define.php');
require_once($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/main/include/prolog_admin_before.php");
Loc::loadMessages( __FILE__ );


if( !Loader::includeModule( SEO_CHPU_LITE ) ){
    $APPLICATION->AuthForm( Loc::getMessage( SEO_CHPU_LITE_PREFIX.'NOT_INSTALLED_MODULE' ) );
}

$APPLICATION->SetTitle( Loc::getMessage( SEO_CHPU_LITE_PREFIX.'CREATE_IBLOCK_TAB' ) );
require($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/main/include/prolog_admin_after.php");

$mid = htmlspecialchars($_REQUEST[ "mid" ]);
$iblock_id = htmlspecialchars($_REQUEST[ "iblock_id" ]);

$aTabs = array(
    array(
        "DIV"   => "create_iblock",
        "TAB"   => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'CREATE_IBLOCK_TAB' ),
        "ICON"  => "main_settings",
        "TITLE" => Loc::getMessage( SEO_CHPU_LITE_PREFIX.'CREATE_IBLOCK_TAB' )
    ),
);

$tabControl = new \CAdminTabControl( "tabControl", $aTabs );

if( $_SERVER[ "REQUEST_METHOD" ] == "POST" && !empty($_REQUEST['create_iblock']) && check_bitrix_sessid() ){
    $iblock_id = \Dwstroy\SeoChpuLite\Helper::getInstance()->createIblock(true);

    if( $iblock_id ){
        LocalRedirect( $APPLICATION->GetCurPage()."?mid=".SEO_CHPU_LITE."&lang=" . LANGUAGE_ID . "&tabControl_active_tab=" . urlencode( $_REQUEST[ "tabControl_active_tab" ] ) . "&iblock_id=".$iblock_id."&back_url_settings=" . urlencode( $_REQUEST[ "back_url_settings" ] ) );
    }else{
        LocalRedirect( $APPLICATION->GetCurPage()."?mid=".SEO_CHPU_LITE."&lang=" . LANGUAGE_ID . "&tabControl_active_tab=" . urlencode( $_REQUEST[ "tabControl_active_tab" ] ) . "&error=Y&back_url_settings=" . urlencode( $_REQUEST[ "back_url_settings" ] ) );
    }


}
?>
<form name="main_options" method="post" enctype="multipart/form-data"
      action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx( $mid ) ?>&amp;lang=<? echo LANG ?>">
<?= bitrix_sessid_post() ?>
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="100%" colspan="2" style="text-align: center">
            <?
            echo BeginNote();
            echo Loc::getMessage( SEO_CHPU_LITE_PREFIX.'FOR_WHAT_CREATE' );
            echo EndNote();
            ?>
        </td>
    </tr>
<?
if( !empty($iblock_id) ){
    ?>
    <tr>
        <td width="100%" colspan="2" style="text-align: center">
            <a target="_blank" href="/bitrix/admin/iblock_edit.php?type=<?=SEO_CHPU_LITE_NO_DOT?>&lang=<?=LANGUAGE_ID?>&ID=<?=$iblock_id?>&admin=Y"><?=Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_GO_TO_EDIT_IBLOCK' )?></a>
        </td>
    </tr>
    <?
}
if( $_REQUEST['error'] === 'Y' ){
    ?>
    <tr>
        <td width="100%" colspan="2" style="text-align: center">
            <?=Loc::getMessage( SEO_CHPU_LITE_PREFIX.'IBLOCK_CREATE_ERROR' )?>
        </td>

    </tr>
    <?
}
?>
    <tr>
        <td width="100%" colspan="2" style="text-align: center">
            <input name="create_iblock" type="submit" class="adm-btn adm-btn-save" value="<?=Loc::getMessage( SEO_CHPU_LITE_PREFIX.'CREATE_BTN' )?>">
        </td>
    </tr>
<? $tabControl->End(); ?>
    </form><?php
require($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/main/include/epilog_admin.php");