<?php

namespace neptune\cache;

use neptune\core\Config;
use neptune\error\ErrorHandler;
use neptune\exceptions\ConfigKeyException;
use neptune\exceptions\RequiredConfigKeyException;
use neptune\exceptions\FileException;
use neptune\core\Loader;
use neptune\cache\drivers\DummyDriver;

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
	public static function getCacheDriver($name = null) {
		if ($name == null) {
			if (!empty(self::$caches)) {
				reset(self::$caches);
				$name = key(self::$caches);
			} else {
				return self::createFromConfig();
			}
		}
		if (!array_key_exists($name, self::$caches)) {
			self::$caches[$name] = self::createFromConfig($name);
		}
		return self::$caches[$name];
	}

	public static function createFromConfig($name = null) {
		if ($name) {
			$driver = 'neptune\cache\drivers\\' . ucfirst(Config::getRequired("cache.$name.driver")) . 'Driver';
			$host = Config::getRequired("cache.$name.host");
			$port = Config::getRequired("cache.$name.port");
		} else {
			$array = Config::getRequired('cache');
			reset($array);
			$name = key($array);
			$driver = 'neptune\cache\drivers\\' . ucfirst(Config::getRequired("cache.$name.driver")) . 'Driver';
			$host = Config::getRequired("cache.$name.host");
			$port = Config::getRequired("cache.$name.port");
		}
		if (Loader::softLoad($driver)) {
			self::$caches[$name] = new $driver($host, $port);
			return self::$caches[$name];
		} else {
			throw new FileException("Cache driver not found: $driver");
		}
	}

}

?>
