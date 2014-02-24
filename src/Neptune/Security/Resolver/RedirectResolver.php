<?php

namespace Neptune\Security\Resolver;

use Neptune\Security\Exception\SecurityException;
use Neptune\Security\Exception\SecurityServiceException;
use Neptune\Security\Exception\CsrfException;
use Neptune\Security\Exception\AuthenticationException;
use Neptune\Security\Exception\CredentialsException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * RedirectResolver
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RedirectResolver implements SecurityResolverInterface
{

    protected $login_url;
    protected $deny_url;

    /**
     * Create a new RedirectResolver. Make sure that $login_url and
     * $deny_url are accessible through any firewalls to avoid a
     * redirect loop.
     */
    public function __construct($login_url = '/login', $deny_url = '/restricted')
    {
        $this->login_url = $login_url;
        $this->deny_url = $deny_url;
    }

    public function onException(SecurityException $exception, Request $request)
    {
        //decide where to redirect. login_url for unauthenticated or
        //bad credentials, deny_url for unauthorized or anything else
        if ($exception instanceof AuthenticationException || $exception instanceof CredentialsException) {
            $url = $this->login_url;
        } else {
            $url = $this->deny_url;
        }

        //check for a potential redirect loop
        if($request->getPathInfo() === $url && $request->getMethod() === 'GET') {
            throw new SecurityServiceException(
                sprintf('Circular redirect to %s detected', $url),
                500,
                $exception
            );
        }

        //if tampering is evident, log the user out
        if ($exception instanceof CsrfTokenException) {
            $exception->getSecurityDriver()->logout();
        }

        return new RedirectResponse($url);
    }

    public function getSupportedDrivers()
    {
        return true;
    }

    public function getSupportedExceptions()
    {
        return true;
    }

}
