<?php

namespace Neptune\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Neptune\Twig\TwigEnvironment;

/**
 * TwigExceptionListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigExceptionListener implements EventSubscriberInterface
{
    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $template = $this->twig->exists($specific = sprintf('errors/%s.html.twig', $code)) ? $specific : 'errors/500.html.twig';

        $content = $this->twig->render($template, [
            'exception' => $exception,
        ]);
        $event->setResponse(new Response($content, $code));
    }

    public static function getSubscribedEvents()
    {
        return [
            //low priority to let other listeners do something first
            KernelEvents::EXCEPTION => ['onKernelException', -128],
        ];
    }
}
