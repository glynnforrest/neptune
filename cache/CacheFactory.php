<?php

namespace neptune\cache;

use neptune\core\Config;
use neptune\core\Loader;
use neptune\exceptions\DriverNotFoundException;

/**
 * CacheFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class CacheFactory {

	protected static $caches = array();

	/**
	 * @return CacheDriver
	 * A neptune cache driver.
	 */
	public static function getDriver($name = null) {
		if ($name == null) {
			if (!empty(self::$caches)) {
				reset(self::$caches);
				$name = key(self::$caches);
			} else {
				return self::createDriver();
			}
		}
		if (!array_key_exists($name, self::$caches)) {
			self::$caches[$name] = self::createDriver($name);
		}
		return self::$caches[$name];
	}

	protected static function createDriver($name = null) {
		if(!$name) {
			$array = Config::getRequired('cache');
			reset($array);
			$name = key($array);
		}
		$driver = 'neptune\cache\drivers\\' . ucfirst(Config::getRequired("cache.$name.driver")) . 'Driver';
		$port = Config::getRequired("cache.$name.port");
		$host = Config::getRequired("cache.$name.host");
		if (Loader::softLoad($driver)) {
			self::$caches[$name] = new $driver($host, $port);
			return self::$caches[$name];
		} else {
			throw new DriverNotFoundException("Cache driver not found: $driver");
		}
	}

}

?>
