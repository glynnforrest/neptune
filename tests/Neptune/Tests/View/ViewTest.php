<?php

namespace Neptune\Tests\View;

use Neptune\View\View;
use Neptune\Core\Config;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ViewTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ViewTest extends \PHPUnit_Framework_TestCase {

	protected $file = 'viewtest.php';
	protected $view = 'viewtest';

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

	public function testConstruct() {
		$v = View::load('some/file');
		$this->assertTrue($v instanceof View);
	}

	public function testLoad() {
		$v = View::load('some/file');
		$expected = $this->getMockPath('some/file.php');
		$this->assertEquals($expected, $v->getView());
	}

	public function testLoadPrefix() {
		$v = View::load('prefix#view');
		$expected = $this->getMockPath('view.php', 'folder_prefix/');
		$this->assertEquals($expected, $v->getView());
	}

	public function testSetAndGet() {
		$v = View::load('some/file');
		$v->set('key', 'value');
		$this->assertEquals('value', $v->get('key'));
		$v->set('arr', array());
		$this->assertEquals(array(), $v->get('arr'));
		$obj = new \stdClass();
		$v->set('obj', $obj);
		$this->assertEquals($obj, $v->get('obj'));
	}

	public function testIsset() {
		$v = View::load('some/file');
		$v->key = 'value';
		$this->assertTrue(isset($v->key));
		$this->assertFalse(isset($v->not_set));
	}

	public function testRenderException() {
		$v = View::load('some/file');
		$this->setExpectedException('Neptune\\Exceptions\\ViewNotFoundException');
		$v->render();
	}

	public function testRenderAbsolutePath() {
		$view = Temping::getInstance()->getDirectory() . $this->view;
		$v = View::loadAbsolute($view);
		$this->assertEquals('testing', $v->render());
	}

	public function testViewVarIsNotOverridden() {
		$view = Temping::getInstance()->getDirectory() . $this->view;
		$v = View::loadAbsolute($view);
		$v->file = 'foo';
		$this->assertEquals('foo', $v->file);
		$this->assertEquals('testing', $v->render());
	}

}
