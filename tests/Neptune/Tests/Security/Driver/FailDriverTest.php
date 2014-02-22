<?php

namespace Neptune\Tests\Security\Driver;

require_once __DIR__ . '/../../../../bootstrap.php';

use Neptune\Security\Driver\FailDriver;

/**
 * FailDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FailDriverTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->driver = new FailDriver();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('\Neptune\Security\Driver\SecurityDriverInterface', $this->driver);
        $this->assertInstanceOf('\Neptune\Security\Driver\AbstractSecurityDriver', $this->driver);
    }

    public function testAuthenticate()
    {
        $this->assertFalse($this->driver->authenticate());
    }

    public function testLogin()
    {
        $this->assertFalse($this->driver->login('username'));
    }

    public function testLogout()
    {
        $this->assertFalse($this->driver->logout());
    }

    public function testIsAuthenticated()
    {
        $this->assertFalse($this->driver->isAuthenticated());
    }

    public function testHasPermission()
    {
        $this->assertFalse($this->driver->hasPermission('ANY'));
    }

}