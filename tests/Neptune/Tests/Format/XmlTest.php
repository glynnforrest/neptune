<?php

namespace Neptune\Format;

use Neptune\Format\Xml;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * XmlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class XmlTest extends \PHPUnit_Framework_TestCase {

	protected $header = '<?xml version="1.0" encoding="UTF-8"?>
';

	public function testAddStrings() {
		$f = Xml::create('test');
		$this->assertEquals($this->header . '<xml><node>test</node></xml>', $f->encode());
		$f->add('foo');
		$this->assertEquals($this->header . '<xml><node>test</node><node>foo</node></xml>', $f->encode());
		$f->add(array('bar', 'baz'));
		$this->assertEquals($this->header . '<xml><node>test</node><node>foo</node><node><node>bar</node><node>baz</node></node></xml>', $f->encode());
		$f = Xml::create();
		$f->add(array('bar', 'baz', array('key' => 'value')));
		$this->assertEquals($this->header . '<xml><node>bar</node><node>baz</node><node><key>value</key></node></xml>', $f->encode());
	}

	public function testAddNamedStrings() {
		$f = Xml::create('value', 'tag');
		$this->assertEquals($this->header . '<xml><tag>value</tag></xml>', $f->encode());
		$f->add('foo', 'key');
		$this->assertEquals($this->header . '<xml><tag>value</tag><key>foo</key></xml>', $f->encode());
		$f->add(array('bar', 'baz'), 'values');
		$this->assertEquals($this->header . '<xml><tag>value</tag><key>foo</key><values><node>bar</node><node>baz</node></values></xml>', $f->encode());
		$f = Xml::create();
		$f->add(array('bar', 'baz', array('key' => 'value')), 'results');
		$this->assertEquals($this->header . '<xml><node>bar</node><node>baz</node><node><key>value</key></node></xml>', $f->encode());
	}
}
?>
