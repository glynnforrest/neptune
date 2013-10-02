<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Neptune;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * NeptuneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class NeptuneTest extends \PHPUnit_Framework_TestCase {

	protected $neptune;

	public function setUp() {
		$this->neptune = Neptune::getInstance();
		$this->neptune->reset();
	}

	public function testGetAndSet() {
		$this->neptune->set('my-component', function() {
			return new \stdClass();
		});
		$component = $this->neptune->get('my-component');
		$this->assertTrue($component instanceof \stdClass);
	}

	public function testGetThrowsExceptionNotRegistered() {
		$this->setExpectedException('\Neptune\Core\ComponentException');
		$this->neptune->get('my-component');
	}

	public function testGetThrowsExceptionNotCallable() {
		$this->neptune->set('my-component', 'no-a-function');
		$this->setExpectedException('\Neptune\Core\ComponentException');
		$this->neptune->get('my-component');
	}

	public function testGetCreatesNewObjectEveryTime() {
		$this->neptune->set('my-component', function() {
			return new \stdClass();
		});
		$one = $this->neptune->get('my-component');
		$two = $this->neptune->get('my-component');
		$this->assertNotSame($one, $two);
	}

	public function testGetAndSetSingleton() {
		$this->neptune->setSingleton('my-singleton', function() {
			return new \stdClass();
		});
		$one = $this->neptune->get('my-singleton');
		$two = $this->neptune->get('my-singleton');
		$this->assertSame($one, $two);
	}

}
