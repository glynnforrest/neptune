<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;

/**
 * ServiceInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface ServiceInterface
{

    public function register(Neptune $neptune);

    public function boot(Neptune $neptune);

}
