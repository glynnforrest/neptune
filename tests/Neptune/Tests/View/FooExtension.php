<?php

namespace Neptune\Tests\View;

use Neptune\View\Extension\ExtensionInterface;

/**
 * FooExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FooExtension implements ExtensionInterface
{

    public function getHelpers()
    {
        return array(
            'foo' => 'fooMethod'
        );
    }

    public function fooMethod($string)
    {
        return 'Foo: ' . $string;
    }

}
