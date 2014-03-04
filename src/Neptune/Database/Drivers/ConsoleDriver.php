<?php

namespace Neptune\Database\Drivers;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConsoleDriver wraps an existing database driver and outputs all
 * queries to the console.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConsoleDriver implements DatabaseDriver
{
    protected $driver;
    protected $output;
    protected $verbosity;

    public function __construct(DatabaseDriver $driver, OutputInterface $output, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->driver = $driver;
        $this->output = $output;
        $this->verbosity = $verbosity;
    }

    public function prepare($query)
    {
        if ($this->output->getVerbosity() <= $this->verbosity) {
            $this->output->writeln($query);
        }

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
