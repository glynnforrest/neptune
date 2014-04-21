<?php

namespace Neptune\Tests\Form;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Form\FormCreator;

use Symfony\Component\HttpFoundation\Request;

/**
 * FormCreatorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormCreatorTest extends \PHPUnit_Framework_TestCase
{

    protected $neptune;
    protected $creator;
    protected $dispatcher;

    public function setup()
    {
        $this->neptune = $this->getMockBuilder('\Neptune\Core\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->creator = new FormCreator($this->neptune, $this->dispatcher);
    }

    public function testCreateEmptyForm()
    {
        $form = $this->creator->create();
        $this->assertInstanceOf('\Reform\Form\Form', $form);
    }

    public function testCreateEmptyFormWithAction()
    {
        $form = $this->creator->create(null, '/login');
        $this->assertInstanceOf('\Reform\Form\Form', $form);
        $this->assertSame('/login', $form->getAction());
    }

    public function testCreateCustomForm()
    {
        $this->creator->register('foo', '\Neptune\Tests\Form\FooForm');
        $form = $this->creator->create('foo');
        $this->assertInstanceOf( '\Neptune\Tests\Form\FooForm', $form);
    }

    public function testCreateUnregisteredThrowsException()
    {
        $msg = 'Form "foo" is not registered';
        $this->setExpectedException('\RuntimeException', $msg);
        $this->creator->create('foo');
    }

    public function testCreateWithService()
    {
        $this->creator->register('foo', '::form.foo');
        $foo_form = $this->getMockBuilder('\Reform\Form\Form')
                         ->disableOriginalConstructor()
                         ->getMock();
        $foo_form->expects($this->once())
                 ->method('setEventDispatcher')
                 ->with($this->dispatcher);
        $foo_form->expects($this->never())
                 ->method('setAction');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('form.foo')
                      ->will($this->returnValue($foo_form));
        $form = $this->creator->create('foo');
        $this->assertSame($foo_form, $form);
    }

    public function testCreateWithServiceDifferentAction()
    {
        $this->creator->register('foo', '::form.foo');
        $foo_form = $this->getMockBuilder('\Reform\Form\Form')
                         ->disableOriginalConstructor()
                         ->getMock();
        $foo_form->expects($this->once())
                 ->method('setEventDispatcher')
                 ->with($this->dispatcher);
        $foo_form->expects($this->once())
                 ->method('setAction')
                 ->with('/url');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('form.foo')
                      ->will($this->returnValue($foo_form));
        $form = $this->creator->create('foo', '/url');
        $this->assertSame($foo_form, $form);
    }

}