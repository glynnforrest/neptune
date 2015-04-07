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

    public function displayFunctionParametersProvider()
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
     * @dataProvider displayFunctionParametersProvider
     */
    public function testDisplayFunctionParameters($expected, $function)
    {
        $this->assertSame($expected, $this->helper->displayFunctionParameters($function));
    }
}
