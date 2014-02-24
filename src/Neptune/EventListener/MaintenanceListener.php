<?php

namespace Neptune\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * MaintenanceListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MaintenanceListener implements EventSubscriberInterface
{

    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->setResponse(new Response($this->content, 503));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 228)
        );
    }

}
