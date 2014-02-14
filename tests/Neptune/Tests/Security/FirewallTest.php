<?php

namespace Neptune\Tests\Security;

require_once __DIR__ . '/../../../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Neptune\Security\Firewall;

/**
 * FirewallTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FirewallTest extends \PHPUnit_Framework_TestCase
{

    protected $driver;
    protected $firewall;

    public function setUp()
    {
        $this->driver = $this->getMock('\Neptune\Security\Driver\SecurityDriverInterface');
        $this->firewall = new Firewall($this->driver);
    }

    protected function createRequest($url)
    {
        return Request::create($url);
    }

    protected function createMatcher($path = null, $host = null, $method = null, $ip = null)
    {
        return new RequestMatcher($path, $host, $method, $ip);
    }

    public function testCheckWithNoRules()
    {
        $this->assertTrue($this->firewall->check($this->createRequest('/foo')));
    }

    public function testCheckWithUrlRule()
    {
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'WHATEVER');
        $this->driver->expects($this->once())
                     ->method('hasPermission')
                     ->with('WHATEVER')
                     ->will($this->returnValue(false));
        $this->assertTrue($this->firewall->check($this->createRequest('/bar')));
        $this->setExpectedException('Neptune\Security\Exception\AccessDeniedException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testAnyAllowsLoginOnly()
    {
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'ANY');
        $this->assertTrue($this->firewall->check($this->createRequest('/foo')));
        $this->assertTrue($this->firewall->check($this->createRequest('/bar')));
    }

    public function testNoneBlocksAll()
    {
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'NONE');
        $this->setExpectedException('Neptune\Security\Exception\AccessDeniedException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testAnyIsConfigurable()
    {
        $firewall = new Firewall($this->driver, 'GO_AHEAD');
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $matcher = $this->createMatcher('/foo');
        $firewall->addRule($matcher, 'GO_AHEAD');
        $this->assertTrue($firewall->check($this->createRequest('/foo')));
    }

    public function testNoneIsConfigurable()
    {
        $firewall = new Firewall($this->driver, 'ANY', 'NO_WAY');
        $matcher = $this->createMatcher('/foo');
        $firewall->addRule($matcher, 'NO_WAY');
        $this->setExpectedException('Neptune\Security\Exception\AccessDeniedException');
        $firewall->check($this->createRequest('/foo'));
    }

}