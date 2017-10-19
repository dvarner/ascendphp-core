<?php namespace Ascend\Core\CommandLine;

use Ascend\Core\CommandLine\_CommandLineAbstract;
use Ascend\Core\CommandLineArguments;

class ConversionLaravel extends _CommandLineAbstract
{

    protected $command = 'conversion:laravel';
    protected $name = 'Convert Laravel project to Ascend and vice versa';
    protected $detail = 'Convert Laravel project to Ascend and vice versa';

    private $pathLaravel;

    public function run()
    {
        $cmdArguments = CommandLineArguments::getArgv();

        if (!isset($cmdArguments[2])) {
            $this->outputError('Command requires path to laravel. Example: php ascend conversion:laravel /var/www/laravel/path/'); exit;
        }
        $this->pathLaravel = $cmdArguments[2];

        if (substr($this->pathLaravel, -1, 1) != '/') {
            $this->outputError('Path to laravel requires ending /.'); exit;
        }

        /**
         * *** Notes
         * Detect what version of laravel; crucial to determining how to convert
         * Required to specify laravel root project directory
         * Convert middleware auth
         *
         * Tell what could and could not be converted
         *
         */

        $this->output('Coming Soon!'); exit;

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
            $this->output('Can not find file .env');
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
}