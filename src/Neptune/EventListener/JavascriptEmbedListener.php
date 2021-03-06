<?php

namespace Neptune\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * JavascriptEmbedListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class JavascriptEmbedListener implements EventSubscriberInterface
{

    protected $urls = array();

    public function __construct($urls)
    {
        $this->urls = (array) $urls;
    }

    /**
     * Adds javascript tags to the Response.
     *
     * @param FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        //replace the last body tag with our injected javascript

        $scripts = '';
        foreach ($this->urls as $url) {
            $scripts .= sprintf('<script type="text/javascript" src="%s"></script>', $url . PHP_EOL);
        }

        $response->setContent(str_replace('</body>', $scripts . '</body>', $response->getContent()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

}
