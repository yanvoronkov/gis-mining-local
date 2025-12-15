<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages( __FILE__ );

require_once(__DIR__.'/define.php');

$arClasses = array(
    SEO_CHPU_LITE_NAMESPACE_PREFIX.'Events' => "lib/Events.php",
    SEO_CHPU_LITE_NAMESPACE_PREFIX.'Main' => "lib/Main.php",
    SEO_CHPU_LITE_NAMESPACE_PREFIX.'Helper' => "lib/Helper.php",
    SEO_CHPU_LITE_NAMESPACE_PREFIX.'UserFieldEnumTable' => "lib/userfieldenumtable.php",
);

Loader::registerAutoLoadClasses(
    SEO_CHPU_LITE, $arClasses
);
?>