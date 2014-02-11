<?php

namespace Neptune\Security;

use Neptune\Security\Driver\SecurityDriverInterface;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Firewall
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Firewall
{

    protected $security;
    protected $rules = array();

    public function __construct(SecurityDriverInterface $security)
    {
        $this->security = $security;
    }

    public function addRule(RequestMatcherInterface $matcher, $permission)
    {
        $this->rules[] = array($matcher, $permission);
    }

    public function check(Request $request)
    {
        foreach ($this->rules as $matcher) {
            if(!$matcher[0]->matches($request)) {
                continue;
            }
            return $this->security->hasPermission($matcher[1]);
        }
        return true;
    }

}
