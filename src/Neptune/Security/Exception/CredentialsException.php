<?php

namespace Neptune\Security\Exception;

use Neptune\Security\Driver\SecurityDriverInterface;

/**
 * CredentialsException is thrown when authentication fails due to
 * incomplete or incorrect credentials.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CredentialsException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'Bad credentials supplied', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 401, $previous);
    }

}
