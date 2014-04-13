<?php

namespace Neptune\Tests\Routing;

use Neptune\Core\Config;
use Neptune\Routing\Router;
use Neptune\Routing\Route;

use Symfony\Component\HttpFoundation\Request;

include __DIR__ . ('/../../../bootstrap.php');

/**
 * RouterTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class RouterTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->config = Config::create('neptune');
		$this->config->set('root_url', 'myapp.local/');
		$this->router = new Router($this->config);
	}

	public function tearDown() {
		Config::unload();
	}

	protected function routeTest($route, $url) {
		$req = Request::create($url);
		return $route->test($req);
	}

    protected function match($pathinfo, $method = 'GET')
    {
        $request = Request::create($pathinfo);
        $request->setMethod($method);
        return $this->router->match($request);
    }

	public function testRouteReturnsRoute() {
		$this->assertInstanceOf('\Neptune\Routing\Route', $this->router->route('/url'));
	}

	public function testGlobalsReturnsRoute() {
		$this->assertInstanceOf('\Neptune\Routing\Route', $this->router->globals());
	}

	public function testCatchAllReturnRoute() {
		$r = $this->router->catchAll('foo');
		$this->assertInstanceOf('\Neptune\Routing\Route', $r);
		$this->assertSame('.*', $r->getUrl());
		//assert route is given a name
		$names = array('neptune.catch_all' => '.*');
		$this->assertSame($names, $this->router->getNames());
	}

	public function testMissingSlash() {
		$r = $this->router->route('test');
		$this->assertSame('/test', $r->getUrl());
	}

	public function testRouteInheritsGlobals() {
		$this->router->globals()->controller('foo');
		$r = $this->router->route('/foo', null, 'index');
		$this->routeTest($r, '/foo');
		$this->assertSame(array('foo', 'index', array()), $r->getAction());
	}

	public function testRouteAssets() {
		$this->config->set('assets.url', '/assets/');
		$r = $this->router->routeAssets();
		$this->assertSame('/assets/:asset', $r->getUrl());
		$this->assertTrue($this->routeTest($r, '/assets/css/test'));
		$expected = array(
			'\Neptune\Controller\AssetsController',
			'serveAsset',
			array('asset' => 'css/test'));
		$this->assertSame($expected, $r->getAction());
		//assert the route is given a name
		$names = array('neptune.assets' => '/assets/:asset');
		$this->assertSame($names, $this->router->getNames());
	}

	public function testRouteAssetsMissingSlashes() {
		$this->config->set('assets.url', 'assets');
		$r = $this->router->routeAssets();
		$this->assertSame('/assets/:asset', $r->getUrl());
		$this->assertTrue($this->routeTest($r, '/assets/lib/js/test'));
		$expected = array(
			'\Neptune\Controller\AssetsController',
			'serveAsset',
			array('asset' => 'lib/js/test')
		);
		$this->assertSame($expected, $r->getAction());
	}

	public function testMatch() {
		$this->router->route('/test', 'test', 'index');
		$expected = array('test', 'index', array());
		$this->assertSame($expected, $this->match('/test'));
	}

	public function testMatchThrowsExceptionNoAction() {
		$msg = 'No route found that matches "foo"';
		$this->setExpectedException('\Neptune\Routing\RouteNotFoundException', $msg);
		$this->match('foo');
	}

	public function testCatchAll() {
		$this->router->route('/test', 'test', 'index');
		$this->router->catchAll('foo');
		$expected = array('foo', 'index', array());
		$this->assertSame($expected, $this->match('/foo'));
	}

	public function testGetRoutes() {
		$this->router->catchAll('foo');
		$routes = $this->router->getRoutes();
		$this->assertInstanceOf('\Neptune\Routing\Route', $routes[0]);
		$this->assertSame('.*', $routes[0]->getUrl());
	}

	protected function setUpTestModule($name) {
		//a simple routes.php is in the etc/ directory
		$this->config->set('dir.root', __DIR__ . '/');
		$this->config->set('modules.' . $name, 'etc/');
	}

	public function testAddModuleSetsPrefix() {
		$this->setUpTestModule('foo');
		//routes.php defines a route with '/:prefix/login
		$this->router->routeModule('foo');
		$routes = $this->router->getRoutes();
		$this->assertInstanceOf('\Neptune\Routing\Route', $routes[0]);
		$this->assertSame('/foo/login', $routes[0]->getUrl());
	}

	public function testAddModuleSetsDefinedPrefix() {
		$this->setUpTestModule('foo');
		$this->router->routeModule('foo', 'different_prefix');
		$routes = $this->router->getRoutes();
		$this->assertInstanceOf('\Neptune\Routing\Route', $routes[0]);
		$this->assertSame('/different_prefix/login', $routes[0]->getUrl());
	}

	public function testAddModuleHasLocalGlobals() {
		$this->setUpTestModule('foo');
		$this->router->routeModule('foo');
		$routes = $this->router->getRoutes();
		$route = $routes[0];
		$this->assertTrue($this->routeTest($route, '/foo/login'));
		$action = $route->getAction();
		$this->assertSame('::foo.controller.bar', $action[0]);
	}

	public function testAddModuleDoesNotChangeGlobals() {
		$this->setUpTestModule('bar');
		$before = $this->router->globals()->controller('foo');
		$this->router->routeModule('bar');
		$after = $this->router->globals();
		$this->assertSame($before, $after);
		$route = $this->router->route('.*', null, 'some_method');
		$this->assertTrue($this->routeTest($route, 'anything'));
		$action = $route->getAction();
		$this->assertSame('foo', $action[0]);
	}

	public function testAddModuleDefinesNewGlobals() {
		$this->setUpTestModule('admin');
		$this->router->globals()->args(array('globals' => 'very_yes'));
		$this->router->routeModule('admin');
		$routes = $this->router->getRoutes();
		$route = $routes[0];
		$this->assertTrue($this->routeTest($route, '/admin/login'));
		$action = $route->getAction();
		$args = $action[2];
		$this->assertSame(array(), $args);
	}

	public function testGetAndSetCache() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$this->router->setCache($driver);
		$this->assertSame($driver, $this->router->getCache());
	}

	public function testName() {
		$this->assertInstanceOf('\Neptune\Routing\Router', $this->router->name('route'));
		$this->assertSame('route', $this->router->getName());
	}

	public function testNameIsAddedToRouteThenUnset() {
		$this->router->name('foo');
		$this->assertSame('foo', $this->router->getName());
		$r = $this->router->route('/test', 'foo');
		$this->assertNull($this->router->getName());
		$expected = array('foo' => $r->getUrl());
		$this->assertSame($expected, $this->router->getNames());
		//second route doesn't have a name, so names should not be modified
		$this->router->route('/second', 'bar');
		$this->assertNull($this->router->getName());
		$this->assertSame($expected, $this->router->getNames());
	}

	public function testGetNamesReturnsEmptyArray() {
		$this->assertSame(array(), $this->router->getNames());
	}

	public function testUrlSimple() {
		$this->router->name('simple')->route('/url', 'controller');
		$this->assertSame('http://myapp.local/url', $this->router->url('simple'));
	}

	public function testUrlSimpleFtp() {
		$this->router->name('ftp')->route('/url', 'controller');
		$this->assertSame('ftp://myapp.local/url', $this->router->url('ftp', array(), 'ftp'));
	}

	public function testUrlWithArgs() {
		$this->router->name('args')->route('/url/:var/:second', 'controller');
		$this->assertSame('http://myapp.local/url/foo/bar', $this->router->url('args', array('var' => 'foo', 'second' => 'bar')));
	}

	public function testUrlWithArgsFtp() {
		$this->router->name('args_ftp')->route('/url/:var/:second', 'controller');
		$this->assertSame('ftp://myapp.local/url/foo/bar', $this->router->url('args_ftp',
			array('var' => 'foo', 'second' => 'bar'), 'ftp'));
	}

	public function testUrlOptionalArgs() {
		$this->router->name('opt_args')->route('/url/(:var(/:second))');
		$this->assertSame('http://myapp.local/url/foo',
			$this->router->url('opt_args', array('var' => 'foo')));
	}

	public function testUrlNoOptionalArgs() {
		$this->router->name('no_opt_args')->route('/url/(:var(/:second))', 'controller');
		$this->assertSame('http://myapp.local/url', $this->router->url('no_opt_args'));
	}

	public function testUrlAppendedGetVariables() {
		$this->router->name('get')->route('/get/:id', 'getController');
		$args = array('id' => 34, 'foo' => 'bar', 'baz' => 'qoz');
		$actual = $this->router->url('get', $args);
		$this->assertSame('http://myapp.local/get/34?foo=bar&baz=qoz', $actual);
	}

	public function testUrlThrowsExceptionWithNoNames() {
		$this->setExpectedException('\Exception', 'No named routes defined');
		$this->router->url('get');
	}

	public function testUrlThrowsExceptionUnknownName() {
		$this->router->name('foo')->route('/foo', 'bar');
		$this->setExpectedException('\Exception', "Unknown route 'get'");
		$this->router->url('get');
	}

	public function testUrlGetsNamesFromCache() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$driver->expects($this->exactly(1))
			   ->method('fetch')
			   ->with(Router::CACHE_KEY_NAMES)
			   ->will($this->returnValue(array('get' => '/get/:id')));
		$this->router->setCache($driver);
		$this->assertSame('http://myapp.local/get/42', $this->router->url('get', array('id' => 42)));
	}

	public function testUrlThrowsExceptionWithInvalidCache() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$driver->expects($this->exactly(1))
			   ->method('fetch')
			   ->with(Router::CACHE_KEY_NAMES)
			   ->will($this->returnValue('foo'));
		$this->router->setCache($driver);
		$this->setExpectedException('\Exception', 'Cache value \'Router.names\' is not an array');
		$this->router->url('get');
	}

	public function testUrlThrowsExceptionWithCacheMiss() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$driver->expects($this->exactly(1))
			   ->method('fetch')
			   ->with(Router::CACHE_KEY_NAMES);
		$this->router->setCache($driver);
		$this->setExpectedException('\Exception', 'No named routes defined');
		$this->router->url('get');
	}

	public function testNamedRouteInModuleHasPrefix() {
		//the second route in etc/routes.php sets a name of 'secret'
		$this->setUpTestModule('foo');
		$this->router->routeModule('foo', 'my-module');
		$names = array('foo:secret' => '/my-module/secret');
		$this->assertSame($names, $this->router->getNames());
		$routes = $this->router->getRoutes();
		$secret = $routes[1];
		$action = array('::foo.controller.bar', 'secretArea', array());
		$this->assertSame($action, $this->match('/my-module/secret'));
	}

    public function testAddModuleDoesNotAffectFutureNames() {
        $this->setUpTestModule('bar');
        $this->router->routeModule('bar');
        $this->router->name('hello')->route('foo');
        $names = array(
            'bar:secret' => '/bar/secret',
            'hello' => '/foo',
        );
        $this->assertSame($names, $this->router->getNames());
    }

    public function testMatchCached()
    {
        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        $route = array('::controller.foo', 'index', array());
        $cache->expects($this->once())
              ->method('fetch')
              ->with('Router./fooGET')
              ->will($this->returnValue($route));
        $cache->expects($this->never())
              ->method('save');
        $this->router->setCache($cache);
        $request = Request::create('/foo');
        $this->assertSame($route, $this->router->matchCached($request));
    }

    public function testMatchCachedThrowsExceptionOnInvalidCache()
    {
        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        $route = array('::controller.foo', 'index', array());
        $cache->expects($this->once())
              ->method('fetch')
              ->with('Router./fooGET')
              ->will($this->returnValue('foo'));
        $this->router->setCache($cache);
        $request = Request::create('/foo');
        $this->setExpectedException('\Exception');
        $this->router->matchCached($request);
    }

    public function testRouteIsCachedOnSuccess()
    {
        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        $expected = array('module:controller', 'index', array());
        $cache->expects($this->exactly(2))
               ->method('save');
        $this->router->setCache($cache);
        $this->router->route('/test', 'module:controller', 'index');
        $this->assertSame($expected, $this->match('/test'));
    }

}
