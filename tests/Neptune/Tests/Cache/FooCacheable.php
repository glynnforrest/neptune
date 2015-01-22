<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\Cacheable;
use Neptune\Config\Config;

/**
 * FooCacheable
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FooCacheable extends Cacheable {

	protected $config;

	public function foo() {
		return 'Foo';
	}

	public function setConfig(Config $config) {
		$this->config = $config;
	}

	public function configUsingMethod() {
        return $this->config->get('foo');
	}
}
