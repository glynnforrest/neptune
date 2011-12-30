<?php

namespace neptune\core;

use neptune\core\Neptune;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * NeptuneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class NeptuneTest extends \PHPUnit_Framework_TestCase {

	public function testSetAndGet() {
		Neptune::set('key', 'value');
		$this->assertEquals('value', Neptune::get('key'));
	}

	public function testGetReturnsNull() {
		$this->assertNull(Neptune::get('foo'));
	}
}
?>
