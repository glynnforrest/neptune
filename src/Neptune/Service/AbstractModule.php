<?php

namespace Neptune\Service;

use Neptune\Service\ServiceInterface;
use Neptune\Routing\Router;
use Neptune\Core\Neptune;

/**
 * AbstractModule
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractModule implements ServiceInterface
{

    abstract public function routes(Router $router, $module, $prefix);

    public function getNamespace()
    {
        $class = get_class($this);

        return substr($class, 0, strrpos($class, '\\'));
    }

    public function getRootDirectory()
    {
        return __DIR__ . '/';
    }

}
