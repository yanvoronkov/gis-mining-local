<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CKDAExportProfileDB extends CKDAExportProfileAll {
	protected static $moduleId = 'esol.importexportexcel';
	protected static $moduleFilePrefix = 'esol_export_excel';
	protected static $moduleSubDir = 'export/';
	private $errors = array();
	private $entity = false;
	private $pid = null;
	private $exportMode = null;
	
	function __construct($suffix='')
	{
		$this->suffix = $suffix;
		$this->pathProfiles = dirname(__FILE__).'/../../profiles'.(strlen($suffix) > 0 ? '_'.$suffix : '').'/';
		$this->CheckStorage();
		
		$upDir = $_SERVER["DOCUMENT_ROOT"].'/upload/';
		$upTmpDir = $upDir.'tmp/';
		$this->tmpdir = $upTmpDir.static::$moduleId.'/'.static::$moduleSubDir;
		$this->uploadDir = $upDir.static::$moduleId.'/';
		
		foreach(array($upDir, $this->uploadDir, $upTmpDir, $this->tmpdir) as $k=>$v)
		{
			CheckDirPath($v);
			$i = 0;
			while(++$i < 10 && strlen($v) > 0 && !file_exists($v) && dirname($v)!=$v)
			{
				$v = dirname($v);
			}
			if(strlen($v) > 0 && file_exists($v) && !is_writable($v))
			{
				$this->errors[] = sprintf(Loc::getMessage('KDA_EE_DIR_NOT_WRITABLE'), $v);
			}
		}
		
		$this->tmpdir = realpath($this->tmpdir).'/';
		$this->uploadDir = realpath($this->uploadDir).'/';
	}
	
	public function GetErrors()
	{
		if(!isset($this->errors) || !is_array($this->errors)) $this->errors = array();
		return implode('<br>', array_unique($this->errors));
	}
	
	public function CheckStorage()
	{
		$optionName = ToUpper(static::$moduleSubDir).'DB_STRUCT_VERSION_'.(strlen($this->suffix) > 0 ? ToUpper($this->suffix) : 'IBLOCK');
		$moduleVersion = false;
		if(is_callable(array('\Bitrix\Main\ModuleManager', 'getVersion')))
		{
			$moduleVersion = \Bitrix\Main\ModuleManager::getVersion(static::$moduleId);
			if($moduleVersion==\Bitrix\Main\Config\Option::get(static::$moduleId, $optionName)) return;
		}
		
		/*Security filter*/
		if(Loader::includeModule('security') && class_exists('\CSecurityFilterMask'))
		{
			$mask = '/bitrix/admin/'.static::$moduleFilePrefix.'*';
			$findMask = false;
			$arMasks = array();
			$dbRes = \CSecurityFilterMask::GetList();
			while($arr = $dbRes->Fetch())
			{
				$arr['MASK'] = $arr['FILTER_MASK'];
				unset($arr['FILTER_MASK']);
				if($arr['MASK']==$mask) $findMask = true;
				if(strlen($arr['SITE_ID'])==0) $arr['SITE_ID'] = 'NOT_REF';
				$arMasks[] = $arr;
			}
			if(!$findMask)
			{
				$arMasks['n0'] = array('MASK'=>$mask, 'SITE_ID'=>'NOT_REF');
				\CSecurityFilterMask::Update($arMasks);
			}
		}
		/*Security filter*/
		
		/*Security addon*/
		$arFiles = array(
			'/bitrix/admin/esol_allimportexport_cron_settings.php' => '/bitrix/modules/esol.allimportexport/admin/cron_settings.php',
			'/bitrix/admin/esol_export_excel_cron_settings.php' => '/bitrix/modules/esol.importexportexcel/admin/iblock_export_excel_cron_settings.php',
			'/bitrix/admin/esol_import_excel_cron_settings.php' => '/bitrix/modules/esol.importexportexcel/admin/iblock_import_excel_cron_settings.php',
			'/bitrix/admin/esol_import_xml_cron_settings.php' => '/bitrix/modules/esol.importxml/admin/import_xml_cron_settings.php',
			'/bitrix/admin/esol_export_xml_cron_settings.php' => '/bitrix/modules/esol.exportxml/admin/export_xml_cron_settings.php',
			'/bitrix/admin/esol_massedit_profile.php' => '/bitrix/modules/esol.massedit/admin/profile.php',
			'/bitrix/admin/kda_export_excel_cron_settings.php' => '/bitrix/modules/kda.exportexcel/admin/iblock_export_excel_cron_settings.php',
			'/bitrix/admin/kda_import_excel_cron_settings.php' => '/bitrix/modules/kda.importexcel/admin/iblock_import_excel_cron_settings.php'
		);
		foreach($arFiles as $f1=>$f2)
		{
			$secBlock = "<?php\r\n".
				"if(isset(\$_REQUEST['path']) && strlen(\$_REQUEST['path']) > 0 || !file_exists(\$_SERVER['DOCUMENT_ROOT'].'".$f2."'))\r\n".
				"{\r\n".
				"	header((stristr(php_sapi_name(), 'cgi') !== false ? 'Status: ' : \$_SERVER['SERVER_PROTOCOL'].' ').'403 Forbidden');\r\n".
				"	die();\r\n".
				"}\r\n".
				"?>";
			$fileBlock = "<?php\r\n".
					"require(\$_SERVER['DOCUMENT_ROOT'].'".$f2."');\r\n".
					"?>";
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$f1))
			{
				file_put_contents($_SERVER['DOCUMENT_ROOT'].$f1, $secBlock.$fileBlock);
			}
			elseif(($c = file_get_contents($_SERVER['DOCUMENT_ROOT'].$f1)) && str_replace("\r", '', trim($c))==str_replace("\r", '', $fileBlock))
			{
				file_put_contents($_SERVER['DOCUMENT_ROOT'].$f1, $secBlock.trim($c));
			}
		}
		/*/Security addon*/
		
		$profileEntity = $this->GetEntity();
		$tblName = $profileEntity->getTableName();
		$conn = $profileEntity->getEntity()->getConnection();
		if(!$conn->isTableExists($tblName))
		{
			$profileEntity->getEntity()->createDbTable();
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `PARAMS` `PARAMS` mediumtext DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE_START` `DATE_START` datetime DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE_FINISH` `DATE_FINISH` datetime DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `SORT` `SORT` int(11) NOT NULL DEFAULT "500"');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `GROUP_ID` `GROUP_ID` int(11) DEFAULT NULL');
			
			$this->CheckTableEncoding($conn, $tblName);
			
			if(file_exists($this->pathProfiles))
			{
				$profileFs = new CKDAExportProfileFS($this->suffix);
				$arProfiles = $profileFs->GetList();
				foreach($arProfiles as $profileId=>$profileName)
				{
					$arParams = $profileFs->GetByID($profileId);
					$profileEntity::Add(array(
						'ID' => ($profileId + 1),
						'NAME' => substr($profileName, 0, 255),
						'PARAMS' => self::EncodeProfileParams($arParams)
					));
				}
			}
		}
		else
		{
			$isNewFields = false;
			$arDbFields = array();
			$dbRes = $conn->query("SHOW COLUMNS FROM `" . $tblName . "`");
			while($arr = $dbRes->Fetch())
			{
				$arDbFields[] = $arr['Field'];
			}
			$fields = $profileEntity->getEntity()->getScalarFields();
			$helper = $conn->getSqlHelper();
			$prevField = 'ID';
			foreach($fields as $columnName => $field)
			{
				$realColumnName = $field->getColumnName();
				if(!in_array($realColumnName, $arDbFields))
				{
					$conn->query('ALTER TABLE '.$helper->quote($tblName).' ADD COLUMN '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT NULL AFTER '.$helper->quote($prevField));
					if($field->getDefaultValue())
					{
						$conn->query('ALTER TABLE '.$helper->quote($tblName).' CHANGE COLUMN '.$helper->quote($realColumnName).' '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT "'.$helper->forSql($field->getDefaultValue()).'"');
						$conn->query('UPDATE '.$helper->quote($tblName).' SET '.$helper->quote($realColumnName).'="'.$helper->forSql($field->getDefaultValue()).'"');
					}
					$isNewFields = true;
				}
				$prevField = $realColumnName;
			}
			if($isNewFields) $this->CheckTableEncoding($conn, $tblName);
		}
		
		/*profile_changes*/
		$tEntity = new \Bitrix\KdaImportexcel\ProfileChangesTable();
		$tblName = $tEntity->getTableName();
		$conn = $tEntity->getEntity()->getConnection();
		if(!$conn->isTableExists($tblName))
		{
			$tEntity->getEntity()->createDbTable();
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `ID` `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE` `DATE` datetime DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `PARAMS` `PARAMS` mediumtext DEFAULT NULL');
			$conn->createIndex($tblName, 'ix_profile_id_profile_type', array('PROFILE_ID', 'PROFILE_TYPE'));
			$this->CheckTableEncoding($conn, $tblName);
		}
		/*/profile_changes*/
		
		/*profile_group*/
		$tEntity = new \Bitrix\KdaImportexcel\ProfileGroupTable();
		$tblName = $tEntity->getTableName();
		$conn = $tEntity->getEntity()->getConnection();
		if(!$conn->isTableExists($tblName))
		{
			$tEntity->getEntity()->createDbTable();
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `SORT` `SORT` int(11) NOT NULL DEFAULT "500"');
			$conn->createIndex($tblName, 'ix_profile_id_profile_type', array('ACTIVE', 'PROFILE_TYPE', 'SORT'));
			$this->CheckTableEncoding($conn, $tblName);
		}
		/*/profile_group*/
		
		/*Old iblock files*/
		$arFiles = array(
			array(
				'OLD' => '/bitrix/modules/iblock/lib/element.php',
				'NEW' => '/bitrix/modules/iblock/lib/elementtable.php',
			),
			array(
				'OLD' => '/bitrix/modules/iblock/lib/section.php',
				'NEW' => '/bitrix/modules/iblock/lib/sectiontable.php',
			)
		);

		foreach($arFiles as $arFileGroup)
		{
			$fn1 = $_SERVER['DOCUMENT_ROOT'].$arFileGroup['OLD'];
			$fn2 = $_SERVER['DOCUMENT_ROOT'].$arFileGroup['NEW'];
			if(file_exists($fn1) && is_file($fn1) && file_exists($fn2) && is_file($fn2))
			{
				$arLines = array(
					'namespace Bitrix\Iblock;',
					'class '.str_replace('.php', '', end(explode('/', $arFileGroup['OLD']))).'Table extends'
				);
				$find = true;
				foreach(array($fn1, $fn2) as $fn)
				{
					$c = file_get_contents($fn);
					foreach($arLines as $line)
					{
						if(!preg_match("/[\r\n]".preg_quote($line, '/')."/i", $c)) $find = false;
					}
				}
				if($find) rename($fn1, str_replace('.php', '_.php', $fn1));
			}
		}
		/*/Old iblock files*/
		
		if($moduleVersion)
		{
			\Bitrix\Main\Config\Option::set(static::$moduleId, $optionName, $moduleVersion);
		}
	}
	
	private function CheckTableEncoding($conn, $tblName)
	{
		$res = $conn->query('SHOW VARIABLES LIKE "character_set_connection"');
		$f = $res->fetch();
		$charset = trim($f['Value']);

		$res = $conn->query('SHOW VARIABLES LIKE "collation_connection"');
		$f = $res->fetch();
		$collation = trim($f['Value']);
		$charset2 = $this->GetCharsetByCollation($conn, $collation);
		
		$res0 = $conn->query('SHOW CREATE TABLE `' . $tblName . '`');
		$f0 = $res0->fetch();
		
		if (preg_match('/DEFAULT CHARSET=([a-z0-9\-_]+)/i', $f0['Create Table'], $regs))
		{
			$t_charset = $regs[1];
			if (preg_match('/COLLATE=([a-z0-9\-_]+)/i', $f0['Create Table'], $regs))
				$t_collation = $regs[1];
			else
			{
				$res0 = $conn->query('SHOW CHARSET LIKE "' . $t_charset . '"');
				$f0 = $res0->fetch();
				$t_collation = $f0['Default collation'];
			}
		}
		else
		{
			$res0 = $conn->query('SHOW TABLE STATUS LIKE "' . $tblName . '"');
			$f0 = $res0->fetch();
			if (!$t_collation = $f0['Collation'])
				return;
			$t_charset = $this->GetCharsetByCollation($conn, $t_collation);
		}
		
		if ($charset != $t_charset)
		{
			$conn->query('ALTER TABLE `' . $tblName . '` CHARACTER SET ' . $charset);
		}
		if ($t_collation != $collation)
		{	// table collation differs
			$conn->query('ALTER TABLE `' . $tblName . '` COLLATE ' . $collation);
		}
		
		$arFix = array();
		$res0 = $conn->query("SHOW FULL COLUMNS FROM `" . $tblName . "`");
		while($f0 = $res0->fetch())
		{
			$f_collation = $f0['Collation'];
			if ($f_collation === NULL || $f_collation === "NULL")
				continue;

			$f_charset = $this->GetCharsetByCollation($conn, $f_collation);
			if ($charset != $f_charset && $charset2 != $f_charset)
			{
				$arFix[] = ' MODIFY `'.$f0['Field'].'` '.$f0['Type'].' CHARACTER SET '.$charset.($collation != $f_collation ? ' COLLATE '.$collation : '').($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL').
						($f0['Default'] === NULL ? ($f0['Null'] == 'YES' ? ' DEFAULT NULL ' : '') : ' DEFAULT '.($f0['Type'] == 'timestamp' && $f0['Default'] == 'CURRENT_TIMESTAMP' ? $f0['Default'] : '"'.$conn->getSqlHelper()->forSql($f0['Default']).'"')).' '.$f0['Extra'];
			}
			elseif ($collation != $f_collation)
			{
				$arFix[] = ' MODIFY `'.$f0['Field'].'` '.$f0['Type'].' COLLATE '.$collation.($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL').
						($f0['Default'] === NULL ? ($f0['Null'] == 'YES' ? ' DEFAULT NULL ' : '') : ' DEFAULT '.($f0['Type'] == 'timestamp' && $f0['Default'] == 'CURRENT_TIMESTAMP' ? $f0['Default'] : '"'.$conn->getSqlHelper()->forSql($f0['Default']).'"')).' '.$f0['Extra'];
			}
		}

		if(count($arFix))
		{
			$conn->query('ALTER TABLE `'.$tblName.'` '.implode(",\n", $arFix));
		}
	}
	
	private function GetCharsetByCollation($conn, $collation)
	{
		static $CACHE;
		if (!$c = &$CACHE[$collation])
		{
			$res0 = $conn->query('SHOW COLLATION LIKE "' . $collation . '"');
			$f0 = $res0->Fetch();
			$c = $f0['Charset'];
		}
		return $c;
	}
	
	private function GetEntity()
	{
		if(!$this->entity)
		{
			if($this->suffix=='highload')
			{
				$this->entity = new \Bitrix\KdaExportexcel\ProfileHlTable();
			}
			else
			{
				$this->entity = new \Bitrix\KdaExportexcel\ProfileTable();
			}
		}
		return $this->entity;
	}
	
	public function GetList($arFilter=array(), $groups = false)
	{		
		if(!is_array($arFilter)) $arFilter = array();
		if(empty($arFilter)) $arFilter = array('ACTIVE'=>'Y');
		$arProfiles = array();
		$profileEntity = $this->GetEntity();
		if($groups)
		{
			$dbRes = $profileEntity::getList(array(
				'select'=>array('ID', 'NAME', 'GGROUP_NAME'=>'GROUP.NAME', 'GGROUP_ID'=>'GROUP.ID'), 
				'runtime' => array('GSORT' => array('data_type'=>'integer', 'expression' => array('(CASE WHEN %s IS NULL then 1 ELSE 0 END)', 'GROUP.ID'))),
				'order'=>array('GSORT'=>'ASC', 'GROUP.SORT'=>'ASC', 'GROUP.ID'=>'ASC', 'SORT'=>'ASC', 'ID'=>'ASC'), 
				'filter'=>$arFilter));
			while($arr = $dbRes->Fetch())
			{
				if(!$arr['GGROUP_ID'])
				{
					$arr['GGROUP_ID'] = 0;
					if(!empty($arProfiles)) $arr['GGROUP_NAME'] = Loc::getMessage("KDA_EE_PROFILES_WITHOUT_GROUP");
				}
				if(!array_key_exists($arr['GGROUP_ID'], $arProfiles))
				{
					$arProfiles[$arr['GGROUP_ID']] = array(
						'NAME' => $arr['GGROUP_NAME'],
						'LIST' => array()
					);
				}
				$arProfiles[$arr['GGROUP_ID']]['LIST'][$arr['ID'] - 1] = $arr['NAME'];
			}
		}
		else 
		{
			$dbRes = $profileEntity::getList(array('select'=>array('ID', 'NAME'), 'order'=>array('SORT'=>'ASC', 'ID'=>'ASC'), 'filter'=>$arFilter));
			while($arr = $dbRes->Fetch())
			{
				$arProfiles[$arr['ID'] - 1] = $arr['NAME'];
			}
		}
		
		return $arProfiles;
	}
	
	public function GetByID($ID)
	{
		$profileEntity = $this->GetEntity();
		$arProfile = $profileEntity::getList(array('filter'=>array('ID'=>($ID + 1)), 'select'=>array('PARAMS')))->fetch();
		if($arProfile && $arProfile['PARAMS'])
		{
			$arProfile = self::DecodeProfileParams($arProfile['PARAMS']);
		}
		if(!is_array($arProfile)) $arProfile = array();
		
		return $arProfile;
	}
	
	public function GetFieldsByID($ID)
	{
		if(!is_numeric($ID)) return array();
		$profileEntity = $this->GetEntity();
		$arProfile = $profileEntity::getList(array('filter'=>array('ID'=>($ID + 1))))->fetch();
		unset($arProfile['PARAMS']);
		
		return $arProfile;
	}
	
	public function Add($name)
	{
		global $APPLICATION;
		$APPLICATION->ResetException();
		
		$name = trim($name);
		if(strlen($name)==0)
		{
			$APPLICATION->throwException(Loc::getMessage("KDA_EE_NOT_SET_PROFILE_NAME"));
			return false;
		}
		
		$profileEntity = $this->GetEntity();
		
		if($arProfile = $profileEntity::getList(array('filter'=>array('NAME'=>$name), 'select'=>array('ID')))->fetch())
		{
			$APPLICATION->throwException(Loc::getMessage("KDA_EE_PROFILE_NAME_EXISTS"));
			return false;
		}
		
		$dbRes = $profileEntity::add(array('NAME'=>$name));
		if(!$dbRes->isSuccess())
		{
			$error = '';
			if($dbRes->getErrors())
			{
				foreach($dbRes->getErrors() as $errorObj)
				{
					$error .= $errorObj->getMessage().'. ';
				}
				$APPLICATION->throwException($error);
			}
			return false;
		}
		else
		{
			$ID = $dbRes->getId() - 1;			
			return $ID;
		}
	}
	
	public static function GetIgnoreChangesParams()
	{
		return array(
			'SETTINGS_DEFAULT' => array(
				'DATA_FILE',
				'URL_DATA_FILE',
				'LAST_MODIFIED_FILE',
				'OLD_DATA_FILE',
				'OLD_FILE_SIZE',
				'MAX_EXECUTION_TIME',
				'LAST_COOKIES',
				'LAST_UAGENT',
				'FILE_HASH'
			)
		);
	}
	
	public function IsChangesSetting(&$isChanges, $s1, $s2)
	{
		if($isChanges) return;
		if(!is_array($s1)) $s1 = array();
		if(!is_array($s2)) $s2 = array();
		$arIgnoreParams = self::GetIgnoreChangesParams();
		$arIgnoreKeys = array();
		foreach($arIgnoreParams as $k=>$v)
		{
			foreach($v as $v2)
			{
				$arIgnoreKeys[$v2] = '';
			}
		}
		$s1 = $this->KSortParams(array_diff_key($s1, $arIgnoreKeys));
		$s2 = $this->KSortParams(array_diff_key($s2, $arIgnoreKeys));
		if(serialize($s1)!=serialize($s2)) $isChanges = true;
	}
	
	public function KSortParams($a)
	{
		ksort($a);
		foreach($a as $k=>$v)
		{
			if(is_array($v)) $a[$k] = $this->KSortParams($v);
		}
		return $a;
	}
	
	public function GetChangesClass()
	{
		return '\Bitrix\KdaImportexcel\ProfileChangesTable';
	}
	
	public function GetChangesProfileType()
	{
		$changesClass = $this->GetChangesClass();
		$profileType = $changesClass::TYPE_EXPORT_IBLOCK;
		if($this->suffix=='highload') $profileType = $changesClass::TYPE_EXPORT_HLBLOCK;
		return $profileType;
	}
	
	public function SaveChangesSettings($PROFILE_ID, $arParams)
	{
		$changesClass = $this->GetChangesClass();
		$profileType = $this->GetChangesProfileType();
		
		$arIgnoreParams = self::GetIgnoreChangesParams();
		foreach($arIgnoreParams as $k=>$v)
		{
			foreach($v as $v2)
			{
				if(isset($arParams[$k]) && array_key_exists($v2, $arParams[$k])) unset($arParams[$k][$v2]);
			}
		}
		$params = self::EncodeProfileParams($arParams);
		
		$dbRes = $changesClass::getList(array('filter'=>array('=PROFILE_ID'=>$PROFILE_ID, '=PROFILE_TYPE'=>$profileType), 'order'=>array('ID'=>'DESC'), 'select'=>array('ID', 'PARAMS')));
		while($arr = $dbRes->Fetch())
		{
			if($params==$arr['PARAMS']) $changesClass::delete($arr['ID']);
		}
		
		$changesClass::add(array(
			'PROFILE_ID' => $PROFILE_ID,
			'PROFILE_TYPE' => $profileType,
			'USER_ID' => ($GLOBALS['USER']->GetID() ? $GLOBALS['USER']->GetID() : 0),
			'DATE' => new \Bitrix\Main\Type\DateTime(),
			'PARAMS' => $params
		));
		$limit = 10;
		$dbRes = $changesClass::getList(array('filter'=>array('=PROFILE_ID'=>$PROFILE_ID, '=PROFILE_TYPE'=>$profileType), 'order'=>array('ID'=>'DESC'), 'select'=>array('ID'), 'limit'=>$limit, 'offset'=>$limit));
		while($arr = $dbRes->Fetch())
		{
			$changesClass::delete($arr['ID']);
		}
	}
	
	public function GetChangesList($PROFILE_ID)
	{
		$changesClass = $this->GetChangesClass();
		$profileType = $this->GetChangesProfileType();
		$arData = array();
		$dbRes = $changesClass::getList(array('filter'=>array('=PROFILE_ID'=>$PROFILE_ID, '=PROFILE_TYPE'=>$profileType), 'order'=>array('ID'=>'DESC'), 'select'=>array('ID', 'DATE')));
		while($arr = $dbRes->Fetch())
		{
			if(is_object($arr['DATE'])) $arr['DATE'] = $arr['DATE']->toString();
			$arData[] = $arr;
		}
		return $arData;
	}
	
	public function RestoreFromChanges($PROFILE_ID, $POINT_ID)
	{
		$PROFILE_ID = (int)$PROFILE_ID;
		$POINT_ID = (int)$POINT_ID;
		if($POINT_ID<=0 || $PROFILE_ID<=0) return;
		$changesClass = $this->GetChangesClass();
		$profileType = $this->GetChangesProfileType();
		if($arPoint = $changesClass::getList(array('filter'=>array('=ID'=>(int)$POINT_ID)))->Fetch())
		{
			if($PROFILE_ID!=$arPoint['PROFILE_ID'] || $profileType!=$arPoint['PROFILE_TYPE']) return;
			$arCurParams = $this->GetByID($PROFILE_ID - 1);
			$this->SaveChangesSettings($PROFILE_ID, $arCurParams);
			$arParams = self::DecodeProfileParams($arPoint['PARAMS']);
			/*if(isset($arCurParams['SETTINGS_DEFAULT']['DATA_FILE']))
			{
				$arParams['SETTINGS_DEFAULT']['DATA_FILE'] = $arCurParams['SETTINGS_DEFAULT']['DATA_FILE'];
			}*/
			$profileEntity = $this->GetEntity();
			$profileEntity::update($PROFILE_ID, array('PARAMS'=>self::EncodeProfileParams($arParams)));
		}
	}
	
	public function Update($ID, $settigs_default, $settings, $extrasettings=null)
	{
		$arProfile = $arOldProfile = $this->GetByID($ID);
		$isChanges = false;
		if(is_array($settigs_default) && !empty($settigs_default) && ($settigs_default['IBLOCK_ID'] > 0 || $settigs_default['HIGHLOADBLOCK_ID'] > 0))
		{
			$this->IsChangesSetting($isChanges, $arProfile['SETTINGS_DEFAULT'], $settigs_default);
			$arProfile['SETTINGS_DEFAULT'] = $settigs_default;
		}
		if(is_array($settings) && !empty($settings))
		{
			$this->IsChangesSetting($isChanges, $arProfile['SETTINGS'], $settings);
			$arProfile['SETTINGS'] = $settings;
		}
		if(isset($extrasettings) && is_array($extrasettings))
		{
			$this->IsChangesSetting($isChanges, $arProfile['EXTRASETTINGS'], $extrasettings);
			$arProfile['EXTRASETTINGS'] = $extrasettings;
		}
		$profileEntity = $this->GetEntity();
		$profileEntity::update(($ID+1), array('PARAMS'=>self::EncodeProfileParams($arProfile)));
		if($isChanges) $this->SaveChangesSettings(($ID+1), $arOldProfile);
	}
	
	public function UpdateExtra($ID, $extrasettings)
	{
		$arProfile = $arOldProfile = $this->GetByID($ID);
		if(!is_array($extrasettings)) $extrasettings = array();
		$isChanges = false;
		$this->IsChangesSetting($isChanges, $arProfile['EXTRASETTINGS'], $extrasettings);
		$arProfile['EXTRASETTINGS'] = $extrasettings;
		$profileEntity = $this->GetEntity();
		$profileEntity::update(($ID+1), array('PARAMS'=>self::EncodeProfileParams($arProfile)));
		if($isChanges) $this->SaveChangesSettings(($ID+1), $arOldProfile);
	}
	
	public function Delete($ID)
	{
		$profileEntity = $this->GetEntity();
		$profileEntity::delete($ID+1);
	}
	
	public function Copy($ID)
	{
		$profileEntity = $this->GetEntity();
		$arProfile = $profileEntity::getList(array('filter'=>array('ID'=>($ID + 1)), 'select'=>array('NAME', 'PARAMS')))->fetch();
		if(!$arProfile) return false;
		
		$newName = $arProfile['NAME'].Loc::getMessage("KDA_EE_PROFILE_COPY");
		$arParams = self::DecodeProfileParams($arProfile['PARAMS']);
		if(true)
		{
			$ext = trim($arParams['SETTINGS_DEFAULT']['FILE_EXTENSION']);
			while(($path = '/upload/export_'.mt_rand().'.'.$ext) && file_exists($_SERVER['DOCUMENT_ROOT'].$path)){}
			$arParams['SETTINGS_DEFAULT']['FILE_PATH'] = $path;
			$arProfile['PARAMS'] = self::EncodeProfileParams($arParams);
		}
		$dbRes = $profileEntity::add(array('NAME'=>$newName, 'PARAMS'=>$arProfile['PARAMS']));
		if(!$dbRes->isSuccess())
		{
			$error = '';
			if($dbRes->getErrors())
			{
				foreach($dbRes->getErrors() as $errorObj)
				{
					$error .= $errorObj->getMessage().'. ';
				}
				$APPLICATION->throwException($error);
			}
			return false;
		}
		else
		{
			$newId = $dbRes->getId() - 1;			
			return $newId;
		}
	}
	
	public function Rename($ID, $name)
	{
		global $APPLICATION;
		$APPLICATION->ResetException();
		
		$name = trim($name);
		if(strlen($name)==0)
		{
			$APPLICATION->throwException(Loc::getMessage("KDA_EE_NOT_SET_PROFILE_NAME"));
			return false;
		}
		$profileEntity = $this->GetEntity();
		$profileEntity::update(($ID+1), array('NAME'=>$name));
	}
	
	public function UpdateFields($ID, $arFields)
	{
		if(!is_numeric($ID)) return false;
		$profileEntity = $this->GetEntity();
		$profileEntity::update(($ID+1), $arFields);
	}
	
	public function GetLastImportProfiles($limit=10)
	{
		$arProfiles = array();
		$limit = (int)$limit;
		if($limit<=0) $limit = 10;
		$profileEntity = $this->GetEntity();
		$dbRes = $profileEntity::getList(array('filter'=>array('!DATE_START'=>false), 'select'=>array('ID', 'NAME', 'DATE_START', 'DATE_FINISH'), 'order'=>array('DATE_START'=>'DESC'), 'limit'=>$limit));
		while($arr = $dbRes->Fetch())
		{
			$arr['ID'] = (int)$arr['ID'] - 1;
			$arProfiles[] = $arr;
		}
		return $arProfiles;
	}
	
	public function ApplyToLists($ID, $listFrom, $listTo)
	{
		if(!is_numeric($listFrom) || !is_array($listTo) || count($listTo)==0) return;
		$listTo = preg_grep('/^\d+$/', $listTo);
		if(count($listTo)==0) return;
		
		$arParams = $this->GetByID($ID);
		foreach($listTo as $key)
		{
			$arParams['SETTINGS']['FIELDS_LIST'][$key] = $arParams['SETTINGS']['FIELDS_LIST'][$listFrom];
			$arParams['EXTRASETTINGS'][$key] = $arParams['EXTRASETTINGS'][$listFrom];
		}
		$profileEntity = $this->GetEntity();
		$profileEntity::update(($ID+1), array('PARAMS'=>self::EncodeProfileParams($arParams)));
	}
	
	public function GetStatus($id, $bImported=false)
	{
		$arProfile = array();
		if(is_array($id))
		{
			$arProfile = $id;
			$id = $arProfile['ID'];
		}
		$tmpfile = $this->tmpdir.$id.($this->suffix ? '_'.$this->suffix : '').'.txt';
		if(!file_exists($tmpfile))
		{
			if($bImported)
			{
				if(empty($arProfile) || !empty($arProfile['DATE_FINISH'])) return array('STATUS'=>'OK', 'MESSAGE'=>Loc::getMessage("KDA_EE_STATUS_COMPLETE"));
				//else return array('STATUS'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_STATUS_FILE_ERROR"));
				else return array('STATUS'=>'OK', 'MESSAGE'=>'');
			}
			else return array('STATUS'=>'OK', 'MESSAGE'=>'');
		}
		$arParams = $this->GetProfileParamsByFile($tmpfile);
		$percent = round(((int)$arParams['total_read_line'] / max((int)$arParams['total_file_line'], 1)) * 100);
		$percent = min($percent, 99);
		$status = 'OK';
		if((time() - filemtime($tmpfile) < 4*60)) $statusText = Loc::getMessage("KDA_EE_STATUS_PROCCESS");
		else 
		{
			$statusText = Loc::getMessage("KDA_EE_STATUS_BREAK");
			$status = 'ERROR';
		}
		return array('STATUS'=>'ERROR', 'MESSAGE'=>$statusText.' ('.$percent.'%)');
	}
	
	public function GetProfileParamsByFile($tmpfile)
	{
		$content = file_get_contents($tmpfile);
		$maxLength = 10*1024;
		if(strlen($content) > $maxLength)
		{
			$arParams = array();
			$content = preg_replace('/(.)\{[^\}]*\}(.)/Uis', '$1$2', $content);
			if(preg_match_all("/'([^']*)':'([^']*)'/", $content, $m))
			{
				foreach($m[1] as $k2=>$v2)
				{
					$arParams[$v2] = $m[2][$k2];
				}
			}
		}
		else
		{
			$arParams = \KdaIE\Utils::JsObjectToPhp(file_get_contents($tmpfile));
		}
		return $arParams;
	}
	
	public function GetProcessedProfiles()
	{
		$arProfiles = $this->GetList();
		foreach($arProfiles as $k=>$v)
		{
			$tmpfile = $this->tmpdir.$k.($this->suffix ? '_'.$this->suffix : '').'.txt';
			if(!file_exists($tmpfile) || filesize($tmpfile)>10*1024 || (time() - filemtime($tmpfile) < 4*60) || filemtime($tmpfile) < mktime(0, 0, 0, 12, 24, 2015))
			{
				unset($arProfiles[$k]);
				continue;
			}
			
			$arParams = $this->GetProfileParamsByFile($tmpfile);
			if(!$arParams['total_file_line']) $arParams['total_file_line'] = 1;
			$percent = round(((int)$arParams['total_read_line'] / max((int)$arParams['total_file_line'], 1)) * 100);
			$percent = min($percent, 99);
			$arProfiles[$k] = array(
				'key' => $k,
				'name' => $v,
				'percent' => $percent
			);
		}
		if(!is_array($arProfiles)) $arProfiles = array();
		return $arProfiles;
	}
	
	public function RemoveProcessedProfile($id)
	{
		$tmpfile = $this->tmpdir.$id.($this->suffix ? '_'.$this->suffix : '').'.txt';
		if(file_exists($tmpfile))
		{
			$arParams = $this->GetProfileParamsByFile($tmpfile);
			if($arParams['tmpdir'])
			{
				DeleteDirFilesEx(substr($arParams['tmpdir'], strlen($_SERVER['DOCUMENT_ROOT'])));
			}
			unlink($tmpfile);
		}
	}
	
	public function GetProccessParams($id)
	{
		$tmpfile = $this->tmpdir.$id.($this->suffix ? '_'.$this->suffix : '').'.txt';
		if(file_exists($tmpfile))
		{
			$arParams = $this->GetProfileParamsByFile($tmpfile);
			$paramFile = $arParams['tmpdir'].'params.txt';
			$arParams = \KdaIE\Utils::Unserialize(file_get_contents($paramFile));
			return $arParams;
		}
		return false;
	}
	
	public function GetProccessParamsFromPidFile($id)
	{
		$tmpfile = $this->tmpdir.$id.($this->suffix ? '_'.$this->suffix : '').'.txt';
		if(file_exists($tmpfile))
		{
			if(time() - filemtime($tmpfile) < 3*60)
			{
				return false;
			}
			$arParams = $this->GetProfileParamsByFile($tmpfile);
			return $arParams;
		}
		return array();
	}
	
	public function SetExportParams($pid, $arParams=array())
	{
		$this->pid = $pid;
		$this->exportMode = ($arParams['EXPORT_MODE']=='CRON' ? 'CRON' : 'USER');
	}
	
	public function OnStartExport()
	{
		$this->UpdateFields($this->pid, array(
			'DATE_START' => new \Bitrix\Main\Type\DateTime(),
			'DATE_FINISH' => false
		));
		
		if(true)
		{
			if(COption::GetOptionString(static::$moduleId, 'NOTIFY_BEGIN_EXPORT', 'N')=='Y'
				&& (COption::GetOptionString(static::$moduleId, 'NOTIFY_MODE', 'NONE')=='ALL'
					|| (COption::GetOptionString(static::$moduleId, 'NOTIFY_MODE', 'NONE')=='CRON' && $this->exportMode=='CRON')))
			{
				$this->CheckEventOnBeginExport();
				$arEventData = array();
				$arProfile = $this->GetFieldsByID($this->pid);
				$arEventData['PROFILE_NAME'] = $arProfile['NAME'];
				$arEventData['EXPORT_START_DATETIME'] = (is_callable(array($arProfile['DATE_START'], 'toString')) ? $arProfile['DATE_START']->toString() : '');
				$arEventData['EMAIL_TO'] = COption::GetOptionString(static::$moduleId, 'NOTIFY_EMAIL');
				CEvent::Send('KDA_EXPORT_START', $this->GetDefaultSiteId(), $arEventData);
			}
		}
	}
	
	public function OnEndExport($file, $arParams, $arErrors=array())
	{
		$this->UpdateFields($this->pid, array(
			'DATE_FINISH'=>new \Bitrix\Main\Type\DateTime()
		));	
		
		if(true)
		{			
			$arEventData = array();
			if(is_array($arParams))
			{
				foreach($arParams as $k=>$v)
				{
					if(!is_array($v)) $arEventData[ToUpper($k)] = $v;
				}
			}
			$arEventData['TOTAL_LINE'] = $arEventData['TOTAL_READ_LINE'];
			$arProfile = $this->GetFieldsByID($this->pid);
			$arEventData['PROFILE_NAME'] = $arProfile['NAME'];
			$arEventData['FILE_PATH'] = \Bitrix\Main\IO\Path::convertPhysicalToLogical($file);
			$arEventData['EXPORT_START_DATETIME'] = (is_callable(array($arProfile['DATE_START'], 'toString')) ? $arProfile['DATE_START']->toString() : '');
			$arEventData['EXPORT_FINISH_DATETIME'] = (is_callable(array($arProfile['DATE_FINISH'], 'toString')) ? $arProfile['DATE_FINISH']->toString() : '');
			if(COption::GetOptionString(static::$moduleId, 'NOTIFY_END_EXPORT', 'N')=='Y'
				&& (COption::GetOptionString(static::$moduleId, 'NOTIFY_MODE', 'NONE')=='ALL'
					|| (COption::GetOptionString(static::$moduleId, 'NOTIFY_MODE', 'NONE')=='CRON' && $this->exportMode=='CRON')))
			{
				$this->CheckEventOnEndExport();
				$arEventData['EMAIL_TO'] = COption::GetOptionString(static::$moduleId, 'NOTIFY_EMAIL');
				$arEventData['ERRORS'] = implode("\r\n--------\r\n", $arErrors);
				CEvent::Send('KDA_EXPORT_END', $this->GetDefaultSiteId(), $arEventData);
			}
		}
	}
	
	public function GetDefaultSiteId()
	{
		if(!($arSite = \CSite::GetList(($by='sort'), ($order='asc'), array('DEFAULT'=>'Y'))->Fetch()))
			$arSite = \CSite::GetList(($by='sort'), ($order='asc'), array())->Fetch();
		return $arSite['ID'];
	}
	
	public function CheckEventOnBeginExport()
	{
		$eventName = 'KDA_EXPORT_START';
		$dbRes = CEventType::GetList(array('TYPE_ID'=>$eventName));
		if(!$dbRes->Fetch())
		{
			$et = new CEventType();
			$et->Add(array(
				"LID" => "ru",
				"EVENT_NAME" => $eventName,
				"NAME" => Loc::getMessage("KDA_EE_EVENT_EXPORT_START"),
				"DESCRIPTION" => 
					"#PROFILE_NAME# - ".Loc::getMessage("KDA_EE_EVENT_PROFILE_NAME")."\r\n".
					"#EXPORT_START_DATETIME# - ".Loc::getMessage("KDA_EE_EVENT_TIME_BEGIN")
				));
		}
		$dbRes = CEventMessage::GetList(($by='id'), ($order='desc'), array('TYPE_ID'=>$eventName));
		if(!$dbRes->Fetch())
		{
			$emess = new CEventMessage();
			$emess->Add(array(
				'ACTIVE' => 'Y',
				'EVENT_NAME' => $eventName,
				'LID' => $this->GetDefaultSiteId(),
				'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
				'EMAIL_TO' => '#EMAIL_TO#',
				'SUBJECT' => '#SITE_NAME#: '.Loc::getMessage("KDA_EE_EVENT_BEGIN_PROFILE").' "#PROFILE_NAME#"',
				'MESSAGE' => 
					Loc::getMessage("KDA_EE_EVENT_PROFILE_NAME").": #PROFILE_NAME#\r\n".
					Loc::getMessage("KDA_EE_EVENT_TIME_BEGIN").": #EXPORT_START_DATETIME#"
			));
		}
	}
	
	public function CheckEventOnEndExport()
	{
		$eventName = 'KDA_EXPORT_END';
		$dbRes = CEventType::GetList(array('TYPE_ID'=>$eventName));
		if(!$dbRes->Fetch())
		{
			$et = new CEventType();
			$et->Add(array(
				"LID" => "ru",
				"EVENT_NAME" => $eventName,
				"NAME" => Loc::getMessage("KDA_EE_EVENT_EXPORT_END"),
				"DESCRIPTION" => 
					"#PROFILE_NAME# - ".Loc::getMessage("KDA_EE_EVENT_PROFILE_NAME")."\r\n".
					"#EXPORT_START_DATETIME# - ".Loc::getMessage("KDA_EE_EVENT_TIME_BEGIN")."\r\n".
					"#EXPORT_FINISH_DATETIME# - ".Loc::getMessage("KDA_EE_EVENT_TIME_END")."\r\n".
					"#TOTAL_LINE# - ".Loc::getMessage("KDA_EE_EVENT_TOTAL_LINE")."\r\n"
				));
		}
		$dbRes = CEventMessage::GetList(($by='id'), ($order='desc'), array('TYPE_ID'=>$eventName));
		if(!$dbRes->Fetch())
		{
			$emess = new CEventMessage();
			$emess->Add(array(
				'ACTIVE' => 'Y',
				'EVENT_NAME' => $eventName,
				'LID' => $this->GetDefaultSiteId(),
				'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
				'EMAIL_TO' => '#EMAIL_TO#',
				'SUBJECT' => '#SITE_NAME#: '.Loc::getMessage("KDA_EE_EVENT_END_PROFILE").' "#PROFILE_NAME#"',
				'MESSAGE' => 
					Loc::getMessage("KDA_EE_EVENT_PROFILE_NAME").": #PROFILE_NAME#\r\n".
					Loc::getMessage("KDA_EE_EVENT_TIME_BEGIN").": #EXPORT_START_DATETIME#\r\n".
					Loc::getMessage("KDA_EE_EVENT_TIME_END").": #EXPORT_FINISH_DATETIME#\r\n".
					"\r\n".
					Loc::getMessage("KDA_EE_EVENT_TOTAL_LINE").": #TOTAL_LINE#"
			));
		}
	}
	
	public function OutputBackup()
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		
		$fileName = 'profiles_'.date('Y_m_d_H_i_s');
		$tempPath = \CFile::GetTempName('', bx_basename($fileName.'.zip'));
		$dir = rtrim(\Bitrix\Main\IO\Path::getDirectory($tempPath), '/').'/'.$fileName;
		\Bitrix\Main\IO\Directory::createDirectory($dir);
		
		$arFiles = array(
			'config' => $dir.'/config.txt',
			'data' => $dir.'/data.txt'
		);
		
		file_put_contents($arFiles['config'], base64_encode(serialize(
			array(
				'domain' => $_SERVER['HTTP_HOST'],
				'encoding' => \CKDAExportUtils::getSiteEncoding()
			)
		)));
		
		$handle = fopen($arFiles['data'], 'a');
		$profileEntity = $this->GetEntity();
		$dbRes = $profileEntity::getList(array('order'=>array('ID'=>'ASC'), 'select'=>array('ID', 'ACTIVE', 'NAME', 'PARAMS', 'SORT')));
		while($arProfile = $dbRes->Fetch())
		{
			foreach($arProfile as $k=>$v)
			{
				$arProfile[$k] = base64_encode($v);
			}
			fwrite($handle, base64_encode(serialize($arProfile))."\r\n");
		}
		fclose($handle);
		
		$zipObj = \CBXArchive::GetArchive($tempPath, 'ZIP');
		$zipObj->SetOptions(array(
			"COMPRESS" =>true,
			"ADD_PATH" => false,
			"REMOVE_PATH" => $dir.'/',
			"CHECK_PERMISSIONS" => false
		));
		$zipObj->Pack($dir.'/');
		
		foreach($arFiles as $file) unlink($file);
		rmdir($dir);
		
		header('Content-type: application/zip');
		header('Content-Transfer-Encoding: Binary');
		header('Content-length: '.filesize($tempPath));
		header('Content-disposition: attachment; filename="'.basename($tempPath).'"');
		readfile($tempPath);
		
		die();
	}
	
	public function GetProfilesFromBackup($arPFile)
	{
		if(!isset($arPFile) || !is_array($arPFile) || $arPFile['error'] > 0 || $arPFile['size'] < 1)
		{
			return array('TYPE'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_NOT_LOAD_FILE"));
		}
		$filename = $arPFile['name'];
		if(ToLower(\CKDAExportUtils::GetFileExtension($filename))!=='zip')
		{
			return array('TYPE'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_FILE_NOT_VALID"));
		}
		
		$tempPath = \CFile::GetTempName('', bx_basename($filename));
		$subdir = current(explode('.', $filename));
		if(strlen($subdir)==0) $subdir = 'backup';
		$dir = rtrim(\Bitrix\Main\IO\Path::getDirectory($tempPath), '/').'/'.$subdir;
		\Bitrix\Main\IO\Directory::createDirectory($dir);
		
		$zipObj = CBXArchive::GetArchive($arPFile['tmp_name'], 'ZIP');
		$zipObj->Unpack($dir.'/');
		
		$arFiles = array(
			'config' => $dir.'/config.txt',
			'data' => $dir.'/data.txt'
		);
		if(!file_exists($arFiles['config']) || !file_exists($arFiles['data']))
		{
			foreach($arFiles as $file) unlink($file);
			rmdir($dir);
			return array('TYPE'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_FILE_NOT_VALID"));
		}
		
		$profileEntity = $this->GetEntity();
		$encoding = \CKDAExportUtils::getSiteEncoding();
		
		$arProfiles = array();
		$arConfig = \KdaIE\Utils::Unserialize(base64_decode(file_get_contents($arFiles['config'])));
		$handle = fopen($arFiles['data'], "r");
		while(!feof($handle))
		{
			$buffer = trim(fgets($handle, 16777216));
			if(strlen($buffer) == 0) continue;			
			$arProfile = \KdaIE\Utils::Unserialize(base64_decode($buffer));
			if(!is_array($arProfile)) continue;
			foreach($arProfile as $k=>$v)
			{
				if(!in_array($k, array('ID', 'NAME')))
				{
					unset($arProfile[$k]);
					continue;
				}
				$v = base64_decode($v);
				if($encoding != $arConfig['encoding'])
				{
					$v = \Bitrix\Main\Text\Encoding::convertEncoding($v, $arConfig['encoding'], $encoding);
				}
				$arProfile[$k] = $v;
			}
			$arProfiles[] = $arProfile;
		}
		fclose($handle);
		foreach($arFiles as $file) unlink($file);
		rmdir($dir);
		
		return array('TYPE'=>'SUCCESS', 'PROFILES'=>$arProfiles);
	}
	
	public function RestoreBackup($arPFile, $arParams)
	{
		if(!isset($arPFile) || !is_array($arPFile) || $arPFile['error'] > 0 || $arPFile['size'] < 1)
		{
			return array('TYPE'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_NOT_LOAD_FILE"));
		}
		$filename = $arPFile['name'];
		if(ToLower(\CKDAExportUtils::GetFileExtension($filename))!=='zip')
		{
			return array('TYPE'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_FILE_NOT_VALID"));
		}
		
		$tempPath = \CFile::GetTempName('', bx_basename($filename));
		$subdir = current(explode('.', $filename));
		if(strlen($subdir)==0) $subdir = 'backup';
		$dir = rtrim(\Bitrix\Main\IO\Path::getDirectory($tempPath), '/').'/'.$subdir;
		\Bitrix\Main\IO\Directory::createDirectory($dir);
		
		$zipObj = CBXArchive::GetArchive($arPFile['tmp_name'], 'ZIP');
		$zipObj->Unpack($dir.'/');
		
		$arFiles = array(
			'config' => $dir.'/config.txt',
			'data' => $dir.'/data.txt'
		);
		if(!file_exists($arFiles['config']) || !file_exists($arFiles['data']))
		{
			foreach($arFiles as $file) unlink($file);
			rmdir($dir);
			return array('TYPE'=>'ERROR', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_FILE_NOT_VALID"));
		}
		
		$profileEntity = $this->GetEntity();
		$encoding = \CKDAExportUtils::getSiteEncoding();
		
		$arIds = array();
		if(is_array($arParams['IDS']) && !empty($arParams['IDS']) && !in_array('ALL', $arParams['IDS']))
		{
			$arIds = $arParams['IDS'];
		}
		
		if($arParams['RESTORE_TYPE']=='REPLACE' && empty($arIds))
		{
			$dbRes = $profileEntity::getList();
			while($arProfile = $dbRes->Fetch())
			{
				$profileEntity::delete($arProfile['ID']);
			}
		}
		
		$arConfig = \KdaIE\Utils::Unserialize(base64_decode(file_get_contents($arFiles['config'])));
		$handle = fopen($arFiles['data'], "r");
		while(!feof($handle))
		{
			$buffer = trim(fgets($handle, 16777216));
			if(strlen($buffer) == 0) continue;			
			$arProfile = \KdaIE\Utils::Unserialize(base64_decode($buffer));
			if(!is_array($arProfile)) continue;
			foreach($arProfile as $k=>$v)
			{
				$v = base64_decode($v);
				if($encoding != $arConfig['encoding'])
				{
					if($k=='PARAMS')
					{
						$v = self::DecodeProfileParams($v);
						$v = \Bitrix\Main\Text\Encoding::convertEncoding($v, $arConfig['encoding'], $encoding);
						$v = self::EncodeProfileParams($v);
					}
					else
					{
						$v = \Bitrix\Main\Text\Encoding::convertEncoding($v, $arConfig['encoding'], $encoding);
					}
				}
				$arProfile[$k] = $v;
			}
			if(!empty($arIds) && !in_array($arProfile['ID'], $arIds)) continue;
			
			if($arParams['RESTORE_TYPE']=='ADD') unset($arProfile['ID']);
			elseif(!empty($arIds))
			{
				if($arOldProfile = $profileEntity::getList(array('select'=>array('ID'), 'filter'=>array('NAME'=>$arProfile['NAME']), 'limit'=>1))->Fetch())
				{
					$profileEntity::delete($arOldProfile['ID']);
					$arProfile['ID'] = $arOldProfile['ID'];
				}
				else unset($arProfile['ID']);
			}
			$dbRes = $profileEntity::add($arProfile);
			/*if(!$dbRes->isSuccess())
			{
				$error = '';
				if($dbRes->getErrors())
				{
					foreach($dbRes->getErrors() as $errorObj)
					{
						$error .= $errorObj->getMessage().'. ';
					}
					$APPLICATION->throwException($error);
				}
			}
			else
			{
				$ID = $dbRes->getId();
			}*/
		}
		fclose($handle);
		foreach($arFiles as $file) unlink($file);
		rmdir($dir);
		
		return array('TYPE'=>'SUCCESS', 'MESSAGE'=>Loc::getMessage("KDA_EE_RESTORE_SUCCESS"));
	}
}