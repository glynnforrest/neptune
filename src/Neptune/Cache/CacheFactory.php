<?php

namespace Neptune\Cache;

use Neptune\Cache\Driver\DebugDriver;
use Neptune\Cache\Driver\FileDriver;
use Neptune\Cache\Driver\MemcachedDriver;

use Neptune\Core\Config;
use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

use \Memcached;

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
		$config_array = $this->config->getRequired("cache.$name");

		//cache driver and prefix are required for all drivers
		if(!isset($config_array['driver'])) {
			throw new ConfigKeyException(
				"Cache configuration '$name' does not list a driver"
			);
		}
		$driver = $config_array['driver'];

		if(!isset($config_array['prefix'])) {
			throw new ConfigKeyException(
				"Cache configuration '$name' does not list a prefix"
			);
		}
		$prefix = $config_array['prefix'];

		$method = 'create' . ucfirst($driver) . 'Driver';
		if (method_exists($this, $method)) {
			$this->drivers[$name] = $this->$method($prefix, $config_array);
			return $this->drivers[$name];
		} else {
			throw new DriverNotFoundException(
				"Cache driver not implemented: $driver");
		}
	}

	protected function readArray(array $array, $name) {
		return isset($array[$name]) ? $array[$name] : null;
	}

	public function createDebugDriver($prefix) {
		return new DebugDriver($prefix);
	}

	public function createFileDriver($prefix, array $config) {
		$dir = $this->readArray($config, 'dir');
		if($dir && substr($dir, 0, 1) !== '/') {
			$dir = $this->config->getRequired('dir.root') . $dir;
		}
		return new FileDriver($prefix, new Temping($dir));
	}

	public function createMemcachedDriver($prefix, array $config) {
		$host = $this->readArray($config, 'host');
		$port = $this->readArray($config, 'port');
		$memcached = new Memcached();
		$memcached->addserver($host, $port);
		return new MemcachedDriver($prefix, $memcached);
	}

}
