<?php

namespace Neptune\Tests\Routing;

use Neptune\Core\Config;
use Neptune\Routing\Dispatcher;
use Neptune\Routing\Route;
use Neptune\Cache\Driver\DebugDriver;

include __DIR__ . ('/../../../bootstrap.php');

/**
 * DispatcherTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->config = Config::create('neptune');
		$this->config->set('root_url', 'myapp.local/');
		$this->router = new Dispatcher($this->config);
	}

	public function tearDown() {
		Config::unload();
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
		$this->router->globals()->transforms('controller', function($controller) {
		return ucfirst($controller) . 'Controller';
	});
		$r = $this->router->route('/foo', 'foo', 'index');
		$r->test('/foo');
		$this->assertSame(array('FooController', 'index', array()), $r->getAction());
	}

	public function testRouteAssets() {
		$this->config->set('assets.url', '/assets/');
		$r = $this->router->routeAssets();
		$this->assertSame('/assets/:args', $r->getUrl());
		$this->assertTrue($r->test('/assets/css/test'));
		$expected = array(
			'Neptune\Controller\AssetsController',
			'serveAsset',
			array('css/test'));
		$this->assertSame($expected, $r->getAction());
		//assert the route is given a name
		$names = array('neptune.assets' => '/assets/:args');
		$this->assertSame($names, $this->router->getNames());
	}

	public function testRouteAssetsMissingSlashes() {
		$this->config->set('assets.url', 'assets');
		$r = $this->router->routeAssets();
		$this->assertSame('/assets/:args', $r->getUrl());
		$this->assertTrue($r->test('/assets/lib/js/test'));
		$expected = array(
			'Neptune\Controller\AssetsController',
			'serveAsset',
			array('lib/js/test')
		);
		$this->assertSame($expected, $r->getAction());
	}

	public function testMatch() {
		$this->router->route('/test', 'test', 'index');
		$expected = array('test', 'index', array());
		$this->assertSame($expected, $this->router->match('/test'));
	}

	public function testMatchThrowsExceptionNoAction() {

	}

	public function testSetPrefix() {
		$this->router->setPrefix('admin');
		$this->assertSame('admin', $this->router->getPrefix());
	}

	public function testSetPrefixRemovesSlashes() {
		$this->router->setPrefix('one/');
		$this->assertSame('one', $this->router->getPrefix());
		$this->router->setPrefix('/two');
		$this->assertSame('two', $this->router->getPrefix());
		$this->router->setPrefix('/three/');
		$this->assertSame('three', $this->router->getPrefix());
	}

	public function testPrefixIsAppliedToRoutes() {
		$this->router->setPrefix('admin/');
		$route = $this->router->route(':prefix/login');
		$this->assertSame('/admin/login', $route->getUrl());
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
		$this->assertTrue($route->test('/foo/login'));
		$action = $route->getAction();
		$this->assertSame('foo_module_controller', $action[0]);
	}

	public function testAddModuleDoesNotChangeGlobals() {
		$this->setUpTestModule('bar');
		$before = $this->router->globals()->controller('foo');
		$this->router->routeModule('bar');
		$after = $this->router->globals();
		$this->assertSame($before, $after);
		$route = $this->router->route('.*', null, 'some_method');
		$this->assertTrue($route->test('anything'));
		$action = $route->getAction();
		$this->assertSame('foo', $action[0]);
	}

	public function testAddModuleDefinesNewGlobals() {
		$this->setUpTestModule('admin');
		$this->router->globals()->args(array('globals' => 'very_yes'));
		$this->router->routeModule('admin');
		$routes = $this->router->getRoutes();
		$route = $routes[0];
		$route->test('/admin/login');
		$action = $route->getAction();
		$args = $action[2];
		$this->assertSame(array(), $args);
	}

	public function testAddModuleDoesNotChangePrefix() {
		$this->setUpTestModule('bar');
		$this->router->setPrefix('prefix_before');
		$this->router->routeModule('bar');
		$this->assertSame('prefix_before', $this->router->getPrefix());
	}

	public function testGetAndSetCacheDriver() {
		$driver = new DebugDriver('testing_');
		$this->router->setCacheDriver($driver);
		$this->assertSame($driver, $this->router->getCacheDriver());
	}

	public function testRouteIsCachedOnSuccess() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->exactly(2))
			   ->method('set');
		$this->router->setCacheDriver($driver);
		$this->router->route('/test', 'module:controller', 'index');
		$expected = array('module:controller', 'index', array());
		$this->assertSame($expected, $this->router->match('/test'));
	}

	public function testName() {
		$this->assertInstanceOf('\Neptune\Routing\Dispatcher', $this->router->name('route'));
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
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->exactly(1))
			   ->method('get')
			   ->with(Dispatcher::CACHE_KEY_NAMES)
			   ->will($this->returnValue(array('get' => '/get/:id')));
		$this->router->setCacheDriver($driver);
		$this->assertSame('http://myapp.local/get/42', $this->router->url('get', array('id' => 42)));
	}

	public function testUrlThrowsExceptionWithInvalidCache() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->exactly(1))
			   ->method('get')
			   ->with(Dispatcher::CACHE_KEY_NAMES)
			   ->will($this->returnValue('foo'));
		$this->router->setCacheDriver($driver);
		$this->setExpectedException('\Exception', 'Cache value \'Router.names\' is not an array');
		$this->router->url('get');
	}

	public function testUrlThrowsExceptionWithCacheMiss() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->exactly(1))
			   ->method('get')
			   ->with(Dispatcher::CACHE_KEY_NAMES);
		$this->router->setCacheDriver($driver);
		$this->setExpectedException('\Exception', 'No named routes defined');
		$this->router->url('get');
	}

}
