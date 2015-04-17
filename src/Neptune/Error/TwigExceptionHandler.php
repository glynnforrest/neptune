<?php

namespace Neptune\Error;

use Symfony\Component\HttpFoundation\Response;

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
        return new Response($this->twig->render('errors/404.html.twig', [
            'exception' => $exception,
        ]), $exception->getStatusCode());
    }
}
