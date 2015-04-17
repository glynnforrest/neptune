<?php

namespace Neptune\Error;

/**
 * ExceptionHandlerInterface objects are used to convert an Exception to a
 * http-foundation Response.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface ExceptionHandlerInterface
{
    /**
     * Handle an exception, optionally returning a response.
     *
     * @param Exception $exception
     * @return Response|null
     */
    public function handleException(\Exception $exception);
}
