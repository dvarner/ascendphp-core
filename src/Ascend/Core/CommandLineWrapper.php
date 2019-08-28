<?php namespace Ascend\Core;

class CommandLineWrapper
{
    protected static $command = '';
    protected static $name = '';
    protected static $help = '';

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

}