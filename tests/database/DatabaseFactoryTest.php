<?php

namespace neptune\database;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

use neptune\core\Config;
use neptune\database\drivers\DebugDriver;

/**
 * DatabaseFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DatabaseFactoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('unittest');
		Config::set('database', array(
			'unittest' => array(
				'driver' => 'debug',
				'database' => 'unittest'
			),
			'unittest2' => array(
				'driver' => 'debug',
				'database' => 'unittest',
				'builder' => 'debug'
			),
			'fake' => array(
				'driver' => 'fake',
				'database' => 'database'
			)
		));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGetDatabase() {
		$this->assertTrue(DatabaseFactory::getDriver() instanceof DebugDriver);
		$this->assertTrue(DatabaseFactory::getDriver('unittest') instanceof DebugDriver);
	}

	public function testGetDatabaseBadConfig() {
		$this->setExpectedException('\\neptune\\exceptions\\ConfigKeyException');
		DatabaseFactory::getDriver('wrong');
	}

	public function testGetDatabaseUndefinedDriver() {
		$this->setExpectedException('\\neptune\\exceptions\\DriverNotFoundException');
		DatabaseFactory::getDriver('fake');
	}

	public function testGetBuilder() {
		$db = DatabaseFactory::getDriver();
		$this->assertEquals('\\neptune\\database\\builders\\GenericSQLBuilder', $db->getBuilderName());
	}

	public function testGetBuilderOverride() {
		$db = DatabaseFactory::getDriver('unittest2');
		$this->assertEquals('debug', $db->getBuilderName());
	}

}

?>
