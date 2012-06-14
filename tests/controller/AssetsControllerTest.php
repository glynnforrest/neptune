<?php

namespace neptune\controller;

use neptune\controller\Controller;
use neptune\core\Config;
use neptune\http\Request;

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

	public function testGetAssetFileName() {
		$c = new AssetsController();
		$this->assertEquals('/tmp/asset.css', $c->getAssetFileName('temp#asset.css'));
		$this->assertEquals('/tmp/asset.css', $c->getAssetFileName('asset.css'));
	}

	public function testServeAsset() {
		$c = new AssetsController();
		$file = '/tmp/asset.css';
		file_put_contents($file, 'css_content');
		Request::getInstance()->setFormat('css');
		$this->assertEquals('css_content', $c->serveAsset('asset'));
		$this->assertEquals('css_content', $c->serveAsset('temp#asset'));
		@unlink($file);
		Request::getInstance()->resetStoredVars();
	}

	
}
?>
