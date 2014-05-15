<?php

namespace Neptune\Tests\Security\Driver;

require_once __DIR__ . '/../../../../bootstrap.php';

use Neptune\Security\Driver\ConfigDriver;
use Neptune\Config\Config;

use Symfony\Component\HttpFoundation\Request;

/**
 * ConfigDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigDriverTest extends \PHPUnit_Framework_TestCase
{

    protected $driver;
    protected $config;

    public function setUp()
    {
        $this->config = new Config('testing');
        $this->driver = new ConfigDriver($this->config);

        $this->request = Request::create('testing');
        $this->driver->setRequest($this->request);
        $session = $this->getMock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
        $this->request->setSession($session);
    }

    public function loginProvider()
    {
        return array(
            array('admin', 'foo', true),
            array('admin', 'bar'),
            array('user', 'foo'),
            array(null, 'foo'),
            array('admin', null),
            array(null, null),
        );
    }

    /**
     * @dataProvider loginProvider
     */
    public function testAuthenticate($username, $password, $pass = false)
    {
        $this->config->set('security.user', 'admin');
        $hash = password_hash('foo', PASSWORD_DEFAULT);
        $this->config->set('security.pass', $hash);
        $this->request->request->set('username', $username);
        $this->request->request->set('password', $password);
        if ($pass) {
            $this->assertTrue($this->driver->authenticate());
        } else {
            $this->setExpectedException('\Blockade\Exception\CredentialsException');
            $this->driver->authenticate();
        }
    }

    public function testInvalidConfigThrowsException()
    {
        $this->config->set('security', null);
        $this->request->request->set('username', 'foo');
        $this->request->request->set('password', 'bar');
        $this->setExpectedException('\Blockade\Exception\BlockadeFailureException');
        $this->driver->authenticate();
    }

}