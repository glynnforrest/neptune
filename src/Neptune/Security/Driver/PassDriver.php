<?php

namespace Neptune\Security\Driver;

/**
 * PassDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class PassDriver implements SecurityDriverInterface
{

    public function loggedIn()
    {
        return true;
    }

    public function login($identifier, $password)
    {
        return true;
    }

    public function logout()
    {
        return true;
    }

    public function hasPermission($permission)
    {
        return true;
    }

}
