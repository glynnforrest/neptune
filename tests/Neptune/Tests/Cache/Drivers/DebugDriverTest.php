<?php

namespace Neptune\Tests\Cache\Drivers;

use Neptune\Cache\Drivers\DebugDriver;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * DebugDriverTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriverTest extends CacheDriverTest {

	protected $driver;

	public function setUp() {
		$this->driver = new DebugDriver('testing_');
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

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDelete($key, $val) {
		$this->driver->set($key, $val);
		$this->assertTrue($this->driver->delete($key));
		$this->assertNull($this->driver->get($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDeleteNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertTrue($this->driver->delete($key, false));
		$this->assertNull($this->driver->get($key, false));
	}

	public function testDeleteNonExistent() {
		$this->assertTrue($this->driver->delete('not_here'));
		$this->assertNull($this->driver->get('not_here'));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testFlush($key, $val) {
		$this->driver->set($key,$val);
		$this->driver->flush();
		$this->assertSame(array(), $this->driver->dump());
	}
}