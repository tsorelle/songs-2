<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/4/2017
 * Time: 6:51 AM
 */

namespace Tops\sys;


class TWebSite
{
    private static $baseUrl=null;
    public static function GetSiteUrl() {
        global $_SERVER;
        if (!isset($_SERVER['HTTP_HOST'])) {
            return '';
        }
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    public static function ExpandUrl($url) {
        $scheme = strtolower(parse_url($url,4)); // PHP_URL_SCHEME
        if ($scheme=='http' || $scheme =='https:') {
            return $url;
        }
        $base = self::GetBaseUrl();
        if (empty($url)) {
            return $base;
        }
        return strpos($url,'/') === 0 ? $base.$url : "$base/$url";
    }

    public static function GetBaseUrl(){
        if (self::$baseUrl!==null) {
            return self::$baseUrl;
        }
        self::$baseUrl = TConfiguration::getValue('url','site');
        if (empty(self::$baseUrl))  {
            self::$baseUrl = self::GetSiteUrl();
        }
        return self::$baseUrl;
    }

    public static function GetDomain() {
        global $_SERVER;
        if (!isset($_SERVER['SERVER_NAME'])) {
            return 'localdomain';
        }
        $parts = explode('.',strtolower($_SERVER['SERVER_NAME']));
        if ($parts[0] == 'www') {
            array_shift($parts);
        }
        return join('.',$parts);
    }

    public static function reset() {
        self::$baseUrl = null;
    }

    public static function SetBaseUrl($value) {
        $baseUrl = $value;
    }

    /**
     * Follows a convention where first part of sub-domain indicates deployment environment.
     * e.g. staging, testing, local.  If not subdomain assume 'production'
     */
    public static function GetEnvironmentName() {
        $domain = strtolower(self::GetDomain());
        if ($domain == 'localhost') {
            return 'local';
        }
        $parts = explode(',',$domain);
        return count($parts) > 1 ? $parts[0] :  'production';
    }

    public static function AppendRequestParams($url,array $params) {
        if (substr($url,-1) == '/') {
            $url = substr($url,0,strlen($url) -1);
        }
        $delim = strpos($url,'?') === false ? '?' : '&';
        foreach ($params as $key => $value) {
            $url .= $delim.$key.'='.$value;
            $delim = '&';
        }
        return $url;
    }
}