<?php

namespace neptune\database\drivers;

use neptune\database\drivers\DebugDriver;
use neptune\database\DatabaseFactory;
use neptune\core\Config;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

/**
 * DebugDriverTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriverTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('database', array(
			'debug' => array(
				'driver' => 'debug',
				'database' => 'debug')
			));	
	}

	public function tearDown() {
		DatabaseFactory::getDriver('debug')->reset();
		Config::unload();	
	}

	public function testConstruct() {
		$this->assertTrue(DatabaseFactory::getDriver('debug') instanceof DebugDriver);
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
