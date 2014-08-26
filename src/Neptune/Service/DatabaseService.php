<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Exceptions\ConfigKeyException;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;

/**
 * DatabaseService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseService implements ServiceInterface
{

    public function register(Neptune $neptune)
    {
        $config = $neptune['config'];

        $names = array_keys($config->get('database', []));
        if (empty($names)) {
            throw new ConfigKeyException('Database configuration is empty');
        }

        foreach ($names as $name) {
            $neptune["db.$name"] = function ($neptune) use ($name, $config) {
                $configuration = new Configuration();

                $logger = $config->get("database.$name.logger", false);
                if ($logger) {
                    $configuration->setSQLLogger(new \Neptune\Database\PsrSqlLogger($neptune[$logger]));
                }

                return DriverManager::getConnection($config->getRequired("database.$name"), $configuration);
            };
        }

        //shortcut for the first database
        $neptune['db'] = function ($neptune) use ($names) {
            $name = $names[0];

            return $neptune["db.$name"];
        };
    }

    public function boot(Neptune $neptune)
    {
    }

}
