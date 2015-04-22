<?php

namespace Neptune\Tests\EventListener;

use Neptune\EventListener\TwigExceptionListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * TwigExceptionListenerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $twig;
    protected $listener;
    protected $kernel;
    protected $request;

    public function setUp()
    {
        $this->twig = $this->getMock('Neptune\Twig\TwigEnvironment');
        $this->listener = new TwigExceptionListener($this->twig);
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $this->request = new Request();
    }

    protected function expectTemplate($template, array $variables, $return, $exists = true)
    {
        if ($exists) {
            $this->twig->expects($this->any())
                ->method('exists')
                ->will($this->returnValue(true));
        }

        $this->twig->expects($this->once())
            ->method('render')
            ->with($template, $variables)
            ->will($this->returnValue($return));
    }

    protected function assertResponse(GetResponseForExceptionEvent $event, $content, $code)
    {
        $response = $event->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame($content, $response->getContent());
        $this->assertSame($code, $response->getStatusCode());
    }

    protected function stubEvent(\Exception $exception)
    {
        return new GetResponseForExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MASTER_REQUEST, $exception);
    }

    public function testNotFoundException()
    {
        $exception = new NotFoundHttpException();
        $event = $this->stubEvent($exception);
        $this->expectTemplate('errors/404.html.twig', ['exception' => $exception], '<p>Error</p>');
        $response = $this->listener->onKernelException($event);
        $this->assertResponse($event, '<p>Error</p>', 404);
    }

    public function testGenericExceptionIsGiven500Code()
    {
        $exception = new \Exception();
        $event = $this->stubEvent($exception);
        $this->expectTemplate('errors/500.html.twig', ['exception' => $exception], '<p>Server Error</p>');
        $response = $this->listener->onKernelException($event);
        $this->assertResponse($event, '<p>Server Error</p>', 500);
    }

    public function testTemplateDefaultsTo500()
    {
        $exception = new BadRequestHttpException();
        $event = $this->stubEvent($exception);
        $this->twig->expects($this->once())
            ->method('exists')
            ->with('errors/400.html.twig')
            ->will($this->returnValue(false));

        $this->expectTemplate('errors/500.html.twig', ['exception' => $exception], '<p>Server Error</p>', false);
        $response = $this->listener->onKernelException($event);
        $this->assertResponse($event, '<p>Server Error</p>', 400);
    }
}
