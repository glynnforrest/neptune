<?php

namespace Neptune\Core;

use Neptune\Config\Config;

/**
 * AbstractFactory
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractFactory
{

    protected $config;
    protected $neptune;
    protected $instances = array();

    public function __construct(Config $config, Neptune $neptune)
    {
        $this->config = $config;
        $this->neptune = $neptune;
    }

    /**
     * Get the instance called $name in the config instance. If $name
     * is not specified, the first instance will be returned.
     *
     * @param string $name The name of the instance to get
     */
    public function get($name = null)
    {
        if (!$name) {
            if (!empty($this->instances)) {
                reset($this->instances);

                return current($this->instances);
            } else {
                return $this->create();
            }
        }
        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        return $this->create($name);
    }

    abstract protected function create($name = null);

}
