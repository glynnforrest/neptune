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

	public function tearDown() {
		Config::unload();
	}

	public function testSetAndGet() {
		Neptune::set('key', 'value');
		$this->assertEquals('value', Neptune::get('key'));
	}

	public function testSimpleClass() {
		$class = new \stdClass();
		$class->foo = 'hello';
		Neptune::set('test', $class);
		$this->assertEquals($class, Neptune::get('test'));
	}

	public function testGetReturnsNull() {
		$this->assertNull(Neptune::get('foo'));
	}

	public function testFunctionIsCalled() {
		Neptune::set('class', function() {
			$class = new \stdClass();
			$class->key = 'value';
			return $class;
		});
		$this->assertEquals('value', Neptune::get('class')->key);
	}

	public function testFunctionNotCalledBeforeAccess() {
		$c = Config::create('test');
		$c->set('some_key', 'value');
		Neptune::set('config_change', function() use ($c) {
			$c->set('some_key', 'changed');
			return 1;
		});
		$this->assertEquals('value', $c->get('some_key'));
		$res = Neptune::get('config_change');
		$this->assertEquals('changed', $c->get('some_key'));
	}
}
