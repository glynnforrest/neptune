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
		Config::bluff('unittest');
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
		$this->assertTrue(DatabaseFactory::getDatabase() instanceof DebugDriver);
		$this->assertTrue(DatabaseFactory::getDatabase('unittest') instanceof DebugDriver);
	}

	public function testGetDatabaseBadConfig() {
		$this->setExpectedException('\\neptune\\exceptions\\ConfigKeyException');
		DatabaseFactory::getDatabase('wrong');
	}

	public function testGetDatabaseUndefinedDriver() {
		$this->setExpectedException('\\neptune\\exceptions\\DriverNotFoundException');
		DatabaseFactory::getDatabase('fake');
	}

	public function testGetBuilder() {
		$db = DatabaseFactory::getDatabase();
		$this->assertEquals('\\neptune\\database\\builders\\GenericSQLBuilder', $db->getBuilderName());
	}

	public function testGetBuilderOverride() {
		$db = DatabaseFactory::getDatabase('unittest2');
		$this->assertEquals('debug', $db->getBuilderName());
	}

}

?>
