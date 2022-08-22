<?php namespace Ascend\Core;

class Route
{
    private static array $uri_list;
    private static bool $auth_on = false;
    private static ?bool $auth_valid = null;

    public static function getRouteList(): array
    {
        return self::$uri_list;
    }

    public static function get($uri, $class, $function)
    {
        self::setUriList($uri, $class, $function, 'GET');
    }

    public static function post($uri, $class, $function)
    {
        self::setUriList($uri, $class, $function, 'POST');
    }

    public static function put($uri, $class, $function)
    {
        self::setUriList($uri, $class, $function, 'PUT');
    }

    public static function patch($uri, $class, $function)
    {
        self::setUriList($uri, $class, $function, 'PATCH');
    }

    public static function delete($uri, $class, $function)
    {
        self::setUriList($uri, $class, $function, 'DELETE');
    }

    public static function setUriList($uri, $class, $function, $method)
    {
        self::$uri_list[$uri] = ['method' => $method, 'class' => $class, 'function' => $function, 'auth_on' => self::$auth_on, 'auth_valid' => self::$auth_valid];
    }

    public static function auth($module, callable $anonymous_function)
    {
        self::$auth_on = true;
        $module_path = Module::getControllerPath($module, $module);
        self::$auth_valid = (file_exists($module_path) ? include_once $module_path : false);
        $anonymous_function();
        self::$auth_valid = null;
        self::$auth_on = false;
    }
}