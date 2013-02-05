<?php

namespace neptune\cache\drivers;

use neptune\cache\drivers\DebugDriver;
use neptune\cache\CacheFactory;
use neptune\core\Config;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

/**
 * DebugDriverTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriverTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('cache', array(
			'debug' => array(
				'driver' => 'debug',
				'prefix' => 'DEBUG__',
			),
			'incomplete' => array(
				'driver' => 'debug'
			),
			'fake' => array(
				'driver' => 'fake',
			)
			));
	}

	public function tearDown() {
		CacheFactory::getDriver('debug')->flush();
		Config::unload();
	}

	public function testGetDriver() {
		$this->assertTrue(CacheFactory::getDriver('debug') instanceof DebugDriver);
	}

	public function testGetDriverBadConfig() {
		$this->setExpectedException('\\neptune\\exceptions\\ConfigKeyException');
		CacheFactory::getDriver('wrong');
		$this->setExpectedException('\\neptune\\exceptions\\ConfigKeyException');
		CacheFactory::getDriver('incomplete');
	}

	public function testAddGetAndSet() {
		$cache = CacheFactory::getDriver('debug');
		$cache->add('foo', 'bar');
		$this->assertEquals('bar', $cache->get('foo'));
		$cache->set('foo', 'blah');
		$this->assertEquals('blah', $cache->get('foo'));
	}

	public function testGetDriverUndefinedDriver() {
		$this->setExpectedException('\\neptune\\exceptions\\DriverNotFoundException');
		CacheFactory::getDriver('fake');
	}

	public function testDelete() {
		$cache = CacheFactory::getDriver('debug');
		$cache->add('key', 'value');
		$cache->delete('key');
		$this->assertFalse($cache->get('key'));
	}

	public function testPrefixIsAdded() {
		$cache = CacheFactory::getDriver('debug');
		$cache->add('foo', 'baz');
		$this->assertEquals(array('DEBUG__foo' => 'baz'), $cache->dump());
	}

	public function testFlush() {
		$cache = CacheFactory::getDriver('debug');
		$cache->add('foo', 'baz');
		$cache->add('one', 1);
		$cache->flush();
		$this->assertEquals(array(), $cache->dump());
	}

}