<?php namespace Ascend\Core\Feature;

class Session {
	
	/**
	 * Non Static Functions
	 */
	/*
    public function __construct() {
        session_start();
    }
    
	public function get($var) {
        if(isset($_SESSION[$var])){
            return $_SESSION[$var];
        }else{
            return null;
        }
    }
    
    public function set($var, $val) {
        $_SESSION[$var] = $val;
    }
    
    public function delete($var = false) {
        if($var === false){
            session_unset();
        }else{
            unset($_SESSION[$var]);
        }
    }
	*/
	/**
	 * Static Functions
	 */
	
	public static function start() {
		$version = phpversion();
		$ve = explode('.', $version);

        if(session_id() == '') {
            ini_set('session.gc_maxlifetime', 12*3600);
            session_set_cookie_params(12*3600);
            session_start();
        } else {
            $data = $_SESSION;
            session_destroy();
            ini_set('session.gc_maxlifetime', 12*3600);
            session_set_cookie_params(12*3600);
            session_start();
            $_SESSION = $data;
        }

        /*
        if ($ve[0] >= 5 && $ve[1] >= 4) {
            if (session_status() == PHP_SESSION_NONE) {
                ini_set('session.gc_maxlifetime', 12*3600);
                session_set_cookie_params(12*3600);
                session_start();
            }
        } else {
            if(session_id() == '') {
                ini_set('session.gc_maxlifetime', 12*3600);
                session_set_cookie_params(12*3600);
                session_start();
            }
        }
        */
	}
	
	public static function exist($var) {
		self::start();
		if(isset($_SESSION[$var])){
            return true;
        }else{
            return false;
        }
	}
	
	public static function get($var) {
		self::start();
		if(isset($_SESSION[$var])){
            return $_SESSION[$var];
        }else{
            return null;
        }
	}
	
    public static function set($var, $val) {
		self::start();
        $_SESSION[$var] = $val;
    }
    
    public static function delete($var = false) {
		self::start();
        if($var === false){
            session_unset();
        }else{
            unset($_SESSION[$var]);
        }
    }
}