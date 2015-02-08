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

    public function processLoad(Config $config, array $values, $prefix = null)
    {
        $override = new DotArray($values);

        $options_key = $prefix ? $prefix.'._options' : '_options';

        $incoming_options = $override->exists($options_key) ? $override->get($options_key) : [];
        $override->remove($options_key);

        $config->merge(['_options' => $incoming_options]);

        foreach ($config->get('_options') as $key => $option) {
            if ($option !== self::OPTION_OVERWRITE) {
                continue;
            }

            $value = $override->get($key);
            if (!$value && $value !== []) {
                continue;
            }
            $config->set($key, $value);
            $override->remove($key);
        }
    }

    public function processBuild(Config $config)
    {
    }
}
