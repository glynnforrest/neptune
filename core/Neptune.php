<?php

namespace neptune\core;

class Neptune {

	protected static $instance;
	protected $registry = array();
	protected $use_cache = false;

	protected function __construct() {
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function set($key, $value) {
		self::getInstance()->registry[$key] = $value;
	}

	public static function get($key) {
		if (isset(self::getInstance()->registry[$key])) {
			return self::getInstance()->registry[$key];
		}
		return null;
	}

	//TODO Remove, give to Cache instead (like Logger)
	public static function enableCache() {
		self::getInstance()->use_cache = true;
	}

	public static function disableCache() {
		self::getInstance()->use_cache = false;
	}

	public static function cacheEnabled() {
		return self::getInstance()->use_cache;
	}

}
