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
        $this->firewall = new Firewall('testing', $this->driver);
    }

    protected function createRequest($url)
    {
        return Request::create($url);
    }

    protected function createMatcher($path = null, $host = null, $method = null, $ip = null)
    {
        return new RequestMatcher($path, $host, $method, $ip);
    }

    public function testNoRules()
    {
        //false means the request has passed - no exceptions were thrown
        //true is reserved for explicit access - the request matching an exemption
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
    }

    public function testNotLoggedIn()
    {
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'WHATEVER');
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->with()
                     ->will($this->returnValue(false));
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
        $this->setExpectedException('Neptune\Security\Exception\AuthenticationException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testLoggedIn()
    {
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'WHATEVER');
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->with()
                     ->will($this->returnValue(true));
        $this->driver->expects($this->once())
                     ->method('hasPermission')
                     ->with('WHATEVER')
                     ->will($this->returnValue(false));
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
        $this->setExpectedException('Neptune\Security\Exception\AuthorizationException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testAny()
    {
        $this->driver->expects($this->never())
                     ->method('isAuthenticated');
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'ANY');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
    }

    public function testNone()
    {
        $this->driver->expects($this->never())
                     ->method('isAuthenticated');
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'NONE');
        $this->setExpectedException('Neptune\Security\Exception\AuthorizationException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testUserNotLoggedIn()
    {
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(false));
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'USER');
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
        $this->setExpectedException('Neptune\Security\Exception\AuthenticationException');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
    }

    public function testUserLoginOnly()
    {
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'USER');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
    }

    public function testAnonNotLoggedIn()
    {
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(false));
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'ANON');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
    }

    public function testAnonLoggedIn()
    {
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'ANON');
        $this->assertFalse($this->firewall->check($this->createRequest('/bar')));
        $this->setExpectedException('Neptune\Security\Exception\AnonymousException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function namesProvider()
    {
        return array(
            array('YES', 'NO', 'AUTH', 'NOBODY', array('YES', 'NO', 'AUTH', 'NOBODY')),
            array(null, 'NO', 'AUTH', 'NOBODY', array('ANY', 'NO', 'AUTH', 'NOBODY')),
            array('YES', null, 'AUTH', 'NOBODY', array('YES', 'NONE', 'AUTH', 'NOBODY')),
            array('YES', 'NO', null, 'NOBODY', array('YES', 'NO', 'USER', 'NOBODY')),
            array('YES', 'NO', 'AUTH', null, array('YES', 'NO', 'AUTH', 'ANON')),
            array(null, null, null, null, array('ANY', 'NONE', 'USER', 'ANON')),
        );
    }

    /**
     * @dataProvider namesProvider()
     */
    public function testGetAndSetPermissionNames($allow, $block, $user, $anon, $expected)
    {
        $this->firewall->setPermissionNames($allow, $block, $user, $anon);
        $this->assertSame($expected, $this->firewall->getPermissionNames());
    }

    public function testSetPermissionNamesReusesOldNames()
    {
        $this->firewall->setPermissionNames(null, null, null, null);
        $this->assertSame(array('ANY', 'NONE', 'USER', 'ANON'), $this->firewall->getPermissionNames());

        $this->firewall->setPermissionNames('ok', 'no', 'logged-in', 'anyone');
        $this->assertSame(array('ok', 'no', 'logged-in', 'anyone'), $this->firewall->getPermissionNames());

        $this->firewall->setPermissionNames(null, null, null, null);
        //we want the names we've defined, not the default
        $this->assertSame(array('ok', 'no', 'logged-in', 'anyone'), $this->firewall->getPermissionNames());
    }

    public function testAnyIsConfigurable()
    {
        $this->firewall->setPermissionNames('GO_AHEAD', null, null, null);
        $this->driver->expects($this->never())
                     ->method('isAuthenticated');
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'GO_AHEAD');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
    }

    public function testNoneIsConfigurable()
    {
        $this->firewall->setPermissionNames(null, 'NO_WAY', null, null);
        $matcher = $this->createMatcher('/foo');
        $this->driver->expects($this->never())
                     ->method('isAuthenticated');
        $this->firewall->addRule($matcher, 'NO_WAY');
        $this->setExpectedException('Neptune\Security\Exception\AuthorizationException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testUserIsConfigurable()
    {
        $this->firewall->setPermissionNames(null, null, 'USERS_ONLY', null);
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'USERS_ONLY');
        $this->setExpectedException('Neptune\Security\Exception\AuthenticationException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testAnonIsConfigurable()
    {
        $this->firewall->setPermissionNames(null, null, null, 'WHOEVER');
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(false));
        $matcher = $this->createMatcher('/foo');
        $this->firewall->addRule($matcher, 'WHOEVER');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
    }

    public function testMultipleRulesCanBeUsed()
    {
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $this->driver->expects($this->once())
                     ->method('hasPermission')
                     ->with('ADMIN')
                     ->will($this->returnValue(false));
        $this->firewall->addRule($this->createMatcher('/bar'), 'ALLOW');
        $this->firewall->addRule($this->createMatcher('/foo'), 'ADMIN');
        $this->setExpectedException('Neptune\Security\Exception\AuthorizationException');
        $this->firewall->check($this->createRequest('/foo'));
    }

    public function testSameUrlCanBeUsedTwice()
    {
        $this->driver->expects($this->exactly(2))
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $this->driver->expects($this->once())
                     ->method('hasPermission')
                     ->with('ADMIN')
                     ->will($this->returnValue(true));
        $this->firewall->addRule($this->createMatcher('/foo'), 'USER');
        $this->firewall->addRule($this->createMatcher('/foo'), 'ADMIN');
        $this->assertFalse($this->firewall->check($this->createRequest('/foo')));
    }

    public function testExemptions()
    {
        //here is a typical 'protect all except the login page' setup
        $this->firewall->addRule($this->createMatcher('/'), 'USER');
        $this->firewall->addExemption($this->createMatcher('/login'), 'ANON');

        // /login should pass and be exempt
        $this->assertTrue($this->firewall->check($this->createRequest('/login')));

        // /account will fail - not authenticated
        $this->driver->expects($this->once())
                     ->method('isAuthenticated')
                     ->will($this->returnValue(false));
        $this->setExpectedException('Neptune\Security\Exception\AuthenticationException');
        $this->firewall->check($this->createRequest('/account'));
    }

    public function testFailedExemptionDoesNotFailFirewall()
    {
        // VIP permission may access /vip, but ADMIN permission also
        // gets in, even without VIP permission
        $this->firewall->addExemption($this->createMatcher('/'), 'ADMIN');
        $this->firewall->addRule($this->createMatcher('/vip'), 'VIP');

        // this request is not ADMIN, but it is VIP. the
        // exemption shouldn't stop the rule from passing
        $this->driver->expects($this->exactly(2))
                     ->method('isAuthenticated')
                     ->will($this->returnValue(true));
        $this->driver->expects($this->exactly(2))
                     ->method('hasPermission')
                     ->will($this->onConsecutiveCalls(false, true));
        //explanation of the indexes used in at() for the driver
        // 0 setRequest()
        // 1 isAuthenticated()
        // 2 hasPermission('ADMIN')
        // 3 setRequest()
        // 4 isAuthenticated()
        // 5 hasPermission('VIP')
        $this->driver->expects($this->at(2))
                     ->method('hasPermission')
                     ->with('ADMIN');
        $this->driver->expects($this->at(5))
                     ->method('hasPermission')
                     ->with('VIP');

        $this->assertFalse($this->firewall->check($this->createRequest('/vip')));
    }


}