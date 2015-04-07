<?php

namespace Neptune\Tests\Helper;

use Neptune\Helper\ReflectionHelper;

/**
 * ReflectionHelperTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReflectionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->helper = new ReflectionHelper();
    }

    public function formatArgumentsProvider()
    {
        return [
            ['()', [$this, 'setUp']],
            ['($str)', 'strlen'],
            ['($needle, $haystack, $strict)', 'in_array'],
            ['($foo, $bar)', function ($foo, $bar) { return $foo + $bar; }],
            ['(Neptune\Helper\ReflectionHelper $helper = null)', [$this, 'someMethod']],
        ];
    }

    protected function someMethod(ReflectionHelper $helper = null)
    {
    }

    /**
     * @dataProvider formatArgumentsProvider
     */
    public function testFormatArguments($expected, $function)
    {
        $this->assertSame($expected, $this->helper->formatArguments($function));
    }
}
