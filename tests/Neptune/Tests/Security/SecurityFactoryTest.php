<?php

namespace Neptune\Tests\Security;

use Neptune\Security\SecurityFactory;
use Neptune\Config\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * SecurityFactoryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityFactoryTest extends \PHPUnit_Framework_TestCase
{

    protected $neptune;
    protected $config;
    protected $factory;

    public function setUp()
    {
        $this->config = new Config('neptune');

        $this->config->set('security.drivers.driver1', array(
            'driver' => 'pass',
        ));

        $this->config->set('security.drivers.driver2', array(
            'driver' => 'fail',
        ));

        $this->neptune = $this->getMockBuilder('\Neptune\Core\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->factory = new SecurityFactory($this->config, $this->neptune);
    }

    public function testGetDefaultDriver()
    {
        $driver = $this->factory->get();
        $this->assertInstanceOf('Blockade\Driver\PassDriver', $driver);
        $this->assertSame($driver, $this->factory->get());
    }

    public function testGetPassDriver()
    {
        $driver = $this->factory->get('driver1');
        $this->assertInstanceOf('Blockade\Driver\PassDriver', $driver);
        $this->assertSame($driver, $this->factory->get('driver1'));
    }

    public function testGetFailDriver()
    {
        $driver = $this->factory->get('driver2');
        $this->assertInstanceOf('Blockade\Driver\FailDriver', $driver);
        $this->assertSame($driver, $this->factory->get('driver2'));
    }

    public function testGetNoDefinition()
    {
        $this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
        $this->factory->get('wrong');
    }

    public function testGetDefaultNoConfig()
    {
        $this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
        $factory = new SecurityFactory(new Config('empty'), $this->neptune);
        $factory->get();
    }

    public function testGetNoDriver()
    {
        $this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
        $this->config->set('security.wrong', array(
            //no driver
        ));
        $this->factory->get('wrong');
    }

    public function testGetUndefinedDriver()
    {
        $this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
        $this->config->set('security.drivers.unknown', array('driver' => 'unicorn'));
        $this->factory->get('unknown');
    }

    public function testGetDriverAsAService()
    {
        $driver = $this->getMock('Blockade\Driver\DriverInterface');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('service.foo')
                      ->will($this->returnValue($driver));
        $this->config->set('security.drivers.foo', 'service.foo');
        $this->assertSame($driver, $this->factory->get('foo'));
    }

    public function testGetDriverAsAServiceThrowsException()
    {
        $driver = new \stdClass();
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('service.foo')
                      ->will($this->returnValue($driver));
        $this->config->set('security.drivers.foo', 'service.foo');
        $msg = "Security driver 'foo' requested service 'service.foo' which does not implement Blockade\Driver\DriverInterface";
        $this->setExpectedException('\Neptune\Exceptions\DriverNotFoundException', $msg);
        $this->factory->get('foo');
    }

    public function testRequestIsAssignedToDriver()
    {
        $driver = $this->getMock('Blockade\Driver\DriverInterface');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('service.foo')
                      ->will($this->returnValue($driver));
        $this->config->set('security.drivers.foo', 'service.foo');

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->factory->setRequest($request);

        $driver->expects($this->once())
               ->method('setRequest')
               ->with($request);

        $this->factory->get('foo');
    }

    public function testRequestIsNotAssignedToDriverWhenAlreadySet()
    {
        $driver = $this->getMock('Blockade\Driver\DriverInterface');
        $this->neptune->expects($this->once())
                      ->method('offsetGet')
                      ->with('service.foo')
                      ->will($this->returnValue($driver));
        $this->config->set('security.drivers.foo', 'service.foo');

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->factory->setRequest($request);

        $driver->expects($this->once())
               ->method('hasRequest')
               ->will($this->returnValue(true));

        $driver->expects($this->never())
               ->method('setRequest');

        $this->factory->get('foo');
    }

}
