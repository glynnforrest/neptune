<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Database\DatabaseFactory;

/**
 * DatabaseService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {

        $neptune['database.factory'] = function($neptune) {
            return new DatabaseFactory($neptune['config'], $neptune);
        };

        $neptune['db'] = function($neptune) {
            return $neptune['database.factory']->get();
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
