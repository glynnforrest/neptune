<?php

namespace neptune\view;

use neptune\view\View;
use neptune\core\Config;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

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
		Config::bluff('view');
		Config::set('view_dir', '/tmp');
	}

	public function tearDown() {
		unlink(self::file);
		Config::unload();
	}

	public function testConstruct() {
		$v = View::load('some/file');
		$this->assertTrue($v instanceof View);
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
		$this->setExpectedException('neptune\\exceptions\\ViewNotFoundException');
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
