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
        $neptune['twig'] = function ($neptune) {
            $options = [
                'strict_variables' => true
            ];

            return new \Twig_Environment($neptune['twig.loader'], $options);
        };

        $neptune['twig.loader'] = function ($neptune) {
            return new FilesystemLoader($neptune);
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
