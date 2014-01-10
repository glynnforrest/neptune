<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\Cacheable;

/**
 * FooCacheable
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FooCacheable extends Cacheable {

	protected $bar;

	public function foo() {
		return 'Foo';
	}

	public function setBar(\stdClass $bar) {
		$this->bar = $bar;
	}

	public function bar() {
		return $this->bar->baz();
	}

}
