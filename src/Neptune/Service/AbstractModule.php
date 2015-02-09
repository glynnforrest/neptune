<?php

namespace Neptune\Service;

use Neptune\Service\ServiceInterface;
use Neptune\Routing\Router;
use Neptune\Core\Neptune;
use Neptune\Config\ConfigManager;

/**
 * AbstractModule
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractModule implements ServiceInterface
{

    protected $route_prefix;

    public function __construct($route_prefix = '')
    {
        $this->route_prefix = $route_prefix;
    }

    /**
     * Set the route prefix for the module. Pass false to disable routing.
     *
     * @param string|bool $route_prefix The prefix
     */
    public function routePrefix($route_prefix)
    {
        $this->route_prefix = $route_prefix;
    }

    /**
     * Load routes for this module.
     *
     * @param Route $router
     * @param Neptune $neptune
     */
    public function loadRoutes(Router $router, Neptune $neptune)
    {
        if ($this->route_prefix !== false) {
            $this->routes($router, $this->route_prefix, $neptune);
        }
    }

    protected function routes(Router $router, $prefix, Neptune $neptune) {
        //add routes in child classes
    }

    /**
     * Load configuration for this module.
     *
     * @param ConfigManager $config
     */
    public function loadConfig(ConfigManager $config)
    {
        $file = $this->getDirectory() . 'config.yml';
        if (file_exists($file)) {
            $config->load($file);
        }
    }

    abstract public function getName();

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
