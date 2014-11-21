<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\View\ViewCreator;

use Neptune\EventListener\ViewListener;

use Neptune\View\Extension\AssetsExtension;
use Neptune\View\Extension\SecurityExtension;
use Neptune\View\Extension\UrlExtension;

/**
 * ViewService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $neptune['view'] = function($neptune) {
            $creator = new ViewCreator($neptune);

            if ($neptune->offsetExists('assets')) {
                $creator->addExtension(new AssetsExtension($neptune['assets']));
            }

            if ($neptune->offsetExists('security')) {
                $creator->addExtension(new SecurityExtension($neptune['security']));
            }

            if ($neptune->offsetExists('router')) {
                $creator->addExtension(new UrlExtension($neptune['router'], $neptune['url']));
            }

            return $creator;
        };
    }

    public function boot(Neptune $neptune)
    {
        $neptune['dispatcher']->addSubscriber(new ViewListener($neptune, 'view'));
    }

}
