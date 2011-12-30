<?php

namespace neptune\error;

use neptune\error\NeptuneError;

/**
 * ErrorHandler
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ErrorHandler {

	protected static $handlers = array();

	public static function init() {
		set_error_handler('\neptune\error\ErrorHandler::dealWithError');
		set_exception_handler('\neptune\error\ErrorHandler::dealWithException');
	}

	public static function dealWithError($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
	}

	public static function dealWithException($exception) {
		foreach(self::$handlers as $k => $v) {
			if($exception instanceof $k) {
				$v($exception);
			}
		}
	}

	public static function addHandler($exception_type, $function) {
		if(is_callable($function)) {
			self::$handlers[$exception_type] = $function;
		}
	}

}

?>
