<?php

namespace Neptune\Security\Driver;

/**
 * SecurityDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface SecurityDriverInterface
{

    public function loggedIn();

    public function login($identifier, $password);

    public function logout();

    public function hasPermission($permission);

}
