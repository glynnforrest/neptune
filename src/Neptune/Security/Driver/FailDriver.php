<?php

namespace Neptune\Security\Driver;

use Neptune\Security\Driver\SecurityDriverInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * FailDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FailDriver implements SecurityDriverInterface
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

    public function authenticate()
    {
        return false;
    }

    public function login($identifier)
    {
        return false;
    }

    public function logout()
    {
        return false;
    }

    public function isAuthenticated()
    {
        return false;
    }

    public function hasPermission($permission)
    {
        return false;
    }

}
