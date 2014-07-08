<?php

namespace Neptune\Tests\Assets;

use Neptune\Config\Config;
use Neptune\Assets\AssetManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetManagerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;
    protected $config;
    protected $assets;

    public function setUp()
    {
        $this->generator = $this->getMockBuilder('Neptune\Assets\TagGenerator')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->config = $this->getMockBuilder('Neptune\Config\ConfigManager')
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->assets = new AssetManager($this->config, $this->generator);
    }

    public function testCss()
    {
        $this->assets->addCss('css/style.css');
        $this->generator->expects($this->once())
                        ->method('css')
                        ->with('css/style.css')
                        ->will($this->returnValue('foo'));
        $this->assertSame('foo', $this->assets->css());
    }

    public function testMultipleCss()
    {
        $this->assets->addCss('css/style.css');
        $this->assets->addCss('css/other.css');
        $this->generator->expects($this->exactly(2))
                        ->method('css')
                        ->with($this->logicalOr(
                            $this->equalTo('css/style.css'),
                            $this->equalTo('css/other.css')
                        ))
                        ->will($this->returnValue('foo'));
        $this->assertSame('foofoo', $this->assets->css());
    }

    public function testCssGroup()
    {
        $this->assets->addCssGroup('login', [
            'main.css', 'styles.css', 'layout.css', 'form.css'
        ]);
        $this->generator->expects($this->exactly(4))
                        ->method('css')
                        ->with($this->logicalOr(
                            $this->equalTo('main.css'),
                            $this->equalTo('styles.css'),
                            $this->equalTo('layout.css'),
                            $this->equalTo('form.css')
                        ));
        $this->assets->css('login');
    }

    public function testCssGroupWithConcat()
    {
        $assets = new AssetManager($this->config, $this->generator, true);
        $assets->addCssGroup('login', [
            'main.css', 'styles.css', 'layout.css', 'form.css'
        ]);
        $this->generator->expects($this->once())
                        ->method('css')
                        ->with(md5('login') . '.css');
        $assets->css('login');
    }

    public function testJs()
    {
        $this->assets->addJs('js/main.js');
        $this->generator->expects($this->once())
                        ->method('js')
                        ->with('js/main.js')
                        ->will($this->returnValue('foo'));
        $this->assertSame('foo', $this->assets->js());
    }

    public function testMultipleJs()
    {
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/other.js');
        $this->generator->expects($this->exactly(2))
                        ->method('js')
                        ->with($this->logicalOr(
                            $this->equalTo('js/main.js'),
                            $this->equalTo('js/other.js')
                        ))
                        ->will($this->returnValue('foo'));
        $this->assertSame('foofoo', $this->assets->js());
    }

    public function testJsGroup()
    {
        $this->assets->addJsGroup('login', [
            'js/validation.js', 'js/forms.js'
        ]);
        $this->generator->expects($this->exactly(2))
                        ->method('js')
                        ->with($this->logicalOr(
                            $this->equalTo('js/validation.js'),
                            $this->equalTo('js/forms.js')
                        ));
        $this->assets->js('login');
    }

    public function testJsGroupWithConcat()
    {
        $assets = new AssetManager($this->config, $this->generator, true);
        $assets->addJsGroup('login', [
            'js/validation.js', 'js/forms.js'
        ]);
        $this->generator->expects($this->once())
                        ->method('js')
                        ->with(md5('login') . '.js');
        $assets->js('login');
    }

}
