<?php

namespace Neptune\Config\Processor;

use Crutches\DotArray;
use Neptune\Config\Config;

/**
 * AbstractProcessor
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractProcessor implements ProcessorInterface
{
    public function onLoad(DotArray $incoming, $prefix = null)
    {
    }

    public function onPreMerge(Config $config, array $incoming)
    {
    }

    public function onPostMerge(Config $config)
    {
    }
}
