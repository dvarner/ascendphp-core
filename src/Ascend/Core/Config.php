<?php namespace Ascend\Core;

class Config
{
    public static array $config;

    public static function get($variable, $module = null)
    {
        $path = self::$config[$variable] ?? null;
        if (!is_null($module)) {
            $path = str_replace('[module]', $module, $path);
        }
        return $path;
    }

    public static function init($module_name = null)
    {
        $dir = __DIR__ . '/../' . (is_null($module_name) ? '' : 'modules/' . $module_name . '/') . 'configurations/';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (filetype($dir . $file) === 'file') {
                        $extension = pathinfo($dir . $file, PATHINFO_EXTENSION);
                        if ($extension === 'php') {
                            self::loadFile($dir . $file, $module_name);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    private static function loadFile($file, $module_name)
    {
//        eo('$file', $file);

        if (file_exists($file)) {

            $variables = include_once $file;
            $base = basename($file, '.php');
            $base = (is_null($module_name) ? '' : $module_name . '.') . $base;

            foreach ($variables as $name => $value) {
                if (is_array($value)) {
                    self::deepCopyConfigVariables($variables, $base);
                } else {
                    if (!isset(self::$config[$base . '.' . $name])) {
                        self::$config[$base . '.' . $name] = $value;
                    }
                }
            }
        }
    }

    private static function deepCopyConfigVariables($variables, $concatenate_variable_name)
    {
        foreach ($variables as $name => $value) {
            if (is_array($value)) {
                $concatenate_variable_name .= '.' . $name;
                self::deepCopyConfigVariables($value, $concatenate_variable_name);
            } else {
                if (!isset(self::$config[$concatenate_variable_name . '.' . $name])) {
                    self::$config[$concatenate_variable_name . '.' . $name] = $value;
                }
            }
        }
    }
}





















