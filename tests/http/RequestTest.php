<?php

namespace neptune\http;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * RequestTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase {

	public function testUri() {
		$this->assertNull(Request::uri());
		$_SERVER['REQUEST_URI'] = 'test';
		$this->assertEquals('test', Request::uri());
		$_SERVER['REQUEST_URI'] = 'test.xml';
		$this->assertEquals('test.xml', Request::uri());
		$_SERVER['REQUEST_URI'] = 'test?foo=bar';
		$this->assertEquals('test', Request::uri());
		$_SERVER['REQUEST_URI'] = 'test?foo=b.ar';
		$this->assertEquals('test', Request::uri());
		$_SERVER['REQUEST_URI'] = 'test.xml?foo=bar';
		$this->assertEquals('test.xml', Request::uri());
	}

	public function testFormat() {
		$_SERVER['REQUEST_URI'] = 'test';
		$this->assertEquals('html', Request::format());
		$_SERVER['REQUEST_URI'] = 'test.json';
		$this->assertEquals('json', Request::format());
		$_SERVER['REQUEST_URI'] = 't.e.s.t.json';
		$this->assertEquals('json', Request::format());
		$_SERVER['REQUEST_URI'] = 'test?foo=bar';
		$this->assertEquals('html', Request::format());
		$_SERVER['REQUEST_URI'] = 'test.xml?foo=bar';
		$this->assertEquals('xml', Request::format());
		$_SERVER['REQUEST_URI'] = 'test.csv?foo=b?ar';
		$this->assertEquals('csv', Request::format());
		$_SERVER['REQUEST_URI'] = 'test.htm?foo=b.ar';
		$this->assertEquals('htm', Request::format());
	}

	public function testPath() {
		$_SERVER['REQUEST_URI'] = 'test';
		$this->assertEquals('test', Request::path());
		$_SERVER['REQUEST_URI'] = 'test.xml';
		$this->assertEquals('test', Request::path());
		$_SERVER['REQUEST_URI'] = 'test?foo=bar';
		$this->assertEquals('test', Request::path());
		$_SERVER['REQUEST_URI'] = 'test.xml?foo=bar';
		$this->assertEquals('test', Request::path());
		$_SERVER['REQUEST_URI'] = 'test.again.and.again.xml?f00=ok';
		$this->assertEquals('test.again.and.again', Request::path());
	}

	public function testGet() {
		$_GET['f00'] = 'ok';
		$this->assertEquals(array('f00' => 'ok'), Request::get());
		$this->assertEquals('ok', Request::get('f00'));
		$this->assertNull(Request::get('empty'));
		unset($_GET['f00']);
		$this->assertNull(Request::get('f00'));
	}

	public function testIp() {
		$this->assertNull(Request::ip());
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->assertEquals('127.0.0.1', Request::ip());
	}

	public function testMethod() {
		$this->assertNull(Request::ip());
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertEquals('POST', Request::method());
	}
}

?>
