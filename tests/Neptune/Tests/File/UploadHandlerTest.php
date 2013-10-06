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
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->object = new UploadHandler($this->files_index);
	}

	public function tearDown() {
		Temping::getInstance()->reset();
		$_SERVER['REQUEST_METHOD'] = null;
	}

	public function testConstruct() {
		$this->assertTrue($this->object instanceof UploadHandler);
	}

}
