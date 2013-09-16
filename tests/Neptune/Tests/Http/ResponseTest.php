<?php

namespace Neptune\Tests\Http;

use Neptune\Http\Response;
use Neptune\Http\Request;
use Neptune\View\View;
use Neptune\View\JsonView;

include __DIR__ . ('/../../../bootstrap.php');

/**
 * ResponseTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ResponseTest extends \PHPUnit_Framework_TestCase {

	protected $obj;

	public function setUp() {
		$this->obj = Response::getInstance();
	}

	public function testFormatView() {
		$content = View::loadAbsolute(null);
		$expected = View::loadAbsolute(null);
		$actual = $this->obj->formatBody($content);
		$this->assertEquals($expected, $actual);
	}

	public function testFormatString() {
		$content = 'test';
		$expected = 'test';
		$actual = $this->obj->formatBody($content);
		$this->assertEquals($expected, $actual);
	}

	public function testFormatStringToJson() {
		$content = 'foo';
		Request::getInstance()->setFormat('json');
		$expected = '["foo"]';
		$actual = $this->obj->formatBody($content);
		$this->assertEquals($expected, $actual->render());
	}

	public function testFormatViewToJson() {
		$content = View::loadAbsolute(null);
		$content->test = 'foo';
		Request::getInstance()->setFormat('json');
		$expected = JsonView::loadAbsolute(null);
		$expected->test = 'foo';
		$actual = $this->obj->formatBody($content);
		$this->assertEquals($expected, $actual);
		$expected_json = '{"test":"foo"}';
		$this->assertEquals($expected_json, $actual->render());
	}

}
