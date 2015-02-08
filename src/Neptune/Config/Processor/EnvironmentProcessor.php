<?php

namespace Neptune\Config\Processor;

use Neptune\Config\Config;
use Neptune\Core\Neptune;
use Crutches\DotArray;

/**
 * EnvironmentProcessor
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvironmentProcessor implements ProcessorInterface
{
    protected $neptune;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function processLoad(Config $config, DotArray $incoming, $prefix = null)
    {
    }

    public function processBuild(Config $config)
    {
        $config->set('ROOT', $this->neptune->getRootDirectory());
        $config->set('ENV', $this->neptune->getEnv());
    }
}
