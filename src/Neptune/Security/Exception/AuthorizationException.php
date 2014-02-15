<?php

namespace Neptune\Security\Exception;

use Neptune\Security\Driver\SecurityDriverInterface;

/**
 * AuthorizationException is thrown when the client does not have
 * permission to access a resource, even with authentication.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AuthorizationException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'Access denied', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 403, $previous);
    }

}
