<?php

namespace Neptune\Config;

use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\ConfigFileException;

use Crutches\DotArray;

/**
 * Config
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Config
{
    const OPTION_NO_MERGE = 'no_merge';

    protected $dot_array;
    protected $original;
    protected $root_dir;

    public function __construct()
    {
        $values = [];
        $this->dot_array = new DotArray($values);
        $this->original = new DotArray($values);

        return true;
    }

    /**
     * Get a configuration value that matches $key.
     * $key uses the dot array syntax: parent.child.child
     * If the key matches an array the whole array will be returned.
     * If no key is specified the entire configuration array will be
     * returned.
     * $default will be returned (null unless specified) if the key is
     * not found.
     */
    public function get($key = null, $default = null)
    {
        return $this->dot_array->get($key, $default);
    }

    /**
     * Get the first value from an array of configuration values that
     * matches $key.
     * $default will be returned (null unless specified) if the key is
     * not found or does not contain an array.
     */
    public function getFirst($key = null, $default = null)
    {
        return $this->dot_array->getFirst($key, $default);
    }

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
            throw new \Exception("Root directory has not been set for Config instance '$this->name'");
        }

        return $this->root_dir . $path;
    }

    /**
     * Get the first value from an array of configuration values that
     * matches $key in the same way as getFirst(), but a
     * ConfigKeyException will be thrown if the key is not found.
     */
    public function getFirstRequired($key)
    {
        $value = $this->dot_array->getFirst($key);
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
        $this->dot_array->set($key, $value);
        $this->original->set($key, $value);

        return $this;
    }

    /**
     * Override values in this Config instance with values from
     * $array. They will not be included in toString() or save().
     */
    public function override(array $array)
    {
        //process any options for specific keys
        $options = $this->dot_array->get('_options', []);
        $override = new DotArray($array);
        foreach ($options as $key => $option) {
            if ($option !== self::OPTION_NO_MERGE) {
                continue;
            }

            if (!$value = $override->get($key)) {
                continue;
            }
            $this->dot_array->set($key, $value);
        }

        //merge the incoming array
        $this->dot_array->merge($array);
    }

    /**
     * Get the current configuration as a string, as it would appear
     * when saved to a file.
     *
     * @return string The current configuration
     */
    public function toString()
    {
        return '<?php return ' . var_export($this->original->get(), true) . '?>';
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
