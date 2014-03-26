<?php

namespace Neptune\Tests\Database\Entity;

use Neptune\Database\Query\AbstractQuery;
use Neptune\Database\Query\MysqlQuery;

use Neptune\Tests\Database\Fixtures\AuthorCollection;
use Neptune\Tests\Database\Fixtures\Author;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * EntityCollectionTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class EntityCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $database;

    public function setUp()
    {
        $this->database = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->collection = new AuthorCollection($this->database);
        $this->collection->setTable(Author::getTable());
        $this->collection->setFields(Author::getFields());
        $this->collection->setPrimaryKey('id');
        $this->collection->setEntityClass('Neptune\Tests\Database\Fixtures\Author');
    }

    public function testIsRelatable()
    {
        $this->assertInstanceOf('\Neptune\Database\Entity\AbstractEntity', $this->collection);
    }

    public function testGetAndSetRaw()
    {
        $this->collection->setRaw('foo', 'bar');
        $this->assertSame('bar', $this->collection->getRaw('foo'));
        $this->collection->setRaw('bar', 'baz');
        $this->assertSame('baz', $this->collection->getRaw('bar'));
    }

    public function testGetAndSet()
    {
        $this->collection->set('first_name', 'foo');
        $this->assertSame('FOO', $this->collection->getRaw('first_name'));
        $this->assertSame('FOO', $this->collection->get('first_name'));
        $this->collection->setRaw('last_name', 'bar');
        $this->assertSame('bar', $this->collection->getRaw('last_name'));
        $this->assertSame('BAR', $this->collection->get('last_name'));
    }

    public function test__GetAnd__Set()
    {
        $this->collection->first_name = 'foo';
        $this->assertSame('FOO', $this->collection->getRaw('first_name'));
        $this->assertSame('FOO', $this->collection->first_name);
        $this->collection->setRaw('last_name', 'bar');
        $this->assertSame('bar', $this->collection->getRaw('last_name'));
        $this->assertSame('BAR', $this->collection->last_name);
    }

    public function testGetAndSetValues()
    {
        $this->collection->setValues(array('first_name' => 'foo', 'last_name' => 'bar'));
        //set methods should have been called in setValues
        $this->assertSame('FOO', $this->collection->getRaw('first_name'));
        $this->assertSame('bar', $this->collection->getRaw('last_name'));
        //get methods should be called in getValues
        $expected = array('first_name' => 'FOO', 'last_name' => 'BAR');
        $this->assertSame($expected, $this->collection->getValues());
    }

    public function testGetAndSetValuesRaw()
    {
        $this->collection->setValuesRaw(array('first_name' => 'foo', 'last_name' => 'bar'));
        //set methods should not have been called in setValuesRaw
        $this->assertSame('foo', $this->collection->getRaw('first_name'));
        $this->assertSame('bar', $this->collection->getRaw('last_name'));
        //get methods should not be called in getValuesRaw
        $expected = array('first_name' => 'foo', 'last_name' => 'bar');
        $this->assertSame($expected, $this->collection->getValuesRaw());
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
        $this->collection[] = new Author($this->database);
        $this->collection[] = new Author($this->database);
        $this->collection->first_name = 'foo';
        $this->collection->last_name = 'bar';
        $query = $this->expectQuery('insert');
        $this->queryExpects($query, 'into', 'authors');
        $this->queryExpects($query, 'fields', array('first_name', 'last_name'));
        $statement = $this->queryExpectsPrepare($query);
        $statement->expects($this->exactly(2))
                  ->method('execute')
        /* INSERT INTO authors (first_name, last_name) VALUES ('foo', 'bar') */
        /* INSERT INTO authors (first_name, last_name) VALUES ('foo', 'bar') */
                  ->with(array('FOO', 'bar'));
        $this->collection->save();
    }

    public function testNoInsert()
    {
        $this->database->expects($this->never())
                       ->method('insert');
        $this->database->expects($this->never())
                       ->method('update');
        $this->collection->save();
    }

    public function testUpdateBuild()
    {
        $this->collection[] = new Author($this->database, array('id' => 1));
        $this->collection[] = new Author($this->database, array('id' => 2));
        $this->collection->first_name = 'foo';
        $this->collection->last_name = 'bar';
        $query = $this->expectQuery('update');
        $this->queryExpects($query, 'tables', 'authors');
        $this->queryExpects($query, 'fields', array('first_name', 'last_name'));
        $statement = $this->queryExpectsPrepare($query);
        $statement->expects($this->exactly(2))
                  ->method('execute')
        /* UPDATE authors SET first_name = 'FOO', last_name = 'bar' WHERE id = 1 */
        /* UPDATE authors SET first_name = 'FOO', last_name = 'bar' WHERE id = 2 */
                  ->with($this->logicalOr(
                      $this->equalTo(array('FOO', 'bar', 1)),
                      $this->equalTo(array('FOO', 'bar', 2))
                  ));
        $this->collection->setStored();
        $this->collection->save();
    }

    public function testNoUpdate()
    {
        $this->database->expects($this->never())
                       ->method('insert');
        $this->database->expects($this->never())
                       ->method('update');
        $this->collection->setStored();
        $this->collection->save();
    }

    public function testNoUpdateDifferentFields()
    {
        $this->collection->bar = 'bar';
        $this->database->expects($this->never())
                       ->method('update');
        $this->database->expects($this->never())
                       ->method('insert');
        $this->collection->setStored();
        $this->collection->save();
    }

    public function testNoInsertDifferentFields()
    {
        $this->collection->bar = 'bar';
        $this->database->expects($this->never())
                       ->method('update');
        $this->database->expects($this->never())
                       ->method('insert');
        $this->collection->save();
    }

}
