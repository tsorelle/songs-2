<?php

namespace Nutshell\cms;

use Peanut\users\AccountManager;
use Tops\sys\TConfiguration;
use Tops\sys\TUser;

class RouteFinder
{
    public static $matched = null;
    public static $routes = null;

    private static function normalizeUri($uri)  {
        $parts = explode('?',$uri);
        $uri = $parts[0];
        if ($uri === '' || $uri === '/') {
            $uri = 'home';
        }
        return $uri;
    }

    public static function matchWithRedirect($uri) {
        $matched = self::match($uri);
        if (!$matched) {
            $settings = parse_ini_file(DIR_CONFIG_SITE . '/settings.ini', true);
            if (isset($settings['locations']['defaultredirect']) ) {
                $sub = $settings['locations']['defaultredirect'];
                if ($sub) {
                    $parts = explode('/', $uri);
                    if ($parts) {
                        $uri = "$sub/" . array_pop($parts);;
                        return self::match($uri);
                    }
                }
            }
        }
        return $matched;
    }

    public static function match($uri)
    {
        $uri = self::normalizeUri($uri);
        self::$routes = parse_ini_file(DIR_CONFIG_SITE . '/routing.ini', true);
        foreach (self::$routes as $matchPath => $values) {
            if (strpos($uri, $matchPath) === 0) {
                if ($uri != $matchPath && (!array_key_exists('args',$values))) {
                    continue;
                }
                $matchParts = explode('/', $matchPath);
                $matchCount = count($matchParts);
                $pathParts = explode('/', $uri);
                for ($i = 0; $i < $matchCount; $i++) {
                    if ($pathParts[$i] !== $matchParts[$i]) {
                        return false;
                    }
                }
                $handler = $values['handler'] ?? null;
                if ($handler === 'redirect') {
                    $uri=  self::normalizeUri($values['target'] ?? '');
                    continue;
                }
                $configuration = $values;
                $pathCount = count($pathParts);
                $argCount = $pathCount - $matchCount;
                if ($argCount > 0) {
                    $argValues = array_splice($pathParts, $matchCount);
                }

                $configuration['uri'] = $uri;
                $configuration['path'] = $matchPath;
                $configuration['argValues'] = $argValues ?? [];
                self::$matched = $configuration;

                return true;
            }
        }
        return false;
    }
}