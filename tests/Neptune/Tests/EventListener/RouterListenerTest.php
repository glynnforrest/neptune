<?php

namespace Neptune\Tests\EventListener;

use Neptune\EventListener\RouterListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

/**
 * RouterListenerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RouterListenerTest extends \PHPUnit_Framework_TestCase
{

    protected $router;
    protected $neptune;
    protected $listener;

    public function setUp()
    {
        $this->router = $this->getMockBuilder('Neptune\Routing\Router')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->listener = new RouterListener($this->router, $this->neptune);
    }

    public function testOnKernelRequest()
    {
        $request = new Request();
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                      ->disableOriginalConstructor()
                      ->getMock();

        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));
        $event->expects($this->once())
              ->method('isMasterRequest')
              ->will($this->returnValue(true));

        $this->router->expects($this->once())
                     ->method('routeModules')
                     ->with($this->neptune);
        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue(['test_controller', 'test_method', 'test_args']));

        $this->listener->onKernelRequest($event);
        $this->assertSame('test_controller', $request->attributes->get('_controller'));
        $this->assertSame('test_method', $request->attributes->get('_method'));
        $this->assertSame('test_args', $request->attributes->get('_args'));
    }

    public function testGetSubscribedEvents()
    {
        $expected = array(KernelEvents::REQUEST => array('onKernelRequest'));
        $this->assertSame($expected, RouterListener::getSubscribedEvents());
    }

    public function testRouterNotRunWhenRequestAlreadyHasController()
    {
        $request = new Request();
        $request->attributes->set('_controller', 'test_controller');
        $request->attributes->set('_method', 'test_method');
        $request->attributes->set('_args', 'test_args');

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                      ->disableOriginalConstructor()
                      ->getMock();

        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $this->router->expects($this->never())
                     ->method('matchCached');
        $this->router->expects($this->never())
                     ->method('routeModules');
        $this->router->expects($this->never())
                     ->method('match');

        $this->listener->onKernelRequest($event);

        //check that the listener hasn't changed the request
        $this->assertSame('test_controller', $request->attributes->get('_controller'));
        $this->assertSame('test_method', $request->attributes->get('_method'));
        $this->assertSame('test_args', $request->attributes->get('_args'));
    }

}
