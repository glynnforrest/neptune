<?php

namespace Neptune\Tests\Config\Processor;

use Neptune\Config\Processor\OptionsProcessor;
use Neptune\Config\Config;

/**
 * OptionsProcessorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class OptionsProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->processor = new OptionsProcessor();
    }

    public function testProcessLoadWithOverwrite()
    {
        $config = new Config();
        $config->set('my-module.array_key', ['bar']);
        $config->set('_options', ['foo' => 'overwrite']);

        $module_config = [
            '_options' => [
                'my-module.array_key' => 'overwrite',
            ],
            'my-module' => [
                'array_key' => ['foo', 'bar'],
            ],
        ];

        $this->processor->processLoad($config, $module_config);

        $this->assertSame(['foo', 'bar'], $config->get('my-module.array_key'));

        $expected_options = [
            'foo' => 'overwrite',
            'my-module.array_key' => 'overwrite',
        ];
        $this->assertSame($expected_options, $config->get('_options'));
    }

    public function testProcessLoadWithOverwriteAndPrefix()
    {
        $config = new Config();
        $config->set('my-module.array_key', ['bar']);
        $config->set('_options', ['foo' => 'overwrite']);

        $module_config = [
            '_options' => [
                'my-module.array_key' => 'overwrite',
            ],
            'array_key' => ['foo', 'bar'],
        ];

        $this->processor->processLoad($config, ['my-module' => $module_config], 'my-module');

        $this->assertSame(['foo', 'bar'], $config->get('my-module.array_key'));

        $expected_options = [
            'foo' => 'overwrite',
            'my-module.array_key' => 'overwrite',
        ];
        $this->assertSame($expected_options, $config->get('_options'));
    }
}
