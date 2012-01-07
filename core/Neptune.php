<?php

namespace neptune\core;

use neptune\exceptions\NeptuneError;
use neptune\core\Events;

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

	public static function handleErrors() {
		set_error_handler('\neptune\core\Neptune::dealWithError');
		set_exception_handler('\neptune\core\Neptune::dealWithException');
	}

	public static function dealWithError($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
	}

	public static function dealWithException($exception) {
		Events::getInstance()->send($exception);
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
