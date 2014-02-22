<?php

namespace Neptune\Security\Driver;

use Symfony\Component\HttpFoundation\Request;

/**
 * SecurityDriverInterface
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface SecurityDriverInterface
{

    public function setRequest(Request $request);

    public function getRequest();

    public function authenticate();

    public function login($identifier);

    public function logout();

    public function isAuthenticated();

    public function hasPermission($permission);

}
