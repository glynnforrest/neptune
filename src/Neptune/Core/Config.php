<?php

namespace Neptune\Core;

use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\ConfigFileException;

/**
 * Config
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Config {

	protected static $instances = array();
	protected $values = array();
	protected $name;
	protected $filename;
	protected $modified = false;

	protected function __construct($name, $filename = null) {
		if($filename) {
			if(!file_exists($filename)) {
				throw new ConfigFileException(
					'Configuration file ' . $filename . ' not found');
			}
			$this->values = include $filename;
			if (!is_array($this->values)) {
				throw new ConfigFileException(
					'Configuration file ' . $filename . ' does not return a php array');
			}
			$this->filename = $filename;
		}
		$this->name = $name;
		return true;
	}

	/**
	 * Get a configuration value that matches $key.
	 * $key uses the dot array syntax: parent.child.child
	 * If the key matches an array the whole array will be returned.
	 * If no key is specified the entire configuration array will be
	 * returned.
	 * $default will be returned (null unless specified) if the key is
	 * not found.
	 */
	public function get($key = null, $default = null) {
		if(!$key) {
			return $this->values;
		}
		$parts = explode('.', $key);
		$scope = &$this->values;
		for ($i = 0; $i < count($parts) - 1; $i++) {
			if(!isset($scope[$parts[$i]])) {
				return $default;
			}
			$scope = &$scope[$parts[$i]];
		}
		if(isset($scope[$parts[$i]])) {
			return $scope[$parts[$i]];
		}
		return $default;
	}

	/**
	 * Get the first value from an array of configuration values that
	 * matches $key.
	 * $default will be returned (null unless specified) if the key is
	 * not found or does not contain an array.
	 */
	public function getFirst($key = null, $default = null) {
		$array = self::get($key);
		if(!is_array($array)) {
			return $default;
		}
		reset($array);
		return current($array);
	}

	/**
	 * Get a configuration value that matches $key in the same way as
	 * get(), but a ConfigKeyException will be thrown if
	 * the key is not found.
	 */
	public function getRequired($key) {
		$value = self::get($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required value not found: $key");
	}

	/**
	 * Get the first value from an array of configuration values that
	 * matches $key in the same way as getFirst(), but a
	 * ConfigKeyException will be thrown if the key is not found.
	 */
	public function getFirstRequired($key) {
		$value = self::getFirst($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required value not found: $key");
	}

	/**
	 * Set a configuration value with $key.
	 * $key uses the dot array syntax: parent.child.child.
	 * If $value is an array this will also be accessible using the
	 * dot array syntax.
	 */
	public function set($key, $value) {
		$parts = explode('.', $key);
		//loop through each part, create it if not present.
		$scope = &$this->values;
		$count = count($parts) - 1;
		for ($i = 0; $i < $count; $i++) {
			if(!isset($scope[$parts[$i]])) {
				$scope[$parts[$i]] = array();
			}
			$scope = &$scope[$parts[$i]];
		}
		$scope[$parts[$i]] = $value;
		$this->modified = true;
	}

	/**
	 * Create config settings with $name.
	 * $filename must be specified (or set with setFilename) if the
	 * settings are intended to be saved.
	 * Giving a $name that already exists will overwrite the settings
	 * with that name.
	 */
	public static function create($name, $filename = null) {
		if (array_key_exists($name, self::$instances)) {
			return self::$instances[$name];
		}
		self::$instances[$name] = new self($name);
		self::$instances[$name]->setFilename($filename);
		return self::$instances[$name];
	}

	/**
	 * Load config settings with $name from $filename.
	 * If $name is loaded, the same Config instance will be
	 * returned if $filename is not specified.
	 * If $name is loaded and $filename does not match with $name
	 * the instance with that name will be overwritten.
	 * If $name is not specified, the first loaded config file will be
	 * returned, or an exception thrown if no Config instances are
	 * set.
	 * If $override_name is supplied and matches the name of a loaded
	 * config file, the values of that Config instance will be
	 * overwritten with the values of the new file.
	 */
	public static function load($name = null, $filename = null, $override_name = null) {
		if (array_key_exists($name, self::$instances)){
			$instance = self::$instances[$name];
			if(!$filename || $instance->getFileName() === $filename) {
				return $instance;
			}
		}
		if(!$name) {
			if(empty(self::$instances)) {
				throw new ConfigFileException(
					'No configuration file loaded, unable to get default');
			}
			reset(self::$instances);
			return self::$instances[key(self::$instances)];
		}
		if(!$filename) {
			//attempt to load the file as a module, but only if the
			//neptune config has been loaded
			if(isset(self::$instances['neptune']) && self::loadModule($name)) {
				return self::$instances[$name];
			}
			//if it isn't a module, we can't do anything without a file
			throw new ConfigFileException(
				"No filename specified for configuration file $name"
			);
		}
		self::$instances[$name] = new self($name, $filename);
		if($override_name && isset(self::$instances[$override_name])) {
			Config::load($override_name)->override(
				self::$instances[$name]->get());
		}
		return self::$instances[$name];
	}

	/**
	 * Override values in this Config instance with values from
	 * $array.
	 */
	public function override(array $array) {
		$this->values = array_replace_recursive($this->values, $array);
	}

	/**
	 * Load the configuration for a module with $name.
	 * This will load the configuration file for the module and also
	 * override that configuration with anything found in
	 * config/modules/$name.php
	 */
	public static function loadModule($name) {
		try {
			$neptune = self::load('neptune');
		} catch (ConfigFileException $e){
			//neptune config not loaded
			//rethrow a ConfigFileException with a more useful message
			throw new ConfigFileException(
				"Neptune config not loaded, unable to load module $name.");
		}
		//fetch the module path and load the config file
		$module_config_file = $neptune->get('dir.root') . $neptune->getRequired('modules.' . $name) . 'config.php';
		$module_instance = self::load($name, $module_config_file);
		//check for a local config to override the module. It should
		//have the path config/modules/<modulename>.php
		$local_config_file = $neptune->getRequired('dir.root') .
			'config/modules/' . $name . '.php';
		try {
			//prepend _ to give it a unique name so it can be used individually.
			self::load('_' . $name, $local_config_file, $name);
		} catch (ConfigFileException $e) {
			//do nothing if there is no config file defined.
		}
		return $module_instance;
	}

	/**
	 * Load the environment configuration called $name. The
	 * environment should be located at config/env/$name.php. The
	 * values in this file will then be merged into the 'neptune'
	 * config instance.
	 */
	public static function loadEnv($name) {
		$file = self::load('neptune')->get('dir.root') .
			'config/env/' . $name . '.php';
		//load $name as a config file, merging into neptune
		return self::load($name, $file, 'neptune');
	}

	/**
	 * Unload configuration settings with $name, requiring them to be
	 * reloaded if they are to be used again.
	 * If $name is not specified, all configuration files will be
	 * unloaded.
	 */
	public static function unload($name=null) {
		if ($name) {
			unset(self::$instances[$name]);
		} else {
			self::$instances = array();
		}
	}

	/**
	 * Save the current configuration instance.
	 * A ConfigFileException will be thrown if filename is not set or
	 * if php can't write to the file.
	 */
	public function save() {
		if ($this->modified) {
			if (!empty($this->values)) {
				if(!$this->filename) {
					throw new ConfigFileException(
						"Unable to save configuration file '$this->name', \$filename is not set"
					);
				}
				if(!file_exists($this->filename) && !@touch($this->filename)){
					throw new ConfigFileException(
						"Unable to create configuration file
						$this->filename. Check file paths and permissions
						are correct."
					);
				};
				if(!is_writable($this->filename)) {
					throw new ConfigFileException(
						"Unable to write to configuration file
						$this->filename. Check file paths and permissions
						are correct."
					);
				}
				$content = '<?php return ' . var_export($this->values, true) . '?>';
				file_put_contents($this->filename, $content);
				return true;
			}
		}
		return true;
	}

	/**
	 * Call save() on all configuration instances.
	 */
	public static function saveAll() {
		foreach(self::$instances as $instance) {
			$instance->save();
		}
		return true;
	}

	/**
	 * Set the filename for the current configuration instance.
	 */
	public function setFileName($filename) {
		$this->filename = $filename;
	}

	/**
	 * Get the filename of the current configuration instance.
	 */
	public function getFileName() {
		return $this->filename;
	}

}
