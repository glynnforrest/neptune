<?php

namespace Neptune\Config;

/**
 * NeptuneConfig
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NeptuneConfig extends Config
{

    /**
     * Create a new NeptuneConfig. If $filename is true,
     * config/neptune.php will be loaded. $filename will be loaded if
     * it's a string. If $filename is false or null, no file will be
     * loaded.
     *
     * @param string $root_directory The path of the app root directory
     * @param mixed  $filename       The filename of the config file
     */
    public function __construct($root_directory, $filename = true)
    {
        //make sure root has a trailing slash
        if (substr($root_directory, -1) !== '/') {
            $root_directory .= '/';
        }
        $this->setRootDirectory($root_directory);

        if ($filename === true) {
            $filename = $root_directory . 'config/neptune.php';
        }

        parent::__construct('neptune', $filename);
    }

}
