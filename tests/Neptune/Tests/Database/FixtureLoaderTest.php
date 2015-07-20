<?php

namespace Neptune\Tests\Database;

use Neptune\Database\FixtureLoader;

/**
 * FixtureLoaderTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FixtureLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loader = new FixtureLoader();
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    public function testLoggerAware()
    {
        $this->assertInstanceOf('Psr\Log\LoggerAwareInterface', $this->loader);
    }

    public function testFixturesAreLogged()
    {
        $fixture1 = $this->getMock('ActiveDoctrine\Fixture\FixtureInterface');
        $fixture2 = $this->getMock('ActiveDoctrine\Fixture\FixtureInterface');
        $this->loader->addFixture($fixture1);
        $this->loader->addFixture($fixture2);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->loader->setLogger($logger);
        $logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Running '.get_class($fixture1)],
                ['Running '.get_class($fixture2)]
            );

        $this->loader->run($this->conn);
    }

    public function testOrderedFixturesAreLogged()
    {
        $fixture1 = $this->getMock('ActiveDoctrine\Fixture\OrderedFixtureInterface');
        $fixture1->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue(1));
        $fixture2 = $this->getMock('ActiveDoctrine\Fixture\OrderedFixtureInterface');
        $fixture2->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue(5));

        $this->loader->addFixture($fixture2);
        $this->loader->addFixture($fixture1);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->loader->setLogger($logger);
        $logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['(1) Running '.get_class($fixture1)],
                ['(5) Running '.get_class($fixture2)]
            );

        $this->loader->run($this->conn);
    }
}
