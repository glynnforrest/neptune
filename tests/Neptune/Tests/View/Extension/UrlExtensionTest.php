<?php

namespace Neptune\Tests\View\Extension;

use Neptune\View\Extension\UrlExtension;

/**
 * UrlExtensionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UrlExtensionTest extends \PHPUnit_Framework_TestCase
{

    protected $router;
    protected $url;
    protected $extension;

    public function setUp()
    {
        $this->url = $this->getMockBuilder('Neptune\Routing\Url')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->router = $this->getMockBuilder('Neptune\Routing\Router')
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->extension = new UrlExtension($this->router, $this->url);
    }

    public function testGetHelpers()
    {
        $expected = [
            'to' => 'to',
            'toRoute' => 'toRoute'
        ];
        $this->assertSame($expected, $this->extension->getHelpers());
    }

    public function testTo()
    {
        $this->url->expects($this->once())
                  ->method('to')
                  ->with('foo')
                  ->will($this->returnValue('http://foo'));

        $this->assertSame('http://foo', $this->extension->to('foo'));
    }

    public function testToRoute()
    {
        $this->router->expects($this->once())
                     ->method('url')
                     ->with('foo', ['foo', 'bar'], 'https')
                     ->will($this->returnValue('router_url'));

        $this->assertSame('router_url', $this->extension->toRoute('foo', ['foo', 'bar'], 'https'));
    }

}
