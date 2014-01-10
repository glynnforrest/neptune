<?php

namespace Neptune\Cache\Driver;

use Neptune\Cache\Driver\CacheDriverInterface;

/**
 * DebugDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DebugDriver implements CacheDriverInterface {

	protected $cache = array();
	protected $prefix;

	public function __construct($prefix) {
		$this->prefix = $prefix;
	}

	public function set($key, $value, $time = null, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		$this->cache[$key] = $value;
	}

	public function get($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		return isset($this->cache[$key]) ? $this->cache[$key]: null;
	}

	public function delete($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		unset($this->cache[$key]);
		return true;
	}

	public function flush($use_prefix = true) {
		$this->cache = array();
	}

	public function dump() {
		return $this->cache;
	}

}