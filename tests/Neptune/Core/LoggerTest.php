<?php

namespace Neptune\Core;

use Neptune\Core\Logger;
use Neptune\Core\Config;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * LoggerTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class LoggerTest extends \PHPUnit_Framework_TestCase {
	const file = '/tmp/logtest';

	public function setUp() {
		Config::create('config');
		Config::set('log', array(
			'type' => array (
				'fatal' => true,
				'error' => false,
				'debug' => true,
				'info' => true
			),
			'file' => self::file,
			'format' => ':message'
		));
		Logger::enable();
		Logger::temp();
		Logger::flush();
	}

	public function tearDown() {
		Config::unload();
		@unlink(self::file);
	}

	public function testConstruct() {
		$this->assertTrue(Logger::getInstance() instanceof Logger);
	}

	public function testCreateLog() {
		Logger::info('test log');
		$this->assertEquals(array('test log'), Logger::getLogs());
	}

	public function testCreateLogFormatsArrays() {
		Logger::info(array('one', 'two', 'three', 'four'));
		$expected = "array (" . PHP_EOL .
		"  0 => 'one'," . PHP_EOL .
		"  1 => 'two'," . PHP_EOL .
		"  2 => 'three'," . PHP_EOL .
		"  3 => 'four'," . PHP_EOL . ")";
		$this->assertEquals($expected, Logger::getLogs()[0]);
	}

	public function testCreateLogDisabled() {
		Logger::disable();
		Logger::fatal('this won\'t be logged');
		$this->assertEquals(array(), Logger::getLogs());
	}

	public function testLogTypeDisabled() {
		Logger::error('big error');
		$this->assertEquals(array(), Logger::getLogs());
	}

	public function testUndefinedLogType() {
		Logger::foo('bar');
		$this->assertEquals(array(), Logger::getLogs());
	}

	public function testParseLog() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		Logger::setFormat('[:type] :date :ip :message');
		Logger::debug('test log');
		$this->assertEquals(array('[debug] ' . date('d/m/y') . ' 127.0.0.1 test log'),
							Logger::getLogs());
	}

	public function testSave() {
		Logger::saving();
		Logger::setFormat(':message');
		Logger::debug('saved to file');
		Logger::save();
		$this->assertEquals('saved to file' . PHP_EOL, file_get_contents(self::file));
	}

}
?>
