<?php namespace Ascend\Core;

class View
{
    public static function get($page, $variables = []) {
        throw new \Exception('Function removed; View::get(). Changed to View::html(). $page = '.$page);
        return self::html($page, $variables);
    }
	public static function html($page,$variables = []) {
		$html = '';
		$f = PATH_VIEWS . $page;
		if (file_exists($f)) {
			ob_start();
			extract($variables);
			require $f;
			$html .= ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}
	
	public static function css($file, $alwaysNew = false) {
		$tm = $alwaysNew ? '?tm=' . time() : '';
		$html = '<link rel="stylesheet" href="' . $file . $tm . '" />' . PHP_EOL;
		return $html;
	}
	
	public static function js($file, $alwaysNew = false) {
		$tm = $alwaysNew ? '?tm=' . time() : '';
		$html = '<script src="' . $file . $tm . '"></script>' . PHP_EOL;
		return $html;
	}
}
