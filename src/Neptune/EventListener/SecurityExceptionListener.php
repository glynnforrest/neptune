<?php

namespace Neptune\EventListener;

use Neptune\Core\Neptune;
use Neptune\Security\Resolver\SecurityResolverInterface;
use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Driver\CsrfDriver;
use Neptune\Security\Exception\SecurityException;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityExceptionListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityExceptionListener implements EventSubscriberInterface
{

    protected $resolvers = array();

    public function add(SecurityResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
    }

    public function push(SecurityResolverInterface $resolver)
    {
        array_unshift($this->resolvers, $resolver);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof SecurityException) {
            return;
        }
        //try to get a response from one of the resolvers. It
        //could be a redirect, an access denied page, or anything
        //really
        try {
            foreach ($this->resolvers as $resolver) {
                if (!$this->resolverSupportsException($resolver, $exception)) {
                    continue;
                }

                if (!$this->resolverSupportsDriver($resolver, $exception->getSecurityDriver())) {
                    continue;
                }

                $request = $event->getRequest();
                $response = $resolver->onException($exception, $request);
                if ($response instanceof Response) {
                    $event->setResponse($response);

                    return true;
                }
            }
            //no response has been created by now, so let other
            //exception listeners handle it
            return;
        } catch (\Exception $e) {
            //if anything at all goes wrong in calling the
            //resolvers, pass the exception on
            $event->setException($e);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException')
        );
    }

    public function resolverSupportsException(SecurityResolverInterface $resolver, SecurityException $exception)
    {
        $supported = $resolver->getSupportedExceptions();

        if (true === $supported) {
            return true;
        }
        if (!is_array($supported)) {
            return false;
        }
        if (in_array(get_class($exception), $supported)) {
            return true;
        }

        return false;
    }

    public function resolverSupportsDriver(SecurityResolverInterface $resolver, SecurityDriverInterface $driver)
    {
        //If the exception comes from the CsrfDriver, imply
        //support. Support for csrf can be disabled by not including
        //CsrfException in getSupportedExceptions.
        if ($driver instanceof CsrfDriver) {
            return true;
        }

        $supported = $resolver->getSupportedDrivers();

        if (true === $supported) {
            return true;
        }

        if (!is_array($supported)) {
            return false;
        }
        if (in_array(get_class($driver), $supported)) {
            return true;
        }

        return false;
    }

}
