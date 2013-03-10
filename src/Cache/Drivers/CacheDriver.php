<?php

namespace neptune\cache\drivers;

/**
 * CacheDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 */
interface CacheDriver {

	public function __construct(array $config);

	public function add($key, $value, $time = null, $use_prefix = true);

	public function set($key, $value, $time = null, $use_prefix = true);

	public function get($key, $use_prefix = true);

	public function delete($key, $time = null, $use_prefix = true);

	public function flush($time = null, $use_prefix = true);
}
