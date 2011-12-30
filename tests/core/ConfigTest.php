<?php

namespace neptune\core;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * ConfigTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {
	const file = '/tmp/configtest.php';

	public function setUp() {
		touch(self::file);
		$content = '<?php';
		$content .= <<<END
		return array(
			'one' => 1,
			'two' => array(
				'one' => 2.1,
				'two' => 2.2
			)
		)
END;
		$content .= '?>';
		file_put_contents(self::file, $content);
		Config::unload();
	}

	public function tearDown() {
		unlink(self::file);
		Config::unload();
	}

	public function testGet() {
		Config::load(self::file);
		$this->assertEquals(1, Config::get('one'));
		$this->assertEquals(2.1, Config::get('two.one'));
	}

	public function testGetDefault() {
		Config::load(self::file);
		$this->assertEquals('default', Config::get('fake-key', 'default'));
	}

	public function testGetNamedFile() {
		Config::load(self::file, 'named');
		$this->assertEquals(1, Config::get('named#one'));
	}

	public function testSet() {
		Config::load(self::file);
		$this->assertTrue(Config::set('three', 3));
		$this->assertEquals(3, Config::get('three'));
	}

	public function testSetNoFile() {
		Config::bluff('fake');
		Config::set('ad-hoc', 'data');
		$this->assertEquals('data', Config::get('ad-hoc'));
		Config::set('nested', array('value' => 'foo'));
		$this->assertEquals('foo', Config::get('nested.value'));
	}

	public function testEmptyGet() {
		Config::load(self::file);
		$this->assertEquals(array(
			 'one' => 1,
			 'two' => array(
				  'one' => 2.1,
				  'two' => 2.2
			 )
				  ), Config::get());
		$this->assertEquals(array(
			 'one' => 1,
			 'two' => array(
				  'one' => 2.1,
				  'two' => 2.2
			 )
				  ), Config::get(null, self::file));
	}

	public function testUnload() {
		Config::load(self::file);
		$this->assertEquals(1, Config::get('one'));
		Config::unload();
		$this->assertNull(Config::get('one'));
	}

}

?>
