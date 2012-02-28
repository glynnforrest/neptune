<?php

namespace neptune\core;

use neptune\core\Route;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * RouteTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RouteTest extends \PHPUnit_Framework_TestCase {

	public function testHomeMatch() {
		$r = new Route('/', 'controller', 'method');
		$this->assertTrue($r->test('/'));
		$this->assertFalse($r->test('/url'));
		$this->assertFalse($r->test(''));
	}

	public function testExplicitMatch() {
		$r = new Route('/hello', 'controller', 'method');
		$this->assertTrue($r->test('/hello'));
		$this->assertFalse($r->test('/not_hello'));
	}

	public function testCatchAllMatch() {
		$r = new Route('.*', 'controller', 'method');
		$this->assertTrue($r->test('/anything'));
		$this->assertTrue($r->test(''));
		$this->assertTrue($r->test('..23sd'));
	}

	public function testControllerMatch() {
		$r = new Route('/url/:controller');
		$r->method('index');
		$this->assertTrue($r->test('/url/foo'));
		$this->assertEquals(array('foo', 'index', null), $r->getAction());
	}

	public function testArgsExplicitMatch() {
		$r = new Route('/url_with_args');
		$r->controller('foo')->method('index')->args(array(1));
		$this->assertTrue($r->test('/url_with_args'));
		$this->assertEquals(array('foo', 'index', array(1)), $r->getAction());
	}

	public function testGetActionNullBeforeTest() {
		$r = new Route('/hello', 'controller', 'method');
		$this->assertNull($r->getAction());
		$r->test('/fail');
		$this->assertNull($r->getAction());
		$r->test('/hello');
		$this->assertNotNull($r->getAction());
	}

	public function testNamedArgs() {
		$r = new Route('/args/:id');
		$r->controller('controller')->method('method');
		$r->test('/args/4');
		$this->assertEquals(array('controller', 'method', array('id' => 4)), $r->getAction());
		$r2 = new Route('/args/:var/:var2/:var3');
		$r2->controller('controller')->method('method');
		$r2->test('/args/foo/bar/baz');
		$this->assertEquals(array('controller', 'method',
			array('var' => 'foo',
			'var2' => 'bar',
			'var3' => 'baz')), $r2->getAction());
	}

	public function testDefaultArgs() {
		$r = new Route('/hello(/:place)', 'foo', 'method');
		$r->defaults(array('place' => 'world'));
		$r->test('/hello');
		$this->assertEquals(array('foo', 'method', array('place' => 'world')), $r->getAction());
	}

}
?>
