<?php

namespace Neptune\Security\Driver;

/**
 * FailDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FailDriver implements SecurityDriverInterface
{

    public function loggedIn()
    {
        return false;
    }

    public function login($identifier, $password)
    {
        return false;
    }

    public function logout()
    {
        return false;
    }

    public function hasPermission($permission)
    {
        return false;
    }

}
