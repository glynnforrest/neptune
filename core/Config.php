<?php

namespace neptune\core;

use neptune\exceptions\ConfigKeyException;
use neptune\exceptions\ConfigFileException;

class Config {

	protected $values = array();
	protected $names = array();
	protected $modified = false;
	protected static $instance;

	protected function __construct() {
	}

	protected static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function get($key = null, $default = null) {
		$pos = strpos($key, '#');
		if($pos) {
			$name = substr($key, 0, $pos);
			$key = substr($key, $pos + 1);
		} else {
			$name = null;
		}
		$me = self::getInstance();
		$name = $me->getFileIndex($name);
		if ($name) {
			if(!$key) {
				return $me->values[$name];
			}
			$parts = explode('.', $key);
			$scope = &$me->values[$name];
			for ($i = 0; $i < count($parts) - 1; $i++) {
				if(!isset($scope[$parts[$i]])) {
					return $default;
				}
				$scope = &$scope[$parts[$i]];
			}
			if(isset($scope[$parts[$i]])) {
				return $scope[$parts[$i]];
			}
		}
		return $default;
	}

	public static function getFirst($key = null, $default = null) {
		$array = self::get($key);
		if(!$key | !is_array($array)) {
			return $default;
		}
		reset($array);
		return current($array);
	}

	public static function getRequired($key) {
		$value = self::get($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required value not found: $key");
	}

	public static function getFirstRequired($key) {
		$value = self::getFirst($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required value not found: $key");
	}

	public static function set($key, $value) {
		$pos = strpos($key, '#');
		if($pos) {
			$name = substr($key, 0, $pos);
			$key = substr($key, $pos + 1);
		} else {
			$name = null;
		}
		$me = self::getInstance();
		$name = $me->getFileIndex($name);
		if ($name) {
			$parts = explode('.', $key);
			//loop through each part, create it if not present.
			$scope = &$me->values[$name];
			$count = count($parts) - 1;
			for ($i = 0; $i < $count; $i++) {
				if(!isset($scope[$parts[$i]])) {
					$scope[$parts[$i]] = array();
				}
				$scope = &$scope[$parts[$i]];
			}
			$scope[$parts[$i]] = $value;
			$me->modified = true;
		} else {
			return false;
		}
	}

	public static function bluff($name) {
		$me = self::getInstance();
		if (array_key_exists($name, $me->values)) {
			return true;
		}
		$me->values[$name] = array();
		$me->names[$name] = $name;
	}

	public static function load($file, $name=null) {
		$me = self::getInstance();
		if (array_key_exists($file, $me->values)) {
			return true;
		}
		$content = include $file;
		if (!is_array($content)) {
			throw new ConfigFileException(
				'Configuration file ' . $file . ' does not return a php array');
		}
		$me->values[$file] = $content;
		if ($name !== null) {
			$me->names[$name] = $file;
		}
		return true;
	}

	//TODO: Finish this function!
	public static function unload($name=null) {
		$me = self::getInstance();
		if ($name === null) {
			$me->values = array();
			$me->names = array();
		} else {

		}
	}

	public static function getAll() {
		return self::getInstance()->values;
	}

	public static function getNames() {
		return self::getInstance()->names;
	}

	/**
	 * @param string $name a filename or name of the file.
	 * @return string a key to the $values array.
	 */
	protected function getFileIndex($name) {
		if ($name === null) {
			reset($this->values);
			$name = key($this->values);
		}
		if (!empty($this->values)) {
			if (array_key_exists($name, $this->names)) {
				return $this->names[$name];
			} elseif (array_key_exists($name, $this->values)) {
				return $name;
			} else {
				throw new ConfigFileException(
					"Invalid config file name '$name' given");
			}
		} else {
			return null;
		}
	}

	public static function save($name = null) {
		$me = self::getInstance();
		if ($me->modified) {
			if ($name === null) {
				if (!empty($me->values)) {
					foreach ($me->values as $k => $v) {
						$me->saveConfig($k, $v);
					}
					return true;
				} else {
					throw new ConfigFileException('No configuration file loaded');
				}
			} else {
				$name = $me->getFileIndex($name);
				if ($name) {
					return $me->saveConfig($name, $me->values[$name]);
				}
			}
		}
		return false;
	}

	protected function saveConfig($file, $values) {
		$content = '<?php return ' . var_export($values, true) . '?>';
		file_put_contents($file, $content);
		return true;
	}

}

?>
