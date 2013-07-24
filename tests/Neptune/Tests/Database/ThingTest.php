<?php
namespace Neptune\Tests\Database;

use Neptune\Database\Thing;
use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;
use Neptune\View\Form;

require_once __DIR__ . '/../../../bootstrap.php';

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
		$c = Config::create('testing');
		$c->set('database', array(
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

	public function testGetAndSetRaw() {
		$d = new UpperCase('db');
		$d->setRaw('column', 'test');
		$this->assertEquals('test', $d->getRaw('column'));
		$d->setRaw('name', 'test');
		$this->assertEquals('test', $d->getRaw('name'));
	}

	public function testGetAndSet() {
		$d = new UpperCase('db');
		$d->set('column', 'test');
		$this->assertEquals('TEST', $d->get('column'));
		$d->set('name', 'test');
		$this->assertEquals('TEST', $d->get('name'));
	}

	public function test__GetAnd__Set() {
		$d = new UpperCase('db');
		$d->column = 'test';
		$this->assertEquals('TEST', $d->column);
		$d->name = 'test';
		$this->assertEquals('TEST', $d->name);
	}

	public function testGetFromResultSet() {
		$d = new UpperCase('db', array('id' => 1, 'name' => 'test', 'column' => 'value'));
		$this->assertEquals(1, $d->id);
		//no magic set methods should be called as it most likely
		//comes from a db query
		$this->assertEquals('test', $d->name);
		$this->assertEquals('test', $d->get('name'));
		$this->assertEquals('test', $d->getRaw('name'));
		//but magic get method is called when retrieving a value
		$this->assertEquals('VALUE', $d->column);
		$this->assertEquals('VALUE', $d->get('column'));
		$this->assertEquals('value', $d->getRaw('column'));
	}

	public function testInsertBuild() {
		$d = new UpperCase('db');
		$d->id = 1;
		$d->column = 'value';
		$d->save();
		$query = 'INSERT INTO table (`id`, `column`) VALUES (1, value)';
		$driver = DatabaseFactory::getDriver('db');
		$this->assertEquals($query, $driver->getExecutedQuery());
	}

	public function testUpdateBuild() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->column = 'changed';
		$d->save();
		$query = 'UPDATE table SET `column` = changed WHERE id = 1';
		$driver = DatabaseFactory::getDriver('db');
		$this->assertEquals($query, $driver->getExecutedQuery());
	}

	public function testNoUpdate() {
		$db = DatabaseFactory::getDriver('db');
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoInsert() {
		$db = DatabaseFactory::getDriver('db');
		$d = new UpperCase('db');
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoUpdateDifferentFields() {
		$db = DatabaseFactory::getDriver('db');
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($db->getExecutedQuery());
	}

	public function testNoInsertDifferentFields() {
		$db = DatabaseFactory::getDriver('db');
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
