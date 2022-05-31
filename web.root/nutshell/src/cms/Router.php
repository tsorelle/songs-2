<?php
namespace Nutshell\cms;
use Peanut\sys\ViewModelManager;
use Peanut\users\AccountManager;
use Tops\sys\TSession;
use Tops\sys\TUser;
use Tops\sys\TWebSite;

class Router
{
    public static function Execute() {
        self::checkAuthorization();
        switch (RouteFinder::$matched['handler'] ?? null) {
            case 'page' :
                self::routePage();
                break;
            case 'service' :
                self::routeService();
                break;
            default:
                throw new \Exception('Invalid configuation, must include "handler"');
        }
        return true;
    }

    public static function redirectToSignin() {

    }

    public static function routeService()
    {
        include __DIR__.'\routing\ServiceRequestHandler.php';
        $routeData = RouteFinder::$matched;
        $method = $routeData['method'] ?? null;
        if (empty($method)) {
            throw new \Exception('Value "method" is required in service routing configuration.');
        }
        $handler = new ServiceRequestHandler();
        $argValues = $routeData->argValues ?? [];
        if (!empty($argValues)) {
            $handler->$method(...$argValues);
        }
        else {
            $handler->$method();
        }
        exit;
    }

    private static function setSwitchValue(&$routeData,$name,$default=1) {
        $value = $routeData[$name] ?? $default;
        $routeData[$name] =  empty($value) ? 0 : 1;
        $routeData['test'] = $name;
    }
    private static function routePage()
    {

        /*
          Additional configuration values
                openpanel
                paneltitle
                addwrapper
                inputvalue
        */

        $routeData = RouteFinder::$matched;
        $uri = $routeData['uri'];
        $user = TUser::getCurrent();
        $theme = $routeData['theme'] ?? 'default';
        $routeData['theme'] = $theme;
        $routeData['themePath'] = '/application/themes/' . $theme;
        $routeData['themeIncludePath'] = DIR_BASE."/application/themes/$theme/inc";
        $user = TUser::getCurrent();
        $routeData['signin'] = $user->isAuthenticated() ?
            $user->getFullName().' | '.'<a class="ms-2" href="/signout">Sign Out</a>' :
            '<a id="footer-signin-link" href="/signin">Sign in</a>';

        if ($theme === 'plain') {
            $routeData['maincolsize'] = 12;
            self::setSwitchValue($routeData,'siteheader',0);
            self::setSwitchValue($routeData,'sitefooter',0);
            self::setSwitchValue($routeData,'breadcrumbs',0);
            self::setSwitchValue($routeData,'pageheader',0);
        }
        else {
            self::setSwitchValue($routeData,'siteheader',1);
            self::setSwitchValue($routeData,'sitefooter',1);
            self::setSwitchValue($routeData,'pageheader',1);
            if ($uri === 'home') {
                self::setSwitchValue($routeData, 'breadcrumbs', 0);
            }
            else {
                self::setSwitchValue($routeData,'breadcrumbs',1);
            }
            $maincolsize = 12;
            if (isset($routeData['menu'])) {
                if (!isset($routeData['colsize'])) {
                    $routeData['colsize'] = 6;
                }
                $maincolsize -= $routeData['colsize'];
                if (!isset($routeData['menutype'])) {
                    $routeData['menutype'] = 'default';
                }
            }

            $routeData['maincolsize'] = $maincolsize;
        }

        if (isset($routeData['view'])) {
            $view = DIR_APPLICATION . '/content/pages/' . $routeData['view'] . '.php';
        } else if (isset($routeData['mvvm'])) {
            $viewModelKey = $routeData['mvvm'];
            $vmInfo = ViewModelManager::getViewModelSettings($viewModelKey);

            if (empty($vmInfo)) {
                $errorMessage = "Error: Cannot find view model configuration for '$viewModelKey'</h2>";
            } else {
                $viewResult = $vmInfo->view ?? null;
                if ($viewResult == 'content') {
                    $errorMessage = 'Embedded views not supported in Nutshell';
                } else {
                    $view = DIR_BASE . '/' . $viewResult;
                    if (!file_exists($view)) {
                        $errorMessage = "View file not found: $viewResult";
                    }
                }

                if (!isset($errorMessage)) {
                     if (array_key_exists('return',$routeData)) {
                        $return = $routeData['return'];

                        if ($return == 'referrer') {
                            $return = $_SERVER['HTTP_REFERER'];
                        }
                        $_SESSION[AccountManager::redirectKey] = $return;
                        unset($routeData['return']);
                    }
                    $argNames = $argNames = $routeData['args'] ?? '';
                    if ($argNames) {
                        $argNames = explode(',',$argNames);
                        $argValues = $routeData['argValues'] ?? [];
                        if (!empty($routeData['argValues'])) {
                            $valueCount = count($argValues);
                            while(count($argNames) > $argValues) {
                                array_shift($argNames);
                            }

                            $pageVars = [];
                            for ($i = 0;$i < $valueCount; $i++) {
                                $pageVars[$argNames[$i]] = $argValues[$i];
                            }
                            $routeData['pageVars'] = $pageVars;
                            unset($routeData['args']);
                        }
                    }

                    $array = explode('/', $vmInfo->vmName);
                    $containerId = array_pop($array);
                    $routeData['containerId'] = strtolower($containerId) . "-view-container";

                    // init security token
                    TSession::Initialize();
                }

            }
            if (isset($errorMessage)) {
                $view = DIR_APPLICATION . '/content/pages/error-page.php';
                $routeData['errorMessage'] = $errorMessage;
                unset($routeData['mvvm']);
                unset($routeData['viewcontainerid']);
                unset($routeData['inputvalue']);
                unset($routeData['paneltitle']);
                unset($routeData['openpanel']);
                unset($routeData['addwrapper']);
            }
        }

        $routeData['view'] = $view;
        $routeData['sitemap'] = new SiteMap($uri);
        extract($routeData);
        include DIR_APPLICATION . '/content/page.php';
    }

    private static function checkAuthorization()
    {
        $user = TUser::getCurrent();
        $rolelist = RouteFinder::$matched['roles'] ?? null;
        if (!empty(trim($rolelist))) {
            $roles = explode(',',$rolelist);
            $ok = false;
            foreach ($roles as $role) {
                if ($user->isMemberOf($role)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                // redirect to signin page
                $signinConfig = RouteFinder::$routes['signin'];
                $signinConfig['uri'] = 'signin';
                $uri = RouteFinder::$matched['uri'] ?? '/';
                $redirect = TWebSite::ExpandUrl($uri);
                // $_SESSION[AccountManager::returnKey] = $redirect;
                $signinConfig['return'] = $redirect;
                RouteFinder::$matched = $signinConfig;
            }
        }
    }
}