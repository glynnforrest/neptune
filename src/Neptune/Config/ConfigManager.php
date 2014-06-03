<?php

namespace Neptune\Config;

use Neptune\Exceptions\ConfigFileException;
use Neptune\Core\Neptune;

/**
 * ConfigManager
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManager
{

    protected $configs = array();
    protected $neptune;
    protected $root_directory;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
        $this->root_directory = $neptune->getRootDirectory();
    }

    /**
     * Create config settings with $name.
     * $filename must be specified (or set with setFilename) if the
     * settings are intended to be saved.
     */
    public function create($name, $filename = null)
    {
        $this->configs[$name] = new Config($name);
        $this->configs[$name]->setFilename($filename);
        $this->configs[$name]->setRootDirectory($this->root_directory);

        return $this->configs[$name];
    }

    /**
     * Load config settings with $name from $filename.
     * If $name is loaded, the same Config instance will be
     * returned if $filename is not specified.
     * If $name is loaded and $filename does not match with $name
     * the instance with that name will be overwritten.
     * If $name is not specified, the first loaded config file will be
     * returned, or an exception thrown if no Config configs are
     * set.
     * If $override_name is supplied and matches the name of a loaded
     * config file, the values of that Config instance will be
     * overwritten with the values of the new file.
     */
    public function load($name = null, $filename = null, $override_name = null)
    {
        if (isset($this->configs[$name])) {
            $instance = $this->configs[$name];
            if (!$filename || $instance->getFileName() === $filename) {
                return $instance;
            }
        }
        if (!$name) {
            if (empty($this->configs)) {
                throw new ConfigFileException(
                    'No Config instance loaded, unable to get default');
            }
            reset($this->configs);

            return $this->configs[key($this->configs)];
        }
        if (!$filename) {
            try {
                return $this->loadModule($name);
            } catch (\InvalidArgumentException $e) {
                throw new ConfigFileException(
                    "No filename specified for Config instance $name"
                );
            }
        }
        $config = new Config($name, $filename);
        if ($override_name && isset($this->configs[$override_name])) {
            $this->configs[$override_name]->override($config->get());
        }
        $config->setRootDirectory($this->root_directory);
        $this->configs[$name] = $config;

        return $this->configs[$name];
    }

    /**
     * Load the configuration for a module.
     * This will load the configuration file for the module and also
     * override that configuration with anything found in
     * config/modules/<module_name>.php
     */
    public function loadModule($name = null)
    {
        if (isset($this->configs[$name])) {
            return $this->configs[$name];
        }

        if ($name === null) {
            $name = $this->neptune->getDefaultModule();
        }

        $module = $this->neptune->getModule($name);

        $module_config_file = $module->getDirectory() . 'config.php';
        $module_config = $this->load($name, $module_config_file);

        //Attempt to load a config file with overrides for the
        //module. It should have the path
        //config/modules/<modulename>.php
        try {
            $local_config_file = $this->neptune->getRootDirectory() .
                'config/modules/' . $name . '.php';
            //prepend _ to give it a unique name so it can be used individually.
            $this->load('_' . $name, $local_config_file, $name);
        } catch (ConfigFileException $e) {
            //do nothing if there is no config file defined.
        }

        return $module_config;
    }

    /**
     * Load the configuration for all loaded modules, if available,
     * and return the Config instances as an array.
     *
     * @return array The module Config instances
     */
    public function loadAllModules()
    {
        $configs = array();
        foreach (array_keys($this->neptune->getModules()) as $name) {
            try {
                $configs[] = $this->loadModule($name);
            } catch (ConfigFileException $e) {
                continue;
            }
        }

        return $configs;
    }

    /**
     * Unload configuration settings with $name, requiring them to be
     * reloaded if they are to be used again.
     * If $name is not specified, all configuration files will be
     * unloaded.
     */
    public function unload($name = null)
    {
        if ($name) {
            unset($this->configs[$name]);
        } else {
            $this->configs = array();
        }
    }

    /**
     * Add a Config instance to this ConfigManager.
     *
     * @param Config The Config instance
     */
    public function add(Config $config)
    {
        $config->setRootDirectory($this->root_directory);
        $this->configs[$config->getName()] = $config;
    }

    /**
     * Save all configs.
     */
    public function saveAll()
    {
        foreach ($this->configs as $config) {
            $config->save();
        }

        return true;
    }

    /**
     * Get the names of all loaded configs.
     */
    public function getNames()
    {
        return array_keys($this->configs);
    }

}
