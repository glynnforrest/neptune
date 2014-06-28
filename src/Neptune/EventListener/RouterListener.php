<?php

namespace Neptune\EventListener;

use Neptune\Routing\Router;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

use Neptune\Core\Neptune;

/**
 * RouterListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RouterListener implements EventSubscriberInterface
{

    protected $router;
    protected $neptune;

    public function __construct(Router $router, Neptune $neptune)
    {
        $this->router = $router;
        $this->neptune = $neptune;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        //if the request already has controller, method and args set,
        //don't run the router
        $attr = $request->attributes;
        if ($attr->has('_controller') && $attr->has('_method') && $attr->has('_args')) {
            return;
        }

        //attempt to fetch a matched route from the cache. If this
        //isn't successful, load routes from all registered modules
        //and match.
        if (!$action = $this->router->matchCached($request)) {
            $this->router->routeModules($this->neptune);
            $action = $this->router->match($request);
        }

        $request->attributes->set('_controller', $action[0]);
        $request->attributes->set('_method', $action[1]);
        $request->attributes->set('_args', $action[2]);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest')
        );
    }

}
