<?php

namespace neptune\core;

use neptune\core\Neptune;

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
		$class = new \StdClass();
		$class->foo = 'hello';
		Neptune::set('test', $class);
		$this->assertEquals($class, Neptune::get('test'));
	}

	public function testGetReturnsNull() {
		$this->assertNull(Neptune::get('foo'));
	}

	public function testFunctionIsCalled() {
		Neptune::set('class', function() {
			$class = new \StdClass();
			$class->key = 'value';
			return $class;
		});
		$this->assertEquals('value', Neptune::get('class')->key);

	}
}
?>
