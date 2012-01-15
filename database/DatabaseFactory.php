<?php

namespace neptune\database;

use neptune\core\Config;
use neptune\exceptions\DriverNotFoundException;
use neptune\core\Loader;

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
	public static function getDatabase($db = null) {
		if ($db == null) {
			if (!empty(self::$databases)) {
				reset(self::$databases);
				$db = key(self::$databases);
				return self::$databases[$db];
			} else {
				return self::create();
			}
		}
		if (!array_key_exists($db, self::$databases)) {
			self::create($db);
		}
		return self::$databases[$db];
	}

	protected static function create($name = null) {
		if (!$name) {
			$array = Config::getRequired('database');
			reset($array);
			$name = key($array);
		}
		$driver = 'neptune\database\drivers\\' . ucfirst(Config::getRequired("database.$name.driver")) . 'Driver';
		$database = Config::getRequired("database.$name.database");
		$host = Config::get("database.$name.host");
		$port = Config::get("database.$name.port");
		$user = Config::get("database.$name.user");
		$pass = Config::get("database.$name.pass");
		$builder = Config::get("database.$name.builder");

		if (Loader::softLoad($driver)) {
			self::$databases[$name] = new $driver
				($host, $port, $user, $pass, $database);
			if($builder) {
				self::$databases[$name]->setBuilderName($builder);
			}
			return self::$databases[$name];
		} else {
			throw new DriverNotFoundException("Database driver not found: " . $driver);
		}
	}

}

?>
