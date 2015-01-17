<?php

namespace Neptune\Tests\Service;

use Neptune\Service\DatabaseService;
use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Doctrine\DBAL\Types\Type;

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

    public function testTypesRegistered()
    {
        $neptune = new Neptune('/app');
        $neptune['config'] = new Config([
            'database' => [
                'test' => [
                    'driver' => 'pdo_sqlite',
                    'memory' => true,
                ],
                '_types' => [
                    'foo' => 'Neptune\Tests\Service\Fixtures\FooType',
                ],
            ],
        ]);
        $this->service->register($neptune);

        //the types are registered lazily, so a connection has to be fetched first.
        $neptune['db'];
        $this->assertInstanceOf('Neptune\Tests\Service\Fixtures\FooType', Type::getType('foo'));

        //check _types isn't registered as a connection
        $dbs = $neptune['dbs'];
        $this->assertSame(['test'], $dbs->keys());
    }
}
