<?php namespace Ascend;

use Ascend\Debug;
use Ascend\Bootstrap as BS;

/**
 */
class Request
{
	public $setMethod = null;
	/**
	 * Non Static functions
	 */
	public function all() {
		
		$method = self::getRequestMethod();
		
		if (
			$method == 'GET'
			||
			(!is_null($this->setMethod) && $this->setMethod == 'GET')
		) {
			return $this->sanitize($_GET);
		} else if (
			$method == 'POST'
			||
			(!is_null($this->setMethod) && $this->setMethod == 'POST')
		) {
			return $this->sanitize($_POST);
		} else {
			parse_str(file_get_contents("php://input"), $params);
			// $params = json_decode(file_get_contents("php://input"), true);
			return $this->sanitize($params);
		}
	}
	
	public function input($variable) {
		return isset($_REQUEST[$variable]) ? $_REQUEST[$variable] : null;
	}
	
	private function sanitize($arr) {
		$clean = array();
		foreach ($arr AS $k => $v) {
			if (is_array($v)) {
				$clean[$k] = $this->sanitize($v);
			} else {
			    // @todo fix cleaning of data...
				$clean[$k] = addslashes($v); // mysqli_real_escape_string($v); // needs connection...
                $clean[$k] = trim(strip_tags($v)); //
			}
		}
		return $clean;
	}
	/**
	 * Static functions
	 */
	 public static function getRequestUriParsed() {
		$uri = $_SERVER['REQUEST_URI'];
		
		if (substr($uri, -1, 1) == '/') {
			$uri = substr($uri, 0, -1);
		}
		
		// @todo Possibly change below to this
		// parse_url($url, PHP_URL_PATH));
		// parse_str($str);
		
		$arr = [];
		if (false !== strpos($uri, '?')) {
			$arr = explode('?', $uri);
			if(count($arr) > 2){
				trigger_error('More than 1 ? more in uri!');
				exit;
			}
			$arr[1] = $_REQUEST;
		} else {
			$arr[] = $uri;
			$arr[] = [];
		}
		$arr[] = self::getRequestMethod();
		
		return $arr;
	}
	
	public static function getRequestMethod()
    {
		// Below allows api access outside the website
        // header("Access-Control-Allow-Orgin: *");
        // header("Access-Control-Allow-Methods: *");
		
		// <input type="hidden" name="_method" value="PUT">

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
		// if ($method != 'GET') { dd($method); }
        return $method;
    }
}