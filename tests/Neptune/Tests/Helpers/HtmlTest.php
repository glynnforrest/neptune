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
		$this->assertSame('<script type="text/javascript" src="http://myapp.local/js/test.js"></script>' . PHP_EOL, Html::js('js/test.js'));
	}

	public function testJsOptions() {
		$this->assertSame('<script type="text/javascript" src="http://myapp.local/js/test.js" id="my_script" class="script"></script>' . PHP_EOL, Html::js('js/test.js', array(
		'id' => 'my_script', 'class' => 'script')));
	}

	public function testJsAbsolute() {
		$this->assertSame('<script type="text/javascript" src="http://site.com/js/test.js"></script>' . PHP_EOL, Html::js('http://site.com/js/test.js'));
	}

	public function testCss() {
		$this->assertSame('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL, Html::css('css/style.css'));
	}

	public function testCssOptions() {
		$this->assertSame('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" id="my_style" class="style" />' . PHP_EOL, Html::css('css/style.css', array(
		'id' => 'my_style', 'class' => 'style')));
	}

	public function testCssAbsolute() {
		$this->assertSame('<link rel="stylesheet" type="text/css" href="http://site.com/css/style.css" />' . PHP_EOL, Html::css('http://site.com/css/style.css'));
	}

	public function testEscape() {
		$this->assertSame('&lt;p&gt;Paragraph&lt;/p&gt;', Html::escape('<p>Paragraph</p>'));
	}

	public function testOpenTag() {
		$this->assertSame('<p>', Html::openTag('p'));
		$this->assertSame('<p class="text">',
		Html::openTag('p', array('class' => 'text')));
		$this->assertSame('<p class="text" id="paragraph5">',
		Html::openTag('p', array('class' => 'text', 'id' => 'paragraph5')));
	}

	public function testCloseTag() {
		$this->assertSame('</p>', Html::closeTag('p'));
	}

	public function testTag() {
		$this->assertSame('<p></p>', Html::tag('p'));
		$this->assertSame('<p>Hello world</p>',
		Html::tag('p', 'Hello world'));
		$this->assertSame('<p class="text" id="something">Hello world</p>',
		Html::tag('p', 'Hello world', array('class' => 'text', 'id' => 'something')));
	}

	public function testSelfTag() {
		$this->assertSame('<input />', Html::selfTag('input'));
		$this->assertSame('<input type="checkbox" checked="checked" />',
		Html::selfTag('input', array('type' => 'checkbox', 'checked')));
	}

	public function testInputText() {
		$expected = '<input type="text" id="test" name="test" value="" />';
		$this->assertSame($expected, Html::input('text', 'test'));
		$expected = '<input type="text" id="other-id" name="test" value="foo" class="text-input" />';
		$this->assertSame($expected, Html::input('text', 'test', 'foo', array('id' => 'other-id', 'class' => 'text-input')));
	}

	/**
	 * Passwords are not hidden by the Html. Use FormRow and Form to
	 * avoid self-foot-shooting.
	 */
	public function testInputPassword() {
		$expected = '<input type="password" id="pword" name="pword" value="secret" />';
		$this->assertSame($expected, Html::input('password', 'pword', 'secret'));
		$expected = '<input type="password" id="password" name="pword" value="secret" />';
		$this->assertSame($expected, Html::input('password', 'pword', 'secret', array('id' => 'password')));
	}

	public function testInputTextarea() {
		$expected = '<textarea id="comment" name="comment"></textarea>';
		$this->assertSame($expected, Html::input('textarea', 'comment'));
		$expected = '<textarea id="other-id" name="comment">Something</textarea>';
		$this->assertSame($expected, Html::input('textarea', 'comment', 'Something', array('id' => 'other-id')));
	}

	public function testOptionsThrowsExceptionForBadOptions() {
		$this->setExpectedException('\Exception');
		Html::options(null);
	}

	public function testLabel() {
		$expected = '<label for="username">Username</label>';
		$this->assertSame($expected, Html::label('username', 'Username'));
	}

	public function testLabelOverrideOptions() {
		$expected = '<label for="username1">Username</label>';
		$this->assertSame($expected, Html::label('username', 'Username', array(
			'for' => 'username1'
		)));
	}

	public function testDuplicateOptionsRemoved() {
		$expected = ' id="tick" name="tick" checked="checked"';
		$options = Html::options(array(
			'id' => 'foo',
			'name' => 'bar',
			'id' => 'tick',
			'name' => 'tick',
			'checked' => 'checked',
			'checked'
		));
		$this->assertSame($expected, $options);
	}

	public function testSelect() {
		$expected = '<select name="choice">';
		$expected .= '<option value="foo">Foo</option>';
		$expected .= '</select>';
		$this->assertSame($expected, Html::select('choice', array('Foo' => 'foo')));
	}

	public function testSelectWithOptions() {
		$expected = '<select name="choice">';
		$expected .= '<option value="foo">Foo</option>';
		$expected .= '<option value="bar" selected="selected">Bar</option>';
		$expected .= '</select>';
		$this->assertSame($expected, Html::select('choice', array('Foo' => 'foo', 'Bar' => 'bar'), 'bar'));
	}

}
