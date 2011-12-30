<?php

namespace neptune\cache\drivers;

use Memcached;

/**
 * MemcachedDriver
 * This requires the Memcached PHP extension.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class MemcachedDriver implements CacheDriver {

	protected $memcached;

	public function __construct($host, $port) {
		$this->memcached = new Memcached();
		$this->memcached->addserver($host,$port); 
	}

	public function add($key, $value, $time = null) {
		return $this->memcached->add($key, $value, $time);
	}

	public function set($key, $value, $time = null) {
		return $this->memcached->set($key, $value, $time);
	}

	public function get($key) {
		return $this->memcached->get($key);
	}

	public function delete($key, $time = null) {
		return $this->memcached->delete($key, $time);
	}

	public function flush($time = null) {
		return $this->memcached->flush($time);
	}

}

?>
