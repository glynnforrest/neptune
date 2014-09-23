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

    public function register(Neptune $neptune)
    {
        $neptune['assets.url'] = function ($neptune) {
            $url =  $neptune['config']->getRequired('assets.url');
            //add a slash if the given url doesn't end with one
            if (substr($url, -1, 1) !== '/') {
                $url .= '/';
            }

            return $url;
        };

        $neptune['assets'] = function ($neptune) {
            return new AssetManager($neptune['config.manager'], new TagGenerator($neptune['url']->to($neptune['assets.url'])));
        };

        $neptune['controller.assets'] = function ($neptune) {
            return new AssetsController($neptune);
        };
    }

    public function loadRoutes(Router $router, Neptune $neptune)
    {
        $url = $neptune['assets.url'];

        $router->name('neptune:assets')
            ->route($url . ':asset', '::controller.assets', 'serveAsset')
            ->format(true)
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
