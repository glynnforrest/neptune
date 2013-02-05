<?php

namespace neptune\cache\drivers;

use neptune\exceptions\ConfigKeyException;
use Memcached;

/**
 * MemcachedDriver
 * This requires the Memcached PHP extension.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class MemcachedDriver implements CacheDriver {

	protected $memcached;
	protected $prefix;

	public function __construct(array $config) {
		if(!isset($config['host']) |
		   !isset($config['port']) |
		   !isset($config['prefix'])) {
			throw new ConfigKeyException('Incorrect credentials
		supplied to memcached cache driver');
		}
		$this->prefix = $config['prefix'];
		$this->memcached = new Memcached();
		$this->memcached->addserver($config['host'], $config['port']);
	}

	public function add($key, $value, $time = null, $use_prefix = true) {
		return $this->memcached->add($this->prefix . $key, $value, $time);
	}

	public function set($key, $value, $time = null, $use_prefix = true) {
		return $this->memcached->set($this->prefix . $key, $value, $time);
	}

	public function get($key, $use_prefix = null) {
		return $this->memcached->get($this->prefix . $key);
	}

	public function delete($key, $time = null, $use_prefix = true) {
		return $this->memcached->delete($this->prefix . $key, $time);
	}

	public function flush($time = null, $use_prefix = true) {
		return $this->memcached->flush($time);
	}

}
