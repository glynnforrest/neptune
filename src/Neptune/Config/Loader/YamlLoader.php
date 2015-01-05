<?php

namespace Neptune\Config\Loader;

use Neptune\Exceptions\ConfigFileException;
use Symfony\Component\Yaml\Yaml;

/**
 * YamlLoader
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class YamlLoader implements LoaderInterface
{
    public function load($filename)
    {
        $values = Yaml::parse(file_get_contents($filename));

        return is_array($values) ? $values : [];
    }

    public function supports($filename)
    {
        return substr($filename, -4) === '.yml';
    }
}
