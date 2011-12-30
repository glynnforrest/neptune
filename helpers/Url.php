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
		foreach($args as $k => $v) {
			$url = str_replace(':' . $k, $v, $url);
		}
		return self::to($url, $protocol);
	}
}
?>
