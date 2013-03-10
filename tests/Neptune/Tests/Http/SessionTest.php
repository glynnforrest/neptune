<?php
namespace Neptune\Http;

use Neptune\Http\Session;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * SessionTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class SessionTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$_SESSION = array();
	}

	public function tearDown() {
		unset($_SESSION);
	}

	public function testEmptyGet() {
		$this->assertNull(Session::get('key'));
	}

	public function testGet() {
		$_SESSION['key'] = 'value';
		$this->assertEquals('value', Session::get('key'));
		$_SESSION['array'] = array();
		$this->assertEquals(array(), Session::get('array'));
		$class = new \stdClass();
		$_SESSION['class'] = $class;
		$this->assertSame($class, Session::get('class'));
	}

	public function testSet() {
		Session::set('key', 'value');
		$this->assertEquals('value', $_SESSION['key']);
		Session::set('array', array());
		$this->assertEquals(array(), $_SESSION['array']);
		$class = new \stdClass();
		Session::set('class', $class);
		$this->assertSame($class, $_SESSION['class']);
	}

	public function testSetNull() {
		Session::set('key');
		$this->assertTrue(array_key_exists('key', $_SESSION));
		$this->assertNull($_SESSION['key']);
	}

	public function testSetArray() {
		Session::set(array('zero', 'one'));
		$this->assertEquals('zero', $_SESSION[0]);
		$this->assertEquals('one', $_SESSION[1]);
		Session::set(array('key' => 'value', 'test' => 'passed'));
		$this->assertEquals('value', $_SESSION['key']);
		$this->assertEquals('passed', $_SESSION['test']);
	}

	public function testToken() {
		$this->assertTrue(strlen(Session::token()) === 32);
		$this->assertTrue(array_key_exists('csrf_token', $_SESSION));
	}

	public function testTokenPersists() {
		$token = Session::token();
		$this->assertEquals($token, Session::token());
	}
}
?>
