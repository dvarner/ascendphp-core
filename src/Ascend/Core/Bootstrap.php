<?php namespace Ascend\Core;

use Exception;

class Bootstrap
{
    private bool $file_exists = false;

    public function init()
    {
        $this->initAutoLoader();
        $this->initEnvFile();
        $this->initConfigurationFiles();
        $this->handleDisplayErrors();
        $this->initDatabase();

        if (PHP_SAPI === 'cli') {
            set_time_limit(Config::get('php.set_time_limit_command_line'));
            $this->runIfCommandline();
        } else {
            set_time_limit(Config::get('php.set_time_limit_http'));
            echo $this->runIfHttpRequest();
        }
    }

    private function initAutoLoader()
    {
        spl_autoload_register(function ($namespace) {
//            $class_name = substr($namespace, ($p = strrpos($namespace, '\\')) !== false ? $p + 1 : 0);
            $split = explode('\\', $namespace);
            $class_name = end($split);
            $section_name = $split[1] ?? null;
            $this->file_exists = false;
            $this->initCoreAutoLoader($class_name);
            $this->initModuleControllersAutoLoader($section_name, $class_name);
            if (!$this->file_exists) {
                throw new Exception('Class "' . $namespace . '" does not exist!');
            }
        });
    }

    private function initEnvFile()
    {
        Environment::init();
    }

    private function initConfigurationFiles()
    {
        Config::init();
    }

    private function initCoreAutoLoader($class_name)
    {
        $file_name = __DIR__ . '/' . $class_name . '.php';
        if (!$this->file_exists) {
            if (file_exists($file_name)) {
                $this->file_exists = true;
                require_once $file_name;
            }
        }
    }

    private function initModuleControllersAutoLoader($module_name, $class_name)
    {
        $file_name = Module::getControllerPath($module_name, $class_name);
        if (!$this->file_exists) {
            if (file_exists($file_name)) {
                $this->file_exists = true;
                Module::loadConfigurations($module_name);
                require_once $file_name;
            }
        }
    }

    private function handleDisplayErrors()
    {
        ini_set('display_errors', Config::get('error_handling.display_errors'));
        ini_set('display_startup_errors', Config::get('error_handling.display_startup_errors'));
        error_reporting(Config::get('error_handling.error_reporting'));
    }

    private function initDatabase()
    {
        new Database();
    }

    private function runIfCommandline()
    {
        echo PHP_EOL;
        echo 'Command line coming soon!' . PHP_EOL;
        echo PHP_EOL;
    }

    private function runIfHttpRequest()
    {
        // todo 3/31/22 Update session_start to db managed
        session_start();

        include_once __DIR__ . '/../routes.php';

        $modules_dir = Config::get('path.modules');
        if (is_dir($modules_dir)) {
            if ($dh = opendir($modules_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $routes_path = $modules_dir . $file . '/routes.php';
                        if (file_exists($routes_path)) {
                            include_once $routes_path;
                        }
                    }
                }
                closedir($dh);
            }
        }

        $route = new RouteManager();
        return $route->executeRoutes();
    }
}