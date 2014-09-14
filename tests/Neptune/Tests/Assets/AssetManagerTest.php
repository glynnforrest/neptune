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

    protected function expectConfigFetch($type, $group_name, array $return_assets)
    {
        $config = new Config('testing');
        $config->set("assets.$type.$group_name", $return_assets);
        $this->config->expects($this->once())
                     ->method('load')
                     ->with('test')
                     ->will($this->returnValue($config));
    }

    public function testCssGroup()
    {
        $this->expectConfigFetch('css', 'login', ['main.css', 'styles.css', 'layout.css', 'form.css']);
        $this->assets->addCssGroup('test:login');

        $this->generator->expects($this->exactly(4))
                        ->method('css')
                        ->with($this->logicalOr(
                            $this->equalTo('main.css'),
                            $this->equalTo('styles.css'),
                            $this->equalTo('layout.css'),
                            $this->equalTo('form.css')
                        ));
        $this->assets->css();
    }

    public function testCssGroupWithInheritance()
    {
        $config = new Config('testing');
        $config->set('assets.css.main', ['main.css', 'styles.css', 'layout.css']);
        $config->set('assets.css.theme', ['theme.css', '@test:main']);
        $config->set('assets.css.super-theme', ['@test:theme', 'super-theme.css']);
        $this->config->expects($this->exactly(3))
                     ->method('load')
                     ->with('test')
                     ->will($this->returnValue($config));

        $this->assets->addCssGroup('test:super-theme');

        $this->generator->expects($this->exactly(5))
                        ->method('css')
                        ->withConsecutive(
                            ['theme.css'],
                            ['main.css'],
                            ['styles.css'],
                            ['layout.css'],
                            ['super-theme.css']
                        );
        $this->assets->css();
    }

    public function testCssGroupWithConcat()
    {
        $assets = new AssetManager($this->config, $this->generator, true);

        $assets->addCssGroup('test:login');

        $this->generator->expects($this->once())
                        ->method('css')
                        ->with(md5('test:login') . '.css');
        $assets->css();
    }

    public function testInlineCss()
    {
        $css = "body { color: #eee; }";
        $this->assets->addInlineCss($css);
        $this->generator->expects($this->once())
                        ->method('inlineCss')
                        ->with($css)
                        ->will($this->returnValue('inline '));
        $this->assertSame('inline ', $this->assets->css());
    }

    public function testInlineCssAndGroup()
    {
        $css = "body { color: #eee; }";
        $this->assets->addInlineCss($css);
        $this->generator->expects($this->once())
                        ->method('inlineCss')
                        ->with($css)
                        ->will($this->returnValue('inline '));

        $this->expectConfigFetch('css', 'login', ['main.css', 'styles.css', 'layout.css', 'form.css']);
        $this->assets->addCssGroup('test:login');

        $this->generator->expects($this->exactly(4))
                        ->method('css')
                        ->with($this->logicalOr(
                            $this->equalTo('main.css'),
                            $this->equalTo('styles.css'),
                            $this->equalTo('layout.css'),
                            $this->equalTo('form.css')
                        ))
                        ->will($this->returnValue('foo '));

        $this->assertSame('inline foo foo foo foo ', $this->assets->css());
    }

    public function testInlineCssAndConcatGroup()
    {
        $this->assets->concatenate();
        $this->assets->addCssGroup('test:login');
        $this->generator->expects($this->once())
                        ->method('css')
                        ->with(md5('test:login') . '.css')
                        ->will($this->returnValue('concat '));

        $css = "body { color: #eee; }";
        $this->assets->addInlineCss($css);
        $this->generator->expects($this->once())
                        ->method('inlineCss')
                        ->with($css)
                        ->will($this->returnValue('inline'));

        $this->assertSame('concat inline', $this->assets->css());
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
        $this->expectConfigFetch('js', 'login', ['js/validation.js', 'js/forms.js']);
        $this->assets->addJsGroup('test:login');
        $this->generator->expects($this->exactly(2))
                        ->method('js')
                        ->with($this->logicalOr(
                            $this->equalTo('js/validation.js'),
                            $this->equalTo('js/forms.js')
                        ));
        $this->assets->js();
    }

    public function testJsGroupWithInheritance()
    {
        $config = new Config('testing');
        $config->set('assets.js.main', ['library.js', 'main.js']);
        $config->set('assets.js.app', ['@test:main', 'app.js']);
        $config->set('assets.js.super-app', ['extra-library.js', '@test:app', 'super-app.js']);
        $this->config->expects($this->exactly(3))
                     ->method('load')
                     ->with('test')
                     ->will($this->returnValue($config));

        $this->assets->addJsGroup('test:super-app');

        $this->generator->expects($this->exactly(5))
                        ->method('js')
                        ->withConsecutive(
                            ['extra-library.js'],
                            ['library.js'],
                            ['main.js'],
                            ['app.js'],
                            ['super-app.js']
                        );
        $this->assets->js();
    }

    public function testJsGroupWithConcat()
    {
        $assets = new AssetManager($this->config, $this->generator, true);
        $assets->addJsGroup('test:login');
        $this->generator->expects($this->once())
                        ->method('js')
                        ->with(md5('test:login') . '.js');
        $assets->js();
    }

    public function testInlineJs()
    {
        $js = "console.log('foo');";
        $this->assets->addInlineJs($js);
        $this->generator->expects($this->once())
                        ->method('inlineJs')
                        ->with($js)
                        ->will($this->returnValue('inline '));
        $this->assertSame('inline ', $this->assets->js());
    }

    public function testInlineJsAndGroup()
    {
        $js = "console.log('foo');";
        $this->assets->addInlineJs($js);
        $this->generator->expects($this->once())
                        ->method('inlineJs')
                        ->with($js)
                        ->will($this->returnValue('inline '));

        $this->expectConfigFetch('js', 'login', ['js/validation.js', 'js/forms.js']);
        $this->assets->addJsGroup('test:login');
        $this->generator->expects($this->exactly(2))
                        ->method('js')
                        ->with($this->logicalOr(
                            $this->equalTo('js/validation.js'),
                            $this->equalTo('js/forms.js')
                        ))
                        ->will($this->returnValue('foo '));

        $this->assertSame('inline foo foo ', $this->assets->js());
    }

    public function testInlineJsAndConcatGroup()
    {
        $this->assets->concatenate();
        $this->assets->addJsGroup('test:login');
        $this->generator->expects($this->once())
                        ->method('js')
                        ->with(md5('test:login') . '.js')
                        ->will($this->returnValue('concat '));

        $js = "console.log('foo');";
        $this->assets->addInlineJs($js);
        $this->generator->expects($this->once())
                        ->method('inlineJs')
                        ->with($js)
                        ->will($this->returnValue('inline'));

        $this->assertSame('concat inline', $this->assets->js());
    }

}
