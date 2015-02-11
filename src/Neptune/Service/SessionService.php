<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\EventListener\SessionListener;
use Neptune\Service\ServiceInterface;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * SessionService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SessionService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $neptune['session'] = function ($neptune) {
            return new Session();
        };

        $neptune['session.listener'] = function ($neptune) {
            return new SessionListener($neptune);
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
