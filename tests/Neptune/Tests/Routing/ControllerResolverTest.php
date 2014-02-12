<?php

namespace Neptune\Tests\Routing;

use Neptune\Routing\ControllerResolver;
use Neptune\Tests\Routing\Controller\TestController;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ControllerResolverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{

    protected $obj;

    public function setUp()
    {
        $this->neptune = $this->getMockBuilder('\\Neptune\\Core\\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->obj = new ControllerResolver($this->neptune);
    }

    public function tearDown()
    {
    }

    public function createRequest($controller, $method, $args = array())
    {
        $req = new Request();
        $req->attributes->set('_controller', $controller);
        $req->attributes->set('_method', $method);

        return $req;
    }

    public function testGetControllerWithModule()
    {
        $this->neptune->expects($this->once())
                      ->method('getModuleNamespace')
                      ->with('module')
                      ->will($this->returnValue('\\Neptune\\Tests\Routing'));
        $req = $this->createRequest('module:test', 'bar');
        $controller = $this->obj->getController($req);

        $this->assertInternalType('array', $controller);
        $this->assertTrue(count($controller) === 2);
        $this->assertInstanceOf('\Neptune\Tests\Routing\Controller\TestController', $controller[0]);
        $this->assertSame('barAction', $controller[1]);
        $this->assertSame($req, $controller[0]->getRequest());
        $this->assertSame($this->neptune, $controller[0]->getNeptune());
    }

    public function testGetControllerWithNoModule()
    {
        $this->neptune->expects($this->once())
                      ->method('getDefaultModule')
                      ->with()
                      ->will($this->returnValue('test-module'));
        $this->neptune->expects($this->once())
                      ->method('getModuleNamespace')
                      ->with('test-module')
                      ->will($this->returnValue('\\Neptune\\Tests\Routing'));

        $req = $this->createRequest('test', 'bar');
        $controller = $this->obj->getController($req);

        $this->assertInternalType('array', $controller);
        $this->assertTrue(count($controller) === 2);
        $this->assertInstanceOf('\Neptune\Tests\Routing\Controller\TestController', $controller[0]);
        $this->assertSame('barAction', $controller[1]);
        $this->assertSame($req, $controller[0]->getRequest());
        $this->assertSame($this->neptune, $controller[0]->getNeptune());
    }

    public function testGetControllerFromService()
    {
        $this->neptune->expects($this->once())
                      ->method('offsetExists')
                      ->with('controllers.foo')
                      ->will($this->returnValue(true));
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('controllers.foo')
                      ->will($this->returnValue(new TestController()));
        $req = $this->createRequest('::controllers.foo', 'bar');
        $controller = $this->obj->getController($req);

        $this->assertInternalType('array', $controller);
        $this->assertTrue(count($controller) === 2);
        $this->assertInstanceOf('\Neptune\Tests\Routing\Controller\TestController', $controller[0]);
        $this->assertSame('barAction', $controller[1]);
        $this->assertSame($req, $controller[0]->getRequest());
        $this->assertSame($this->neptune, $controller[0]->getNeptune());
    }

}
