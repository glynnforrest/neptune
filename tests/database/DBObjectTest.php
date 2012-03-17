<?php
namespace neptune\database;

use neptune\database\DBObject;
use neptune\database\DatabaseFactory;
use neptune\core\Config;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

class UpperCase extends DBObject {

	public function setName($name) {
		$this->values['name'] = strtoupper($name);
	}

	public function getColumn() {
		return strtoupper($this->values['column']);
	}
	
}

/**
 * DBObjectTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DBObjectTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('database', array(
			'db' => array(
				'driver' => 'debug',
				'database' => 'debug')
			));	
	}

	public function tearDown() {
		DatabaseFactory::getDriver('db')->reset();
		Config::unload();	
	}


	public function testConstruct() {
		$d = new DBObject('db', 'table');
		$this->assertTrue($d instanceof DBObject);
		$d2 = new DBObject('db', 'table', array());
		$this->assertTrue($d2 instanceof DBObject);
	}

	public function testGetAndSet() {
		$d = new DBObject('db', 'table');
		$d->set('key', 'value');
		$this->assertEquals('value', $d->get('key'));
		$d->set('array', array());
		$this->assertEquals(array(), $d->get('array'));
		$obj = new \stdClass();
		$d->set('obj', $obj);
		$this->assertSame($obj, $d->get('obj'));
	}


	public function test__GetAnd__Set() {
		$d = new DBObject('db', 'table');
		$d->key = 'value';
		$this->assertEquals('value', $d->key);
		$d->array = array();
		$this->assertEquals(array(), $d->array);
		$obj = new \stdClass();
		$d->obj = $obj;
		$this->assertSame($obj, $d->obj);
	}

	public function testGetFromResultSet() {
		$d = new DBObject('db', 'table', array('id' => 1, 'column' => 'value'));
		$this->assertEquals(1, $d->id);
		$this->assertEquals('value', $d->column);
	}

	public function testGetOverride() {
		$u = new UpperCase('db', 'table', array('id' => 2, 'column' => 'value'));
		$this->assertEquals('value', $u->get('column'));
		$this->assertEquals('VALUE', $u->column);
	}

	public function testSetOverride() {
		$u = new UpperCase('db', 'table');
		$u->name = ('test');
		$this->assertEquals('TEST', $u->name);
		$this->assertEquals('TEST', $u->get('name'));
	}

	public function testInsertBuild() {
		$d = new DBObject('db', 'table');
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->id = 1;
		$d->column = 'value';
		$d->save();
		$this->assertEquals('INSERT INTO table (`id`, `column`) VALUES (1, value)', 
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testUpdateBuild() {
		$d = new DBObject('db', 'table', array('id' => 1, 'column' => 'value'));
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->column = 'changed';
		$d->save();
		$this->assertEquals('UPDATE table SET `column` = changed WHERE id = 1',
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testNoUpdate() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new DBObject('db', 'table', array('id' => 1, 'column' => 'value'));
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoInsert() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new DBObject('db', 'table');
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoUpdateDifferentFields() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new DBObject('db', 'table', array('id' => 1, 'column' => 'value'));
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoInsertDifferentFields() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new DBObject('db', 'table');
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testPrimaryKeyIsNotUpdated() {
		$d = new DBObject('db', 'table', array('id' => 1, 'column' => 'value'));
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->column = 'changed';
		$d->id = 2;
		$d->save();
		$this->assertEquals('UPDATE table SET `column` = changed, `id` = 2 WHERE id = 1',
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testPrimaryKeyUpdatedOnInsert() {
		$d = new DBObject('db', 'table');
		$d->setFields(array('id', 'column'));
		$d->setPrimaryKey('id');
		$d->id = 1;
		$d->id = 3;
		$d->column = 'value';
		$d->save();
		$this->assertEquals('INSERT INTO table (`id`, `column`) VALUES (3, value)', 
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

}
?>
