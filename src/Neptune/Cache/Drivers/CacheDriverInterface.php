<?php

namespace Neptune\Cache\Drivers;

/**
 * CacheDriverInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 */
interface CacheDriverInterface {

	public function set($key, $value, $time = null, $use_prefix = true);

	public function get($key, $use_prefix = true);

	public function delete($key, $use_prefix = true);

	public function flush($use_prefix = true);

}
