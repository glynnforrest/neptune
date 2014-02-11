<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Service\ServiceInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * MonologService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MonologService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $neptune['logger'] = function() use ($neptune) {
            $logger = new Logger('neptune');
            $path = $neptune['config']->getPath('log.file');
            $logger->pushHandler(new StreamHandler($path, Logger::DEBUG));
            return $logger;
        };
    }

    public function boot(Neptune $neptune)
    {

    }

}
