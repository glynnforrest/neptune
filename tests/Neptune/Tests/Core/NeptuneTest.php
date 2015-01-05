<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Config\NeptuneConfig;
use Neptune\Tests\Routing\TestModule;

use Temping\Temping;

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
        $this->config = new Config();
        $this->neptune['config'] = $this->config;
	}

	public function tearDown() {
		$this->temp->reset();
	}

    protected function stubEnvConfig()
    {
        $yaml = 'foo: override';
        $this->temp->create('config/env/production.yml', $yaml);
    }

    public function testLoadAndGetEnv()
    {
        $neptune = new Neptune($this->temp->getDirectory());
        //stub config/neptune.yml for testing
        $this->temp->create('config/neptune.yml');
        $config = $neptune['config'];
        $config->set('foo', 'default');
        $this->assertSame('default', $config->get('foo'));

        $this->stubEnvConfig();
        $neptune->loadEnv('production');
        $this->assertSame('override', $config->get('foo'));
        $this->assertSame('production', $neptune->getEnv());
    }

	public function testLoadAndGetDefaultEnv() {
        $this->stubEnvConfig();
		$this->config->set('env', 'production');
		$this->neptune->loadEnv();
		$this->assertSame('production', $this->neptune->getEnv());
	}

    public function testLoadEnvNotInConfig()
    {
        $this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
        $this->neptune->loadEnv();
    }

    public function testLoadEnvNotFound()
    {
        $msg = sprintf('Unable to load environment "development": %s not found', $this->temp->getPathname('config/env/development.yml'));
        $this->setExpectedException('\Neptune\Exceptions\ConfigFileException', $msg);
        $this->neptune->loadEnv('development');
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

    public function testGetDefaultModuleNoneRegistered()
    {
        $this->setExpectedException('\Exception');
        $this->neptune->getDefaultModule();
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

    public function testConfigSetup()
    {
        $yaml = 'foo: bar';
        $this->temp->create('config/neptune.yml', $yaml);

        $neptune = new Neptune($this->temp->getDirectory());
        $config = $neptune['config'];
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame($this->temp->getDirectory(), $config->getRootDirectory());
        $this->assertSame('bar', $config->get('foo'));
    }

}
