<?php

namespace Neptune\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Pimple\Container;

/**
 * ViewListener
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewListener implements EventSubscriberInterface
{
    protected $container;

    public function __construct(Container $container, $service)
    {
        $this->container = $container;
        $this->service = $service;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        //attempt to extend the service so it isn't loaded unless necessary
        try {
            $this->container->extend($this->service, function ($creator) use ($event) {
                    $creator->setGlobal('request', $event->getRequest());

                    return $creator;
                });
        } catch (\InvalidArgumentException $e) {
            //the service has already been loaded
            $this->container[$this->service]->setGlobal('request', $event->getRequest());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest'),
        );
    }
}
