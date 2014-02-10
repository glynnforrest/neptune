<?php

namespace Neptune\EventListener;

use Neptune\Core\Neptune;

use Symfony\Component\HttpKernel\EventListener\SessionListener as SymfonySessionListener;

/**
 * SessionListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SessionListener extends SymfonySessionListener
{

    protected $neptune;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    protected function getSession()
    {
        return $this->neptune['session'];
    }

}
