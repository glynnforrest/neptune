<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Twig\Loader\FilesystemLoader;

/**
 * TwigService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['twig.options'] = function ($neptune) {
            $defaults = [
                'strict_variables' => true,
            ];

            return array_merge($defaults, $neptune['config']->get('twig'));
        };

        $neptune['twig'] = function ($neptune) {
            $environment = new \Twig_Environment($neptune['twig.loader'], $neptune['twig.options']);

            foreach ($neptune->getTaggedServices('twig.extensions') as $service) {
                $environment->addExtension($service);
            }

            return $environment;
        };

        $neptune['twig.loader'] = function ($neptune) {
            return new FilesystemLoader($neptune);
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
