<?php

namespace Neptune\Tests\Security;

use Neptune\Security\SecurityFactory;
use Neptune\Config\Config;

use Temping\Temping;

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
        $this->assertInstanceOf('\Neptune\Security\Driver\PassDriver', $driver);
        $this->assertSame($driver, $this->factory->get());
    }

    public function testGetPassDriver()
    {
        $driver = $this->factory->get('driver1');
        $this->assertInstanceOf('\Neptune\Security\Driver\PassDriver', $driver);
        $this->assertSame($driver, $this->factory->get('driver1'));
    }

    public function testGetFailDriver()
    {
        $driver = $this->factory->get('driver2');
        $this->assertInstanceOf('\Neptune\Security\Driver\FailDriver', $driver);
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
        $driver = $this->getMock('\\Neptune\\Security\\Driver\\SecurityDriverInterface');
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
        $msg = "Security driver 'foo' requested service 'service.foo' which does not implement Neptune\Security\Driver\SecurityDriverInterface";
        $this->setExpectedException('\Neptune\Exceptions\DriverNotFoundException');
        $this->factory->get('foo');
    }

}
