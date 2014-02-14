<?php

namespace Neptune\Security\Exception;

/**
 * SecurityServiceException should be thrown when the security service
 * itself encounters an error.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityServiceException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'An error occurred', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 500, $previous);
    }

}
