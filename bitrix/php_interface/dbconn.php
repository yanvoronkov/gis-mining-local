<?php
/* Ansible managed */

$DBDebug = false;
$DBDebugToFile = false;

// need for old distros
define('CACHED_b_lang', 3600);
define('CACHED_b_agent', 3600);
define('CACHED_b_lang_domain', 3600);

define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~(BX_FILE_PERMISSIONS | BX_DIR_PERMISSIONS) & 0777);

define("MYSQL_TABLE_TYPE", "INNODB");
define("SHORT_INSTALL", true);
define("VM_INSTALL", true);

define("BX_UTF", true);

define("BX_CRONTAB_SUPPORT", true);
define("BX_DISABLE_INDEX_PAGE", true);
define("BX_COMPRESSION_DISABLED", true);
define("BX_USE_MYSQLI", true);

define("BX_TEMPORARY_FILES_DIRECTORY", "/home/bitrix/.bx_temp/dbgis-mining/");

// --- Настройки для использования Memcache ---
define("BX_CACHE_TYPE", "memcache");
define("BX_CACHE_SID", $_SERVER["DOCUMENT_ROOT"] . "#01");
define("BX_MEMCACHE_HOST", "127.0.0.1");
define("BX_MEMCACHE_PORT", "11211");
?>
