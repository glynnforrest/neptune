<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\CacheFactory;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * CacheFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CacheFactoryTest extends \PHPUnit_Framework_TestCase {

	protected $config;
	protected $factory;

	public function setUp() {
		$this->config = Config::create('testing');

		$this->config->set('cache.driver1', array(
			'driver' => 'file',
			'prefix' => 'testing_',
			'dir' => __DIR__ . '/test_cache'
		));

		$this->config->set('cache.driver2', array(
			'driver' => 'debug',
			'prefix' => 'testing_'
		));

		$this->config->set('cache.driver3', array(
			'driver' => 'memcached',
			'prefix' => 'testing_',
			'host' => 'localhost',
			'port' => '5000'
		));
		$this->factory = new CacheFactory($this->config);
	}

	public function tearDown() {
	}

	public function testGetDefaultDriver() {
		$driver = $this->factory->getDriver();
		$this->assertInstanceOf('\Neptune\Cache\Drivers\FileDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver());
	}

	public function testGetFileDriver() {
		$driver = $this->factory->getDriver('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Drivers\FileDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver('driver1'));

		$this->assertSame(__DIR__ . '/test_cache/', $driver->getDirectory());
		//check that no directory has been made
		$this->assertFileNotExists(__DIR__ . '/test_cache');
	}

	public function testGetDebugDriver() {
		$driver = $this->factory->getDriver('driver2');
		$this->assertInstanceOf('\Neptune\Cache\Drivers\DebugDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver('driver2'));
	}

	public function testGetMemcachedDriver() {
		if(!class_exists('\\Memcached')) {
			$this->markTestSkipped('Memcached extension not installed.');
		}
		$driver = $this->factory->getDriver('driver3');
		$this->assertInstanceOf('\Neptune\Cache\Drivers\MemcachedDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver('driver3'));
	}

	public function testGetNoConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$this->factory->getDriver('wrong');
	}

	public function testGetDefaultNoConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$factory = new CacheFactory(Config::create('empty'));
		$factory->getDriver();
	}

	public function testGetNoDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException',
		"Cache configuration 'wrong' does not list a driver");
		$this->config->set('cache.wrong', array(
			'prefix' => 'testing:'
			//no driver
		));
		$this->factory->getDriver('wrong');
	}

	public function testGetNoPrefix() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException',
		"Cache configuration 'wrong' does not list a prefix");
		$this->config->set('cache.wrong', array(
			'driver' => 'file'
			//no prefix
		));
		$this->factory->getDriver('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		$this->config->set('cache.unknown', array('driver' => 'unicorn', 'prefix' => 'testing_'));
		$this->factory->getDriver('unknown');
	}

}
