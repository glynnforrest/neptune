<?php

namespace Neptune\Tests\Core;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Config\ConfigManager;
use Neptune\Core\Neptune;
use Neptune\Config\NeptuneConfig;
use Neptune\Config\Config;

use Temping\Temping;

/**
 * ConfigManagerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{

    protected $manager;
    protected $neptune;

    protected $file;
    protected $file2;

    protected $temp;

    public function setUp()
    {
        $this->temp = new Temping();
        $this->temp->init();
        $config = new NeptuneConfig($this->temp->getDirectory(), null);
        $this->neptune = new Neptune($config);
        $this->manager = new ConfigManager($this->neptune);
        $this->file =  __DIR__ . '/fixtures/config.php';
        $this->file2 = __DIR__ . '/fixtures/config2.php';
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    /**
     * Strip out whitespace and new lines from config settings to make
     * them easier to compare against.
     */
    protected function removeWhitespace($content)
    {
        return preg_replace('`\s+`', '', $content);
    }

    public function testCreate()
    {
        $config1 = $this->manager->create('testing');
        $this->assertInstanceOf('Neptune\Config\Config', $config1);
        $config2 = $this->manager->create('testing');
        $this->assertNotSame($config1, $config2);
    }

    public function testLoad()
    {
        $config = $this->manager->load('testing', $this->file);
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame('bar', $config->get('foo'));
    }

    public function testLoadThrowsExceptionWithNoFile()
    {
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException');
        $this->manager->load('testing');
    }

    public function testLoadThrowExceptionWithNoName()
    {
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException');
        $this->manager->load();
    }

    public function testLoadAfterCreation()
    {
        $created = $this->manager->create('testing');
        $this->assertInstanceOf('Neptune\Config\Config', $created);
        $loaded = $this->manager->load('testing');
        $this->assertInstanceOf('Neptune\Config\Config', $loaded);
        $this->assertSame($created, $loaded);
    }

    public function testLoadOverwritesWithDifferentFilename()
    {
        $config = $this->manager->load('testing', $this->file);
        $same_file = $this->manager->load('testing', $this->file);
        $this->assertSame($config, $same_file);
        $different_file = $this->manager->load('testing', $this->file2);
        $this->assertNotSame($config, $different_file);
    }

    public function testSaveAll()
    {
        $file = $this->temp->getPathname('config1.php');
        $file2 = $this->temp->getPathname('config2.php');

        $first = $this->manager->create('first', $file);
        $first->set('foo', 'bar');

        $second = $this->manager->create('second', $file2);
        $second->set('foo', 'bar');

        $this->manager->saveAll();
        $this->assertSame($this->removeWhitespace($first->toString()),
                            $this->removeWhitespace($this->temp->getContents('config1.php')));
        $this->assertSame($this->removeWhitespace($second->toString()),
                            $this->removeWhitespace($this->temp->getContents('config2.php')));
    }

    public function testLoadWithOverride()
    {
        $default = $this->manager->load('default', $this->file);
        $this->assertSame('bar', $default->get('foo'));
        $this->manager->load('override', $this->file2, 'default');
        $this->assertSame('bar-override', $default->get('foo'));
    }

    public function testLoadModule()
    {
        $module = $this->getMock('Neptune\Service\AbstractModule');
        $module->expects($this->once())
               ->method('getDirectory')
               ->with()
               ->will($this->returnValue(__DIR__ . '/fixtures/'));
        $module->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('test_module'));
        $this->neptune->addModule($module);
        $config = $this->manager->loadModule('test_module');
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame('bar', $config->get('foo'));
    }

    public function testLoadModuleThrowsExceptionForUnknownModule()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->manager->loadModule('unknown');
    }

    public function testLoadModuleThrowsExceptionForConfigFileNotFound()
    {
        $module = $this->getMock('Neptune\Service\AbstractModule');
        $module->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('test_module'));
        $this->neptune->addModule($module);
        $this->setExpectedException('\Neptune\Exceptions\ConfigFileException');
        $this->manager->loadModule('test_module');
    }

    protected function createModuleConfigs()
    {
        //neptune will look in for config/modules/<modulename>.php and
        //override any values in the module config. Set these files up here.
        $config = new Config('module');
        $config->set('foo', 'bar');
        $this->temp->create('test_module/config.php', $config->toString());

        $override = new Config('override');
        $override->set('foo', 'bar-override');
        $this->temp->create('config/modules/test_module.php', $override->toString());

        $module = $this->getMock('Neptune\Service\AbstractModule');
        $module->expects($this->once())
               ->method('getDirectory')
               ->with()
               ->will($this->returnValue($this->temp->getPathname('test_module/')));
        $module->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('test_module'));
        $this->neptune->addModule($module);
    }

    public function testLoadModuleLoadsLocalConfig()
    {
        $this->createModuleConfigs();
        //test_module/config.php should be overridden by
        //config/modules/test_module.php
        $module_config = $this->manager->loadModule('test_module');
        $this->assertEquals('bar-override', $module_config->get('foo'));
    }

    public function testLoadCallsLoadModule()
    {
        $this->createModuleConfigs();
        //test_module/config.php should be overridden by
        //config/modules/test_module.php
        $module_config = $this->manager->load('test_module');
        $this->assertEquals('bar-override', $module_config->get('foo'));
    }

    public function testLoadingNeptuneAsAModuleDoesNotBreakEverything()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->manager->loadModule('neptune');
    }

    public function testGetNames()
    {
        $configs = array('foo', 'bar', 'baz');
        foreach ($configs as $name) {
            $this->manager->create($name);
        }
        $this->assertSame($configs, $this->manager->getNames());
    }

    public function testUnloadNamed()
    {
        $this->manager->create('foo');
        $this->manager->create('bar');
        $this->manager->unload('foo');
        $this->assertSame(array('bar'), $this->manager->getNames());
    }

    public function testUnloadAll()
    {
        $this->manager->create('foo');
        $this->manager->create('bar');
        $this->manager->unload();
        $this->assertSame(array(), $this->manager->getNames());
    }

}
