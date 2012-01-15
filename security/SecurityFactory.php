<?php

namespace neptune\security;

use neptune\core\Config;
use neptune\core\Loader;
use neptune\exceptions\DriverNotFoundException;

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
				$name = key(self::$drivers);
			} else {
				return self::createDriver();
			}
		}
		if (!array_key_exists($name, self::$drivers)) {
			self::$drivers[$name] = self::createDriver($name);
		}
		return self::$drivers[$name];
	}

	public static function createDriver($name = null) {
		if($name) {
			$driver = Config::getRequired("security.$name");
		} else {
			$array = Config::getRequired("security");
			reset($array);
			$driver = $array[key($array)];
		}
		$driver = array_key_exists($driver, self::$registered) ?
			self::$registered[$driver] : '\\neptune\\security\\drivers\\' . 
			ucfirst($driver) . 'Driver';
		if (Loader::softLoad($driver)) {
			self::$drivers[$name] = new $driver();
			return self::$drivers[$name];
		} else {
			throw new DriverNotFoundException("Security driver not found: $driver");
		}
	}

}

?>
