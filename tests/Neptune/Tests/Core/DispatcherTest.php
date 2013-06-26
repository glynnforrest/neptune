<?php

namespace Neptune\Tests\Core;


use Neptune\Core\Dispatcher;
use Neptune\Core\Route;

include __DIR__ . ('/../../../bootstrap.php');

/**
 * DispatcherTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Dispatcher::getInstance()->clearRoutes();
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
		$r = $d->routeAssets('/assets/');
		$this->assertEquals('/assets/:asset', $r->getUrl());
		$this->assertTrue($r->test('/assets/test'));
		$this->assertEquals(
			array(
				'Neptune\Controller\AssetsController',
				'serveAsset',
				array('asset' => 'test')
			),
			$r->getAction());
	}

	public function testRouteAssetsMissingSlashes() {
		$d = Dispatcher::getInstance();
		$r = $d->routeAssets('assets');
		$this->assertEquals('/assets/:asset', $r->getUrl());
		$this->assertTrue($r->test('/assets/test'));
		$this->assertEquals(
			array(
				'Neptune\Controller\AssetsController',
				'serveAsset',
				array('asset' => 'test')
			),
			$r->getAction());
	}

}
