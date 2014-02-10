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
    }

    public function boot(Neptune $neptune)
    {
        //register a listener that will attach the session driver to the request
        $dispatcher = $neptune['dispatcher'];
        $dispatcher->addSubscriber(new SessionListener($neptune));
    }

}
