<?php

namespace neptune\helpers;

/**
 * Timer
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Timer {

	protected static $times = array();
	protected static $stopped = array();

	public static function start($name) {
		self::$times[$name] = microtime(true);
		if (isset(self::$stopped[$name])) {
			unset(self::$stopped[$name]);
		}
	}

	public static function stop($name) {
		if (!isset(self::$stopped[$name]) && isset(self::$times[$name])) {
			self::$times[$name] = microtime(true) - self::$times[$name];
			self::$stopped[$name] = true;
		}
	}

	public static function current($name) {
		if (isset(self::$times[$name])) {
			return!isset(self::$stopped[$name]) ? microtime(true) - self::$times[$name] : false;
		}
		return false;
	}

	public static function result($name) {
		return isset(self::$times[$name]) && isset(self::$stopped[$name]) ? self::$times[$name] : false;
	}

	public static function results() {
		$times = array();
		foreach (self::$stopped as $k => $v) {
			$times[$k] = self::$times[$k];
		}
		return $times;
	}

}

?>
