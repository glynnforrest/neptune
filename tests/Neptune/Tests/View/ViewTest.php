<?php

namespace Neptune\Tests\View;

use Neptune\View\View;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ViewTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ViewTest extends \PHPUnit_Framework_TestCase {
	const file = '/tmp/viewtest.php';
	const view = '/tmp/viewtest';

	public function setUp() {
		touch(self::file);
		$content = '<?php';
		$content .= <<<END
		echo 'testing';
END;
		$content .= '?>';
		file_put_contents(self::file, $content);
		Config::create('view');
		Config::set('view.dir', '/tmp/');
		Config::create('prefix');
		Config::set('prefix#view.dir', 'folder_prefix/');
	}

	public function tearDown() {
		unlink(self::file);
		Config::unload();
	}

	public function testConstruct() {
		$v = View::load('some/file');
		$this->assertTrue($v instanceof View);
	}

	public function testLoad() {
		$v = View::load('some/file');
		$this->assertEquals('/tmp/some/file.php', $v->getViewName());

	}

	public function testLoadPrefix() {
		$v = View::load('prefix#view');
		$this->assertEquals('folder_prefix/view.php', $v->getViewName());
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
		$v->var = 'value';
		$this->assertTrue(isset($v->var));
		$this->assertFalse(isset($v->unset));
	}

	public function testRenderException() {
		$v = View::load('some/file');
		$this->setExpectedException('Neptune\\Exceptions\\ViewNotFoundException');
		$v->render();
	}

	public function testRenderAbsolutePath() {
		$v = View::loadAbsolute(self::view);
		$this->assertEquals('testing', $v->render());
	}

	public function testViewVarIsNotOverridden() {
		$v = View::loadAbsolute(self::view);
		$v->file = 'foo';
		$this->assertEquals('foo', $v->file);
		$this->assertEquals('testing', $v->render());
	}

}
?>
