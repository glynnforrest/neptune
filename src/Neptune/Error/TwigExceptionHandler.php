<?php

namespace Neptune\Error;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * TwigExceptionHandler
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function handleException(\Exception $exception)
    {
        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        return new Response($this->twig->render(sprintf('errors/%s.html.twig', $code), [
            'exception' => $exception,
        ]), $code);
    }
}
