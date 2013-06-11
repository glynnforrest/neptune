<?php

namespace Neptune\Http;

/**
 * Session
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Session {

	/**
	 * Set $key to $value in the $_SESSION array. 
	 * If no session is set, session_start
	 * will be called.
	 */
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

	/**
	 * Get $key from the $_SESSION array. 
	 * If no session is set, session_start
	 * will be called.
	 */
	public static function get($key) {
		if(!isset($_SESSION)) {
			session_start();
		}
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	/**
	 * Get the token unique for this session.
	 */
	public static function token() {
		if(!isset($_SESSION)) {
			session_start();
		}
		if(!isset($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token' ] = md5(uniqid());
		}
		return $_SESSION['csrf_token'];
	}

	/**
	 * Flush (empty) the $_SESSION array.
	 */
	public static function flush() {
		$_SESSION = array();
	}

}

?>
