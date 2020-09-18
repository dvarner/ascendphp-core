<?php namespace Ascend\Core;

class CommandLineWrapper
{
    protected static string $command = '';
    protected static string $name = '';
    protected static string $help = '';

    public static function getCommand()
    {
        return static::$command;
    }

    public static function getName()
    {
        return static::$name;
    }

    public static function getHelp()
    {
        return static::$help;
    }

    protected static function out($msg = '')
    {
        echo $msg . PHP_EOL;
    }

}