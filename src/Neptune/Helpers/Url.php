<?php

namespace Neptune\Helpers;

/**
 * Url
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Url
{
    protected $root_url;

    public function __construct($root_url)
    {
        //make sure url has a trailing slash
        if (substr($root_url, -1) !== '/') {
            $root_url .= '/';
        }
        $this->root_url = $root_url;
    }

    public function to($url = '', $protocol = 'http')
    {
        if (strpos($url, '://')) {
            return $url;
        }
        if (substr($url, 0, 1) == '/') {
            $url = substr($url, 1);
        }

        return $protocol . '://' . $this->root_url . $url;
    }

}
