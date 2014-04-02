<?php

namespace Neptune\Database\EventListener;

use Symfony\Component\Console\Output\OutputInterface;
use Neptune\Database\DatabaseEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ConsoleDriver wraps an existing database driver and outputs all
 * queries to the console.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConsoleListener implements EventSubscriberInterface
{
    protected $output;
    protected $verbosity;

    public function __construct(OutputInterface $output, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->output = $output;
        $this->verbosity = $verbosity;
    }

    public function onPrepare(DatabaseEvent $query)
    {
        if ($this->output->getVerbosity() >= $this->verbosity) {
            $this->output->writeln($query->getData());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            DatabaseEvent::PREPARE => 'onPrepare',
        );
    }

}
