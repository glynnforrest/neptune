<?php

namespace Neptune\Security;

use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Exception\AuthenticationException;
use Neptune\Security\Exception\AuthorizationException;

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
    protected $allow;
    protected $block;
    protected $anon;

    public function __construct($name, SecurityDriverInterface $security)
    {
        $this->name = $name;
        $this->allow = 'ALLOW';
        $this->block = 'BLOCK';
        $this->anon = 'ANON';
        $this->security = $security;
    }

    /**
     * Add a rule to this Firewall.
     *
     * @param RequestMatcherInterface $matcher The matcher to check
     * the request with
     * @param string $permission The name of the permission to enforce
     */
    public function addRule(RequestMatcherInterface $matcher, $permission)
    {
        $this->rules[] = array($matcher, $permission);
    }

    /**
     * Set the names of the permissions to use for allow, block and
     * anonymous rules. Set any of these to null to continue using the
     * current name.
     *
     * @param string $allow The rule that allows any authentication
     * @param string $block The rule that blocks the request unconditionally
     * @param string $anon  The rule that allows anonymous access as
     * well as any authentication
     */
    public function setPermissionNames($allow, $block, $anon)
    {
        if ($allow) {
            $this->allow = $allow;
        }
        if ($block) {
            $this->block = $block;
        }
        if ($anon) {
            $this->anon = $anon;
        }
    }

    /**
     * Get the names of the permissions that are used for allow, block
     * and anonymous access.
     *
     * @return array An array containing the permission names for
     * allow, block and anonymous access.
     */
    public function getPermissionNames()
    {
        return array($this->allow, $this->block, $this->anon);
    }

    /**
     * Check if a Request has permission to access a resource.
     *
     * @param  Request                 $request The request to check
     * @return true                    on success
     * @throws AuthenticationException if the request is not authenticated
     * @throws AuthorizationException  if the request is not authorized
     */
    public function check(Request $request)
    {
        foreach ($this->rules as $rule) {
            if (!$rule[0]->matches($request)) {
                continue;
            }
            $permission = $rule[1];

            //anonymous access
            if ($permission === $this->anon) {
                return true;
            }

            $this->security->setRequest($request);

            //block unconditionally
            if ($permission === $this->block) {
                $this->failAuthorization($request, $permission . ' has blocked all');
            }

            //allow any authentication
            if ($permission === $this->allow) {
                if (!$this->security->isAuthenticated()) {
                    $this->failAuthentication($request);
                }

                return true;
            }

            //this is a regular permission - check for authentication
            //and authorization

            //first check if authenticated at all
            if (!$this->security->isAuthenticated()) {
                $this->failAuthentication($request);
            }

            //now check authorization
            if (!$this->security->hasPermission($permission)) {
                $this->failAuthorization($request, $permission . ' required');
            }

            //the request matched and the rule has passed - return
            //true and stop iterating over rules
            return true;
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
        throw new AuthenticationException($this->security, $message);
    }

    protected function failAuthorization(Request $request, $permission)
    {
        $message = sprintf(
            'Firewall %s blocked url %s - permission %s',
            $this->name,
            $request->getUri(),
            $permission
        );
        throw new AuthorizationException($this->security, $message);
    }

}
