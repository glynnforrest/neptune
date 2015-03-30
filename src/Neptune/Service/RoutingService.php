<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;

use Neptune\Routing\Url;
use Neptune\Routing\Router;
use Neptune\Routing\ControllerResolver;
use Neptune\EventListener\RouterListener;
use Neptune\View\Extension\UrlExtension;
use Neptune\Twig\Extension\RoutingExtension;

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
            return new Url($neptune['config']->getRequired('neptune.routing.root_url'));
        };

        $neptune['router'] = function ($neptune) {
            $router = new Router($neptune['url']);
            if ($cache = $neptune['config']->get('neptune.routing.cache')) {
                $router->setCache($neptune[$cache]);
            }

            return $router;
        };

        $neptune['resolver'] = function ($neptune) {
            return new ControllerResolver($neptune);
        };

        $neptune['router.listener'] = function ($neptune) {
            return new RouterListener($neptune['router'], $neptune);
        };

        $neptune['view.extension.url'] = function ($neptune) {
            return new UrlExtension($neptune['router'], $neptune['url']);
        };

        $neptune['twig.extension.routing'] = function ($neptune) {
            return new RoutingExtension($neptune['router'], $neptune['url']);
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
