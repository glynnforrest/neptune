<?php

namespace Neptune\Config;

use Neptune\Exceptions\ConfigFileException;

/**
 * ConfigManager
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManager
{
    protected $config;

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
     * Load configuration settings from a file.
     *
     * @param string $filename
     * @param string|null $prefix
     */
    public function load($filename, $prefix = null)
    {
        if (!file_exists($filename)) {
            throw new ConfigFileException($filename . ' not found');
        }
        ob_start();
        $values = include $filename;
        ob_end_clean();
        if (!is_array($values)) {
            throw new ConfigFileException($filename . ' does not return a php array');
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
