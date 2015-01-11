<?php

namespace Neptune\Tests\Service;

use Neptune\Core\Neptune;
use Neptune\Service\SecurityService;

/**
 * SecurityServiceTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $service;

    public function setUp()
    {
        $this->service = new SecurityService();
    }

    public function testServicesDefined()
    {
        $neptune = new Neptune('/path/to/root');
        $this->service->register($neptune);
        $services = $neptune->keys();
        $expected = [
            'security',
            'security.firewall',
            'security.request',
            'security.resolver',
        ];
        foreach ($expected as $service) {
            $this->assertTrue(in_array($service, $services));
        }
    }
}
