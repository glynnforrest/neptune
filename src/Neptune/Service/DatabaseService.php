<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Database\DatabaseFactory;
use Neptune\Database\Driver\PDOCreator;
use Neptune\Database\EventListener\LoggerListener;

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
            return new DatabaseFactory($neptune['config'], $neptune, new PDOCreator());
        };

        $neptune['db'] = function($neptune) {
            return $neptune['database.factory']->get();
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
