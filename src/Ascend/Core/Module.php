<?php namespace Ascend\Core;

class Module
{
    public static function loadConfigurations($module_name)
    {
        Config::init($module_name);
    }

    public static function getControllerPath($module_name, $class_name)
    {
        $path = Config::get('path.modules') . $module_name . '/Controllers/' . $class_name . '.php';
        return file_exists($path) ? $path : false;
    }

    public static function getViewPath($module_name, $file_name)
    {
        $path = Config::get('path.modules') . $module_name . '/views/' . $file_name . '.view.php';
        return file_exists($path) ? $path : false;
    }

    public static function getViewHTML($module_name, $file_name)
    {
            ob_start();
            include_once self::getViewPath($module_name, $file_name);
            return ob_get_clean();
    }
}