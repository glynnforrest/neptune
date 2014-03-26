<?php

namespace Neptune\Tests\Database\Fixtures;

use Neptune\Database\Entity\Entity;

class Book extends Entity
{
    protected static $table = 'books';
    protected static $fields = array('id', 'authors_id', 'title');

}
