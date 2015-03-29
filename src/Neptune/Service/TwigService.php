<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Twig\Loader\FilesystemLoader;
use Neptune\Twig\Extension\AssetsExtension;

/**
 * TwigService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['twig'] = function ($neptune) {
            $options = [
                'strict_variables' => true
            ];

            $environment = new \Twig_Environment($neptune['twig.loader'], $options);

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
