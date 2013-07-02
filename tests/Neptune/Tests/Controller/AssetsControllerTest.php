<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;
use Neptune\Controller\AssetsController;
use Neptune\Core\Config;
use Neptune\Http\Request;
use Neptune\Tests\Assets\UpperCaseFilter;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetsControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsControllerTest extends \PHPUnit_Framework_TestCase {


	public function setUp() {
		$c = Config::create('temp');
		$c->set('assets.dir', '/tmp/');
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
		$conf = Config::load();
		$conf->set('assets.filters', array('`.*foo.*`' => 'foo_filter'));
		$c = new AssetsController();
		$this->assertEquals(array('foo_filter'), $c->getAssetFilters('asset_with_foo_in'));
		$this->assertEquals(array(), $c->getAssetFilters('asset_without_f00_in'));
	}

	public function testGetAssetFiltersMany() {
		$conf = Config::load();
		$conf->set('assets.filters', array('`.*\.js`' => 'js_filter',
			'`.*\.css`' => 'css_filter|upper'));
		$c = new AssetsController();
		$this->assertEquals(array('js_filter'), $c->getAssetFilters('javascript.js'));
		$this->assertEquals(array(), $c->getAssetFilters('blahjs'));
		$this->assertEquals(array('css_filter', 'upper'), $c->getAssetFilters('style.css'));
		$this->assertEquals(array('js_filter', 'css_filter', 'upper'), $c->getAssetFilters('test.js.css'));
	}

	public function testServeAsset() {
		$c = new AssetsController();
		$file = '/tmp/asset.css';
		file_put_contents($file, 'css_content');
		Request::getInstance()->setFormat('css');
		$this->assertEquals('css_content', $c->serveAsset('asset'));
		@unlink($file);
		Request::getInstance()->resetStoredVars();
	}

	public function testServeFilteredAsset() {
		$c = new AssetsController();
		$file = '/tmp/filtered.js';
		file_put_contents($file, 'js_content');
		$conf = Config::load();
		$conf->set('assets.filters', array('`.*\.js$`' => 'upper'));
		AssetsController::registerFilter('upper', '\\Neptune\\Tests\\Assets\\UpperCaseFilter');
		Request::getInstance()->setFormat('js');
		$this->assertEquals('JS_CONTENT', $c->serveAsset('filtered'));
		@unlink($file);
		Request::getInstance()->resetStoredVars();
	}

}
