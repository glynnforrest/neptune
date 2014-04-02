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
    protected $sql_verbs = array(
        'asc',
        'by',
        'create',
        'delete',
        'desc',
        'drop',
        'from',
        'insert',
        'into',
        'limit',
        'order',
        'select',
        'table',
        'update',
    );

    public function __construct(OutputInterface $output, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->output = $output;
        $this->verbosity = $verbosity;
    }

    public function onPrepare(DatabaseEvent $query)
    {
        if ($this->output->getVerbosity() >= $this->verbosity) {
            $sql = $query->getData() . ' selection';
            foreach ($this->sql_verbs as $verb) {
                $sql = preg_replace("`\b($verb)\b`i", '<info>\1</info>', $sql);
            }
            $this->output->writeln($sql);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            DatabaseEvent::PREPARE => 'onPrepare',
        );
    }

}
