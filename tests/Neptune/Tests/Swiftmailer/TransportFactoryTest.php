<?php

namespace Neptune\Tests\Swiftmailer;

use Neptune\Swiftmailer\TransportFactory;

/**
 * TransportFactory
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TransportFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $this->factory = new TransportFactory(new \Swift_Events_SimpleEventDispatcher());
    }
    public function testInvalidDriver()
    {
        $this->setExpectedException('Neptune\Exceptions\DriverNotFoundException');
        $this->factory->create(['driver' => 'foo']);
    }

    public function testCreateNull()
    {
        $this->assertInstanceOf('\Swift_Transport_NullTransport', $this->factory->create([]));
        $config = [
            'driver' => 'null'
        ];
        $this->assertInstanceOf('\Swift_Transport_NullTransport', $this->factory->create($config));
    }

    public function testCreateSmtpDefaults()
    {
        $config = [
            'driver' => 'smtp'
        ];
        $transport = $this->factory->create($config);

        $this->assertInstanceOf('\Swift_Transport_EsmtpTransport', $transport);
        $this->assertSame('localhost', $transport->getHost());
        $this->assertSame(25, $transport->getPort());
        $this->assertSame('', $transport->getUsername());
        $this->assertSame('', $transport->getPassword());
        $this->assertSame(null, $transport->getEncryption());
        $this->assertSame(null, $transport->getAuthMode());
    }

    public function testCreateSmtp()
    {
        $config = [
            'driver' => 'smtp',
            'host' => 'example.org',
            'port' => 23,
            'username' => 'user',
            'password' => 'pass',
            'encryption' => 'ssl',
            'auth_mode' => 'login'
        ];
        $transport = $this->factory->create($config);

        $this->assertInstanceOf('\Swift_Transport_EsmtpTransport', $transport);
        $this->assertSame('example.org', $transport->getHost());
        $this->assertSame(23, $transport->getPort());
        $this->assertSame('user', $transport->getUsername());
        $this->assertSame('pass', $transport->getPassword());
        $this->assertSame('ssl', $transport->getEncryption());
        $this->assertSame('login', $transport->getAuthMode());
    }

    public function testCreateGmail()
    {
        $config = [
            'driver' => 'gmail',
            'username' => 'example',
            'password' => 'example'
        ];
        $transport = $this->factory->create($config);

        $this->assertInstanceOf('\Swift_Transport_EsmtpTransport', $transport);
        $this->assertSame('smtp.gmail.com', $transport->getHost());
        $this->assertSame(465, $transport->getPort());
        $this->assertSame('example', $transport->getUsername());
        $this->assertSame('example', $transport->getPassword());
        $this->assertSame('ssl', $transport->getEncryption());
        $this->assertSame('login', $transport->getAuthMode());
    }

    public function testCreateGmailNoOverrideSettings()
    {
        $config = [
            'driver' => 'gmail',
            'username' => 'example',
            'password' => 'example',
            'host' => 'foo',
            'port' => 42,
            'auth_mode' => 'foo',
            'encryption' => 'foo'
        ];
        $transport = $this->factory->create($config);

        $this->assertInstanceOf('\Swift_Transport_EsmtpTransport', $transport);
        $this->assertSame('smtp.gmail.com', $transport->getHost());
        $this->assertSame(465, $transport->getPort());
        $this->assertSame('example', $transport->getUsername());
        $this->assertSame('example', $transport->getPassword());
        $this->assertSame('ssl', $transport->getEncryption());
        $this->assertSame('login', $transport->getAuthMode());
    }

}
