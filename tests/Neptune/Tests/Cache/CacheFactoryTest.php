<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\CacheFactory;
use Neptune\Core\Config;

use Temping\Temping;

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
		Config::unload();
	}

	public function testGetDefaultDriver() {
		$driver = $this->factory->getDriver();
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver());
	}

	public function testGetFileDriver() {
		$driver = $this->factory->getDriver('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver('driver1'));

		$this->assertSame(__DIR__ . '/test_cache/', $driver->getDirectory());
		//check that no directory has been made
		$this->assertFileNotExists(__DIR__ . '/test_cache');
	}

	public function testGetFileDriverRelativeDir() {
		//a dir without a leading slash should be appended to dir.root
		$this->config->set('dir.root', '/path/to/app/root/');
		$this->config->set('cache.driver1.dir', 'cache/');
		$driver = $this->factory->getDriver('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		$this->assertSame('/path/to/app/root/cache/', $driver->getDirectory());
	}

	public function testGetFileDriverRelativeDirThrowsExceptionOnNoRoot() {
		$this->config->set('cache.driver1.dir', 'cache/');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$driver = $this->factory->getDriver();
	}

	public function testGetFileDriverNoDir() {
		$this->config->set('cache.driver1.dir', null);
		$driver = $this->factory->getDriver('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		//when no dir is defined, FileDriver will use the system
		//temporary directory that is provided by Temping, which will
		//contain Temping::TEMPING_DIR_NAME
		$this->assertContains(Temping::TEMPING_DIR_NAME, $driver->getDirectory());
	}

	public function testGetDebugDriver() {
		$driver = $this->factory->getDriver('driver2');
		$this->assertInstanceOf('\Neptune\Cache\Driver\DebugDriver', $driver);
		$this->assertSame($driver, $this->factory->getDriver('driver2'));
	}

	public function testGetMemcachedDriver() {
		if(!class_exists('\\Memcached')) {
			$this->markTestSkipped('Memcached extension not installed.');
		}
		$driver = $this->factory->getDriver('driver3');
		$this->assertInstanceOf('\Neptune\Cache\Driver\MemcachedDriver', $driver);
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
