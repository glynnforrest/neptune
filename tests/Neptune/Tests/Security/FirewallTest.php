<?php

namespace Neptune\Tests\Security;

require_once __DIR__ . '/../../../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Neptune\Security\Firewall;
use Neptune\Security\Driver\FailDriver;

/**
 * FirewallTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FirewallTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->firewall = new Firewall(new FailDriver());
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
        $request = $this->createRequest('foo');
        $this->assertTrue($this->firewall->check($request));
    }

    public function testCheckWithUrlRule()
    {
        $matcher = $this->createMatcher('foo');
        $this->firewall->addRule($matcher, 'WHATEVER');
        $this->assertFalse($this->firewall->check($this->createRequest('foo')));
        $this->assertTrue($this->firewall->check($this->createRequest('something-else')));
    }

}