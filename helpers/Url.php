<?php

namespace neptune\helpers;

use neptune\core\Config;
use neptune\core\Dispatcher;

/**
 * Url
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Url {

	public static function to($url, $protocol = 'http') {
		if(strpos($url, '://')) {
			return $url;
		}
		if(substr($url, 0, 1) !== '/') {
			$url = '/' . $url;
		}
		return $protocol . '://' . Config::getRequired('root_url') . $url;
	}

	public static function toRoute($name, $args = array(), $protocol = 'http') {
		$url = Dispatcher::getInstance()->getRouteUrl($name);
		if(preg_match_all('`:([a-zA-Z][a-zA-Z0-9]+)`', $url, $matches)) {
			foreach ($matches[1] as $m) {
				if(isset($args[$m])) {
					$url = str_replace(":{$m}", $args[$m], $url);
				} else {
					$url = str_replace(":{$m}", null, $url);
				}
			}
			$url = str_replace('(', '', $url);
			$url = str_replace(')', '', $url);
			if(substr($url, -1) === '/') {
				$url = substr($url, 0, -1);
			}
		}
		return self::to($url, $protocol);
	}

}
?>
