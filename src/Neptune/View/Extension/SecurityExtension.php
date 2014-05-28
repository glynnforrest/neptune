<?php

namespace Neptune\View\Extension;

use Neptune\Security\SecurityFactory;
use Neptune\View\Extension\ExtensionInterface;

/**
 * SecurityExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityExtension implements ExtensionInterface
{

    protected $security;

    public function __construct(SecurityFactory $security)
    {
        $this->security = $security;
    }

    public function getHelpers()
    {
        return [
            'hasRole' => 'hasRole',
            'loggedIn' => 'loggedIn',
            'getUser' => 'getUser'
        ];
    }

    public function hasRole($role, $driver = null)
    {
        return $this->security->get($driver)->hasPermission($role);
    }

    public function loggedIn($driver = null)
    {
        return $this->security->get($driver)->isAuthenticated();
    }

    public function getUser($driver = null)
    {
        return $this->security->get($driver)->getUser();
    }

}
