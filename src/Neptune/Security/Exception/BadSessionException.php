<?php

namespace Neptune\Security\Exception;

use Neptune\Security\Driver\SecurityDriverInterface;

/**
 * BadSessionException is thrown when the session is invalid or has
 * expired.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BadSessionException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'Session is invalid', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 403, $previous);
    }

}
