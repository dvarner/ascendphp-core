<?php namespace Ascend\Examples\App\CommandLine;

use Ascend\Core\CommandLineWrapper;

class ExampleCommandLine extends CommandLineWrapper
{
    protected static $command = 'example';
    protected static $name = 'Example Command Line Script';
    protected static $help = 'example [arg1] [arg2] [etc]';

    public static function run($arguments = null)
    {
        if (is_null($arguments)) {
            self::out(' >> Start << ');
            // call some static function here
            self::out(' >> Complete <<');
        } else {
            // call some static function here if arguments are passed and do if/else or switch to check
        }
    }
}