<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;
use Neptune\Controller\AssetsController;
use Neptune\Core\Config;
use Neptune\Http\Request;
use Neptune\Tests\Assets\UpperCaseFilter;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetsControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsControllerTest extends \PHPUnit_Framework_TestCase {

	protected $dir;
	protected $temp;

	public function setUp() {
		$c = Config::create('temp');
		$this->temp = new Temping();
		$this->dir = $this->temp->getDirectory();
		$c->set('assets.dir', $this->dir);
	}

	public function tearDown() {
		Config::unload();
		$this->temp->reset();
	}

	public function testConstruct() {
		$c = new AssetsController();
		$this->assertTrue($c instanceof Controller);
	}

	public function testGetAssetPath() {
		$c = new AssetsController();
		$actual = $this->dir . 'asset.css';
		$this->assertEquals($actual, $c->getAssetPath('asset.css'));
	}

	public function testGetAssetFiltersSingle() {
		$conf = Config::load();
		$filters = array('`.*foo.*`' => 'foo_filter');
		$c = new AssetsController();
		$this->assertEquals(array('foo_filter'), $c->getAssetFilters('asset_with_foo_in', $filters));
		$this->assertEquals(array(), $c->getAssetFilters('asset_without_f00_in', $filters));
	}

	public function testGetAssetFiltersMany() {
		$conf = Config::load();
		$filters = array('`.*\.js`' => 'js_filter',
			'`.*\.css`' => 'css_filter|upper');
		$c = new AssetsController();
		$this->assertEquals(array('js_filter'), $c->getAssetFilters('javascript.js', $filters));
		$this->assertEquals(array(), $c->getAssetFilters('blahjs', $filters));
		$this->assertEquals(array('css_filter', 'upper'), $c->getAssetFilters('style.css', $filters));
		$this->assertEquals(array('js_filter', 'css_filter', 'upper'), $c->getAssetFilters('test.js.css', $filters));
	}

	public function testServeAsset() {
		$c = new AssetsController();
		$this->temp->create('asset.css', 'css_content');
		Request::getInstance()->setFormat('css');
		$this->assertEquals('css_content', $c->serveAsset('asset'));
		Request::getInstance()->resetStoredVars();
	}

	public function testServeFilteredAsset() {
		$c = new AssetsController();
		$this->temp->create('filtered.js', 'js_content');
		$conf = Config::load();
		$conf->set('assets.filters', array('`.*\.js$`' => 'upper'));
		AssetsController::registerFilter('upper', '\\Neptune\\Tests\\Assets\\UpperCaseFilter');
		Request::getInstance()->setFormat('js');
		$this->assertEquals('JS_CONTENT', $c->serveAsset('filtered'));
		Request::getInstance()->resetStoredVars();
	}

}
