<?php

namespace Neptune\Tests\EventListener;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Security\SecurityRequestListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

/**
 * SecurityRequestListenerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityRequestListenerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->factory = $this->getMockBuilder('Neptune\Security\SecurityFactory')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->listener = new SecurityRequestListener($this->factory);
    }

    public function testOnKernelRequest()
    {
        $request = new Request();
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                      ->disableOriginalConstructor()
                      ->getMock();

        $event->expects($this->once())
              ->method('isMasterRequest')
              ->will($this->returnValue(true));
        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $this->factory->expects($this->once())
                      ->method('setRequest')
                      ->with($request);

        $this->listener->onKernelRequest($event);
    }

    public function testGetSubscribedEvents()
    {
        $expected = array(KernelEvents::REQUEST => array('onKernelRequest'));
        $this->assertSame($expected, SecurityRequestListener::getSubscribedEvents());
    }

}
