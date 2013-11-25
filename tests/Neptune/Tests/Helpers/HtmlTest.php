<?php

namespace Neptune\Tests\Helpers;

use Neptune\Core\Config;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * HtmlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class HtmlTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('testing');
		$c->set('root_url', 'myapp.local/');
	}

	public function tearDown() {
		Config::unload();
	}

	public function testJs() {
		$this->assertEquals('<script type="text/javascript" src="http://myapp.local/js/test.js"></script>' . PHP_EOL, Html::js('js/test.js'));
	}

	public function testJsOptions() {
		$this->assertEquals('<script type="text/javascript" src="http://myapp.local/js/test.js" id="my_script" class="script"></script>' . PHP_EOL, Html::js('js/test.js', array(
		'id' => 'my_script', 'class' => 'script')));
	}

	public function testJsAbsolute() {
		$this->assertEquals('<script type="text/javascript" src="http://site.com/js/test.js"></script>' . PHP_EOL, Html::js('http://site.com/js/test.js'));
	}

	public function testCss() {
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL, Html::css('css/style.css'));
	}

	public function testCssOptions() {
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" id="my_style" class="style" />' . PHP_EOL, Html::css('css/style.css', array(
		'id' => 'my_style', 'class' => 'style')));
	}

	public function testCssAbsolute() {
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://site.com/css/style.css" />' . PHP_EOL, Html::css('http://site.com/css/style.css'));
	}

	public function testEscape() {
		$this->assertEquals('&lt;p&gt;Paragraph&lt;/p&gt;', Html::escape('<p>Paragraph</p>'));
	}

	public function testOpenTag() {
		$this->assertEquals('<p>', Html::openTag('p'));
		$this->assertEquals('<p class="text">',
		Html::openTag('p', array('class' => 'text')));
		$this->assertEquals('<p class="text" id="paragraph5">',
		Html::openTag('p', array('class' => 'text', 'id' => 'paragraph5')));
	}

	public function testCloseTag() {
		$this->assertEquals('</p>', Html::closeTag('p'));
	}

	public function testTag() {
		$this->assertEquals('<p></p>', Html::tag('p'));
		$this->assertEquals('<p>Hello world</p>',
		Html::tag('p', 'Hello world'));
		$this->assertEquals('<p class="text" id="something">Hello world</p>',
		Html::tag('p', 'Hello world', array('class' => 'text', 'id' => 'something')));
	}

	public function testSelfTag() {
		$this->assertEquals('<input />', Html::selfTag('input'));
		$this->assertEquals('<input type="checkbox" checked="checked" />',
		Html::selfTag('input', array('type' => 'checkbox', 'checked')));
	}

	public function testInput() {
		$expected = '<input type="text" name="test" value="" />';
		$this->assertEquals($expected, Html::input('text', 'test'));
		$expected = '<input type="text" name="test" value="foo" />';
		$this->assertEquals($expected, Html::input('text', 'test', 'foo'));
		$expected = '<input type="text" name="test" value="foo" id="test" />';
		$this->assertEquals($expected, Html::input('text', 'test', 'foo', array('id' => 'test')));
	}

	public function testInputPassword() {
		$expected = '<input type="password" name="pword" value="" />';
		$this->assertEquals($expected, Html::input('password', 'pword', 'secret'));
		$expected = '<input type="password" name="pword" value="" id="pword" />';
		$this->assertEquals($expected, Html::input('password', 'pword', 'secret', array('id' => 'pword')));
	}

	public function testInputTextarea() {
		$expected = '<textarea name="comment"></textarea>';
		$this->assertEquals($expected, Html::input('textarea', 'comment'));
		$expected = '<textarea name="comment" id="comment">Something</textarea>';
		$this->assertEquals($expected, Html::input('textarea', 'comment', 'Something', array('id' => 'comment')));
	}

	public function testInputToken() {
		$_SESSION['csrf_token'] = md5('token');
		$this->assertEquals('<input type="hidden" name="csrf_token" value="94a08da1fecbb6e8b46990538c7b50b2" />', Html::inputToken());
	}

	public function testOptionsThrowsExceptionForBadOptions() {
		$this->setExpectedException('\Exception');
		Html::options(null);
	}

	public function testLabel() {
		$expected = '<label for="username" id="username">Username</label>';
		$this->assertSame($expected, Html::label('username', 'Username'));
	}

	public function testLabelOverrideOptions() {
		$expected = '<label for="username1" id="some-id-1">Username</label>';
		$this->assertSame($expected, Html::label('username', 'Username', array(
			'id' => 'some-id-1',
			'for' => 'username1'
		)));
	}

}
