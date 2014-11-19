<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Swiftmailer\TransportFactory;
use Neptune\EventListener\SwiftmailerListener;

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

        $neptune['mailer.spool'] = function ($neptune) {
            $config = $this->config->get('mailer.spool', []);

            if (is_string($config)) {
                //the spool is a service
                return $neptune[$config];
            }

            return $neptune['mailer.factory']->createSpool($config);
        };

        $neptune['mailer.transport.spool'] = function ($neptune) {
            return new \Swift_Transport_SpoolTransport($neptune['mailer.dispatcher'], $neptune['mailer.spool']);
        };

        $neptune['mailer'] = function ($neptune) {
            if ($this->config->get('mailer.spool', false)) {
                $neptune['mailer.spool_used'] = true;

                return new \Swift_Mailer($neptune['mailer.transport.spool']);
            }

            return new \Swift_Mailer($neptune['mailer.transport']);
        };

        $neptune['mailer.dispatcher'] = function () {
            return new \Swift_Events_SimpleEventDispatcher();
        };

        $neptune['mailer.listener'] = function ($neptune) {
            return new SwiftmailerListener($neptune);
        };
    }

    public function boot(Neptune $neptune)
    {
        $neptune['dispatcher']->addSubscriber($neptune['mailer.listener']);
    }
}
