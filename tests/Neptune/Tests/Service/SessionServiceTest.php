<?php

namespace Neptune\Tests\Service;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Service\SessionService;

/**
 * SessionServiceTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SessionServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $neptune;
    protected $service;

    public function setUp()
    {
        $this->neptune = $this->getMockBuilder('\\Neptune\\Core\\Neptune')
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->service = new SessionService();
    }

    public function testIsAService()
    {
        $this->assertInstanceOf('\Neptune\Service\ServiceInterface', $this->service);
    }

    public function testRegister()
    {
        $this->neptune->expects($this->once())
                   ->method('offsetSet')
                   ->with('session');
        $this->service->register($this->neptune);
    }

}