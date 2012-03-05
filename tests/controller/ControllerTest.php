<?php

namespace neptune\controller;

use neptune\controller\Controller;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

class SampleController extends Controller {

	public function _before() {
		$_GET['before_counter']++;
		if(!isset($_GET['return_false'])) {
			return true;
		}
	}

	public function method() {
		return 'Hello World';
	}

	public function _hidden() {
		return 'Hidden';
	}

}

class AnotherController extends Controller {

	public function go() {
		return true;
	}

}
/**
 * ControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ControllerTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$_GET['before_counter'] = 0;
	}

	public function testBeforeIsCalled() {
		$c = new SampleController();
		$c->_runMethod('method');
		$this->assertEquals(1, $_GET['before_counter']);
	}

	public function testBeforeIsCalledOnce() {
		$c = new SampleController();
		$c->_runMethod('method');
		$c->_runMethod('method');
		$this->assertEquals(1, $_GET['before_counter']);
	}

	public function testMethodIsCalled() {
		$c = new SampleController();
		$this->assertEquals('Hello World', $c->_runMethod('method'));
	}

	public function testAfterIsCalled() {

	}

	public function testMethodNotRunIfBeforeIsFalse() {
		$c = new SampleController();
		$_GET['return_false'] = true;
		$this->assertFalse($c->_runMethod('method'));
	}

	public function testUnknownMethodThrowsException() {
		$c = new SampleController();
		$this->setExpectedException('\\neptune\\exceptions\\MethodNotFoundException');
		$c->unknown();
		$this->setExpectedException('\\neptune\\exceptions\\MethodNotFoundException');
		$c->_runMethod('unknown');
	}

	public function testUnderScoreMethodNotCalled() {
		$c = new SampleController();
		$this->assertFalse($c->_runMethod('_security'));
		$this->assertFalse($c->_runMethod('_before'));
		$this->assertFalse($c->_runMethod('_after'));
		$this->assertFalse($c->_runMethod('_hidden'));
		$this->assertEquals('Hidden', $c->_hidden());
	}

	public function testRunMethodNotCalled() {
		$c = new SampleController();
		$this->assertFalse($c->_runMethod('_runMethod'));
	}

	public function testBeforeNotFoundExceptionCaught() {
		$c = new AnotherController();
		$this->assertTrue($c->_runMethod('go'));
		$this->setExpectedException('\\neptune\\exceptions\\MethodNotFoundException');
		$c->_before();
	}
	
}
?>
