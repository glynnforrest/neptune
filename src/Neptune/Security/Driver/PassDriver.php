<?php

namespace Neptune\Security\Driver;

use Neptune\Security\Driver\AbstractSecurityDriver;

/**
 * PassDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class PassDriver extends AbstractSecurityDriver
{

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
