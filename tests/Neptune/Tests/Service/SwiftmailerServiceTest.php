<?php

namespace Neptune\Tests\Service;

use Neptune\Service\SwiftmailerService;
use Neptune\Core\Neptune;

/**
 * SwiftmailerServiceTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SwiftmailerServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $service;

    public function setUp()
    {
        $this->service = new SwiftmailerService();
    }

    public function testServicesDefined()
    {
        $neptune = new Neptune('/path/to/root');
        $this->service->register($neptune);
        $services = $neptune->keys();
        $expected = [
            'mailer',
            'mailer.transport',
            'mailer.spool',
            'mailer.transport.spool',
            'mailer.factory',
            'mailer.listener',
        ];
        foreach ($expected as $service) {
            $this->assertTrue(in_array($service, $services));
        }
    }
}
