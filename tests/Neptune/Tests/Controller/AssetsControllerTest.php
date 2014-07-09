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
    protected $neptune;

	public function setUp() {
		$this->temp = new Temping();
		$this->dir = $this->temp->getDirectory();
        $this->neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                        ->disableOriginalConstructor()
                        ->getMock();
		$this->obj = new AssetsController($this->neptune);
	}

	public function tearDown() {
		$this->temp->reset();
	}

	public function testInheritsController() {
		$this->assertTrue($this->obj instanceof Controller);
	}

	public function testGetAssetPath() {
		$actual = $this->dir . 'app/assets/asset.css';
        $this->neptune->expects($this->once())
                      ->method('getRootDirectory')
                      ->will($this->returnValue($this->dir));

		$this->assertSame($actual, $this->obj->getAssetPath('asset.css'));
	}

	public function testServeAsset() {
		$this->temp->create('app/assets/asset.css', 'css_content');
        $this->neptune->expects($this->once())
                      ->method('getRootDirectory')
                      ->will($this->returnValue($this->dir));
		$response = $this->obj->serveAssetAction(new Request(), 'asset.css');
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
		$this->assertSame('css_content', $response->getContent());
		$this->assertEquals('text/css', $response->headers->get('content-type'));
	}

}
