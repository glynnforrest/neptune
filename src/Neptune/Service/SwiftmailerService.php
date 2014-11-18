<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Swiftmailer\TransportFactory;

/**
 * SwiftmailerService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SwiftmailerService implements ServiceInterface
{
    protected $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    public function register(Neptune $neptune)
    {
        if (!$this->config) {
            $this->config = $neptune['config'];
        }

        $neptune['mailer.factory'] = function ($neptune) {
            return new TransportFactory($neptune['mailer.dispatcher']);
        };

        $neptune['mailer.transport'] = function ($neptune) {
            return $neptune['mailer.factory']->create($this->config->get('mailer', []));
        };

        $neptune['mailer'] = function ($neptune) {
            return new \Swift_Mailer($neptune['mailer.transport']);
        };

        $neptune['mailer.dispatcher'] = function () {
            return new \Swift_Events_SimpleEventDispatcher();
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
