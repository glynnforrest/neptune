<?php

namespace Neptune\Tests\Cache\Drivers;

/**
 * FileDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class CacheDriverTest extends \PHPUnit_Framework_TestCase {

	protected $driver;

	public function cacheDataProvider() {
		return array(
			array('foo', 'bar'),
			array('1', 1),
			array(1, 1),
			array('an-array', array()),
			array('another-array', array(1, '2', array(), new \stdClass())),
			array('object', new \stdClass())
		);
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetAndSet($key, $val) {
		$this->driver->set($key, $val);
		$this->assertEquals($val, $this->driver->get($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetAndSetNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertEquals($val, $this->driver->get($key, false));
	}

	public function testGetReturnsNullOnMiss() {
		$this->assertNull($this->driver->get('foo'));
		$this->assertNull($this->driver->get('foo', false));
	}

}