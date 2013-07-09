<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Config;
use Neptune\Core\Neptune;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ConfigTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {

	const file = '/tmp/configtest.php';
	const file2 = '/tmp/configtest2.php';
	const file_override = '/tmp/configoverride.php';

	public function setUp() {
		touch(self::file);
		touch(self::file2);
		touch(self::file_override);
		$content = '<?php';
		$content .= <<<END
		return array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2,
			),
		)
END;
		$content .= '?>';
		file_put_contents(self::file, $content);
		file_put_contents(self::file2, $content);
		$content = '<?php';
		$content .= <<<END
		return array(
			'one' => 'override',
			'two' => array(
				'two' => 'override_again'
			),
		)
END;
		$content .= '?>';
		file_put_contents(self::file_override, $content);
		Config::unload();
	}

	public function tearDown() {
		@unlink(self::file);
		@unlink(self::file2);
		@unlink(self::file_override);
		Config::unload();
	}

	public function testLoad() {
		$name_and_file = Config::load('testing', self::file);
		$this->assertTrue($name_and_file instanceof Config);
		$name_only = Config::load('testing');
		$this->assertTrue($name_only instanceof Config);
		$none = Config::load();
		$this->assertTrue($none instanceof Config);
		$this->assertTrue($name_and_file === $name_only);
		$this->assertTrue($name_and_file === $none);
		$this->assertTrue($name_only === $none);
	}

	public function testLoadMultipleFiles() {
		$default = Config::load('testing', self::file);
		$extra = Config::load('extra', self::file2);
		$none = Config::load();
		$this->assertTrue($default instanceof Config);
		$this->assertTrue($extra instanceof Config);
		$this->assertTrue($none instanceof Config);
		$this->assertTrue($default === $none);
		$this->assertFalse($extra === $default);
		$this->assertFalse($extra === $none);
	}

	public function testLoadThrowsExceptionWithNoConfig() {
		$this->setExpectedException('Neptune\\Exceptions\\ConfigFileException');
		Config::load();
	}

	public function testLoadThowsExceptionWithNoFile() {
		$this->setExpectedException('Neptune\\Exceptions\\ConfigFileException');
		Config::load('not-here');
	}

	public function testLoadOverwritesWithDifferentFilename() {
		$c = Config::load('testing', self::file);
		$d = Config::load('testing', self::file);
		$this->assertTrue($c === $d);
		$e = Config::load('testing', self::file2);
		$this->assertFalse($c === $e);
	}

	public function testCreate() {
		$c = Config::create('creation');
		$this->assertTrue($c instanceof Config);
		$d = Config::create('creation');
		$this->assertTrue($c === $d);
	}

	public function testCreateCanSaveNewFiles() {
		$c = Config::create('new', '/tmp/new-config.php');
		$c->set('key', 'value');
		$c->save();
		$this->assertTrue(file_exists('/tmp/new-config.php'));
		@unlink('/tmp/new-config.php');
	}

	public function testCreateDoesntLoadFileThatExists() {
		$c = Config::create('new', self::file);
		$this->assertNull($c->get('one'));
	}

	public function testGet() {
		$c = Config::load('testing', self::file);
		$this->assertEquals(1, $c->get('one'));
		$this->assertEquals(2.1, $c->get('two.one'));
		$expected = array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2
			)
		);
		$this->assertEquals($expected, $c->get());
	}

	public function testGetDefault() {
		$c = Config::load('testing', self::file);
		$this->assertEquals('default', $c->get('fake-key', 'default'));
		$expected = array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2
			)
		);
		$this->assertEquals($expected, $c->get(null, 'default'));
	}

	public function testGetFirst() {
		$c = Config::load('testing', self::file);
		$this->assertEquals(2.1, $c->getFirst('two'));
		$this->assertEquals(1, $c->getFirst());
	}

	public function testGetFirstDefault() {
		$c = Config::load('testing', self::file);
		$this->assertEquals('default', $c->getFirst('fake-key', 'default'));
	}

	public function testGetRequired() {
		$c = Config::load('testing', self::file);
		$this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException');
		$c->getRequired('fake');
		$this->assertEquals(2.1, $c->getRequired('two.one'));
	}

	public function testGetFirstRequired() {
		$c = Config::load('testing', self::file);
		$this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException');
		$c->getFirstRequired('fake');
		/* $this->assertEquals(2.1, $c->getFirstRequired('two')); */
		//also throw an exception if the first value is an array
		//why doesn't the second setExpectedException work?
		$c->set('3.1', 'value');
		$this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException');
		/* $this->assertEquals(4, 3); */
		/* $c->getFirstRequired('4.1'); */
	}

	public function testSet() {
		$c = Config::load('testing', self::file);
		$c->set('three', 3);
		$this->assertEquals(3, $c->get('three'));
	}

	public function testSetNoFile() {
		$c = Config::create('fake');
		$c->set('ad-hoc', 'data');
		$this->assertEquals('data', $c->get('ad-hoc'));
		$c->set('nested', array('value' => 'foo'));
		$this->assertEquals('foo', $c->get('nested.value'));
	}

	public function testSetNested() {
		$c = Config::create('fake');
		$c->set('parent.child', 'value');
		$this->assertEquals(array('parent' => array('child' => 'value')), $c->get());
	}

	public function testGetNested() {
		$c = Config::create('fake');
		$c->set('parent', array('child' => 'value'));
		$this->assertEquals('value', $c->get('parent.child'));
	}

	public function testSetDeepNested() {
		$c = Config::create('fake');
		$c->set('parent.child.0.1.2.3.4', 'value');
		$this->assertEquals(array('parent' => array('child' => array(
			0 => array(1 => array(2 => array(3 => array(4 =>'value'))))))), $c->get());
	}

	public function testGetDeepNested() {
		$c = Config::create('fake');
		$c->set('parent', array('child' => array(
			0 => array(1 => array(2 => array(3 => array(4 =>'value')))))));
		$this->assertEquals('value', $c->get('parent.child.0.1.2.3.4'));
	}

	public function testEmptyGet() {
		$c = Config::load('testing', self::file);
		$this->assertEquals(array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2
			)
		), $c->get());
		$this->assertEquals(array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2
			)
		), $c->get(null));
	}

	public function testUnload() {
		$c = Config::load('testing', self::file);
		$this->assertEquals(1, $c->get('one'));
		$d = Config::load('testing');
		$this->assertTrue($c === $d);
		Config::unload('testing');
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigFileException');
		$e = Config::load('testing');
		$f = Config::load('testing', self::file);
		$this->assertFalse($c === $f);
	}

	/**
	 * Strip out whitespace and new lines from config settings to make
	 * them easier to compare against.
	 */
	protected function flattenConfig($content) {
		return preg_replace('`\s+`', '', $content);
	}

	public function testSave() {
		$c = Config::load('testing', self::file);
		$c->set('one', 'changed');
		$c->save();
		$content = '<?php';
		$content .= <<<END
		return array(
			'one' => 'changed',
			'two' => array(
				'one' => 2.1,
				'two' => 2.2,
			),
		)
END;
		$content .= '?>';
		$this->assertEquals($this->flattenConfig($content),
							$this->flattenConfig(file_get_contents(self::file)));
	}

	public function testSaveThrowsExceptionWithNoFile() {
		$c = Config::create('ad-hoc');
		$c->set('key', 'value');
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigFileException');
		$c->save();
	}

	public function testSaveThrowsExceptionWhenFileWriteFails() {
		$restricted = '/root/config.php';
		$c = Config::create('unlikely', $restricted);
		$c->set('key', 'value');
		Neptune::handleErrors();
		$this->setExpectedException('\\Neptune\\Exceptions\\NeptuneError');
		$c->save();
		restore_error_handler();
		restore_exception_handler();
	}

	public function testSaveDoesNotWriteIfNotModified() {
		$file = '/tmp/do-not-write.php';
		@unlink($file);
		$c = Config::create('new', $file);
		$c->save();
		$this->assertFalse(file_exists($file));
	}

	public function testSaveAll() {
		$c = Config::load('testing', self::file);
		$c->set('two.one', 2.11);
		$d = Config::load('other', self::file2);
		$d->set('one', 'changed');
		Config::saveAll();
		$expectedC = '<?php';
		$expectedC .= <<<END
		return array(
			'one' => 1,
			'two' => array(
				'one' => 2.11,
				'two' => 2.2,
			),
		)
END;
		$expectedC .= '?>';
		$expectedD = '<?php';
		$expectedD .= <<<END
		return array(
			'one' => 'changed',
			'two' => array(
				'one' => 2.1,
				'two' => 2.2,
			),
		)
END;
		$expectedD .= '?>';
		$this->assertEquals($this->flattenConfig($expectedC),
							$this->flattenConfig(file_get_contents(self::file)));
		$this->assertEquals($this->flattenConfig($expectedD),
							$this->flattenConfig(file_get_contents(self::file2)));
	}

	public function testSetFilename() {
		$c = Config::load('testing', self::file);
		$c->set('one', 'changed');
		$c->setFilename(self::file2);
		$c->save();
		//the first test file should be unmodified
		$content = '<?php';
		$content .= <<<END
		return array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2,
			),
		)
END;
		$content .= '?>';
		$this->assertEquals($this->flattenConfig($content),
							$this->flattenConfig(file_get_contents(self::file)));
		//file2 should have changed instead
		$changed = '<?php';
		$changed .= <<<END
		return array(
			'one' => 'changed',
			'two' => array(
				'one' => 2.1,
				'two' => 2.2,
			),
		)
END;
		$changed .= '?>';
		$this->assertEquals($this->flattenConfig($changed),
							$this->flattenConfig(file_get_contents(self::file2)));
	}

	public function testGetFilename() {
		$c = Config::load('testing', self::file);
		$d = Config::load('testing');
		$this->assertEquals(self::file, $d->getFileName());
	}

	public function testOverride() {
		$c = Config::load('testing', self::file);
		$this->assertEquals(1, $c->get('one'));
		$c->override(array('one' => 'override'));
		$this->assertEquals('override', $c->get('one'));
	}

	public function testLoadCallsOverride() {
		$default = Config::load('default', self::file);
		$this->assertEquals(1, $default->get('one'));
		$this->assertEquals(2.1, $default->get('two.one'));
		Config::load('override', self::file_override, 'default');
		$this->assertEquals('override', $default->get('one'));
		$this->assertEquals('override_again', $default->get('two.two'));
		$this->assertEquals('2.1', $default->get('two.one'));
	}

}
