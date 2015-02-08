<?php

namespace Neptune\Config\Processor;

use Neptune\Config\Config;

/**
 * ProcessorInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface ProcessorInterface
{
    /**
     * Process incoming configuration values.
     *
     * @param Config      $config The current configuration
     * @param array       $values The incoming values
     * @param string|null $prefix The prefix of the values, if any
     */
    public function processLoad(Config $config, array $values, $prefix = null);

    /**
     * Process the configuration before it is completed.
     *
     * @param Config $config The current configuration
     */
    public function processBuild(Config $config);
}
