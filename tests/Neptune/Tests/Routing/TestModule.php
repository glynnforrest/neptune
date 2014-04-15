<?php

namespace Neptune\Tests\Routing;

use Neptune\Service\AbstractModule;
use Neptune\Routing\Router;
use Neptune\Core\Neptune;

/**
 * TestModule
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TestModule extends AbstractModule
{

    public function routes(Router $router, $prefix, $module)
    {
        $router->globals()->controller("::$module.controller.bar");
        $router->route("$prefix/login")->method("{$module}_module_method");
        $router->name('secret')->route("$prefix/secret")->method('secretArea');
    }

    public function register(Neptune $neptune)
    {
    }

    public function boot(Neptune $neptune)
    {
    }

}
