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

    public function postMergeProvider()
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
     * @dataProvider postMergeProvider
     */
    public function testPostMerge($original, $expected)
    {
        $config = new Config($original);
        $this->processor->onPostMerge($config);
        $this->assertSame($expected, $config->get());
    }

    public function testOptionsAreNotModified()
    {
        $config = new Config([
            'foo' => '%bar%',
            'bar' => 'something',
            '_options' => [
                'foo.bar.baz' => 'combine'
            ]
        ]);
        $this->processor->onPostMerge($config);

        // _options must be ignored, otherwise there will be an array like this:
        // '_options' => [
        //     'foo.bar.baz' => 'combine',
        //     'foo' => [
        //         'bar' => [
        //             'baz' => 'combine'
        //         ]
        //     ]
        // ]
        $expected = [
            'foo' => 'something',
            'bar' => 'something',
            '_options' => [
                'foo.bar.baz' => 'combine'
            ]
        ];
        $this->assertSame($expected, $config->get());
    }

    public function testAKeyCanHaveOptionsInTheName()
    {
        $config = new Config([
            'options_service' => 'service.options',
            '_options_processor' => '%options_service%',
        ]);
        $this->processor->onPostMerge($config);
        $expected = [
            'options_service' => 'service.options',
            '_options_processor' => 'service.options',
        ];
        $this->assertSame($expected, $config->get());
    }
}
