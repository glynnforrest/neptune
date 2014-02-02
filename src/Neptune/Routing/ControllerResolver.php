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
        if(!$controller) {
            throw new \Exception(sprintf(
                'No _controller attribute set on Request with URI %s',
                $request->getPathInfo()));
        }

        $method = $request->attributes->get('_method');
        if(!$method) {
            throw new \Exception(sprintf(
                'No _method attribute set on Request with URI %s',
                $request->getPathInfo()));
        }
        $method .= 'Action';

        //check if controller is defined as a service (it begins with
        //'::')
        $prefix = '::';
        if (substr($controller, 0, 2) === $prefix) {
            $service = substr($controller, 2);
            if(!$this->neptune->offsetExists($service)) {
                throw new \Exception(sprintf('Undefined controller service %s', $service));
            }
            return array($this->neptune[$service], $method);
        }

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
