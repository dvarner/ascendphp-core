<?php namespace Ascend\Core;

class Environment
{
    public static array $env;

    public static function get($variable, $default = null)
    {
        return self::$env[$variable] ?? $default;
    }

    public static function init()
    {
        $env_file = __DIR__ . '/../.env';
        if (is_file($env_file)) {
            $content = file_get_contents($env_file);
            $lines = explode(PHP_EOL, $content);
            foreach ($lines as $line) {
                if (str_contains($line, '=') && 0 !== strpos($line, '#')) {
                    $split = explode('=', $line);
                    $key = $split[0];
                    unset($split[0]);
                    $value = implode('=', $split);
                    self::$env[$key] = $value;
                }
            }
        }
    }
}





















