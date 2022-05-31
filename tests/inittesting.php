<?php
error_reporting(E_ALL & ~E_NOTICE);
session_set_cookie_params(604800);
session_start();

define('DIRNAME_CORE', 'nutshell');
define('DIR_PROJECT_ROOT','/dev/twoquakers/nutshell-1');
define('DIR_TEST_ROOT',DIR_PROJECT_ROOT.'/tests');
define('DIR_TEST_DATA',DIR_TEST_ROOT.'/data');
define('DIR_BASE',DIR_PROJECT_ROOT."/web.root");
define('DIR_APPLICATION', DIR_BASE . '/application');
define('DIR_CONFIG_SITE', DIR_APPLICATION . '/config');

include_once DIR_BASE.'\nutshell\src\tops\sys\TPath.php';
\Tops\sys\TPath::Initialize(DIR_BASE,'/application/config');
include_once DIR_CONFIG_SITE . "/peanut-bootstrap.php";
\Peanut\Bootstrap::initialize(DIR_BASE);
