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

    public function routes(Router $router, $prefix, Neptune $neptune)
    {
        $module = $this->getName();

        $router->route("$prefix/login", "::$module.controller.bar", "{$module}_module_method");
        $router->name('secret')->route("$prefix/secret", "::$module.controller.bar", 'secretArea');
    }

    public function getName()
    {
        return 'test-module';
    }

    public function register(Neptune $neptune)
    {
    }

    public function boot(Neptune $neptune)
    {
    }

}
