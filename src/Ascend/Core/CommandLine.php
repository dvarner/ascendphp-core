<?php namespace Ascend\Core;

/**
 * Class CommandLine
 * @package Ascend\Core
 *
 * CommandLineArguments and self::getArgv() is defined inside "ascend" file in root folder
 */

class CommandLine extends CommandLineArguments
{
    public static function init()
    {
        $args = self::getArgv();
        $service = null;
        $method = null;
        if (count($args) == 2) list($ignore, $service) = $args;
        if (count($args) == 3) list($ignore, $service, $method) = $args;

        if (!is_null($service)) {
            $dir = PATH_COMMANDLINE;
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        $pattern = '@^([a-zA-Z0-9-_]{1,100})CommandLine\.php$@'; //
                        preg_match($pattern, $file, $matches);
                        if (filetype($dir . $file) == 'file' && count($matches) > 0) {
                            $e = explode('.',$file);
                            $class = $e[0];
                            $commandline_name = call_user_func('\\App\\CommandLine\\' . $class . '::getCommand');
                            if ($service == $commandline_name) {
                                if (!isset($method)) {
                                    $run = call_user_func('\\App\\CommandLine\\' . $class . '::run');
                                } else{
                                    $run = call_user_func('\\App\\CommandLine\\' . $class . '::run', $method);
                                }
                                self::out($run);
                                exit;
                            }
                        }
                    }
                    closedir($dh);
                }
            }
            self::out();
            self::out('No Command Line found by that name!');
            self::out('List of available command lines:');
            self::out();
            self::availableCommandLines();
            self::out();
            self::out();
        } else {
            self::out();
            self::out('No Command Line command passed!');
            self::out('List of available command lines:');
            self::out();
            self::availableCommandLines();
            self::out();
            self::out();
        }
    }

    private static function availableCommandLines() {
        $dir = PATH_COMMANDLINE;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    $pattern = '@^([a-zA-Z0-9-_]{1,100})CommandLine\.php$@'; //
                    preg_match($pattern, $file, $matches);
                    if (filetype($dir . $file) == 'file' && count($matches) > 0) {
                        $e = explode('.',$file);
                        $class = $e[0];
                        $r = call_user_func('\\App\\CommandLine\\' . $class . '::getHelp');
                        self::out($file);
                        self::out( '  php fw ' . $r);
                    }
                }
                closedir($dh);
            }
        }
    }

    private static function out($msg = '')
    {
        echo $msg . PHP_EOL;
    }
}