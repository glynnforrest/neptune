<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Twig\Loader\FilesystemLoader;
use Neptune\Twig\TwigEnvironment;
use Neptune\EventListener\TwigExceptionListener;

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

            return array_merge($defaults, $neptune['config']->get('twig', []));
        };

        $neptune['twig'] = function ($neptune) {
            $environment = new TwigEnvironment($neptune['twig.loader'], $neptune['twig.options']);

            foreach ($neptune->getTaggedServices('twig.extensions') as $service) {
                $environment->addExtension($service);
            }

            $environment->addGlobal('app', $neptune);

            return $environment;
        };

        $neptune['twig.loader'] = function ($neptune) {
            return new FilesystemLoader($neptune);
        };

        $neptune['twig.exception_listener'] = function ($neptune) {
            return new TwigExceptionListener($neptune['twig']);
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
