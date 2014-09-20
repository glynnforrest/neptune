<?php

namespace Neptune\Tests\Routing;

use Neptune\Routing\Route;

use Symfony\Component\HttpFoundation\Request;

/**
 * RouteTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RouteTest extends \PHPUnit_Framework_TestCase {

	protected function request($string) {
		return Request::create($string);
	}

	public function testHomeMatch() {
		$r = new Route('foo', '/', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/')));
		$this->assertTrue($r->test($this->request('/.html')));
		$this->assertTrue($r->test($this->request('')));
		$this->assertFalse($r->test($this->request('/url')));
	}

	public function testExplicitMatch() {
		$r = new Route('test', '/hello', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/hello')));
		$this->assertFalse($r->test($this->request('/not_hello')));
		$this->assertFalse($r->test($this->request('/hello/world')));
	}

	public function testCatchAllMatch() {
		$r = new Route('test', '.*', 'controller', 'method');
		$r->format('any');
		$this->assertTrue($r->test($this->request('/anything')));
		$this->assertTrue($r->test($this->request('')));
		$this->assertTrue($r->test($this->request('..23sd')));
	}

	public function testArgsExplicitMatch() {
		$r = new Route('test', '/url_with_args');
		$r->controller('foo')->action('index')->args([1]);
		$this->assertTrue($r->test($this->request('/url_with_args')));
		$this->assertSame(['foo', 'index', [1]], $r->getControllerAction());
	}

    public function testGetControllerAction()
    {
		$r = new Route('test', '/hello', 'controller', 'method');
		$r->test($this->request('/hello'));
        $this->assertSame(['controller', 'method', []], $r->getControllerAction());
	}

    public function testGetControllerActionThrowsExceptionBeforeTest()
    {
        $r = new Route('test', '/hello', 'controller', 'method');
        $msg = 'Route "test" is untested, unable to get controller action.';
        $this->setExpectedException('Neptune\\Routing\\RouteUntestedException', $msg);
        $r->getControllerAction();
    }

    public function testGetControllerActionThrowsExceptionWithFailedTest()
    {
        $r = new Route('test', '/hello', 'controller', 'method');
        $this->assertFalse($r->test($this->request('/fails')));
        $msg = 'Route "test" failed, unable to get controller action.';
        $this->setExpectedException('Neptune\\Routing\\RouteFailedException', $msg);
        $r->getControllerAction();
    }

	public function testNamedArgs() {
		$r = new Route('test', '/args/:id');
		$r->controller('controller')->action('method');
		$this->assertTrue($r->test($this->request('/args/4')));
		$this->assertSame(['controller', 'method', ['id' => '4']], $r->getControllerAction());
		$r2 = new Route('test', '/args/:var/:var2/:var3');
		$r2->controller('controller')->action('method');
		$r2->test($this->request('/args/foo/bar/baz'));
		$this->assertSame(array('controller', 'method',
			array('var' => 'foo',
			'var2' => 'bar',
			'var3' => 'baz')), $r2->getControllerAction());
	}

	public function testDefaultArgs() {
		$r = new Route('test', '/hello(/:place)', 'foo', 'method');
		$r->args(['place' => 'world']);
		$r->test($this->request('/hello'));
		$this->assertSame(['foo', 'method', ['place' => 'world']], $r->getControllerAction());
		$r->test($this->request('/hello/earth'));
		$this->assertSame(['foo', 'method', ['place' => 'earth']], $r->getControllerAction());
	}

	public function testAutoArgs() {
		$r = new Route('test', '/url(/:args)');
		$r->controller('test')->action('index')->autoArgs();
		$r->test($this->request('/url'));
		$this->assertSame(['test', 'index', []], $r->getControllerAction());
		$r->test($this->request('/url/one'));
		$this->assertSame(['test', 'index', ['one']], $r->getControllerAction());
		$r->test($this->request('/url/one/2'));
		$this->assertSame(['test', 'index', ['one', '2']], $r->getControllerAction());
		$r->test($this->request('/url/one/2/thr££'));
		$this->assertSame(['test', 'index', ['one', '2', 'thr££']], $r->getControllerAction());
	}

	public function testAutoArgsDifferentDelimeter() {
		$r = new Route('test', '/url/:args', 'controller', 'method');
		$r->autoArgs('[a-z]+');
		$this->assertTrue($r->test($this->request('/url/fooBbar5baz-qoz')));
		$expected = ['controller', 'method', ['foo', 'bar', 'baz', 'qoz']];
		$this->assertSame($expected, $r->getControllerAction());
	}

	public function testAutoArgsBadRegexThrowsException() {
		$r = new Route('test', '/url/:args', 'controller', 'method');
		$r->autoArgs('bad_regex');
		$msg = 'Unable to parse auto args with regex `bad_regex`';
		$this->setExpectedException('\Neptune\Routing\RouteFailedException', $msg);
		$r->test($this->request('/url/foo/bar/baz'));
	}

	public function testAutoArgsThrowsExceptionWithNoArgs() {
		$r = new Route('test', '/url/without_args', 'foo', 'bar');
		$r->autoArgs();
		$msg = "A route with auto args must contain ':args' in the url";
		$this->setExpectedException('\Neptune\Routing\RouteFailedException', $msg);
		$r->test($this->request('something'));
	}

	public function testNonAutoRouteCanUseArgsName() {
		$r = new Route('test', '/url/with/:args', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/url/with/foo')));
		$expected = ['controller', 'method', ['args' => 'foo']];
		$this->assertSame($expected, $r->getControllerAction());
	}

	public function testArgsRegexNoDelimeter() {
		$r = new Route('test', '/website(/:site)');
		$r->controller('test')->action('index');
		$r->argsRegex('.*');
		$r->test($this->request('/website'));
		$this->assertSame(['test', 'index', []], $r->getControllerAction());
		$r->test($this->request('/website/http://foo.com/bar/baz'));
		$this->assertSame(['test', 'index', ['site' => 'http://foo.com/bar/baz']], $r->getControllerAction());
	}

	public function testValidatedArgs() {
		$r = new Route('test', '/add/:first/:second/ok');
		$r->controller('calculator')->action('add')->rules(
			array('first' => '\d+',
			'second' => '\d+'));
		$this->assertFalse($r->test($this->request('/add/1/a/ok')));
		$this->assertTrue($r->test($this->request('/add/4/4/ok')));
	}

	public function testValidatedArgsWithDot() {
		$r = new Route('test', '/email/:email/foo');
		$r->controller('email')
		  ->action('verify')
		  ->argsRegex('[^/]+')
            ->rules(['email' => '\w+@\w+\.\w+']);
		$this->assertFalse($r->test($this->request('/email/me@glynnforrest@com/foo')));
		$this->assertTrue($r->test($this->request('/email/me@glynnforrest.com/foo')));
	}

	public function testTransforms() {
		$r = new Route('test', '/:var');
		$r->controller('foo')
          ->action('index')
          ->transforms('var', function($string) {
          return strtoupper($string);
      });
		$this->assertTrue($r->test($this->request('/foo')));
		$this->assertSame(['foo', 'index', ['var' => 'FOO']], $r->getControllerAction());
	}

    public function testTransformsCreatingObject()
    {
        $r = new Route('test', '/user/:id', 'user', 'show');
        $r->transforms('id', function($id) {
            $user = new \stdClass();
            $user->id = $id;
            return $user;
        });
        $this->assertTrue($r->test($this->request('/user/3')));
        $action = $r->getControllerAction();
        $this->assertSame('user', $action[0]);
        $this->assertSame('show', $action[1]);
        $this->assertInstanceOf('\stdClass', $action[2]['id']);
        $this->assertSame('3', $action[2]['id']->id);
    }

	public function testOneFormat() {
		$r = new Route('test', '/foo', 'test', 'foo');
		$r->format('json');
		$this->assertTrue($r->test($this->request('/foo.json')));
		$this->assertFalse($r->test($this->request('/foo.html')));
		$this->assertFalse($r->test($this->request('/foo')));
	}

	public function testHtmlFormatDefault() {
		$r = new Route('test', '/foo', 'test', 'foo');
		$this->assertFalse($r->test($this->request('/foo.xml')));
		$this->assertTrue($r->test($this->request('/foo.html')));
	}

	public function testAnyFormat() {
		$r = new Route('test', '/format', 'test', 'index');
		$r->format('any');
		$this->assertTrue($r->test($this->request('/format.json')));
		$this->assertTrue($r->test($this->request('/format.html')));
		$this->assertTrue($r->test($this->request('/format.xml')));
		$this->assertTrue($r->test($this->request('/format.alien_format')));
	}

	public function testGetUrl() {
		$r = new Route('test', '/hiya');
		$r->controller('controller')->action('index');
		$this->assertSame('/hiya', $r->getUrl());
	}

	public function testChangeUrl() {
		$r = new Route('test', '.*', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/anything')));
		$this->assertTrue($r->test($this->request('/url')));
		$r->url('/url');
		$this->assertFalse($r->test($this->request('/anything')));
		$this->assertTrue($r->test($this->request('/url')));
	}

	public function testOneMethod() {
		$r = new Route('test', '.*', 'controller', 'method');
		$r->method('get');
		$req = $this->request('/anything');
		$req->setMethod('get');
		$this->assertTrue($r->test($req));
		$req->setMethod('post');
		$this->assertFalse($r->test($req));
	}

	public function testArrayMethod() {
		$r = new Route('test', '.*', 'controller', 'method');
		$r->method(['get', 'PoST']);
		$req = $this->request('/anything');
		$req->setMethod('get');
		$this->assertTrue($r->test($req));
		$req->setMethod('Post');
		$this->assertTrue($r->test($req));
		$req->setMethod('PUT');
		$this->assertFalse($r->test($req));
	}

    public function testMethodIsChanged()
    {
        $r = new Route('test', '.*', 'controller', 'method');
        $r->method('get');
        $this->assertTrue($r->test($this->request('/page')));
        $r->method('post');
        $this->assertFalse($r->test($this->request('/page')));
    }

	public function testTrailingSlashesStripped() {
		$r = new Route('test', '/page', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/page/')));
		$this->assertTrue($r->test($this->request('/page//')));
		$this->assertTrue($r->test($this->request('/page////')));
	}

	public function testArgWithDot() {
		$r = new Route('test', '/route/:arg/suffix/just/because');
		$r->controller('Foo')->action('bar')->argsRegex('[^/]+');
		$this->assertTrue($r->test($this->request('/route/test.css/suffix/just/because')));
		$this->assertSame(['Foo', 'bar', ['arg' => 'test.css']],
							$r->getControllerAction());
	}

    public function testArgsMixedWithDefaults()
    {
        $r = new Route('test', '/hello/:name', 'foo', 'action');
        $r->args(['lang' => 'en']);
        $this->assertTrue($r->test($this->request('/hello/glynn')));
        $args = [
            'lang' => 'en',
            'name' => 'glynn'
        ];
        $this->assertSame(['foo', 'action', $args], $r->getControllerAction());
    }

    public function testArgsMixedSetOrder()
    {
        $r = new Route('test', '/hello/:name', 'foo', 'action');
        $r->args(['name' => 'world', 'lang' => 'en']);
        $this->assertTrue($r->test($this->request('/hello/glynn')));
        $args = [
            'name' => 'glynn',
            'lang' => 'en'
        ];
        $this->assertSame(['foo', 'action', $args], $r->getControllerAction());
    }

	public function testControllerNullNotApplied() {
		$r = new Route('test', '.*', 'controller', 'method');
		$r->controller(null);
		$r->test($this->request('anything'));
		$action = $r->getControllerAction();
        $this->assertSame('controller', $action[0]);
		$this->assertSame('method', $action[1]);
	}

	public function testMethodCanBeReset() {
		$r = new Route('test', '.*', 'controller', 'method');
		$r->action(null);
		$this->assertFalse($r->test($this->request('anything')));
        $this->assertSame(Route::FAILURE_ACTION, $r->getStatus());
	}

    public function testArgsCanBeReset()
    {
        $r = new Route('test', '.*', 'controller', 'method', ['foo' => 'bar']);
        $r->args([]);
        $this->assertTrue($r->test($this->request('/url')));
        $this->assertSame(['controller', 'method', []], $r->getControllerAction());
    }

	public function testGetStatusPassed() {
		$r = new Route('test', '/url', 'controller', 'method');
		$this->assertTrue($r->test($this->request('/url')));
		$this->assertSame(Route::PASSED, $r->getStatus());
	}

	public function testGetStatusUntested() {
		$r = new Route('test', '.*');
		$this->assertSame(Route::UNTESTED, $r->getStatus());
	}

	public function testGetStatusFailedUrl() {
		$r = new Route('test', '/some_url', 'foo', 'bar');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_URL, $r->getStatus());
	}

	public function testGetStatusFailedMethod() {
		$r = new Route('test', '.*', 'foo', 'bar');
		$r->method('post');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_METHOD, $r->getStatus());
	}

	public function testGetStatusFailedFormat() {
		$r = new Route('test', '.*', 'foo', 'bar');
		$r->format('json');
		$this->assertFalse($r->test($this->request('/something')));
		$this->assertSame(Route::FAILURE_FORMAT, $r->getStatus());
	}

	public function testGetStatusFailedController() {
		$r = new Route('test', '/url');
		$this->assertFalse($r->test($this->request('/url')));
		$this->assertSame(Route::FAILURE_CONTROLLER, $r->getStatus());
	}

	public function testGetStatusFailedAction() {
		$r = new Route('test', '/url', 'controller');
		$this->assertFalse($r->test($this->request('/url')));
		$this->assertSame(Route::FAILURE_ACTION, $r->getStatus());
	}

	public function testGetStatusFailedValidation() {
		$r = new Route('test', '/foo/bar/:message');
		$r->controller('foo')->action('bar');
		$r->rules(['message' => 'alpha']);
		$this->assertFalse($r->test($this->request('/foo/bar/baz1')));
	}

	public function testHiddenFormatIsNotInAction() {
		$r = new Route('test', '/foo/:bar/baz', 'foo', 'hello');
		$this->assertTrue($r->test($this->request('/foo/hello/baz.html')));
		$expected = array(
			'foo', 'hello', ['bar' => 'hello']
		);
		$this->assertSame($expected, $r->getControllerAction());
	}

	public function testHiddenFormatWorksWithVariable() {
		$r = new Route('test', '/foo/:bar/:baz', 'foo', 'hello');
		$this->assertFalse($r->test($this->request('/foo/hello/world.json')));
		$this->assertTrue($r->test($this->request('/foo/hello/world')));
		$this->assertTrue($r->test($this->request('/foo/hello/world.html')));
		$expected = ['foo', 'hello', ['bar' => 'hello', 'baz' => 'world']];
		$this->assertSame($expected, $r->getControllerAction());
	}

}
