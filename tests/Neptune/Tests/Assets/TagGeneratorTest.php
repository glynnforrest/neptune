<?php

namespace Neptune\Tests\Assets;

use Neptune\Assets\TagGenerator;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * TagGeneratorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TagGeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->generator = new TagGenerator('http://example.com/assets/');
    }

    public function testCss()
    {
        $expected = '<link rel="stylesheet" type="text/css" href="http://example.com/assets/css/style.css" />' . PHP_EOL;
        $this->assertSame($expected, $this->generator->css('css/style.css'));
    }

    public function testCssWithAttributes()
    {
        $expected = '<link rel="stylesheet" type="text/css" href="http://example.com/assets/css/style.css" id="css-id" />' . PHP_EOL;
        $this->assertSame($expected, $this->generator->css('css/style.css', ['id' => 'css-id']));
    }

    public function testCssWithOverrideAttributes()
    {
        $expected = '<link rel="foo" type="bar" href="baz" />' . PHP_EOL;
        $this->assertSame($expected, $this->generator->css('css/style.css', [
            'rel' => 'foo',
            'type' => 'bar',
            'href' => 'baz'
        ]));
    }

    public function testExternalCssUrl()
    {
        $expected = '<link rel="stylesheet" type="text/css" href="http://example.org/foo.css" id="css-id" />' . PHP_EOL;
        $this->assertSame($expected, $this->generator->css('http://example.org/foo.css', ['id' => 'css-id']));
    }

    public function testJs()
    {
        $expected = '<script type="text/javascript" src="http://example.com/assets/js/main.js"></script>' . PHP_EOL;
        $this->assertSame($expected, $this->generator->js('js/main.js'));
    }

    public function testJsWithAttributes()
    {
        $expected = '<script type="text/javascript" src="http://example.com/assets/js/main.js" id="js-id"></script>' . PHP_EOL;
        $this->assertSame($expected, $this->generator->js('js/main.js', ['id' => 'js-id']));
    }

    public function testJsWithOverrideAttributes()
    {
        $expected = '<script type="bar" src="baz"></script>' . PHP_EOL;
        $this->assertSame($expected, $this->generator->js('js/main.js', [
            'type' => 'bar',
            'src' => 'baz'
        ]));
    }

    public function testExternalJsUrl()
    {
        $expected = '<script type="text/javascript" src="http://example.com/foo.js" id="js-id"></script>' . PHP_EOL;
        $this->assertSame($expected, $this->generator->js('http://example.com/foo.js', ['id' => 'js-id']));
    }

    public function testSetAssetsUrl()
    {
        $this->generator->setAssetsUrl('http://example.com/foo/');

        $css = '<link rel="stylesheet" type="text/css" href="http://example.com/foo/css/style.css" />' . PHP_EOL;
        $this->assertSame($css, $this->generator->css('css/style.css'));

        $js = '<script type="text/javascript" src="http://example.com/foo/js/main.js"></script>' . PHP_EOL;
        $this->assertSame($js, $this->generator->js('js/main.js'));
    }

    public function testCacheBusting()
    {
        $this->generator->setCacheBusting();

        $css_regex = '`<link rel="stylesheet" type="text/css" href="http://example.com/assets/lib.css\?\w+" />`';
        $this->assertRegExp($css_regex, $this->generator->css('lib.css'));

        $js_regex = '`<script type="text/javascript" src="http://example.com/assets/lib.js\?\w+"></script>`';
        $this->assertRegExp($js_regex, $this->generator->js('lib.js'));
    }

    public function testCacheBustingNotAppliedToExternalUrls()
    {
        $this->generator->setCacheBusting();

        $css = '<link rel="stylesheet" type="text/css" href="http://example.org/lib.css" />' . PHP_EOL;
        $this->assertSame($css, $this->generator->css('http://example.org/lib.css'));

        $js = '<script type="text/javascript" src="http://example.org/main.js"></script>' . PHP_EOL;
        $this->assertSame($js, $this->generator->js('http://example.org/main.js'));
    }
}
