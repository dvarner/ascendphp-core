<?php namespace Ascend\Core;

use Exception;

class RouteManager
{
    private array $routes_list = [];
    private string $route_html = '';

    public function __construct()
    {
        $this->routes_list = Route::getRouteList();
    }

    public function executeRoutes()
    {
        $exact_match_route = $this->routes_list[self::getUri()] ?? null;
//        vd('$exact_match_route', $exact_match_route);
        if (!is_null($exact_match_route)
            && isset($exact_match_route['auth_on']) && isset($exact_match_route['auth_valid'])
            && ($exact_match_route['auth_on'] === true && $exact_match_route['auth_valid'] === false)
        ) {
            // todo 4/5/22 turn this into a page and/or throw
            return Theme::getUnauthorized(['container' => 'Page not found!']);
        }

        if (!is_null($exact_match_route)) {
            if (isset($exact_match_route['method']) && isset($exact_match_route['class']) && isset($exact_match_route['function'])
                && method_exists($exact_match_route['class'], $exact_match_route['function'])
            ) {
                return $this->matchExact($exact_match_route['method'], $exact_match_route['class'], $exact_match_route['function']);
            }
            // todo 4/5/22 log error if got here but method doesnt exist. Coding error.
        }
        unset($exact_match_route);

        foreach ($this->routes_list as $uri => $route) {
            $route_match_result = $this->matchRegularExpression($uri, $route['method'], $route['class'], $route['function']);
            if ($route_match_result) {
                if ($route['auth_on'] === true && $route['auth_valid'] === false) {
                    // todo 4/5/22 turn this into a page and/or throw
                    return Theme::getUnauthorized(['container' => 'Page not found!']);
                } else {
                    return $route_match_result;
                }
            }
            unset($route);
        }

        return Theme::getNotFound(['container' => 'Page not found!']);
    }

    public function returnHtml(): string
    {
//        vd('returnHtml: ', $this->routes_list);
        return $this->route_html;
    }

    public function debugConflictingRoutes()
    {
        // todo make a way to tell where routes are "in what module"
        // todo if there are duplicates of the same path
    }

    private function matchExact($method, $class, $function)
    {
        if ($method === $this->getRequestMethod()) {
            try {
                return call_user_func([$class, $function]);
            } catch (Exception $e) {
                echo '<br />Throw Exception:<br />';
                dd($e);
            }
        }
        return false;
    }

    private function matchRegularExpression($uri, $method, $class, $function)
    {
        if ($method === $this->getRequestMethod()) {
            preg_match('@^' . $uri . '$@', self::getUri(), $matches);

            if (isset($matches[1])) {
                unset($matches[0]);
                try {
//                    vd('matchRegularExpression():', $uri, $class, $function, $matches);
                    return call_user_func_array([$class, $function], $matches);
                } catch (Exception $e) {
                    echo '<br />Throw Exception:<br />';
                    dd($e);
                }
            }
        }

        return false;
    }

    private function getRequestMethod()
    {
        // Below allows api access outside the website
        // header("Access-Control-Allow-Origin: *");
        // header("Access-Control-Allow-Methods: *");

        // <input type="hidden" name="_method" value="PUT">
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $method = 'PUT';
            } else {
                throw new \Exception("Unexpected Header");
            }
        }
        return $method;
    }

    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (str_contains($uri, '?')) {
            $e = explode('?', $uri);
            $uri = $e[0];
        }
        return $uri;
    }
}