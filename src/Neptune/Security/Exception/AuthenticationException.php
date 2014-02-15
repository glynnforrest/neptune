<?php

namespace Neptune\Security\Exception;

use Neptune\Security\Driver\SecurityDriverInterface;

/**
 * AuthenticationException is thrown when authentication is required.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AuthenticationException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'Authentication required', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 401, $previous);
    }

}
