<?php

namespace Neptune\Cache\Drivers;

use Neptune\Cache\Drivers\CacheDriverInterface;

use \Memcached;

/**
 * MemcachedDriver
 * This requires the Memcached PHP extension.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class MemcachedDriver implements CacheDriverInterface {

	protected $memcached;
	protected $prefix;

	public function __construct($prefix, Memcached $memcached) {
		$this->prefix = $prefix;
		$this->memcached = $memcached;
	}

	public function set($key, $value, $time = null, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		return $this->memcached->set($key, $value, $time);
	}

	public function get($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		$result = $this->memcached->get($key);
		if ($this->memcached->getResultCode() === 0) {
			return $result;
		}
	}

	public function delete($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		return $this->memcached->delete($key);
	}

	public function flush($use_prefix = true) {
		return $this->memcached->flush();
	}

}
