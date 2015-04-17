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

    public function testHandleNotFoundException()
    {
        $exception = new NotFoundHttpException();
        $this->twig->expects($this->once())
            ->method('render')
            ->with('errors/404.html.twig', ['exception' => $exception])
            ->will($this->returnValue('<p>Error</p>'));

        $response = $this->handler->handleException($exception, 404);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('<p>Error</p>', $response->getContent());
        $this->assertSame(404, $response->getStatusCode());
    }
}
