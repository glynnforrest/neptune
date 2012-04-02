<?php

namespace neptune\assets;

use neptune\core\Config;
use neptune\assets\AssetsManager;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * AssetsManagerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsManagerTest extends \PHPUnit_Framework_TestCase {

	protected $assets;

	public function setUp() {
		Config::create('testing');
		Config::set('root_url', 'myapp.local');
		$this->assets = AssetsManager::getInstance();
		$this->assets->clear();
	}

	public function tearDown() {
		Config::unload();
	}

	public function testCss() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL, AssetsManager::css());
	}

	public function testCssOptions() {
		$this->assets->addCss('style', 'css/style.css', null, array('id' => 'my_style', 'class' => 'style'));
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" id="my_style" class="style" />' . PHP_EOL, AssetsManager::css());
	}

	public function testCssMultiple() {
		$this->assets->addCss('style', 'css/style.css');
		$this->assets->addCss('main', 'css/main.css');
		$expected = '<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL . '<link rel="stylesheet" type="text/css" href="http://myapp.local/css/main.css" />' . PHP_EOL ;
		$this->assertEquals($expected, AssetsManager::css());
	}


	public function testJs() {
		$this->assets->addJs('main', 'js/main.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testJsOptions() {
		$this->assets->addJs('main', 'js/main.js', null ,array('id' => 'my_script'));
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js" id="my_script"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testMultipleJs() {
		$this->assets->addJs('main', 'js/main.js');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/other.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testJsDepends() {
		$this->assets->addJs('page', 'js/page.js', 'lib');
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testMultipleJsDepends() {
		$this->assets->addJs('page', 'js/page.js', array('lib', 'other'));
		$this->assets->addJs('lib', 'http://site.com/js/lib.js');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testNestedJsDepends() {
		$this->assets->addJs('page', 'js/page.js', array('lib', 'other'));
		$this->assets->addJs('lib', 'http://site.com/js/lib.js', 'other');
		$this->assets->addJs('other', 'js/other.js');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/other.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://site.com/js/lib.js"></script>' . PHP_EOL .'<script type="text/javascript" src="http://myapp.local/js/page.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testJsDependsOnSelf() {
		$this->assets->addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/recurse.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}

	public function testJsDepDependsOnSelf() {
		$this->assets->addJs('main', 'js/main.js', 'recursive');
		$this->assets->addJs('recursive', 'js/recurse.js', 'recursive');
		$expected = '<script type="text/javascript" src="http://myapp.local/js/recurse.js"></script>' . PHP_EOL . '<script type="text/javascript" src="http://myapp.local/js/main.js"></script>' . PHP_EOL;
		$this->assertEquals($expected, AssetsManager::js());
	}
	
}
?>
