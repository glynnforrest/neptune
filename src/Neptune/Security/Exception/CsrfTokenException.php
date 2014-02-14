<?php

namespace Neptune\Security\Exception;

/**
 * CsrfTokenException is thrown when the client supplies an incorrect
 * csrf token, or fails to supply one at all.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CsrfTokenException extends SecurityException
{

    public function __construct(SecurityDriverInterface $driver, $message = 'Invalid csrf token supplied', \Exception $previous = null)
    {
        parent::__construct($driver, $message, 403, $previous);
    }

}
