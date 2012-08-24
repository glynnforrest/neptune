<?php

namespace neptune\core;

use neptune\core\Route;
use neptune\http\Request;

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
		$this->assertEquals(array('foo', 'index', array()), $r->getAction());
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
		$r->defaultArgs(array('place' => 'world'));
		$r->test('/hello');
		$this->assertEquals(array('foo', 'method', array('place' => 'world')), $r->getAction());
		$r->test('/hello/earth');
		$this->assertEquals(array('foo', 'method', array('place' => 'earth')), $r->getAction());
	}

	public function testAutoRoute() {
		$r = new Route('/:controller(/:method(/:args))');
		$r->method('index');
		$r->test('/home');
		$this->assertEquals(array('home', 'index', array()), $r->getAction());
		$r->test('/home/hello');
		$this->assertEquals(array('home', 'hello', array()), $r->getAction());
		$r->test('/home/hello/world');
		$this->assertEquals(array('home', 'hello', array('world')), $r->getAction());
	}

	public function testAutoArgsArray() {
		$r = new Route('/url(/:args)');
		$r->controller('test')->method('index');
		$r->argsFormat(Route::ARGS_EXPLODE);
		$r->test('/url');
		$this->assertEquals(array('test', 'index', array()), $r->getAction());
		$r->test('/url/one');
		$this->assertEquals(array('test', 'index', array('one')), $r->getAction());
		$r->test('/url/one/2');
		$this->assertEquals(array('test', 'index', array('one', 2)), $r->getAction());
		$r->test('/url/one/2/thr££');
		$this->assertEquals(array('test', 'index', array('one', 2, 'thr££')), $r->getAction());
	}

	public function testAutoArgsSingle() {
		$r = new Route('/args(/:args)');
		$r->controller('test')->method('index');
		$r->argsFormat(Route::ARGS_SINGLE);
		$r->test('/args');
		$this->assertEquals(array('test', 'index', array()), $r->getAction());
		$r->test('/args/args/4/sd/£$/ds/sdv');
		$this->assertEquals(array('test', 'index', array('args/4/sd/£$/ds/sdv')), $r->getAction());
	}

	public function testValidateController() {
		$r = new Route('/:controller');
		$r->method('index');
		$r->rules(array('controller' => 'alpha'));
		$this->assertFalse($r->test('/f00'));
		$this->assertTrue($r->test('/foo'));
	}

	public function testValidateMethod() {
		$r = new Route('/:method');
		$r->controller('foo');
		$r->rules(array('method' => 'max:5'));
		$this->assertFalse($r->test('/too_long'));
		$this->assertTrue($r->test('/ok'));
	}

	public function testValidatedArgs() {
		$r = new Route('/email/:email');
		$r->controller('email')->method('verify')->rules(array('email' => 'email'));
		$this->assertFalse($r->test('/email/me@glynnforrest@com'));
		$this->assertTrue($r->test('/email/me@glynnforrest.com'));
		$r = new Route('/add/:first/:second');
		$r->controller('calculator')->method('add')->rules(array('first' =>
			'int',
			'second' => 'num'));
		$this->assertFalse($r->test('/add/1/a'));
		$this->assertTrue($r->test('/add/4/4.3'));
	}

	public function testTransforms() {
		$r = new Route('/:controller');
		$r->method('index')->transforms('controller', function($string) {
			return strtoupper($string);
		});
		$this->assertTrue($r->test('/foo'));
		$this->assertEquals(array('FOO', 'index', array()), $r->getAction());
	}

	public function testOneFormat() {
		Request::getInstance()->setFormat('json');
		$r = new Route('/foo', 'test', 'foo');
		$r->format('json');
		$this->assertTrue($r->test('/foo'));
		Request::getInstance()->setFormat('html');
		$this->assertFalse($r->test('/foo'));
	}

	public function testHtmlFormatDefault() {
		Request::getInstance()->setFormat('xml');
		$r = new Route('/foo', 'test', 'foo');
		$this->assertFalse($r->test('/foo'));
		Request::getInstance()->setFormat('html');
		$this->assertTrue($r->test('/foo'));
	}

	public function testAnyFormat() {
		$r = new Route('/format', 'test', 'index');
		$r->format('any');
		Request::getInstance()->setFormat('json');
		$this->assertTrue($r->test('/format'));
		Request::getInstance()->setFormat('html');
		$this->assertTrue($r->test('/format'));
		Request::getInstance()->setFormat('xml');
		$this->assertTrue($r->test('/format'));
		Request::getInstance()->setFormat('alien_format');
		$this->assertTrue($r->test('/format'));
		Request::getInstance()->setFormat('html');
	}

	public function testGetUrl() {
		$r = new Route('/hiya');
		$r->controller('controller')->method('index');
		$this->assertEquals('/hiya', $r->getUrl());
	}

	public function testChangeUrl() {
		$r = new Route('.*', 'controller', 'method');
		$this->assertTrue($r->test('/anything'));
		$this->assertTrue($r->test('/url'));
		$r->url('/url');
		$this->assertFalse($r->test('/anything'));
		$this->assertTrue($r->test('/url'));
	}

	public function testOneHttpMethod() {
		$r = new Route('.*', 'controller', 'method');
		$r->httpMethod('get');
		$_SERVER['REQUEST_METHOD'] = 'Get';
		$this->assertTrue($r->test('/anything'));
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this->assertFalse($r->test('/anything'));
	}

	public function testArrayHttpMethod() {
		$r = new Route('.*', 'controller', 'method');
		$r->httpMethod(array('get', 'PoST'));
		$_SERVER['REQUEST_METHOD'] = 'Get';
		$this->assertTrue($r->test('/anything'));
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this->assertTrue($r->test('/anything'));
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$this->assertFalse($r->test('/anything'));
	}

	public function testTrailingSlashesStripped() {
		$r = new Route('/page', 'controller', 'method');
		$this->assertTrue($r->test('/page/'));
		$this->assertTrue($r->test('/page//'));
		$this->assertTrue($r->test('/page////'));
	}

}
?>
