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
		$this->assertTrue($r->test($this->request('/.html')));
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

	public function testAutoArgs() {
		$r = new Route('/url(/:args)');
		$r->controller('test')->method('index')->autoArgs();
		$r->test($this->request('/url'));
		$this->assertEquals(array('test', 'index', array()), $r->getAction());
		$r->test($this->request('/url/one'));
		$this->assertEquals(array('test', 'index', array('one')), $r->getAction());
		$r->test($this->request('/url/one/2'));
		$this->assertEquals(array('test', 'index', array('one', 2)), $r->getAction());
		$r->test($this->request('/url/one/2/thr££'));
		$this->assertEquals(array('test', 'index', array('one', 2, 'thr££')), $r->getAction());
	}

	public function testAutoArgsDifferentDelimeter() {
		$r = new Route('/url/:args', 'controller', 'method');
		$r->autoArgs('[a-z]+');
		$this->assertTrue($r->test($this->request('/url/fooBbar5baz-qoz')));
		$expected = array('controller', 'method', array('foo', 'bar', 'baz', 'qoz'));
		$this->assertSame($expected, $r->getAction());
	}

	public function testAutoArgsBadRegexThrowsException() {
		$r = new Route('/url/:args', 'controller', 'method');
		$r->autoArgs('bad_regex');
		$msg = 'Unable to parse auto args with regex `bad_regex`';
		$this->setExpectedException('\Neptune\Routing\RouteFailedException', $msg);
		$r->test($this->request('/url/foo/bar/baz'));
	}

	public function testAutoArgsThrowsExceptionWithNoArgs() {
		$r = new Route('/url/without_args', 'foo', 'bar');
		$r->autoArgs();
		$msg = "A route with auto args must contain ':args' in the url";
		$this->setExpectedException('\Neptune\Routing\RouteFailedException', $msg);
		$r->test($this->request('something'));
	}

	public function testNonAutoRouteCanUseArgsName() {
		$r = new Route('/url/with/:args', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/url/with/foo')));
		$expected = array('controller', 'method', array('args' => 'foo'));
		$this->assertSame($expected, $r->getAction());
	}

	public function testArgsRegexNoDelimeter() {
		$r = new Route('/website(/:site)');
		$r->controller('test')->method('index');
		$r->argsRegex('.*');
		$r->test($this->request('/website'));
		$this->assertEquals(array('test', 'index', array()), $r->getAction());
		$r->test($this->request('/website/http://foo.com/bar/baz'));
		$this->assertEquals(array('test', 'index', array('site' => 'http://foo.com/bar/baz')), $r->getAction());
	}

	public function testValidatedArgs() {
		$r = new Route('/add/:first/:second/ok');
		$r->controller('calculator')->method('add')->rules(
			array('first' => 'int',
			'second' => 'int'));
		$this->assertFalse($r->test($this->request('/add/1/a/ok')));
		$this->assertTrue($r->test($this->request('/add/4/4/ok')));
	}

	public function testValidatedArgsWithDot() {
		$r = new Route('/email/:email/foo');
		$r->controller('email')
		  ->method('verify')
		  ->argsRegex('[^/]+')
		  ->rules(array('email' => 'email'));
		$this->assertFalse($r->test($this->request('/email/me@glynnforrest@com/foo')));
		$this->assertTrue($r->test($this->request('/email/me@glynnforrest.com/foo')));
	}

	public function testTransforms() {
		$r = new Route('/:var');
		$r->controller('foo')
          ->method('index')
          ->transforms('var', function($string) {
          return strtoupper($string);
      });
		$this->assertTrue($r->test($this->request('/foo')));
		$this->assertEquals(array('foo', 'index', array('var' => 'FOO')), $r->getAction());
	}

    public function testTransformsCreatingObject()
    {
        $r = new Route('/user/:id', 'user', 'show');
        $r->transforms('id', function($id) {
            $user = new \stdClass();
            $user->id = $id;
            return $user;
        });
        $this->assertTrue($r->test($this->request('/user/3')));
        $action = $r->getAction();
        $this->assertSame('user', $action[0]);
        $this->assertSame('show', $action[1]);
        $this->assertInstanceOf('\stdClass', $action[2]['id']);
        $this->assertSame('3', $action[2]['id']->id);
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
		$r->controller('Foo')->method('bar')->argsRegex('[^/]+');
		$this->assertTrue($r->test($this->request('/route/test.css/suffix/just/because')));
		$this->assertEquals(array('Foo', 'bar', array('arg' => 'test.css')),
							$r->getAction());
	}

	public function testControllerNullNotApplied() {
		$r = new Route('.*', 'controller', 'method');
		$r->controller(null);
		$r->test($this->request('anything'));
		$action = $r->getAction();
        $this->assertSame('controller', $action[0]);
		$this->assertSame('method', $action[1]);
	}

	public function testMethodNullNotApplied() {
		$r = new Route('.*', 'controller', 'method');
		$r->method(null);
		$r->test($this->request('anything'));
		$action = $r->getAction();
        $this->assertSame('controller', $action[0]);
		$this->assertSame('method', $action[1]);
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
		$r = new Route('/some_url', 'foo', 'bar');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_REGEXP, $r->getResult());
	}

	public function testGetResultFailedHttpMethod() {
		$r = new Route('.*', 'foo', 'bar');
		$r->httpMethod('post');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_HTTP_METHOD, $r->getResult());
	}

	public function testGetResultFailedFormat() {
		$r = new Route('.*', 'foo', 'bar');
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

	public function testHiddenFormatIsNotInActionArray() {
		$r = new Route('/foo/:bar/baz', 'foo', 'hello');
		$this->assertTrue($r->test($this->request('/foo/hello/baz.html')));
		$expected = array(
			'foo', 'hello', array('bar' => 'hello')
		);
		$this->assertSame($expected, $r->getAction());
	}

	public function testHiddenFormatWorksWithVariable() {
		$r = new Route('/foo/:bar/:baz', 'foo', 'hello');
		$this->assertFalse($r->test($this->request('/foo/hello/world.json')));
		$this->assertTrue($r->test($this->request('/foo/hello/world')));
		$this->assertTrue($r->test($this->request('/foo/hello/world.html')));
		$expected = array('foo', 'hello', array('bar' => 'hello', 'baz' => 'world'));
		$this->assertSame($expected, $r->getAction());
	}

    public function testSetPrefix()
    {
        $r = new Route('/foo');
        $this->assertSame($r, $r->setPrefix('admin'));
        $this->assertSame('admin', $r->getPrefix());
    }

    public function testPrefixIsSubsitutedInUrl()
    {
        $r = new Route('/:prefix');
        $r->setPrefix('foo');
        $this->assertSame('/:prefix', $r->getUrl());
        $r->url('/:prefix');
        $this->assertSame('/foo', $r->getUrl());
    }

}
