<?php

namespace Neptune\Config\Loader;

/**
 * JsonLoader
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class JsonLoader implements LoaderInterface
{
    public function load($filename)
    {
        $values = json_decode(file_get_contents($filename), true);

        return is_array($values) ? $values : [];
    }

    public function supports($filename)
    {
        return substr($filename, -5) === '.json';
    }
}
