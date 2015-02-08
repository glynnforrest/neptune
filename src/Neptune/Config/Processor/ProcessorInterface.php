<?php

namespace Neptune\Config\Processor;

use Neptune\Config\Config;
use Crutches\DotArray;

/**
 * ProcessorInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface ProcessorInterface
{
    /**
     * Process incoming configuration values. Any modifications made
     * to the current configuration or incoming values will be kept.
     *
     * @param Config      $config   The current configuration
     * @param DotArray    $incoming The incoming values
     * @param string|null $prefix   The prefix of the values, if any
     */
    public function processLoad(Config $config, DotArray $incoming, $prefix = null);

    /**
     * Process the configuration before it is completed.
     *
     * @param Config $config The current configuration
     */
    public function processBuild(Config $config);
}
