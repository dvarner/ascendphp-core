<?php namespace Ascend\CommandLine;

use Ascend\CommandLine\_CommandLineAbstract;
use Ascend\CommandLineArguments;

class LaravelConversion extends _CommandLineAbstract
{

    protected $command = 'laravel:conversion';
    protected $name = 'Convert Laravel project to Ascend and vice versa';
    protected $detail = 'Convert Laravel project to Ascend and vice versa';

    private $pathLaravel;

    public function run()
    {
        $cmdArguments = CommandLineArguments::getArgv();

        if (!isset($cmdArguments[2])) {
            $this->out('Command requires path to laravel. Example: php ascend laravel::conversion /var/www/laravel/path/', false, 'red'); exit;
        }
        $this->pathLaravel = $cmdArguments[2];

        if (substr($this->pathLaravel, -1, 1) != '/') {
            $this->out('Path to laravel requires ending /.', false, 'red'); exit;
        }

        /**
         * *** Notes
         * Detect what version of laravel; crucial to determining how to convert
         * Required to specify laravel root project directory
         * Convert middleware auth
         *
         *
         */

        $this->out('Coming Soon!'); exit;

        $this->convertEnv();
        $this->convertRoutes();
        $this->convertMigrations();
        $this->convertModels();
        $this->convertControllers();
    }

    protected function convertEnv()
    {
        $file = '.env';

        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.
            }

            fclose($handle);
        } else {
            $this->out('Can not find file .env');
        }

    }

    protected function convertRoutes()
    {
        $web = $this->getFile('routes/web.php');
        $web = str_replace('Resource::', 'Rest::', $web);

        $api = $this->getFile('routes/api.php');
        $api = str_replace('Resource::', 'Rest::', $api);

    }

    protected function convertMigrations()
    {

    }

    protected function convertModels()
    {

    }

    protected function convertControllers()
    {

    }

    private function getFile($file)
    {
        return file_get_contents($this->pathLaravel . $file);
    }

    private function out($msg, $colorText = false, $colorBG = false)
    {
        // does not work....
        /*
        switch($colorBG){
            case 'green': // bg
                $msg = '[42m ' . $msg . PHP_EOL;
            case 'red': // bg
                $msg = '[41m ' . $msg . PHP_EOL;
            case 'yellow': // bg
                $msg = '[43m ' . $msg . PHP_EOL;
            case 'blue': // bg
                $msg = '[44m ' . $msg . PHP_EOL;
            default:
                $msg = $msg .PHP_EOL;
        }
        */
        echo $msg;
    }
}