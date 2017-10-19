<?php namespace Ascend\Core\CommandLine;

$DS = DIRECTORY_SEPARATOR;
$PCmd = PATH_PROJECT . 'vendor'.$DS.'dvarner'.$DS.'ascendphp-core'.$DS.
    'src'.$DS.'Ascend'.$DS.'Core'.$DS.'CommandLine'.$DS;

require $PCmd . '_CommandLineColor.php';
use Ascend\Core\CommandLine\CommandLineColor;

/**
 * Class _CommandLineAbstract
 * @package Ascend\CommandLine
 *
 * ** Notes
 * https://stackoverflow.com/questions/29422276/laravel-artisan-commands-colors-not-showing
 */

abstract class _CommandLineAbstract
{
	protected $command;
    protected $name;
	protected $detail;
    
    public function getCommand(){ return $this->command; }
    public function getName(){ return $this->name; }
    public function getDetail(){ return $this->detail; }
    
    abstract public function run();
	
	protected function output($msg, $color = 'off') {
		echo CommandLineColor::set($msg . RET, $color);
	}

	protected function outputError($msg) {
        echo CommandLineColor::set($msg . RET, 'red+bold');
    }

    protected function outputSuccess($msg) {
        echo CommandLineColor::set($msg . RET, 'green+bold');
    }
}