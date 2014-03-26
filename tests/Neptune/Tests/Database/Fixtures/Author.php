<?php

namespace Neptune\Tests\Database\Fixtures;

use Neptune\Database\Entity\Entity;

class Author extends Entity
{
    protected static $table = 'authors';
    protected static $fields = array(
        'id',
        'first_name',
        'last_name',
        'age'
    );
    protected static $relations = array(
        'books' => array(
            'type' => 'has_many',
            'key' => 'id',
            'other_key' => 'authors_id',
            'other_class' => 'Neptune\Tests\Database\Relation\Book'
        ),
        'details' => array(
            'type' => 'has_one',
            'key' => 'id',
            'other_key' => 'authors_id',
            'other_class' => 'Neptune\Tests\Database\Fixtures\AuthorDetails'
        )
    );

}
