<?php

namespace Neptune\EventListener;

use Neptune\Core\Neptune;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SwiftmailerListener sends any emails in the memory spool at the end of the
 * request.
 *
 * @author Glynn Forrest me@glynnforrest.com
 **/
class SwiftmailerListener implements EventSubscriberInterface
{
    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function onKernelTerminate()
    {
        if (!isset($this->neptune['mailer.spool_used'])) {
            return;
        }

        $spool = $this->neptune['mailer.transport.spool']->getSpool();
        if (!$spool instanceof \Swift_MemorySpool) {
            return;
        }

        $spool->flushQueue($this->neptune['mailer.transport']);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => 'onKernelTerminate',
        );
    }
}
