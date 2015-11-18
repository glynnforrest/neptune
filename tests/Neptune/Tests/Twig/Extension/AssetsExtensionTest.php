<?php

namespace Neptune\Tests\Twig\Extension;

use Neptune\Twig\Extension\AssetsExtension;

/**
 * AssetsExtensionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;
    protected $extension;

    protected static $templates = [
        'css' => "<head>{{ css('main.css') }}</head>",
        'inlineCss' => "<head>{{ inlineCss('body {}') }}</head>",
        'cssGroup' => "<head>{{ cssGroup('admin:main') }}</head>",
        'js' => "<body>{{ js('main.js') }}</body>",
        'inlineJs' => "<head>{{ inlineJs('console.log') }}</head>",
        'jsGroup' => "<head>{{ jsGroup('admin:main') }}</head>",
    ];

    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Neptune\Assets\AssetManager')
                      ->disableOriginalConstructor()
                      ->getMock();
        $this->extension = new AssetsExtension($this->manager);

        $this->twig = new \Twig_Environment(new \Twig_Loader_Array(static::$templates));
        $this->twig->addExtension($this->extension);
    }

    public function testIsExtension()
    {
        $this->assertInstanceOf('Twig_Extension', $this->extension);
    }

    public function testGetName()
    {
        $this->assertSame('assets', $this->extension->getName());
    }

    public function testCss()
    {
        $this->manager->expects($this->once())
                     ->method('css')
                     ->with('main.css')
                     ->will($this->returnValue('<link/>'));

        $this->assertSame('<head><link/></head>', $this->twig->loadTemplate('css')->render([]));
    }

    public function testInlineCss()
    {
        $this->manager->expects($this->once())
                     ->method('inlineCss')
                     ->with('body {}')
                     ->will($this->returnValue('<style/>'));

        $this->assertSame('<head><style/></head>', $this->twig->loadTemplate('inlineCss')->render([]));
    }

    public function testCssGroup()
    {
        $this->manager->expects($this->once())
                     ->method('cssGroup')
                     ->with('admin:main')
                     ->will($this->returnValue('<linkgroup/>'));

        $this->assertSame('<head><linkgroup/></head>', $this->twig->loadTemplate('cssGroup')->render([]));
    }

    public function testJs()
    {
        $this->manager->expects($this->once())
                     ->method('js')
                     ->with('main.js')
                     ->will($this->returnValue('<script/>'));

        $this->assertSame('<body><script/></body>', $this->twig->loadTemplate('js')->render([]));
    }

    public function testInlineJs()
    {
        $this->manager->expects($this->once())
                     ->method('inlineJs')
                     ->with('console.log')
                     ->will($this->returnValue('<script></script>'));

        $this->assertSame('<head><script></script></head>', $this->twig->loadTemplate('inlineJs')->render([]));
    }

    public function testJsGroup()
    {
        $this->manager->expects($this->once())
                     ->method('jsGroup')
                     ->with('admin:main')
                     ->will($this->returnValue('<scriptgroup/>'));

        $this->assertSame('<head><scriptgroup/></head>', $this->twig->loadTemplate('jsGroup')->render([]));
    }
}
