<?php

namespace Neptune\Config;

use Neptune\Config\Exception\ConfigKeyException;
use Crutches\DotArray;
use IteratorAggregate;
use ArrayIterator;

/**
 * Config
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Config extends DotArray implements IteratorAggregate
{
    /**
     * Get a configuration value that matches $key in the same way as
     * get(), but a ConfigKeyException will be thrown if
     * the key is not found.
     */
    public function getRequired($key)
    {
        $value = $this->get($key);
        if (null !== $value) {
            return $value;
        }
        throw new ConfigKeyException("Required value not found: $key");
    }

    /**
     * Get the first value from an array of configuration values that
     * matches $key in the same way as getFirst(), but a
     * ConfigKeyException will be thrown if the key is not found.
     */
    public function getFirstRequired($key)
    {
        $value = $this->getFirst($key);
        if ($value) {
            return $value;
        }
        throw new ConfigKeyException("Required first value not found: $key");
    }

    /**
     * Set a configuration value with $key.
     * $key uses the dot array syntax: parent.child.child.
     * If $value is an array this will also be accessible using the
     * dot array syntax.
     */
    public function set($key, $value)
    {
        parent::set($key, $value);

        return $this;
    }

    /**
     * Override values in this Config instance with values from
     * $array.
     */
    public function override(array $array)
    {
        //merge the incoming array
        $this->merge($array);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->flatten($this->get()));
    }

    private function flatten(array $array, $key_prefix = '')
    {
        $values = [];
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $values[$key_prefix.$key] = $value;
                continue;
            }
            $values = array_merge($values, $this->flatten($value, $key_prefix.$key.'.'));
        }

        return $values;
    }
}
