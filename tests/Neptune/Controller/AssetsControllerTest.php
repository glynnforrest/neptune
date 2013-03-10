<?php

namespace neptune\controller;

use neptune\controller\Controller;
use neptune\core\Config;
use neptune\http\Request;
use neptune\tests\assets\UpperCaseFilter;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * AssetsControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsControllerTest extends \PHPUnit_Framework_TestCase {


	public function setUp() {
		Config::create('temp');
		Config::set('assets.dir', '/tmp/');
	}

	public function tearDown() {
		Config::unload();
	}

	public function testConstruct() {
		$c = new AssetsController();
		$this->assertTrue($c instanceof Controller);
	}

	public function testGetAssetPath() {
		$c = new AssetsController();
		$this->assertEquals('/tmp/asset.css', $c->getAssetPath('asset.css'));
	}

	public function testGetAssetFiltersSingle() {
		Config::set('assets.filters', array('`.*foo.*`' => 'foo_filter'));
		$c = new AssetsController();
		$this->assertEquals(array('foo_filter'), $c->getAssetFilters('asset_with_foo_in'));
		$this->assertEquals(array(), $c->getAssetFilters('asset_without_f00_in'));
	}

	public function testGetAssetFiltersMany() {
		Config::set('assets.filters', array('`.*\.js`' => 'js_filter',
			'`.*\.css`' => 'css_filter'));
		$c = new AssetsController();
		$this->assertEquals(array('js_filter'), $c->getAssetFilters('javascript.js'));
		$this->assertEquals(array(), $c->getAssetFilters('blahjs'));
		$this->assertEquals(array('css_filter'), $c->getAssetFilters('style.css'));
		$this->assertEquals(array('js_filter', 'css_filter'), $c->getAssetFilters('test.js.css'));
	}

	public function testServeAsset() {
		$c = new AssetsController();
		$file = '/tmp/asset.css';
		file_put_contents($file, 'css_content');
		Request::getInstance()->setFormat('css');
		$this->assertEquals('css_content', $c->serveAsset('temp#asset'));
		$this->assertEquals('css_content', $c->serveAsset('asset'));
		@unlink($file);
		Request::getInstance()->resetStoredVars();
	}

	public function testServeFilteredAsset() {
		$c = new AssetsController();
		$file = '/tmp/filtered.js';
		file_put_contents($file, 'js_content');
		Config::set('temp#assets.filters', array('`.*\.js$`' => 'upper'));
		AssetsController::registerFilter('upper', '\\neptune\\tests\\assets\\UpperCaseFilter');
		Request::getInstance()->setFormat('js');
		$this->assertEquals('JS_CONTENT', $c->serveAsset('temp#filtered'));
		$this->assertEquals('JS_CONTENT', $c->serveAsset('filtered'));
		@unlink($file);
		Request::getInstance()->resetStoredVars();
	}
	
}
?>
