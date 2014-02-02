<?php

namespace Neptune\EventListener;

use Neptune\Routing\Router;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * RouterListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RouterListener implements EventSubscriberInterface
{

    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $action = $this->router->matchRequest($request);
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
