<?php

namespace Neptune\Config\Loader;

/**
 * LoaderInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface LoaderInterface
{
    /**
     * Load configuration from a file.
     *
     * @param  string $filename
     * @return array  An array of configuration
     */
    public function load($filename);

    /**
     * Check if this loader can load configuration from a file.
     *
     * @param  string $filename
     * @return bool
     */
    public function supports($filename);
}
