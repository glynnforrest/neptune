<?php

namespace neptune\http;

/**
 * Request
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Request {

	protected static $instance;
	protected $path;
	protected $uri;
	protected $format;

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() { 
	}

	public function path() {
		if($this->path) {
			return $this->path;
		}
		$path = self::uri();
		$dot = strrpos($path, '.');
		if ($dot) {
			$path = substr($path, 0, $dot);
		}
		$this->path = $path;
		return $path;
	}

	public function uri() {
		if($this->uri) {
			return $this->uri;
		}
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
			$mark = strpos($_SERVER['REQUEST_URI'], '?');
			if ($mark) {
				$uri = substr($uri, 0, $mark);
			}
			$this->uri = $uri;
			return $uri;
		}
		return null;
	}

	public function ip() {
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	public function method() {
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
	}

	public function format() {
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

	public function get($key = null) {
		if (!$key) {
			return $_GET;
		}
		return isset($_GET[$key]) ? $_GET[$key] : null;
	}

}

?>
