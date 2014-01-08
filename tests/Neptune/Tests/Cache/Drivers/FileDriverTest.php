<?php

namespace Neptune\Tests\Cache\Drivers;

use Neptune\Cache\Drivers\FileDriver;

use Temping\Temping;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FileDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FileDriverTest extends \PHPUnit_Framework_TestCase {

	public function setup() {
		$this->temp = new Temping();
		$config = array(
			'dir' => $this->temp->getDirectory(),
			'prefix' => 'testing_'
		);
		$this->driver = new FileDriver($config);
	}

	public function tearDown() {
		$this->temp->reset();
	}

	public function testNoConfigThrowsException() {
		$this->setExpectedException('\Exception');
		$driver = new FileDriver(array());
	}

	public function testNonWritableDirectoryThrowsException() {
		$this->setExpectedException('\Exception');
		$driver = new FileDriver(array(
			'dir' => 'some/dir',
			'prefix' => 'testing_'
		));
	}

	public function testAdd() {
		$this->driver->add('foo', 'bar');
		$this->assertTrue($this->temp->exists('testing_foo'));
		$this->assertEquals('bar', $this->temp->getContents('testing_foo'));
	}

	public function testAddNoPrefix() {
		$this->driver->add('foo', 'bar', null, false);
		$this->assertTrue($this->temp->exists('foo'));
		$this->assertEquals('bar', $this->temp->getContents('foo'));
	}

	public function testSet() {
		$this->driver->set('one', 'two');
		$this->assertTrue($this->temp->exists('testing_one'));
		$this->assertEquals('two', $this->temp->getContents('testing_one'));
	}

	public function testSetNoPrefix() {
		$this->driver->set('one', 'two', null, false);
		$this->assertTrue($this->temp->exists('one'));
		$this->assertEquals('two', $this->temp->getContents('one'));
	}

	public function testGet() {
		$this->temp->create('testing_fuzz', 'buzz');
		$this->assertEquals('buzz', $this->driver->get('fuzz'));
	}

	public function testGetNoPrefix() {
		$this->temp->create('fuzz', 'buzz');
		$this->assertEquals('buzz', $this->driver->get('fuzz', false));
	}

	public function testGetReturnsNullOnMiss() {
		$this->assertNull($this->driver->get('foo'));
		$this->assertNull($this->driver->get('foo', false));
	}

	public function testDelete() {
		$this->driver->set('whoops', 'something');
		$this->assertTrue($this->driver->delete('whoops'));
		$this->assertFalse($this->temp->exists('whoops'));
	}

	public function testDeleteNonExistent() {
		$this->assertTrue($this->driver->delete('not_here'));
		$this->assertFalse($this->temp->exists('not_here'));
	}

	public function testFlush() {
		$this->driver->set('foo', 'bar');
		$this->driver->set('bar', 'baz');
		$this->assertFalse($this->temp->isEmpty());
		$this->driver->flush();
		$this->assertTrue($this->temp->isEmpty());
		$this->assertTrue($this->temp->exists());
	}

}
