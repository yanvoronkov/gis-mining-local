<?
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

require_once(__DIR__.'/../define.php');

if($ex = $APPLICATION->GetException())
	\CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else{
	\CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
    if (\Bitrix\Main\Loader::includeModule(SEO_CHPU_LITE)) {
        ?><p><a href="<?=$urlList?>"><?=GetMessage(SEO_CHPU_LITE_PREFIX."TO_SETTINGS" )?></a></p><?php
    }
}
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>