<?php

namespace Neptune\Tests\Assets;

use Neptune\Core\Config;
use Neptune\Assets\Assets;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetsTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsTest extends \PHPUnit_Framework_TestCase {

	protected $assets;

	public function setUp() {
		Config::create('testing');
		Config::set('root_url', 'myapp.local');
		Config::set('assets.url', 'assets/');
		$this->assets = Assets::getInstance();
		$this->assets->clear();
	}

	public function tearDown() {
		Config::unload();
	}

	public function testCss() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/style.css" />' . PHP_EOL, Assets::css());
	}

	public function testRemoveCss() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assets->removeCss('style');
		$this->assertEquals('', Assets::css());
		$this->assets->removeCss('not_there');
		$this->assertEquals('', Assets::css());
	}

	public function testCssOptions() {
		$this->assets->addCss('style', 'css/style.css', null, array('id' => 'my_style', 'class' => 'style'));
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/style.css" id="my_style" class="style" />' . PHP_EOL, Assets::css());
	}

	public function testCssMultiple() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assets->addCss('main', 'css/main.css');
		$expected = '<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/style.css" />' . PHP_EOL . '<link rel="stylesheet" type="text/css" href="http://myapp.local/assets/css/main.css" />' . PHP_EOL ;
		$this->assertEquals($expected, Assets::css());
	}

	public function testJs() {
		$this->assets->addJs('main', 'js/main.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testRemoveJs() {
		$this->assets->addJs('main', 'js/main.js');
		$this->assets->removeJs('main');
		$this->assertEquals('', Assets::js());
		$this->assets->removeJs('not_there');
		$this->assertEquals('', Assets::js());
	}

	public function testJsOptions() {
		$this->assets->addJs('main', 'js/main.js', null ,array('id' => 'my_script'));
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/main.js" id="my_script"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testMultipleJs() {
		$this->assets->addJs('main', 'js/main.js');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/main.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/other.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDepends() {
		$this->assets->addJs('page', 'js/page.js', 'lib');
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDependsDepFirst() {
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$this->assets->addJs('page', 'js/page.js', 'lib');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testMultipleJsDepends() {
		$this->assets->addJs('page', 'js/page.js', array('lib', 'other'));
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testNestedJsDepends() {
		$this->assets->addJs('page', 'js/page.js', array('lib', 'other'));
		$this->assets->addJs('lib', 'http://site.com/js/lib.js', 'other');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL .'<script type="text/javascript" src="http://myapp.local/assets/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDependsOnSelf() {
		$this->assets->addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/recurse.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDepDependsOnSelf() {
		$this->assets->addJs('main', 'js/main.js', 'recursive');
		$this->assets->addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/assets/js/recurse.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/assets/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testExternalAssetUrl() {
		Config::set('assets.url', 'http://cdn.site.com/assets/');
		$this->assets->addCss('lib', 'lib.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://cdn.site.com/assets/lib.css" />' . PHP_EOL, Assets::css());
	}

}
