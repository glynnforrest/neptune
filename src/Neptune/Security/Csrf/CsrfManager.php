<?php

namespace Neptune\Security\Csrf;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Neptune\Security\Exception\CsrfTokenException;
use Neptune\Security\Driver\FailDriver;

/**
 * CsrfManager
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CsrfManager
{

    protected $driver;
    protected $session;
    protected $session_key;

    public function __construct(SessionInterface $session, $driver = null)
    {
        $this->driver = new FailDriver();
        $this->session = $session;
        $this->session_key = 'security.csrf.';
    }

    public function setSessionKey($session_key)
    {
        $this->session_key = $session_key;
    }

    public function getSessionKey()
    {
        return $this->session_key;
    }

    /**
     * Create a new token in the session with identifier $id. Any
     * current token with the same identifier will be replaced.
     *
     * @param string $id The identifier of the token
     */
    public function init($id)
    {
        //as suggested in the OWASP PHP CSRF helper page
        //https://www.owasp.org/index.php/PHP_CSRF_Guard
        if (function_exists("hash_algos") && in_array("sha512", hash_algos())) {
            $token = hash("sha512", mt_rand(0, mt_getrandmax()));
        } else {
            $token = ' ';
            for ($i = 0; $i < 128; ++$i) {
                $r = mt_rand(0, 35);
                if ($r < 26) {
                    $c = chr(ord('a') + $r);
                } else {
                    $c = chr(ord('0') + $r - 26);
                }
                $token .= $c;
            }
        }
        $this->session->set($this->session_key . $id, $token);

        return $token;
    }

    /**
     * Create a new token in the session with identifier $id, but only
     * if it doesn't exist.
     *
     * @param string $id The identifier of the token
     */
    public function maybeInit($id)
    {
        if (!$this->session->has($this->session_key . $id)) {
            return $this->init($id);
        }

        return $this->get($id);
    }

    /**
     * Remove the token with identifier $id from the session.
     *
     * @param string $id The identifier of the token
     */
    public function remove($id)
    {
        return $this->session->remove($this->session_key . $id);
    }

    /**
     * Get the token with identifier $id from the session.
     *
     * @param string $id The identifier of the token
     */
    public function get($id)
    {
        return $this->session->get($this->session_key . $id);
    }

    /**
     * Check the value of the token with identifier $id against the
     * supplied token. If the token matches, it will be removed from
     * the session to prevent additional usage.
     *
     * @param  string             $id    The identifier of the token
     * @param  string             $token The supplied token
     * @throws CsrfTokenException when the supplied token does not match.
     */
    public function check($id, $token)
    {
        if (!$token) {
            throw new CsrfTokenException($this->driver, "No Csrf token submitted");
        }

        $session_token = $this->get($id);
        if ($session_token !== $token) {
            throw new CsrfTokenException($this->driver, "Invalid Csrf token");
        }

        //remove the token from the session after it has been used
        $this->remove($id);

        return true;
    }

}
