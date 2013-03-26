<?php

namespace Neptune\Security;

use Neptune\Core\Config;
use Neptune\Exceptions\DriverNotFoundException;

/**
 * SecurityFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class SecurityFactory {

	protected static $drivers = array();
	protected static $registered = array();

	/**
	 * @return SecurityDriver
	 * A neptune security driver.
	 */
	public static function getDriver($name = null) {
		if ($name == null) {
			if (!empty(self::$drivers)) {
				reset(self::$drivers);
				return current(self::$drivers);
			} else {
				return self::createDriver();
			}
		}
		if (!array_key_exists($name, self::$drivers)) {
			return self::createDriver($name);
		}
		return self::$drivers[$name];
	}

	public static function createDriver($name = null) {
		$pos = strpos($name, '#');
		if($pos) {
			$prefix = substr($name, 0, $pos);
			$key = substr($name, $pos + 1);
			$c = Config::load($prefix);
		} else {
			$key = $name;
			$c = Config::load();
		}
		if (!$key) {
			$array = $c->getRequired("security");
			reset($array);
			$key = key($array);
			$name = isset($prefix)? $prefix . '#' . $key: $key;
		}
		$driver = $c->getRequired("security.$key");
		$driver = array_key_exists($driver, self::$registered) ?
			self::$registered[$driver] : '\\Neptune\\Security\\Drivers\\' .
			ucfirst($driver) . 'Driver';
		if (class_exists($driver)) {
			self::$drivers[$name] = new $driver();
			return self::$drivers[$name];
		} else {
			throw new DriverNotFoundException("Security driver not found: $driver");
		}
	}

	public static function registerDriver($name, $class_name) {
		self::$registered[$name] = $class_name;
	}

}
