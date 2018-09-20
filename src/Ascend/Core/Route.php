<?php namespace Ascend\Core;

use Ascend\Core\Debug;
use Ascend\Core\Request;

/**
 * Routes class allows route creation for mapping uri to views, controllers, etc
 */
class Route
{
    public static function get($path, $call)
    {
        list($uri, $param, $method) = Request::getRequestUriParsed();
        list($path, $dynamicVariables) = self::dynamicVariables($path, $uri);
        /*
        echo $uri.'<br />';
        echo $path.'<br />';
        echo ' ------------ <BR />';
        */
        if ($path == $uri && $method == 'GET') {
            self::getControllerByUri($path, $call, $uri, $dynamicVariables);
            exit;
        }
    }

    public static function post($path, $call)
    {
        list($uri, $param, $method) = Request::getRequestUriParsed();
        list($path, $dynamicVariables) = self::dynamicVariables($path, $uri);

        if ($path == $uri && $method == 'POST') {
            self::getControllerByUri($path, $call, $uri, $dynamicVariables);
            exit;
        }
    }

    public static function put($path, $call)
    {

        list($uri, $param, $method) = Request::getRequestUriParsed();
        list($path, $dynamicVariables) = self::dynamicVariables($path, $uri);

        if ($path == $uri && $method == 'PUT') {
            self::getControllerByUri($path, $call, $uri, $dynamicVariables);
            exit;
        }
    }

    public static function delete($path, $call)
    {

        list($uri, $param, $method) = Request::getRequestUriParsed();
        list($path, $dynamicVariables) = self::dynamicVariables($path, $uri);

        if ($path == $uri && $method == 'DELETE') {
            self::getControllerByUri($path, $call, $uri, $dynamicVariables);
            exit;
        }
    }

    public static function rest($path, $call)
    {
        self::get('/' . $path, $call . '@viewList');    // Show html page for listing results
        self::get('/api/' . $path, $call . '@methodGet');        // Get a json result of all
        self::get('/' . $path . '/create', $call . '@viewCreate'); // Get html form for create
        self::post('/api/' . $path, $call . '@methodPost');        // Insert a record(s)
        self::get('/api/' . $path . '/{id}', $call . '@methodGetOne');        // Get single result back in json
        self::get('/' . $path . '/{id}/edit', $call . '@viewEdit');    // Show html form for editing
        self::put('/api/' . $path . '/{id}', $call . '@methodPut');        // Update call results json
        self::delete('/api/' . $path . '/{id}', $call . '@methodDelete');        // Delete call results json
    }

    public static function view($uri, $path)
    {

        list($requestUri, $requestParams, $requestMethod) = Request::getRequestUriParsed();

        if ($uri == $requestUri) {
            self::getView($path);
        }
    }

    public static function getView($path, $arr = null)
    {
        if (substr($path, -4) != '.php') {
            $path .= '.php';
        }

        $pathView = PATH_VIEWS . $path;

        if (file_exists($pathView)) {
            if (is_array($arr)) {
                extract($arr);
            }

            http_response_code(200);
            header('Content-Type: text/html');
            ob_start();
            require_once $pathView;
            /*if (Bootstrap::getConfig('debug.script_runtime')) {
                echo Debug::displayLogTime();
            }*/
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        } else {
            die($path . ' not found');
        }
    }

    public static function error404()
    {
        // http_response_code(404);
        header("HTTP/1.0 404 Not Found");
        echo '<center>';
        echo "<h1>404 Not Found</h1>";
        echo "The page that you have requested could not be found.";
        echo '</center>';
        exit;
    }

    public static function maint()
    {
        if (Bootstrap::getConfig('maint') === true) {
            require_once PATH_VIEWS . 'maint.php';
            exit;
        }
    }

    public static function lock()
    {
        if (Bootstrap::getConfig('lock') === true) {
            if (!isset($_SESSION['_LOCK']['loggedin'])) {
                if (
                    isset($_POST['locked_user'])
                    && isset($_POST['locked_pass'])
                    && $_POST['locked_user'] == Bootstrap::getConfig('lock_user')
                    && $_POST['locked_pass'] == Bootstrap::getConfig('lock_pass')
                ) {
                    unset($_SESSION['_LOCK']['error']);
                    $_SESSION['_LOCK']['loggedin'] = true;
                } else {
                    $_SESSION['_LOCK']['error'] = 'User/Pass Incorrect!';
                    require_once PATH_VIEWS . 'locked_login.php';
                    exit;
                }
            }
        }
    }

    public static function denied()
    {
        self::view('/access-denied', 'access-denied'); // @todo make this into a function
    }

    // Takes path, checks for {?}, and changes {?} to values from uri.
    private static function dynamicVariables($path, $uri)
    {
        // @todo push this update to framework
        $dynamicVariables = [];

        $pattern = '@\{([a-zA-Z]{1,50})\}@';
        preg_match_all($pattern, $path, $pathMatches);

        $splitPath = explode('/', $path);
        $splitUri = explode('/', $uri);

        /*
        echo ' ========================================== <br />'.PHP_EOL;
        echo $path.'<br />'.PHP_EOL;
        echo $uri.'<br />'.PHP_EOL;
        */

        $var = [];
        $replacePath = '';
        if (count($splitPath) == count($splitUri) && count($pathMatches[1]) > 0) {
            // echo '<pre>'; var_dump($splitPath, $splitUri, $pathMatches[1]); echo '</pre>';
            for ($i = 0; $i < count($splitPath); $i++) {
                $pattern = '@\{([a-zA-Z]{1,50})\}@';
                preg_match_all($pattern, $splitPath[$i], $matches);
                if (isset($matches[1]) && count($matches[1]) > 0) {
                    $k = $matches[1][0];
                    $v = $splitUri[$i];
                    $var[$k] = $v;
                    $replacePath .= $v . '/';
                } else {
                    $replacePath .= $splitPath[$i] . '/';
                }
            }
            if (count($var) > 0) {
                /*
                echo '<pre>bla';
                var_dump($var);
                echo '</pre>';
                */
                $dynamicVariables = $var;
            }
            if (substr($uri, -1, 1) != '/') {
                $replacePath = substr($replacePath, 0, -1);
            }
        } else {
            $replacePath = $path;
        }

        // echo 'rp: '.$replacePath.'<br />';

        return [$replacePath, $dynamicVariables];
    }

    private static function getControllerByUri($path, $call, $uri, $dynamicVariables) // $dynVar, $dynVal)
    {
        if (is_callable($call)) {
            echo $call();
            if (Bootstrap::getConfig('debug.script_runtime')) {
                echo Debug::displayLogTime();
            }
        } else {
            if (false !== strpos($call, '@')) {

                list($class, $func) = explode('@', $call);
                require_once PATH_CONTROLLERS . $class . '.php';
                if (file_exists(PATH_CONTROLLERS . $class . '.php')) {
                    $classNamespace = 'App\\Controller\\' . $class;
                } else {
                    die($class . ' failed to load within Route::getControllerByUri()');
                    /** REMOVED: Dont want controllers in features or fw which are stuck the way they are. Give devs power to change.
                     * if (file_exists(PATH_FRAMEWORK . 'feature' . DIRECTORY_SEPARATOR . $class . '.php')) {
                     * $classNamespace = 'Ascend' . '\\' . 'Feature' . '\\' . $class;
                     * } else {
                     * die($class . ' failed to load within Route::getControllerByUri()');
                     * }
                     */
                }

                /*
                if (!isset($_SESSION['user.id']) && $_SERVER['REQUEST_URI'] != '/login') {
                    header("location: /login");
                    exit;
                }
                */

                $classNamespaceObject = new $classNamespace;

                $call = str_replace('@', '::', $call);

                $result = null;
                /*
                if (isset($dynVar[1]) && count($dynVar[1]) == 1) {
                    $result = $classNamespaceObject->$func($dynVal[0]);
                } elseif (isset($dynVar[1]) && count($dynVar[1]) == 2) {
                    $result = $classNamespaceObject->$func($dynVal[0], $dynVal[1]);
                } elseif (isset($dynVar[1]) && count($dynVar[1]) == 3) {
                    $result = $classNamespaceObject->$func($dynVal[0], $dynVal[1], $dynVal[2]);
                } elseif (isset($dynVar[1]) && count($dynVar[1]) == 4) {
                    $result = $classNamespaceObject->$func($dynVal[0], $dynVal[1], $dynVal[2], $dynVal[3]);
                } elseif (isset($dynVar[1]) && count($dynVar[1]) > 4) {
                    trigger_error('Route does not suppore more than 4 dynamic variable. Fix in Route::getControllerByUri!', E_USER_ERROR);
                */
                if (count($dynamicVariables) > 0) {
                    $result = call_user_func_array([$classNamespaceObject, $func], $dynamicVariables);
                } else {
                    $rClass = new \ReflectionClass($classNamespace);
                    $method = $rClass->getMethod($func);

                    $c = $method->getNumberOfParameters();
                    if ($c == 0) {
                        $result = $classNamespaceObject->$func();
                    } else if ($c >= 1) {
                        $inst = array();
                        foreach ($method->getParameters() as $num => $parameter) {
                            // var_dump($classNamespace, $method, $parameter); exit;
                            // $defClassName = $parameter->getType(); // only a PHP 7+ thing...
                            // $defClassName = $parameter->getClass();
                            // $defVariable = $parameter->getName();
                            // echo 'Ascend\Route.php > 220<br />'.PHP_EOL;
                            if (isset($parameter->getClass()->name)) {
                                $nam = '\\' . $parameter->getClass()->name;
                                $inst[] = new $nam;
                            } else {
                                $inst[] = $parameter;
                            }
                        }
                        if (count($inst) == 1) {
                            $result = $classNamespaceObject->$func($inst[0]);
                        } elseif (count($inst) == 2) {
                            $result = $classNamespaceObject->$func($inst[0], $inst[1]);
                        } elseif (count($inst) == 3) {
                            $result = $classNamespaceObject->$func($inst[0], $inst[1], $inst[2]);
                        } else {
                            die('Fix Route > getControllerByUri > ReflectionClass');
                        }
                    }
                }
                if (is_array($result)) {

                    $request = new Request;
                    if (!is_null($request->input('json-pretty'))) {
                        echo '<pre>';
                        var_dump($result);
                    } else {
                        // @todo setup what sites are allowed to access api
                        /*
                        header('Access-Control-Allow-Origin: http://mysite1.com', false);
                        header('Access-Control-Allow-Origin: http://example.com', false);
                        header('Access-Control-Allow-Origin: https://www.mysite2.com', false);
                        header('Access-Control-Allow-Origin: http://www.mysite2.com', false);
                        */
                        header("Access-Control-Allow-Origin: *");
                        header("Access-Control-Allow-Methods: *");
                        header("Content-Type: application/json");
                        echo json_encode($result);
                    }

                    exit;
                } else {
                    echo $result;
                    if (Bootstrap::getConfig('debug.script_runtime')) {
                        echo Debug::displayLogTime();
                    }
                }
            } else {
                trigger_error('Route "' . $uri . '" incorrectly setup. Contact Support!', E_USER_ERROR);
            }
        }
    }

    /**
     * @todo Found the following below and want to reference and use for header status output
     */

    private function processAPI()
    {
        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
        return $this->_response("No Endpoint: $this->endpoint", 404);
    }

    private function _response($data, $status = 200)
    {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }
}
