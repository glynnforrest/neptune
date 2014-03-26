<?php

namespace Neptune\Tests\Database\Entity\Entity;

use Neptune\Database\Entity\Entity;
use Neptune\Database\Query\AbstractQuery;
use Neptune\Database\Query\MysqlQuery;

require_once __DIR__ . '/../../../../bootstrap.php';

class UpperCase extends Entity
{
    protected static $fields = array('id', 'name', 'column');
    protected static $primary_key = 'id';
    protected static $table = 'table';

    public function setName($name)
    {
        return strtoupper($name);
    }

    public function getColumn()
    {
        return strtoupper($this->values['column']);
    }

}

/**
 * EntityTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class EntityTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;
    protected $database;

    public function setUp()
    {
        $this->database = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->obj = new UpperCase($this->database);
    }

    public function testIsRelatable()
    {
        $this->assertInstanceOf('\Neptune\Database\Entity\AbstractEntity', $this->obj);
    }

    public function testGetAndSetRaw()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->setRaw('column', 'test');
        $this->assertEquals('test', $this->obj->getRaw('column'));
        $this->obj->setRaw('name', 'test');
        $this->assertEquals('test', $this->obj->getRaw('name'));
    }

    public function testGetAndSet()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->set('column', 'test');
        $this->assertEquals('TEST', $this->obj->get('column'));
        $this->obj->set('name', 'test');
        $this->assertEquals('TEST', $this->obj->get('name'));
    }

    public function test__GetAnd__Set()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->column = 'test';
        $this->assertEquals('TEST', $this->obj->column);
        $this->obj->name = 'test';
        $this->assertEquals('TEST', $this->obj->name);
    }

    public function testGetFromResultSet()
    {
        $this->obj = new UpperCase($this->database, array('id' => 1, 'name' => 'test', 'column' => 'value'));
        $this->assertEquals(1, $this->obj->id);
        //no set methods should be called as it most likely
        //comes from a db query
        $this->assertEquals('test', $this->obj->name);
        $this->assertEquals('test', $this->obj->get('name'));
        $this->assertEquals('test', $this->obj->getRaw('name'));
        //but get method is called when retrieving a value
        $this->assertEquals('VALUE', $this->obj->column);
        $this->assertEquals('VALUE', $this->obj->get('column'));
        $this->assertEquals('value', $this->obj->getRaw('column'));
    }

    public function testGetAndSetValues()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->setValues(array('name' => 'test', 'column' => 'value'));
        //set methods should have been called in setValues
        $this->assertEquals('TEST', $this->obj->getRaw('name'));
        $this->assertEquals('value', $this->obj->getRaw('column'));
        //get methods should be called in getValues
        $expected = array('name' => 'TEST', 'column' => 'VALUE');
        $this->assertEquals($expected, $this->obj->getValues());
    }

    public function testGetAndSetValuesRaw()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->setValuesRaw(array('name' => 'test', 'column' => 'value'));
        //set methods should not have been called in setValuesRaw
        $this->assertEquals('test', $this->obj->getRaw('name'));
        $this->assertEquals('value', $this->obj->getRaw('column'));
        //get methods should not be called in getValuesRaw
        $expected = array('name' => 'test', 'column' => 'value');
        $this->assertEquals($expected, $this->obj->getValuesRaw());
    }

    protected function expectQuery($type)
    {
        $query = $this->getMockBuilder('Neptune\Database\Query\MysqlQuery')
                      ->disableOriginalConstructor()
                      ->getMock();
        $this->database->expects($this->once())
                       ->method($type)
                       ->will($this->returnValue($query));

        return $query;
    }

    protected function queryExpects(AbstractQuery $query, $method, $args = array())
    {
        $args = (array) $args;
        $mocker = $query->expects($this->once())
                        ->method($method)
                        ->will($this->returnValue($query));
        //add expected arguments. fields actually expects an array so call it differently.
        if ($method === 'fields') {
            call_user_func(array($mocker, 'with'), $args);
        } else {
            call_user_func_array(array($mocker, 'with'), $args);
        }
    }

    protected function queryExpectsPrepare(AbstractQuery $query)
    {
        $statement = $this->getMock('\PDOStatement');
        $query->expects($this->once())
              ->method('prepare')
              ->will($this->returnValue($statement));

        return $statement;
    }

    public function testInsertBuild()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->id = 1;
        $this->obj->column = 'value';
        $query = $this->expectQuery('insert');
        $this->queryExpects($query, 'into', 'table');
        $this->queryExpects($query, 'fields', array('id', 'column'));
        $statement = $this->queryExpectsPrepare($query);
        $statement->expects($this->once())
                  ->method('execute')
        /* INSERT INTO table (id, column) VALUES (1, value) */
                  ->with(array(1, 'value'));
        $this->obj->save();
    }

    public function testUpdateBuild()
    {
        $this->obj = new UpperCase($this->database, array('id' => 1, 'column' => 'value'));
        $this->obj->column = 'changed';
        $query = $this->expectQuery('update');
        $this->queryExpects($query, 'tables', 'table');
        $statement = $this->queryExpectsPrepare($query);
        $statement->expects($this->once())
                  ->method('execute')
        /* UPDATE table SET column = changed WHERE id = 1 */
                  ->with(array('changed', 1));
        $this->obj->setStored();
        $this->obj->save();
    }

    public function testNoUpdate()
    {
        $this->obj = new UpperCase($this->database, array('id' => 1, 'column' => 'value'));
        $this->database->expects($this->never())
                       ->method('update');
        $this->database->expects($this->never())
                       ->method('insert');
        $this->obj->setStored();
        $this->obj->save();
    }

    public function testNoInsert()
    {
        $this->obj = new UpperCase($this->database);
        $this->database->expects($this->never())
                       ->method('update');
        $this->database->expects($this->never())
                       ->method('insert');
        $this->obj->save();
    }

    public function testNoUpdateDifferentFields()
    {
        $this->obj = new UpperCase($this->database, array('id' => 1, 'column' => 'value'));
        $this->obj->foo = 'bar';
        $this->database->expects($this->never())
                       ->method('update');
        $this->database->expects($this->never())
                       ->method('insert');
        $this->obj->setStored();
        $this->obj->save();
    }

    public function testNoInsertDifferentFields()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->foo = 'bar';
        $this->database->expects($this->never())
                       ->method('update');
        $this->database->expects($this->never())
                       ->method('insert');
        $this->obj->save();
    }

    public function testPrimaryKeyIsUpdatedCorrectly()
    {
        $this->obj = new UpperCase($this->database, array('id' => 1, 'column' => 'value'));
        $this->obj->column = 'changed';
        $this->obj->id = 2;
        $query = $this->expectQuery('update');
        $this->queryExpects($query, 'tables', 'table');
        $statement = $this->queryExpectsPrepare($query);
        $statement->expects($this->once())
                  ->method('execute')
        /* UPDATE table SET column = changed, id = 2 WHERE id = 1 */
                  ->with(array('changed', 2, 1));
        $this->obj->setStored();
        $this->obj->save();
    }

    public function testPrimaryKeyUpdatedOnInsert()
    {
        $this->obj = new UpperCase($this->database);
        $this->obj->id = 1;
        $this->obj->id = 3;
        $this->obj->column = 'value';
        $query = $this->expectQuery('insert');
        $this->queryExpects($query, 'into', 'table');
        $statement = $this->queryExpectsPrepare($query);
        $statement->expects($this->once())
                  ->method('execute')
        /* INSERT INTO table (id, column) VALUES (3, value) */
                  ->with(array(3, 'value'));
        $this->obj->save();
    }

}
