<?php

namespace Neptune\Tests\Assets;

use Neptune\Config\Config;
use Neptune\Assets\AssetManager;
use Temping\Temping;

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
        $this->config = new Config();
        $this->assets = new AssetManager($this->config, $this->generator);
    }

    public function testHashGroup()
    {
        $this->assertSame(md5('my-module:testcss') . '.css', $this->assets->hashGroup('my-module:test', 'css'));
        $this->assertSame(md5('my-module:testjs') . '.js', $this->assets->hashGroup('my-module:test', 'js'));
    }

    public function testCss()
    {
        $this->generator->expects($this->once())
                        ->method('css')
                        ->with('css/style.css')
                        ->will($this->returnValue('asset'));
        $this->assertSame('asset', $this->assets->css('css/style.css'));
    }

    public function testCssGroup()
    {
        $this->config->set('test-module.assets.css.login', ['main.css', 'styles.css', 'layout.css', 'form.css']);
        $this->generator->expects($this->exactly(4))
                        ->method('css')
                        ->with($this->logicalOr(
                            $this->equalTo('main.css'),
                            $this->equalTo('styles.css'),
                            $this->equalTo('layout.css'),
                            $this->equalTo('form.css')
                        ))
                        ->will($this->returnValue('asset'));
        $this->assertSame('assetassetassetasset', $this->assets->cssGroup('test-module:login'));
    }

    public function testCssGroupWithInheritance()
    {
        $this->config->set('test-module.assets.css.main', ['main.css', 'styles.css', 'layout.css']);
        $this->config->set('test-module.assets.css.theme', ['theme.css', '@test-module:main']);
        $this->config->set('test-module.assets.css.super-theme', ['@test-module:theme', 'super-theme.css']);
        $this->generator->expects($this->exactly(5))
                        ->method('css')
                        ->withConsecutive(
                            ['theme.css'],
                            ['main.css'],
                            ['styles.css'],
                            ['layout.css'],
                            ['super-theme.css']
                        )
                        ->will($this->returnValue('asset'));
        $this->assertSame('assetassetassetassetasset', $this->assets->cssGroup('test-module:super-theme'));
    }

    public function testCssGroupWithConcat()
    {
        $assets = new AssetManager($this->config, $this->generator, true);

        $this->generator->expects($this->once())
            ->method('css')
            ->with($this->assets->hashGroup('test-module:login', 'css'))
            ->will($this->returnValue('asset'));
        $this->assertSame('asset', $assets->cssGroup('test-module:login'));
    }

    public function testInlineCss()
    {
        $css = "body { color: #eee; }";
        $this->generator->expects($this->once())
                        ->method('inlineCss')
                        ->with($css)
                        ->will($this->returnValue('inline '));
        $this->assertSame('inline ', $this->assets->inlineCss($css));
    }

    public function testJs()
    {
        $this->generator->expects($this->once())
                        ->method('js')
                        ->with('js/main.js')
                        ->will($this->returnValue('asset'));
        $this->assertSame('asset', $this->assets->js('js/main.js'));
    }

    public function testJsGroup()
    {
        $this->config->set('test-module.assets.js.login', ['js/validation.js', 'js/forms.js']);
        $this->generator->expects($this->exactly(2))
                        ->method('js')
                        ->with($this->logicalOr(
                            $this->equalTo('js/validation.js'),
                            $this->equalTo('js/forms.js')
                        ))
                        ->will($this->returnValue('asset'));
        $this->assertSame('assetasset', $this->assets->jsGroup('test-module:login'));
    }

    public function testJsGroupWithInheritance()
    {
        $this->config->set('test-module.assets.js.main', ['library.js', 'main.js']);
        $this->config->set('test-module.assets.js.app', ['@test-module:main', 'app.js']);
        $this->config->set('test-module.assets.js.super-app', ['extra-library.js', '@test-module:app', 'super-app.js']);
        $this->generator->expects($this->exactly(5))
                        ->method('js')
                        ->withConsecutive(
                            ['extra-library.js'],
                            ['library.js'],
                            ['main.js'],
                            ['app.js'],
                            ['super-app.js']
                        )
                        ->will($this->returnValue('asset'));
        $this->assertSame('assetassetassetassetasset', $this->assets->jsGroup('test-module:super-app'));
    }

    public function testJsGroupWithConcat()
    {
        $assets = new AssetManager($this->config, $this->generator, true);
        $this->generator->expects($this->once())
            ->method('js')
            ->with($this->assets->hashGroup('test-module:login', 'js'))
            ->will($this->returnValue('asset'));
        $this->assertSame('asset', $assets->jsGroup('test-module:login'));
    }

    public function testInlineJs()
    {
        $js = "console.log('foo');";
        $this->generator->expects($this->once())
                        ->method('inlineJs')
                        ->with($js)
                        ->will($this->returnValue('inline '));
        $this->assertSame('inline ', $this->assets->inlineJs($js));
    }

    public function testConcatenateAssets()
    {
        $temping = new Temping();
        $base = 'path/to/assets/';

        $this->config->set('my-module.assets.css.main', ['foo.css', 'bar/bar.css']);
        $hashed_css_file = $temping->getPathname($base . $this->assets->hashGroup('my-module:main', 'css'));
        $this->assertFileNotExists($hashed_css_file);

        $this->config->set('my-module.assets.js.main', ['foo.js', 'bar/bar.js']);
        $hashed_js_file = $temping->getPathname($base . $this->assets->hashGroup('my-module:main', 'js'));
        $this->assertFileNotExists($hashed_js_file);

        $temping = new Temping();
        $asset_files = ['foo.css', 'bar/bar.css', 'foo.js', 'bar/bar.js'];
        foreach ($asset_files as $file) {
            $temping->create($base . $file, $file);
        }

        $this->assets->concatenateAssets('my-module', $temping->getPathname('path/to/assets/'));

        $this->assertFileExists($hashed_css_file);
        $css_content = 'foo.css' . PHP_EOL . PHP_EOL . 'bar/bar.css' . PHP_EOL . PHP_EOL;
        $this->assertSame($css_content, file_get_contents($hashed_css_file));

        $this->assertFileExists($hashed_js_file);
        $js_content = 'foo.js' . PHP_EOL . PHP_EOL . 'bar/bar.js' . PHP_EOL . PHP_EOL;
        $this->assertSame($js_content, file_get_contents($hashed_js_file));
        $temping->reset();
    }

}
