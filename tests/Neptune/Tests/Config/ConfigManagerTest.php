<?php

namespace Neptune\Tests\Core;

use Neptune\Config\ConfigManager;
use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Temping\Temping;
use Neptune\Config\Loader\PhpLoader;
use Neptune\Config\Processor\OptionsProcessor;

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
        $this->manager = new ConfigManager();
        $this->manager->addLoader(new PhpLoader());
        $this->file =  __DIR__.'/fixtures/config.php';
        $this->file2 = __DIR__.'/fixtures/config2.php';
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('Neptune\Config\Config', $this->manager->getConfig());
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
        $this->setExpectedException('Neptune\Config\Exception\ConfigFileException');
        $this->manager->load('testing');
    }

    public function testLoadWithOverride()
    {
        $this->manager->load($this->file);
        $config = $this->manager->getConfig();
        $this->assertSame('bar', $config->get('foo'));

        $this->manager->load($this->file2);
        $config = $this->manager->getConfig();
        $this->assertSame('bar-override', $config->get('foo'));
    }

    public function testLoadNonExistentFileThrowsException()
    {
        $not_here = $this->temp->getPathname('not_here');
        $this->setExpectedException('Neptune\Config\Exception\ConfigFileException', sprintf('Configuration file "%s" not found', $not_here));
        $this->manager->load($not_here);
    }

    public function testLoadInvalidFileThrowsException()
    {
        $this->temp->create('invalid.php', 'foo');
        $path = $this->temp->getPathname('invalid.php');
        $this->setExpectedException('Neptune\Config\Exception\ConfigFileException', $path.' does not return a php array');
        $this->manager->load($path);
    }

    public function testLoadFailsWithNoSuitableLoader()
    {
        $this->temp->create('invalid.txt', 'foo');
        $path = $this->temp->getPathname('invalid.txt');
        $this->setExpectedException('Neptune\Config\Exception\ConfigFileException', sprintf('No configuration loader available for "%s"', $path));
        $this->manager->load($path);
    }

    public function testAddProcessor()
    {
        $processor = $this->getMock('Neptune\Config\Processor\ProcessorInterface');
        $this->manager->addProcessor($processor);
    }

    public function testLoadValuesContainingOptionsKey()
    {
        $this->manager->addProcessor(new OptionsProcessor());
        $this->manager->loadValues(['_options' => ['foo' => 'overwrite']]);

        $module_config = [
            '_options' => [
                'my-module.array_key' => 'overwrite',
            ],
            'array_key' => ['foo', 'bar'],
        ];

        $this->manager->loadValues($module_config, 'my-module');
        $config = $this->manager->getConfig();

        //_options should have been merged with the global options
        $expected_options = [
            'foo' => 'overwrite',
            'my-module.array_key' => 'overwrite',
        ];
        $this->assertSame($expected_options, $config->get('_options'));

        $this->assertSame(['foo', 'bar'], $config->get('my-module.array_key'));

        //now overwrite the key with another config
        $this->manager->loadValues(['my-module' => ['array_key' => ['bar']]]);
        $config = $this->manager->getConfig();

        $expected = [
            'my-module' => [
                'array_key' => ['bar'],
            ],
            '_options' => [
                'foo' => 'overwrite',
                'my-module.array_key' => 'overwrite',
            ],
        ];
        $this->assertSame($expected, $config->get());
    }

    public function testLoadValuesContainingOptionsKeyAndOverride()
    {
        $this->manager->addProcessor(new OptionsProcessor());
        $this->manager->loadValues(['my-module.array_key' => ['foo', 'bar']]);

        $module_config = [
            '_options' => [
                'my-module.array_key' => 'overwrite',
            ],
            'array_key' => ['bar'],
        ];

        $this->manager->loadValues($module_config, 'my-module');

        $config = $this->manager->getConfig();

        //options should have been loaded in advance before merging
        //the module config, specifying that my-module.array_key
        //should not be merged.
        $this->assertSame(['bar'], $config->get('my-module.array_key'));

        //_options should have been merged with the global options
        $expected_options = [
            'my-module.array_key' => 'overwrite',
        ];
        $this->assertSame($expected_options, $config->get('_options'));
    }

    public function testLoadWhenOptionsKeyIsDefinedLate()
    {
        $this->manager->addProcessor(new OptionsProcessor());

        $this->manager->loadValues(['foo' => ['one']]);
        $this->manager->loadValues(['foo' => ['two']]);
        $this->manager->loadValues(['foo' => ['three'], '_options' => ['foo' => 'combine']]);
        $this->manager->loadValues(['foo' => ['four']]);

        $expected = [
            'foo' => ['one', 'two', 'three', 'four'],
            '_options' => ['foo' => 'combine'],
        ];
        $this->assertSame($expected, $this->manager->getConfig()->get());
    }
}
