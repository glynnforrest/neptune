<?php

namespace neptune\cache\drivers;

/**
 * CacheDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 */
interface CacheDriver {

	public function __construct($host, $port);

	public function add($key, $value, $time = null);

	public function set($key, $value, $time = null);

	public function get($key);

	public function delete($key, $time = null);

	public function flush($time = null);
}

?>
