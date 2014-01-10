<?php

namespace Neptune\Tests\Cache\Drivers;

use Neptune\Tests\Cache\Drivers\CacheDriverTest;
use Neptune\Cache\Drivers\FileDriver;

use Temping\Temping;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FileDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FileDriverTest extends CacheDriverTest {

	protected $driver;
	protected $temp;

	public function setup() {
		$this->temp = new Temping();
		$this->driver = new FileDriver('testing_', $this->temp);
	}

	public function tearDown() {
		$this->temp->reset();
	}

	protected function setContents($key, $value) {
		$this->temp->create($key, serialize($value));
	}

	protected function getContents($key) {
		return unserialize($this->temp->getContents($key));
	}

	public function testNonWritableDirectoryThrowsException() {
		$this->setExpectedException('\Exception', "'some/dir/' is not a directory");
		$driver = new FileDriver('testing_', new Temping('some/dir'));
		$driver->set('foo', 'bar');
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testSet($key, $val) {
		$this->driver->set($key, $val);
		$this->assertTrue($this->temp->exists('testing_' . $key));
		$this->assertEquals($val, $this->getContents('testing_' . $key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testSetNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertTrue($this->temp->exists($key));
		$this->assertEquals($val, $this->getContents($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGet($key, $val) {
		$this->setContents('testing_' . $key, $val);
		$this->assertEquals($val, $this->driver->get($key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testGetNoPrefix($key, $val) {
		$this->setContents($key, $val);
		$this->assertEquals($val, $this->driver->get($key, false));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDelete($key, $val) {
		$this->driver->set($key, $val);
		$this->assertTrue($this->temp->exists('testing_' . $key));
		$this->assertTrue($this->driver->delete($key));
		$this->assertFalse($this->temp->exists('testing_' . $key));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testDeleteNoPrefix($key, $val) {
		$this->driver->set($key, $val, null, false);
		$this->assertTrue($this->temp->exists($key));
		$this->assertTrue($this->driver->delete($key, false));
		$this->assertFalse($this->temp->exists($key));
	}

	public function testDeleteNonExistent() {
		$this->assertTrue($this->driver->delete('not_here'));
		$this->assertFalse($this->temp->exists('not_here'));
	}

	/**
	 * @dataProvider cacheDataProvider()
	 */
	public function testFlush($key, $val) {
		$this->driver->set($key,$val);
		$this->assertFalse($this->temp->isEmpty());
		$this->driver->flush();
		$this->assertTrue($this->temp->isEmpty());
		$this->assertTrue($this->temp->exists());
	}

	public function testGetDirectory() {
		$this->assertEquals($this->temp->getDirectory(), $this->driver->getDirectory());
	}

	public function testDirectoryIsNotCreatedOnConstruct() {
		$this->assertFileNotExists($this->temp->getDirectory());
	}

}
