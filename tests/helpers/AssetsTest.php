<?php

namespace neptune\helpers;

use neptune\core\Config;
use neptune\helpers\Assets;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * AssetsTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('root_url', 'myapp.local');
		Assets::clear();
	}

	public function tearDown() {
		Config::unload();
	}

	public function testCss() {
		Assets::addCss('style', 'css/style.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL, Assets::css());
	}

	public function testCssOptions() {
		Assets::addCss('style', 'css/style.css', null, array('id' => 'my_style', 'class' => 'style'));
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" id="my_style" class="style" />' . PHP_EOL, Assets::css());
	}

	public function testCssMultiple() {
		Assets::addCss('style', 'css/style.css');
		Assets::addCss('main', 'css/main.css');
		$expected = '<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL . '<link rel="stylesheet" type="text/css" href="http://myapp.local/css/main.css" />' . PHP_EOL ;
		$this->assertEquals($expected, Assets::css());
	}


	public function testJs() {
		Assets::addJs('main', 'js/main.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsOptions() {
		Assets::addJs('main', 'js/main.js', null ,array('id' => 'my_script'));
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js" id="my_script"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testMultipleJs() {
		Assets::addJs('main', 'js/main.js');
		Assets::addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/other.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDepends() {
		Assets::addJs('page', 'js/page.js', 'lib');
		Assets::addJs('lib', 'http://site.com/js/lib.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testMultipleJsDepends() {
		Assets::addJs('page', 'js/page.js', array('lib', 'other'));
		Assets::addJs('lib', 'http://site.com/js/lib.js');
		Assets::addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testNestedJsDepends() {
		Assets::addJs('page', 'js/page.js', array('lib', 'other'));
		Assets::addJs('lib', 'http://site.com/js/lib.js', 'other');
		Assets::addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL .'<script type="text/javascript" src="http://myapp.local/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDependsOnSelf() {
		Assets::addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/recurse.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}

	public function testJsDepDependsOnSelf() {
		Assets::addJs('main', 'js/main.js', 'recursive');
		Assets::addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/recurse.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, Assets::js());
	}
	
}
?>
