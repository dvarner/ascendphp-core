<?php namespace Ascend;

/**
 * BootStrap loads in all controllers / models needed when they are called and their dependencies
 * IoC aka Inversion of Control container
 */
class Debug {
    
    /**
     * Holds all the configurations variables
     */
    private static $_debug;
    
    public static function showError() {
        ob_start();
        var_dump(debug_backtrace(), true);
        $data = ob_get_contents();
        ob_end_clean();
        return '<pre>' . $data . '</pre>';
    }
    
    public static function showFileLine() {
        $data = debug_backtrace();
        $display = '';
        $display.= 'File: ' . $data[0]['file'] . '<br />';
        $display.= 'Line: ' . $data[0]['line'] . '<br />';
        return $display;
    }
    
    public static function dump($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
	
	public static function logTime($msg = '') {
		static::$_debug['logTime'][] = array('tm' => self::microtime_float(), 'msg' => $msg);
	}
	
	public static function displayLogTime() {
		self::logTime('Last call before display');
		$output = '';
		$first = null;
		$last = null;
		foreach (static::$_debug['logTime'] AS $k => $v) {
			if($k == 0) {
				$first = $v['tm'];
				$lapse = 'Init';
			} else {
				$lapse = round($v['tm'] - $prev, 4);
			}
			$output.= $lapse . ': ' . $v['msg'] . '<br />' . RET;
			$prev = $v['tm'];
		}
		$last = $v['tm'];
		$lapse = round($last - $first, 4);
		$output.= $lapse . ': Total<br />' . RET;
		return $output;
	}
	
	protected static function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

Debug::logTime('Initial');