<?php

namespace Neptune\Security\Driver;

use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Exception\SecurityServiceException;

use Symfony\Component\HttpFoundation\Request;

/**
 * AbstractSecurityDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
abstract class AbstractSecurityDriver implements SecurityDriverInterface
{

    protected $request;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    protected function getSession()
    {
        if (!$this->request) {
            throw new SecurityServiceException("No Request defined");
        }
        if (!$this->request->hasSession()) {
            throw new SecurityServiceException("No Session defined");
        }
        //check for invalid session before returning
        return $this->request->getSession();
    }

}
