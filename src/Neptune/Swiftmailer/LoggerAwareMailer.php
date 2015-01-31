<?php

namespace Neptune\Swiftmailer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Swift_Mime_Message;

/**
 * LoggerAwareMailer
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class LoggerAwareMailer extends \Swift_Mailer implements LoggerAwareInterface
{
    protected $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        if ($this->logger) {
            $this->logger->debug($message);
        }
        parent::send($message, $failedRecipients);
    }
}
