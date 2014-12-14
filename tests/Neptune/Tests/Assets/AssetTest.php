<?php

namespace Neptune\Tests\Assets;

use Neptune\Assets\Asset;

use Temping\Temping;

/**
 * AssetTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetTest extends \PHPUnit_Framework_TestCase {

	protected $file;

	public function setUp() {
		$this->temp = new Temping();
		$this->file = $this->temp->create('test_asset', 'content')
								 ->getPathname('test_asset');
	}

	public function tearDown() {
		$this->temp->reset();
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

	public function testAssetFromFile() {
		$a = new Asset();
		$this->assertNull($a->getContent());
		$a->loadFile($this->file);
		$this->assertEquals('content', $a->getContent());
	}

	public function testAssetFromFileConstruct() {
		$a = new Asset($this->file);
		$this->assertEquals('content', $a->getContent());
	}

	public function testExceptionThrownWhenFileNotFound() {
		$this->setExpectedException('\\Neptune\\Exceptions\\FileException');
		$a = new Asset('not_a_file');
	}

	public function testGetMimeType() {
		$a = new Asset($this->file);
		$this->assertSame('text/plain', $a->getMimeType());
	}

	public function testGetMimeTypeNoContent() {
		$a = new Asset();
		$this->assertNull($a->getMimeType());
	}

	public function testOverrideMimeType() {
		$a = new Asset($this->file);
		$this->assertSame('text/plain', $a->getMimeType());
		$a->setMimeType('text/css');
		$this->assertSame('text/css', $a->getMimeType());
	}

	public function testGetMimeTypeCss() {
		$css = 'styles.css';
		$this->temp->create($css, '#Stylesheet');
		$a = new Asset($this->temp->getPathname($css));
		$this->assertSame('text/css', $a->getMimeType());
	}

	public function testGetMimeTypeJs() {
		$js = 'app.js';
		$this->temp->create($js, '//javascript');
		$a = new Asset($this->temp->getPathname($js));
		$this->assertSame('application/javascript', $a->getMimeType());
	}

	public function testGetContentLength() {
		$a = new Asset($this->file);
		$this->assertSame(7, $a->getContentLength());
		$a->setContent('foo_bar_baz');
		$this->assertSame(11, $a->getContentLength());
	}

	public function testGetEmptyContentLength() {
		$a = new Asset();
		$this->assertSame(0, $a->getContentLength());
	}

}
