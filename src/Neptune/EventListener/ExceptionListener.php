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
    protected $neptune;
    protected $handlers = [];

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function addHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        foreach ($this->handlers as $service) {
            $handler = $this->neptune[$service];
            $result = $handler->handleException($exception, $request);
            if ($result instanceof Response) {
                $event->setResponse($result);

                return;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }
}
