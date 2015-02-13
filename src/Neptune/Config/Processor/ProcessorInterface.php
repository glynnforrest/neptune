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
     * to the incoming values will be kept.
     *
     * @param DotArray    $incoming The incoming values
     * @param string|null $prefix   The prefix of the values, if any
     */
    public function onLoad(DotArray $incoming, $prefix = null);

    /**
     * Process configuration values before they are merged.
     *
     * @param Config     $config   An empty configuration
     * @param DotArray[] $incoming An array of configurations to be merged
     */
    public function onPreMerge(Config $config, array $incoming);

    /**
     * Process the configuration after merging.
     *
     * @param Config $config The merged configuration
     */
    public function onPostMerge(Config $config);
}
