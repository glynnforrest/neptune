<?php

namespace Neptune\Database;

use Neptune\Core\Config;
use Neptune\Exceptions\DriverNotFoundException;

/**
 * DatabaseFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 *
 */
class DatabaseFactory {

	protected static $databases = array();

	/**
	 * @return DatabaseDriver
	 * A neptune database driver.
	 */
	public static function getDriver($db = null) {
		if (!$db) {
			if (!empty(self::$databases)) {
				reset(self::$databases);
				return current(self::$databases);
			} else {
				return self::createDriver();
			}
		}
		if (!array_key_exists($db, self::$databases)) {
			return self::createDriver($db);
		}
		return self::$databases[$db];
	}

	protected static function createDriver($dbname = null) {
		$pos = strpos($dbname, '#');
		if($pos) {
			$prefix = substr($dbname, 0, $pos);
			$name = substr($dbname, $pos + 1);
			$c = Config::load($prefix);
		} else {
			$name = $dbname;
			$c = Config::load();
		}
		if (!$name) {
			$array = $c->getRequired('database');
			reset($array);
			$name = key($array);
			$dbname = isset($prefix)? $prefix . '#' . $name: $name;
		}
		$driver = 'Neptune\Database\Drivers\\' . ucfirst($c->getRequired("database.$name.driver")) . 'Driver';
		$database = $c->getRequired("database.$name.database");
		$host = $c->get("database.$name.host");
		$port = $c->get("database.$name.port");
		$user = $c->get("database.$name.user");
		$pass = $c->get("database.$name.pass");
		$builder = $c->get("database.$name.builder");

		if (class_exists($driver)) {
			self::$databases[$dbname] = new $driver
			($host, $port, $user, $pass, $database);
			if($builder) {
				self::$databases[$dbname]->setBuilderName($builder);
			}
			return self::$databases[$dbname];
		} else {
			throw new DriverNotFoundException("Database driver not found: " . $driver);
		}
	}

}

?>
