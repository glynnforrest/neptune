<?php

namespace Neptune\Security\Driver;

/**
 * PassDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class PassDriver implements SecurityDriverInterface
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
        return true;
    }

    public function login($identifier)
    {
        return true;
    }

    public function logout()
    {
        return true;
    }

    public function isAuthenticated()
    {
        return true;
    }

    public function hasPermission($permission)
    {
        return true;
    }

}
