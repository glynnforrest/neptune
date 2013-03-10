<?php

namespace Neptune\Tests\File;

use Neptune\File\UploadHandler;
use Neptune\Helpers\String;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * UploadHandlerTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UploadHandlerTest extends \PHPUnit_Framework_TestCase {

	protected static $file_path = '/tmp/file.txt';
	protected static $files_index = 'file';
	protected $object;


	public function setUp() {
		touch(self::$file_path);
		$_FILES = array();
		$_FILES[self::$files_index] = array(
			'name' => 'file.txt',
			'type' => 'text/plain',
			'tmp_name' => self::$file_path,
			'error' => 0,
			'size' => 0
		);
		$this->object = new UploadHandler(self::$files_index);
	}

	public function tearDown() {
		@unlink(self::$file_path);
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
		$this->assertTrue(preg_match('`^\w+\.txt$`', $this->object->getFilename()) === 1);
		$this->assertTrue(preg_match('`[^\w]+`', $this->object->getFilename()) === 0);
	}

	public function testScrambleOptionsLength() {
		$this->object->setScrambleOptions(8);
		$this->object->scrambleFilename();
		$this->assertNotEquals('file.txt', $this->object->getFilename());
		$this->assertTrue(strlen($this->object->getFilename()) === 8);
	}

	public function testScrambleOptions() {
		$this->object->setScrambleOptions(6, String::HEX);
		$this->object->scrambleFilename();
		$this->assertNotEquals('file.txt', $this->object->getFilename());
		$this->assertTrue(strlen($this->object->getFilename()) === 6);
		$this->assertTrue(preg_match('`^[0-9A-F]+$`', $this->object->getFilename()) === 1);
		$this->assertTrue(preg_match('`[G-Z]+`', $this->object->getFilename()) === 0);
	}


}
?>
