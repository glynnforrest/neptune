<?php

namespace Neptune\Tests\Routing;

use Neptune\Routing\Route;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * RouteTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RouteTest extends \PHPUnit_Framework_TestCase {

	protected function request($string) {
		return Request::create($string);
	}

	public function testHomeMatch() {
		$r = new Route('/', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/')));
		$this->assertTrue($r->test($this->request('')));
		$this->assertFalse($r->test($this->request('/url')));
	}

	public function testExplicitMatch() {
		$r = new Route('/hello', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/hello')));
		$this->assertFalse($r->test($this->request('/not_hello')));
		$this->assertFalse($r->test($this->request('/hello/world')));
	}

	public function testCatchAllMatch() {
		$r = new Route('.*', 'controller', 'method');
		$r->format('any');
		$this->assertTrue($r->test($this->request('/anything')));
		$this->assertTrue($r->test($this->request('')));
		$this->assertTrue($r->test($this->request('..23sd')));
	}

	public function testControllerMatch() {
		$r = new Route('/url/:controller');
		$r->method('index');
		$this->assertTrue($r->test($this->request('/url/foo')));
		$this->assertEquals(array('foo', 'index', array()), $r->getAction());
	}

	public function testArgsExplicitMatch() {
		$r = new Route('/url_with_args');
		$r->controller('foo')->method('index')->args(array(1));
		$this->assertTrue($r->test($this->request('/url_with_args')));
		$this->assertEquals(array('foo', 'index', array(1)), $r->getAction());
	}

	public function testGetAction() {
		$r = new Route('/hello', 'controller', 'method');
		$r->test($this->request('/hello'));
		$this->assertNotNull($r->getAction());
	}

	public function testGetActionThrowsExceptionBeforeTest() {
		$r = new Route('/hello', 'controller', 'method');
		$this->setExpectedException('\\Neptune\\Routing\\RouteUntestedException');
		$r->getAction();
	}

	public function testGetActionThrowsExceptionWithFailedTest() {
		$r = new Route('/hello', 'controller', 'method');
		$r->test($this->request('/fails'));
		$this->setExpectedException('\\Neptune\\Routing\\RouteFailedException');
		$r->getAction();
	}

	public function testNamedArgs() {
		$r = new Route('/args/:id');
		$r->controller('controller')->method('method');
		$r->test($this->request('/args/4'));
		$this->assertEquals(array('controller', 'method', array('id' => 4)), $r->getAction());
		$r2 = new Route('/args/:var/:var2/:var3');
		$r2->controller('controller')->method('method');
		$r2->test($this->request('/args/foo/bar/baz'));
		$this->assertEquals(array('controller', 'method',
			array('var' => 'foo',
			'var2' => 'bar',
			'var3' => 'baz')), $r2->getAction());
	}

	public function testDefaultArgs() {
		$r = new Route('/hello(/:place)', 'foo', 'method');
		$r->defaultArgs(array('place' => 'world'));
		$r->test($this->request('/hello'));
		$this->assertEquals(array('foo', 'method', array('place' => 'world')), $r->getAction());
		$r->test($this->request('/hello/earth'));
		$this->assertEquals(array('foo', 'method', array('place' => 'earth')), $r->getAction());
	}

	public function testAutoRoute() {
		$r = new Route('/:controller(/:method(/:args))');
		$r->method('index');
		$r->test($this->request('/home'));
		$this->assertEquals(array('home', 'index', array()), $r->getAction());
		$r->test($this->request('/home/hello'));
		$this->assertEquals(array('home', 'hello', array()), $r->getAction());
		$r->test($this->request('/home/hello/world'));
		$this->assertEquals(array('home', 'hello', array('world')), $r->getAction());
	}

	public function testAutoArgsArray() {
		$r = new Route('/url(/:args)');
		$r->controller('test')->method('index');
		$r->argsFormat(Route::ARGS_EXPLODE);
		$r->test($this->request('/url'));
		$this->assertEquals(array('test', 'index', array()), $r->getAction());
		$r->test($this->request('/url/one'));
		$this->assertEquals(array('test', 'index', array('one')), $r->getAction());
		$r->test($this->request('/url/one/2'));
		$this->assertEquals(array('test', 'index', array('one', 2)), $r->getAction());
		$r->test($this->request('/url/one/2/thr££'));
		$this->assertEquals(array('test', 'index', array('one', 2, 'thr££')), $r->getAction());
	}

	public function testAutoArgsSingle() {
		$r = new Route('/args(/:args)');
		$r->controller('test')->method('index');
		$r->argsFormat(Route::ARGS_SINGLE);
		$r->test($this->request('/args'));
		$this->assertEquals(array('test', 'index', array()), $r->getAction());
		$r->test($this->request('/args/args/4/sd/£$/ds/sdv'));
		$this->assertEquals(array('test', 'index', array('args/4/sd/£$/ds/sdv')), $r->getAction());
	}

	public function testValidateController() {
		$r = new Route('/:controller');
		$r->method('index');
		$r->rules(array('controller' => 'alpha'));
		$this->assertFalse($r->test($this->request('/f00')));
		$this->assertTrue($r->test($this->request('/foo')));
	}

	public function testValidateMethod() {
		$r = new Route('/:method');
		$r->controller('foo');
		$r->rules(array('method' => 'max:5'));
		$this->assertFalse($r->test($this->request('/too_long')));
		$this->assertTrue($r->test($this->request('/ok')));
	}

	public function testValidatedArgs() {
		$r = new Route('/email/:email/foo');
		$r->controller('email')->method('verify')->rules(array('email' => 'email'));
		$this->assertFalse($r->test($this->request('/email/me@glynnforrest@com/foo')));
		$this->assertTrue($r->test($this->request('/email/me@glynnforrest.com/foo')));
		$r = new Route('/add/:first/:second/ok');
		$r->controller('calculator')->method('add')->rules(array('first' =>
			'int',
			'second' => 'num'));
		$this->assertFalse($r->test($this->request('/add/1/a/ok')));
		$this->assertTrue($r->test($this->request('/add/4/4.3/ok')));
	}

	public function testTransforms() {
		$r = new Route('/:controller');
		$r->method('index')->transforms('controller', function($string) {
			return strtoupper($string);
		});
		$this->assertTrue($r->test($this->request('/foo')));
		$this->assertEquals(array('FOO', 'index', array()), $r->getAction());
	}

	public function testOneFormat() {
		$r = new Route('/foo', 'test', 'foo');
		$r->format('json');
		$this->assertTrue($r->test($this->request('/foo.json')));
		$this->assertFalse($r->test($this->request('/foo.html')));
		$this->assertFalse($r->test($this->request('/foo')));
	}

	public function testHtmlFormatDefault() {
		$r = new Route('/foo', 'test', 'foo');
		$this->assertFalse($r->test($this->request('/foo.xml')));
		$this->assertTrue($r->test($this->request('/foo.html')));
	}

	public function testAnyFormat() {
		$r = new Route('/format', 'test', 'index');
		$r->format('any');
		$this->assertTrue($r->test($this->request('/format.json')));
		$this->assertTrue($r->test($this->request('/format.html')));
		$this->assertTrue($r->test($this->request('/format.xml')));
		$this->assertTrue($r->test($this->request('/format.alien_format')));
	}

	public function testGetUrl() {
		$r = new Route('/hiya');
		$r->controller('controller')->method('index');
		$this->assertEquals('/hiya', $r->getUrl());
	}

	public function testChangeUrl() {
		$r = new Route('.*', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/anything')));
		$this->assertTrue($r->test($this->request('/url')));
		$r->url('/url');
		$this->assertFalse($r->test($this->request('/anything')));
		$this->assertTrue($r->test($this->request('/url')));
	}

	public function testOneHttpMethod() {
		$r = new Route('.*', 'controller', 'method');
		$r->httpMethod('get');
		$req = $this->request('/anything');
		$req->setMethod('get');
		$this->assertTrue($r->test($req));
		$req->setMethod('post');
		$this->assertFalse($r->test($req));
	}

	public function testArrayHttpMethod() {
		$r = new Route('.*', 'controller', 'method');
		$r->httpMethod(array('get', 'PoST'));
		$req = $this->request('/anything');
		$req->setMethod('get');
		$this->assertTrue($r->test($req));
		$req->setMethod('Post');
		$this->assertTrue($r->test($req));
		$req->setMethod('PUT');
		$this->assertFalse($r->test($req));
	}

	public function testTrailingSlashesStripped() {
		$r = new Route('/page', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/page/')));
		$this->assertTrue($r->test($this->request('/page//')));
		$this->assertTrue($r->test($this->request('/page////')));
	}

	public function testArgWithDot() {
		$r = new Route('/route/:arg/suffix/just/because');
		$r->controller('Foo')->method('bar');
		$this->assertTrue($r->test($this->request('/route/test.css/suffix/just/because')));
		$this->assertEquals(array('Foo', 'bar', array('arg' => 'test.css')),
							$r->getAction());
	}

	public function testControllerNullNotApplied() {
		$r = new Route('.*', 'controller', 'method');
		$r->controller(null);
		$r->test($this->request('anything'));
		$action = $r->getAction();
		$controller = $action[0];
		$this->assertEquals('controller', $controller);
	}

	public function testMethodNullNotApplied() {
		$r = new Route('.*', 'controller', 'method');
		$r->method(null);
		$r->test($this->request('anything'));
		$action = $r->getAction();
		$method = $action[1];
		$this->assertEquals('method', $method);
	}

	public function testArgsNullNotApplied() {
		$args = array('foo' => 'bar');
		$r = new Route('.*', 'controller', 'method', $args);
		$r->args(null);
		$r->test($this->request('anything'));
		$action = $r->getAction();
		$actual_args = $action[2];
		$this->assertEquals($args, $actual_args);
	}

	public function testGetResultPassed() {
		$r = new Route('/url', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/url')));
		$this->assertSame(Route::PASSED, $r->getResult());
	}

	public function testGetResultUntested() {
		$r = new Route('.*');
		$this->assertSame(Route::UNTESTED, $r->getResult());
	}

	public function testGetResultFailedRegexp() {
		$r = new Route('/some_url');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_REGEXP, $r->getResult());
	}

	public function testGetResultFailedHttpMethod() {
		$r = new Route('.*');
		$r->httpMethod('post');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_HTTP_METHOD, $r->getResult());
	}

	public function testGetResultFailedFormat() {
		$r = new Route('.*');
		$r->format('json');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_FORMAT, $r->getResult());
	}

	public function testGetResultFailedController() {
		$r = new Route('/url');
		$this->assertFalse($r->test($this->request('/url')));
		$this->assertSame(Route::FAILURE_CONTROLLER, $r->getResult());
	}

	public function testGetResultFailedMethod() {
		$r = new Route('/url', 'controller');
		$this->assertFalse($r->test($this->request('/url')));
		$this->assertSame(Route::FAILURE_METHOD, $r->getResult());
	}

	public function testGetResultFailedValidation() {
		$r = new Route('/foo/bar/:message');
		$r->controller('foo')->method('bar');
		$r->rules(array('message' => 'alpha'));
		$this->assertFalse($r->test($this->request('/foo/bar/baz1')));
	}

}
