<?php

namespace Neptune\Tests\Database\Fixtures;

use Neptune\Database\Entity\Entity;

class AuthorDetails extends Entity
{

    protected static $table = 'author_details';
    protected static $fields = array('id', 'authors_id', 'info');
    protected static $relations = array(
        'author' => array(
            'type' => 'belongs_to',
            'key' => 'authors_id',
            'other_key' => 'id',
            'other_class', 'Neptune\Tests\Database\Fixtures\Author'
        )
    );

}
