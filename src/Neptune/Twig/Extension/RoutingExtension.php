<?php

namespace Neptune\Twig\Extension;

use Neptune\Routing\Router;

/**
 * RoutingExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RoutingExtension extends \Twig_Extension
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getName()
    {
        return 'routing';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('route', [$this, 'route']),
        ];
    }

    public function route($name, array $args = [], $protocol = 'http')
    {
        return $this->router->url($name, $args, $protocol);
    }
}
