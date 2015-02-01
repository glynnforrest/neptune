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
    private $lifetime;
    private $default_lifetime = 0;

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

        $lifetime = $this->lifetime !== null ? $this->lifetime : $this->default_lifetime;
        $this->cache->save($key, $result, $lifetime);
        $this->lifetime = null;

        return $result;
    }

    /**
     * Set the cache lifetime for the next cached method. The cache
     * lifetime will be reset to the default after a cached method is
     * called.
     *
     * @param  int   $lifetime
     * @return mixed This object
     */
    public function lifetime($lifetime)
    {
        $this->lifetime = (int) $lifetime;

        return $this;
    }

    /**
     * Set the default cache lifetime.
     *
     * @param  int   $lifetime
     * @return mixed This object
     */
    public function setDefaultLifetime($lifetime)
    {
        $this->default_lifetime = (int) $lifetime;

        return $this;
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
