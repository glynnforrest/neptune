<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;

class Neptune {

	protected static $instance;
	protected $registry = array();

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
		$me = self::getInstance();
		if (isset($me->registry[$key])) {
			if(is_callable($me->registry[$key])) {
				return $me->registry[$key]();
			}
			return $me->registry[$key];
		}
		return null;
	}

	public static function handleErrors() {
		set_error_handler('\Neptune\Core\Neptune::dealWithError');
		set_exception_handler('\Neptune\Core\Neptune::dealWithException');
	}

	public static function dealWithError($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
	}

	public static function dealWithException($exception) {
		Events::getInstance()->send($exception);
	}

}

?>
