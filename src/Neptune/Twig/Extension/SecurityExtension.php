<?php

namespace Neptune\Twig\Extension;

use Neptune\Security\SecurityFactory;

/**
 * SecurityExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityExtension extends \Twig_Extension
{
    protected $security;

    public function __construct(SecurityFactory $security)
    {
        $this->security = $security;
    }

    public function getName()
    {
        return 'security';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('hasRole', [$this, 'hasRole']),
            new \Twig_SimpleFunction('loggedIn', [$this, 'loggedIn']),
            new \Twig_SimpleFunction('getUser', [$this, 'getUser']),
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
