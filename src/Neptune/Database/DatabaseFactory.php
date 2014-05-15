<?php

namespace Neptune\Database;

use Neptune\Database\Driver\PDODriver;
use Neptune\Database\Driver\EventDriver;
use Neptune\Database\Driver\PDOCreator;
use Neptune\Core\AbstractFactory;
use Neptune\Core\Neptune;
use Neptune\Config\Config;

use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

/**
 * DatabaseFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 *
 */
class DatabaseFactory extends AbstractFactory
{

    public function __construct(Config $config, Neptune $neptune, PDOCreator $creator)
    {
        $this->pdo_creator = $creator;

        return parent::__construct($config, $neptune);
    }

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
        $maybe_service = $this->config->getRequired("database.$name");
        if (is_string($maybe_service)) {
            //check the service implements database interface first
            $service = $this->neptune[$maybe_service];
            if ($service instanceof DatabaseDriverInterface) {
                return $service;
            }
            throw new DriverNotFoundException(sprintf(
                "Database driver '%s' requested service '%s' which does not implement Neptune\Database\Driver\DatabaseDriverInterface",
                $name,
                $maybe_service));
        }

        //database driver and is required for all instances
        $driver = $this->config->getRequired("database.$name.driver");

        $method = 'create' . ucfirst($driver) . 'Driver';
        if (method_exists($this, $method)) {
            $this->instances[$name] = $this->$method($name);

            return $this->instances[$name];
        } else {
            throw new DriverNotFoundException(
                "Database driver not implemented: $driver");
        }
    }

    protected function createMysqlDriver($name)
    {
        $host = $this->config->get("database.$name.host", 'localhost');
        $port = $this->config->get("database.$name.port", '3306');
        $user = $this->config->getRequired("database.$name.user");
        $pass = $this->config->getRequired("database.$name.pass");
        $database = $this->config->getRequired("database.$name.database");
        $charset = $this->config->get("database.$name.charset", 'UTF8');
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";
        $options = array(
             \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
        $driver = new PDODriver($this->pdo_creator->createPDO($dsn, $user, $pass, $options));
        $driver->setQueryClass('\\Neptune\\Database\\Query\\MysqlQuery');

        if ($this->config->get("database.$name.events", false)) {
            return new EventDriver($driver, $this->neptune['dispatcher']);
        }

        return $driver;
    }

}
