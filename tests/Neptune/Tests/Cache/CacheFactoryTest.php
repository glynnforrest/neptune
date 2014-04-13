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
    protected $temping;

	public function setUp() {
		$this->config = Config::create('neptune');
        $this->temping = new Temping();

		$this->config->set('cache.array', array(
			'driver' => 'array',
		));

		$this->config->set('cache.file', array(
			'driver' => 'file',
			'namespace' => 'testing_',
			'dir' => $this->temping->getDirectory()
		));

		$this->config->set('cache.memcached', array(
			'driver' => 'memcached',
			'namespace' => 'testing_',
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
        $this->temping->reset();
	}

	public function testGetDefaultDriver() {
		$driver = $this->factory->get();
		$this->assertInstanceOf('\Doctrine\Common\Cache\ArrayCache', $driver);
		$this->assertSame($driver, $this->factory->get());
	}

	public function testGetArrayDriver() {
		$driver = $this->factory->get('array');
		$this->assertInstanceOf('\Doctrine\Common\Cache\ArrayCache', $driver);
		$this->assertSame($driver, $this->factory->get('array'));
	}

	public function testGetFileDriver() {
		$driver = $this->factory->get('file');
		$this->assertInstanceOf('\Doctrine\Common\Cache\FilesystemCache', $driver);
		$this->assertSame($driver, $this->factory->get('file'));

		$this->assertSame($this->temping->getDirectory(), $driver->getDirectory() . '/');
        $this->assertSame('testing_', $driver->getNamespace());
	}

	public function testGetFileDriverRelativeDir() {
		//a dir without a leading slash should be appended to dir.root
		$this->config->set('dir.root', $this->temping->getDirectory() . 'foo/');
		$this->config->set('cache.file.dir', 'cache/');
		$driver = $this->factory->get('file');
		$this->assertInstanceOf('\Doctrine\Common\Cache\FilesystemCache', $driver);
		$this->assertSame($this->temping->getDirectory() . 'foo/cache' , $driver->getDirectory());
        $this->assertSame('testing_', $driver->getNamespace());
	}

	public function testGetFileDriverNoRoot() {
		$this->config->set('cache.file.dir', 'cache/');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$driver = $this->factory->get('file');
	}

	public function testGetFileDriverNoDir() {
		$this->config->set('cache.file.dir', null);
		$driver = $this->factory->get('file');
        $this->assertSame(sys_get_temp_dir(), $driver->getDirectory());
	}

	public function testGetFileDriverNoNamespace() {
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$this->config->set('cache.file.namespace', null);
		$this->factory->get('file');
	}

	public function testGetMemcachedDriver() {
		if(!class_exists('\\Memcached')) {
			$this->markTestSkipped('Memcached extension not installed.');
		}
		$driver = $this->factory->get('memcached');
		$this->assertInstanceOf('\Doctrine\Common\Cache\MemcachedCache', $driver);
		$this->assertSame($driver, $this->factory->get('memcached'));
        $this->assertSame('testing_', $driver->getNamespace());
        $this->assertInstanceOf('\\Memcached', $driver->getMemcached());
        $servers = $driver->getMemcached()->getServerList();
        $this->assertSame('localhost', $servers[0]['host']);
        $this->assertSame(5000, $servers[0]['port']);
	}

    public function testGetMemcachedDriverWithDefaults()
    {
        $this->config->set('cache.memcached', array(
            'driver' => 'memcached',
            'namespace' => 'testing_'
            //no host or port
        ));
        $driver = $this->factory->get('memcached');
        $this->assertInstanceOf('\Doctrine\Common\Cache\MemcachedCache', $driver);
        $this->assertSame($driver, $this->factory->get('memcached'));
        $this->assertSame('testing_', $driver->getNamespace());
        $this->assertInstanceOf('\\Memcached', $driver->getMemcached());
        $servers = $driver->getMemcached()->getServerList();
        $this->assertSame('127.0.0.1', $servers[0]['host']);
        $this->assertSame(11211, $servers[0]['port']);
    }

	public function testGetMemcachedDriverNoNamespace() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$this->config->set('cache.memcached.namespace', null);
		$this->factory->get('memcached');
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
			'namespace' => 'testing:'
			//no driver
		));
		$this->factory->get('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		$this->config->set('cache.unknown', array('driver' => 'unicorn', 'namespace' => 'testing_'));
		$this->factory->get('unknown');
	}

    public function testGetDriverAsAService()
    {
        $driver = $this->getMock('\Doctrine\Common\Cache\Cache');
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
