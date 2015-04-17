<?php

namespace Neptune\Tests\Error;

use Neptune\Error\TwigExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * TwigExceptionHandlerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $twig;
    protected $handler;

    public function setUp()
    {
        $this->twig = $this->getMock('Twig_Environment');
        $this->handler = new TwigExceptionHandler($this->twig);
    }

    protected function expectTemplate($template, array $variables, $return)
    {
        $this->twig->expects($this->once())
            ->method('render')
            ->with($template, $variables)
            ->will($this->returnValue($return));
    }

    protected function assertResponse($response, $content, $code)
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame($content, $response->getContent());
        $this->assertSame($code, $response->getStatusCode());
    }

    public function testHandleNotFoundException()
    {
        $exception = new NotFoundHttpException();
        $this->expectTemplate('errors/404.html.twig', ['exception' => $exception], '<p>Error</p>');
        $response = $this->handler->handleException($exception);
        $this->assertResponse($response, '<p>Error</p>', 404);
    }

    public function testGenericExceptionIsGiven500Code()
    {
        $exception = new \Exception();
        $this->expectTemplate('errors/500.html.twig', ['exception' => $exception], '<p>Server Error</p>');
        $response = $this->handler->handleException($exception);
        $this->assertResponse($response, '<p>Server Error</p>', 500);
    }
}
