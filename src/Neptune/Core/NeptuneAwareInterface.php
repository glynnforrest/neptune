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

    /**
     * Set the Neptune instance of this object.
     *
     * @param Neptune $neptune The neptune instance
     */
    public function setNeptune(Neptune $neptune);

    /**
     * Get the Neptune instance of this object, if set.
     *
     * @return Neptune The neptune instance
     */
    public function getNeptune();

}
