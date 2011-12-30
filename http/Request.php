<?php

namespace neptune\http;

/**
 * Request
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Request {

	public static function path() {
		$path = self::uri();
		$dot = strrpos($path, '.');
		if ($dot) {
			$path = substr($path, 0, $dot);
		}
		return $path;
	}

	public static function uri() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
			$mark = strpos($_SERVER['REQUEST_URI'], '?');
			if ($mark) {
				$uri = substr($uri, 0, $mark);
			}
			return $uri;
		}
		return null;
	}

	public static function ip() {
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	public static function method() {
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
	}

	public static function format() {
		$format = self::uri();
		if ($format) {
			$dot = strrpos($format, '.');
			if ($dot) {
				$format = substr($format, $dot + 1);
				return $format;
			}
		}
		return 'html';
	}

	public static function get($key = null) {
		if (!$key) {
			return $_GET;
		}
		return isset($_GET[$key]) ? $_GET[$key] : null;
	}

}

?>
