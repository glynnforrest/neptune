<?php

namespace Neptune\EventListener;

use Neptune\Security\Firewall;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * FirewallListener listens for Requests and validates them against a
 * collection of Firewalls.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FirewallListener implements EventSubscriberInterface
{

    protected $firewalls = array();

    /**
     * Add a Firewall instance at the end of the collection of
     * firewalls.
     *
     * @param Firewall $firewall the firewall to add
     */
    public function add(Firewall $firewall)
    {
        $this->firewalls[] = $firewall;

        return $this;
    }

    /**
     * Add a Firewall instance at the beginning of the collection of
     * firewalls.
     *
     * @param Firewall $firewall the firewall to add
     */
    public function push(Firewall $firewall)
    {
        array_unshift($this->firewalls, $firewall);

        return $this;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        foreach ($this->firewalls as $firewall) {
            if ($firewall->check($request)) {
                return true;
            }
        }

        return true;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest')
        );
    }

}
