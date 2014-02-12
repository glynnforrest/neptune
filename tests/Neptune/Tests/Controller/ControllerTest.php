<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;

    public function setUp()
    {
        $request = new Request();
        $this->obj = new FooController();
        $this->obj->setRequest($request);
    }

    public function testCreateForm()
    {
        $this->assertInstanceOf('\Neptune\Form\Form', $this->obj->createForm());
    }

    public function testRedirect()
    {
        $response = $this->obj->redirect('/foo');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('/foo', $response->getTargetUrl());
    }

}
