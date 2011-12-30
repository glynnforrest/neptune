<?php

namespace neptune\database;

use neptune\core\Config;
use neptune\exceptions\ConfigKeyException;
use neptune\exceptions\RequiredConfigKeyException;
use neptune\exceptions\FileException;
use neptune\database\drivers\DummyDriver;
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
				return self::createDatabase(self::getDatabaseDetails());
			}
		}
		if (!array_key_exists($db, self::$databases)) {
			self::$databases[$db] = self::createDatabase(self::getDatabaseDetails($db));
		}
		return self::$databases[$db];
	}

	protected static function getDatabaseDetails($name = null) {
		if (!$name) {
			$array = Config::getRequired('database');
			reset($array);
			$name = key($array);
		}
		$details = array();
		$details['name'] = $name;
		$details['driver'] = 'neptune\database\drivers\\' . ucfirst(Config::getRequired("database.$name.driver")) . 'Driver';
		$details['database'] = Config::getRequired("database.$name.database");
		$details['host'] = Config::get("database.$name.host");
		$details['port'] = Config::get("database.$name.port");
		$details['user'] = Config::get("database.$name.user");
		$details['pass'] = Config::get("database.$name.pass");
		$details['builder'] = Config::get("database.$name.builder");
		return $details;
	}

	protected static function createDatabase(array $details) {
		if (Loader::softLoad($details['driver'])) {
			self::$databases[$details['name']] = new $details['driver']
				($details['host'], $details['port'], $details['user'], $details['pass'], $details['database']);
			if($details['builder']) {
				self::$databases[$details['name']]->setBuilderName($details['builder']);
			}
			return self::$databases[$details['name']];
		} else {
			throw new FileException("Database driver not found: " . $details['driver']);
		}
	}

}

?>
