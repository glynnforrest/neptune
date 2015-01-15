<?php

namespace Neptune\Config\Loader;

use Neptune\Config\Exception\ConfigFileException;

/**
 * PhpLoader
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class PhpLoader implements LoaderInterface
{
    public function load($filename)
    {
        ob_start();
        $values = include $filename;
        ob_end_clean();
        if (!is_array($values)) {
            throw new ConfigFileException($filename . ' does not return a php array');
        }

        return $values;
    }

    public function supports($filename)
    {
        return substr($filename, -4) === '.php';
    }
}
