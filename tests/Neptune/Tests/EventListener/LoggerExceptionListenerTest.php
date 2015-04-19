<?php

namespace Neptune\Tests\EventListener;

use Neptune\EventListener\LoggerExceptionListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * LoggerExceptionListenerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class LoggerExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;
    protected $request;
    protected $logger;
    protected $listener;

    public function setUp()
    {
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $this->request = new Request();
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->listener = new LoggerExceptionListener($this->logger);
    }

    protected function stubEvent(\Exception $exception)
    {
        return new GetResponseForExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MASTER_REQUEST, $exception);
    }

    public function testHandleNotFoundException()
    {
        $exception = new NotFoundHttpException();
        $event = $this->stubEvent($exception);
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($exception);
        $response = $this->listener->onKernelException($event);
    }

    public function testGenericExceptionIsGiven500Code()
    {
        $exception = new \Exception();
        $event = $this->stubEvent($exception);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);
        $response = $this->listener->onKernelException($event);
    }
}
