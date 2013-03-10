<?php
namespace Neptune\Database;

use Neptune\Database\Thing;
use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;
use Neptune\View\Form;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

class UpperCase extends Thing {

	protected static $fields = array('id', 'column');
	protected static $primary_key = 'id';
	protected static $table = 'table';

	public function setName($name) {
		$this->values['name'] = strtoupper($name);
	}

	public function getColumn() {
		return strtoupper($this->values['column']);
	}

}

/**
 * ThingTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ThingTest extends \PHPUnit_Framework_TestCase {

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
		$d = new UpperCase('db');
		$this->assertTrue($d instanceof Thing);
		$d2 = new UpperCase('db', array());
		$this->assertTrue($d2 instanceof Thing);
	}

	public function testGetAndSet() {
		$d = new UpperCase('db');
		$d->set('key', 'value');
		$this->assertEquals('value', $d->get('key'));
		$d->set('array', array());
		$this->assertEquals(array(), $d->get('array'));
		$obj = new \stdClass();
		$d->set('obj', $obj);
		$this->assertSame($obj, $d->get('obj'));
	}


	public function test__GetAnd__Set() {
		$d = new UpperCase('db');
		$d->key = 'value';
		$this->assertEquals('value', $d->key);
		$d->array = array();
		$this->assertEquals(array(), $d->array);
		$obj = new \stdClass();
		$d->obj = $obj;
		$this->assertSame($obj, $d->obj);
	}

	public function testGetFromResultSet() {
		$d = new Thing('db', array('id' => 1, 'column' => 'value'));
		$this->assertEquals(1, $d->id);
		$this->assertEquals('value', $d->column);
	}

	public function testGetOverride() {
		$u = new UpperCase('db', array('id' => 2, 'column' => 'value'));
		$this->assertEquals('value', $u->get('column'));
		$this->assertEquals('VALUE', $u->column);
	}

	public function testSetOverride() {
		$u = new UpperCase('db');
		$u->name = ('test');
		$this->assertEquals('TEST', $u->name);
		$this->assertEquals('TEST', $u->get('name'));
	}

	public function testInsertBuild() {
		$d = new UpperCase('db');
		$d->id = 1;
		$d->column = 'value';
		$d->save();
		$this->assertEquals('INSERT INTO table (`id`, `column`) VALUES (1, value)',
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testUpdateBuild() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->column = 'changed';
		$d->save();
		$this->assertEquals('UPDATE table SET `column` = changed WHERE id = 1',
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testNoUpdate() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoInsert() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new UpperCase('db');
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoUpdateDifferentFields() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoInsertDifferentFields() {
		$db = DatabaseFactory::getDriver('db');
		$db->reset();
		$d = new UpperCase('db');
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testPrimaryKeyIsNotUpdated() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->column = 'changed';
		$d->id = 2;
		$d->save();
		$this->assertEquals('UPDATE table SET `column` = changed, `id` = 2 WHERE id = 1',
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testPrimaryKeyUpdatedOnInsert() {
		$d = new UpperCase('db');
		$d->id = 1;
		$d->id = 3;
		$d->column = 'value';
		$d->save();
		$this->assertEquals('INSERT INTO table (`id`, `column`) VALUES (3, value)',
		DatabaseFactory::getDriver('db')->getExecutedQuery());
	}

	public function testBuildForm() {
		$this->assertTrue(UpperCase::buildForm() instanceof Form);
	}

}
