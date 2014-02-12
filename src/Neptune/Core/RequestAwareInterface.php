<?php

namespace Neptune\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestAwareInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface RequestAwareInterface
{

    public function setRequest(Request $request);

    public function getRequest();

}
