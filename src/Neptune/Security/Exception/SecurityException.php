<?php

namespace Neptune\Security\Exception;

use Neptune\Security\Driver\SecurityDriverInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * SecurityException
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityException extends \Exception
{

    protected $driver;

    public function __construct(SecurityDriverInterface $driver, $message = '', $code = 403, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->driver = $driver;
    }

    public function setSecurityDriver(SecurityDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function getSecurityDriver()
    {
        return $this->driver;
    }

}
