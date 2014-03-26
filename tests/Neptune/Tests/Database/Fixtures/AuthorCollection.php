<?php

namespace Neptune\Tests\Database\Fixtures;

use Neptune\Database\Entity\EntityCollection;

/**
 * AuthorCollection
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AuthorCollection extends EntityCollection
{

    public function setFirst_name($name)
    {
        return strtoupper($name);
    }

    public function getLast_name()
    {
        return strtoupper($this->getRaw('last_name'));
    }

}
