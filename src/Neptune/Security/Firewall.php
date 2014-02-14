<?php

namespace Neptune\Security;

use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Exception\AccessDeniedException;

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
    protected $any;
    protected $none;

    public function __construct(SecurityDriverInterface $security, $any = 'ANY', $none = 'NONE')
    {
        $this->any = $any;
        $this->none = $none;
        $this->security = $security;
    }

    public function addRule(RequestMatcherInterface $matcher, $permission)
    {
        $this->rules[] = array($matcher, $permission);
    }

    public function check(Request $request)
    {
        foreach ($this->rules as $matcher) {
            if (!$matcher[0]->matches($request)) {
                continue;
            }
            $this->security->setRequest($request);
            $permission = $matcher[1];

            //any access
            if ($permission === $this->any) {
                if (!$this->security->isAuthenticated()) {
                    $message = sprintf(
                        'Firewall %s blocked url %s - not logged in',
                        $this->name,
                        $request->getUri(),
                        $permission
                    );

                    return $this->fail($message);
                }
                continue;
            }

            //not allowed at all
            if ($permission === $this->none) {
                $message = sprintf(
                    'Firewall %s blocked url %s',
                    $this->name,
                    $request->getUri(),
                    $permission
                );

                return $this->fail($message);
            }

            //permission required
            if (!$this->security->hasPermission($permission)) {
                $message = sprintf(
                    'Firewall %s blocked url %s - permission %s required',
                    $this->name,
                    $request->getUri(),
                    $permission
                );

                return $this->fail($message);
            }
        }

        return true;
    }

    protected function fail($message)
    {
        throw new AccessDeniedException($this->security, $message);
    }

}
