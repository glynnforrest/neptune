<?php

namespace Neptune\Database;

use ActiveDoctrine\Fixture\FixtureInterface;
use ActiveDoctrine\Fixture\FixtureLoader as BaseFixtureLoader;
use ActiveDoctrine\Fixture\OrderedFixtureInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * FixtureLoader with optional logging.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FixtureLoader extends BaseFixtureLoader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected function runFixture(Connection $connection, FixtureInterface $fixture)
    {
        $msg = '';
        if ($fixture instanceof OrderedFixtureInterface) {
            $msg .= '('.$fixture->getOrder().') ';
        }

        $msg .= 'Running '.get_class($fixture);

        if ($this->logger) {
            $this->logger->info($msg);
        }

        parent::runFixture($connection, $fixture);
    }
}
