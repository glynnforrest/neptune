<?php

namespace Neptune\Security;

use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Exception\AnonymousException;
use Neptune\Security\Exception\AuthenticationException;
use Neptune\Security\Exception\AuthorizationException;
use Neptune\Security\Exception\SecurityException;

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
    protected $exemptions = array();
    protected $any;
    protected $none;
    protected $user;
    protected $anon;

    public function __construct($name, SecurityDriverInterface $security)
    {
        $this->name = $name;
        $this->any = 'ANY';
        $this->none = 'NONE';
        $this->user = 'USER';
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
     * Add a exemption to this Firewall. If a request passes this
     * exemption it will be granted explicit access, skipping any
     * other firewall rules.
     *
     * @param RequestMatcherInterface $matcher The matcher to check
     * the request with
     * @param string $permission The name of the permission required
     */
    public function addExemption(RequestMatcherInterface $matcher, $permission)
    {
        $this->exemptions[] = array($matcher, $permission);
    }

    /**
     * Set the names of the permissions to use for any, none, user and
     * anonymous rules. Set any of these to null to continue using the
     * current name.
     *
     * @param string $any  The rule that allows any request
     * @param string $none The rule that blocks any request
     * @param string $user The rule that allows any authentication
     * @param string $anon The rule that allows anonymous access only
     */
    public function setPermissionNames($any, $none, $user, $anon)
    {
        if ($any) {
            $this->any = $any;
        }
        if ($none) {
            $this->none = $none;
        }
        if ($user) {
            $this->user = $user;
        }
        if ($anon) {
            $this->anon = $anon;
        }
    }

    /**
     * Get the names of the permissions that are used for any, none,
     * user and anonymous rules.
     *
     * @return array An array containing the permission names for
     * any, none, user and anonymous access.
     */
    public function getPermissionNames()
    {
        return array($this->any, $this->none, $this->user, $this->anon);
    }

    /**
     * Check if a Request has permission to access a resource.
     *
     * @param  Request $request The request to check
     * @return Boolean true if the request is granted explicit access
     * via a exemption, false if the request passed but has not been
     * granted explicit access (allowing other firewalls to check)
     * @throws AuthenticationException if the request is not authenticated
     * @throws AuthorizationException  if the request is not authorized
     */
    public function check(Request $request)
    {
        //first check the exemptions. If the request passes any of
        //these, skip all other rules and firewalls.
        foreach ($this->exemptions as $exemption) {
            try {
                if (true === $this->checkRule($request, $exemption)) {
                    return true;
                }
                //catch any security exceptions - we dont want
                //to fail the firewall if an exemption fails
            } catch (SecurityException $e) {}
        }

        //check the rules. If they fail they will throw exceptions.
        foreach ($this->rules as $rule) {
            $this->checkRule($request, $rule);
        }

        //all rules have passed, but we can't be sure the request is
        //good as there may be other firewalls. Returning false is
        //this Firewall saying 'not sure' to the FirewallListener.
        return false;
    }

    protected function checkRule(Request $request, array $rule)
    {
        if (!$rule[0]->matches($request)) {
            return false;
        }
        $permission = $rule[1];

        //check for any access
        if ($permission === $this->any) {
            return true;
        }

        //check for no access
        if ($permission === $this->none) {
            $this->failAuthorization($request, $permission . ' has blocked all');
        }

        $this->security->setRequest($request);

        $authenticated = $this->security->isAuthenticated();

        //check for anonymous rule
        if ($permission === $this->anon) {
            if ($authenticated) {
                $this->failAnonymous($request);
            }

            return true;
        }

        //authentication is now required
        if (!$authenticated) {
            $this->failAuthentication($request);
        }

        //check for any authentication
        if ($permission === $this->user) {
            return true;
        }

        //check authorization
        if (!$this->security->hasPermission($permission)) {
            $this->failAuthorization($request, $permission . ' required');
        }

        //the request matched and the rule has passed
        return true;
    }

    protected function failAuthentication(Request $request)
    {
        $message = sprintf(
            'Firewall %s blocked url %s - authentication required',
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

    protected function failAnonymous(Request $request)
    {
        $message = sprintf(
            'Firewall %s blocked url %s - anonymous, authentication forbidden',
            $this->name,
            $request->getUri()
        );
        throw new AnonymousException($this->security, $message);
    }

}
