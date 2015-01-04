<?php

namespace Neptune\Config;

use Neptune\Exceptions\ConfigFileException;
use Neptune\Config\Loader\LoaderInterface;

/**
 * ConfigManager
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManager
{
    protected $config;
    protected $loaders = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get the configuration.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Add a configuration loader.
     *
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        array_unshift($this->loaders, $loader);
    }

    /**
     * Load configuration settings from a file.
     *
     * @param string      $filename
     * @param string|null $prefix
     */
    public function load($filename, $prefix = null)
    {
        if (!file_exists($filename)) {
            throw new ConfigFileException($filename.' not found');
        }

        foreach ($this->loaders as $loader) {
            if (!$loader->supports($filename)) {
                continue;
            }

            $values = $loader->load($filename);
        }

        if (!isset($values)) {
            throw new ConfigFileException(sprintf('No configuration loader available for %s', $filename));
        }

        return $this->loadValues($values, $prefix);
    }

    /**
     * Load an array of configuration settings.
     */
    public function loadValues(array $values, $prefix = null)
    {
        $options = isset($values['_options']) ? $values['_options'] : [];
        unset($values['_options']);

        if ($prefix) {
            $values = [$prefix => $values];
        }

        $values['_options'] = $options;

        $this->config->override($values);
    }
}
