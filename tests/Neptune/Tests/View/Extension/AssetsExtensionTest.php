<?php

namespace Neptune\Tests\View\Extension;

use Neptune\View\Extension\AssetsExtension;
use Neptune\View\ViewCreator;

/**
 * AssetsExtensionTest.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $assets;
    protected $extension;
    protected $view;

    public function setUp()
    {
        $this->assets = $this->getMockBuilder('Neptune\Assets\AssetManager')
                      ->disableOriginalConstructor()
                      ->getMock();
        $this->extension = new AssetsExtension($this->assets);
        $neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                 ->disableOriginalConstructor()
                 ->getMock();
        $this->view = new ViewCreator($neptune);
        $this->view->addExtension($this->extension);
    }

    public function testCss()
    {
        $this->assets->expects($this->once())
                     ->method('css')
                     ->with('main.css')
                     ->will($this->returnValue('<link/>'));

        $this->assertSame('<link/>', $this->view->callHelper('css', ['main.css']));
    }

    public function testInlineCss()
    {
        $this->assets->expects($this->once())
                     ->method('inlineCss')
                     ->with('body {}')
                     ->will($this->returnValue('<style/>'));

        $this->assertSame('<style/>', $this->view->callHelper('inlineCss', ['body {}']));
    }

    public function testCssGroup()
    {
        $this->assets->expects($this->once())
                     ->method('cssGroup')
                     ->with('admin:main')
                     ->will($this->returnValue('<linkgroup/>'));

        $this->assertSame('<linkgroup/>', $this->view->callHelper('cssGroup', ['admin:main']));
    }

    public function testJs()
    {
        $this->assets->expects($this->once())
                     ->method('js')
                     ->with('main.js')
                     ->will($this->returnValue('<script/>'));

        $this->assertSame('<script/>', $this->view->callHelper('js', ['main.js']));
    }

    public function testInlineJs()
    {
        $this->assets->expects($this->once())
                     ->method('inlineJs')
                     ->with('console.log')
                     ->will($this->returnValue('<script></script>'));

        $this->assertSame('<script></script>', $this->view->callHelper('inlineJs', ['console.log']));
    }

    public function testJsGroup()
    {
        $this->assets->expects($this->once())
                     ->method('jsGroup')
                     ->with('admin:main')
                     ->will($this->returnValue('<scriptgroup/>'));

        $this->assertSame('<scriptgroup/>', $this->view->callHelper('jsGroup', ['admin:main']));
    }
}
