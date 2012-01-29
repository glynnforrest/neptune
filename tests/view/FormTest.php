<?php

namespace neptune\view;

use neptune\view\Form;
use neptune\core\Config;
use neptune\helpers\Html;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * FormTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FormTest extends \PHPUnit_Framework_TestCase {
	const file = '/tmp/formtest.php';
	const view = '/tmp/formtest';

	public function setUp() {
		touch(self::file);
		$content = '<?php';
		$content .= <<<END
		echo 'testing';
END;
		$content .= '?>';
		file_put_contents(self::file, $content);
		Config::bluff('view');
		Config::set('view_dir', '/tmp');
	}

	public function tearDown() {
		unlink(self::file);
		Config::unload();
	}

	public function testCreate() {
		$v = Form::create('url');
		$this->assertTrue($v instanceof Form);
	}	

	public function testRowHiddenField() {
		$v = Form::create('url');
		$v->add('hidden', 'name');
		$this->assertEquals(Html::input('hidden', 'name'), $v->row('name'));
	}

	public function testCreateSimpleForm() {
		$v = Form::create('/post/url');
		$v->add('text', 'name');
		$expected = Html::openTag('form', array('action' => '/post/url', 'method' => 'post'));
		$expected .= '<ul><li><label for="name">Name</label>';
		$expected .= Html::input('text', 'name');
		$expected .= '</li></ul>';
		$expected .= '</form>';
		$this->assertEquals($expected, $v->render());
	}

	public function testSetAndGet() {
		$v = Form::create('some/url');
		$v->key = 'value';
		$this->assertEquals('value', $v->key);
		$v->arr = array();
		$this->assertEquals(array(), $v->arr);
		$obj = new \stdClass();
		$v->obj = $obj;
		$this->assertEquals($obj, $v->obj);
	}

	public function testIsset() {
		$v = Form::create('/url');
		$v->set = 'value';
		$this->assertTrue(isset($v->set));
		$this->assertFalse(isset($v->unset));
	}

	public function testRenderException() {
		$v = Form::load('some/file');
		$this->setExpectedException('neptune\\exceptions\\ViewNotFoundException');
		$v->render();
	}

	public function testRenderAbsolutePath() {
		$v = Form::loadAbsolute(self::view);
		$this->assertEquals('testing', $v->render());
	}

	public function testFormVarIsNotOverridden() {
		$v = Form::loadAbsolute(self::view);
		$v->file = 'foo';
		$this->assertEquals('foo', $v->file);
		$this->assertEquals('testing', $v->render());
	}

	public function testCreateFromArray() {
		$values = array('name' => 'foo', 'age' => 100);
		$v = Form::create('/url')->set($values);
		$expected = Html::openTag('form', array('action' => '/url', 'method' => 'post'));
		$expected .= '<ul><li><label for="name">Name</label>';
		$expected .= Html::input('text', 'name', 'foo');
		$expected .= '</li>';
		$expected .= '<li><label for="age">Age</label>';
		$expected .= Html::input('text', 'age', 100);
		$expected .= '</li></ul>';
		$expected .= '</form>';
		$this->assertEquals($expected, $v->render());
	}

	public function setChangeType() {
		$v = Form::create('/url')->set(array('pass' => 'secret'));
		$this->assertEquals(Html::input('text', 'pass', 'secret'), $v->input('pass'));
		$v->setType('pass', 'password');
		$this->assertEquals(Html::input('password', 'pass', 'secret'), $v->input('pass'));
	}

	public function testRowUndefinedVar() {
		$v = Form::create('/url');
		$this->assertNull($v->row('name'));
		$v->name = 'value';
		$this->assertEquals(Html::input('text', 'name', 'value'), $v->input('name'));
	}

}
?>
