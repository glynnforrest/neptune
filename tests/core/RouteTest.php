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

	public function testControllerMatch() {
		$r = new Route('/url/:controller');
		$r->func('index');
		$this->assertTrue($r->test('/url/foo'));
		$this->assertEquals(array('foo', 'index', null), $r->getAction());
	}

	public function testArgsExplicitMatch() {
		$r = new Route('/url_with_args');
		$r->controller('foo')->func('index')->args(array(1));
		$this->assertTrue($r->test('/url_with_args'));
		$this->assertEquals(array('foo', 'index', array(1)), $r->getAction());
	}

	public function testGetActionNullBeforeTest() {
		$r = new Route('/hello', 'controller', 'function');
		$this->assertNull($r->getAction());
		$r->test('/fail');
		$this->assertNull($r->getAction());
		$r->test('/hello');
		$this->assertNotNull($r->getAction());
	}

	public function testNamedArgs() {
		$r = new Route('/args/:id');
		$r->controller('controller')->func('function');
	}

}
?>
