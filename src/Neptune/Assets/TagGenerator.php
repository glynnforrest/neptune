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
     * Create a css tag.
     *
     * @param string $src The source
     */
    public function css($src)
    {
        return sprintf('<link rel="stylesheet" type="text/css" href="%s" />', $this->createUrl($src)) . PHP_EOL;
    }

    /**
     * Create a javascript tag.
     *
     * @param string $src The source
     */
    public function js($src)
    {
        return sprintf('<script type="text/javascript" src="%s"></script>', $this->createUrl($src)) . PHP_EOL;
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

    /**
     * Create an inline javascript tag.
     *
     * @param string $js The javascript code
     */
    public function inlineJs($js)
    {
        return sprintf('<script type="text/javascript">%s</script>', PHP_EOL . $js . PHP_EOL) . PHP_EOL;
    }

    /**
     * Create an inline css tag.
     *
     * @param string $css The css code
     */
    public function inlineCss($css)
    {
        return sprintf('<style type="text/css">%s</style>', PHP_EOL . $css . PHP_EOL) . PHP_EOL;
    }

}
