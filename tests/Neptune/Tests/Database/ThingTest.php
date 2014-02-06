<?php

namespace Neptune\Tests\Database;

use Neptune\Database\Thing;
use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;
use Neptune\Form\Form;

require_once __DIR__ . '/../../../bootstrap.php';

class UpperCase extends Thing {

	protected static $fields = array('id', 'name', 'column');
	protected static $primary_key = 'id';
	protected static $table = 'table';

	public function setName($name) {
		return strtoupper($name);
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
		//no set methods should be called as it most likely
		//comes from a db query
		$this->assertEquals('test', $d->name);
		$this->assertEquals('test', $d->get('name'));
		$this->assertEquals('test', $d->getRaw('name'));
		//but get method is called when retrieving a value
		$this->assertEquals('VALUE', $d->column);
		$this->assertEquals('VALUE', $d->get('column'));
		$this->assertEquals('value', $d->getRaw('column'));
	}

	public function testGetAndSetValues() {
		$d = new UpperCase('db');
		$d->setValues(array('name' => 'test', 'column' => 'value'));
		//set methods should have been called in setValues
		$this->assertEquals('TEST', $d->getRaw('name'));
		$this->assertEquals('value', $d->getRaw('column'));
		//get methods should be called in getValues
		$expected = array('name' => 'TEST', 'column' => 'VALUE');
		$this->assertEquals($expected, $d->getValues());
	}

	public function testGetAndSetValuesRaw() {
		$d = new UpperCase('db');
		$d->setValuesRaw(array('name' => 'test', 'column' => 'value'));
		//set methods should not have been called in setValuesRaw
		$this->assertEquals('test', $d->getRaw('name'));
		$this->assertEquals('value', $d->getRaw('column'));
		//get methods should not be called in getValuesRaw
		$expected = array('name' => 'test', 'column' => 'value');
		$this->assertEquals($expected, $d->getValuesRaw());
	}

	protected function lastQuery() {
		return DatabaseFactory::getDriver('db')->getExecutedQuery();
	}

	public function testInsertBuild() {
		$d = new UpperCase('db');
		$d->id = 1;
		$d->column = 'value';
		$d->save();
		$query = 'INSERT INTO `table` (`id`, `column`) VALUES (`1`, `value`)';
		$this->assertEquals($query, $this->lastQuery());
	}

	public function testUpdateBuild() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->column = 'changed';
		$d->save();
		$query = 'UPDATE `table` SET `column` = `changed` WHERE id = `1`';
		$this->assertEquals($query, $this->lastQuery());
	}

	public function testInsertBuildWithSetMethod() {
		$d = new UpperCase('db');
		$d->name = 'test';
		//setName should be called, and modified flag set for saving
		$d->save();
		$query = 'INSERT INTO `table` (`name`) VALUES (`TEST`)';
		$this->assertEquals($query, $this->lastQuery());
	}

	public function testNoUpdate() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->save();
		$this->assertNull($this->lastQuery());
	}

	public function testNoInsert() {
		$d = new UpperCase('db');
		$d->save();
		$this->assertNull($this->lastQuery());
	}

	public function testNoUpdateDifferentFields() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($this->lastQuery());
	}

	public function testNoInsertDifferentFields() {
		$d = new UpperCase('db');
		$d->foo = 'bar';
		$d->save();
		$this->assertNull($this->lastQuery());
	}

	public function testPrimaryKeyIsUpdatedCorrectly() {
		$d = new UpperCase('db', array('id' => 1, 'column' => 'value'));
		$d->column = 'changed';
		$d->id = 2;
		$d->save();
		$query = 'UPDATE `table` SET `column` = `changed`, `id` = `2` WHERE id = `1`';
		$this->assertEquals($query, $this->lastQuery());
	}

	public function testPrimaryKeyUpdatedOnInsert() {
		$d = new UpperCase('db');
		$d->id = 1;
		$d->id = 3;
		$d->column = 'value';
		$d->save();
		$query = 'INSERT INTO `table` (`id`, `column`) VALUES (`3`, `value`)';
		$this->assertEquals($query, $this->lastQuery());
	}

    public function testBuildForm()
    {
        $form = UpperCase::buildForm(new Form('/url'));
        $this->assertInstanceOf('\Neptune\Form\Form', $form);
    }

    public function testBuildFormDoesNotIncludePrimaryKey()
    {
        $form = UpperCase::buildForm(new Form('/url'));
        $expected = array('name', 'column', '_save');
        $this->assertSame($expected, $form->getFields());
    }

    public function testBuildFormAddsValuesAndErrors()
    {
        $values = array(
            'name' => 'foo',
            'column' => 'bar'
        );
        $errors = array(
            'name' => 'Some name error'
        );
        $form = UpperCase::buildForm(new Form('/url'), $values, $errors);
        $this->assertSame('foo', $form->getValue('name'));
        $this->assertSame('bar', $form->getValue('column'));
        $this->assertSame('Some name error', $form->getError('name'));
    }


}
