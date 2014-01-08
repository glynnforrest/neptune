<?php

namespace Neptune\Cache\Drivers;

use Neptune\Exceptions\ConfigKeyException;

use Temping\Temping;

/**
 * FileDriver
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FileDriver implements CacheDriver {

	protected $temping;
	protected $prefix;

	public function __construct(array $config) {
		if(!isset($config['prefix']) ||
		!isset($config['dir'])) {
			throw new ConfigKeyException(
				'Incorrect credentials supplied to file cache driver.'
			);
		}
		$this->prefix = $config['prefix'];
		$this->temping = new Temping($config['dir']);
	}

	public function add($key, $value, $time = null, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		return $this->temping->create($key, $value);
	}

	public function set($key, $value, $time = null, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		return $this->temping->create($key, $value);
	}

	public function get($key, $use_prefix = true) {
		if($use_prefix) {
			$key = $this->prefix . $key;
		}
		try {
			return $this->temping->getContents($key);
		} catch (\Exception $e) {
			return null;
		}
	}

	public function delete($key, $time = null, $use_prefix = true) {
		$this->temping->delete($key);
		return true;
	}

	public function flush($time = null, $use_prefix = true) {
		$this->temping->reset();
		$this->temping->init();
	}

}
