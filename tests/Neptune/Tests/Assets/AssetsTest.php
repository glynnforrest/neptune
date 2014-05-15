<?php

namespace Neptune\Tests\Assets;

use Neptune\Config\Config;
use Neptune\Config\ConfigManager;
use Neptune\Assets\Assets;
use Neptune\Helpers\Url;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetsTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsTest extends \PHPUnit_Framework_TestCase {

	protected $assets;
    protected $config;

	public function setUp() {
		$this->config = new Config('testing');
		$this->config->set('assets.url', 'assets/');
        $neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                        ->disableOriginalConstructor()
                        ->getMock();
        $manager = new ConfigManager($neptune);
        $manager->add($this->config);
        $url = new Url('myapp.local/');
		$this->assets = new Assets($manager, $url);
	}

	public function testCss() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/style.css" />' . PHP_EOL, $this->assets->css());
	}

	public function testRemoveCss() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assets->removeCss('style');
		$this->assertEquals('', $this->assets->css());
		$this->assets->removeCss('not_there');
		$this->assertEquals('', $this->assets->css());
	}

	public function testCssOptions() {
		$this->assets->addCss('style', 'css/style.css', null, array('id' => 'my_style', 'class' => 'style'));
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/style.css" id="my_style" class="style" />' . PHP_EOL, $this->assets->css());
	}

	public function testCssMultiple() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assets->addCss('main', 'css/main.css');
		$expected = '<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/style.css" />' . PHP_EOL . '<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/main.css" />' . PHP_EOL ;
		$this->assertEquals($expected, $this->assets->css());
	}

	public function testJs() {
		$this->assets->addJs('main', 'js/main.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testRemoveJs() {
		$this->assets->addJs('main', 'js/main.js');
		$this->assets->removeJs('main');
		$this->assertEquals('', $this->assets->js());
		$this->assets->removeJs('not_there');
		$this->assertEquals('', $this->assets->js());
	}

	public function testJsOptions() {
		$this->assets->addJs('main', 'js/main.js', null ,array('id' => 'my_script'));
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/main.js" id="my_script"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testMultipleJs() {
		$this->assets->addJs('main', 'js/main.js');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/main.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/other.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testJsDepends() {
		$this->assets->addJs('page', 'js/page.js', 'lib');
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testJsDependsDepFirst() {
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$this->assets->addJs('page', 'js/page.js', 'lib');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testMultipleJsDepends() {
		$this->assets->addJs('page', 'js/page.js', array('lib', 'other'));
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testNestedJsDepends() {
		$this->assets->addJs('page', 'js/page.js', array('lib', 'other'));
		$this->assets->addJs('lib', 'http://site.com/js/lib.js', 'other');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL .'<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testJsDependsOnSelf() {
		$this->assets->addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/recurse.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testJsDepDependsOnSelf() {
		$this->assets->addJs('main', 'js/main.js', 'recursive');
		$this->assets->addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/recurse.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testExternalAssetUrl() {
		$this->config->set('assets.url', 'http://cdn.site.com/assets/');
		$this->assets->addCss('lib', 'lib.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://cdn.site.com/assets/lib.css" />' . PHP_EOL, $this->assets->css());
	}

	public function testAssetAbsoluteUrl() {
		$this->assets->addCss('lib', '/css/lib.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/lib.css" />' . PHP_EOL, $this->assets->css());
		$this->assets->addJs('main', '/js/main.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, $this->assets->js());
	}

	public function testCacheBusting() {
		$this->config->set('assets.cache_bust', true);
		$this->assets->addCss('lib', '/css/lib.css');
		$css_regex = '`<link rel="stylesheet" type="text/css" href="http://myapp.local/css/lib.css\?\w+" />`';
		$this->assertRegExp($css_regex, $this->assets->css());
		$this->assets->addJs('main', '/js/main.js');
		$js_regex = '`<script type="text/javascript" src="http://myapp.local/js/main.js\?\w+"></script>`';
		$this->assertRegExp($js_regex, $this->assets->js());
	}

	public function testCacheBustingNotAppliedToExternalUrls() {
		$this->config->set('assets.cache_bust', true);
		$this->assets->addCss('lib', 'http://example.org/lib.css');
		$css_expected = '<link rel="stylesheet" type="text/css" href="http://example.org/lib.css" />' . PHP_EOL;
		$this->assertEquals($css_expected, $this->assets->css());
		$this->assets->addJs('main', 'http://example.org/main.js');
		$js_expected = '<script type="text/javascript" src="http://example.org/main.js"></script>' . PHP_EOL;
		$this->assertEquals($js_expected, $this->assets->js());
	}

}
