<?php

namespace neptune\validate;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * ValidatorTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testRequired() {
		$v = new Validator(array('one' => 1, 'two' => array('hi')));
		$this->assertTrue($v->check('one', 'required'));
		$this->assertTrue($v->check('two', 'required'));
		$this->assertFalse($v->check('false', 'required'));
		$this->assertFalse($v->validate());
	}

	public function testValidateOptionalKey() {
		$v = new Validator(array('required' => 'foo', 'optional' => ''));
		$this->assertTrue($v->check('required', 'required|alpha'));
		$this->assertFalse($v->check('optional', 'alphanum'));
		$this->assertTrue($v->validate());
		$this->assertFalse($v->check('false', 'required'));
		$this->assertFalse($v->validate());
	}

	public function testValidateOptionalGiven() {
		$v = new Validator(array('required' => 'foo', 'optional' => 'given_but_fails'));
		$this->assertTrue($v->check('required', 'required|alpha'));
		$this->assertFalse($v->check('optional', 'alphanum'));
		$this->assertFalse($v->validate());
	}

	public function testInt() {
		$v = new Validator(array('a' => 300, 'b' => '300', 'c' => '4.4', 'd' => array()));
		$this->assertTrue($v->check('a', 'required|int'));
		$this->assertTrue($v->check('b', 'int'));
		$this->assertFalse($v->check('c', 'required|int'));
		$this->assertFalse($v->check('d', 'int'));
	}

	public function testNum() {
		$v = new Validator(array('a' => 300, 'b' => '30.0', 'c' => '4.4', 'd' => 'one', 'e' => new \stdClass()));
		$this->assertTrue($v->check('a', 'required|int|num'));
		$this->assertTrue($v->check('b', 'num'));
		$this->assertTrue($v->check('c', 'num'));
		$this->assertFalse($v->check('d', 'num'));
		$this->assertFalse($v->check('d', 'required|num'));
	}

	public function testHex() {
		$v = new Validator(array('a' => 123, 'b' => '456', 'c' => 'abef', 'd' => '012feba62',
						'e' => '172hge', 'f' => array(), 'g' => 'hello'));
		$this->assertFalse($v->check('a', 'hex'));
		$this->assertTrue($v->check('b', 'hex|required'));
		$this->assertTrue($v->check('c', 'required|hex'));
		$this->assertTrue($v->check('d', 'hex'));
		$this->assertFalse($v->check('e', 'hex'));
		$this->assertFalse($v->check('f', 'hex'));
		$this->assertFalse($v->check('g', 'hex'));
	}

	public function testAlpha() {
		$v = new Validator(array('a' => 1, 2 => 'abc', 'three' => 'sjhb347', 'four' => array(),
						'five' => '', 'six' => 'hf_jf', 'seven' => 'hello'));
		$this->assertFalse($v->check('a', 'alpha'));
		$this->assertTrue($v->check('2', 'required|alpha'));
		$this->assertFalse($v->check('three', 'alpha|required'));
		$this->assertFalse($v->check('four', 'alpha'));
		$this->assertFalse($v->check('five', 'alpha'));
		$this->assertFalse($v->check('six', 'alpha'));
		$this->assertTrue($v->check('seven', 'alpha'));
	}

	public function testAlphanum() {
		$v = new Validator(array('a' => '1', 2 => 'abc', 'three' => 'sjhb347', 'four' => array(),
						'five' => '', 'six' => 'hf_jf', 'seven' => 'hello'));
		$this->assertTrue($v->check('a', 'alphanum'));
		$this->assertTrue($v->check('2', 'required|alphanum'));
		$this->assertTrue($v->check('three', 'alphanum|required'));
		$this->assertFalse($v->check('four', 'alphanum'));
		$this->assertFalse($v->check('five', 'alphanum'));
		$this->assertFalse($v->check('six', 'alphanum'));
		$this->assertTrue($v->check('seven', 'alphanum'));
	}

	public function testAlphadash() {
		$v = new Validator(array('a' => '1-', 2 => 'a_bc', 'three' => 'sjhb347', 'four' => array(),
						'five' => '', 'six' => 'hf@jf', 'seven' => 'hello'));
		$this->assertTrue($v->check('a', 'alphadash'));
		$this->assertTrue($v->check('2', 'required|alphadash'));
		$this->assertTrue($v->check('three', 'alphadash|required'));
		$this->assertFalse($v->check('four', 'alphadash'));
		$this->assertFalse($v->check('five', 'alphadash'));
		$this->assertFalse($v->check('six', 'alphadash'));
		$this->assertTrue($v->check('seven', 'alphadash'));
	}

	public function testAlphaspace() {
		$v = new Validator(array('a' => '1', 2 => 'a_bc', 'three' => 'sjhb347', 'four' => array(),
						'five' => '', 'six' => 'hf jf', 'seven' => 'hello'));
		$this->assertTrue($v->check('a', 'alphaspace'));
		$this->assertFalse($v->check('2', 'required|alphaspace'));
		$this->assertTrue($v->check('three', 'alphaspace|required'));
		$this->assertFalse($v->check('four', 'alphaspace'));
		$this->assertFalse($v->check('five', 'alphaspace'));
		$this->assertTrue($v->check('six', 'alphaspace'));
		$this->assertTrue($v->check('seven', 'alphaspace'));
	}

	public function testAlphadashspace() {
		$v = new Validator(array('a' => '1', 2 => 'a_bc', 'three' => 'sjhb347', 'four' => array(),
						'five' => '', 'six' => 'hf jf', 'seven' => 'hello this is a sentence'));
		$this->assertTrue($v->check('a', 'alphadashspace'));
		$this->assertTrue($v->check('2', 'required|alphadashspace'));
		$this->assertTrue($v->check('three', 'alphadashspace|required'));
		$this->assertFalse($v->check('four', 'alphadashspace'));
		$this->assertFalse($v->check('five', 'alphadashspace'));
		$this->assertTrue($v->check('six', 'alphadashspace'));
		$this->assertTrue($v->check('seven', 'alphadashspace'));
	}

	public function testLength() {
		$v = new Validator(array('foo' => 'bar', 'two' => '1', 'three' => 'hi'));
		$this->assertTrue($v->check('foo', 'length:3'));
		$this->assertFalse($v->check('two', 'length:0'));
		$this->assertFalse($v->check('three', 'length'));
	}

	public function testMin() {
		$v = new Validator(array('foo' => 'bar', 'two' => '1', 'three' => 'hi'));
		$this->assertTrue($v->check('foo', 'min:3'));
		$this->assertFalse($v->check('two', 'min:4'));
	}

	public function testMax() {
		$v = new Validator(array('foo' => 'bar', 'two' => '1', 'three' => 'hi'));
		$this->assertFalse($v->check('foo', 'max:2'));
		$this->assertTrue($v->check('two', 'max:1'));
		$this->assertTrue($v->check('two', 'max:467'));
		$this->assertFalse($v->check('three', 'max'));
	}

	public function testBetween() {
		$v = new Validator(array('foo' => 'bar', 'two' => '1', 'three' => 'hello'));
		$this->assertTrue($v->check('foo', 'required|between:1,4'));
		$this->assertTrue($v->check('foo', 'between:3,4'));
		$this->assertFalse($v->check('two', 'between:3,6'));
		$this->assertFalse($v->check('three', 'between:0,4'));
		$this->assertFalse($v->check('foo', 'required|between,2'));
		$this->assertFalse($v->check('two', 'between:2'));
		$this->assertFalse($v->check('three', 'between'));
	}

	public function testEmail() {
		$v = new Validator(array('valid' => 'test@test.com', 'notvalid' => 'j@hdja.*8', 'fake' => 'skdj92'));
		$this->assertTrue($v->check('valid', 'email'));
		$this->assertTrue($v->check('valid', 'required|email'));
		$this->assertFalse($v->check('notvalid', 'required|email'));
		$this->assertFalse($v->check('fake', 'email'));
	}

	public function testMatches() {
		$v = new Validator(array('foo' => 'bar', 'abc' => 'bar', 'one' => 'two'));
		$this->assertTrue($v->check('foo', 'required|matches:abc'));
		$this->assertTrue($v->check('foo', 'matches:abc'));
		$this->assertFalse($v->check('foo', 'matches:one'));
		$this->assertFalse($v->check('foo', 'matches:null'));
		$this->assertFalse($v->check('foo', 'matches'));
	}

	public function testConstructRules() {
		$rules = array('one' => 'required', 'two' => 'required|int|num',
			 'three' => 'between:2,4|required|alphadash');
		$v = new Validator(array('one' => 'foo', 'two' => 345, 'three' => 'etc'), $rules);
		$this->assertTrue($v->validate());
		$rules = array('one' => 'required', 'two' => 'required|int|alpha',
			 'three' => 'between:2,4|required|alphadash');
		$v = new Validator(array('one' => 'foo', 'two' => 345, 'three' => 'etc'), $rules);
		$this->assertFalse($v->validate());
	}

	public function testUndefinedFilter() {
		$v = new Validator(array(1 => 1, 'two' => 'two'));
		$this->assertFalse($v->check(1, 'notmadeyet'));
		$this->assertFalse($v->validate());
		$this->assertFalse($v->check('two', 'required|alpha|notmadeyet'));
		$this->assertFalse($v->validate());
	}

	public function testValidateNoRules() {
		$v = new Validator(array('one' => 1));
		$this->assertFalse($v->validate());
	}

	public function testValidate() {
		$v = new Validator(array('one' => 1));
		$v->check('one', 'int|required');
		$this->assertTrue($v->validate());
		$v = new Validator(array('one' => 1));
		$v->check('one', 'required');
		$v->check('one', 'alpha');
		$this->assertFalse($v->validate());
		$v = new Validator(array('one' => 1));
		$v->check('one', 'notamethod');
		$this->assertFalse($v->validate());
	}

}

?>
