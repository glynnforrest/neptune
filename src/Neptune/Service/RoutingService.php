<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;

use Neptune\Helpers\Url;
use Neptune\Routing\Router;
use Neptune\Routing\ControllerResolver;
use Neptune\EventListener\RouterListener;

/**
 * RoutingService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RoutingService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $neptune['url'] = function ($neptune) {
            return new Url($neptune['config']->getRequired('root_url'));
        };

        $neptune['router'] = function ($neptune) {
            return new Router($neptune['url']);
        };

        $neptune['resolver'] = function ($neptune) {
            return new ControllerResolver($neptune);
        };

        $neptune['router.listener'] = function ($neptune) {
            return new RouterListener($neptune['router'], $neptune);
        };

    }

    public function boot(Neptune $neptune)
    {
        $neptune['dispatcher']->addSubscriber($neptune['router.listener']);
    }

}
