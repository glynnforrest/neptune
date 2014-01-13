<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ControllerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ControllerTest extends \PHPUnit_Framework_TestCase {

	protected $obj;

	public function setUp() {
		$this->request = new Request();
		$this->obj = new FooController($this->request);
	}

	public function testMethodIsCalled() {
		$this->assertSame('Foo', $this->obj->runMethod('foo'));
	}

	protected function getMockFoo() {
		return $this->getMockBuilder('\Neptune\Tests\Controller\FooController')
					->setMethods(array('before'))
					->setConstructorArgs(array($this->request))
					->getMock();
	}

	public function testBeforeIsCalled() {
		$mock = $this->getMockFoo();
		$mock->expects($this->once())
			 ->method('before')
			 ->will($this->returnValue(true));
		$this->assertSame('Foo', $mock->runMethod('foo'));
		$this->assertSame('Foo', $mock->runMethod('foo'));
		$this->assertSame('Foo', $mock->runMethod('foo'));
	}

	public function testBeforeFalse() {
		$mock = $this->getMockFoo();
		$mock->expects($this->once())
			 ->method('before')
			 ->will($this->returnValue(false));
		$this->assertFalse($mock->runMethod('actionFoo'));
	}

	public function testUnknownMethodThrowsException() {
		$this->setExpectedException('\\Neptune\\Exceptions\\MethodNotFoundException');
		$this->obj->actionUnknown();
	}

	public function testUnknownRunMethodThrowsException() {
		$this->setExpectedException('\\Neptune\\Exceptions\\MethodNotFoundException');
		$this->obj->runMethod('actionUnknown');
	}

}
