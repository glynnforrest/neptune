<?php
namespace neptune\file;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

class ImageHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ImageHandler
	 */
	protected $object;

	protected function setUp() {
		$this->object = new ImageHandler('/tmp/file');
	}

	protected function tearDown() {

	}

	public function testIsFileObject() {
		$this->assertTrue($this->object instanceof \SPLFileObject);
	}

	public function testAdd() {
		$this->assertEquals(1,1);
	}

}

?>
