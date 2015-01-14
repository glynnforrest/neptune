<?php

namespace Neptune\Tests\Core;

use Neptune\Config\ConfigManager;
use Neptune\Core\Neptune;
use Neptune\Config\NeptuneConfig;
use Neptune\Config\Config;

use Temping\Temping;
use Neptune\Config\Loader\PhpLoader;

/**
 * ConfigManagerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;
    protected $file;
    protected $file2;

    protected $temp;

    public function setUp()
    {
        $this->temp = new Temping();
        $this->temp->init();
        $this->config = new Config();
        $this->manager = new ConfigManager($this->config);
        $this->manager->addLoader(new PhpLoader());
        $this->file =  __DIR__ . '/fixtures/config.php';
        $this->file2 = __DIR__ . '/fixtures/config2.php';
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    public function testGetConfig()
    {
        $this->assertSame($this->config, $this->manager->getConfig());
    }

    public function testLoad()
    {
        $this->manager->load($this->file);
        $config = $this->manager->getConfig();
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame('bar', $config->get('foo'));
    }

    public function testLoadThrowsExceptionWithNoFile()
    {
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException');
        $this->manager->load('testing');
    }

    public function testLoadWithOverride()
    {
        $this->manager->load($this->file);
        $this->assertSame('bar', $this->config->get('foo'));

        $this->manager->load($this->file2);
        $this->assertSame('bar-override', $this->config->get('foo'));
    }

    public function testLoadNonExistentFileThrowsException()
    {
        $not_here = $this->temp->getPathname('not_here');
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException', sprintf('Configuration file "%s" not found', $not_here));
        $this->manager->load($not_here);
    }

    public function testLoadInvalidFileThrowsException()
    {
        $this->temp->create('invalid.php', 'foo');
        $path = $this->temp->getPathname('invalid.php');
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException', $path . ' does not return a php array');
        $this->manager->load($path);
    }

    public function testLoadFailsWithNoSuitableLoader()
    {
        $this->temp->create('invalid.txt', 'foo');
        $path = $this->temp->getPathname('invalid.txt');
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException', sprintf('No configuration loader available for "%s"', $path));
        $this->manager->load($path);
    }

    public function testLoadValuesContainingOptionsKey()
    {
        $this->config->set('_options', ['foo' => 'no_merge']);

        $module_config = [
            '_options' => [
                'my-module.array_key' => 'no_merge'
            ],
            'array_key' => ['foo', 'bar']
        ];

        $this->manager->loadValues($module_config, 'my-module');
        $this->assertSame(['foo', 'bar'], $this->config->get('my-module.array_key'));

        //_options should have been merged with the global options
        $expected_options = [
            'foo' => 'no_merge',
            'my-module.array_key' => 'no_merge'
        ];
        $this->assertSame($expected_options, $this->config->get('_options'));

        $this->manager->loadValues(['my-module' => ['array_key' => ['bar']]]);
        $this->assertSame(['bar'], $this->config->get('my-module.array_key'));
    }

    public function testLoadValuesContainingOptionsKeyAndOverride()
    {
        $this->config->set('my-module.array_key', ['foo', 'bar']);

        $module_config = [
            '_options' => [
                'my-module.array_key' => 'no_merge'
            ],
            'array_key' => ['bar']
        ];

        $this->manager->loadValues($module_config, 'my-module');

        //options should have been loaded in advance before merging
        //the module config, specifying that my-module.array_key
        //should not be merged.
        $this->assertSame(['bar'], $this->config->get('my-module.array_key'));

        //_options should have been merged with the global options
        $expected_options = [
            'my-module.array_key' => 'no_merge',
        ];
        $this->assertSame($expected_options, $this->config->get('_options'));
    }
}
