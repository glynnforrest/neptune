<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Neptune;
use Neptune\Core\Config;

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
		$this->temp->create('app/env/production.php', file_get_contents(__DIR__ . '/etc/sample_env.php'));
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $this->temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$this->assertFalse(defined('SOME_CONSTANT'));
		//loadEnv should call Config::loadEnv and include app/env/<env>.php
		$this->neptune->loadEnv('production');
		$this->assertTrue(defined('SOME_CONSTANT'));
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
		$this->temp->create('app/env/development.php', file_get_contents(__DIR__ . '/etc/sample_env.php'));
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
        $module = $this->getMock('\Neptune\Service\AbstractModule');
        $module->expects($this->once())
         ->method('getDirectory')
         ->will($this->returnValue($path = '/path/to/MyApp'));
        $this->neptune->addModule('my-app', $module);
        $this->assertSame($path, $this->neptune->getModuleDirectory('my-app'));
    }

	public function testGetDefaultModule() {
		$modules = array(
			'my-app' => 'app/MyApp/',
			'other-module' => 'app/OtherModel/');
		$this->config->set('modules', $modules);
		$this->assertSame('my-app', $this->neptune->getDefaultModule());
	}

	public function testGetModuleNamespace() {
		$config = Config::create('my-app');
		$config->set('namespace', 'MyApp');
		$this->assertSame('MyApp', $this->neptune->getModuleNamespace('my-app'));
		$config->set('namespace', 'Changed');
		$this->assertSame('Changed', $this->neptune->getModuleNamespace('my-app'));
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
        $this->neptune->addModule('test', $module);
    }

    public function testGetModule()
    {
        $module = $this->getMock('\Neptune\Service\AbstractModule');
        $this->neptune->addModule('test', $module);
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
        $module = $this->getMock('\Neptune\Service\AbstractModule');
        $this->neptune->addModule('foo', $module);
        $this->neptune->addModule('bar', $module);
        $expected = array('foo' => $module, 'bar' => $module);
        $this->assertSame($expected, $this->neptune->getModules());
    }

}
