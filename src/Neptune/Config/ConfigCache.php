<?php

namespace Neptune\Config;

use Neptune\Config\Exception\ConfigFileException;

/**
 * ConfigCache
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigCache
{
    protected $cache_file;

    public function __construct($cache_file)
    {
        $this->cache_file = $cache_file;
    }

    /**
     * Check if the cache is saved.
     *
     * @return bool
     */
    public function isSaved()
    {
        return file_exists($this->cache_file);
    }

    /**
     * Get configuration from the cache.
     *
     * @return Config
     */
    public function getConfig()
    {
        ob_start();
        $values = include $this->cache_file;
        ob_end_clean();

        if (!is_array($values)) {
            throw new ConfigFileException(sprintf('Configuration cache for "%s" is invalid.', $this->cache_file));
        }

        return new Config($values);
    }

    /**
     * Save configuration to the cache.
     *
     * @param Config $config
     */
    public function save(Config $config)
    {
        $cache = '<?php'.PHP_EOL;
        $cache .= '// configuration cache generated '.date('Y/m/d h:i:s').PHP_EOL;
        $cache .= sprintf('return %s;', var_export($config->get(), true));

        $directory = dirname($this->cache_file);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        return file_put_contents($this->cache_file, $cache);
    }
}
