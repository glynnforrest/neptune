<?php

namespace Neptune\Cache;

use Neptune\Cache\Drivers\DebugDriver;
use Neptune\Cache\Drivers\FileDriver;

use Neptune\Core\Config;
use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

use Temping\Temping;

/**
 * CacheFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class CacheFactory {

	protected $config;
	protected $drivers = array();

	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Get the driver called $name in the config instance. If $name is
	 * not specified, the first driver will be returned.
	 *
	 * @param string $name The name of the cache driver
	 */
	public function getDriver($name = null) {
		if (!$name) {
			if (!empty($this->drivers)) {
				reset($this->drivers);
				return current($this->drivers);
			} else {
				return $this->createDriver();
			}
		}
		if (array_key_exists($name, $this->drivers)) {
			return $this->drivers[$name];
		}
		return $this->createDriver($name);
	}

	protected function createDriver($name = null) {
		if (!$name) {
			$cache_names = array_keys($this->config->getRequired("cache"));
			if(empty($cache_names)) {
				throw new ConfigKeyException(
					"Cache configuration array is empty");
			}
			$name = $cache_names[0];
		}
		$driver = $this->config->getRequired("cache.$name.driver");
		$config_array = $this->config->getRequired("cache.$name");
		$method = 'create' . ucfirst($driver) . 'Driver';
		if (method_exists($this, $method)) {
			$this->drivers[$name] = $this->$method($config_array);
			return $this->drivers[$name];
		} else {
			throw new DriverNotFoundException(
				"Cache driver not implemented: $driver");
		}
	}

	protected function readArray(array $array, $name) {
		return isset($array[$name]) ? $array[$name] : null;
	}

	public function createDebugDriver(array $config) {
		return new DebugDriver($this->readArray($config, 'prefix'));
	}

	public function createFileDriver(array $config) {
		$prefix = $this->readArray($config, 'prefix');
		$dir = $this->readArray($config, 'prefix');
		return new FileDriver($prefix, new Temping($dir));
	}

}
