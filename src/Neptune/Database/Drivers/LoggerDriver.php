<?php

namespace Neptune\Database\Drivers;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * LoggerDriver wraps an existing database driver with a Logger.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class LoggerDriver implements DatabaseDriver
{
    protected $driver;
    protected $logger;
    protected $level;

    public function __construct(DatabaseDriver $driver, LoggerInterface $logger, $level = LogLevel::INFO)
    {
        $this->driver = $driver;
        $this->logger = $logger;
        $this->level = $level;
    }

    public function prepare($query)
    {
        $this->logger->log($this->level, $query);

        return $this->driver->prepare($query);
    }

    public function quote($string)
    {
        return $this->driver->quote($string);
    }

    public function getBuilderName()
    {
        return $this->driver->getBuilderName();
    }

    public function setBuilderName($builder)
    {
        $this->driver->setBuilderName($builder);
    }

    public function lastInsertId($column = null)
    {
        return $this->driver->lastInsertId($column);
    }

}
