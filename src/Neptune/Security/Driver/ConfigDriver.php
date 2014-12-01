<?php

namespace Neptune\Security\Driver;

use Neptune\Config\Config;
use Neptune\Exceptions\ConfigKeyException;

use Blockade\Exception\CredentialsException;
use Blockade\Exception\BlockadeFailureException;
use Blockade\Driver\AbstractDriver;

/**
 * ConfigDriver
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigDriver extends AbstractDriver
{

    protected $config;
    protected $user_key;
    protected $pass_key;

    public function __construct(Config $config, $user_key = 'security.user', $pass_key = 'security.pass')
    {
        $this->config = $config;
        $this->user_key = $user_key;
        $this->pass_key = $pass_key;
    }

    public function isAuthenticated()
    {
        //compare session token with token generated from the request
        return $this->getSession()->get('security.token') === md5($this->request->server->get('HTTP_USER_AGENT'));
    }

    public function authenticate()
    {
        $username = $this->request->request->get('username');
        if (!$username) {
            throw CredentialsException::from($this, "No username supplied.");
        }

        $password = $this->request->request->get('password');
        if (!$password) {
            throw CredentialsException::from($this, "No password supplied.");
        }

        try {
            $config_username = $this->config->getRequired($this->user_key);
            $hash = $this->config->getRequired($this->pass_key);
        } catch (ConfigKeyException $e) {
            throw new BlockadeFailureException('Invalid security configuration: ' . $e->getMessage());
        }

        //safe compare required
        if ($username !== $config_username) {
            throw CredentialsException::from($this, "Invalid username and password combination.");
        }

        if (!password_verify($password, $hash)) {
            throw CredentialsException::from($this, "Invalid username and password combination.");
        }

        return true;
    }

    public function login($identifier)
    {
        $this->getSession()->set('security.token', md5($this->request->server->get('HTTP_USER_AGENT')));
        //catch no request or no session exceptions and rethrow as a
        //LoginFailedException
        return true;
    }

    public function logout()
    {
        $this->getSession()->set('security.token', null);
    }

    public function hasPermission($permission)
    {
        return $this->isAuthenticated();
    }

}
