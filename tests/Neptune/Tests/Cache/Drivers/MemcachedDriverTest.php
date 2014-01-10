<?php

namespace Neptune\Tests\Cache\Drivers;

use Neptune\Tests\Cache\Drivers\CacheDriverTest;
use Neptune\Cache\Drivers\MemcachedDriver;

use Temping\Temping;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * MemcachedDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MemcachedDriverTest extends CacheDriverTest {

	protected $driver;
	protected $mock;

	public function setup() {
		if(!class_exists('\\Memcached')) {
			$this->markTestSkipped('Memcached extension not installed.');
		}
		$this->mock = $this->getMock('Memcached');
		$this->driver = new MemcachedDriver('testing_', $this->mock);
	}

	protected function expectSet($key, $val, $prefix) {
		if($prefix) {
			$this->mock->expects($this->once())
					   ->method('set')
					   ->with('testing_' . $key, $val);
		} else {
			$this->mock->expects($this->once())
					   ->method('set')
					   ->with($key, $val);
		}
	}

	protected function expectGet($key, $val, $prefix) {
		if($prefix) {
			$this->mock->expects($this->once())
					   ->method('get')
					   ->with('testing_' . $key)
					   ->will($this->returnValue($val));
		} else {
			$this->mock->expects($this->once())
					   ->method('get')
					   ->with($key)
					   ->will($this->returnValue($val));
		}
		$this->mock->expects($this->once())
				   ->method('getResultCode')
				   ->will($this->returnValue(0));
	}

	protected function expectDelete($key, $prefix) {
		if($prefix) {
			$this->mock->expects($this->once())
					   ->method('delete')
					   ->with('testing_' . $key)
					   ->will($this->returnValue(true));
		} else {
			$this->mock->expects($this->once())
					   ->method('delete')
					   ->with($key)
					   ->will($this->returnValue(true));
		}
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testSet($key, $val) {
		$this->expectSet($key, $val, true);
		$this->driver->set($key, $val);
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testSetNoPrefix($key, $val) {
		$this->expectSet($key, $val, false);
		$this->driver->set($key, $val, null, false);
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGet($key, $val) {
		$this->expectGet($key, $val, true);
		$this->assertEquals($val, $this->driver->get($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetNoPrefix($key, $val) {
		$this->expectGet($key, $val, false);
		$this->assertEquals($val, $this->driver->get($key, false));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDelete($key, $val) {
		$this->expectDelete($key, true);
		$this->assertTrue($this->driver->delete($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDeleteNoPrefix($key, $val) {
		$this->expectDelete($key, false);
		$this->assertTrue($this->driver->delete($key, false));
	}

	public function testDeleteNonExistent() {
		$this->expectDelete('not_here', true);
		$this->assertTrue($this->driver->delete('not_here'));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testFlush($key, $val) {
		$this->expectSet($key, $val, true);
		$this->driver->set($key,$val);
		$this->mock->expects($this->once())
				   ->method('flush');
		$this->driver->flush();
	}

}
