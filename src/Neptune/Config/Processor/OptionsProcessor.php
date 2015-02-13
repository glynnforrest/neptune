<?php

namespace Neptune\Config\Processor;

use Neptune\Config\Config;
use Crutches\DotArray;

/**
 * OptionsProcessor
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class OptionsProcessor extends AbstractProcessor
{
    const OPTION_OVERWRITE = 'overwrite';
    const OPTION_COMBINE = 'combine';
    const OPTION_MERGE = 'merge';

    protected $options = [];

    public function onLoad(DotArray $incoming, $prefix = null)
    {
        $options_key = $prefix ? $prefix.'._options' : '_options';
        $incoming_options = $incoming->exists($options_key) ? $incoming->get($options_key) : [];

        $this->options = array_merge($this->options, $incoming_options);
        $incoming->remove($options_key);
    }

    public function onPreMerge(Config $config, array $incoming)
    {
        foreach ($this->options as $key => $option) {
            switch ($option) {
            case self::OPTION_OVERWRITE:
                if ($value = $this->resolveOverwrite($key, $incoming)) {
                    $config->set($key, $value);
                }
                continue;
            case self::OPTION_COMBINE:
                if ($value = $this->resolveCombined($key, $incoming)) {
                    $config->set($key, $value);
                }
                continue;
            default:
                continue;
            }
        }
        $config->set('_options', $this->options);
    }

    protected function resolveOverwrite($key, array $incoming_configs)
    {
        $overwrite = null;
        foreach ($incoming_configs as $incoming) {
            $value = $incoming->get($key);
            if (!$value && $value !== []) {
                continue;
            }
            $overwrite = $value;
            $incoming->remove($key);
        }

        return $overwrite;
    }

    protected function resolveCombined($key, array $incoming_configs)
    {
        $combined = [];
        foreach ($incoming_configs as $incoming) {
            $value = $incoming->get($key, []);
            if (!is_array($value)) {
                continue;
            }
            $combined = array_merge($combined, array_values($value));
            $incoming->remove($key);
        }

        return $combined;
    }
}
