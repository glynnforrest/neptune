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

	protected $neptune;
	protected $config;
	protected $factory;

	public function setUp() {
		$this->config = Config::create('neptune');

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
        $this->neptune = $this->getMockBuilder('\Neptune\Core\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
		$this->factory = new CacheFactory($this->config, $this->neptune);
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGetDefaultDriver() {
		$driver = $this->factory->get();
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		$this->assertSame($driver, $this->factory->get());
	}

	public function testGetFileDriver() {
		$driver = $this->factory->get('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		$this->assertSame($driver, $this->factory->get('driver1'));

		$this->assertSame(__DIR__ . '/test_cache/', $driver->getDirectory());
		//check that no directory has been made
		$this->assertFileNotExists(__DIR__ . '/test_cache');
	}

	public function testGetFileDriverRelativeDir() {
		//a dir without a leading slash should be appended to dir.root
		$this->config->set('dir.root', '/path/to/app/root/');
		$this->config->set('cache.driver1.dir', 'cache/');
		$driver = $this->factory->get('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		$this->assertSame('/path/to/app/root/cache/', $driver->getDirectory());
	}

	public function testGetFileDriverRelativeDirThrowsExceptionOnNoRoot() {
		$this->config->set('cache.driver1.dir', 'cache/');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$driver = $this->factory->get();
	}

	public function testGetFileDriverNoDir() {
		$this->config->set('cache.driver1.dir', null);
		$driver = $this->factory->get('driver1');
		$this->assertInstanceOf('\Neptune\Cache\Driver\FileDriver', $driver);
		//when no dir is defined, FileDriver will use the system
		//temporary directory that is provided by Temping, which will
		//contain Temping::TEMPING_DIR_NAME
		$this->assertContains(Temping::TEMPING_DIR_NAME, $driver->getDirectory());
	}

	public function testGetDebugDriver() {
		$driver = $this->factory->get('driver2');
		$this->assertInstanceOf('\Neptune\Cache\Driver\DebugDriver', $driver);
		$this->assertSame($driver, $this->factory->get('driver2'));
	}

	public function testGetMemcachedDriver() {
		if(!class_exists('\\Memcached')) {
			$this->markTestSkipped('Memcached extension not installed.');
		}
		$driver = $this->factory->get('driver3');
		$this->assertInstanceOf('\Neptune\Cache\Driver\MemcachedDriver', $driver);
		$this->assertSame($driver, $this->factory->get('driver3'));
	}

	public function testGetNoConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$this->factory->get('wrong');
	}

	public function testGetDefaultNoConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$factory = new CacheFactory(Config::create('empty'), $this->neptune);
		$factory->get();
	}

	public function testGetNoDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$this->config->set('cache.wrong', array(
			'prefix' => 'testing:'
			//no driver
		));
		$this->factory->get('wrong');
	}

	public function testGetNoPrefix() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$this->config->set('cache.wrong', array(
			'driver' => 'file'
			//no prefix
		));
		$this->factory->get('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		$this->config->set('cache.unknown', array('driver' => 'unicorn', 'prefix' => 'testing_'));
		$this->factory->get('unknown');
	}

    public function testGetDriverAsAService()
    {
        $driver = $this->getMock('\\Neptune\\Cache\\Driver\\CacheDriverInterface');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('service.foo')
                      ->will($this->returnValue($driver));
        $this->config->set('cache.foo', 'service.foo');
        $this->assertSame($driver, $this->factory->get('foo'));
    }

    public function testGetDriverAsAServiceThrowsException()
    {
        $driver = new \stdClass();
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('service.foo')
                      ->will($this->returnValue($driver));
        $this->config->set('cache.foo', 'service.foo');
        $msg = "Cache driver 'foo' requested service 'service.foo' which does not implement Neptune\Cache\Driver\CacheDriverInterface";
        $this->setExpectedException('\Neptune\Exceptions\DriverNotFoundException');
        $this->factory->get('foo');
    }

}
