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

    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Neptune\Assets\AssetManager')
                      ->disableOriginalConstructor()
                      ->getMock();
        $this->extension = new AssetsExtension($this->manager);
    }

    public function testIsExtension()
    {
        $this->assertInstanceOf('Twig_Extension', $this->extension);
    }

    public function testGetName()
    {
        $this->assertSame('assets', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $options = ['is_safe' => ['html']];
        $expected = [
            new \Twig_SimpleFunction('js', [$this->extension, 'js'], $options),
            new \Twig_SimpleFunction('css', [$this->extension, 'css'], $options),
        ];

        $this->assertEquals($expected, $this->extension->getFunctions());
    }

    public function testCss()
    {
        $this->manager->expects($this->once())
                     ->method('css')
                     ->will($this->returnValue('<css />'));

        $this->assertSame('<css />', $this->extension->css());
    }

    public function testJs()
    {
        $this->manager->expects($this->once())
                     ->method('js')
                     ->will($this->returnValue('<js />'));

        $this->assertSame('<js />', $this->extension->js());
    }
}
