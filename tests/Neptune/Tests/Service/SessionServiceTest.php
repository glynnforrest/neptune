<?php

namespace Neptune\Tests\Service;

use Neptune\Service\SessionService;
use Neptune\Core\Neptune;

/**
 * SessionServiceTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SessionServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $service;

    public function setUp()
    {
        $this->service = new SessionService();
    }

    public function testRegister()
    {
        $neptune = new Neptune('/path/to/root');
        $this->service->register($neptune);
        $services = $neptune->keys();
        foreach (['session', 'session.listener'] as $service) {
            $this->assertTrue(in_array($service, $services));
        }
    }
}
