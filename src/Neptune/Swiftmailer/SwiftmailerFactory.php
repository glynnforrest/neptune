<?php

namespace Neptune\Swiftmailer;

use Neptune\Exceptions\DriverNotFoundException;
use Neptune\Exceptions\ConfigKeyException;

/**
 * SwiftmailerFactory
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SwiftmailerFactory
{
    protected $dispatcher;
    protected $defaults = [
        'driver' => 'null',
        'host' => 'localhost',
        'port' => 25,
        'username' => '',
        'password' => '',
        'encryption' => null,
        'auth_mode' => null,
    ];
    protected $spool_defaults = [
        'driver' => 'memory',
    ];

    public function __construct(\Swift_Events_EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Create a Swiftmailer transport from a configuration.
     *
     * Valid settings with their defaults:
     *
     * driver - 'smtp', 'gmail' or 'null' (null)
     * host - The hostname (localhost)
     * port - The port number (25)
     * username - The username ()
     * password - The password ()
     * encryption - 'ssl', 'tls' or null (null)
     * auth_mode - 'login', 'md5', 'plain' or null (null)
     *
     * @param array $config The configuration.
     */
    public function createTransport(array $config)
    {
        $config = array_merge($this->defaults, $config);

        switch ($config['driver']) {
            case 'null':
                return new \Swift_Transport_NullTransport($this->dispatcher);
            case 'smtp':
                return $this->createSmtp($config);
            case 'gmail':
                $config['host'] = 'smtp.gmail.com';
                $config['encryption'] = 'ssl';
                $config['auth_mode'] = 'login';
                $config['port'] = 465;

                return $this->createSmtp($config);
            default:
                throw new DriverNotFoundException(sprintf('Swiftmailer transport not implemented: "%s"', $config['driver']));
        }
    }

    protected function createSmtp(array $config)
    {
        $buffer = new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory());
        $auth = new \Swift_Transport_Esmtp_AuthHandler([
            new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator(),
            new \Swift_Transport_Esmtp_Auth_LoginAuthenticator(),
            new \Swift_Transport_Esmtp_Auth_PlainAuthenticator(),
        ]);

        $transport = new \Swift_Transport_EsmtpTransport($buffer, [$auth], $this->dispatcher);

        $transport->setHost($config['host']);
        $transport->setPort($config['port']);
        $transport->setUsername($config['username']);
        $transport->setPassword($config['password']);
        $transport->setEncryption($config['encryption']);
        $transport->setAuthMode($config['auth_mode']);

        return $transport;
    }

    /**
     * Create a Swiftmailer spool from a configuration.
     *
     * Valid settings with their defaults:
     *
     * driver - 'memory' or 'file' (memory)
     * path - The path to the spool folder when using the file driver
     *
     * @param array $config The configuration.
     */
    public function createSpool(array $config)
    {
        $config = array_merge($this->spool_defaults, $config);

        switch ($config['driver']) {
            case 'memory':
                return new \Swift_MemorySpool();
            case 'file':
                if (!isset($config['path'])) {
                    throw new ConfigKeyException('Swiftmailer file spool must have a path set');
                }

                return new \Swift_FileSpool($config['path']);
            default:
                throw new DriverNotFoundException(sprintf('Swiftmailer spool not implemented: "%s"', $config['driver']));
        }
    }
}
