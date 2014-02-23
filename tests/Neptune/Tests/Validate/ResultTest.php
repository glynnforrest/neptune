<?php

namespace Neptune\Tests\Validate;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Validate\Result;

/**
 * ResultTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $obj = new \stdClass();
        $input = array(
            'key' => 'value',
            'array' => array(),
            'object' => $obj
        );
        $r = new Result($input);
        $this->assertEquals('value', $r->get('key'));
        $this->assertEquals('value', $r->key);
        $this->assertEquals(array(), $r->get('array'));
        $this->assertEquals(array(), $r->array);
        $this->assertEquals($obj, $r->get('object'));
        $this->assertEquals($obj, $r->object);
    }

    public function testAddAndGetErrors()
    {
        $r = new Result();
        $required = 'Foo is required.';
        $alpha = 'Foo is not alphabetical.';
        $r->addError('foo', $required);
        $this->assertSame(array($required), $r->getErrors('foo'));
        $r->addError('foo', $alpha);
        $this->assertSame(array($required, $alpha), $r->getErrors('foo'));
        $this->assertSame(array($required, $alpha), $r->getErrors());
    }

    public function testGetAllErrors()
    {
        $r = new Result();
        $foo_required = 'Foo is required.';
        $foo_alpha = 'Foo is not alphabetical.';
        $bar_required = 'Bar is required.';
        $bar_alpha = 'Bar is not alphabetical.';

        $r->addError('foo', $foo_required);
        $r->addError('bar', $bar_required);
        $this->assertSame(array($foo_required, $bar_required), $r->getErrors());

        $r->addError('foo', $foo_alpha);
        $r->addError('bar', $bar_alpha);
        $expected = array($foo_required, $foo_alpha, $bar_required, $bar_alpha);
        $this->assertSame($expected, $r->getErrors());
    }

    public function testGetFirstError()
    {
        $r = new Result();
        $required = 'Foo is required.';
        $alpha = 'Foo is not alphabetical.';
        $r->addError('foo', $required);
        $this->assertSame($required, $r->getFirstError('foo'));
        $r->addError('foo', $alpha);
        $this->assertSame($required, $r->getFirstError('foo'));
        $this->assertSame($required, $r->getFirstError());
    }

    public function testGetFirstErrors()
    {
        $r = new Result();
        $foo_required = 'Foo is required.';
        $foo_alpha = 'Foo is not alphabetical.';
        $bar_required = 'Bar is required.';
        $bar_alpha = 'Bar is not alphabetical.';

        $expected = array(
            'foo' => $foo_required,
            'bar' => $bar_required
        );

        $r->addError('foo', $foo_required);
        $r->addError('bar', $bar_required);
        $this->assertSame($expected, $r->getFirstErrors());

        $r->addError('foo', $foo_alpha);
        $r->addError('bar', $bar_alpha);
        $this->assertSame($expected, $r->getFirstErrors());
    }

}
