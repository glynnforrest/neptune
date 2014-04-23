<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Neptune;
use Neptune\Core\Config;
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
		$this->config = Config::create('neptune');
		$this->neptune = new Neptune($this->config);
		$this->temp = new Temping();
	}

	public function tearDown() {
		$this->temp->reset();
		Config::unload();
	}

	public function testLoadAndGetEnv() {
		$config_file = '<?php';
		$config_file .= <<<END
		return array(
			'foo' => 'override',
		);
END;
		$this->temp->create('config/env/production.php', $config_file);
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $this->temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$this->neptune->loadEnv('production');
		$this->assertEquals('override', $c->get('foo'));
		$this->assertSame('production', $this->neptune->getEnv());
	}

	public function testLoadAndGetEnvDefaultNoArg() {
		$config_file = '<?php';
		$config_file .= <<<END
		return array(
			'foo' => 'override',
		);
END;
		$this->temp->create('config/env/development.php', $config_file);
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $this->temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$c->set('env', 'development');
		$this->neptune->loadEnv();
		$this->assertEquals('override', $c->get('foo'));
		$this->assertSame('development', $this->neptune->getEnv());
	}

	public function testLoadEnvNoArgThrowsException() {
		$c = Config::create('neptune');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$this->neptune->loadEnv();
	}

	public function testGetEnvReturnsNullWithNoEnv() {
		$this->assertNull($this->neptune->getEnv());
	}

	public function testGetRootDirectory() {
		$this->config->set('dir.root', '/root/');
		$this->assertSame('/root/', $this->neptune->getRootDirectory());
	}

	public function testGetRootDirectoryAppendsTrailingSlash() {
		$this->config->set('dir.root', '/no/trailing/slash');
		$this->assertSame('/no/trailing/slash/', $this->neptune->getRootDirectory());
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

}
