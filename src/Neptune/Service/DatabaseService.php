<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Exceptions\ConfigKeyException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Neptune\Database\PsrSqlLogger;
use Pimple\Container;

/**
 * DatabaseService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['db.config'] = function ($neptune) {
            $config = $neptune['config']->get('database', []);

            if (empty($config)) {
                throw new ConfigKeyException('Database configuration is empty');
            }

            return $config;
        };

        $neptune['dbs'] = function ($neptune) {
            $dbs = new Container();

            foreach ($neptune['db.config'] as $name => $config) {
                $dbs[$name] = function ($dbs) use ($name, $config, $neptune) {
                    $configuration = new Configuration();
                    if (isset($config['logger'])) {
                        $configuration->setSQLLogger(new PsrSqlLogger($neptune[$config['logger']]));
                    }

                    return DriverManager::getConnection($config, $configuration);
                };
            }

            return $dbs;
        };

        //shortcut for the first database
        $neptune['db'] = function ($neptune) {
            $config = $neptune['db.config'];
            reset($config);
            $default = key($config);

            return $neptune['dbs'][$default];
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}
