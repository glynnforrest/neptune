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
	}

	public function tearDown() {
		$this->temp->reset();
	}

    protected function stubConfig($env = null)
    {
        $this->temp->create("config/neptune.yml", 'foo: bar');

        if ($env) {
            $this->temp->create("config/env/$env.yml", 'foo: override');
        }
    }

    public function testSetAndGetEnv()
    {
        $this->assertNull($this->neptune->getEnv());
        $this->neptune->setEnv('dev');
        $this->assertSame('dev', $this->neptune->getEnv());
    }

    public function testLoadConfig()
    {
        $this->stubConfig();
        $this->assertSame('bar', $this->neptune['config']->get('foo'));
    }

    public function testLoadConfigFromModule()
    {
        $this->stubConfig();
        $module = $this->getMock('Neptune\Service\AbstractModule');
        $module->expects($this->any())
               ->method('getName')
               ->will($this->returnValue('test-module'));
        $this->neptune->addModule($module);

        $module->expects($this->once())
               ->method('loadConfig');
        $this->neptune['config'];
    }

    public function testLoadConfigWithEnv()
    {
        $this->stubConfig('production');
        $this->neptune->setEnv('production');
        $this->assertSame('override', $this->neptune['config']->get('foo'));
    }

    public function testSetEnvAfterConfigLoadThrowsException()
    {
        $this->stubConfig();
        $this->assertSame('bar', $this->neptune['config']->get('foo'));
        $msg = 'Environment is locked because configuration is already loaded.';
        $this->setExpectedException('\Exception', $msg);
        $this->neptune->setEnv('development');
    }

    public function testSetEnvAfterConfigLoadWithEnvThrowsException()
    {
        $this->stubConfig('production');
        $this->neptune->setEnv('production');
        $this->assertSame('override', $this->neptune['config']->get('foo'));
        $msg = 'Environment is locked to "production" because configuration is already loaded.';
        $this->setExpectedException('\Exception', $msg);
        $this->neptune->setEnv('development');
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
