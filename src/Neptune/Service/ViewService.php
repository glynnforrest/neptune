<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\View\ViewCreator;
use Neptune\EventListener\ViewListener;

/**
 * ViewService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['view'] = function ($neptune) {
            $creator = new ViewCreator($neptune);

            foreach ($neptune->getTaggedServices('neptune.view.extensions') as $service) {
                $creator->addExtension($service);
            }

            return $creator;
        };
    }

    public function boot(Neptune $neptune)
    {
        $neptune['dispatcher']->addSubscriber(new ViewListener($neptune, 'view'));
    }
}
