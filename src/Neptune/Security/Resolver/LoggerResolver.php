<?php

namespace Neptune\Security\Resolver;

use Neptune\Security\Exception\SecurityException;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * LoggerResolver uses a LoggerInterface class to log all security
 * exceptions before passing them on.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class LoggerResolver implements SecurityResolverInterface
{
    protected $logger;
    protected $level;

    public function __construct(LoggerInterface $logger, $level = LogLevel::INFO)
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    public function onException(SecurityException $exception, Request $request)
    {
        $this->logger->log($this->level, sprintf(
            '%s threw %s with message "%s" from ip %s on page %s',
            get_class($exception->getSecurityDriver()),
            get_class($exception),
            $exception->getMessage(),
            $request->getClientIp(),
            $request->getUri()
        ));
    }

    public function getSupportedDrivers()
    {
        return true;
    }

    public function getSupportedExceptions()
    {
        return true;
    }

}
