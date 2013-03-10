<?php

namespace Neptune\Core;

use Neptune\Core\Route;

include dirname(__FILE__) . ('/../test_bootstrap.php');

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

}

?>
