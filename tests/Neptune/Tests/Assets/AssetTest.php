<?php

namespace Neptune\Tests\Assets;

use Neptune\Assets\Asset;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * AssetTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetTest extends \PHPUnit_Framework_TestCase {


	public function setUp() {

	}

	public function tearDown() {

	}

	public function testConstruct() {
		$a = new Asset();
		$this->assertTrue($a instanceof Asset);
	}

	public function testSetAndGetContent() {
		$a = new Asset();
		$a->setContent('source');
		$this->assertEquals('source', $a->getContent());
	}

	public function testAddAndGetFilters() {
		$a = new Asset();
		$a->addFilter('test_filter');
		$this->assertEquals(array('test_filter'), $a->getFilters());
		$a->addFilter('another_filter');
		$this->assertEquals(array('test_filter', 'another_filter'), $a->getFilters());
	}

	public function testAssetFromFile() {
		$file = '/tmp/test_asset';
		file_put_contents($file, 'content');
		$a = new Asset();
		$this->assertNull($a->getContent());
		$a->loadFile($file);
		$this->assertEquals('content', $a->getContent());
		@unlink($file);
	}

	public function testAssetFromFileConstruct() {
		$file = '/tmp/test_asset';
		file_put_contents($file, 'content');
		$a = new Asset($file);
		$this->assertEquals('content', $a->getContent());
		@unlink($file);
	}

	public function testExceptionThrownWhenFileNotFound() {
		$this->setExpectedException('\\Neptune\\Exceptions\\FileException');
		$a = new Asset('not_a_file');
	}

}
?>
