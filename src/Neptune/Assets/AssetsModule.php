<?php

namespace Neptune\Assets;

use Neptune\Service\AbstractModule;

use Neptune\Routing\Router;
use Neptune\Core\Neptune;
use Neptune\Controller\AssetsController;

/**
 * AssetsModule
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsModule extends AbstractModule
{
    protected $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    public function register(Neptune $neptune)
    {
        //if no config was supplied, grab the default
        if (!$this->config) {
            $this->config = $neptune['config'];
        }

        $neptune['assets'] = function($neptune) {
            $url = $neptune['url']->to($neptune['assets.url']);
            $cache_bust = $neptune['config']->getRequired('assets.cache_bust');

            return new AssetManager($neptune['config.manager'], new TagGenerator($url, $cache_bust));
        };

        $neptune['controller.assets'] = function($neptune) {
            return new AssetsController($neptune);
        };
    }

    public function routes(Router $router, $prefix, Neptune $neptune)
    {
        //add a slash if the given url doesn't start or end with one
        $url = $this->config->getRequired('assets.url');
        if (substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        if (substr($url, -1, 1) !== '/') {
            $url .= '/';
        }
        $url = $url . ':asset';
        $router->name('neptune.assets')
               ->route($url, '::controller.assets', 'serveAsset')
               ->format('any')
               ->argsRegex('.+');
    }

    public function boot(Neptune $neptune)
    {
    }

    public function getName()
    {
        return 'neptune-assets';
    }

}
