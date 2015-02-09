<?php

namespace Neptune\Config\Processor;

use Neptune\Config\Config;
use Crutches\DotArray;

/**
 * OptionsProcessor
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class OptionsProcessor implements ProcessorInterface
{
    const OPTION_OVERWRITE = 'overwrite';
    const OPTION_COMBINE = 'combine';
    const OPTION_MERGE = 'merge';

    public function processLoad(Config $config, DotArray $incoming, $prefix = null)
    {
        $options_key = $prefix ? $prefix.'._options' : '_options';
        $incoming_options = $incoming->exists($options_key) ? $incoming->get($options_key) : [];

        $config->merge(['_options' => $incoming_options]);

        foreach ($config->get('_options') as $key => $option) {
            switch ($option) {
            case self::OPTION_OVERWRITE:
                $value = $incoming->get($key);
                if (!$value && $value !== []) {
                    continue;
                }
                $config->set($key, $value);
                continue;
            case self::OPTION_COMBINE:
                $current = $config->get($key, []);
                $value = $incoming->get($key, []);
                if (!is_array($current) || !is_array($value)) {
                    continue;
                }
                $combined = array_unique(array_merge(array_values($current), array_values($value)));
                $config->set($key, $combined);
                continue;
            default:
                continue;
            }
        }
    }

    public function processBuild(Config $config)
    {
    }
}
