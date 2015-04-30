<?php

namespace Neptune\Tests\Config\Processor;

use Neptune\Config\Processor\EnvironmentProcessor;
use Neptune\Config\Config;

/**
 * EnvironmentProcessorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvironmentProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->processor = new EnvironmentProcessor($this->neptune);
    }

    public function testVariablesAreAdded()
    {
        $config = new Config();
        $this->neptune->expects($this->once())
            ->method('getRootDirectory')
            ->will($this->returnValue('/path/to/app'));
        $this->neptune->expects($this->once())
            ->method('getEnv')
            ->will($this->returnValue('dev'));
        $this->processor->onPostMerge($config);
        $this->assertSame([
            'ROOT' => '/path/to/app',
            'ENV' => 'dev',
        ], $config->get());
    }
}
