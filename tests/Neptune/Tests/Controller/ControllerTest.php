<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;

    public function setUp()
    {
        $this->obj = new FooController();
    }

    public function testForm()
    {
    }

    public function testRedirect()
    {
        $response = $this->obj->redirect('/foo');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('/foo', $response->getTargetUrl());
    }

}
