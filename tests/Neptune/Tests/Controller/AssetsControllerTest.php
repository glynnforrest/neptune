<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;
use Neptune\Controller\AssetsController;
use Neptune\Config\Config;
use Neptune\Config\ConfigManager;
use Neptune\Tests\Assets\UpperCaseFilter;

use Symfony\Component\HttpFoundation\Request;

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
		$this->temp = new Temping();
		$this->dir = $this->temp->getDirectory();
		$this->config = new Config('temp');
		$this->config->set('assets.dir', $this->dir);
        $neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                        ->disableOriginalConstructor()
                        ->getMock();
        $manager = new ConfigManager($neptune);
        $manager->add($this->config);
		$this->obj = new AssetsController($manager);
	}

	public function tearDown() {
		$this->temp->reset();
	}

	public function testInheritsController() {
		$this->assertTrue($this->obj instanceof Controller);
	}

	public function testGetAssetPath() {
		$actual = $this->dir . 'asset.css';
		$this->assertEquals($actual, $this->obj->getAssetPath('asset.css'));
	}

	public function testGetAssetFiltersSingle() {
		$filters = array('`.*foo.*`' => 'foo_filter');
		$this->assertEquals(array('foo_filter'), $this->obj->getAssetFilters('asset_with_foo_in', $filters));
		$this->assertEquals(array(), $this->obj->getAssetFilters('asset_without_f00_in', $filters));
	}

	public function testGetAssetFiltersMany() {
		$filters = array('`.*\.js`' => 'js_filter',
			'`.*\.css`' => 'css_filter|upper');
		$this->assertEquals(array('js_filter'), $this->obj->getAssetFilters('javascript.js', $filters));
		$this->assertEquals(array(), $this->obj->getAssetFilters('blahjs', $filters));
		$this->assertEquals(array('css_filter', 'upper'), $this->obj->getAssetFilters('style.css', $filters));
		$this->assertEquals(array('js_filter', 'css_filter', 'upper'), $this->obj->getAssetFilters('test.js.css', $filters));
	}

	public function testServeAsset() {
		$this->temp->create('asset.css', 'css_content');
		$response = $this->obj->serveAssetAction(new Request(), 'asset.css');
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
		$this->assertSame('css_content', $response->getContent());
		$this->assertEquals('text/css', $response->headers->get('content-type'));
	}

	public function testServeFilteredAsset() {
		$this->temp->create('filtered.js', 'js_content');
		$this->config->set('assets.filters', array('`.*\.js$`' => 'upper'));
		AssetsController::registerFilter('upper', '\\Neptune\\Tests\\Assets\\UpperCaseFilter');
		$response = $this->obj->serveAssetAction(new Request(), 'filtered.js');
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('JS_CONTENT', $response->getContent());
		$this->assertEquals('application/javascript', $response->headers->get('content-type'));
	}

}
