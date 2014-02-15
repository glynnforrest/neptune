<?php

namespace Neptune\Security\Driver;

use Neptune\Security\Driver\AbstractSecurityDriver;

/**
 * FailDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FailDriver extends AbstractSecurityDriver
{

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
