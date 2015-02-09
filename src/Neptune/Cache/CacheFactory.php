<?php

namespace Neptune\Cache;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\Cache;

use Neptune\Core\AbstractFactory;
use Neptune\Config\Exception\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

use \Memcached;

/**
 * CacheFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class CacheFactory extends AbstractFactory
{

    protected function create($name = null)
    {
        if (!$name) {
            $names = array_keys($this->config->get('neptune.cache', array()));
            if (empty($names)) {
                throw new ConfigKeyException(
                    'Cache configuration array is empty');
            }
            $name = $names[0];
        }
        //if the entry in the config is a string, load it as a service
        $maybe_service = $this->config->getRequired("neptune.cache.$name");
        if (is_string($maybe_service)) {
            //check the service implements cache interface first
            $service = $this->neptune[$maybe_service];
            if ($service instanceof Cache) {
                return $service;
            }
            throw new DriverNotFoundException(sprintf(
                "Cache driver '%s' requested service '%s' which does not implement Doctrine\Common\Cache\Cache",
                $name,
                $maybe_service));
        }

        $driver = $this->config->getRequired("neptune.cache.$name.driver");

        $method = 'create' . ucfirst($driver) . 'Driver';
        if (method_exists($this, $method)) {
            $this->instances[$name] = $this->$method($name);

            return $this->instances[$name];
        } else {
            throw new DriverNotFoundException(
                "Cache driver not implemented: $driver");
        }
    }

    protected function createArrayDriver($name)
    {
        return new ArrayCache();
    }

    protected function createFileDriver($name)
    {
        $namespace = $this->config->getRequired("neptune.cache.$name.namespace");
        $dir = $this->config->get("neptune.cache.$name.dir", sys_get_temp_dir());
        if (substr($dir, 0, 1) !== '/') {
            $dir = $this->neptune->getRootDirectory() . $dir;
        }

        $driver = new FilesystemCache($dir);
        $driver->setNamespace($namespace);

        return $driver;
    }

    protected function createMemcachedDriver($name)
    {
        $host = $this->config->get("neptune.cache.$name.host", '127.0.0.1');
        $port = $this->config->get("neptune.cache.$name.port", '11211');
        $memcached = new Memcached();
        $memcached->addserver($host, $port);

        $driver = new MemcachedCache();
        $driver->setNamespace($this->config->getRequired("neptune.cache.$name.namespace"));
        $driver->setMemcached($memcached);

        return $driver;
    }

}
