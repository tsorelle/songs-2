<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70209) {
    die("Nutshell requires PHP 7.2.9 to run.\nYou are running PHP " . PHP_VERSION . "\n");
}
error_reporting(E_ALL & ~E_NOTICE);
session_set_cookie_params(604800);
session_start();
require 'nutshell/bootstrap/configure.php';
require 'nutshell/src/cms/routing/RouteFinder.php';
if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
    $uri = preg_replace("/(^\/)|(\/$)/","",$_SERVER['REQUEST_URI']);
    if (\Nutshell\cms\RouteFinder::match($uri)) {
        unset($uri);
        include_once DIR_CONFIG_SITE . "/peanut-bootstrap.php";
        \Peanut\Bootstrap::initialize();

        // check to see if autoload succeeded
        if (!class_exists('\Nutshell\cms\router')) {
            throw new \Exception('Initialization failed');
        };

        if (\Nutshell\cms\Router::Execute()) {
            exit;
        }
    }
    if (!file_exists(DIR_BASE.'/'.$uri)) {
        header("HTTP/1.0 404 Not Found");
    }
}