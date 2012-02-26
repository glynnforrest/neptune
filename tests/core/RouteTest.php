<?php

namespace neptune\core;

use neptune\core\Route;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * RouteTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RouteTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		
	}

	public function tearDown() {
		
	}

	public function testHomeMatch() {
		$r = new Route('/', 'controller', 'function');
		$this->assertTrue($r->test('/'));
		$this->assertFalse($r->test('/url'));
		$this->assertFalse($r->test(''));
	}

	public function testExplicitMatch() {
		$r = new Route('/hello', 'controller', 'function');
		$this->assertTrue($r->test('/hello'));
		$this->assertFalse($r->test('/not_hello'));
	}

	public function testCatchAllMatch() {
		$r = new Route('.*', 'controller', 'function');
		$this->assertTrue($r->test('/anything'));
		$this->assertTrue($r->test(''));
		$this->assertTrue($r->test('..23sd'));
	}


	
}
?>
