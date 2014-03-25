<?php

namespace Neptune\Tests\Database\Driver;

use Neptune\Database\Driver\DebugDriver;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * DebugDriverTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock_driver = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->driver = new DebugDriver($this->mock_driver, $this->dispatcher);
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

}
