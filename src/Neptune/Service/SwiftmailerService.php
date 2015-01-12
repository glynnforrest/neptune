<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Swiftmailer\SwiftmailerFactory;
use Neptune\EventListener\SwiftmailerListener;

/**
 * SwiftmailerService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SwiftmailerService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['mailer.factory'] = function ($neptune) {
            return new SwiftmailerFactory($neptune['mailer.dispatcher']);
        };

        $neptune['mailer.transport'] = function ($neptune) {
           return $neptune['mailer.factory']->createTransport($neptune['config']->get('mailer', []));
        };

        $neptune['mailer.spool'] = function ($neptune) {
            $spool_config = $neptune['config']->get('mailer.spool', []);

            if (is_string($spool_config)) {
                //the spool is a service
                return $neptune[$spool_config];
            }

            return $neptune['mailer.factory']->createSpool($spool_config);
        };

        $neptune['mailer.transport.spool'] = function ($neptune) {
            return new \Swift_Transport_SpoolTransport($neptune['mailer.dispatcher'], $neptune['mailer.spool']);
        };

        $neptune['mailer'] = function ($neptune) {
            if ($neptune['config']->get('mailer.spool', false)) {
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
