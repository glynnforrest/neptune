<?php

namespace Neptune\Tests\Swiftmailer;

use Neptune\Swiftmailer\TransportFactory;
use Temping\Temping;

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

    public function testInvalidTransport()
    {
        $this->setExpectedException('Neptune\Exceptions\DriverNotFoundException');
        $this->factory->create(['driver' => 'foo']);
    }

    public function testDefaultTransport()
    {
        $this->assertInstanceOf('\Swift_Transport_NullTransport', $this->factory->create([]));
    }

    public function testNullTransport()
    {
        $config = [
            'driver' => 'null',
        ];
        $this->assertInstanceOf('\Swift_Transport_NullTransport', $this->factory->create($config));
    }

    public function testDefaultSmtpTransport()
    {
        $config = [
            'driver' => 'smtp',
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

    public function testSmtpTransport()
    {
        $config = [
            'driver' => 'smtp',
            'host' => 'example.org',
            'port' => 23,
            'username' => 'user',
            'password' => 'pass',
            'encryption' => 'ssl',
            'auth_mode' => 'login',
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

    public function testGmailTransport()
    {
        $config = [
            'driver' => 'gmail',
            'username' => 'example',
            'password' => 'example',
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

    public function testGmailTransportNoOverrideSettings()
    {
        $config = [
            'driver' => 'gmail',
            'username' => 'example',
            'password' => 'example',
            'host' => 'foo',
            'port' => 42,
            'auth_mode' => 'foo',
            'encryption' => 'foo',
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

    public function testInvalidSpool()
    {
        $this->setExpectedException('Neptune\Exceptions\DriverNotFoundException');
        $this->factory->createSpool(['driver' => 'foo']);
    }

    public function testDefaultSpool()
    {
        $this->assertInstanceOf('\Swift_MemorySpool', $this->factory->createSpool([]));
    }

    public function testMemorySpool()
    {
        $config = ['driver' => 'memory'];
        $this->assertInstanceOf('\Swift_MemorySpool', $this->factory->createSpool($config));
    }

    public function testFileSpoolNoPath()
    {
        $this->setExpectedException('Neptune\Exceptions\ConfigKeyException');
        $this->factory->createSpool(['driver' => 'file']);
    }

    public function testFileSpool()
    {
        //use a temporary directory so FileSpool can create the path it
        //requires
        $temping = new Temping();
        $path = $temping->getPathname('foo/bar');

        $config = [
            'driver' => 'file',
            'path' => $path,
        ];
        $spool = $this->factory->createSpool($config);

        //clean up the temporary directory before any assertions in case they
        //fail
        $temping->reset();

        $this->assertInstanceOf('\Swift_FileSpool', $spool);
        //FileSpool doesn't have a getPath() method
        $r = new \ReflectionClass('\Swift_FileSpool');
        $property = $r->getProperty('_path');
        $property->setAccessible(true);
        $this->assertSame($path, $property->getValue($spool));
    }
}
