<?php

namespace Neptune\Security\Exception;

/**
 * BadCredentialsException is thrown when authentication fails due to
 * incomplete or incorrect credentials.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BadCredentialsException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'Bad credentials supplied', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 401, $previous);
    }

}
