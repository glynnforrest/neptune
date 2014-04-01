<?php

namespace Neptune\Tests\Database\Driver;

use Neptune\Database\Driver\EventDriver;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * EventDriverTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class EventDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock_driver = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->driver = new EventDriver($this->mock_driver, $this->dispatcher);
    }

    public function testPrepare()
    {
        $this->mock_driver->expects($this->once())
                     ->method('prepare')
                     ->with('select * from foo');
        $this->dispatcher->expects($this->once())
                         ->method('hasListeners')
                         ->will($this->returnValue(true));
        $this->dispatcher->expects($this->once())
                         ->method('dispatch');
        $this->driver->prepare('select * from foo');
    }

    public function queryMethodsProvider()
    {
        return array(
            array('select'),
            array('insert'),
            array('update'),
            array('delete'),
        );
    }

    /**
     * @dataProvider queryMethodsProvider()
     */
    public function testQueryIsGivenCorrectDatabaseDriver($method)
    {
        $query = $this->getMockBuilder('Neptune\Database\Query\MysqlQuery')
                      ->disableOriginalConstructor()
                      ->getMock();
        $this->mock_driver->expects($this->once())
                          ->method($method)
                          ->will($this->returnValue($query));
        $query->expects($this->once())
              ->method('setDatabase')
              ->with($this->driver)
              ->will($this->returnValue($query));
        $this->assertSame($query, $this->driver->$method());
    }

}
