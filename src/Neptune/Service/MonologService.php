<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;

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
        $neptune['logger.name'] = function ($neptune) {
            return $neptune['config']->get('logger.name', 'neptune');
        };

        $neptune['logger.path'] = function ($neptune) {
            return $neptune['config']->getRequired('logger.path');
        };

        $neptune['logger.level'] = function ($neptune) {
            $level = $neptune['config']->get('logger.level', Logger::DEBUG);

            if (is_int($level)) {
                return $level;
            }

            $level = strtoupper($level);
            $levels = Logger::getLevels();

            if (!isset($levels[$level])) {
                throw new \InvalidArgumentException("Invalid log level $level provided");
            }

            return $levels[$level];
        };

        $neptune['logger'] = function ($neptune) {
            $logger = new Logger($neptune['logger.name']);
            $logger->pushHandler(new StreamHandler($neptune['logger.path'], $neptune['logger.level']));

            return $logger;
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
