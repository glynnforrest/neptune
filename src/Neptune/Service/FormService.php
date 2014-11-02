<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Form\FormCreator;

/**
 * FormService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $neptune['form'] = function ($neptune) {
            return new FormCreator($neptune, $neptune['dispatcher']);
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
