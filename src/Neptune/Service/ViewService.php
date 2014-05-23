<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\View\ViewCreator;

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
            return new ViewCreator($neptune);
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
