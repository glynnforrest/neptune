<?php

namespace Neptune\Security\Driver;

use Neptune\Core\RequestAwareInterface;

/**
 * SecurityDriverInterface
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface SecurityDriverInterface extends RequestAwareInterface
{

    public function authenticate();

    public function login($identifier);

    public function logout();

    public function isAuthenticated();

    public function hasPermission($permission);

}
