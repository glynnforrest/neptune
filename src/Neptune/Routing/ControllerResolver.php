<?php

namespace Neptune\Routing;

use Neptune\Core\Neptune;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerResolver
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ControllerResolver implements ControllerResolverInterface
{
    protected $neptune;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function getController(Request $request)
    {
        $controller = $request->attributes->get('_controller');
        //controller can be either a single name of a controller, or a
        //name prefixed with the module, e.g. 'foo' or 'my-module:foo'
        if (strpos($controller, ':')) {
            list($module, $controller_name) = explode(':', $controller, 2);
        } else {
            $module = $this->neptune->getDefaultModule();
            $controller_name = $controller;
        }
        $module = $this->neptune->getModuleNamespace($module);
        $class = sprintf('%s\\Controller\\%sController', $module, ucfirst($controller_name));
        $method = $request->attributes->get('_method') . 'Action';
        if (!class_exists($class)) {
            throw new \Exception(sprintf('Controller not found: %s', $class));
        }

        return array(new $class($request), $method);
    }

    public function getArguments(Request $request, $controller)
    {
        $args = $request->attributes->get('_args');

        return $args;
    }

}
