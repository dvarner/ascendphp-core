<?php namespace Ascend\Core\CommandLine;

use \Ascend\Bootstrap;
use \Ascend\Core\CommandLine\_CommandLineAbstract;
use \Ascend\Core\CommandLineArguments;
use \App\Model\User;

class LocalhostWebServer extends _CommandLineAbstract
{

    protected $command = 'webserver';
    protected $name = 'Run php built-in web server to test Ascend';
    protected $detail = 'Run php built-in web server to test Ascend';

    public function run()
    {

        $argv = CommandLineArguments::getArgv();

        $cmd = 'php -S localhost ' . PATH_FRAMEWORK . 'bootstrap.php';

        if (isset($argv[2])) { // add port
            $cmd = 'php -S localhost:' . $argv[2] . ' ' . PATH_FRAMEWORK . 'bootstrap.php';
        }

        // @todo Doesnt output anything; must fix but does work
        ob_implicit_flush(true);
        // echo shell_exec($cmd);
        // passthru($cmd);

        $this->outputError('Does not work');
        $this->outputError('Run the following command');
        $this->outputSuccess($cmd);
        exit;
        // $this->outputSuccess('Start');
        /*
        $descriptorspec = array(
            0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
            2 => array("pipe", "w")    // stderr is a pipe that the child will write to
        );
        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                print $s;
                flush();ob_flush();
            }
        }


        /*
        $h = popen($cmd, 'r');
        sleep(1);
        // echo fread($h, 128);
        pclose($h);
        $this->outputSuccess('End');

        /*
        $proc = popen($cmd, 'r');
        while (!feof($proc))
        {
            echo fread($proc, 512);
            ob_flush();
            flush();
        }
        $this->outputSuccess('End');

        /*
        ob_implicit_flush(true);

        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout -> we use this
            2 => array("pipe", "w")   // stderr
        );

        $process = proc_open($exec, $descriptorspec, $pipes);

        if (is_resource($process)) {
            while (!feof($pipes[1])) {
                $return_message = fgets($pipes[1], 1024);
                if (strlen($return_message) == 0) break;

                echo $return_message . '<br />';
                ob_flush();
                flush();
            }
        }
        */
    }
}