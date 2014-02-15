<?php

namespace Neptune\Security;

use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Exception\AccessDeniedException;
use Neptune\Security\Exception\UnauthorizedException;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Firewall
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Firewall
{

    protected $name;
    protected $security;
    protected $rules = array();
    protected $any;
    protected $none;

    public function __construct(SecurityDriverInterface $security, $any = 'ANY', $none = 'NONE')
    {
        $this->name = 'neptune';
        $this->any = $any;
        $this->none = $none;
        $this->security = $security;
    }

    public function addRule(RequestMatcherInterface $matcher, $permission)
    {
        $this->rules[] = array($matcher, $permission);
    }

    /**
     * Check if a Request has permission to access a resource.
     *
     * @param Request $request The request to check
     * @return true on success
     * @throws UnauthorizedException if the request is not authenticated
     * @throws AccessDeniedException if the request is not authorized
     */
    public function check(Request $request)
    {
        foreach ($this->rules as $matcher) {
            if (!$matcher[0]->matches($request)) {
                continue;
            }
            $this->security->setRequest($request);
            $permission = $matcher[1];

            //not allowed at all
            if ($permission === $this->none) {
                $this->failAuthorization($request, $permission . ' has blocked all');
            }

            //any access
            if ($permission === $this->any) {
                if (!$this->security->isAuthenticated()) {
                    $this->failAuthentication($request);
                }
                continue;
            }

            //first check if authenticated at all
            if (!$this->security->isAuthenticated($permission)) {
                $this->failAuthentication($request);
            }

            //now check authorization
            if (!$this->security->hasPermission($permission)) {
                $this->failAuthorization($request, $permission);
            }
        }

        return true;
    }

    protected function failAuthentication(Request $request)
    {
        $message = sprintf(
            'Firewall %s blocked url %s - not authenticated',
            $this->name,
            $request->getUri()
        );
        throw new UnauthorizedException($this->security, $message);
    }

    protected function failAuthorization(Request $request, $permission)
    {
        $message = sprintf(
            'Firewall %s blocked url %s - permission %s',
            $this->name,
            $request->getUri(),
            $permission
        );
        throw new AccessDeniedException($this->security, $message);
    }

}
