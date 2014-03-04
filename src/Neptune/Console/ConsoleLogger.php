<?php

namespace Neptune\Console;

use Psr\Log\AbstractLogger;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConsoleLogger
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConsoleLogger extends AbstractLogger
{

    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function log($level, $message, array $context = array())
    {
        $this->output->writeln($message);
    }

}
