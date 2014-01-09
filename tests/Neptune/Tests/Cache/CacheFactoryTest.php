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
			'prefix' => 'testing_'
		));

		$this->config->set('cache.driver2', array(
			'driver' => 'debug',
			'prefix' => 'testing_'
		));
		$this->factory = new CacheFactory($this->config);
	}

	public function tearDown() {
	}

	public function testGetDefaultDriver() {
		$this->assertInstanceOf('\Neptune\Cache\Drivers\FileDriver', $this->factory->getDriver());
	}

	public function testGetFileDriver() {
		$this->assertInstanceOf('\Neptune\Cache\Drivers\FileDriver', $this->factory->getDriver('driver1'));
	}

	public function testGetDebugDriver() {
		$this->assertInstanceOf('\Neptune\Cache\Drivers\DebugDriver', $this->factory->getDriver('driver2'));
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

	public function testGetBadConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$this->config->set('cache.wrong', array(
			'prefix' => 'test_'
			//no driver
		));
		$this->factory->getDriver('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		$this->config->set('cache.unknown', array('driver' => 'unicorn', 'prefix' => 'testing_'));
		$this->factory->getDriver('unknown');
	}

}
