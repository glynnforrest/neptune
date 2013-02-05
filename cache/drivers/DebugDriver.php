<?php

namespace neptune\cache\drivers;

use neptune\exceptions\ConfigKeyException;


/**
 * DebugDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DebugDriver implements CacheDriver {

	protected $cache = array();
	protected $prefix;

	public function __construct(array $config) {
		if(!isset($config['prefix'])) {
			throw new ConfigKeyException('Incorrect credentials
		supplied to debug cache driver');
		}
		$this->prefix = $config['prefix'];

	}

	public function add($key, $value, $time = null, $use_prefix = true) {
		$this->cache[$this->prefix . $key] = $value;
	}

	public function set($key, $value, $time = null, $use_prefix = true) {
		$this->cache[$this->prefix . $key] = $value;
	}

	public function get($key, $use_prefix = true) {
		return isset($this->cache[$this->prefix . $key]) ?
		$this->cache[$this->prefix . $key]: false;
	}

	public function delete($key, $time = null, $use_prefix = true) {
		unset($this->cache[$this->prefix . $key]);
	}

	public function flush($time = null, $use_prefix = true) {
		$this->cache = array();

	}

	public function dump() {
		return $this->cache;
	}
}