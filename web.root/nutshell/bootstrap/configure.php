<?php
/*
 * ----------------------------------------------------------------------------
 * Ensure that we have a currently defined time zone.
 * This needs to be done very early in order to avoid Whoops quitting with
 * "It is not safe to rely on the system's timezone settings."
 * ----------------------------------------------------------------------------
 */
// @date_default_timezone_set(@date_default_timezone_get() ?: 'UTC');
// todo: handle timezone settings

/*
 * ----------------------------------------------------------------------------
 * The following constants need to load very early, because they're used
 * if we determine that we have an updated core, and they're also used to
 * determine where we grab our site config file from. So first we load them,
 * then we attempt to load the site config, then we pass through to an updated
 * core, should our site config point to that new core. Only then after that
 * do we continue loading this instance of concrete5.
 * ----------------------------------------------------------------------------
 */
defined('DISPATCHER_FILENAME') or define('DISPATCHER_FILENAME', 'index.php');
defined('DISPATCHER_FILENAME_CORE') or define('DISPATCHER_FILENAME_CORE', 'dispatcher.php');
defined('DIRNAME_APPLICATION') or define('DIRNAME_APPLICATION', 'application');
// defined('DIRNAME_UPDATES') or define('DIRNAME_UPDATES', 'updates');
defined('DIRNAME_CORE') or define('DIRNAME_CORE', 'nutshell');
defined('DIR_BASE') or define('DIR_BASE', str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME'])));
defined('DIR_APPLICATION') or define('DIR_APPLICATION', DIR_BASE . '/' . DIRNAME_APPLICATION);
defined('DIR_CONFIG_SITE') or define('DIR_CONFIG_SITE', DIR_APPLICATION . '/config');

/*
 * ----------------------------------------------------------------------------
 * Make sure you cannot call dispatcher.php directly.
 * ----------------------------------------------------------------------------
 */
if (basename($_SERVER['PHP_SELF']) === DISPATCHER_FILENAME_CORE) {
    die('Access Denied.');
}




