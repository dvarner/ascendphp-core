<?php namespace Ascend\Core;

use Ascend\Core\Debug;
use Ascend\Core\CommandLineArguments;

class CommandLine
{
	private $cmdArguments = null;
	
    public static function init()
    {
        $DS = DIRECTORY_SEPARATOR;
        $PCmd = PATH_PROJECT . 'vendor'.$DS.'dvarner'.$DS.'ascendphp-core'.$DS.'src'.$DS.'Ascend'.$DS.'CommandLine'.$DS;
		$argv = CommandLineArguments::getArgv();
		$cmd = isset($argv[1]) ? $argv[1] : null;
		// 2+ arguments.... figure out later

		$output = '';
		$output.= 'PHP Version: ' . phpversion() . RET;
		$output.= 'Help! Command not found!.' . RET;
		$output.= 'Here is a list of commands available:' . RET . RET;

		require_once $PCmd . '_CommandLineAbstract.php';

		// Get Framework specific Command lines
        $listOfCommands = [];
		$path = $PCmd;
		$cdir = scandir($path); 
		foreach ($cdir as $key => $value) { 
			if (!in_array($value, array(".", ".."))) { 
				if (
					!is_dir($path . DIRECTORY_SEPARATOR . $value)
					&&
					'_' != substr($value, 0, 1)
				) { 
                /*
                echo 'A'.RET;
                echo $cmd.RET;
                echo $path.RET;
                echo $value.RET;
                echo RET;
                */
					$listOfCommands[] = self::getEachModel($cmd, $path, $value);
				}
			} 
		}

		// Get App specific Command Lines
        $path = PATH_APP_COMMANDLINE;
        $cdir = scandir($path);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (
                    !is_dir($path . DIRECTORY_SEPARATOR . $value)
                    &&
                    '_' != substr($value, 0, 1)
                ) {
                /*
                echo 'B'.RET;
                echo $cmd.RET;
                echo $path.RET;
                echo $value.RET;
                */
                echo RET;
                    $listOfCommands[] = self::getEachModel($cmd, $path, $value);
                }
            }
        }

        sort($listOfCommands);
        foreach ($listOfCommands as $v) {
            $output .= $v . RET;
        }
		
		echo $output;
		exit;
    }
	
	private static function getEachModel($cmd, $path, $value) {
		$output = '';
		// $output.= 'filename: ' . $path . $value . RET;
		
		require_once $path . $value;
		
		$className = str_replace('.php', '', $value);
        if (false !== strpos($path,'vendor')) {
            $className = '\\' . 'Ascend' . '\\' . 'CommandLine' . '\\' . $className;
        } else {
            $className = '\\' . 'App' . '\\' . 'CommandLine' . '\\' . $className;
        }
		
		$n = new $className;
		
		if($n->getCommand() == $cmd) {
			$n->run(); exit;
		}
		
		$output.= $n->getCommand() . ' - ' . $n->getName();
		
		unset($className, $n);
		
		return $output;
	}
}