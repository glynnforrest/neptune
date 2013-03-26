<?php

namespace Neptune\Cache;

use Neptune\Core\Config;
use Neptune\Exceptions\DriverNotFoundException;

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
				return current(self::$caches);
			} else {
				return self::createDriver();
			}
		}
		if (!array_key_exists($name, self::$caches)) {
			return self::createDriver($name);
		}
		return self::$caches[$name];
	}

	protected static function createDriver($name = null) {
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
			$array = $c->getRequired("cache");
			reset($array);
			$key = key($array);
			$name = isset($prefix)? $prefix . '#' . $key: $key;
		}
		$driver = 'Neptune\Cache\Drivers\\' . ucfirst($c->getRequired("cache.$key.driver")) . 'Driver';
		$config = $c->getRequired("cache.$key");
		if (class_exists($driver)) {
			self::$caches[$name] = new $driver($config);
			return self::$caches[$name];
		} else {
			throw new DriverNotFoundException("Cache driver not found: $driver");
		}
	}

	public static function remove($name = null) {
		if($name) {
			unset(self::$caches[$name]);
		} else {
			self::$caches = array();
		}
	}

}
