<?php

namespace neptune\http;

/**
 * Session
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Session {

	public static function set($key, $value = null) {
		if(!isset($_SESSION)) {
			session_start();
		}
		if(is_array($key)) {
			foreach($key as $k => $v) {
				$_SESSION[$k] = $v;
			}
		} else {
			$_SESSION[$key] = $value;
		}
	}

	public static function get($key) {
		if(!isset($_SESSION)) {
			session_start();
		}
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	public static function token() {
		if(!isset($_SESSION)) {
			session_start();
		}
		if(!isset($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token' ] = md5(uniqid());
		}
		return $_SESSION['csrf_token'];
	}

}

?>
