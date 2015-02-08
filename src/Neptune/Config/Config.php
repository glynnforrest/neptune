<?php

namespace Neptune\Config;

use Neptune\Config\Exception\ConfigKeyException;
use Crutches\DotArray;

/**
 * Config
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Config extends DotArray
{
    protected $root_dir;

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
     * Get a directory path from the configuration value that matches
     * $key. The value will be added to the root directory to form a
     * complete directory path. If the value begins with a slash it
     * will be treated as an absolute path and returned explicitly. A
     * ConfigKeyException will be thrown if the path can't be
     * resolved.
     *
     * @param string $key The key in the config file
     */
    public function getPath($key)
    {
        $path = $this->getRequired($key);
        if (substr($path, 0, 1) === '/') {
            return $path;
        }
        if (!$this->root_dir) {
            throw new \Exception('Configuration root directory has not been set.');
        }

        return $this->root_dir.$path;
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

    /**
     * Set the path of the root directory of the application. The
     * directory is used in the getPath() method.
     *
     * @return string The root directory, with a trailing slash
     */
    public function setRootDirectory($directory)
    {
        $this->root_dir = $directory;

        return $this;
    }

    /**
     * Get the path of the root directory of the application.
     *
     * @return string The root directory, with a trailing slash.
     */
    public function getRootDirectory()
    {
        return $this->root_dir;
    }
}
