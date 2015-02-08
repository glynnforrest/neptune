<?php

namespace Neptune\Config;

use Neptune\Config\Exception\ConfigFileException;
use Neptune\Config\Loader\LoaderInterface;
use Neptune\Config\Processor\ProcessorInterface;

/**
 * ConfigManager
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManager
{
    protected $config;
    protected $loaders = [];
    protected $processors = [];

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
        foreach ($this->processors as $processor) {
            $processor->processBuild($this->config);
        }

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
     * Add a configuration processor.
     *
     * @param ProcessorInterface $processor
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
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
            throw new ConfigFileException(sprintf('Configuration file "%s" not found', $filename));
        }

        foreach ($this->loaders as $loader) {
            if (!$loader->supports($filename)) {
                continue;
            }

            $values = $loader->load($filename);
        }

        if (!isset($values)) {
            throw new ConfigFileException(sprintf('No configuration loader available for "%s"', $filename));
        }

        return $this->loadValues($values, $prefix);
    }

    /**
     * Load an array of configuration settings.
     *
     * @param array       $values The configuration to load
     * @param string|null $prefix The prefix of the values, if any
     */
    public function loadValues(array $values, $prefix = null)
    {
        if ($prefix) {
            $values = [$prefix => $values];
        }

        foreach ($this->processors as $processor) {
            $processor->processLoad($this->config, $values, $prefix);
        }

        $this->config->merge($values);
    }
}
