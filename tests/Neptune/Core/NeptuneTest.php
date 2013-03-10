<?php

namespace Neptune\Core;

use Neptune\Core\Neptune;
use Neptune\Core\Config;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * NeptuneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class NeptuneTest extends \PHPUnit_Framework_TestCase {

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
		Config::create('test');
		Config::set('some_key', 'value');
		Neptune::set('config_change', function() {
			Config::set('some_key', 'changed');
			return 1;
		});
		$this->assertEquals('value', Config::get('some_key'));
		$res = Neptune::get('config_change');
		$this->assertEquals('changed', Config::get('some_key'));
	}
}
?>
