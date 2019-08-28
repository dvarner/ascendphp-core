<?php namespace Ascend\Core;

class Route
{
    public static function sendWhereFrameworkBeingUsed()
    {
        $e = explode('/api', self::getURI());
        if (!isset($e[1])) {
            $url = 'https://framework.ldev.work/api/domain/' . DOMAIN;

            $options = array(
                CURLOPT_RETURNTRANSFER => true,     // return web page
                CURLOPT_HEADER => false,    // don't return headers
                CURLOPT_FOLLOWLOCATION => true,     // follow redirects
                CURLOPT_ENCODING => "",       // handle all encodings
                CURLOPT_USERAGENT => "framework", // who am i
                CURLOPT_AUTOREFERER => true,     // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
                CURLOPT_TIMEOUT => 120,      // timeout on response
                CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
                CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
            );

            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $content = curl_exec($ch);
            $err = curl_errno($ch);
            $errmsg = curl_error($ch);
            $header = curl_getinfo($ch);
            curl_close($ch);

            $header['errno'] = $err;
            $header['errmsg'] = $errmsg;
            $header['content'] = $content;
            return $header;
        }
    }

    public static function getURI()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== strpos($uri, '?')) {
            $e = explode('?', $uri);
            $uri = $e[0];
        }
        return $uri;
    }

    public static function view($uri, $class, $method)
    {
        if (self::getRequestMethod() == 'GET') {
            $class = $class . 'Controller';
            if ($uri == self::getURI()) {
                $class = 'App\\Controller\\' . $class;
                call_user_func(array($class, $method));
                exit;
            }
            preg_match('@^' . $uri . '$@', self::getURI(), $matches);
            if (isset($matches[1])) {
                unset($matches[0]);
                $class = 'App\\Controller\\' . $class;
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
            // header('Content-Type: application/json');
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

    public static function display404()
    {
        header("HTTP/1.0 404 Not Found");
        $tpl = [];
        $html = '';
        $html.= '<center>';
        $html.= "<h1>404 Not Found</h1>";
        $html.= "The page that you have requested could not be found.";
        $html.= '</center>';
        // @todo uncomment and make work if user wants
        //$tpl['is_logged_in'] = User::isLoggedIn();
        $tpl['container'] = $html;
        echo View::html('_template.php', $tpl);
    }
}
