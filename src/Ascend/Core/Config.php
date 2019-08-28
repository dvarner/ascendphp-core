<?php namespace Ascend\Core;

class Config
{
    private static $_config;

    public static function init()
    {
        $_config = [];
        require_once __DIR__ . DS . '..' . DS . 'App' . DS . 'config.php';
        self::$_config = $_config;
    }

    public static function get($variable)
    {
        if (isset(self::$_config[$variable])) {
            return self::$_config[$variable];
        } else {
            var_dump('Variable "' . $variable . '" was requested from Config::get() and does not exist!', debug_backtrace());
            exit;
        }
    }
}