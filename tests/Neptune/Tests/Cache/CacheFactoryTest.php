<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\CacheFactory;
use Neptune\Cache\Drivers\DebugDriver;
use Neptune\Cache\Drivers\MemcachedDriver;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * CacheFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CacheFactoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		if(!class_exists('\\Memcached')) {
			$this->markTestSkipped('Memcached extension not installed.');
		}
		$c = Config::create('unittest');
		$c->set('cache', array(
			'memcached' => array (
				'host' => 'localhost',
				'driver' => 'memcached',
				'port' => '11211',
				'prefix' => 'unittest-',
			),
			'debug' => array (
				'driver' => 'debug',
				'prefix' => 'unittest-',
			),
			'undefined' => array (
				'driver' => 'undefined',
				'prefix' => 'unittest-',
			),
		));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGetDriver() {
		$this->assertTrue(CacheFactory::getDriver() instanceof MemcachedDriver);
		$this->assertTrue(CacheFactory::getDriver('memcached') instanceof MemcachedDriver);
		$this->assertTrue(CacheFactory::getDriver('debug') instanceof DebugDriver);
		$this->assertTrue(CacheFactory::getDriver() === CacheFactory::getDriver('memcached'));
	}

	public function testGetDriverPrefix() {
		$c = Config::create('prefix');
		$c->set('cache', array(
			'debug' => array (
				'driver' => 'debug',
				'prefix' => 'unittest-',
			),
			'memcached' => array (
				'host' => 'localhost',
				'driver' => 'memcached',
				'port' => '11211',
				'prefix' => 'unittest-',
			)));
		$this->assertTrue(CacheFactory::getDriver('prefix#debug') instanceof DebugDriver);
		$this->assertTrue(CacheFactory::getDriver('prefix#memcached') instanceof MemcachedDriver);
		$this->assertTrue(CacheFactory::getDriver('prefix#') instanceof DebugDriver);
		$this->assertTrue(CacheFactory::getDriver('prefix#') === CacheFactory::getDriver('prefix#debug'));
	}

	public function testGetBadConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$c = Config::load('unittest');
		$c->set('security', array());
		CacheFactory::getDriver('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		CacheFactory::getDriver('undefined');
	}


}
