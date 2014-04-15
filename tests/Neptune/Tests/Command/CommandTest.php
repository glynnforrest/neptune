<?php

namespace Neptune\Tests\Command;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Core\Neptune;

/**
 * CommandTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CommandTest extends \PHPUnit_Framework_TestCase
{
    protected $neptune;
    protected $command;

    public function setup()
    {
        $this->neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->command = new EmptyCommand($this->neptune);
    }

    public function testGetRootDirectory()
    {
        $this->neptune->expects($this->once())
                      ->method('getRootDirectory')
                      ->will($this->returnValue('/path/to/dir/'));
        $this->assertSame('/path/to/dir/', $this->command->getRootDirectory());
    }

    public function testGetModuleDirectory()
    {
        $this->neptune->expects($this->once())
                      ->method('getModuleDirectory')
                      ->with('my-app')
                      ->will($this->returnValue('/path/to/module'));
        $this->assertSame('/path/to/module', $this->command->getModuleDirectory('my-app'));
    }

    public function testGetDefaultModule()
    {
        $this->neptune->expects($this->once())
                      ->method('getDefaultModule')
                      ->will($this->returnValue('my-app'));
        $this->assertSame('my-app', $this->command->getDefaultModule());
    }

    public function testGetModuleNamespace()
    {
        $this->neptune->expects($this->once())
                      ->method('getModuleNamespace')
                      ->with('my-app')
                      ->will($this->returnValue('MyApp'));
        $this->assertSame('MyApp', $this->command->getModuleNamespace('my-app'));
    }

}
