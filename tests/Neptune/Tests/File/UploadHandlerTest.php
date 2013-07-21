<?php

namespace Neptune\Tests\File;

use Neptune\File\UploadHandler;
use Neptune\Helpers\String;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * UploadHandlerTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UploadHandlerTest extends \PHPUnit_Framework_TestCase {

	protected $filename = 'file.txt';
	protected $files_index = 'file';
	protected $object;


	public function setUp() {
		Temping::getInstance()->create($this->filename);
		$_FILES = array();
		$_FILES[$this->files_index] = array(
			'name' => 'file.txt',
			'type' => 'text/plain',
			'tmp_name' => $this->filename,
			'error' => 0,
			'size' => 0
		);
		$this->object = new UploadHandler($this->files_index);
	}

	public function tearDown() {
		Temping::getInstance()->reset();
	}

	public function testConstruct() {
		$this->assertTrue($this->object instanceof UploadHandler);
	}

	public function testGetFilename() {
		$this->assertEquals('file.txt', $this->object->getFilename());
	}

	public function testSetFilename() {
		$this->object->setFilename('renamed.txt');
		$this->assertEquals('renamed.txt', $this->object->getFilename());
	}

	public function testScrambleFilename() {
		$this->object->scrambleFilename();
		$this->assertNotEquals('file.txt', $this->object->getFilename());
		//assert the random string is 16 characters long by default (.txt = 20).
		$this->assertTrue(strlen($this->object->getFilename()) === 20);
		//assert the random string is alphanumeric by default.
		$this->assertRegexp('`^\w+\.txt$`', $this->object->getFilename());
		$this->assertTrue(preg_match('`^\W+`', $this->object->getFilename()) === 0);
	}

	public function testScrambleOptionsLength() {
		$this->object->setScrambleOptions(8);
		$this->object->scrambleFilename();
		$this->assertNotEquals('file.txt', $this->object->getFilename());
		$this->assertTrue(strlen($this->object->getFilename()) === 12);
	}

	public function testScrambleOptions() {
		$this->object->setScrambleOptions(6, String::HEX);
		$this->object->scrambleFilename();
		$this->assertNotEquals('file.txt', $this->object->getFilename());
		$this->assertTrue(strlen($this->object->getFilename()) === 10);
		$this->assertRegexp('`^[0-9A-F]+\.txt$`', $this->object->getFilename());
		$this->assertTrue(preg_match('`[G-Z]+`', $this->object->getFilename()) === 0);
	}

}
