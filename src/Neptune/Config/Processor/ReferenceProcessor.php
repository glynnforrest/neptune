<?php

namespace Neptune\Config\Processor;

use Neptune\Config\Config;
use Neptune\Config\Exception\ConfigKeyException;

/**
 * ReferenceProcessor resolves references to other configuration keys
 * and detects circular references.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReferenceProcessor extends AbstractProcessor
{
    public function onPostMerge(Config $config)
    {
        try {
            foreach ($config as $key => $value) {
                $config->set($key, $this->resolveValue($config, $value));
            }
        } catch (ConfigKeyException $e) {
            throw new ConfigKeyException(sprintf('Error resolving references in configuration key "%s"', $key), 1, $e);
        }
    }

    /**
     * Resolve a configuration value by replacing any %tokens% with
     * their substituted values.
     *
     * @param Config $config
     * @param string $value
     */
    protected function resolveValue(Config $config, $value)
    {
        preg_match_all('/%([^%]+)%/', $value, $matches);
        if (!$matches) {
            return;
        }

        foreach ($matches[1] as $config_key) {
            $replacement = $this->resolveValue($config, $config->getRequired($config_key));
            $value = str_replace('%'.$config_key.'%', $replacement, $value);
        }

        return $value;
    }
}
