<?php namespace Ascend\Core;

class Theme
{
    public static function getLayoutPath($theme_name): string
    {
        return Config::get('path.themes') . $theme_name . '/' . $theme_name . '.php';
    }

    public static function checkLayoutPath($theme_name): bool
    {
        return is_file(self::getLayoutPath($theme_name));
    }

    public static function getHtml($variables = [], $theme_name = 'default')
    {
        if (self::checkLayoutPath($theme_name)) {
            ob_start();
            extract($variables);
            include_once self::getLayoutPath($theme_name);
            return ob_get_clean();
        }
        return false;
    }

    public static function getUnauthorized($variables = [], $theme_name = 'default')
    {
        if (self::checkLayoutPath($theme_name)) {
            header("HTTP/1.0 401 Unauthorized");
            ob_start();
            extract($variables);
            include_once self::getLayoutPath($theme_name);
            return ob_get_clean();
        }
        return false;
    }

    public static function getForbidden($variables = [], $theme_name = 'default')
    {
        if (self::checkLayoutPath($theme_name)) {
            header("HTTP/1.0 403 Forbidden");
            ob_start();
            extract($variables);
            include_once self::getLayoutPath($theme_name);
            return ob_get_clean();
        }
        return false;
    }

    public static function getNotFound($variables = [], $theme_name = 'default')
    {
        if (self::checkLayoutPath($theme_name)) {
            header("HTTP/1.0 404 Not Found");
            ob_start();
            extract($variables);
            include_once self::getLayoutPath($theme_name);
            return ob_get_clean();
        }
        return false;
    }
}