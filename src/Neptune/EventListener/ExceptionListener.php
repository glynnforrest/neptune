<?php

namespace Neptune\EventListener;

use Neptune\Core\Neptune;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * ExceptionListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ExceptionListener implements EventSubscriberInterface
{

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new Response('<pre>' . $exception . '</pre>', 500);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException')
        );
    }

}
