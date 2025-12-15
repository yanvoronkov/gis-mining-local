<?php
$arUrlRewrite = array(
  26 =>
    array(
      'CONDITION' => '#^/catalog/([^/]+)/([a-z0-9_\\-]+)/calculator-dohodnosti/?(?:\\?(.*))?$#i',
      'RULE' => 'ELEMENT_CODE=$2&$3',
      'ID' => 'bitrix:catalog.element',
      'PATH' => '/catalog/detail_dohodnost.php',
      'SORT' => 1,
    ),
  20 =>
    array(
      'CONDITION' => '#^/catalog/videocard/filter/(.+?)/apply/?#',
      'RULE' => 'SMART_FILTER_PATH=$1',
      'ID' => 'bitrix:catalog.smart.filter',
      'PATH' => '/catalog/videocard/index.php',
      'SORT' => 2,
    ),
  19 =>
    array(
      'CONDITION' => '#^/catalog/asics/filter/(.+?)/apply/?#',
      'RULE' => 'SMART_FILTER_PATH=$1',
      'ID' => 'bitrix:catalog.smart.filter',
      'PATH' => '/catalog/asics/index.php',
      'SORT' => 2,
    ),
  21 =>
    array(
      'CONDITION' => '#^/catalog/videocard/([a-z0-9_\\-]+)/?(?:\\?(.*))?$#i',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => 'bitrix:catalog.element',
      'PATH' => '/catalog/videocard/detail.php',
      'SORT' => 3,
    ),
  23 =>
    array(
      'CONDITION' => '#^/catalog/asics/([a-z0-9_\\-]+)/?(?:\\?(.*))?$#i',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => 'bitrix:catalog.element',
      'PATH' => '/catalog/asics/detail.php',
      'SORT' => 3,
    ),
  18 =>
    array(
      'CONDITION' => '#^/catalog/product/([a-z0-9_\\-]+)/?(?:\\?(.*))?$#i',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => 'bitrix:catalog.element',
      'PATH' => '/catalog/detail.php',
      'SORT' => 4,
    ),
  /* 10 => 
  array (
    'CONDITION' => '#^/catalog/([^/]+)/([^/]+)/?(?:\\?(.*))?$#',
    'RULE' => 'SECTION_CODE=$1&ELEMENT_CODE=$2&$3',
    'ID' => 'bitrix:catalog.element',
    'PATH' => '/catalog/detail.php',
    'SORT' => 5,
  ), */
  22 =>
    array(
      'CONDITION' => '#^/smi-o-nas/([^/]+)/?(?:\\?(.*))?$#',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => '',
      'PATH' => '/smi-o-nas/detail.php',
      'SORT' => 5,
    ),
  13 =>
    array(
      'CONDITION' => '#^/our-blog/([^/]+)/?(?:\\?(.*))?$#',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => '',
      'PATH' => '/our-blog/detail/index.php',
      'SORT' => 5,
    ),
  24 =>
    array(
      'CONDITION' => '#^/news/([^/]+)/?(?:\\?(.*))?$#',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => '',
      'PATH' => '/news/detail.php',
      'SORT' => 5,
    ),
  11 =>
    array(
      'CONDITION' => '#^/catalog/([^/]+)/?(?:\\?(.*))?$#',
      'RULE' => 'SECTION_CODE=$1&$2',
      'ID' => 'bitrix:catalog.section',
      'PATH' => '/catalog/section.php',
      'SORT' => 20,
    ),
  12 =>
    array(
      'CONDITION' => '#^/catalog/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog.section.list',
      'PATH' => '/catalog/index.php',
      'SORT' => 30,
    ),
  5 =>
    array(
      'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
      'RULE' => 'alias=$1',
      'ID' => NULL,
      'PATH' => '/desktop_app/router.php',
      'SORT' => 100,
    ),
  4 =>
    array(
      'CONDITION' => '#^/video([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
      'RULE' => 'alias=$1&videoconf',
      'ID' => NULL,
      'PATH' => '/desktop_app/router.php',
      'SORT' => 100,
    ),
  1 =>
    array(
      'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
      'RULE' => 'componentName=$1',
      'ID' => NULL,
      'PATH' => '/bitrix/services/mobileapp/jn.php',
      'SORT' => 100,
    ),
  3 =>
    array(
      'CONDITION' => '#^/bitrix/services/ymarket/#',
      'RULE' => '',
      'ID' => '',
      'PATH' => '/bitrix/services/ymarket/index.php',
      'SORT' => 100,
    ),
  14 =>
    array(
      'CONDITION' => '#^/catalog/gotovyy-biznes/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog.section',
      'PATH' => '/catalog/gotovyy-biznes/index.php',
      'SORT' => 100,
    ),
  16 =>
    array(
      'CONDITION' => '#^/catalog/investicii/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog.section',
      'PATH' => '/catalog/investicii/index.php',
      'SORT' => 100,
    ),
  17 =>
    array(
      'CONDITION' => '#^/catalog/conteynery/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog.section',
      'PATH' => '/catalog/conteynery/index.php',
      'SORT' => 100,
    ),
  25 =>
    array(
      'CONDITION' => '#^/catalog/videocard/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog.section',
      'PATH' => '/catalog/videocard/index.php',
      'SORT' => 100,
    ),
  6 =>
    array(
      'CONDITION' => '#^/online(/?)([^/]*)#',
      'RULE' => '',
      'ID' => NULL,
      'PATH' => '/desktop_app/router.php',
      'SORT' => 100,
    ),
  0 =>
    array(
      'CONDITION' => '#^/stssync/calendar/#',
      'RULE' => '',
      'ID' => 'bitrix:stssync.server',
      'PATH' => '/bitrix/services/stssync/calendar/index.php',
      'SORT' => 100,
    ),
  15 =>
    array(
      'CONDITION' => '#^/catalog/gpu/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog.section',
      'PATH' => '/catalog/gpu/index.php',
      'SORT' => 100,
    ),
  2 =>
    array(
      'CONDITION' => '#^/rest/#',
      'RULE' => '',
      'ID' => NULL,
      'PATH' => '/bitrix/services/rest/index.php',
      'SORT' => 100,
    ),
  27 =>
    array(
      'CONDITION' => '#^/cases/([^/]+)/?(?:\\?(.*))?$#',
      'RULE' => 'ELEMENT_CODE=$1&$2',
      'ID' => '',
      'PATH' => '/cases/detail/index.php',
      'SORT' => 5,
    ),
);
