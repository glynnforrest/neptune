<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Config;
use Neptune\Core\Dispatcher;
use Neptune\Core\Route;

include __DIR__ . ('/../../../bootstrap.php');

/**
 * DispatcherTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Dispatcher::getInstance()->clearRoutes()->clearGlobals();
	}

	public function testRouteReturnsRoute() {
		$r = Dispatcher::getInstance()->route('/url');
		$this->assertTrue($r instanceof Route);
	}

	public function testGlobalsReturnsRoute() {
		$r = Dispatcher::getInstance()->globals();
		$this->assertTrue($r instanceof Route);
	}

	public function testCatchAllReturnRoute() {
		$r = Dispatcher::getInstance()->catchAll('foo');
		$this->assertTrue($r instanceof Route);
		$this->assertEquals('.*', $r->getUrl());
	}

	public function testMissingSlash() {
		$r = Dispatcher::getInstance()->route('test');
		$this->assertEquals('/test', $r->getUrl());
	}

	public function testRouteInheritsGlobals() {
		$d = Dispatcher::getInstance();
		$d->globals()->transforms('controller', function($controller) {
		return ucfirst($controller) . 'Controller';
	});
		$r = $d->route('/foo', 'foo', 'index');
		$r->test('/foo');
		$this->assertEquals(array('FooController', 'index', array()), $r->getAction());
	}

	public function testRouteAssets() {
		$d = Dispatcher::getInstance();
		$c = Config::create('neptune');
		$c->set('assets.url', '/assets/');
		$r = $d->routeAssets();
		$this->assertEquals('/assets/:args', $r->getUrl());
		$this->assertTrue($r->test('/assets/css/test'));
		$this->assertEquals(
			array(
				'Neptune\Controller\AssetsController',
				'serveAsset',
				array('css/test')
			),
			$r->getAction());
		Config::unload();
	}

	public function testRouteAssetsMissingSlashes() {
		$d = Dispatcher::getInstance();
		$c = Config::create('neptune');
		$c->set('assets.url', '/assets/');
		$r = $d->routeAssets();
		$this->assertEquals('/assets/:args', $r->getUrl());
		$this->assertTrue($r->test('/assets/lib/js/test'));
		$this->assertEquals(
			array(
				'Neptune\Controller\AssetsController',
				'serveAsset',
				array('lib/js/test')
			),
			$r->getAction());
		Config::unload();
	}

	public function testGoReturnsControllerResponse() {
		$d = Dispatcher::getInstance();
		$d->route('/test', '\\Neptune\\Tests\\Core\\TestController', 'index');
		$this->assertEquals('test route', $d->go('/test'));
	}

	//anything captured by output buffering should not be returned
	//but still available after the request
	public function testOtherContent() {
		$d = Dispatcher::getInstance();
		$d->route('/test', '\\Neptune\\Tests\\Core\\TestController', 'withEcho');
		$this->assertEquals('return value', $d->go('/test'));
		$this->assertEquals('hello from echo', $d->getOther());
	}

	//if no response is provided, output buffered content will be used
	public function testEchoWhenNoControllerResponse() {
		$d = Dispatcher::getInstance();
		$d->route('/test', '\\Neptune\\Tests\\Core\\TestController', 'echos');
		$this->assertEquals('testing', $d->go('/test'));
	}

	public function testNoResponse() {
		$d = Dispatcher::getInstance();
		$d->route('/test', '\\Neptune\\Tests\\Core\\TestController', 'nothing');
		$this->assertFalse($d->go('/test'));
	}

	public function testSetPrefix() {
		$d = Dispatcher::getInstance();
		$d->setPrefix('admin');
		$this->assertEquals('admin', $d->getPrefix());
	}

	public function testSetPrefixRemovesSlashes() {
		$d = Dispatcher::getInstance();
		$d->setPrefix('one/');
		$this->assertEquals('one', $d->getPrefix());
		$d->setPrefix('/two');
		$this->assertEquals('two', $d->getPrefix());
		$d->setPrefix('/three/');
		$this->assertEquals('three', $d->getPrefix());
	}

	public function testPrefixIsAppliedToRoutes() {
		$d = Dispatcher::getInstance();
		$d->setPrefix('admin/');
		$route = $d->route(':prefix/login');
		$this->assertEquals('/admin/login', $route->getUrl());
	}

	public function testGetRoutes() {
		$d = Dispatcher::getInstance();
		$d->catchAll('foo');
		$routes = $d->getRoutes();
		$this->assertTrue($routes[0] instanceof Route);
		$this->assertEquals('.*', $routes[0]->getUrl());
	}

	protected function setupTestModule($name) {
		//a simple routes.php is in the etc/ directory
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', __DIR__ . '/');
		$neptune->set('modules.' . $name, 'etc/');
	}

	public function testAddModuleSetsPrefix() {
		$d = Dispatcher::getInstance();
		$this->setupTestModule('foo');
		//routes.php defines a route with '/:prefix/login
		$d->routeModule('foo');
		$routes = $d->getRoutes();
		$this->assertTrue($routes[0] instanceof Route);
		$this->assertEquals('/foo/login', $routes[0]->getUrl());
	}

	public function testAddModuleSetsDefinedPrefix() {
		$d = Dispatcher::getInstance();
		$this->setupTestModule('foo');
		$d->routeModule('foo', 'different_prefix');
		$routes = $d->getRoutes();
		$this->assertTrue($routes[0] instanceof Route);
		$this->assertEquals('/different_prefix/login', $routes[0]->getUrl());
	}

	public function testAddModuleHasLocalGlobals() {
		$d = Dispatcher::getInstance();
		$this->setupTestModule('foo');
		$d->routeModule('foo');
		$routes = $d->getRoutes();
		$route = $routes[0];
		$this->assertTrue($route->test('/foo/login'));
		$action = $route->getAction();
		$this->assertEquals('foo_module_controller', $action[0]);
	}

	public function testAddModuleDoesNotChangeGlobals() {
		$d = Dispatcher::getInstance();
		$this->setupTestModule('bar');
		$before = $d->globals()->controller('foo');
		$d->routeModule('bar');
		$after = $d->globals();
		$this->assertSame($before, $after);
		$route = $d->route('.*', null, 'some_method');
		$this->assertTrue($route->test('anything'));
		$action = $route->getAction();
		$this->assertSame('foo', $action[0]);
	}

	public function testAddModuleDefinesNewGlobals() {
		$d = Dispatcher::getInstance();
		$this->setupTestModule('admin');
		$d->globals()->args(array('globals' => 'very_yes'));
		$d->routeModule('admin');
		$routes = $d->getRoutes();
		$route = $routes[0];
		$route->test('/admin/login');
		$action = $route->getAction();
		$args = $action[2];
		$this->assertEquals(array(), $args);
	}

	public function testAddModuleDoesNotChangePrefix() {
		$d = Dispatcher::getInstance();
		$this->setupTestModule('bar');
		$d->setPrefix('prefix_before');
		$d->routeModule('bar');
		$this->assertSame('prefix_before', $d->getPrefix());
	}

}
