<?php namespace Ascend\Core;

class Session
{

    private static $db_session = null;

    public static function getSessionDB()
    {
        return self::$db_session;
    }

    public static function start()
    {
        // ini_set('session.save_handler', 'files');
        self::$db_session = new SessionDB('mysql', DB_HOST, DB_NAME, DB_USER, DB_PASS);
        $handler = self::$db_session;
        if(!isset($_SESSION)) { // REQUIRED FOR 7.2+ TO WORK; else PHP Warning!
            session_set_save_handler(
                array($handler, 'open'),
                array($handler, 'close'),
                array($handler, 'read'),
                array($handler, 'write'),
                array($handler, 'destroy'),
                array($handler, 'gc')
            );
            // register_shutdown_function('session_write_close');
            if (session_status() == PHP_SESSION_NONE) {
                // session_name();
                try {
                    @session_start();
                } catch (\Exception $e) {
                    echo 'Run migration to created database!<br />';
                    echo 'Caught exception: ' . $e->getMessage();
                }
            }
        }

        // $db_session->updateUserId($userId, session_id());
    }

    public static function exists($var)
    {
        self::start();
        if (isset($_SESSION[$var])) {
            return true;
        } else {
            return false;
        }
    }

    // @todo 20190709 remove these
    public static function exist($var) {
        return self::exists($var);
    }

    public static function get($var)
    {
        self::start();
        if (isset($_SESSION[$var])) {
            return $_SESSION[$var];
        } else {
            return null;
        }
    }

    public static function set($var, $val)
    {
        self::start();
        $_SESSION[$var] = $val;
    }

    public static function delete($var = false)
    {
        self::start();
        if ($var !== false) {
            unset($_SESSION[$var]);
        }
    }

    public static function destroy() {
        $id = session_id();
        self::$db_session->destroy($id);
        // exit;
    }

    public static function getErrorString() {
        $err = '';
        if (self::exist('error_string')) {
            $err = self::get('error_string');
            self::delete('error_string');
        }
        return $err;
    }
    public static function setErrorString($message) {
        self::set('error_string', $message);
    }

    public static function getSuccessString() {
        $err = '';
        if (self::exist('success_string')) {
            $err = self::get('success_string');
            self::delete('success_string');
        }
        return $err;
    }
    public static function setSuccessString($message) {
        self::set('success_string', $message);
    }
}