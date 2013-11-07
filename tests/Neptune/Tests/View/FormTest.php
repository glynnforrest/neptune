<?php

namespace Neptune\Tests\View;

use Neptune\View\Form;
use Neptune\Core\Config;
use Neptune\Helpers\Html;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * FormTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FormTest extends \PHPUnit_Framework_TestCase {

	protected $file = 'formtest.php';
	protected $view = 'formtest';

	public function setUp() {
		$content = '<?php';
		$content .= <<<END
		echo 'testing';
END;
		$content .= '?>';
		$temp = Temping::getInstance();
		$temp->create($this->file, $content);
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', $temp->getDirectory());
		$neptune->set('view.dir', 'views/');
		$d = Config::create('prefix');
		$d->set('view.dir', 'folder_prefix/');
	}

	protected function getMockPath($view_name, $view_dir = 'views/') {
		return Temping::getInstance()->getDirectory() . $view_dir . $view_name;
	}

	public function tearDown() {
		Temping::getInstance()->reset();
		Config::unload();
	}

	public function testLoad() {
		$v = Form::load('some/file');
		$expected = $this->getMockPath('some/file.php');
		$this->assertEquals($expected, $v->getView());
	}

	public function testLoadPrefix() {
		$v = Form::load('prefix#view');
		$expected = $this->getMockPath('view.php', 'folder_prefix/');
		$this->assertEquals($expected, $v->getView());
	}

	public function testCreate() {
		$v = Form::create('url');
		$this->assertTrue($v instanceof Form);
	}

	public function testRowHiddenField() {
		$v = Form::create('url');
		$v->add('name', 'hidden');
		$this->assertEquals(Html::input('hidden', 'name'), $v->row('name'));
	}

	public function testCreateSimpleForm() {
		$v = Form::create('/post/url');
		$v->add('name', 'text');
		$expected = Html::openTag('form', array('action' => '/post/url', 'method' => 'post'));
		$expected .= '<ul><li><label for="name">Name</label>';
		$expected .= Html::input('text', 'name');
		$expected .= '</li></ul>';
		$expected .= '</form>';
		$this->assertEquals($expected, $v->render());
	}

	public function testSetAndGet() {
		$v = Form::load('some/file');
		$v->set('key', 'value');
		$this->assertEquals('value', $v->get('key'));
		$v->set('arr', array());
		$this->assertEquals(array(), $v->get('arr'));
		$obj = new \stdClass();
		$v->set('obj', $obj);
		$this->assertEquals($obj, $v->get('obj'));
	}

	public function testOnlyFieldsRendered() {
		$v = Form::create('/url');
		$v->add('name');
		$this->assertEquals(Html::input('text', 'name'), $v->input('name'));
		$v->var = 'hello';
		$this->assertNull($v->input('var'));
		$v->add('var');
		$this->assertEquals(Html::input('text', 'var'), $v->input('var'));
	}

	public function testIsset() {
		$v = Form::create('/url');
		$v->add('set', 'text', 'value');
		$this->assertTrue(isset($v->set));
		$this->assertFalse(isset($v->not_set));
	}

	public function testRenderException() {
		$v = Form::load('some/file');
		$this->setExpectedException('Neptune\\Exceptions\\ViewNotFoundException');
		$v->render();
	}

	public function testRenderAbsolutePath() {
		$view = Temping::getInstance()->getDirectory() . $this->view;
		$v = Form::loadAbsolute($view);
		$this->assertEquals('testing', $v->render());
	}

	public function testFormVarIsNotOverridden() {
		$view = Temping::getInstance()->getDirectory() . $this->view;
		$v = Form::loadAbsolute($view);
		$v->add('file', 'text', 'foo');
		$this->assertEquals('foo', $v->file);
		$this->assertEquals('testing', $v->render());
	}

	public function testCreateFromArray() {
		$values = array('name' => 'foo', 'age' => 100);
		$v = Form::create('/url');
		$v->setValues($values, true);
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
		$v->add('name', 'text', 'value');
		$this->assertEquals(Html::input('text', 'name', 'value'), $v->input('name'));
	}

	public function testDefaultAction() {
		$_SERVER['REQUEST_URI'] = '/default';
		$v = Form::create();
		$expected = Html::openTag('form', array('action' => '/default', 'method' => 'post'));
		$expected .= '<ul></ul>';
		$expected .= '</form>';
		$this->assertEquals($expected, $v->render());
	}

}
