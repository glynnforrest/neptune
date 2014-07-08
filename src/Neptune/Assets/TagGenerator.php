<?php

namespace Neptune\Assets;

/**
 * TagGenerator
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TagGenerator
{

    protected $assets_url;
    protected $cache_bust;

    public function __construct($assets_url, $cache_bust = false)
    {
        $this->assets_url = $assets_url;
        $this->cache_bust = $cache_bust;
    }

    /**
     * Create a url for an asset. Cache busting will be added if
     * enabled.
     *
     * @param string $src The source
     */
    protected function createUrl($src)
    {
        if (strpos($src, '://')) {
            return $src;
        }
        $url = $this->assets_url . $src;

        return $this->cache_bust ? $url . '?' . md5(uniqid()) : $url;
    }

    /**
     * Create a list of html attributes from an associative array.
     *
     * @param  array  $attributes The attributes
     * @return string The html attributes
     */
    protected function attributes(array $attributes)
    {
        $text = '';
        foreach ($attributes as $key => $value) {
            $text .=  sprintf(' %s="%s"', $key, $value);
        }

        return $text;
    }

    /**
     * Create a css tag.
     *
     * @param string $src        The source
     * @param array  $attributes The html attributes
     */
    public function css($src, array $attributes = array())
    {
        $attributes = array_merge(array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => $this->createUrl($src)), $attributes);

        return sprintf('<link%s />', $this->attributes($attributes)) . PHP_EOL;
    }

    /**
     * Create a javascript tag.
     *
     * @param string $src        The source
     * @param array  $attributes The html attributes
     */
    public function js($src, array $attributes = array())
    {
        $attributes = array_merge(array(
            'type' => 'text/javascript',
            'src' => $this->createUrl($src)), $attributes);

        return sprintf('<script%s></script>', $this->attributes($attributes)) . PHP_EOL;
    }

    /**
     * Enable cache busting for assets by appending a random query
     * string to the end of the url.
     *
     * @param bool $on True to enable, false to disable
     */
    public function setCacheBusting($on = true)
    {
        $this->cache_bust = (bool) $on;
    }

    /**
     * Set the url that assets are served under.
     *
     * @param string $assets_url The base assets url.
     */
    public function setAssetsUrl($assets_url)
    {
        $this->assets_url = $assets_url;
    }

}
