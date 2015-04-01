<?php

namespace Neptune\Tests\Twig\Extension;

use Neptune\Twig\Extension\RoutingExtension;

/**
 * RoutingExtensionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RoutingExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $router;
    protected $extension;

    public function setUp()
    {
        $this->router = $this->getMockBuilder('Neptune\Routing\Router')
                      ->disableOriginalConstructor()
                      ->getMock();
        $this->extension = new RoutingExtension($this->router);
    }

    public function testIsExtension()
    {
        $this->assertInstanceOf('Twig_Extension', $this->extension);
    }

    public function testGetName()
    {
        $this->assertSame('routing', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $expected = [
            new \Twig_SimpleFunction('route', [$this->extension, 'route']),
        ];

        $this->assertEquals($expected, $this->extension->getFunctions());
    }

    public function testRoute()
    {
        $this->router->expects($this->once())
                     ->method('url')
                     ->with('foo', ['foo', 'bar'], 'https')
                     ->will($this->returnValue('router_url'));

        $this->assertSame('router_url', $this->extension->route('foo', ['foo', 'bar'], 'https'));
    }
}
