<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Config\Exception\ConfigKeyException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Types\Type;
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
            $config = $neptune['config']->get('neptune.database', []);

            if (empty($config)) {
                throw new ConfigKeyException('Database configuration is empty');
            }

            return $config;
        };

        $neptune['dbs'] = function ($neptune) {
            $dbs = new Container();

            $config = $neptune['db.config'];

            //register types
            if (isset($config['_types'])) {
                foreach ($config['_types'] as $name => $classname) {
                    Type::addType($name, $classname);
                }
                unset($config['_types']);
            }

            foreach ($config as $name => $config) {
                $dbs[$name] = function ($dbs) use ($config, $neptune) {
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
