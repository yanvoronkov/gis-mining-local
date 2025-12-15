<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
$this->setFrameMode(false);

if($arResult['FILE_PATH'])
{
	?>
	<a href="<?echo htmlspecialcharsbx($arResult['FILE_PATH'])?>"><?echo GetMessage("ESOL_EE_DOWNLOAD_FILE")?></a>
	<?
}
?>