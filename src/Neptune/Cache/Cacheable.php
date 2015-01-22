<?php

namespace Neptune\Cache;

use Neptune\Exceptions\MethodNotFoundException;
use Doctrine\Common\Cache\Cache;

/**
 * Cacheable
 * @author Glynn Forrest <me@glynnforrest.com>
 */
abstract class Cacheable
{
    private $cache;

    public function __call($method, $args)
    {
        if (substr($method, -6) !== 'Cached' || method_exists($this, $method)) {
            throw new MethodNotFoundException('Function not found: '.$method);
        }
        $method = substr($method, 0, -6);

        //if there is no cache available, just call the method
        if (!$this->cache) {
            return call_user_func_array(array($this, $method), $args);
        }

        //build key
        $key = get_class($this).':'.$method;
        //create a unique hash of the args
        foreach ($args as $arg) {
            $key .= ':'.serialize($arg);
        }
        //make sure the key isn't too long
        $key = md5($key);

        //check for a cached version. if it does, return the value
        $result = $this->cache->fetch($key);
        if (false !== $result) {
            return $result;
        }

        //doesn't, call the function and cache it
        $result = call_user_func_array(array($this, $method), $args);
        $this->cache->save($key, $result);

        return $result;
    }

    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getCache()
    {
        return $this->cache;
    }
}
