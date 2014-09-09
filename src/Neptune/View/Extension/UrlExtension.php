<?php

namespace Neptune\View\Extension;

use Neptune\Helpers\Url;
use Neptune\Routing\Router;

/**
 * UrlExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UrlExtension implements ExtensionInterface
{

    protected $router;
    protected $url;

    public function __construct(Router $router, Url $url)
    {
        $this->router = $router;
        $this->url = $url;
    }

    public function getHelpers()
    {
        return [
            'to' => 'to',
            'toRoute' => 'toRoute'
        ];
    }

    public function to($url)
    {
        return $this->url->to($url);
    }

    public function toRoute($name, array $args = [], $protocol = 'http')
    {
        return $this->router->url($name, $args, $protocol);
    }

}
