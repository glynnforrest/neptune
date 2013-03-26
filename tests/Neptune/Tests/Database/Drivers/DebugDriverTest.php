<?php

namespace Neptune\Tests\Database\Drivers;

use Neptune\Database\Drivers\DebugDriver;
use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * DebugDriverTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriverTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('testing');
		$c->set('database', array(
			'debug' => array(
				'driver' => 'debug',
				'database' => 'debug')
			));
	}

	public function tearDown() {
		DatabaseFactory::getDriver('debug')->reset();
		Config::unload();
	}

	public function testGetDriver() {
		$this->assertTrue(DatabaseFactory::getDriver('debug') instanceof DebugDriver);
	}

	public function testGetDriverPrefix() {
		$c = Config::create('prefix');
		$c->set('database', array(
			'debug' => array(
				'driver' => 'debug',
				'database' => 'default'
			),
			'second' => array(
				'driver' => 'debug',
				'database' => 'second'
			),
		));
		$this->assertTrue(DatabaseFactory::getDriver('prefix#') instanceof DebugDriver);
		$this->assertTrue(DatabaseFactory::getDriver('prefix#debug') instanceof DebugDriver);
		$this->assertTrue(DatabaseFactory::getDriver('prefix#second') instanceof DebugDriver);
		$this->assertTrue(DatabaseFactory::getDriver('prefix#') === DatabaseFactory::getDriver('prefix#debug'));
	}

	public function testGetPreparedQuery() {
		$db = DatabaseFactory::getDriver('debug');
		$db->prepare('SELECT * FROM test');
		$this->assertEquals('SELECT * FROM test', $db->getPreparedQuery());
	}

	public function testGetPreparedQueryNull() {
		$db = DatabaseFactory::getDriver('debug');
		$this->assertNull($db->getPreparedQuery());
	}

	public function testGetExecutedQuery() {
		$db = DatabaseFactory::getDriver('debug');
		$stmt = $db->prepare('INSERT INTO test (id, column) VALUES (?, ?)');
		$stmt->execute(array(1, 'value'));
		$this->assertEquals('INSERT INTO test (id, column) VALUES (1, value)',
			$db->getExecutedQuery());
	}

	public function testGetExecutedQueryNull() {
		$db = DatabaseFactory::getDriver('debug');
		$this->assertNull($db->getExecutedQuery());
	}
}
?>
