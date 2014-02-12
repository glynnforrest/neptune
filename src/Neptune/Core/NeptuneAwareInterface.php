<?php

namespace Neptune\Core;

use Neptune\Core\Neptune;

/**
 * NeptuneAwareInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface NeptuneAwareInterface
{

    public function setNeptune(Neptune $neptune);

    public function getNeptune();

}
