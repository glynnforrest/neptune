<?php

namespace Neptune\Tests\Config\Processor;

use Neptune\Config\Processor\OptionsProcessor;
use Neptune\Config\Config;
use Crutches\DotArray;

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

        $module_config = new DotArray([
            '_options' => [
                'my-module.array_key' => 'overwrite',
            ],
            'my-module' => [
                'array_key' => ['foo', 'bar'],
            ],
        ]);

        $this->processor->processLoad($config, $module_config);

        $this->assertSame(['foo', 'bar'], $config->get('my-module.array_key'));

        $expected = [
            'my-module' => [
                'array_key' => ['foo', 'bar'],
            ],
            '_options' => [
                'foo' => 'overwrite',
                'my-module.array_key' => 'overwrite',
            ],
        ];
        $this->assertSame($expected, $config->get());
    }

    public function testProcessLoadWithOverwriteAndPrefix()
    {
        $config = new Config();
        $config->set('my-module.array_key', ['bar']);
        $config->set('_options', ['foo' => 'overwrite']);

        $module_config = new DotArray([
            'my-module' => [
                '_options' => [
                    'my-module.array_key' => 'overwrite',
                ],
                'array_key' => ['foo', 'bar'],
            ]
        ]);

        $this->processor->processLoad($config, $module_config, 'my-module');

        $this->assertSame(['foo', 'bar'], $config->get('my-module.array_key'));
        $expected = [
            'my-module' => [
                'array_key' => ['foo', 'bar'],
            ],
            '_options' => [
                'foo' => 'overwrite',
                'my-module.array_key' => 'overwrite',
            ],
        ];
        $this->assertSame($expected, $config->get());
    }

    /**
     * @group combine
     */
    public function testProcessLoadWithCombine()
    {
        $config = new Config();
        $config->set('_options.foo', 'combine');
        $config->set('foo', ['foo', 'bar']);

        $this->processor->processLoad($config, new DotArray(['foo' => ['baz', 'quo']]));

        $this->assertSame(['foo', 'bar', 'baz', 'quo'], $config->get('foo'));
        $expected = [
            '_options' => [
                'foo' => 'combine',
            ],
            'foo' => ['foo', 'bar', 'baz', 'quo'],
        ];
        $this->assertSame($expected, $config->get());
    }
}
