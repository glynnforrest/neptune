<?php

namespace Neptune\Tests\Config\Processor;

use Neptune\Config\Processor\ReferenceProcessor;
use Neptune\Config\Config;

/**
 * ReferenceProcessorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReferenceProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->processor = new ReferenceProcessor();
    }

    public function processBuildProvider()
    {
        return [
            [
                //original
                [
                    'foo' => 'bar',
                    'bar' => '%foo%',
                ],
                //expected
                [
                    'foo' => 'bar',
                    'bar' => 'bar',
                ],
            ],

            [
                //original
                [
                    'foo' => 'bar',
                    'bar' => '%baz%',
                    'baz' => '%foo%',
                ],
                //expected
                [
                    'foo' => 'bar',
                    'bar' => 'bar',
                    'baz' => 'bar',
                ],
            ],

            [
                //original
                [
                    'foo' => 'foo-%bar.baz%',
                    'bar' => [
                        'baz' => 'baz-%bar.foo%',
                        'foo' => 'value',
                    ],
                ],
                //expected
                [
                    'foo' => 'foo-baz-value',
                    'bar' => [
                        'baz' => 'baz-value',
                        'foo' => 'value',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider processBuildProvider
     */
    public function testProcessBuild($original, $expected)
    {
        $config = new Config($original);
        $this->processor->processBuild($config);
        $this->assertSame($expected, $config->get());
    }
}
