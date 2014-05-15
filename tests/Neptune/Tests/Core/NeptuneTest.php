<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Config\NeptuneConfig;
use Neptune\Tests\Routing\TestModule;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * NeptuneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class NeptuneTest extends \PHPUnit_Framework_TestCase {

	protected $neptune;
	protected $temp;
    protected $config;

	public function setUp() {
		$this->temp = new Temping();
		$this->neptune = new Neptune($this->temp->getDirectory());
        //override config for testing
        $this->config = new Config('neptune');
        $this->neptune['config'] = $this->config;
        //this is an ugly hack to make sure the router works. When the
        //router is decoupled into a service this won't be needed
        $this->neptune['config']->set('root_url', 'myapp.local/');
	}

	public function tearDown() {
		$this->temp->reset();
	}

    protected function stubEnvConfig()
    {
        $config = new Config('production');
        $config->set('foo', 'override');
        $this->temp->create('config/env/production.php', $config->toString());
    }

	public function testLoadAndGetEnv() {
        $this->stubEnvConfig();
		$this->config->set('foo', 'default');
		$this->assertSame('default', $this->config->get('foo'));
		$this->neptune->loadEnv('production');
		$this->assertEquals('override', $this->config->get('foo'));
		$this->assertSame('production', $this->neptune->getEnv());
	}

	public function testLoadAndGetDefaultEnv() {
        $this->stubEnvConfig();
		$this->config->set('env', 'production');
		$this->neptune->loadEnv();
		$this->assertSame('production', $this->neptune->getEnv());
	}

	public function testLoadEnvNoArgThrowsException() {
		$c = new Config('neptune');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$this->neptune->loadEnv();
	}

	public function testGetEnvReturnsNullWithNoEnv() {
		$this->assertNull($this->neptune->getEnv());
	}

	public function testGetRootDirectory() {
		$this->assertSame($this->temp->getDirectory(), $this->neptune->getRootDirectory());
	}

	public function testGetRootDirectoryAppendsTrailingSlash() {
		$neptune = new Neptune('/no/trailing/slash');
		$this->assertSame('/no/trailing/slash/', $neptune->getRootDirectory());
	}

    public function testGetModuleDirectory() {
        $module = new TestModule();
        $this->neptune->addModule($module);
        $path = $module->getDirectory();
        $this->assertSame($path, $this->neptune->getModuleDirectory('test-module'));
    }

    public function testGetDefaultModule() {
        $first = new TestModule();
        $second = $this->getMock('\Neptune\Service\AbstractModule');
        $this->neptune->addModule($first);
        $this->neptune->addModule($second);
        $this->assertSame('test-module', $this->neptune->getDefaultModule());
    }

    public function testGetModuleNamespace()
    {
        $module = new TestModule();
        $this->neptune->addModule($module);
        $this->assertSame('Neptune\Tests\Routing', $this->neptune->getModuleNamespace('test-module'));
    }

    public function testAddService()
    {
        $service = $this->getMock('\Neptune\Service\ServiceInterface');
        $service->expects($this->once())
                ->method('register')
                ->with($this->neptune);
        $service->expects($this->never())
                ->method('boot');
        $this->neptune->addService($service);
    }

    public function testBootServices()
    {
        $service = $this->getMock('\Neptune\Service\ServiceInterface');
        $service->expects($this->once())
                ->method('boot')
                ->with($this->neptune);
        $this->neptune->addService($service);
        $this->neptune->boot();
        //check it is only called once
        $this->neptune->boot();
    }

    public function testAddModule()
    {
        $module = $this->getMock('\Neptune\Service\AbstractModule');
        $module->expects($this->once())
                ->method('register')
                ->with($this->neptune);
        $module->expects($this->never())
                ->method('boot');
        $this->neptune->addModule($module);
    }

    public function testGetModule()
    {
        $module = $this->getMock('\Neptune\Service\AbstractModule');
        $module->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('test'));

        $this->neptune->addModule($module);
        $this->assertSame($module, $this->neptune->getModule('test'));
    }

    public function testGetUndefinedModule()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Module "foo" not registered');
        $this->neptune->getModule('foo');
    }

    public function testGetModules()
    {
        $this->assertSame(array(), $this->neptune->getModules());

        $foo = $this->getMock('\Neptune\Service\AbstractModule');
        $foo->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('foo'));
        $this->neptune->addModule($foo);

        $bar = $this->getMock('\Neptune\Service\AbstractModule');
        $bar->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('bar'));
        $this->neptune->addModule($bar);

        $expected = array('foo' => $foo, 'bar' => $bar);
        $this->assertSame($expected, $this->neptune->getModules());
    }

    public function testGetRoutePrefix()
    {
        $this->assertFalse($this->neptune->getRoutePrefix('test-module'));
        $module = new TestModule();
        $this->neptune->addModule($module, 'admin/');
        $this->assertSame('admin/', $this->neptune->getRoutePrefix('test-module'));
    }

    public function testGetRoutePrefixNoRouting()
    {
        $this->assertFalse($this->neptune->getRoutePrefix('foo'));
        $module = $this->getMock('\Neptune\Service\AbstractModule');
        //no routing prefix set, so the module won't be routed.
        $this->neptune->addModule($module);
        $this->assertFalse($this->neptune->getRoutePrefix('foo'));
    }

    public function testConfigSetup()
    {
        $stub = new Config('testing');
        $this->temp->create('config/neptune.php', $stub->toString());

        $neptune = new Neptune($this->temp->getDirectory());
        $config = $neptune['config'];
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame($this->temp->getDirectory(), $config->getRootDirectory());
    }

}
