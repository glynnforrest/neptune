<?php

namespace Neptune\Format;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * JsonTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class JsonTest extends \PHPUnit_Framework_TestCase {

	public function testStrings() {
		$f = Json::create('test');
		$this->assertEquals('"test"', $f->encode());
		$f->add('foo');
		$this->assertEquals('["test","foo"]', $f->encode());
		$f->add(array('bar', 'baz'));
		$this->assertEquals('["test","foo",["bar","baz"]]', $f->encode());
		$f = Json::create();
		$f->add(array('bar', 'baz', array('key' => 'value')));
		$this->assertEquals('["bar","baz",{"key":"value"}]', $f->encode());
	}

	public function testEmpty() {
		$f = Json::create('test');
		$f->add('');
		$this->assertEquals('"test"', $f->encode());
		$f->add(null);
		$this->assertEquals('"test"', $f->encode());
		$f->add(false);
		$this->assertEquals('"test"', $f->encode());
		$f->add(0);
		$this->assertEquals('"test"', $f->encode());
		$f->add(array());
		$this->assertEquals('"test"', $f->encode());
	}

	public function testClear() {
		$f = Json::create();
		$f->add('text');
		$f->clear();
		$this->assertEquals('[]', $f->encode());
	}

}
?>
