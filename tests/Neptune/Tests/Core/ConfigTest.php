<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Config;
use Neptune\Core\Neptune;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ConfigTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {

	const file = 'neptune-config-test/config.php';
	const file2 = 'neptune-config-test/config2.php';
	const file_override = 'neptune-config-test/configoverride.php';

	public function setUp() {
		$this->temp = Temping::getInstance();
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
		$this->temp->create(self::file, $content);
		$this->temp->create(self::file2, $content);
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
		$this->temp->create(self::file_override, $content);
	}

	public function tearDown() {
		$this->temp->reset();
		Config::unload();
	}

	public function testLoad() {
		$name_and_file = Config::load('testing', $this->temp->getPathname(self::file));
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
		$default = Config::load('testing', $this->temp->getPathname(self::file));
		$extra = Config::load('extra', $this->temp->getPathname(self::file2));
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
		$file = $this->temp->getPathname(self::file);
		$c = Config::load('testing', $file);
		$d = Config::load('testing', $file);
		$this->assertTrue($c === $d);
		$file2 = $this->temp->getPathname(self::file2);
		$e = Config::load('testing', $file2);
		$this->assertFalse($c === $e);
	}

	public function testCreate() {
		$c = Config::create('creation');
		$this->assertTrue($c instanceof Config);
		$d = Config::create('creation');
		$this->assertTrue($c === $d);
	}

	public function testCreateCanSaveNewFiles() {
		$new_config = $this->temp->getDirectory() . 'new-config.php';
		$c = Config::create('new', $new_config);
		$c->set('key', 'value');
		$c->save();
		$this->assertFileExists($new_config);
	}

	public function testCreateDoesntLoadFileThatExists() {
		$c = Config::create('new', $this->temp->getPathname(self::file));
		$this->assertNull($c->get('one'));
	}

	public function testGet() {
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
		$c = Config::load('testing', $this->temp->getPathname(self::file));
		$this->assertEquals(2.1, $c->getFirst('two'));
		$this->assertEquals(1, $c->getFirst());
	}

	public function testGetFirstDefault() {
		$c = Config::load('testing', $this->temp->getPathname(self::file));
		$this->assertEquals('default', $c->getFirst('fake-key', 'default'));
	}

	public function testGetRequired() {
		$c = Config::load('testing', $this->temp->getPathname(self::file));
		$this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException');
		$c->getRequired('fake');
		$this->assertEquals(2.1, $c->getRequired('two.one'));
	}

	public function testGetFirstRequired() {
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
		$c = Config::load('testing', $this->temp->getPathname(self::file));
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
							$this->flattenConfig($this->temp->getContents(self::file)));
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
		$file = $this->temp->getDirectory() . 'do-not-write.php';
		$c = Config::create('new', $file);
		$c->save();
		$this->assertFalse(file_exists($file));
	}

	public function testSaveAll() {
		$c = Config::load('testing', $this->temp->getPathname(self::file));
		$c->set('two.one', 2.11);
		$d = Config::load('other', $this->temp->getPathname(self::file2));
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
							$this->flattenConfig($this->temp->getContents(self::file)));
		$this->assertEquals($this->flattenConfig($expectedD),
							$this->flattenConfig($this->temp->getContents(self::file2)));
	}

	public function testSetFilename() {
		$file = $this->temp->getPathname(self::file);
		$file2 = $this->temp->getPathname(self::file2);
		$c = Config::load('testing', $file);
		$c->set('one', 'changed');
		$c->setFilename($file2);
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
							$this->flattenConfig(file_get_contents($file)));
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
							$this->flattenConfig(file_get_contents($file2)));
	}

	public function testGetFilename() {
		$file = $this->temp->getPathname(self::file);
		$c = Config::load('testing', $file);
		$d = Config::load('testing');
		$this->assertEquals($file, $d->getFileName());
	}

	public function testOverride() {
		$c = Config::load('testing', $this->temp->getPathname(self::file));
		$this->assertEquals(1, $c->get('one'));
		$c->override(array('one' => 'override'));
		$this->assertEquals('override', $c->get('one'));
	}

	public function testLoadCallsOverride() {
		$default = Config::load('default', $this->temp->getPathname(self::file));
		$this->assertEquals(1, $default->get('one'));
		$this->assertEquals(2.1, $default->get('two.one'));
		Config::load('override', $this->temp->getPathname(self::file_override), 'default');
		$this->assertEquals('override', $default->get('one'));
		$this->assertEquals('override_again', $default->get('two.two'));
		$this->assertEquals('2.1', $default->get('two.one'));
	}

	public function testLoadModule() {
		//neptune will look for modules defined in config/neptune.php
		$neptune = Config::create('neptune');
		//any file named config.php will be loaded in the config
		//directory. Let's pretend the test config file is for a
		//module.
		$this->temp->create('test_module/config.php',
							$this->temp->getContents(self::file));
		$neptune->set('dir.root', $this->temp->getDirectory());
		$neptune->set('modules', array('test_module' => 'test_module/'));
		$module = Config::loadModule('test_module');
		$this->assertEquals(2.1, $module->get('two.one'));
	}

	public function testLoadModuleThrowsExceptionForNoNeptune() {
		$this->setExpectedException('Neptune\\Exceptions\\ConfigFileException');
		$module = Config::loadModule('test_module');
	}

	public function testLoadModuleThrowsExceptionForUnknownModule() {
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', $this->temp->getDirectory());
		$this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException');
		$module = Config::loadModule('unknown');
	}

	public function testLoadModuleThrowsExceptionForConfigFileNotFound() {
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', $this->temp->getDirectory());
		$neptune->set('modules', array('not-here' => '/path/to/not/here'));
		$this->setExpectedException('Neptune\\Exceptions\\ConfigFileException');
		$module = Config::loadModule('not-here');
	}

	public function testLoadModuleAlsoLoadsOverride() {
		//neptune will look in for config/modules/<modulename>.php and
		//override any values in the module config.
		//it will use dir.root in the neptune config to get the path,
		//so let's mock the config directory here.
		$this->temp->create('test_module/config.php',
							$this->temp->getContents(self::file));
		$this->temp->create('config/modules/test_module.php',
							$this->temp->getContents(self::file_override));
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', $this->temp->getDirectory());
		$neptune->set('modules', array('test_module' => 'test_module/'));
		$module = Config::loadModule('test_module');
		$this->assertEquals('override_again', $module->get('two.two'));
	}

	public function testLoadCallsLoadModule() {
		$this->temp->create('test_module/config.php',
							$this->temp->getContents(self::file));
		$this->temp->create('config/modules/test_module.php',
							$this->temp->getContents(self::file_override));
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', $this->temp->getDirectory());
		$neptune->set('modules', array('test_module' => 'test_module/'));
		$module = Config::load('test_module');
		$this->assertEquals('override_again', $module->get('two.two'));
	}

	public function testLoadingNeptuneAsAModuleDoesNotBreakEverything() {
		$this->setExpectedException('Neptune\\Exceptions\\ConfigFileException');
		Config::loadModule('neptune');
	}

	public function testLoadEnv() {
		//the loaded env gets merged into neptune config
		$neptune = Config::create('neptune');
		$neptune->set('one', 'default');
		$neptune->set('dir.root', $this->temp->getDirectory());
		$this->assertEquals('default', $neptune->get('one'));
		$this->temp->create('config/env/test.php',
							$this->temp->getContents(self::file_override));
		Config::loadEnv('test');
		$this->assertEquals('override', $neptune->get('one'));
	}

	public function testGetPath() {
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', '/path/to/root/');
		$neptune->set('some.dir', 'my-dir');
		$this->assertEquals('/path/to/root/my-dir', $neptune->getPath('some.dir'));
	}

	public function testGetPathAbsolute() {
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', '/path/to/root/');
		$neptune->set('some.absolute.dir', '/my-dir');
		$this->assertEquals('/my-dir', $neptune->getPath('some.absolute.dir'));
	}

	public function testGetPathDifferentFile() {
		$neptune = Config::create('neptune');
		$neptune->set('dir.root', '/path/to/root/');
		$other = Config::create('other');
		$other->set('some.other.dir', 'other-dir');
		$this->assertEquals('/path/to/root/other-dir', $other->getPath('some.other.dir'));
	}

}
