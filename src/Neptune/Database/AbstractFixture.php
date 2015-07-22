<?php

namespace Neptune\Database;

use Neptune\Core\Neptune;
use ActiveDoctrine\Fixture\FixtureInterface;

/**
 * AbstractFixture adds awareness of the framework.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractFixture implements FixtureInterface
{
    protected $neptune;

    public function setNeptune(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }
}
