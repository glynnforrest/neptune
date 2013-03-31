<?php

namespace Neptune\Tests\Http;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Http\Request;

/**
 * RequestTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase {

	protected $request;

	public function setUp() {
		$this->request = Request::getInstance();
	}

	protected function reset() {
		$this->request->resetStoredVars();
	}

	public function testUri() {
		$this->assertNull($this->request->uri());
		$_SERVER['REQUEST_URI'] = 'test';
		$this->assertEquals('test', $this->request->uri());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.xml';
		$this->assertEquals('test.xml', $this->request->uri());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test?foo=bar';
		$this->assertEquals('test', $this->request->uri());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test?foo=b.ar';
		$this->assertEquals('test', $this->request->uri());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.xml?foo=bar';
		$this->assertEquals('test.xml', $this->request->uri());
		$this->reset();
	}

	public function testFormat() {
		$_SERVER['REQUEST_URI'] = 'test';
		$this->assertEquals('html', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.';
		$this->assertEquals('html', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.json';
		$this->assertEquals('json', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 't.e.s.t.json';
		$this->assertEquals('json', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test?foo=bar';
		$this->assertEquals('html', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.xml?foo=bar';
		$this->assertEquals('xml', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.csv?foo=b?ar';
		$this->assertEquals('csv', $this->request->format());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.htm?foo=b.ar';
		$this->assertEquals('htm', $this->request->format());
		$this->reset();
	}

	public function testPath() {
		$_SERVER['REQUEST_URI'] = 'test';
		$this->assertEquals('test', $this->request->path());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.xml';
		$this->assertEquals('test', $this->request->path());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test?foo=bar';
		$this->assertEquals('test', $this->request->path());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.xml?foo=bar';
		$this->assertEquals('test', $this->request->path());
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'test.again.and.again.xml?f00=ok';
		$this->assertEquals('test.again.and.again', $this->request->path());
		$this->reset();
	}

	public function testGet() {
		$_GET['f00'] = 'ok';
		$this->assertEquals(array('f00' => 'ok'), $this->request->get());
		$this->assertEquals('ok', $this->request->get('f00'));
		$this->assertNull($this->request->get('empty'));
		unset($_GET['f00']);
		$this->assertNull($this->request->get('f00'));
        $this->assertEquals('fallback', $this->request->get('f00', 'fallback'));
	}

	public function testPost() {
		$_POST['f00'] = 'ok';
		$this->assertEquals(array('f00' => 'ok'), $this->request->post());
		$this->assertEquals('ok', $this->request->post('f00'));
		$this->assertNull($this->request->post('empty'));
		unset($_POST['f00']);
		$this->assertNull($this->request->post('f00'));
        $this->assertEquals('fallback', $this->request->post('f00', 'fallback'));
	}

	public function testIp() {
		$this->assertNull($this->request->ip());
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->assertEquals('127.0.0.1', $this->request->ip());
	}

	public function testMethod() {
		$this->assertNull($this->request->ip());
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this->assertEquals('POST', $this->request->method());
	}

	public function testIsPost() {
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this->assertTrue($this->request->isPost());
		$_SERVER['REQUEST_METHOD'] = 'get';
		$this->assertFalse($this->request->isPost());
		$_SERVER['REQUEST_METHOD'] =null;
		$this->assertFalse($this->request->isPost());
	}

	public function testIsGet() {
		$_SERVER['REQUEST_METHOD'] = 'get';
		$this->assertTrue($this->request->isGet());
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this->assertFalse($this->request->isGet());
		$_SERVER['REQUEST_METHOD'] =null;
		$this->assertFalse($this->request->isGet());
	}

}
