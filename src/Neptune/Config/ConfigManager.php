<?php

namespace Neptune\Config;

use Neptune\Config\Exception\ConfigFileException;
use Neptune\Config\Loader\LoaderInterface;
use Neptune\Config\Processor\ProcessorInterface;
use Crutches\DotArray;

/**
 * ConfigManager
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigManager
{
    protected $configs = [];
    protected $loaders = [];
    protected $processors = [];
    protected $loaded_files = [];

    /**
     * Get the configuration.
     *
     * @return Config
     */
    public function getConfig()
    {
        $configuration = new Config();
        foreach ($this->processors as $processor) {
            $processor->onPreMerge($configuration, $this->configs);
        }

        foreach ($this->configs as $config) {
            $configuration->merge($config->get());
        }

        foreach ($this->processors as $processor) {
            $processor->onPostMerge($configuration);
        }

        return $configuration;
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
     * Get a suitable message to accompany a cache of the current
     * configuration.
     *
     * @return string the message
     */
    public function getCacheMessage()
    {
        $message = 'Configuration cache generated '.date('Y/m/d H:i:s').PHP_EOL;

        $message .= PHP_EOL.'Loaded files:'.PHP_EOL;

        foreach ($this->loaded_files as $file) {
            $message .= $file[0];
            if ($file[1]) {
                $message .= sprintf(' (prefix "%s")', $file[1]);
            }
            $message .= PHP_EOL;
        }

        $message .= PHP_EOL.'Active processors:'.PHP_EOL;

        foreach ($this->processors as $processor) {
            $message .= get_class($processor).PHP_EOL;
        }

        return $message;
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

        $this->loaded_files[] = [$filename, $prefix];

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

        $incoming = new DotArray($values);

        foreach ($this->processors as $processor) {
            $processor->onLoad($incoming, $prefix);
        }

        $this->configs[] = $incoming;
    }
}
