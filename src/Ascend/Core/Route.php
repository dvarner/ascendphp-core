<?php namespace Ascend\Core;

class Route
{
    public static function getURI()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== strpos($uri, '?')) {
            $e = explode('?', $uri);
            $uri = $e[0];
        }
        return $uri;
    }

    public static function basicAuthHeader() {
        if (BASIC_AUTH_HEADER) {
            header('Cache-Control: no-cache, must-revalidate, max-age=0');
            // echo '<pre>'; var_dump($_SERVER); exit;
            $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
            $is_not_authenticated = (
                !$has_supplied_credentials ||
                $_SERVER['PHP_AUTH_USER'] !== BASIC_AUTH_HEADER_USER ||
                $_SERVER['PHP_AUTH_PW']   !== BASIC_AUTH_HEADER_PASS
            );
            if ($is_not_authenticated) {
                header('HTTP/1.1 401 Authorization Required');
                header('WWW-Authenticate: Basic realm="Access denied"');
                // echo 'Access Denied!';
                // unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                exit;
            }
        }
    }

    public static function view($uri, $class, $method, $module = null)
    {
        if (!is_null($module)) {
            $module = '\\Ascend\\' . $module . '\\';
        }
        if (self::getRequestMethod() == 'GET') {
            $class = $class . 'Controller';
            if ($uri == self::getURI()) {
                $class = $module . 'App\\Controller\\' . $class;
                call_user_func(array($class, $method));
                exit;
            }
            preg_match('@^' . $uri . '$@', self::getURI(), $matches);
            if (isset($matches[1])) {
                unset($matches[0]);
                $class = $module . 'App\\Controller\\' . $class;
                call_user_func(array($class, $method), $matches);
                exit;
            }
        }
    }

    public static function json($uri, $class, $method)
    {
        $class = $class . 'Controller';
        if ($uri == self::getURI()) {
            $class = 'App\\Controller\\' . $class;
            $r = call_user_func(array($class, $method));
            echo json_encode($r, true);
            exit;
        }
        preg_match('@' . $uri . '@', self::getURI(), $matches);
        if (isset($matches[1])) {
            unset($matches[0]);
            $class = 'App\\Controller\\' . $class;
            $r = call_user_func(array($class, $method), $matches);
            // header('Content-Type: application/json'); // @todo 200828 find out why we dont need this...
            echo json_encode($r, true);
            exit;
        }
    }

    public static function get($uri, $class, $method)
    {
        if (self::getRequestMethod() == 'GET') {
            echo self::json($uri, $class, $method);
        }
    }

    public static function post($uri, $class, $method)
    {
        if (self::getRequestMethod() == 'POST') {
            echo self::json($uri, $class, $method);
        }
    }

    public static function put($uri, $class, $method)
    {
        if (self::getRequestMethod() == 'PUT') {
            echo self::json($uri, $class, $method);
        }
    }

    public static function delete($uri, $class, $method)
    {
        if (self::getRequestMethod() == 'DELETE') {
            echo self::json($uri, $class, $method);
        }
    }

    public static function rest($uri)
    {
        if ($uri == self::getURI()) {
            echo '<h1>Under Construction</h1>';
            $method = self::getRequestMethod();
            $uri_requested = $_SERVER['REQUEST_URI'];
            $uri_parse = parse_url($uri_requested);
            parse_str($uri_parse['query'],$query_array);
            echo '<pre>';
            var_dump($uri_parse,$query_array);
            exit;
        }
    }

    public static function getRequestMethod()
    {
        // Below allows api access outside the website
        // header("Access-Control-Allow-Orgin: *");
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
        // if ($method != 'GET') { dd($method); }
        return $method;
    }

    public static function getPutVariables() {
        parse_str(file_get_contents("php://input"), $output);
        return $output;
    }

    public static function display404($template_file = '_template.php', $tpl = [], $replace_variable = 'container')
    {
        header("HTTP/1.0 404 Not Found");
        $html = '';
        $html.= '<center>';
        $html.= "<h1>404 Not Found</h1>";
        $html.= "The page that you have requested could not be found.";
        $html.= '</center>';
        // @todo uncomment and make work if user wants
        //$tpl['is_logged_in'] = User::isLoggedIn();
        $tpl[$replace_variable] = $html;
        echo View::html($template_file, $tpl);
    }
}
