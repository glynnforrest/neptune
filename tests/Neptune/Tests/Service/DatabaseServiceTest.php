<?php

namespace Neptune\Tests\Service;

use Neptune\Service\DatabaseService;
use Neptune\Core\Neptune;

/**
 * DatabaseServiceTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $service;

    public function setUp()
    {
        $this->service = new DatabaseService();
    }

    public function testServicesDefined()
    {
        $neptune = new Neptune('/path/to/root');
        $this->service->register($neptune);
        $services = $neptune->keys();
        foreach (['db', 'dbs', 'db.config'] as $service) {
            $this->assertTrue(in_array($service, $services));
        }
    }
}
