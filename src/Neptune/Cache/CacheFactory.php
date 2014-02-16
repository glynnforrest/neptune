<?php

namespace Neptune\Cache;

use Neptune\Cache\Driver\DebugDriver;
use Neptune\Cache\Driver\FileDriver;
use Neptune\Cache\Driver\MemcachedDriver;
use Neptune\Cache\Driver\CacheDriverInterface;

use Neptune\Core\AbstractFactory;
use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

use \Memcached;

use Temping\Temping;

/**
 * CacheFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class CacheFactory extends AbstractFactory
{

    protected function create($name = null)
    {
        if (!$name) {
            $names = array_keys($this->config->get('cache', array()));
            if (empty($names)) {
                throw new ConfigKeyException(
                    'Cache configuration array is empty');
            }
            $name = $names[0];
        }
        //if the entry in the config is a string, load it as a service
        $maybe_service = $this->config->getRequired("cache.$name");
        if (is_string($maybe_service)) {
            //check the service implements cache interface first
            $service = $this->neptune[$maybe_service];
            if ($service instanceof CacheDriverInterface) {
                return $service;
            }
            throw new DriverNotFoundException(sprintf(
                "Cache driver '%s' requested service '%s' which does not implement Neptune\Cache\Driver\CacheDriverInterface",
                $name,
                $maybe_service));
        }

        //cache driver and prefix are required for all instances
        $driver = $this->config->getRequired("cache.$name.driver");
        $prefix = $this->config->getRequired("cache.$name.prefix");

        $method = 'create' . ucfirst($driver) . 'Driver';
        if (method_exists($this, $method)) {
            $this->instances[$name] = $this->$method($name, $prefix);

            return $this->instances[$name];
        } else {
            throw new DriverNotFoundException(
                "Cache driver not implemented: $driver");
        }
    }

    protected function createDebugDriver($name, $prefix)
    {
        return new DebugDriver($prefix);
    }

    protected function createFileDriver($name, $prefix)
    {
        $dir = $this->config->get("cache.$name.dir");
        if ($dir && substr($dir, 0, 1) !== '/') {
            $dir = $this->config->getRequired('dir.root') . $dir;
        }

        return new FileDriver($prefix, new Temping($dir));
    }

    protected function createMemcachedDriver($name, $prefix)
    {
        $host = $this->config->getRequired("cache.$name.host");
        $port = $this->config->getRequired("cache.$name.port");
        $memcached = new Memcached();
        $memcached->addserver($host, $port);

        return new MemcachedDriver($prefix, $memcached);
    }

}
