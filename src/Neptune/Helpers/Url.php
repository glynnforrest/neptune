<?php

namespace Neptune\Helpers;

use Neptune\Core\Config;

/**
 * Url
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Url {

	public static function to($url, $protocol = 'http') {
		if(strpos($url, '://')) {
			return $url;
		}
		if(substr($url, 0, 1) == '/') {
			$url = substr($url, 1);
		}
		return $protocol . '://' . Config::load()->getRequired('root_url') . $url;
	}

}
