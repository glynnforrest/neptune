<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Cache\CacheFactory;

/**
 * CacheService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CacheService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $neptune['cache.factory'] = function ($neptune) {
            return new CacheFactory($neptune['config'], $neptune);
        };

        $neptune['cache'] = function ($neptune) {
            return $neptune['cache.factory']->get();
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
