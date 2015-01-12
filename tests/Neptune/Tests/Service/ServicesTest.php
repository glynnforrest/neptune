<?php

namespace Neptune\Tests\Service;

use Neptune\Core\Neptune;
use Neptune\Service\CacheService;
use Neptune\Service\DatabaseService;
use Neptune\Service\FormService;
use Neptune\Service\MonologService;
use Neptune\Service\RoutingService;
use Neptune\Service\SecurityService;
use Neptune\Service\SessionService;
use Neptune\Service\SwiftmailerService;
use Neptune\Service\ViewService;

/**
 * ServicesTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ServicesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check the default configuration of all services doesn't break.
     */
    public function testLoadAllDefaultServices()
    {
        $neptune = new Neptune('/path/to/root');

        $neptune->addService(new CacheService());
        $neptune->addService(new DatabaseService());
        $neptune->addService(new FormService());
        $neptune->addService(new MonologService());
        $neptune->addService(new RoutingService());
        $neptune->addService(new SecurityService());
        $neptune->addService(new SessionService());
        $neptune->addService(new SwiftmailerService());
        $neptune->addService(new ViewService());

        $services_property = new \ReflectionProperty($neptune, 'services');
        $services_property->setAccessible(true);

        $this->assertSame(9, count($services_property->getValue($neptune)));
    }
}
