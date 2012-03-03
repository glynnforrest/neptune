<?php

namespace neptune\helpers;

use neptune\core\Config;

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

}
?>
