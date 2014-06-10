<?php

namespace Neptune\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * SecurityRequestListener assigns the current Request to the
 * SecurityFactory automatically. This enables security drivers to
 * have access to the request without assigning it manually.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityRequestListener implements EventSubscriberInterface
{

    protected $factory;

    public function __construct(SecurityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->factory->setRequest($event->getRequest());

        return true;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest')
        );
    }

}
