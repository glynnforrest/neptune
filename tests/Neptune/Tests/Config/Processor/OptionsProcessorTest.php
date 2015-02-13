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

    public function testOverwrite()
    {
        $default = new DotArray([
            '_options' => [
                'foo' => 'overwrite',
            ],
            'my-module' => [
                'array_key' => ['bar'],
            ]
        ]);
        $module_config = new DotArray([
            '_options' => [
                'my-module.array_key' => 'overwrite',
            ],
            'my-module' => [
                'array_key' => ['foo', 'bar'],
            ]
        ]);

        $this->processor->onLoad($default);
        $this->processor->onLoad($module_config);

        $config = new Config();
        $this->processor->onPreMerge($config, [$default, $module_config]);

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

    public function testOverwriteAndPrefix()
    {
        $default = new DotArray([
            '_options' => [
                'foo' => 'overwrite',
            ],
            'my-module' => [
                'array_key' => ['bar'],
            ]
        ]);
        $module_config = new DotArray([
            'my-module' => [
                '_options' => [
                    'my-module.array_key' => 'overwrite',
                ],
                'array_key' => ['foo', 'bar'],
            ]
        ]);

        $this->processor->onLoad($default);
        $this->processor->onLoad($module_config, 'my-module');

        $config = new Config();
        $this->processor->onPreMerge($config, [$default, $module_config]);

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

    public function testCombine()
    {
        $one = new DotArray([
            '_options' => [
                'foo' => 'combine',
            ],
            'foo' => ['foo', 'bar'],
        ]);
        $two = new DotArray([
            'foo' => ['baz', 'quo']
        ]);

        $this->processor->onLoad($one);
        $this->processor->onLoad($two);

        $config = new Config();
        $this->processor->onPreMerge($config, [$one, $two]);

        $this->assertSame(['foo', 'bar', 'baz', 'quo'], $config->get('foo'));
        $expected = [
            'foo' => ['foo', 'bar', 'baz', 'quo'],
            '_options' => [
                'foo' => 'combine',
            ],
        ];
        $this->assertSame($expected, $config->get());
    }

    public function testCombineEmpty()
    {
        $values = new DotArray([
            '_options' => [
                'foo' => 'combine'
            ]
        ]);
        $this->processor->onLoad($values);

        $config = new Config();
        $this->processor->onPreMerge($config, [$values]);

        $expected = [
            '_options' => [
                'foo' => 'combine',
            ],
        ];
        $this->assertSame($expected, $config->get());
    }
}
