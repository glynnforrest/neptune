<?php

namespace Neptune\Tests\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Database\DatabaseFactory;
use Neptune\Core\Config;

/**
 * DatabaseFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DatabaseFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->config = Config::create('neptune');

        $this->config->set('database.mysql', array(
            'driver' => 'mysql',
            'database' => 'testing',
            'host' => 'example.org',
            'port' => '100',
            'user' => 'user',
            'pass' => 'pass',
            'charset' => 'utf8'
        ));

        $this->neptune = $this->getMockBuilder('\Neptune\Core\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->creator = $this->getMock('\Neptune\Database\Driver\PDOCreator');

        $this->factory = new DatabaseFactory($this->config, $this->neptune, $this->creator);
    }

    public function tearDown()
    {
        Config::unload();
    }

    public function testGetDefaultDriver()
    {
        $pdo = new PDOStub();
        $this->creator->expects($this->once())
                      ->method('createPDO')
                      ->with('mysql:host=example.org;port=100;dbname=testing;charset=utf8', 'user', 'pass')
                      ->will($this->returnValue($pdo));
        $driver = $this->factory->get();
        $this->assertInstanceOf('\Neptune\Database\Driver\PDODriver', $driver);
        $this->assertSame($driver, $this->factory->get());
    }

    public function testGetMysqlDriver()
    {
        $pdo = new PDOStub();
        $this->creator->expects($this->once())
                      ->method('createPDO')
                      ->with('mysql:host=example.org;port=100;dbname=testing;charset=utf8', 'user', 'pass')
                      ->will($this->returnValue($pdo));
        $driver = $this->factory->get('mysql');
        $this->assertInstanceOf('\Neptune\Database\Driver\PDODriver', $driver);
        $this->assertSame($driver, $this->factory->get());
    }

    public function testGetMysqlDriverDefaultValues()
    {
        //host, port and charset can be optional. They default to
        //localhost, 3306 and UTF8.
        $this->config->set('database.mysql', array(
            'driver' => 'mysql',
            'database' => 'testing',
            'user' => 'user',
            'pass' => 'pass',
        ));
        $pdo = new PDOStub();
        $this->creator->expects($this->once())
                      ->method('createPDO')
                      ->with('mysql:host=localhost;port=3306;dbname=testing;charset=UTF8', 'user', 'pass')
                      ->will($this->returnValue($pdo));
        $driver = $this->factory->get('mysql');
        $this->assertInstanceOf('\Neptune\Database\Driver\PDODriver', $driver);
        $this->assertSame($driver, $this->factory->get());
    }

}
