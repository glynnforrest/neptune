<?php

namespace Neptune\Database;

use Neptune\Core\AbstractFactory;
use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

/**
 * DatabaseFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 *
 */
class DatabaseFactory extends AbstractFactory
{

    protected $defaults = [
        'pdo_mysql' => [
            'port' => 3306,
            'host' => '127.0.0.1',
            'charset' => 'utf8'
        ]
    ];

    protected function create($name = null)
    {
        if (!$name) {
            $names = array_keys($this->config->get('database', array()));
            if (empty($names)) {
                throw new ConfigKeyException(
                    'Database configuration array is empty');
            }
            $name = $names[0];
        }
        //if the entry in the config is a string, load it as a service
        $config_array = $this->config->getRequired("database.$name");
        if (is_string($config_array)) {
            //check the service implements database interface first
            $service = $this->neptune[$config_array];
            if ($service instanceof Connection) {
                $this->instances[$name] = $service;

                return $service;
            }
            throw new DriverNotFoundException(sprintf(
                "The database '%s' requested service '%s' which is not an instance of Doctrine\DBAL\Connection",
                $name,
                $config_array));
        }

        //database driver is required for all instances
        $driver = $this->config->getRequired("database.$name.driver");

        //merge config with driver defaults
        if (isset($this->defaults[$driver])) {
            $config_array = array_merge($this->defaults[$driver], $config_array);
        }

        $configuration = new Configuration();
        if ($this->config->get("database.$name.logging", false)) {
            //set sql logger here
        }

        $this->instances[$name] = DriverManager::getConnection($config_array, $configuration);

        return $this->instances[$name];
    }

}
