<?php

namespace Neptune\Security\Driver;

use Neptune\Security\Driver\AbstractSecurityDriver;

/**
 * CsrfDriver is a driver that is only used by the CsrfManager.
 *
 * @author Glynn Forrest me@glynnforrest.com
 **/
class CsrfDriver extends AbstractSecurityDriver
{

    public function authenticate()
    {
    }

    public function login($identifier)
    {
    }

    public function logout()
    {
    }

    public function isAuthenticated()
    {
    }

    public function hasPermission($permission)
    {
    }

}
