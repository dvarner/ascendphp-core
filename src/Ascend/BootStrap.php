<?php namespace Ascend;

use Ascend\Databate;
use Ascend\Debug;

/**
 * BootStrap loads in all controllers / models needed when they are called and their dependencies
 * IoC aka Inversion of Control container
 */
class BootStrap
{

    /**
     * Holds all the configurations variables
     */
    private static $_config;
    private static $_dbPdo;
    private static $_db;

    /**
     * Initializes bootstrap config
     */
    public static function init()
    {
        self::autoloader();
        self::initConfig();

        /*
		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();
		*/
        
        $DS = DIRECTORY_SEPARATOR;

        if (self::existConfig('db')) {
            require_once PATH_PROJECT . 'vendor'.$DS.'dvarner'.$DS.'ascendphp-core'.$DS.'src'.$DS.'Ascend'.$DS.'DatabasePDO.php';
            require_once PATH_PROJECT . 'vendor'.$DS.'dvarner'.$DS.'ascendphp-core'.$DS.'src'.$DS.'Ascend'.$DS.'Database.php';
            require_once PATH_PROJECT . 'vendor'.$DS.'dvarner'.$DS.'ascendphp-core'.$DS.'src'.$DS.'Ascend'.$DS.'Model.php';
            self::initDBPDO();
            self::initDB();
        }

        if (self::isCommandLine()) {
            require_once PATH_PROJECT . 'vendor'.$DS.'dvarner'.$DS.'ascendphp-core'.$DS.'src'.$DS.'Ascend'.$DS.'CommandLine.php';
            CommandLine::init();
            exit;
        }
    }
    public static function autoloader()
    {
        spl_autoload_register(function ($name) {
            $DS = DIRECTORY_SEPARATOR;

            // Change namespace forward slashes to backward slashes for windows
            $name = str_replace('\\', '/', $name);

            $path = $name;
            /*
            $replacements = array(
                'App' . $DS . 'CommandLine' . $DS => 'app' . $DS . 'commandline' . $DS,
                'App' . $DS . 'Controller' . $DS => 'app' . $DS . 'controllers' . $DS,
                'App' . $DS . 'Model' . $DS => 'app' . $DS . 'models' . $DS,
                // 'Ascend' . $DS . 'Feature' . $DS => 'fw' . $DS . 'features' . $DS,
                // 'Ascend' . $DS => 'fw' . $DS,
            );

            $find = array_keys($replacements);
            $replace = array_values($replacements);

            $path = str_replace($find, $replace, $path);
            */

            // echo ' ## Ascend/Boostrap.php > 73<br />'.PHP_EOL;
            // echo PATH_PROJECT . $path . '.php'.'<br />'.PHP_EOL;
            if (file_exists(PATH_PROJECT . $path . '.php')) {
                require_once PATH_PROJECT . $path . '.php';
            // } else {

                // $path = str_replace('fw' . $DS, 'fw' . $DS . 'feature' . $DS, $path);

                // if (file_exists(PATH_PROJECT . $path . '.php')) {
                    // // echo PATH_PROJECT . $path . '.php' . RET;
                    // require_once PATH_PROJECT . $path . '.php';
                // } else {
                    // echo '<pre>';
                    // echo 'Name: ' . $name . RET;
                    // echo 'Count not find "' . PATH_PROJECT . $path . '.php"' . RET;
                    // var_dump(debug_backtrace());
                    // exit;
                // }
            }

            // throw new Exception("Unable to load $name.");
        });
    }

    /**
     * Initializes bootstrap config
     */
    private static function initConfig()
    {
        if (count(static::$_config) == 0) {

            /** Gets master configurations. */
            $path_config = __DIR__ . '/../../../../../App/config.php';
            if (file_exists($path_config)) {
                require_once $path_config;
            } else {
                // @todo -user:dvarner -date:11/25/2015 Create better error for when config.php file not created
                trigger_error('app/config.php does not exist! Please, copy config.sample.php and update with site information.', E_USER_ERROR);
            }

            // Setup defaults for configuration variables

            $_config['domain_full'] = ($_config['https'] === true ? 'https' : 'http') . '://www.' . $_config['domain'];

            if (is_array($_config['debug']) || $_config['debug'] === true) {
                unset($_config['debug']);
                $_config['debug']['basic'] = true;
                if (!isset($_config['debug']['script_runtime'])) {
                    $_config['debug']['script_runtime'] = false;
                }
                if (!isset($_config['debug']['validation'])) {
                    $_config['debug']['validation'] = false;
                }
            } else {
                unset($_config['debug']);
                $_config['debug']['basic'] = false;
                $_config['debug']['script_runtime'] = false;
                $_config['debug']['validation'] = false;
            }

            static::$_config = $_config;

            self::setDebug();
            self::wwwRedirect();
            self::setTimeZone();
        }
    }

    public static function existConfig($field)
    {
        $reqConfig = array('debug', 'dev', 'https', 'lock', 'maint', 'domain', 'timezone');

        if (in_array($field, $reqConfig)) {
            die('Variable required! "' . $field . '"');
        } else {
            return isset(static::$_config[$field]);
        }
    }

    /**
     * Gets configuration variables for use
     */
    public static function getConfig($field = false)
    {
        if ($field === false) {
            if (isset(static::$_config)) {
                return static::$_config;
            } else {
                die('static::$_config not set!'); // @todo fix this
            }
        } else {
            if (false === strpos($field, '.')) {
                if (isset(static::$_config[$field])) {
                    return static::$_config[$field];
                } else {
                    die('Variable not set static::$_config[' . $field . ']');
                }
            } else {
                $exp = explode('.', $field);

                // @todo Override for debug if false for all sub fields
                /*
                if($exp[0] == 'debug') && static::$_config[$exp[0]] == false) {
                    return false;
                }
                */

                if (count($exp) == 2) {
                    if (isset(static::$_config[$exp[0]][$exp[1]])) {
                        return static::$_config[$exp[0]][$exp[1]];
                    } else {
                        die('Variable does not exist "static::$_config[' . $exp[0] . '][' . $exp[1] . ']"');
                    }
                } elseif (count($exp) == 3) {
                    if (isset(static::$_config[$exp[0]][$exp[1]][$exp[2]])) {
                        return static::$_config[$exp[0]][$exp[1]][$exp[2]];
                    } else {
                        die('Variable does not exist "static::$_config[' . $exp[0] . '][' . $exp[1] . '][' . $exp[2] . ']"');
                    }
                } elseif (count($exp) == 4) {
                    if (isset(static::$_config[$exp[0]][$exp[1]][$exp[2]][$exp[3]])) {
                        return static::$_config[$exp[0]][$exp[1]][$exp[2]][$exp[3]];
                    } else {
                        die('Variable does not exist "static::$_config[' . $exp[0] . '][' . $exp[1] . '][' . $exp[2] . '][' . $exp[3] . ']"');
                    }
                } else {
                    // @todo Add more levels in depth to the static::$_config variable
                    die('Fix BootStrap > getConfig with more...');
                }
            }
        }
    }

    public static function initDBPDO()
    {
        self::$_dbPdo = new DatabasePDO;
    }

    public static function getDBPDO()
    {
        return self::$_dbPdo;
    }

    public static function initDB()
    {
        self::$_db = new Database;
    }

    public static function getDB()
    {
        return self::$_db;
    }

    public static function isCommandLine()
    {
        if (PHP_SAPI == 'cli') {
            $value = true;
            set_time_limit(0);
        } else {
            $value = false;
            if (isset(self::$_config['set_time_out'])) {
                set_time_limit(self::$_config['set_time_out']);
            }
        }
        // define('IS_CRON', $value);
        // define('IS_COMMAND_LINE', $value);
        return $value;
    }

    /**
     * Sets if debug is turned on of off in ini according to config
     *
     * @param array $_config Array of configuration variables from bootstrap.php, tpl/[tpl]/config.php
     */
    private static function setDebug()
    {
        if (isset(self::$_config['debug']['basic']) && self::$_config['debug']['basic'] === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
    }

    private static function wwwRedirect()
    {
        if (!self::isCommandLine()) {
            $domain = $_SERVER['HTTP_HOST'];
            if ($domain == self::$_config['domain']) {
                header("location: " . self::$_config['domain_full']);
                exit;
            }
        }
    }

    private static function setTimeZone()
    {
        date_default_timezone_set(self::$_config['timezone']);
    }
}
