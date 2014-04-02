<?php

namespace Neptune\Database\EventListener;

use Neptune\Database\DatabaseEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LoggerListener
 * @author Glynn Forrest me@glynnforrest.com
 **/
class LoggerListener implements EventSubscriberInterface
{

    protected $logger;
    protected $level;

    public function __construct(LoggerInterface $logger, $level = LogLevel::INFO)
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    public function onPrepare(DatabaseEvent $event)
    {
        $this->logger->log($this->level, $event->getData());
    }

    public static function getSubscribedEvents()
    {
        return array(
            DatabaseEvent::PREPARE => 'onPrepare',
        );
    }

}
