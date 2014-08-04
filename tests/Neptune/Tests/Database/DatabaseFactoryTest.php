<?php

namespace Neptune\Tests\Database;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Database\DatabaseFactory;
use Neptune\Config\Config;
use Neptune\Core\Neptune;

/**
 * DatabaseFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DatabaseFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->config = new Config('neptune');

        $this->config->set('database.mysql', array(
            'driver' => 'pdo_mysql',
            'database' => 'testing',
            'host' => 'example.org',
            'port' => '100',
            'user' => 'user',
            'pass' => 'pass',
            'charset' => 'utf8'
        ));

        $this->neptune = new Neptune('/root/app');
        $this->neptune['config'] = $this->config;

        $this->factory = new DatabaseFactory($this->config, $this->neptune);
    }

    public function testGetDefaultDriver()
    {
        $connection = $this->factory->get();
        $this->assertInstanceOf('Doctrine\DBAL\Connection', $connection);
        $this->assertSame($connection, $this->factory->get());
        $this->assertInstanceOf('Doctrine\DBAL\Driver\PDOMysql\Driver', $connection->getDriver());
    }

    public function testDriverAsService()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->neptune['my_db'] = $connection;
        $this->config->set('database.custom', 'my_db');
        $this->assertSame($connection, $this->factory->get('custom'));
    }

    public function testInvalidServiceThrowsException()
    {
        $this->neptune['my_db'] = new \stdClass();
        $this->config->set('database.custom', 'my_db');
        $msg = "The database 'custom' requested service 'my_db' which is not an instance of Doctrine\DBAL\Connection";
        $this->setExpectedException('Neptune\Exceptions\DriverNotFoundException', $msg);
        $this->factory->get('custom');
    }

    public function testGetMysqlDriver()
    {
        $connection = $this->factory->get('mysql');
        $this->assertInstanceOf('Doctrine\DBAL\Connection', $connection);
        $this->assertSame($connection, $this->factory->get('mysql'));
        $this->assertInstanceOf('Doctrine\DBAL\Driver\PDOMysql\Driver', $connection->getDriver());
    }

    public function testGetMysqlDriverDefaultValues()
    {
        //host, port and charset can be optional. They default to
        //localhost, 3306 and UTF8.
        $this->config->set('database.mysql', array(
            'driver' => 'pdo_mysql',
            'database' => 'testing',
            'user' => 'user',
            'pass' => 'pass',
        ));
        $connection = $this->factory->get('mysql');
        $this->assertInstanceOf('Doctrine\DBAL\Connection', $connection);
        $this->assertSame($connection, $this->factory->get('mysql'));
        $this->assertInstanceOf('Doctrine\DBAL\Driver\PDOMysql\Driver', $connection->getDriver());
    }

}
