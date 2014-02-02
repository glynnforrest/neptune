<?php

namespace Neptune\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * StringResponseListener
 * @author Glynn Forrest me@glynnforrest.com
 **/
class StringResponseListener implements EventSubscriberInterface
{
    /**
     * Changes strings to a Response instance.
     *
     * @param GetResponseForControllerResultEvent $event The event to handle
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();
        if (is_string($response) || (is_object($response) && method_exists($response, '__toString'))) {
            $event->setResponse(new Response((string) $response));
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView',
        );
    }
}
