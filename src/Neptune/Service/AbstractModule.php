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

    abstract public function routes(Router $router, $prefix, $module);

    public function getNamespace()
    {
        $class = get_class($this);

        return substr($class, 0, strrpos($class, '\\'));
    }

    public function getDirectory()
    {
        $self = new \ReflectionObject($this);

        return dirname($self->getFileName()) . '/';
    }

}
