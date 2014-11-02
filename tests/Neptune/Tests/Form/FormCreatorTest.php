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

    public function testCreateDefaultForm()
    {
        $form = $this->creator->create();
        $this->assertInstanceOf('\Reform\Form\Form', $form);
    }

    public function testCreateDefaultFormWithAction()
    {
        $form = $this->creator->create(null, '/login');
        $this->assertInstanceOf('\Reform\Form\Form', $form);
        $this->assertSame('/login', $form->getAction());
    }

    public function testCreateForm()
    {
        $module = $this->getMockBuilder('Neptune\Service\AbstractModule')
            ->disableOriginalConstructor()
            ->getMock();
        $module->expects($this->once())
              ->method('getNamespace')
              ->will($this->returnValue('Neptune\Tests'));

        $this->neptune->expects($this->once())
            ->method('getModule')
            ->with('my-module')
            ->will($this->returnValue($module));

        $form = $this->creator->create('my-module:foo');
        $this->assertInstanceOf( '\Neptune\Tests\Form\FooForm', $form);
    }

    public function testCreateWithService()
    {
        $form = new FooForm('/foo');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('form.foo')
                      ->will($this->returnValue($form));
        $this->assertSame($form, $this->creator->create('form.foo'));
    }

    public function testBadServiceThrowsException()
    {
        $msg = 'Service "form.foo" is not an instance of Reform\Form\Form';
        $this->neptune->expects($this->once())
            ->method('offsetGet')
            ->with('form.foo')
            ->will($this->returnValue(new \stdClass()));
        $this->setExpectedException('\RuntimeException', $msg);
        $this->creator->create('form.foo');
    }

}
