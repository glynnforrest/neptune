<?php

namespace Neptune\Tests\Database\Driver;

use Neptune\Database\Driver\PDODriver;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * PDODriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class PDODriverTest extends \PHPUnit_Framework_TestCase
{

    protected $pdo;
    protected $driver;

    public function setUp()
    {
        $this->pdo = $this->getMock('Neptune\Tests\Database\PDOStub');
        $this->driver = new PDODriver($this->pdo);
        $this->driver->setQueryClass('Neptune\Database\Query\MysqlQuery');
    }

    public function testPrepare()
    {
        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->with('delete from foo');
        $this->driver->prepare('delete from foo');
    }

    public function testQuote()
    {
        $this->pdo->expects($this->once())
                  ->method('quote')
                  ->with('foo');
        $this->driver->quote('foo');
    }

    public function testGetAndSetQueryClass()
    {
        $this->assertSame('Neptune\Database\Query\MysqlQuery', $this->driver->getQueryClass());
        $this->assertSame($this->driver, $this->driver->setQueryClass('Query'));
        $this->assertSame('Query', $this->driver->getQueryClass());
    }

    public function testSelect()
    {
        $query = $this->driver->select();
        $this->assertInstanceOf('Neptune\Database\Query\MysqlQuery', $query);
        $this->assertSame('SELECT', $query->getType());
    }

    public function testInsert()
    {
        $query = $this->driver->insert();
        $this->assertInstanceOf('Neptune\Database\Query\MysqlQuery', $query);
        $this->assertSame('INSERT', $query->getType());
    }

    public function testUpdate()
    {
        $query = $this->driver->update();
        $this->assertInstanceOf('Neptune\Database\Query\MysqlQuery', $query);
        $this->assertSame('UPDATE', $query->getType());
    }

    public function testDelete()
    {
        $query = $this->driver->delete();
        $this->assertInstanceOf('Neptune\Database\Query\MysqlQuery', $query);
        $this->assertSame('DELETE', $query->getType());
    }

    public function testGetRelationManager()
    {
        $manager1 = $this->driver->getRelationManager();
        $this->assertInstanceOf('\Neptune\Database\Relation\RelationManager', $manager1);
        $manager2 = $this->driver->getRelationManager();
        $this->assertInstanceOf('\Neptune\Database\Relation\RelationManager', $manager2);
        $this->assertSame($manager1, $manager2);
    }

}